<?php

namespace App\Observers;

use App\Models\Request as RepairRequest;
use App\Models\RequestLog;
use Illuminate\Support\Facades\Auth;

class RequestObserver
{
    /**
     * Handle the Request "created" event.
     */
    public function created(RepairRequest $request): void
    {
        $this->logAction($request, 'Заявка создана');
    }

    /**
     * Handle the Request "updated" event.
     */
    public function updated(RepairRequest $request): void
    {
        if ($request->isDirty('assigned_to')) {
            $masterName = $request->assignedTo->name ?? 'N/A';
            $this->logAction($request, "Заявка назначена на мастера: {$masterName}");
        }

        if ($request->isDirty('status')) {
            $this->logAction($request, "Статус заявки изменен на '{$request->status}'");
        }
    }

    /**
     * Log an action for the request.
     *
     * @param RepairRequest $request
     * @param string $action
     */
    private function logAction(RepairRequest $request, string $action): void
    {
        RequestLog::create([
            'request_id' => $request->id,
            'user_id' => Auth::id(),
            'action' => $action,
        ]);
    }

    /**
     * Handle the Request "deleted" event.
     */
    public function deleted(RepairRequest $request): void
    {
        //
    }

    /**
     * Handle the Request "restored" event.
     */
    public function restored(RepairRequest $request): void
    {
        //
    }

    /**
     * Handle the Request "force deleted" event.
     */
    public function forceDeleted(RepairRequest $request): void
    {
        //
    }
}
