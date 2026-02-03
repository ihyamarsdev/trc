<?php

namespace App\Filament\Enum;

enum Jenjang: string
{
    case SD  = 'SD';
    case MI  = 'MI';
    case SMP = 'SMP';
    case MTS = 'MTS';
    case SMA = 'SMA';
    case MA  = 'MA';
    case SMK = 'SMK';

    public function label(): string
    {
        return match ($this) {
            self::SD  => 'SD',
            self::MI  => 'MI',
            self::SMP => 'SMP',
            self::MTS => 'MTS',
            self::SMA => 'SMA',
            self::MA  => 'MA',
            self::SMK => 'SMK',
        };
    }

    public static function list(): array
    {
        return [
            'SD'  => self::SD->label(),
            'MI'  => self::MI->label(),
            'SMP' => self::SMP->label(),
            'MTS' => self::MTS->label(),
            'SMA' => self::SMA->label(),
            'MA'  => self::MA->label(),
            'SMK' => self::SMK->label(),
        ];
    }
}
