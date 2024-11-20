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
            $table->string('detail_invoice')->nullable();
            $table->string('number_invoice')->nullable();
            $table->string('qty_invoice')->nullable();
            $table->string('unit_price')->nullable();
            $table->string('amount_invoice')->nullable();
            $table->string('tax_rate')->nullable();
            $table->string('sales_tsx')->nullable();
            $table->string('other')->nullable();
            $table->string('subtotal_invoice')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('registration_data', function (Blueprint $table) {
            //
        });
    }
};
