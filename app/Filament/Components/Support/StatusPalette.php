<?php

namespace App\Filament\Components\Support;

use App\Models\Status;

class StatusPalette
{
    /**
     * @var array<int, string>|null
     */
    private static ?array $iconMap = null;

    /**
     * @var array<int, string>|null
     */
    private static ?array $colorMap = null;

    public static function icon(int|string|null $order): string
    {
        return self::iconMap()[(int) $order] ?? 'heroicon-m-clock';
    }

    public static function color(int|string|null $order): string
    {
        $rawColor = strtolower((string) (self::colorMap()[(int) $order] ?? ''));

        return match ($rawColor) {
            'green' => 'green',
            'blue' => 'blue',
            'yellow' => 'yellow',
            'red' => 'red',
            default => 'gray',
        };
    }

    /**
     * @return array<int, string>
     */
    private static function iconMap(): array
    {
        if (self::$iconMap === null) {
            self::$iconMap = Status::query()
                ->pluck('icon', 'order')
                ->all();
        }

        return self::$iconMap;
    }

    /**
     * @return array<int, string>
     */
    private static function colorMap(): array
    {
        if (self::$colorMap === null) {
            self::$colorMap = Status::query()
                ->pluck('color', 'order')
                ->all();
        }

        return self::$colorMap;
    }
}
