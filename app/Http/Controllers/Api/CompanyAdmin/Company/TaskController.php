<?php

namespace App\Http\Controllers\Api\CompanyAdmin\Company;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResponse;
use App\Models\Company\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $tasks = Task::where('company_id', $request->company_id)->latest()->get();
        return $this->successResponse($tasks);
    }

    public function store(Request $request)
    {
        $data = $request->validate(['name' => 'required|string|max:50']);
        $data['user_id'] = $request->auth_user->id;
        $data['company_id'] = $request->company_id;
        $task = Task::create($data);
        return $this->successResponse($task, 'Task created successfully', 201);
    }

    public function show(Request $request, $id)
    {
        $task = Task::where('company_id', $request->company_id)->findOrFail($id);
        return $this->successResponse($task);
    }

    public function update(Request $request, $id)
    {
        $task = Task::where('company_id', $request->company_id)->findOrFail($id);
        $data = $request->validate(['name' => 'required|string|max:50']);
        $task->update($data);
        return $this->successResponse($task, 'Task updated successfully');
    }

    public function destroy(Request $request, $id)
    {
        $task = Task::where('company_id', $request->company_id)->findOrFail($id);
        $task->delete();
        return $this->successResponse(null, 'Task deleted successfully');
    }
}
