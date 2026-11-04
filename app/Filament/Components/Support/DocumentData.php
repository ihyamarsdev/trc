<?php

namespace App\Filament\Components\Support;

use App\Filament\Components\Format;
use App\Filament\Enum\OptionGroup;
use App\Models\Option;
use App\Models\RegistrationData;
use Illuminate\Support\Str;
use NumberFormatter;

/**
 * The values a record puts into the SPK, kuitansi and invoice templates. The PDF download and the
 * on-screen preview both read from here, so what is previewed is what gets printed.
 */
class DocumentData
{
    /**
     * What an invoice's description can hold before it runs into the rows below it; longer than
     * LONG_DETAIL is printed in a smaller font.
     */
    public const MAX_DETAIL = 90;

    public const LONG_DETAIL = 40;

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
            'tanggal_pendek' => ($record->payment_date ?? now())->format('d / m / Y'),
            'number' => $record->number_kwitansi ?? '',
            'qty' => $record->qty_invoice ?: $record->real_count ?: $record->student_count,
            'total' => number_format($total, 0, ',', '.'),
            'totalSpell' => Str::title(Format::formatKeRupiah($total)),
        ];
    }

    /**
     * @return array<string, scalar|null>
     */
    public static function invoice(RegistrationData $record): array
    {
        return [
            'detail' => $record->detail_invoice ?? '-',
            'detail_size' => Str::length((string) $record->detail_invoice) > self::LONG_DETAIL ? 'small' : 'normal',
            'schools' => $record->schools ?? '-',
            'qty' => $record->qty_invoice ?? '0',
            'tanggal' => now()->format('d/m/Y'),
            'number_invoice' => $record->number_invoice ?? '-',
            'price' => self::money($record->unit_price),
            'amount' => self::money($record->amount_invoice),
            'subtotal_invoice' => self::money($record->subtotal_invoice),
            'total_invoice' => self::money($record->total_invoice),
            'pph' => self::percent($record->pph),
            'ppn' => self::percent($record->ppn),
            'pph_label' => self::taxLabel('PPH 23', $record->pph),
            'pph_amount' => self::taxAmount($record->subtotal_invoice, $record->pph, none: '-'),
            'ppn_label' => self::taxLabel('PPN', $record->ppn),
            'ppn_amount' => self::taxAmount($record->subtotal_invoice, $record->ppn, none: '-'),
            'pph_rupiah' => self::taxAmount($record->subtotal_invoice, $record->pph, 'Rp '),
            'ppn_rupiah' => self::taxAmount($record->subtotal_invoice, $record->ppn, 'Rp '),
            'program' => filled($record->type) ? 'Program '.Str::upper($record->type) : '-',
        ];
    }

    /**
     * @return array<string, scalar|null>
     */
    public static function for(string $document, RegistrationData $record): array
    {
        return match (DocumentTemplate::kind($document)) {
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

    /**
     * `PPN (11%)`; empty when the tax is not applied, so its row on the invoice stays blank.
     */
    private static function taxLabel(string $name, mixed $percent): string
    {
        return (float) $percent > 0 ? "{$name} (".self::percent($percent).')' : '';
    }

    /**
     * The tax in rupiah on the subtotal; `$none` (empty unless given) when the tax is not applied.
     */
    private static function taxAmount(mixed $subtotal, mixed $percent, string $prefix = '', string $none = ''): string
    {
        return (float) $percent > 0 ? $prefix.self::money((float) $subtotal * (float) $percent / 100) : $none;
    }
}
