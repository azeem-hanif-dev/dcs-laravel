<?php

namespace App\Http\Controllers\Api\CompanyAdmin\Project;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResponse;
use App\Models\Project\ProjectMaterialAssignment;
use Illuminate\Http\Request;

class ProjectMaterialAssignmentController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $assignments = ProjectMaterialAssignment::whereHas('project', function ($q) use ($request) {
            $q->where('company_id', $request->company_id);
        })->with(['project', 'material', 'worker'])->latest()->get();
        return $this->successResponse($assignments);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'projectId' => 'required|exists:projects,id',
            'materialId' => 'required|exists:materials,id',
            'workerId' => 'nullable|exists:staff,id',
            'category' => 'nullable|string',
            'assignedQuantity' => 'required|integer|min:0',
            'remainingQuantity' => 'required|integer|min:0',
            'dailyConsumption' => 'nullable|integer|min:0',
            'usedQuantity' => 'nullable|integer|min:0',
            'lifeUsed' => 'nullable|integer|min:0',
            'type' => 'nullable|string',
            'expiryDate' => 'nullable|date',
            'status' => 'nullable|string',
            'returnedQuantity' => 'nullable|integer|min:0',
            'notes' => 'nullable|string',
        ]);
        if (isset($data['projectId'])) { $data['project_id'] = $data['projectId']; unset($data['projectId']); }
        if (isset($data['materialId'])) { $data['material_id'] = $data['materialId']; unset($data['materialId']); }
        if (isset($data['workerId'])) { $data['worker_id'] = $data['workerId']; unset($data['workerId']); }
        if (isset($data['assignedQuantity'])) { $data['assigned_quantity'] = $data['assignedQuantity']; unset($data['assignedQuantity']); }
        if (isset($data['remainingQuantity'])) { $data['remaining_quantity'] = $data['remainingQuantity']; unset($data['remainingQuantity']); }
        if (isset($data['dailyConsumption'])) { $data['daily_consumption'] = $data['dailyConsumption']; unset($data['dailyConsumption']); }
        if (isset($data['usedQuantity'])) { $data['used_quantity'] = $data['usedQuantity']; unset($data['usedQuantity']); }
        if (isset($data['lifeUsed'])) { $data['life_used'] = $data['lifeUsed']; unset($data['lifeUsed']); }
        if (isset($data['expiryDate'])) { $data['expiry_date'] = $data['expiryDate']; unset($data['expiryDate']); }
        if (isset($data['returnedQuantity'])) { $data['returned_quantity'] = $data['returnedQuantity']; unset($data['returnedQuantity']); }
        $assignment = ProjectMaterialAssignment::create($data);
        return $this->successResponse($assignment, 'Material assignment created successfully', 201);
    }

    public function show(Request $request, $id)
    {
        $assignment = ProjectMaterialAssignment::whereHas('project', function ($q) use ($request) {
            $q->where('company_id', $request->company_id);
        })->with(['project', 'material', 'worker'])->findOrFail($id);
        return $this->successResponse($assignment);
    }

    public function update(Request $request, $id)
    {
        $assignment = ProjectMaterialAssignment::whereHas('project', function ($q) use ($request) {
            $q->where('company_id', $request->company_id);
        })->findOrFail($id);
        $data = $request->validate([
            'projectId' => 'sometimes|exists:projects,id',
            'materialId' => 'sometimes|exists:materials,id',
            'workerId' => 'nullable|exists:staff,id',
            'category' => 'nullable|string',
            'assignedQuantity' => 'sometimes|integer|min:0',
            'remainingQuantity' => 'sometimes|integer|min:0',
            'dailyConsumption' => 'nullable|integer|min:0',
            'usedQuantity' => 'nullable|integer|min:0',
            'lifeUsed' => 'nullable|integer|min:0',
            'type' => 'nullable|string',
            'expiryDate' => 'nullable|date',
            'status' => 'nullable|string',
            'returnedQuantity' => 'nullable|integer|min:0',
            'notes' => 'nullable|string',
        ]);
        if (isset($data['projectId'])) { $data['project_id'] = $data['projectId']; unset($data['projectId']); }
        if (isset($data['materialId'])) { $data['material_id'] = $data['materialId']; unset($data['materialId']); }
        if (isset($data['workerId'])) { $data['worker_id'] = $data['workerId']; unset($data['workerId']); }
        if (isset($data['assignedQuantity'])) { $data['assigned_quantity'] = $data['assignedQuantity']; unset($data['assignedQuantity']); }
        if (isset($data['remainingQuantity'])) { $data['remaining_quantity'] = $data['remainingQuantity']; unset($data['remainingQuantity']); }
        if (isset($data['dailyConsumption'])) { $data['daily_consumption'] = $data['dailyConsumption']; unset($data['dailyConsumption']); }
        if (isset($data['usedQuantity'])) { $data['used_quantity'] = $data['usedQuantity']; unset($data['usedQuantity']); }
        if (isset($data['lifeUsed'])) { $data['life_used'] = $data['lifeUsed']; unset($data['lifeUsed']); }
        if (isset($data['expiryDate'])) { $data['expiry_date'] = $data['expiryDate']; unset($data['expiryDate']); }
        if (isset($data['returnedQuantity'])) { $data['returned_quantity'] = $data['returnedQuantity']; unset($data['returnedQuantity']); }
        $assignment->update($data);
        return $this->successResponse($assignment, 'Material assignment updated successfully');
    }

    public function destroy(Request $request, $id)
    {
        $assignment = ProjectMaterialAssignment::whereHas('project', function ($q) use ($request) {
            $q->where('company_id', $request->company_id);
        })->findOrFail($id);
        $assignment->delete();
        return $this->successResponse(null, 'Material assignment deleted successfully');
    }

    public function byProject(Request $request, $projectId)
    {
        $assignments = ProjectMaterialAssignment::where('project_id', $projectId)
            ->with(['material', 'worker'])->latest()->get();
        return $this->successResponse($assignments);
    }
}
