<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Default calendar years seeded into master data (`options` table).
     *
     * @var list<string>
     */
    private const DEFAULT_YEARS = ['2024', '2025', '2026', '2027'];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $existingYears = DB::table('registration_data')
            ->whereNotNull('years')
            ->distinct()
            ->pluck('years')
            ->map(fn ($year): string => trim((string) $year))
            ->filter(fn (string $year): bool => $year !== '')
            ->all();

        $years = collect(array_merge(self::DEFAULT_YEARS, $existingYears))
            ->unique()
            ->sort()
            ->values();

        $now = now();
        $alreadyInOptions = DB::table('options')
            ->where('group', 'year')
            ->pluck('value')
            ->all();

        $order = ((int) DB::table('options')->where('group', 'year')->max('sort_order')) + 1;
        $rows = [];

        foreach ($years as $year) {
            if (in_array($year, $alreadyInOptions, true)) {
                continue;
            }

            $rows[] = [
                'group' => 'year',
                'value' => $year,
                'label' => $year,
                'description' => null,
                'sort_order' => $order++,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if ($rows !== []) {
            DB::table('options')->insert($rows);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('options')->where('group', 'year')->delete();
    }
};
