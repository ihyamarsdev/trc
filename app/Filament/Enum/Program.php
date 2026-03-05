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

    public function description(): string
    {
        return match ($this) {
            self::ANBK => 'ASESMEN NASIONAL BERBASIS KOMPUTER',
            self::APPS => 'ASESMEN PSIKOTES POTENSI SISWA',
            self::SNBT => 'SELEKSI NASIONAL BERDASARKAN TES',
            self::TKA => 'TEST KEMAMPUAN AKADEMIK',
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

    public static function meta(?string $value, bool $allowNone = false): array
    {
        $program = self::tryFrom(strtolower((string) $value));

        if ($program instanceof self) {
            return [
                'nameRegister' => $program->label(),
                'DescriptionRegister' => $program->description(),
            ];
        }

        if ($allowNone) {
            return [
                'nameRegister' => 'NONE',
                'DescriptionRegister' => 'NONE',
            ];
        }

        return [
            'nameRegister' => self::APPS->label(),
            'DescriptionRegister' => self::APPS->description(),
        ];
    }
}
