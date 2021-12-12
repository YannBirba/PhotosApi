<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\GroupController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('register',[AuthController::class, 'register']);
Route::post('login',[AuthController::class, 'login']);
Route::get('isloggedin',[AuthController::class, 'isloggedin']);

Route::middleware('auth:sanctum')->group(function(){
    Route::get('user',[AuthController::class, 'user']);
    Route::get('user-events',[AuthController::class, 'events']);
    Route::post('logout',[AuthController::class, 'logout']);
    Route::apiResource('event',EventController::class);
    Route::apiResource('group',GroupController::class);
    Route::get('group-events/{id}',[GroupController::class, 'events']);
    Route::get('event-groups/{id}',[EventController::class, 'groups']);
    Route::get('group-users/{id}',[GroupController::class, 'users']);
    Route::get('eventyear',[EventController::class, 'indexactualyear']);
});