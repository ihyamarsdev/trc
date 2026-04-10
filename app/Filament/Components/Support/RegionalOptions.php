<?php

namespace App\Filament\Components\Support;

use Creasi\Nusa\Models\District;
use Creasi\Nusa\Models\Province;
use Creasi\Nusa\Models\Regency;

class RegionalOptions
{
    public static function provinces(): array
    {
        return Province::query()
            ->orderBy('name')
            ->pluck('name', 'name')
            ->all();
    }

    public static function regenciesByProvinceName(?string $provinceName): array
    {
        if (blank($provinceName)) {
            return [];
        }

        $provinceCode = Province::query()
            ->where('name', $provinceName)
            ->value('code');

        if (! $provinceCode) {
            return [];
        }

        return Regency::query()
            ->where('province_code', $provinceCode)
            ->orderBy('name')
            ->pluck('name', 'name')
            ->all();
    }

    public static function districtsByRegencyName(?string $regencyName): array
    {
        if (blank($regencyName)) {
            return [];
        }

        $regencyCode = Regency::query()
            ->where('name', $regencyName)
            ->value('code');

        if (! $regencyCode) {
            return [];
        }

        return District::query()
            ->where('regency_code', $regencyCode)
            ->orderBy('name')
            ->pluck('name', 'name')
            ->all();
    }

    public static function areasByRegencyName(?string $regencyName): array
    {
        if (blank($regencyName)) {
            return [];
        }

        $regencyCode = Regency::query()
            ->where('name', $regencyName)
            ->value('code');

        if (! $regencyCode) {
            return [];
        }

        return self::areasByRegencyCode($regencyCode);
    }

    public static function areasByRegencyCode(?string $regencyCode): array
    {
        if (blank($regencyCode)) {
            return [];
        }

        return self::areaMap()[self::normalizeCode($regencyCode)] ?? [];
    }

    /**
     * @return array<string, array<string, string>>
     */
    private static function areaMap(): array
    {
        return [
            '3101' => ['kS 01' => 'KS 01', 'KS_02' => 'KS 02'],
            '3171' => ['JP 01' => 'JP 01', 'JP 02' => 'JP 02'],
            '3172' => ['JU 01' => 'JU 01', 'JU 02' => 'JU 02'],
            '3173' => ['JB 01' => 'JB 01', 'JB 02' => 'JB 02'],
            '3174' => ['JS 01' => 'JS 01', 'JS 02' => 'JU 02'],
            '3175' => ['JT 01' => 'JT 01', 'JT 02' => 'JT 02'],
        ];
    }

    private static function normalizeCode(string $regencyCode): string
    {
        return preg_replace('/\D+/', '', $regencyCode) ?? '';
    }
}
