<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\ImageController;
use App\Http\Middleware\IsAdmin;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;

Route::controller(AuthController::class)->group(function () {
    Route::post('register', 'register');
    Route::post('login', 'login');
});

Route::middleware('auth:sanctum')->group(function () {
    Route::controller(AuthController::class)->group(function () {
        Route::get('user', 'user');
        Route::post('logout', 'logout');
        Route::put('user/updatecurrent', 'updatecurrent');
    });

    Route::middleware(IsAdmin::class)->group(function () {
        Route::controller(AuthController::class)->group(function () {
            Route::get('userlist', 'index');
            Route::get('/user/{user}', 'show');
            Route::put('user/{user}', 'update');
        });

        Route::controller(GroupController::class)->group(function () {
            Route::get('/group', 'index');
            Route::get('/group/{group}', 'show');
            Route::get('/group/{group}/events', 'events');
            Route::get('/group/{group}/users', 'users');
            Route::post('/event/{event}/group', 'group');
            Route::post('/group/{group}/event', 'event');
            Route::post('/group', 'store');
            Route::put('/group/{group}', 'update');
            Route::delete('/group/{group}', 'destroy');
        });

        Route::controller(EventController::class)->group(function () {
            Route::get('/event', 'index');
            Route::get('/event/{event}', 'show');
            Route::get('/event/{event}/groups', 'groups');
            Route::get('/event/{event}/images', 'images');
            Route::get('/event/{event}/image', 'image');
            Route::get('/event/usergroupindex', 'usergroupindex');
            Route::post('/event', 'store');
            Route::put('/event/{event}', 'update');
            Route::delete('/event/{event}', 'destroy');
            Route::delete('/user/{user}', 'destroy');
        });

        Route::controller(ImageController::class)->group(function () {
            Route::get('/image', 'index');
            Route::get('/image/{image}', 'show');
            Route::get('/image/file/{image}', 'file');
            Route::get('/image/download/{image}', 'download');
            Route::post('/image', 'store');
            Route::put('/image/{image}', 'update');
            Route::delete('/image/{image}', 'destroy');
        });
    });
});

Route::fallback(function () {
    return response()->json(['error' => 'Ressource non trouvée'], Response::HTTP_NOT_FOUND);
});
