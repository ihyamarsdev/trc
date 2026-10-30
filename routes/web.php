<?php

use App\Filament\Components\Support\DocumentTemplate;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\SampleSalesforce;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('welcome');
// });

Route::post('/login', function (Request $request) {
    if ($request->expectsJson()) {
        return response()->json([
            'message' => 'Use the login page at /login.',
        ], 405);
    }

    return redirect('/login');
})->middleware('guest');

// # DOKUMEN (SPK, kwitansi, invoice) per perusahaan; template HTML ada di public/template/{perusahaan}/
foreach (DocumentTemplate::COMPANIES as $company) {
    foreach (['anbk', 'apps', 'snbt'] as $program) {
        Route::get("/{record}/{$company}/download/{$program}", [DocumentController::class, 'spk'])
            ->defaults('company', $company)
            ->name("{$company}.{$program}.download");
    }

    Route::get("/{record}/{$company}/download/kwitansi", [DocumentController::class, 'kwitansi'])
        ->defaults('company', $company)
        ->name("{$company}.kwitansi.download");

    Route::get("/{record}/{$company}/download/invoice", [DocumentController::class, 'invoice'])
        ->defaults('company', $company)
        ->name("{$company}.invoice.download");
}

Route::get('/download-sample-excel', [SampleSalesforce::class, 'download'])->name('download.sample.excel');
