<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * `note` holds why a status changed outside the normal flow; `is_override` marks an admin bypass.
     */
    public function up(): void
    {
        Schema::table('registration_statuses', function (Blueprint $table) {
            $table->text('note')->nullable();
            $table->boolean('is_override')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('registration_statuses', function (Blueprint $table) {
            $table->dropColumn(['note', 'is_override']);
        });
    }
};
