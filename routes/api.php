<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\ImageController;
use App\Http\Middleware\IsAdmin;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Route;

Route::post('register',[AuthController::class, 'register']);
Route::post('login',[AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function(){

    Route::get('user',[AuthController::class, 'user']);
    Route::post('logout',[AuthController::class, 'logout']);
    Route::put('user/updatecurrent',[AuthController::class, 'updatecurrent']);

    Route::middleware(IsAdmin::class)->group(function(){

        Route::put('user/{user}',[AuthController::class, 'update']);
        Route::delete('/user/{user}',[EventController::class, 'destroy']);

        Route::post('/event/{event}/group',[GroupController::class, 'group']);
        Route::post('/group/{group}/event',[GroupController::class, 'event']);

        Route::put('/event/{event}',[EventController::class, 'update']);
        Route::post('/event',[EventController::class, 'store']);
        Route::delete('/event/{event}',[EventController::class, 'destroy']);

        Route::put('/group/{group}',[GroupController::class, 'update']);
        Route::post('/group',[GroupController::class, 'store']);
        Route::delete('/group/{group}',[GroupController::class, 'destroy']);

        Route::put('/image/{image}',[ImageController::class, 'update']);
        Route::post('/image',[ImageController::class, 'store']);
        Route::delete('/image/{image}',[ImageController::class, 'destroy']);



        Route::get('userlist',[AuthController::class, 'index']);
        Route::get('/user/{user}',[EventController::class, 'show']);

        Route::get('/event/{event}/groups',[EventController::class, 'groups']);
        Route::get('/event/{event}/images',[EventController::class, 'images']);
        Route::get('/event/{event}/image',[EventController::class, 'image']);
        Route::get('/event/usergroupindex',[EventController::class, 'usergroupindex']);
        Route::get('/group/{group}/events',[GroupController::class, 'events']);
        Route::get('/group/{group}/users',[GroupController::class, 'users']);

        Route::get('/image/file/{image}',[ImageController::class, 'file']);
        Route::get('/image/download/{image}',[ImageController::class, 'download']);

        Route::get('/event',[EventController::class, 'index']);
        Route::get('/event/{event}',[EventController::class, 'show']);

        Route::get('/group',[GroupController::class, 'index']);
        Route::get('/group/{group}',[GroupController::class, 'show']);

        Route::get('/image',[ImageController::class, 'index']);
        Route::get('/image/{image}',[ImageController::class, 'show']);

    });
});

Route::fallback(function(){
    return response()->json(['error' => 'Ressource non trouvée'], Response::HTTP_NOT_FOUND);
});
