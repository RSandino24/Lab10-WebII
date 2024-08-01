<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TaskController;

// Endpoint público para listar tareas con su id y nombre
Route::get('/tasks', [TaskController::class, 'index']);

// Endpoint para obtener una tarea específica por su id
Route::get('/tasks/{id}', [TaskController::class, 'show']);

// Endpoints privados para manejar las tareas del usuario
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user-tasks', [TaskController::class, 'getUserTasks']);
    Route::put('/tasks/{id}', [TaskController::class, 'update']);
    Route::delete('/tasks/{id}', [TaskController::class, 'destroy']);
    Route::post('/tasks/{id}/complete', [TaskController::class, 'complete']);
});
