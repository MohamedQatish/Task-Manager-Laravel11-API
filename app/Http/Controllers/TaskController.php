<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Resources\StoreTaskResource;
use App\Models\Category;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{

    public function addToFavorites($taskId)
    {
        Task::findOrFail($taskId);
        Auth::user()->favoriteTasks()->syncWithoutDetaching($taskId);
        return response()->json(['message' => 'Task added to favorites'], 200);
    }

    public function removeFromFavorites($taskId)
    {
        Task::findOrFail($taskId);
        Auth::user()->favoriteTasks()->detach($taskId);
        return response()->json(['message' => 'Task removed from favorites'], 200);
    }
    public function getFavoriteTasks()
    {
        $tasks = Auth::user()->favoriteTasks()->get();

        return response()->json($tasks, 200);
    }

    public function  addCategoriesToTask(Request $request, $taskId)
    {
        $task = Task::findOrFail($taskId);
        $task->categories()->attach($request->category_id);
        return response()->json('Category attached successfully', 200);
    }
    public function getTaskCategories($taskId)
    {
        $categories = Task::findOrFail($taskId)->categories;
        return response()->json($categories, 200);
    }

    public function getCategorieTasks($categoriyId)
    {
        $tasks = Category::findOrFail($categoriyId)->tasks;
        return response()->json($tasks, 200);
    }
    public function getTaskUser($id)
    {
        $user = Task::findOrFail($id)->user;
        return response()->json($user, 200);
    }

    public function getTasksByPriority(Request $request)
    {
        $order = $request->input('order', 'desc');
        $priorityOrder = $order === 'asc' ? "FIELD(priority, 'low', 'medium', 'high')" : "FIELD(priority, 'high', 'medium', 'low')";
        $tasks = Auth::user()->tasks()->orderByRaw($priorityOrder)->get();
        return response()->json($tasks, 200);
    }

    public function getAllTasks()
    {
        $tasks = Task::all();
        return response()->json($tasks, 200);
    }

    public function index()
    {
        $tasks = Auth::user()->tasks;
        return response()->json($tasks, 200);
    }
    public function store(StoreTaskRequest $request)
    {
        $user_id = Auth::user()->id;
        $validatedData = $request->validated();
        $validatedData['user_id'] = $user_id;
        $task = Task::create($validatedData);
        return response()->json($task,201);
    }

    public function update(UpdateTaskRequest $request, $id) // تحديث المهمة
    { // id=1
        $user_id = Auth::user()->id; // 2
        $task = Task::findOrFail($id);
        if ($task->user_id != $user_id)
            return response()->json(['message' => 'Unauthurized'], 403);

        $task->update($request->validated());
        return response()->json($task, 200);
    }

    public function show($id)
    {
        $task = Task::find($id);
        return response()->json($task, 200);
    }

    public function destroy($id)
    {
        $task = Task::findOrFail($id);
        $task->delete();
        return response()->json(null, 204);
    }
}
