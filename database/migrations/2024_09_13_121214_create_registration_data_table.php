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
        Schema::create('registration_data', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['apps','anbk','snbt']);
            $table->enum('periode', ['Januari - Juni', 'Juli - Desember']);
            $table->date('date_register');
            $table->string('provinces');
            $table->string('regencies');
            $table->integer('student_count');
            $table->date('implementation_estimate');
            $table->date('group')->nullable();
            $table->date('bimtek')->nullable();
            $table->integer('account_count_created')->nullable();
            $table->integer('implementer_count')->nullable();
            $table->integer('difference')->nullable();
            $table->enum('students_download', ['ya', 'tidak'])->nullable();
            $table->enum('schools_download', ['ya', 'tidak'])->nullable();
            $table->enum('pm', ['ya', 'tidak'])->nullable();
            $table->date('counselor_consultation_date')->nullable();
            $table->date('student_consultation_date')->nullable();
            $table->string('price')->nullable();
            $table->string('total')->nullable();
            $table->string('net')->nullable();
            $table->string('total_net')->nullable();
            $table->date('invoice_date')->nullable();
            $table->date('spk_sent')->nullable();
            $table->date('payment_date')->nullable();
            $table->string('payment')->nullable();
            $table->string('cb')->nullable();
            $table->string('schools');
            $table->string('education_level');
            $table->string('principal');
            $table->string('phone_principal')->nullable();
            $table->enum('education_level_type', ['Negeri','Swasta']);


            $table->foreignId('curriculum_deputies_id')->constrained()->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('counselor_coordinators_id')->constrained()->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('proctors_id')->constrained()->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('users_id')->constrained()->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('school_years_id')->constrained()->onUpdate('cascade')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registration_data');
    }
};
