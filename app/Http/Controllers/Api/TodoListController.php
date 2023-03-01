<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TaskResource;
use App\Http\Resources\TodoListResource;
use App\Repositories\TodoListRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TodoListController extends Controller
{
    protected $todoListRepository;
    public function __construct(TodoListRepository $todoListRepository)
    {
        $this->middleware('auth:api');
        $this->todoListRepository = $todoListRepository;
    }

    public function index(): \Illuminate\Http\JsonResponse
    {
        try {
            $todo_lists             = $this->todoListRepository->paginate(['tasks']);

            $data = [
                'todo_lists'        => new TodoListResource($todo_lists),
                'success'           => 'Todo List Retrieved Successfully'
            ];
            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }
    }

    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:todo_lists,name',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()],422);
        }
        try {
            $data = $request->all();
            $data['user_id'] = jwtUser()->id;
            $this->todoListRepository->create($data);

            $data = [
                'success' => 'Todo List Created Successfully'
            ];

            return response()->json($data, 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }
    }

    public function show($id): \Illuminate\Http\JsonResponse
    {
        try {
            $todo = $this->todoListRepository->find($id, ['tasks']);

            if (!$todo) {
                return response()->json([
                    'error' => 'Todo List No found'
                ], 404);
            }

            $data = [
                'id'    => (int)$todo->id,
                'name'  => $todo->name,
                'tasks' => TaskResource::collection($todo->tasks),
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
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()],422);
        }

        try {
            $todo = $this->todoListRepository->find($id);

            if (!$todo) {
                return response()->json([
                    'error' => 'Todo List No found'
                ], 404);
            }
            if ($todo->user_id != jwtUser()->id) {
                return response()->json([
                    'error' => 'Todo List No found'
                ], 404);
            }

            $this->todoListRepository->update($request->all(), $id);

            return response()->json([
                'success' => 'Todo List Updated'
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }
    }

    public function destroy($id): \Illuminate\Http\JsonResponse
    {
        try {
            $todo = $this->todoListRepository->find($id);

            if (!$todo) {
                return response()->json([
                    'error' => 'Todo List No found'
                ], 404);
            }
            if ($todo->user_id != jwtUser()->id) {
                return response()->json([
                    'error' => 'Todo List No found'
                ], 404);
            }

            $this->todoListRepository->delete($id);
            return response()->json([
                'success' => 'Todo List Deleted'
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }
    }
}
