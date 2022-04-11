<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\ImageController;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Route;

Route::post('register',[AuthController::class, 'register']);
Route::post('login',[AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function(){
    Route::middleware('isAdmin')->group(function(){

    });
    Route::get('user',[AuthController::class, 'user']);
    Route::get('user/{user_id}/events',[AuthController::class, 'events']);
    Route::post('logout',[AuthController::class, 'logout']);
    Route::get('userlist',[AuthController::class, 'index']);
    Route::put('user/updatecurrent',[AuthController::class, 'updatecurrent']);
    Route::put('user/{user_id}',[AuthController::class, 'update']);

    Route::get('/event/{event_id}/groups',[EventController::class, 'groups']);
    Route::get('/event/{event_id}/images',[EventController::class, 'images']);
    Route::get('/event/{event_id}/image',[EventController::class, 'image']);
    Route::get('/event/usergroupindex',[EventController::class, 'usergroupindex']);
    Route::post('/event/{event_id}/group',[GroupController::class, 'group']);
    Route::get('/group/{group_id}/events',[GroupController::class, 'events']);
    Route::get('/group/{group_id}/users',[GroupController::class, 'users']);
    Route::post('/group/{group_id}/event',[GroupController::class, 'event']);


    Route::get('/image/event',[ImageController::class, 'event']);
    Route::get('/image/{image_id}/file',[ImageController::class, 'file']);

    Route::get('/event',[EventController::class, 'index']);
    Route::put('/event/{event}',[EventController::class, 'update']);
    Route::post('/event',[EventController::class, 'store']);
    Route::delete('/event/{event}',[EventController::class, 'destroy']);


    Route::get('/group',[GroupController::class, 'index']);
    Route::put('/group/{group}',[GroupController::class, 'update']);
    Route::post('/group',[GroupController::class, 'store']);
    Route::delete('/group/{group}',[GroupController::class, 'destroy']);

    Route::get('/image',[ImageController::class, 'index']);
    Route::put('/image/{image}',[ImageController::class, 'update']);
    Route::post('/image',[ImageController::class, 'store']);
    Route::delete('/image/{image}',[ImageController::class, 'destroy']);
});

Route::fallback(function(){
    return response()->json(['error' => 'Non autorisé'], Response::HTTP_UNAUTHORIZED);
});