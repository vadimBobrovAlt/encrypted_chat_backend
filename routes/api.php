<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ChatController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'auth'], function () {
    Route::post('/login',[AuthController::class,'login']);
    Route::post('/twoFactor',[AuthController::class,'twoFactor'])->middleware('auth:sanctum');
    Route::post('/register',[AuthController::class,'register']);
    Route::get('/logout',[AuthController::class,'logout'])->middleware('auth:sanctum');
});

Route::middleware(['auth:sanctum'])->group(function () {
    Route::group(['prefix' => 'chat'], function () {
        Route::post('/add-chat',[ChatController::class,'createChat']);
        Route::post('/add-message',[ChatController::class,'addMessage']);
        Route::get('/message/{id}',[ChatController::class,'getMessageInChat']);
        Route::get('/users',[ChatController::class,'getUsers']);
        Route::get('/',[ChatController::class,'getChats']);
        Route::get('/chat/{id}',[ChatController::class,'getOne']);
        Route::get('/{id}',[ChatController::class,'logout']);
    });
});
