<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use App\Models\User;
use App\Notifications\TaskAssigned;
use App\Notifications\TaskCompleted;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
        $this->authorizeResource(Task::class, 'task');
    }

    /**
     * Display a listing of the resource.
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
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'assigned_to' => 'nullable',
            'due_date' => 'nullable|date'
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

        if ($assignedTo !== auth()->user()->id) {
            User::find($assignedTo)->notify(new TaskAssigned($task));
        }
        return new TaskResource($task);
    }

    /**
     * Display the specified resource.
     */
    public function show(Task $task)
    {
        $task->load(['creator', 'assignee']);
        return new TaskResource($task);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Task $task)
    {
        $validatedData = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'assigned_to' => 'sometimes',
            'due_date' => 'sometimes|date',
            'completed' => 'sometimes|boolean'
        ]);

        $task->update($validatedData);

        return new TaskResource($task);
    }

    public function complete(Task $task)
    {
        $this->authorize('complete', $task);

        $task->update([
            'completed' => true
        ]);

        if($task->assignee->id !== $task->creator->id) {
            $task->assignee->notify(new TaskCompleted($task));
        }
        return new TaskResource($task);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Task $task)
    {
        $task->delete();

        return response()->json([
            'message' => 'Task deleted successfully.'
        ]);
    }
}
