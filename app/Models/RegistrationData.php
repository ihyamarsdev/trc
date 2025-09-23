<?php

namespace App\Models;

use App\Models\Status;
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
        'district',
        'area',
        'student_count',
        'implementation_estimate',
        'users_id',
        'schools',
        'class',
        'years',
        'education_level',
        'description',
        'principal',
        'phone',
        'principal_phone',
        'schools_type',
        'curriculum_deputies',
        'curriculum_deputies_phone',
        'counselor_coordinators',
        'counselor_coordinators_phone',
        'proctors',
        'proctors_phone',

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
        'total_invoice',

        'mitra_difference',
        'mitra_net',
        'mitra_subtotal',
        'ss_difference',
        'ss_net',
        'ss_subtotal',
        'dll_difference',
        'dll_net',
        'dll_subtotal',

        'option_price',
        'cb,',
        'status_id', // Foreign key to statuses table
    ];

    protected $casts = [
        'date_register' => 'datetime',
        'counselor_consultation_date' => 'datetime',
        'student_consultation_date' => 'datetime',
        'invoice_date' => 'datetime',
        'spk_sent' => 'datetime',
        'payment_date' => 'datetime',
    ];

    /**
     * Get the user that owns the registration data
     */
    public function users(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the status associated with the registration data
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class);
    }

    /**
     * Get the color indicator based on status
     */
    public function getColorIndicatorAttribute()
    {
        return $this->status ? $this->status->color : 'gray';
    }

    /**
     * Get the category based on status
     */
    public function getCategoryAttribute()
    {
        return $this->status ? $this->status->category : 'general';
    }

    /**
     * Check if registration data is in academic phase (yellow)
     */
    public function isAcademicPhase()
    {
        return $this->getCategoryAttribute() === 'akademik';
    }

    /**
     * Check if registration data is in technician phase (blue)
     */
    public function isTechnicianPhase()
    {
        return $this->getCategoryAttribute() === 'teknisi';
    }

    /**
     * Check if registration data is in finance phase (green)
     */
    public function isFinancePhase()
    {
        return $this->getCategoryAttribute() === 'finance';
    }

    /**
     * Move to the next status in the flow
     */
    public function moveToNextStatus()
    {
        if ($this->status) {
            $nextStatus = $this->status->nextStatus();
            if ($nextStatus) {
                $this->status_id = $nextStatus->id;
                $this->save();
                return $nextStatus;
            }
        }
        return null;
    }

    /**
     * Move to the previous status in the flow
     */
    public function moveToPreviousStatus()
    {
        if ($this->status) {
            $previousStatus = $this->status->previousStatus();
            if ($previousStatus) {
                $this->status_id = $previousStatus->id;
                $this->save();
                return $previousStatus;
            }
        }
        return null;
    }

    /**
     * Set status to the first status in the flow
     */
    public function setToFirstStatus()
    {
        $firstStatus = Status::active()->ordered()->first();
        if ($firstStatus) {
            $this->status_id = $firstStatus->id;
            $this->save();
            return $firstStatus;
        }
        return null;
    }
}
