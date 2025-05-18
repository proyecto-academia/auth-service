<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', [
    'uses' => function () {
        return response()->json([
            'message' => 'Welcome to the API',
            'status' => 200,
            'url' => request()->url(),
            'path' => request()->path(),
        ]);
    },
]);


Route::post('/register', [\App\Http\Controllers\AuthController::class, 'register']);
Route::post('/login', [\App\Http\Controllers\AuthController::class, 'login']);
Route::post('/logout', [\App\Http\Controllers\AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::get('/me', [\App\Http\Controllers\AuthController::class, 'me'])->middleware('auth:sanctum');
Route::post('/refresh', [\App\Http\Controllers\AuthController::class, 'refresh'])->middleware('auth:sanctum');



Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [UserController::class, 'show']);
    Route::put('/user', [UserController::class, 'update']);
    Route::post('/user/avatar', [UserController::class, 'updateAvatar']);
});


// not found route
Route::fallback(function () {
    return response()->json([
        'message' => 'Not Found',
        'status' => 404,
        'url' => request()->url(),
        'path' => request()->path(),
    ], 404);
});
