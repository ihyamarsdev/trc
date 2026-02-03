<?php

namespace App\Filament\Enum;

enum Periode: string
{
    case PERIODE_1 = 'PERIODE 1';
    case PERIODE_2 = 'PERIODE 2';

    public function label(): string
    {
        return match($this) {
            self::PERIODE_1 => 'PERIODE 1',
            self::PERIODE_2 => 'PERIODE 2',
        };
    }

    public static function list(): array
    {
        return [
            'PERIODE 1' => self::PERIODE_1->label(),
            'PERIODE 2' => self::PERIODE_2->label(),
        ];
    }
}
