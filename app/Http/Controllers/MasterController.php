<?php

namespace App\Http\Controllers;

use App\Models\Request as RepairRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class MasterController extends Controller
{
    public function index()
    {
        if (Auth::user()->role !== 'master') {
            abort(403);
        }

        $masterId = Auth::id();
        $requests = RepairRequest::where('assigned_to', $masterId)
                                ->whereIn('status', ['assigned', 'in_progress'])
                                ->orderBy('created_at', 'desc')
                                ->get();

        return view('master.dashboard', compact('requests'));
    }

    public function takeInWork(RepairRequest $repairRequest)
    {
        if (Auth::user()->role !== 'master' || $repairRequest->assigned_to !== Auth::id()) {
            abort(403);
        }

        try {
            DB::transaction(function () use ($repairRequest) {
                $requestToUpdate = RepairRequest::where('id', $repairRequest->id)
                    ->where('status', 'assigned')
                    ->lockForUpdate()
                    ->first();

                if (!$requestToUpdate) {
                    // This will be caught by the outer catch block and result in a generic error.
                    // For a more specific message, we'd need a custom exception.
                    throw new \Exception('Could not obtain lock or status has changed.');
                }

                $requestToUpdate->status = 'in_progress';
                $requestToUpdate->save(); // This will trigger the observer
            });
        } catch (\Exception $e) {
            return back()->with('error', 'Заявка уже была взята в работу другим мастером или ее статус изменился.');
        }

        return back()->with('success', 'Заявка успешно взята в работу!');
    }

    public function complete(RepairRequest $repairRequest)
    {
        if (Auth::user()->role !== 'master' || $repairRequest->assigned_to !== Auth::id()) {
            abort(403);
        }

        if ($repairRequest->status !== 'in_progress') {
            return back()->with('error', 'Заявка не находится в статусе "в работе".');
        }

        $repairRequest->status = 'done';
        $repairRequest->save(); // This will trigger the observer

        return back()->with('success', 'Заявка успешно завершена!');
    }
}
