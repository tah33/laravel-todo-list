<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TaskController;
use App\Http\Controllers\Api\TodoListController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::get('login', [AuthController::class, 'showLoginError'])->name('login');
Route::post('login', [AuthController::class, 'login']);
Route::post('register', [AuthController::class, 'register']);
Route::get('logout', [AuthController::class, 'logout'])->middleware('auth:api');
//to do list
Route::resource('todo-lists', TodoListController::class)->except(['create','edit','update']);
Route::post('todo-lists/{id}', [TodoListController::class, 'update'])->name('todo-lists.update');

//task
Route::resource('tasks', TaskController::class)->except(['create','edit','index','update']);
Route::post('tasks/{id}', [TaskController::class, 'update'])->name('tasks.update');

