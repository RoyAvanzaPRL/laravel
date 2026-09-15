<?php

use App\Http\Controllers\Api\AuthorController;
use App\Http\Controllers\Api\BookController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\LoanController;

Route::apiResource('authors', AuthorController::class);
Route::apiResource('books', BookController::class);
Route::get('loans', [LoanController::class, 'index'])->name('loans.index');
Route::post('loans/{loan}/return', [LoanController::class, 'returnLoan'])->name('loans.return');