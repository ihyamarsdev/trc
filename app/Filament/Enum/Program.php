<?php

namespace App\Filament\Enum;

enum Program: string
{
    case ANBK = 'anbk';
    case APPS = 'apps';
    case SNBT = 'snbt';
    case TKA = 'tka';
    case PASJ = 'pasj';

    private static function registry(): array
    {
        return [
            self::ANBK->value => [
                'label' => 'ANBK',
                'description' => 'ASESMEN NASIONAL BERBASIS KOMPUTER',
            ],
            self::APPS->value => [
                'label' => 'APPS',
                'description' => 'ASESMEN PSIKOTES POTENSI SISWA',
            ],
            self::SNBT->value => [
                'label' => 'SNBT',
                'description' => 'SELEKSI NASIONAL BERDASARKAN TES',
            ],
            self::TKA->value => [
                'label' => 'TKA',
                'description' => 'TEST KEMAMPUAN AKADEMIK',
            ],
            self::PASJ->value => [
                'label' => 'PASJ',
                'description' => 'PROGRAM ANALISIS SIDIK JARI',
            ],
        ];
    }

    public function label(): string
    {
        return self::registry()[$this->value]['label'] ?? strtoupper($this->value);
    }

    public function description(): string
    {
        return self::registry()[$this->value]['description'] ?? strtoupper($this->value);
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
        $list = [];
        foreach (self::cases() as $case) {
            $list[$case->value] = $case->label();
        }
        return $list;
    }
}
