<?php

use App\Http\Controllers\Api\TicketHistoryPdfController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/tickets/{ticket}/history-pdf/file', [TicketHistoryPdfController::class, 'download'])
    ->name('tickets.history-pdf.download')
    ->middleware('signed');