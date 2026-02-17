<?php

namespace App\Http\Controllers;

use App\Models\Request as RepairRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DispatcherController extends Controller
{
    public function index(Request $request)
    {
        if (Auth::user()->role !== 'dispatcher') {
            abort(403);
        }

        $query = RepairRequest::query();

        if ($request->has('status') && $request->input('status') !== '') {
            $query->where('status', $request->input('status'));
        }

        $requests = $query->with('assignedTo')->orderBy('created_at', 'desc')->get();
        $masters = User::where('role', 'master')->get();

        return view('dispatcher.dashboard', compact('requests', 'masters'));
    }

    public function assign(Request $request, RepairRequest $repairRequest)
    {
        if (Auth::user()->role !== 'dispatcher') {
            abort(403);
        }

        $validated = $request->validate([
            'master_id' => 'required|exists:users,id',
        ]);

        $repairRequest->assigned_to = $validated['master_id'];
        $repairRequest->status = 'assigned';
        $repairRequest->save();

        return back()->with('success', 'Мастер успешно назначен.');
    }

    public function cancel(RepairRequest $repairRequest)
    {
        if (Auth::user()->role !== 'dispatcher') {
            abort(403);
        }

        $repairRequest->status = 'canceled';
        $repairRequest->assigned_to = null; // Unassign master if canceled
        $repairRequest->save();

        return back()->with('success', 'Заявка успешно отменена.');
    }

    public function history(RepairRequest $repairRequest)
    {
        if (Auth::user()->role !== 'dispatcher') {
            abort(403);
        }

        $logs = $repairRequest->history()->with('user')->get();

        return view('dispatcher.requests.history', ['request' => $repairRequest, 'logs' => $logs]);
    }
}
