<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * `note` holds why a status entry was added outside a plain edit (automatic, overdue, bulk change).
     */
    public function up(): void
    {
        Schema::table('registration_statuses', function (Blueprint $table) {
            $table->text('note')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('registration_statuses', function (Blueprint $table) {
            $table->dropColumn('note');
        });
    }
};
