<?php

namespace App\Http\Controllers\Api\CompanyAdmin\Work;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResponse;
use App\Models\Work\WorkCheck;
use App\Models\Work\WorkPlan;
use Illuminate\Http\Request;
use Carbon\Carbon;

class WorkCheckController extends Controller
{
    use ApiResponse;

    public function checkIn(Request $request, $workPlanId)
    {
        $workPlan = WorkPlan::findOrFail($workPlanId);
        $data = $request->validate([
            'workerId' => 'required|exists:staff,id',
            'date' => 'required|date',
            'weekNumber' => 'nullable|integer',
            'dayOfWeek' => 'nullable|integer',
            'location' => 'nullable|array',
            'notes' => 'nullable|string',
        ]);
        $check = WorkCheck::create([
            'work_plan_id' => $workPlanId,
            'worker_id' => $data['workerId'],
            'date' => $data['date'],
            'week_number' => $data['weekNumber'] ?? null,
            'day_of_week' => $data['dayOfWeek'] ?? null,
            'check_in_time' => Carbon::now(),
            'status' => 'checked_in',
            'location' => $data['location'] ?? null,
            'notes' => $data['notes'] ?? null,
        ]);
        return $this->successResponse($check, 'Check-in recorded successfully', 201);
    }

    public function checkOut(Request $request, $workPlanId)
    {
        $data = $request->validate(['workerId' => 'required|exists:staff,id', 'date' => 'required|date']);
        $check = WorkCheck::where('work_plan_id', $workPlanId)
            ->where('worker_id', $data['workerId'])
            ->where('date', $data['date'])
            ->whereNull('check_out_time')
            ->latest()->firstOrFail();
        $check->update([
            'check_out_time' => Carbon::now(),
            'status' => 'checked_out',
        ]);
        return $this->successResponse($check, 'Check-out recorded successfully');
    }

    public function updateCheckIn(Request $request)
    {
        $data = $request->validate([
            'id' => 'required|exists:work_checks,id',
            'checkInTime' => 'required|date',
        ]);
        $check = WorkCheck::findOrFail($data['id']);
        $check->update(['check_in_time' => $data['checkInTime']]);
        return $this->successResponse($check, 'Check-in time updated successfully');
    }

    public function updateCheckOut(Request $request)
    {
        $data = $request->validate([
            'id' => 'required|exists:work_checks,id',
            'checkOutTime' => 'required|date',
        ]);
        $check = WorkCheck::findOrFail($data['id']);
        $check->update(['check_out_time' => $data['checkOutTime']]);
        return $this->successResponse($check, 'Check-out time updated successfully');
    }

    public function history(Request $request, $workPlanId, $workerId)
    {
        $checks = WorkCheck::where('work_plan_id', $workPlanId)
            ->where('worker_id', $workerId)
            ->latest()->get();
        return $this->successResponse($checks);
    }

    public function todayStatus(Request $request, $workPlanId)
    {
        $checks = WorkCheck::where('work_plan_id', $workPlanId)
            ->whereDate('date', Carbon::today())
            ->with('worker')
            ->get();
        return $this->successResponse($checks);
    }

    public function workerRecords(Request $request)
    {
        $workerId = $request->workerId;
        $query = WorkCheck::with(['workPlan', 'worker']);
        if ($workerId) {
            $query->where('worker_id', $workerId);
        }
        return $this->successResponse($query->latest()->get());
    }

    public function workerStats(Request $request)
    {
        $workerId = $request->workerId;
        $query = WorkCheck::with('worker');
        if ($workerId) {
            $query->where('worker_id', $workerId);
        }
        $checks = $query->get();
        $stats = [
            'totalCheckIns' => $checks->count(),
            'totalCheckOuts' => $checks->whereNotNull('check_out_time')->count(),
            'incompleteCheckOuts' => $checks->where('incomplete_check_out', true)->count(),
        ];
        return $this->successResponse($stats);
    }

    public function fixIncompleteCheckout(Request $request)
    {
        $data = $request->validate([
            'id' => 'required|exists:work_checks,id',
            'checkOutTime' => 'required|date',
        ]);
        $check = WorkCheck::findOrFail($data['id']);
        $check->update([
            'check_out_time' => $data['checkOutTime'],
            'incomplete_check_out' => false,
            'status' => 'checked_out',
        ]);
        return $this->successResponse($check, 'Incomplete checkout fixed successfully');
    }
}
