<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use App\Models\User;
use App\Notifications\TaskAssigned;
use App\Notifications\TaskCompleted;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TaskController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
        $this->authorizeResource(Task::class, 'task');
    }

    /**
     * @OA\Get(
     *     path="/api/tasks",
     *     summary="Get all tasks",
     *     tags={"Tasks"},
     *     description="Returns all tasks from the system that the user has access to.",
     *     operationId="indexTasks",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="filter",
     *         in="query",
     *         description="Filter tasks by status",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             enum={"completed", "incompleted", "overdue", "assigned_to_me", "created_by_me", "assigned_to_others", "created_by_others"}
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="sort",
     *         in="query",
     *         description="Sort tasks by field",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             enum={"dueDate", "-dueDate", "title", "-title", "completed", "-completed", "createdAt", "-createdAt"}
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Tasks retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/TaskResource")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Unauthenticated."
     *             )
     *         )
     *    ),
     * )
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        $query = Task::with(['creator', 'assignee']);

        if (!$user->isAdmin()) {
            $query->where('created_by', $user->id)
                ->orWhere('assigned_to', $user->id);
        }

        $possibleFilters = [
            'completed' => 'completed',
            'incompleted' => 'incompleted',
            'overdue' => 'overdue',
            'assigned_to_me' => 'assignedToMe',
            'created_by_me' => 'createdByMe',
            'assigned_to_others' => 'assignedToOthers',
            'created_by_others' => 'createdByOthers',
        ];

        $filters = explode(',', $request->filter);

        foreach ($possibleFilters as $key => $filter) {
            if (in_array($key, $filters)) {
                $query->$filter();
            }
        }

        $possibleOrders = [
            'dueDate' => 'orderByDueDate',
            '-dueDate' => 'orderByDueDateDesc',
            'title' => 'orderByTitle',
            '-title' => 'orderByTitleDesc',
            'completed' => 'orderByCompleted',
            '-completed' => 'orderByCompletedDesc',
            'createdAt' => 'orderByCreatedAt',
            '-createdAt' => 'orderByCreatedAtDesc',
        ];

        $sort = explode(',', $request->sort);

        foreach ($possibleOrders as $key => $order) {
            if (in_array($key, $sort)) {
                $query->$order();
            }
        }

        $tasks = $query->get();

        return TaskResource::collection($tasks);
    }

    /**
     * @OA\Post(
     *     path="/api/tasks",
     *     summary="Create a task",
     *     tags={"Tasks"},
     *     description="Create a task",
     *     operationId="storeTasks",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Task object that needs to be created",
     *         @OA\JsonContent(
     *             required={"title"},
     *             @OA\Property(property="title", type="string", example="Task title"),
     *             @OA\Property(property="description", type="string", example="Task description"),
     *             @OA\Property(property="assigned_to", type="string", example="9a136676-8ebb-4304-9de9-78bc74ab1d69", description="User ID or email"),
     *             @OA\Property(property="due_date", type="string", format="date", example="2021-01-01"),
     *             @OA\Property(property="file", type="string", format="binary", example="file.pdf"),
     *             @OA\Property(property="completed", type="boolean", example="false"),
     *             @OA\Property(property="created_by", type="string", example="9a136676-8ebb-4304-9de9-78bc74ab1d69"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Task created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 ref="#/components/schemas/TaskResource"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Unauthenticated."
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Unprocessable Entity",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="The given data was invalid."
     *             ),
     *         ),
     *     ),
     * )
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'assigned_to' => 'nullable',
            'due_date' => 'nullable|date',
            'file' => 'nullable|file|mimes:pdf'
        ]);

        $assignedTo = $request->has('assigned_to')
            ? User::where('id', $request->assigned_to)->orWhere('email', $request->assigned_to)->first()->id
            : auth()->user()->id;

        $task = Task::create([
            'title' => $request->title,
            'description' => $request->description ? $request->description : '',
            'assigned_to' => $assignedTo,
            'created_by' => auth()->user()->id,
            'due_date' => $request->due_date ? $request->due_date : null,
        ]);

        if ($request->has('file')) {
            Storage::put('users/' . $assignedTo . '/' . 'tasks/' . $task->id . '.pdf', file_get_contents($request->file->getRealPath()));
        }

        if ($assignedTo !== auth()->user()->id) {
            User::find($assignedTo)->notify(new TaskAssigned($task));
        }
        return new TaskResource($task);
    }


    /**
     * @OA\Get(
     *     path="/api/tasks/{task}",
     *     summary="Display the specified task",
     *     description="Display the specified task",
     *     operationId="showTasks",
     *     tags={"Tasks"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="task",
     *         in="path",
     *         description="The ID of the task to be displayed",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *             format="uuid"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Task resource",
     *         @OA\JsonContent(ref="#/components/schemas/TaskResource")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Unauthenticated."
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Forbidden."
     *             )
     *         ),
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Task not found"
     *     )
     * )
     */
    public function show(Task $task)
    {
        $task->load(['creator', 'assignee']);
        return new TaskResource($task);
    }

    /**
     * Download the file attached to the given task.
     *
     * @OA\Get(
     *     path="/api/tasks/{task}/file",
     *     summary="Download the file attached to the given task",
     *     description="Download the file attached to the given task",
     *     operationId="downloadFileTasks",
     *     tags={"Tasks"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="task",
     *         in="path",
     *         description="ID of the task to download the file from",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *             format="uuid"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="File downloaded successfully"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Not Found"
     *     )
     * )
     */
    public function downloadFile(Task $task)
    {
        $this->authorize('view', $task);

        if ($task->file === null) {
            return response()->json([
                'message' => 'No file attached to this task.'
            ], 404);
        }
        return Storage::download('users/' . $task->assignee->id . '/' . 'tasks/' . $task->id . '.pdf');
    }


    /**
     * @OA\Put(
     *     path="/api/tasks/{task}",
     *     summary="Update a task",
     *     description="Update a task by ID",
     *     operationId="updateTasks",
     *     tags={"Tasks"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="task",
     *         in="path",
     *         description="ID of the task to update",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *             format="uuid"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="title", type="string", example="Task title"),
     *             @OA\Property(property="description", type="string", example="Task description"),
     *             @OA\Property(property="assigned_to", type="string", example="9a136676-8ebb-4304-9de9-78bc74ab1d69", description="User ID or email"),
     *             @OA\Property(property="due_date", type="string", format="date", example="2021-01-01"),
     *             @OA\Property(property="file", type="string", format="binary", example="file.pdf"),
     *             @OA\Property(property="completed", type="boolean", example="false"),
     *             @OA\Property(property="created_by", type="string", example="9a136676-8ebb-4304-9de9-78bc74ab1d69"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Task updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Task")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Unauthenticated."
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Forbidden."
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Task not found",
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Unprocessable Entity",
     *     ),
     * )
     */
    public function update(Request $request, Task $task)
    {
        $validatedData = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'assigned_to' => 'sometimes',
            'due_date' => 'sometimes|date',
            'completed' => 'sometimes|boolean',
            'file' => 'sometimes|file|mimes:pdf'
        ]);

        if ($request->has('file')) {
            Storage::put('users/' . $task->assignee->id . '/' . 'tasks/' . $task->id . '.pdf', file_get_contents($request->file->getRealPath()));
        }
        $task->update($validatedData);

        return new TaskResource($task);
    }

    /**
     * @OA\Put(
     *     path="/api/tasks/{task}/complete",
     *     summary="Mark a task as completed",
     *     description="Mark a task as completed and notify the assignee if they are not the creator.",
     *     operationId="completeTask",
     *     security={{"bearerAuth":{}}},
     *     tags={"Tasks"},
     *     @OA\Parameter(
     *         name="task",
     *         in="path",
     *         description="ID of the task to complete",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *             format="uuid"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Task resource",
     *         @OA\JsonContent(ref="#/components/schemas/TaskResource")
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Not Found"
     *     )
     * )
     */
    public function complete(Task $task)
    {
        $this->authorize('complete', $task);

        $task->update([
            'completed' => true
        ]);

        if ($task->assignee->id !== $task->creator->id) {
            $task->assignee->notify(new TaskCompleted($task));
        }
        return new TaskResource($task);
    }

    /**
     * @OA\Delete(
     *     path="/api/tasks/{task}",
     *     summary="Delete a task",
     *     description="Delete a task by ID",
     *     operationId="deleteTask",
     *     security={{"bearerAuth":{}}},
     *     tags={"Tasks"},
     *     @OA\Parameter(
     *         name="task",
     *         in="path",
     *         description="ID of the task to delete",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *             format="uuid"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Task deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Task deleted successfully."
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Forbidden."
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Task not found",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Task not found."
     *             )
     *         )
     *     )
     * )
     */
    public function destroy(Task $task)
    {
        $task->delete();

        return response()->json([
            'message' => 'Task deleted successfully.'
        ]);
    }
}
