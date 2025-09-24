<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('registration_statuses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('registration_id')->constrained('registration_data')->cascadeOnDelete();
            $table->foreignId('status_id')->constrained('statuses')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->index(['registration_id', 'created_at']);
            $table->index(['status_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registration_statuses');
    }
};
