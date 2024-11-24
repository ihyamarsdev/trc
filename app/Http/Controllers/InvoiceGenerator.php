<?php

namespace App\Http\Controllers;

use NumberFormatter;
use App\Models\SchoolYear;
use Illuminate\Http\Request;
use App\Models\RegistrationData;
use PhpOffice\PhpWord\TemplateProcessor;
use LaravelDaily\Invoices\Invoice;
use LaravelDaily\Invoices\Classes\Buyer;
use LaravelDaily\Invoices\Classes\InvoiceItem;

class InvoiceGenerator extends Controller
{
    public function rasyidu_invoice(RegistrationData $record)
    {
        $digit = new NumberFormatter("id", NumberFormatter::SPELLOUT);


        $school_year = SchoolYear::where('id', '=', $record->school_years_id)->first();

        $templateProcessor = new TemplateProcessor('template/rasyidu/invoice.docx');

        $templateProcessor->setValues([
            'deskripsi' => '',
            'schools' => $record->schools,
            'year' => $school_year->name,
            'tanggal' => $record->date_register->format('F d, Y'),
            'harga' => number_format($record->price, 0, ',', '.'),
            'total_invoice' => number_format($record->total_invoice, 0, ',', '.'),
            'payment' => $record->payment,
            'province' => $record->provinces,
        ]);

        $doc_name = 'INVOICE RASYIDUU ANBK ' . $record->schools . '.docx';

        $templateProcessor->saveAs($doc_name);

        return response()->download(public_path($doc_name))->deleteFileAfterSend(true);
    }

    public function edunesia_invoice(RegistrationData $record)
    {
        $digit = new NumberFormatter("id", NumberFormatter::SPELLOUT);


        $school_year = SchoolYear::where('id', '=', $record->school_years_id)->first();

        $templateProcessor = new TemplateProcessor('template/rasyidu/invoice.docx');

        $templateProcessor->setValues([
            'deskripsi' => '',
            'schools' => $record->schools,
            'year' => $school_year->name,
            'tanggal' => $record->date_register->format('F d, Y'),
            'harga' => number_format($record->price, 0, ',', '.'),
            'total_invoice' => number_format($record->total_invoice, 0, ',', '.'),
            'payment' => $record->payment,
            'province' => $record->provinces,
        ]);

        $doc_name = 'INVOICE EDUNESIA ANBK ' . $record->schools . '.docx';

        $templateProcessor->saveAs($doc_name);

        return response()->download(public_path($doc_name))->deleteFileAfterSend(true);
    }

}
