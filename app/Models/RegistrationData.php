<?php

namespace App\Models;

use App\Filament\Components\Support\StatusTransition;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
        'status_color',
        'notes',

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
        'spk',
        'payment_name',
        'payment_date',
        'monthYear',
        'net_2',
        'student_count_1',
        'student_count_2',
        'subtotal_1',
        'subtotal_2',
        'difference_total',
        'detail_kwitansi',
        'number_kwitansi',
        'number_spk',
        'note_invoice',

        'detail_invoice',
        'number_invoice',
        'qty_invoice',
        'unit_price',
        'amount_invoice',
        'tax_rate',
        'sales_tsx',
        'pph',
        'ppn',
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
        'real_count',
        'invoice_amount',
        'support_school',
        'tax_rates',
        'tax_custom_rate',
        'tax_amount',
        'total_received',
        'total_support_school',
        'support_school_date',
        'bank_name',
        'cb,',
        'status_id', // Foreign key to statuses table
    ];

    protected $casts = [
        'date_register' => 'datetime',
        'counselor_consultation_date' => 'datetime',
        'student_consultation_date' => 'datetime',
        'invoice_date' => 'datetime',
        'tax_rates' => 'array',
        'spk' => 'datetime',
        'payment_date' => 'datetime',
        'support_school_date' => 'datetime',
        'group' => 'datetime',
        'implementation_estimate' => 'datetime',
    ];

    /**
     * Yellow records whose implementation date has passed go back to red. The yellow log entry is kept
     * and a red one is appended, so the history shows what happened.
     */
    public static function updateOverdueYellowStatuses(): int
    {
        $redStatus = Status::query()->where('color', 'red')->orderBy('order')->first();

        if (! $redStatus) {
            return 0;
        }

        $overdueRecords = static::query()
            ->where('implementation_estimate', '<', now())
            ->whereRelation('status', 'color', 'yellow')
            ->get();

        foreach ($overdueRecords as $record) {
            StatusTransition::revertOverdue($record, $redStatus);
        }

        return $overdueRecords->count();
    }

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

    public function activity()
    {
        return $this->hasMany(RegistrationStatus::class, 'registration_id')
            ->with(['status:id,name,description,color,category', 'user:id,name']);
    }

    public function latestStatusLog()
    {
        return $this->hasOne(RegistrationStatus::class, 'registration_id')
            ->latestOfMany(); // ambil baris log terakhir (created_at / id terbesar)
    }
}
