<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * TEXT instead of VARCHAR(255): `registration_data` is close to MySQL's 65,535-byte row limit.
     */
    public function up(): void
    {
        Schema::table('registration_data', function (Blueprint $table) {
            $table->text('real_count')->nullable();
            $table->text('invoice_amount')->nullable();
            $table->text('support_school')->nullable();
            $table->json('tax_rates')->nullable();
            $table->text('tax_custom_rate')->nullable();
            $table->text('tax_amount')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('registration_data', function (Blueprint $table) {
            $table->dropColumn([
                'real_count',
                'invoice_amount',
                'support_school',
                'tax_rates',
                'tax_custom_rate',
                'tax_amount',
            ]);
        });
    }
};
