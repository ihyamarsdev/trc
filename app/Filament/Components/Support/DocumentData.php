<?php

namespace App\Filament\Components\Support;

use App\Filament\Components\Format;
use App\Filament\Enum\OptionGroup;
use App\Models\Option;
use App\Models\RegistrationData;
use NumberFormatter;

/**
 * The values a record puts into the SPK, kuitansi and invoice templates. The PDF download and the
 * on-screen preview both read from here, so what is previewed is what gets printed.
 */
class DocumentData
{
    /**
     * @return array<string, scalar|null>
     */
    public static function spk(RegistrationData $record): array
    {
        $digit = new NumberFormatter('id', NumberFormatter::SPELLOUT);
        $price = self::wholeNumber($record->price);
        $total = self::wholeNumber($record->total);

        return [
            'deskripsi' => Format::formatTanggal($record->date_register->format('Y-m-d')),
            'year' => $record->years,
            'tanggal' => Format::tanggal($record->date_register->format('Y-m-d')),
            'to' => $record->principal,
            'jabatan' => 'KEPALA SEKOLAH '.$record->schools,
            'siswa' => $record->student_count,
            'siswaSpell' => $digit->format((int) $record->student_count),
            'harga' => number_format($price, 0, ',', '.'),
            'hargaSpell' => Format::formatKeRupiah($price),
            'total' => number_format($total, 0, ',', '.'),
            'totalSpell' => Format::formatKeRupiah($total),
            'payment' => Option::label(OptionGroup::PaymentVia, $record->payment_name) ?? $record->payment_name,
            'province' => $record->provinces,
        ];
    }

    /**
     * @return array<string, scalar|null>
     */
    public static function kwitansi(RegistrationData $record): array
    {
        $total = self::wholeNumber($record->total);

        return [
            'schools' => $record->schools,
            'detail' => $record->detail_kwitansi,
            'principal' => $record->principal,
            'tanggal' => Format::tanggal($record->payment_date),
            'total' => number_format($total, 0, ',', '.'),
            'totalSpell' => Format::formatKeRupiah($total),
        ];
    }

    /**
     * @return array<string, scalar|null>
     */
    public static function invoice(RegistrationData $record): array
    {
        return [
            'detail' => $record->detail_invoice ?? '-',
            'schools' => $record->schools ?? '-',
            'qty' => $record->qty_invoice ?? '0',
            'tanggal' => $record->date_register->format('d/m/Y'),
            'number_invoice' => $record->number_invoice ?? '-',
            'price' => self::money($record->unit_price),
            'amount' => self::money($record->amount_invoice),
            'subtotal_invoice' => self::money($record->subtotal_invoice),
            'total_invoice' => self::money($record->total_invoice),
            'pph' => self::percent($record->pph),
            'ppn' => self::percent($record->ppn),
        ];
    }

    /**
     * @return array<string, scalar|null>
     */
    public static function for(string $document, RegistrationData $record): array
    {
        return match ($document) {
            'spk' => self::spk($record),
            'kuitansi' => self::kwitansi($record),
            'invoice' => self::invoice($record),
        };
    }

    private static function wholeNumber(mixed $value): int
    {
        return (int) round((float) $value);
    }

    private static function money(mixed $value): string
    {
        return number_format((float) $value, 0, ',', '.');
    }

    /**
     * 11 and 0.5 become `11%` and `0,5%`; an empty or zero tax is shown as a dash.
     */
    private static function percent(mixed $value): string
    {
        $percent = (float) $value;

        if ($percent <= 0) {
            return '-';
        }

        return rtrim(rtrim(number_format($percent, 2, ',', ''), '0'), ',').'%';
    }
}
