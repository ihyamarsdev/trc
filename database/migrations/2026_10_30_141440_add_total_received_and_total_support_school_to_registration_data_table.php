<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Columns added for the finance form; all TEXT because `registration_data` is already at MySQL's
     * 65,535-byte row limit (every VARCHAR(255) costs up to 1,020 bytes, a TEXT only about 10).
     *
     * @var list<string>
     */
    private const NEW_COLUMNS = ['total_received', 'total_support_school', 'bank_name'];

    /**
     * Columns of the earlier finance migrations that may still exist as VARCHAR(255) on servers
     * where they ran (or half-ran, MySQL does not roll back DDL) before they became TEXT.
     *
     * @var list<string>
     */
    private const SHRINK_COLUMNS = [
        'real_count',
        'invoice_amount',
        'support_school',
        'tax_custom_rate',
        'tax_amount',
        'total_received',
        'total_support_school',
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Shrinking first frees the room the new columns need.
        $types = collect(Schema::getColumns('registration_data'))->pluck('type_name', 'name');

        foreach (self::SHRINK_COLUMNS as $column) {
            if ($types->has($column) && ! str_ends_with((string) $types->get($column), 'text')) {
                Schema::table('registration_data', function (Blueprint $table) use ($column) {
                    $table->text($column)->nullable()->change();
                });
            }
        }

        foreach (self::NEW_COLUMNS as $column) {
            if (! Schema::hasColumn('registration_data', $column)) {
                Schema::table('registration_data', function (Blueprint $table) use ($column) {
                    $table->text($column)->nullable();
                });
            }
        }
    }

    /**
     * Reverse the migrations. The shrunk columns stay TEXT.
     */
    public function down(): void
    {
        $existing = array_values(array_filter(self::NEW_COLUMNS, fn (string $column): bool => Schema::hasColumn('registration_data', $column)));

        if ($existing !== []) {
            Schema::table('registration_data', function (Blueprint $table) use ($existing) {
                $table->dropColumn($existing);
            });
        }
    }
};
