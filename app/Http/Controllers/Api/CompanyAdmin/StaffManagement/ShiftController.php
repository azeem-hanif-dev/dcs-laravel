<?php

namespace App\Http\Controllers\Api\CompanyAdmin\StaffManagement;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResponse;
use App\Models\StaffManagement\Shift;
use Illuminate\Http\Request;

class ShiftController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $shifts = Shift::where('company_id', $request->company_id)->latest()->get();
        return $this->successResponse($shifts);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'startTime' => 'required|string',
            'endTime' => 'required|string',
            'timeCushion' => 'nullable|string',
            'totalTime' => 'nullable|string',
        ]);
        $data['user_id'] = $request->auth_user->id;
        $data['company_id'] = $request->company_id;
        if (isset($data['startTime'])) { $data['start_time'] = $data['startTime']; unset($data['startTime']); }
        if (isset($data['endTime'])) { $data['end_time'] = $data['endTime']; unset($data['endTime']); }
        if (isset($data['timeCushion'])) { $data['time_cushion'] = $data['timeCushion']; unset($data['timeCushion']); }
        if (isset($data['totalTime'])) { $data['total_time'] = $data['totalTime']; unset($data['totalTime']); }
        $shift = Shift::create($data);
        return $this->successResponse($shift, 'Shift created successfully', 201);
    }

    public function show(Request $request, $id)
    {
        $shift = Shift::where('company_id', $request->company_id)->findOrFail($id);
        return $this->successResponse($shift);
    }

    public function update(Request $request, $id)
    {
        $shift = Shift::where('company_id', $request->company_id)->findOrFail($id);
        $data = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'startTime' => 'sometimes|string',
            'endTime' => 'sometimes|string',
            'timeCushion' => 'nullable|string',
            'totalTime' => 'nullable|string',
        ]);
        if (isset($data['startTime'])) { $data['start_time'] = $data['startTime']; unset($data['startTime']); }
        if (isset($data['endTime'])) { $data['end_time'] = $data['endTime']; unset($data['endTime']); }
        if (isset($data['timeCushion'])) { $data['time_cushion'] = $data['timeCushion']; unset($data['timeCushion']); }
        if (isset($data['totalTime'])) { $data['total_time'] = $data['totalTime']; unset($data['totalTime']); }
        $shift->update($data);
        return $this->successResponse($shift, 'Shift updated successfully');
    }

    public function destroy(Request $request, $id)
    {
        $shift = Shift::where('company_id', $request->company_id)->findOrFail($id);
        $shift->delete();
        return $this->successResponse(null, 'Shift deleted successfully');
    }
}
