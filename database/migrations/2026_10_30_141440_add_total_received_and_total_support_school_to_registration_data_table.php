<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * `registration_data` is already close to MySQL's 65,535-byte row limit, so these are TEXT
     * (stored outside the row) instead of VARCHAR(255). MySQL does not roll back DDL, so a failed
     * run can leave some columns behind; each one is only added when it is missing.
     *
     * @var list<string>
     */
    private const COLUMNS = ['total_received', 'total_support_school', 'bank_name'];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach (self::COLUMNS as $column) {
            if (Schema::hasColumn('registration_data', $column)) {
                continue;
            }

            Schema::table('registration_data', function (Blueprint $table) use ($column) {
                $table->text($column)->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $existing = array_filter(self::COLUMNS, fn (string $column): bool => Schema::hasColumn('registration_data', $column));

        if ($existing !== []) {
            Schema::table('registration_data', function (Blueprint $table) use ($existing) {
                $table->dropColumn(array_values($existing));
            });
        }
    }
};
