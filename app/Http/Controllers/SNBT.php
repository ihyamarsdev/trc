<?php

namespace App\Http\Controllers;

use NumberFormatter;
use Illuminate\Http\Request;
use App\Models\RegistrationData;
use App\Filament\Components\Format;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;
use PhpOffice\PhpWord\TemplateProcessor;

class SNBT extends Controller
{
    public function rasyidu(RegistrationData $record)
    {

        $digit = new NumberFormatter("id", NumberFormatter::SPELLOUT);

        $templateProcessor = new TemplateProcessor('template/rasyidu/spk.docx');

        $templateProcessor->setValues([
            'deskripsi' => Format::formatTanggal($record->date_register->format('Y-m-d')),
            'year' => $record->years,
            'tanggal' => Format::tanggal($record->date_register->format('Y-m-d')),
            'to' => $record->principal,
            'jabatan' => 'KEPALA SEKOLAH ' . $record->schools,
            'siswa' => $record->student_count,
            'siswaSpell' => $digit->format($record->student_count),
            'harga' => number_format($record->price, 0, ',', '.'),
            'hargaSpell' => Format::formatKeRupiah($record->price),
            'total' => number_format($record->total, 0, ',', '.'),
            'totalSpell' => Format::formatKeRupiah($record->total),
            'payment' => $record->payment,
            'province' => $record->provinces,
        ]);

        $doc_name = 'SPK RASYIDUU SNBT ' . $record->schools . '.docx';

        $recipient = Auth::user();

        $recipient->notify(
            Notification::make()
                ->title('SPK Berhasil di Download')
                ->icon('heroicon-o-document-text')
                ->success()
                ->toDatabase(),
        );

        $templateProcessor->saveAs($doc_name);

        return response()->download(public_path($doc_name))->deleteFileAfterSend(true);
    }

    public function edunesia(RegistrationData $record)
    {

        $digit = new NumberFormatter("id", NumberFormatter::SPELLOUT);


        $templateProcessor = new TemplateProcessor('template/edunesia/spk.docx');

        $templateProcessor->setValues([
            'deskripsi' => Format::formatTanggal($record->date_register->format('Y-m-d')),
            'year' => $record->years,
            'tanggal' => Format::tanggal($record->date_register->format('Y-m-d')),
            'to' => $record->principal,
            'jabatan' => 'KEPALA SEKOLAH ' . $record->schools,
            'siswa' => $record->student_count,
            'siswaSpell' => $digit->format($record->student_count),
            'harga' => number_format($record->price, 0, ',', '.'),
            'hargaSpell' => Format::formatKeRupiah($record->price),
            'total' => number_format($record->total, 0, ',', '.'),
            'totalSpell' => Format::formatKeRupiah($record->total),
            'payment' => $record->payment,
            'province' => $record->provinces,
        ]);

        $doc_name = 'SPK EDUNESIA APPS ' . $record->schools . '.docx';

        $recipient = Auth::user();

        $recipient->notify(
            Notification::make()
                ->title('SPK Berhasil di Download')
                ->icon('heroicon-o-document-text')
                ->success()
                ->toDatabase(),
        );

        $templateProcessor->saveAs($doc_name);

        return response()->download(public_path($doc_name))->deleteFileAfterSend(true);
    }
}
