<?php

namespace App\Filament\Enum;

enum Program: string
{
    case ANBK = 'anbk';
    case APPS = 'apps';
    case SNBT = 'snbt';
    case TKA = 'tka';
    case PASJ = 'pasj';

    public function label(): string
    {
        return match ($this) {
            self::ANBK => 'ANBK',
            self::APPS => 'APPS',
            self::SNBT => 'SNBT',
            self::TKA => 'TKA',
            self::PASJ => 'PASJ',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::ANBK => 'ASESMEN NASIONAL BERBASIS KOMPUTER',
            self::APPS => 'ASESMEN PSIKOTES POTENSI SISWA',
            self::SNBT => 'SELEKSI NASIONAL BERDASARKAN TES',
            self::TKA => 'TEST KEMAMPUAN AKADEMIK',
            self::PASJ => 'PROGRAM ANALISIS SIDIK JARI',
        };
    }

    public static function getMetadata(?string $type, string $default = 'apps'): array
    {
        $program = self::tryFrom(strtolower($type ?? ''));

        if ($program) {
            return [
                'nameRegister' => $program->label(),
                'DescriptionRegister' => $program->description(),
            ];
        }

        $fallback = self::tryFrom($default);
        return [
            'nameRegister' => $fallback ? $fallback->label() : strtoupper($default),
            'DescriptionRegister' => $fallback ? $fallback->description() : strtoupper($default),
        ];
    }

    public static function list(): array
    {
        return [
            'anbk' => self::ANBK->label(),
            'apps' => self::APPS->label(),
            'snbt' => self::SNBT->label(),
            'tka' => self::TKA->label(),
            'pasj' => self::PASJ->label(),
        ];
    }
}
