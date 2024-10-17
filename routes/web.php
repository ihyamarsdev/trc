<?php

use App\Http\Controllers\DownloadPdfController;
use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     // return view('welcome');
//     return view('welcome');
// });

Route::get('/{record}/pdf/downloads', [DownloadPdfController::class, 'download'])
    ->name('finance.invoice.download');
