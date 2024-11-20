<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RegistrationData extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'periode',
        'date_register',
        'provinces',
        'regencies',
        'student_count',
        'implementation_estimate',
        'group',
        'bimtek',
        'account_count_created',
        'implementer_count',
        'difference',
        'students_download',
        'schools_download',
        'pm',
        'counselor_consultation_date',
        'student_consultation_date',
        'price',
        'total',
        'net',
        'total_net',
        'invoice_date',
        'spk_sent',
        'payment',
        'payment_date',
        'curriculum_deputies_id',
        'counselor_coordinators_id',
        'proctors_id',
        'users_id',
        'schools',
        'school_years_id',
        'education_level',
        'principal',
        'phone',
        'phone_principal',
        'education_level_type',
        'monthYear',
        'net_2',
        'student_count_1',
        'student_count_2',
        'subtotal_1',
        'subtotal_2',
        'difference_total',
        'detail_kwitansi',

        'detail_invoice',
        'number_invoice',
        'qty_invoice',
        'unit_price',
        'amount_invoice',
        'tax_rate',
        'sales_tsx',
        'other',
        'subtotal_invoice',
        'total_invoice'
    ];

    protected $casts = [
        'date_register' => 'datetime'
    ];


    public function users(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }


    public function school_years(): BelongsTo
    {
        return $this->belongsTo(SchoolYear::class);
    }

    public function counselor_coordinators(): BelongsTo
    {
        return $this->belongsTo(CounselorCoordinator::class);
    }

    public function curriculum_deputies(): BelongsTo
    {
        return $this->belongsTo(CurriculumDeputies::class);
    }

    public function proctors(): BelongsTo
    {
        return $this->belongsTo(Proctors::class);
    }
}
