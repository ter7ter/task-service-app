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

        if ($repairRequest->status !== 'assigned') {
            return back()->with('error', 'Заявка уже взята в работу или имеет другой статус.');
        }

        // Concurrency handling
        try {
            DB::beginTransaction();

            $updated = RepairRequest::where('id', $repairRequest->id)
                                    ->where('status', 'assigned')
                                    ->update(['status' => 'in_progress']);

            if (!$updated) {
                DB::rollBack();
                return back()->with('error', 'Заявка уже была взята в работу или ее статус изменился.');
            }

            DB::commit();
            return back()->with('success', 'Заявка успешно взята в работу!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Произошла ошибка при попытке взять заявку в работу: ' . $e->getMessage());
        }
    }

    public function complete(RepairRequest $repairRequest)
    {
        if (Auth::user()->role !== 'master' || $repairRequest->assigned_to !== Auth::id()) {
            abort(403);
        }

        if ($repairRequest->status !== 'in_progress') {
            return back()->with('error', 'Заявка не находится в статусе "в работе".');
        }

        $repairRequest->update(['status' => 'done']);

        return back()->with('success', 'Заявка успешно завершена!');
    }
}
