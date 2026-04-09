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
            $table->index(['years', 'users_id'], 'registration_data_years_users_id_index');
            $table->index(['years', 'implementation_estimate'], 'registration_data_years_implementation_estimate_index');
        });

        Schema::table('registration_statuses', function (Blueprint $table) {
            $table->index(['registration_id', 'id'], 'registration_statuses_registration_id_id_index');
        });

        Schema::table('statuses', function (Blueprint $table) {
            $table->index(['category', 'order'], 'statuses_category_order_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('statuses', function (Blueprint $table) {
            $table->dropIndex('statuses_category_order_index');
        });

        Schema::table('registration_statuses', function (Blueprint $table) {
            $table->dropIndex('registration_statuses_registration_id_id_index');
        });

        Schema::table('registration_data', function (Blueprint $table) {
            $table->dropIndex('registration_data_years_implementation_estimate_index');
            $table->dropIndex('registration_data_years_users_id_index');
        });
    }
};
