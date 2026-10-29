<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('registration_data', function (Blueprint $table) {
            $table->string('real_count')->nullable();
            $table->string('invoice_amount')->nullable();
            $table->string('support_school')->nullable();
            $table->json('tax_rates')->nullable();
            $table->string('tax_custom_rate')->nullable();
            $table->string('tax_amount')->nullable();
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
