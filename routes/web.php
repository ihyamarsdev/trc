<?php

use App\Http\Controllers\ANBK;
use App\Http\Controllers\APPS;
use App\Http\Controllers\SNBT;
use LaravelDaily\Invoices\Invoice;
use Illuminate\Support\Facades\Route;
use LaravelDaily\Invoices\Classes\Party;
use App\Http\Controllers\InvoiceGenerator;
use App\Http\Controllers\SampleSalesforce;
use LaravelDaily\Invoices\Classes\InvoiceItem;
use App\Http\Controllers\DownloadPdfController;
use App\Http\Controllers\Kwitansi;

// Route::get('/', function () {
//     return view('welcome');
// });

## RASYIDU

Route::get('/{record}/rasyidu/download/anbk', [ANBK::class, 'rasyidu'])
    ->name('rasyidu.anbk.download');

Route::get('/{record}/rasyidu/download/apps', [APPS::class, 'rasyidu'])
    ->name('rasyidu.apps.download');

Route::get('/{record}/rasyidu/download/snbt', [SNBT::class, 'rasyidu'])
    ->name('rasyidu.snbt.download');

Route::get('/{record}/rasyidu/download/kwitansi', [Kwitansi::class, 'rasyidu'])
    ->name('rasyidu.kwitansi.download');

Route::get('/{record}/rasyidu/download/invoice', [InvoiceGenerator::class, 'rasyidu'])
    ->name('rasyidu.invoice.download');


## EDUNESIA
Route::get('/{record}/edunesia/download/anbk', [ANBK::class, 'edunesia'])
    ->name('edunesia.anbk.download');

Route::get('/{record}/edunesia/download/apps', [APPS::class, 'edunesia'])
    ->name('edunesia.apps.download');

Route::get('/{record}/edunesia/download/snbt', [SNBT::class, 'edunesia'])
    ->name('edunesia.snbt.download');

Route::get('/{record}/edunesia/download/kwitansi', [Kwitansi::class, 'edunesia'])
    ->name('edunesia.kwitansi.download');

Route::get('/{record}/edunesia/download/invoice', [InvoiceGenerator::class, 'edunesia'])
    ->name('edunesia.invoice.download');

Route::get('/download-sample-excel', [SampleSalesforce::class, 'download'])->name('download.sample.excel');
