<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\TaskController;
use App\Http\Controllers\api\ProjectController;
use App\Http\Controllers\api\UserController;

Route::get('users', [UserController::class, 'index']);
Route::get('users/{user}', [UserController::class, 'show']);
Route::put('users/{user}', [UserController::class, 'update']);
Route::patch('users/{user}/password', [UserController::class, 'update_password']);

Route::get('users/{user}/tasks', [TaskController::class, 'getTasksOfUser']);
Route::get('tasks/{task}', [TaskController::class, 'show']);
Route::post('tasks', [TaskController::class, 'store']);
Route::delete('tasks/{task}', [TaskController::class, 'destroy']);
Route::put('tasks/{task}', [TaskController::class, 'update']);
Route::patch('tasks/{task}/completed', [TaskController::class, 'update_completed']);

Route::get('projects', [ProjectController::class, 'index']);
Route::get('projects/{project}', [ProjectController::class, 'show']);
Route::get('projects/{project}/tasks', [ProjectController::class, 'showWithTasks']);
Route::post('projects', [ProjectController::class, 'store']);
Route::delete('projects/{project}', [ProjectController::class, 'destroy']);
Route::put('projects/{project}', [ProjectController::class, 'update']);
Route::get('users/{user}/projects', [ProjectController::class, 'getProjectsOfUser']);
Route::get('users/{user}/projects/inprogress', [ProjectController::class, 'getProjectsInProgressOfUser']);
