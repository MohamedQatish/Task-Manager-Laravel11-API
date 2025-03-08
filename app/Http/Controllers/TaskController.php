<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Resources\StoreTaskResource;
use App\Models\Category;
use App\Models\Task;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    public function addToFavorites($taskId)
    {
        try {
            Task::findOrFail($taskId);
            Auth::user()->favoriteTasks()->syncWithoutDetaching($taskId);
            return response()->json(['message' => 'Task added to favorites'], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Task not found'], 404);
        } catch (Exception $e) {
            return response()->json(['error' => 'Something went wrong'], 500);
        }
    }

    public function removeFromFavorites($taskId)
    {
        try {
            Task::findOrFail($taskId);
            Auth::user()->favoriteTasks()->detach($taskId);
            return response()->json(['message' => 'Task removed from favorites'], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Task not found'], 404);
        } catch (Exception $e) {
            return response()->json(['error' => 'Something went wrong'], 500);
        }
    }

    public function getFavoriteTasks()
    {
        try {
            $tasks = Auth::user()->favoriteTasks()->get();
            return response()->json($tasks, 200);
        } catch (Exception $e) {
            return response()->json(['error' => 'Something went wrong'], 500);
        }
    }

    public function addCategoriesToTask(Request $request, $taskId)
    {
        try {
            $task = Task::findOrFail($taskId);
            $task->categories()->attach($request->category_id);
            return response()->json('Category attached successfully', 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Task not found'], 404);
        } catch (QueryException $e) {
            return response()->json(['error' => 'Category not found or already attached'], 400);
        } catch (Exception $e) {
            return response()->json(['error' => 'Something went wrong'], 500);
        }
    }

    public function getTaskCategories($taskId)
    {
        try {
            $categories = Task::findOrFail($taskId)->categories;
            return response()->json($categories, 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Task not found'], 404);
        } catch (Exception $e) {
            return response()->json(['error' => 'Something went wrong'], 500);
        }
    }

    public function getCategorieTasks($categoriyId)
    {
        try {
            $tasks = Category::findOrFail($categoriyId)->tasks;
            return response()->json($tasks, 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Category not found'], 404);
        } catch (Exception $e) {
            return response()->json(['error' => 'Something went wrong'], 500);
        }
    }

    public function getTaskUser($id)
    {
        try {
            $user = Task::findOrFail($id)->user;
            return response()->json($user, 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Task not found'], 404);
        } catch (Exception $e) {
            return response()->json(['error' => 'Something went wrong'], 500);
        }
    }

    public function getTasksByPriority(Request $request)
    {
        try {
            $order = $request->input('order', 'desc');
            $priorityOrder = $order === 'asc' ? "FIELD(priority, 'low', 'medium', 'high')" : "FIELD(priority, 'high', 'medium', 'low')";
            $tasks = Auth::user()->tasks()->orderByRaw($priorityOrder)->get();
            return response()->json($tasks, 200);
        } catch (Exception $e) {
            return response()->json(['error' => 'Something went wrong'], 500);
        }
    }

    public function getAllTasks()
    {
        try {
            $tasks = Task::all();
            return response()->json($tasks, 200);
        } catch (Exception $e) {
            return response()->json(['error' => 'Something went wrong'], 500);
        }
    }

    public function index()
    {
        try {
            $tasks = Auth::user()->tasks;
            return response()->json($tasks, 200);
        } catch (Exception $e) {
            return response()->json(['error' => 'Something went wrong'], 500);
        }
    }

    public function store(StoreTaskRequest $request)
    {
        try {
            $user_id = Auth::user()->id;
            $validatedData = $request->validated();
            $validatedData['user_id'] = $user_id;
            $task = Task::create($validatedData);
            return response()->json($task, 201);
        } catch (Exception $e) {
            return response()->json(['error' => 'Something went wrong'], 500);
        }
    }

    public function update(UpdateTaskRequest $request, $id)
    {
        try {
            $user_id = Auth::user()->id;
            $task = Task::findOrFail($id);
            if ($task->user_id != $user_id)
                return response()->json(['message' => 'Unauthorized'], 403);

            $task->update($request->validated());
            return response()->json($task, 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Task not found'], 404);
        } catch (Exception $e) {
            return response()->json(['error' => 'Something went wrong'], 500);
        }
    }

    public function show($id)
    {
        try {
            $task = Task::findOrFail($id);
            return response()->json($task, 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Task not found'], 404);
        } catch (Exception $e) {
            return response()->json(['error' => 'Something went wrong'], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $task = Task::findOrFail($id);
            $task->delete();
            return response()->json('Task Deleted successfully', 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Task not found'], 404);
        } catch (Exception $e) {
            return response()->json(['error' => 'Something went wrong while deleting the task'], 500);
        }
    }
}
