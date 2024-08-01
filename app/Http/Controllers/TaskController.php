<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;
use App\Models\Tag;
use Illuminate\Support\Facades\Validator;
use App\Policies\TaskPolicy;

class TaskController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->except(['index']);
    }

    // Endpoint público para listar tareas con su id y nombre
    public function index()
    {
        $tasks = Task::select('id', 'title')->get();
        return response()->json($tasks, 200);
    }
public function show($id)
{
    $task = Task::findOrFail($id);
    return response()->json($task, 200);
}

    // Endpoint privado para obtener las tareas de un usuario
    public function getUserTasks()
    {
        $user = auth()->user();
        $tasks = $user->tasks()->select('id', 'title', 'priority', 'completed')->get();
        return response()->json($tasks, 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'priority' => 'required|in:baja,media,alta',
            'tags' => 'array',
            'tags.*' => 'exists:tags,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $task = new Task();
        $task->title = $request->input('title');
        $task->priority = $request->input('priority');
        $task->completed = false;
        $task->user_id = auth()->user()->id;
        $task->save();

        $task->tags()->sync($request->input('tags', []));

        return response()->json(['message' => 'Tarea creada correctamente', 'task' => $task], 201);
    }

    public function update(Request $request, $id)
    {
        $task = Task::findOrFail($id);
        $this->authorize('update', $task);

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'priority' => 'required|in:baja,media,alta',
            'completed' => 'required|boolean',
            'tags' => 'array',
            'tags.*' => 'exists:tags,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $task->title = $request->input('title');
        $task->priority = $request->input('priority');
        $task->completed = $request->input('completed');
        $task->save();

        $task->tags()->sync($request->input('tags', []));

        return response()->json(['message' => 'Tarea actualizada correctamente', 'task' => $task], 200);
    }

    public function destroy($id)
    {
        $task = Task::findOrFail($id);
        $this->authorize('delete', $task);

        $task->delete();
        return response()->json(['message' => 'Tarea eliminada correctamente'], 200);
    }

    public function complete($id)
    {
        $task = Task::findOrFail($id);
        $this->authorize('update', $task);

        $task->update(['completed' => true]);
        return response()->json(['message' => 'Tarea marcada como completada', 'task' => $task], 200);
    }
}
