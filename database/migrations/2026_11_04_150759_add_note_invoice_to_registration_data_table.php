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
            $table->text('note_invoice')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('registration_data', function (Blueprint $table) {
            $table->dropColumn('note_invoice');
        });
    }
};
