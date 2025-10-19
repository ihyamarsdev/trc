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
        return match($this) {
            self::ANBK => 'ANBK',
            self::APPS => 'APPS',
            self::SNBT => 'SNBT',
            self::TKA => 'TKA',
        };
    }

    public static function list(): array
    {
        return [
            'ANBK' => self::ANBK->label(),
            'APPS' => self::APPS->label(),
            'SNBT' => self::SNBT->label(),
            'TKA' => self::TKA->label(),
        ];
    }
    
}
