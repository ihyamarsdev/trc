<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\RegistrationData;
use LaravelDaily\Invoices\Invoice;
use LaravelDaily\Invoices\Classes\Buyer;
use LaravelDaily\Invoices\Classes\InvoiceItem;
use Barryvdh\DomPDF\Facade\Pdf;

class DownloadPdfController extends Controller
{
    public function download(RegistrationData $record){

        $carbonDate = Carbon::parse($record->date_register);


        $pdf = Pdf::loadView('template.apps-rasyidu', [$record]);
        return $pdf->stream('invoice.pdf');

    }
}
