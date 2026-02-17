<?php

namespace Tests\Feature;

use App\Models\Request as RepairRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DispatcherActionsTest extends TestCase
{
    use RefreshDatabase;

    private User $dispatcher;
    private User $master;

    protected function setUp(): void
    {
        parent::setUp();
        // Create users using factories
        $this->dispatcher = User::factory()->create(['role' => 'dispatcher']);
        $this->master = User::factory()->create(['role' => 'master']);
    }

    /**
     * Test that a dispatcher can assign a new request to a master.
     */
    public function test_dispatcher_can_assign_request(): void
    {
        // Create a new request
        $request = RepairRequest::factory()->create(['status' => 'new']);

        // Authenticate as the dispatcher and send the request
        $response = $this->actingAs($this->dispatcher)
                         ->post(route('dispatcher.requests.assign', $request), [
                             'master_id' => $this->master->id,
                         ]);

        // Assertions
        $response->assertRedirect();
        $response->assertSessionHas('success', 'Мастер успешно назначен.');

        $this->assertDatabaseHas('requests', [
            'id' => $request->id,
            'status' => 'assigned',
            'assigned_to' => $this->master->id,
        ]);

        $this->assertDatabaseHas('request_logs', [
            'request_id' => $request->id,
            'action' => "Заявка назначена на мастера: {$this->master->name}",
        ]);
    }

    /**
     * Test that a dispatcher can cancel an assigned request.
     */
    public function test_dispatcher_can_cancel_request(): void
    {
        // Create a request already assigned to the master
        $request = RepairRequest::factory()->create([
            'status' => 'assigned',
            'assigned_to' => $this->master->id,
        ]);

        // Authenticate as the dispatcher and send the request
        $response = $this->actingAs($this->dispatcher)
                         ->post(route('dispatcher.requests.cancel', $request));

        // Assertions
        $response->assertRedirect();
        $response->assertSessionHas('success', 'Заявка успешно отменена.');

        $this->assertDatabaseHas('requests', [
            'id' => $request->id,
            'status' => 'canceled',
            'assigned_to' => null,
        ]);

        $this->assertDatabaseHas('request_logs', [
            'request_id' => $request->id,
            'action' => "Статус заявки изменен на 'canceled'",
        ]);
    }
}
