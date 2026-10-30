<?php

namespace App\Filament\Enum;

/**
 * The master-data lists whose choices are managed from the admin panel (`options` table).
 * Each case is a list the application code reads; the choices inside are data.
 */
enum OptionGroup: string
{
    case Program = 'program';
    case Periode = 'periode';
    case Jenjang = 'jenjang';
    case Keterangan = 'keterangan';
    case SchoolType = 'school_type';
    case PaymentVia = 'payment_via';
    case Bank = 'bank';
    case Tax = 'tax';

    public function label(): string
    {
        return match ($this) {
            self::Program => 'Program',
            self::Periode => 'Periode',
            self::Jenjang => 'Jenjang',
            self::Keterangan => 'Keterangan',
            self::SchoolType => 'Negeri / Swasta',
            self::PaymentVia => 'Pembayaran Via',
            self::Bank => 'Bank',
            self::Tax => 'Pajak',
        };
    }

    /**
     * Column on `registration_data` that stores the chosen value of this list.
     */
    public function column(): string
    {
        return match ($this) {
            self::Program => 'type',
            self::Periode => 'periode',
            self::Jenjang => 'education_level',
            self::Keterangan => 'description',
            self::SchoolType => 'schools_type',
            self::PaymentVia => 'payment_name',
            self::Bank => 'bank_name',
            self::Tax => 'tax_rates',
        };
    }

    /**
     * Programs carry a longer description shown in the page header.
     */
    public function hasDescription(): bool
    {
        return $this === self::Program;
    }

    /**
     * Tax choices are percentages, so the stored value has to be a number.
     */
    public function isNumeric(): bool
    {
        return $this === self::Tax;
    }
}
