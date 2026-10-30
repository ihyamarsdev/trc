<?php

namespace App\Http\Controllers;

use App\Filament\Components\Support\DocumentData;
use App\Filament\Components\Support\DocumentTemplate;
use App\Models\RegistrationData;
use Filament\Notifications\Notification;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

/**
 * SPK, kuitansi and invoice downloads. Each is an HTML template in `public/template/{company}/`
 * filled with the record's values (see DocumentData) and delivered as a PDF.
 */
class DocumentController extends Controller
{
    private const COMPANY_NAMES = [
        'rasyidu' => 'RASYIDUU',
        'edunesia' => 'EDUNESIA',
    ];

    public function spk(RegistrationData $record, string $company): Response
    {
        return $this->deliver($record, $company, 'spk', 'SPK');
    }

    public function kwitansi(RegistrationData $record, string $company): Response
    {
        return $this->deliver($record, $company, 'kuitansi', 'KWITANSI');
    }

    public function invoice(RegistrationData $record, string $company): Response
    {
        return $this->deliver($record, $company, 'invoice', 'INVOICE');
    }

    private function deliver(RegistrationData $record, string $company, string $document, string $label): Response
    {
        $user = Auth::user() ?? abort(403);

        $pdf = DocumentTemplate::pdf($company, $document, DocumentData::for($document, $record));

        $user->notify(
            Notification::make()
                ->title(Str::title($label).' Berhasil di Download')
                ->icon('heroicon-o-document-text')
                ->success()
                ->toDatabase(),
        );

        return $pdf->download($this->fileName($label, $company, $record));
    }

    /**
     * `SPK RASYIDUU ANBK SMAN 1 Contoh.pdf`: the school name may hold characters a file name cannot.
     */
    private function fileName(string $label, string $company, RegistrationData $record): string
    {
        $name = implode(' ', [$label, self::COMPANY_NAMES[$company] ?? Str::upper($company), Str::upper((string) $record->type), $record->schools]);

        return Str::of($name)->replaceMatches('/[\\\\\/:*?"<>|]+/', ' ')->squish()->append('.pdf')->toString();
    }
}
