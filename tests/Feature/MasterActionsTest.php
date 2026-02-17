<?php

namespace Tests\Feature;

use App\Models\Request as RepairRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MasterActionsTest extends TestCase
{
    use RefreshDatabase;

    private User $master;
    private RepairRequest $request;

    protected function setUp(): void
    {
        parent::setUp();
        $this->master = User::factory()->create(['role' => 'master']);
    }

    /**
     * Test a master can take an assigned request in work.
     */
    public function test_master_can_take_request_in_work(): void
    {
        $request = RepairRequest::factory()->create([
            'status' => 'assigned',
            'assigned_to' => $this->master->id,
        ]);

        $response = $this->actingAs($this->master)
                         ->post(route('master.requests.take_in_work', $request));

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Заявка успешно взята в работу!');

        $this->assertDatabaseHas('requests', [
            'id' => $request->id,
            'status' => 'in_progress',
        ]);

        $this->assertDatabaseHas('request_logs', [
            'request_id' => $request->id,
            'action' => "Статус заявки изменен на 'in_progress'",
        ]);
    }

    /**
     * Test a master can complete an in-progress request.
     */
    public function test_master_can_complete_request(): void
    {
        $request = RepairRequest::factory()->create([
            'status' => 'in_progress',
            'assigned_to' => $this->master->id,
        ]);

        $response = $this->actingAs($this->master)
                         ->post(route('master.requests.complete', $request));

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Заявка успешно завершена!');

        $this->assertDatabaseHas('requests', [
            'id' => $request->id,
            'status' => 'done',
        ]);

        $this->assertDatabaseHas('request_logs', [
            'request_id' => $request->id,
            'action' => "Статус заявки изменен на 'done'",
        ]);
    }

    /**
     * Test a master cannot take a request assigned to another master.
     */
    public function test_master_cannot_take_unassigned_request(): void
    {
        $otherMaster = User::factory()->create(['role' => 'master']);
        $request = RepairRequest::factory()->create([
            'status' => 'assigned',
            'assigned_to' => $otherMaster->id,
        ]);

        $response = $this->actingAs($this->master)
                         ->post(route('master.requests.take_in_work', $request));

        $response->assertStatus(403); // Forbidden
    }
}
