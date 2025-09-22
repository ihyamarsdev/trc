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

            ## Sales
            $table->enum('type', ['apps','anbk','snbt']);
            $table->enum('periode', ['Januari - Juni', 'Juli - Desember']);
            $table->string('school_years');
            $table->date('date_register');
            $table->string('provinces');
            $table->string('regencies');
            $table->integer('student_count');
            $table->string('counselor_coordinators');
            $table->string('counselor_coordinators_phone')->nullable();
            $table->string('curriculum_deputies');
            $table->string('curriculum_deputies_phone')->nullable();
            $table->string('proctors');
            $table->string('proctors_phone')->nullable();
            $table->string('schools');
            $table->string('class')->nullable();
            $table->string('education_level');
            $table->string('principal');
            $table->string('principal_phone')->nullable();
            $table->enum('education_level_type', ['Negeri','Swasta']);
            
            ## Akademik dan Teknisi
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
            
            ## Finance
            $table->string('price')->nullable();
            $table->string('total')->nullable();
            $table->string('net')->nullable();
            $table->string('total_net')->nullable();
            $table->date('invoice_date')->nullable();
            $table->date('spk_sent')->nullable();
            $table->date('payment_date')->nullable();
            $table->string('payment')->nullable();
            $table->string('cb')->nullable();
            $table->string('option_price')->nullable();
            $table->string('monthYear')->nullable();


            $table->string('sudin')->nullable();

            $table->string('mitra_difference')->nullable();
            $table->string('mitra_net')->nullable();
            $table->string('mitra_subtotal')->nullable();

            $table->string('ss_difference')->nullable();
            $table->string('ss_net')->nullable();
            $table->string('ss_subtotal')->nullable();

            $table->string('dll_difference')->nullable();
            $table->string('dll_net')->nullable();
            $table->string('dll_subtotal')->nullable();

            $table->string('detail_invoice')->nullable();
            $table->string('number_invoice')->nullable();
            $table->string('qty_invoice')->nullable();
            $table->string('unit_price')->nullable();
            $table->string('amount_invoice')->nullable();
            $table->string('tax_rate')->nullable();
            $table->string('sales_tsx')->nullable();
            $table->string('other')->nullable();
            $table->string('subtotal_invoice')->nullable();
            $table->string('total_invoice')->nullable();

            $table->string('detail_kwitansi')->nullable();
            $table->string('difference_total')->nullable();

            $table->string('subtotal_1')->nullable();
            $table->string('subtotal_2')->nullable();

            $table->string('net_2')->nullable();
            $table->string('student_count_1')->nullable();
            $table->string('student_count_2')->nullable();


            $table->foreignId('users_id')->constrained()->onUpdate('cascade')->onDelete('cascade');
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
