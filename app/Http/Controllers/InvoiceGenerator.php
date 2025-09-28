<?php

namespace App\Http\Controllers;

use NumberFormatter;
use Illuminate\Http\Request;
use App\Models\RegistrationData;
use LaravelDaily\Invoices\Invoice;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;
use LaravelDaily\Invoices\Classes\Buyer;
use PhpOffice\PhpWord\TemplateProcessor;
use LaravelDaily\Invoices\Classes\InvoiceItem;

class InvoiceGenerator extends Controller
{
    public function rasyidu(RegistrationData $record)
    {
        $pph = $record->tax_rate;
        $ppn = $record->sales_tsx;

        $templateProcessor = new TemplateProcessor('template/rasyidu/invoice.docx');

        if ($pph == 0) {
            $pph = "-";
            $ppn = $record->sales_tsx . "%";
        }

        if ($ppn == 0) {
            $ppn = "-";
            $pph = $record->tax_rate . "%";
        }

        $templateProcessor->setValues([
            'detail' => $record->detail_invoice ?? '-',
            'schools' => $record->schools ?? '-',
            'qty' => $record->qty_invoice ?? '0',
            'tanggal' => $record->date_register->format('d/m/Y') ?? '-/-/-',
            'price' => number_format($record->unit_price, 0, ',', '.') ?? '0',
            'amount' => number_format($record->amount_invoice, 0, ',', '.') ?? '0',
            'total_invoice' => number_format($record->total_invoice, 0, ',', '.') ?? '0',
            'subtotal' => number_format($record->subtotal_invoice, 0, ',', '.') ?? '0',
            'number' => $record->number_invoice ?? '-',
            'pph' => $pph ?? '-',
            'ppn' => $ppn ?? '-',
        ]);

        $doc_name = 'INVOICE RASYIDUU ANBK ' . $record->schools . '.docx';

        $recipient = Auth::user();

        $recipient->notify(
            Notification::make()
                ->title('Invoice Berhasil di Download')
                ->icon('heroicon-o-document-text')
                ->success()
                ->toDatabase(),
        );

        $templateProcessor->saveAs($doc_name);

        return response()->download(public_path($doc_name))->deleteFileAfterSend(true);
    }

    public function edunesia(RegistrationData $record)
    {
        $pph = $record->tax_rate;
        $ppn = $record->sales_tsx;

        $templateProcessor = new TemplateProcessor('template/edunesia/invoice.docx');

        if ($pph == 0) {
            $pph = "-";
            $ppn = $record->sales_tsx . "%";
        }

        if ($ppn == 0) {
            $ppn = "-";
            $pph = $record->tax_rate . "%";
        }

        $templateProcessor->setValues([
            'detail' => $record->detail_invoice ?? '-',
            'schools' => $record->schools ?? '-',
            'qty' => $record->qty_invoice ?? '0',
            'tanggal' => $record->date_register->format('d/m/Y') ?? '-/-/-',
            'price' => number_format($record->unit_price, 0, ',', '.') ?? '0',
            'amount' => number_format($record->amount_invoice, 0, ',', '.') ?? '0',
            'total_invoice' => number_format($record->total_invoice, 0, ',', '.') ?? '0',
            'subtotal' => number_format($record->subtotal_invoice, 0, ',', '.') ?? '0',
            'number' => $record->number_invoice ?? '-',
            'pph' => $pph ?? '-',
            'ppn' => $ppn ?? '-',
        ]);

        $doc_name = 'INVOICE EDUNESIA ANBK ' . $record->schools . '.docx';


        $recipient = Auth::user();

        $recipient->notify(
            Notification::make()
                ->title('Invoice Berhasil di Download')
                ->icon('heroicon-o-document-text')
                ->success()
                ->toDatabase(),
        );


        $templateProcessor->saveAs($doc_name);

        return response()->download(public_path($doc_name))->deleteFileAfterSend(true);
    }

}
