<?php

namespace App\Filament\Enum;

enum Program: string
{
    case ANBK = 'anbk';
    case APPS = 'apps';
    case SNBT = 'snbt';
    case TKA = 'tka';

    public function label(): string
    {
        return match ($this) {
            self::ANBK => 'ANBK',
            self::APPS => 'APPS',
            self::SNBT => 'SNBT',
            self::TKA => 'TKA',
        };
    }

    public static function list(): array
    {
        return [
            'anbk' => self::ANBK->label(),
            'apps' => self::APPS->label(),
            'snbt' => self::SNBT->label(),
            'tka' => self::TKA->label(),
        ];
    }

}
