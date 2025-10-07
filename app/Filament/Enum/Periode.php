<?php

namespace App\Filament\Enum;

enum Periode: string
{
    case PERIODE_1 = 'Periode 1';
    case PERIODE_2 = 'Periode 2';

    public function label(): string
    {
        return match($this) {
            self::PERIODE_1 => 'Periode 1',
            self::PERIODE_2 => 'Periode 2',
        };
    }

    public static function list(): array
    {
        return [
            'Periode 1' => self::PERIODE_1->label(),
            'Periode 2' => self::PERIODE_2->label(),
        ];
    }
}
