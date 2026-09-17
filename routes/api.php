<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\TicketController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware(['auth:sanctum', 'ensure.active', 'log.auth'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    Route::get('/tickets', [TicketController::class, 'index']);
    Route::post('/tickets', [TicketController::class, 'store']);
    Route::get('/tickets/{ticket}', [TicketController::class, 'show']);
    Route::put('/tickets/{ticket}', [TicketController::class, 'update']);
    Route::patch('/tickets/{ticket}', [TicketController::class, 'update']);

    Route::post('/tickets/{ticket}/assign', [TicketController::class, 'assign']);
    Route::post('/tickets/{ticket}/transition', [TicketController::class, 'transition']);

    Route::post('/tickets/{ticket}/comments', [CommentController::class, 'store']);

    Route::middleware('role:admin')->prefix('admin')->group(function () {
        Route::get('/ping', function () {
            return response()->json([
                'message' => 'Admin area OK.',
            ]);
        });
    });
});