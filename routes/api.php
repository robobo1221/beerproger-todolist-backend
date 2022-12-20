<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TodolistController;

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

Route::get('/getList', [TodolistController::class, 'getList']);
Route::get('/getItemByName/{name}', [TodolistController::class, 'getItemByName']);
Route::get('/getItemById/{id}', [TodolistController::class, 'getItemById']);

Route::delete('/deleteItemById/{id}', [TodolistController::class, 'deleteItemById']);
Route::delete('/deleteItemByName/{name}', [TodolistController::class, 'deleteItemByName']);

Route::post('/updateItem', [TodolistController::class, 'updateItem']);
Route::post('/addItem', [TodolistController::class, 'addItem']);
