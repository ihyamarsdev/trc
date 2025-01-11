<?php

use App\Http\Controllers\DownloadPdfController;
use App\Http\Controllers\InvoiceGenerator;
use App\Http\Controllers\SampleSalesforce;
use Illuminate\Support\Facades\Route;
use LaravelDaily\Invoices\Invoice;
use LaravelDaily\Invoices\Classes\InvoiceItem;
use LaravelDaily\Invoices\Classes\Party;

// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/{record}/rasyidu/download/anbk', [DownloadPdfController::class, 'anbk_rasyidu'])
    ->name('rasyidu.anbk.download');

Route::get('/{record}/rasyidu/download/apps', [DownloadPdfController::class, 'apps_rasyidu'])
    ->name('rasyidu.apps.download');

Route::get('/{record}/rasyidu/download/snbt', [DownloadPdfController::class, 'snbt_rasyidu'])
    ->name('rasyidu.snbt.download');

Route::get('/{record}/rasyidu/download/kwitansi', [DownloadPdfController::class, 'kwitansi_rasyidu'])
    ->name('rasyidu.kwitansi.download');

Route::get('/{record}/rasyidu/download/invoice', [InvoiceGenerator::class, 'rasyidu_invoice'])
    ->name('rasyidu.invoice.download');


Route::get('/{record}/edunesia/download/anbk', [DownloadPdfController::class, 'anbk_edunesia'])
    ->name('edunesia.anbk.download');

Route::get('/{record}/edunesia/download/apps', [DownloadPdfController::class, 'apps_edunesia'])
    ->name('edunesia.apps.download');

Route::get('/{record}/edunesia/download/snbt', [DownloadPdfController::class, 'snbt_edunesia'])
    ->name('edunesia.snbt.download');

Route::get('/{record}/edunesia/download/kwitansi', [DownloadPdfController::class, 'kwitansi_edunesia'])
    ->name('edunesia.kwitansi.download');

Route::get('/{record}/edunesia/download/invoice', [InvoiceGenerator::class, 'edunesia_invoice'])
    ->name('edunesia.invoice.download');

Route::get('/download-sample-excel', [SampleSalesforce::class, 'download'])->name('download.sample.excel');
