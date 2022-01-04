<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\ImageController;

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
    Route::apiResource('image',ImageController::class);
    Route::get('/group/events/{group_id}',[GroupController::class, 'events']);
    Route::get('/event/groups/{event_id}',[EventController::class, 'groups']);
    Route::get('/event/images/{event_id}',[EventController::class, 'images']);
    Route::get('/event/image/{event_id}',[EventController::class, 'image']);
    Route::get('/group/users/{group_id}',[GroupController::class, 'users']);
    Route::get('/event/actualyear',[EventController::class, 'indexactualyear']);
    Route::post('/group/event/{group_id}',[GroupController::class, 'event']);
    Route::post('/event/group/{event_id}',[GroupController::class, 'group']);
    Route::get('/image/event',[ImageController::class, 'event']);
});