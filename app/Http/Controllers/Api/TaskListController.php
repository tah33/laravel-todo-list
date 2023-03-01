<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\TaskRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TaskListController extends Controller
{
    protected $taskRepository;
    public function __construct(TaskRepository $taskRepository)
    {
        $this->middleware('auth:api');
        $this->taskRepository = $taskRepository;
    }

    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'todo_list_id'  => 'required|exists:todo_lists,id',
            'name'          => 'required|unique:tasks,name',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()],422);
        }
        try {
            $this->taskRepository->create($request->all());

            $data = [
                'success' => 'Task Created Successfully'
            ];

            return response()->json($data, 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }
    }

    public function show($id): \Illuminate\Http\JsonResponse
    {
        try {
            $task = $this->taskRepository->find($id);

            if (!$task) {
                return response()->json('Task No found', 404);
            }

            $data = [
                'id'    => (int)$task->id,
                'name'  => $task->name,
                'todo'  => @$task->todoList->name,
            ];

            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }
    }

    public function update(Request $request, $id): \Illuminate\Http\JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:todo_lists,name,'.$id,
            'todo_list_id' => 'required|exists:todo_lists,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()],422);
        }

        try {
            $task = $this->taskRepository->find($id);

            if (!$task) {
                return response()->json('Todo List No found', 404);
            }

            $this->taskRepository->update($request->all(), $id);

            return response()->json('Task Updated');
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }
    }

    public function destroy($id): \Illuminate\Http\JsonResponse
    {
        try {
            $todo = $this->taskRepository->find($id);

            if (!$todo) {
                return response()->json('Task No found', 404);
            }

            $this->taskRepository->delete($id);
            return response()->json('Task Deleted');
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }
    }
}
