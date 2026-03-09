<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Requests\StoreTaskRequest;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateTaskRequest;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Arr;
use App\Services\TaskInputParser;



class TaskController extends Controller
{

public function __construct(private TaskInputParser $parser)
    {
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
         Gate::authorize('viewAny', Task::class);

        
        return $request
            ->user()
            ->tasks()
            ->handleSort($request->query('sort_by') ?? 'time')
            ->handleFilter($request->query('due_date'))
            ->with('priority')
            ->get()
            ->toResourceCollection();
    }

    private function prepareData(array $data): array
{
    $rawName = $data['name'];
    $parsed = $this->parser->parse($data['name']);
    if ($parsed) {
        $data['name'] = $parsed['name'];
        $data['priority_id'] = $data['priority_id'] ?? ($parsed['priority_id'] ?? null);
        $data['due_date'] = $data['due_date'] ?? ($parsed['due_date'] ?? null);
    }
    $data['raw_name'] = $rawName;
    return $data;
}

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTaskRequest $request)
    {
        Gate::authorize('create', Task::class);

         $data = $request->validated();
        $task = $request->user()->tasks()->create(
            $this->prepareData($data)
        );
            $task->load('priority');
            
        return $task->toResource();
    }

    /**
     * Display the specified resource.
     */
    public function show(Task $task)
    {
       Gate::authorize('view', $task);

        $task->load('priority');
        return $task->toResource();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTaskRequest $request, Task $task)
    {
       Gate::authorize('update', $task);

        $task->update(
            $this->prepareData($request->validated())
        );
        $task->load('priority');
        return $task->toResource();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Task $task)
    {
       Gate::authorize('delete', $task);
       
        $task->delete();
        return response()->noContent();
    }
}