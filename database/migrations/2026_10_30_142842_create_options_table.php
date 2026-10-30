<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Choices that used to be hard-coded in the application, now editable from the admin panel.
     * Format per group: value => label (programs: value => [label, description]).
     *
     * @var array<string, array<string, string|array{0: string, 1: string}>>
     */
    private const DEFAULTS = [
        'program' => [
            'anbk' => ['ANBK', 'ASESMEN NASIONAL BERBASIS KOMPUTER'],
            'apps' => ['APPS', 'ASESMEN PSIKOTES POTENSI SISWA'],
            'snbt' => ['SNBT', 'SELEKSI NASIONAL BERDASARKAN TES'],
            'tka' => ['TKA', 'TEST KEMAMPUAN AKADEMIK'],
            'pasj' => ['PASJ', 'PROGRAM ANALISIS SIDIK JARI'],
        ],
        'periode' => [
            'PERIODE 1' => 'PERIODE 1',
            'PERIODE 2' => 'PERIODE 2',
        ],
        'jenjang' => [
            'SD' => 'SD',
            'MI' => 'MI',
            'SMP' => 'SMP',
            'MTS' => 'MTS',
            'SMA' => 'SMA',
            'MA' => 'MA',
            'SMK' => 'SMK',
        ],
        'keterangan' => [
            'ABK' => 'ABK',
            'NON-ABK' => 'NON ABK',
        ],
        'school_type' => [
            'NEGERI' => 'NEGERI',
            'SWASTA' => 'SWASTA',
        ],
        'payment_via' => [
            'SIPLAH' => 'SIPLAH',
            'SI/TF' => 'SI / TF',
            'CASH' => 'CASH',
        ],
        'bank' => [
            'PT. TDW BANK MANDIRI' => 'PT. TDW BANK MANDIRI',
            'PT. TDW BANK NAGARI' => 'PT. TDW BANK NAGARI',
            'PT. EDUNESIA BANK BNI' => 'PT. EDUNESIA BANK BNI',
            'PT. EDSINDO BANK BPD' => 'PT. EDSINDO BANK BPD',
        ],
        'tax' => [
            '11' => '11%',
            '2' => '2%',
            '0.5' => '0,5%',
        ],
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('options', function (Blueprint $table) {
            $table->id();
            $table->string('group', 40);
            $table->string('value', 100);
            $table->string('label', 150);
            $table->text('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['group', 'value']);
            $table->index(['group', 'sort_order']);
        });

        $now = now();
        $rows = [];

        foreach (self::DEFAULTS as $group => $choices) {
            $order = 1;

            foreach ($choices as $value => $label) {
                [$label, $description] = is_array($label) ? $label : [$label, null];

                $rows[] = [
                    'group' => $group,
                    'value' => (string) $value,
                    'label' => $label,
                    'description' => $description,
                    'sort_order' => $order++,
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        DB::table('options')->insert($rows);

        // Admin form used "Negeri" while the Salesforce form used "NEGERI"; one list now serves both.
        DB::table('registration_data')
            ->whereNotNull('schools_type')
            ->update(['schools_type' => DB::raw('UPPER(schools_type)')]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('options');
    }
};
