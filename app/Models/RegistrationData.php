<?php

namespace App\Models;

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
        'spk' => 'datetime',
        'payment_date' => 'datetime',
        'group' => 'datetime',
        'implementation_estimate' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::saving(function (RegistrationData $record) {
            if ($record->implementation_estimate && $record->implementation_estimate < now()) {
                $latestColor = $record->latestStatusLog?->status?->color ?? $record->status?->color ?? $record->status_color;

                if (strtolower((string) $latestColor) === 'yellow') {
                    $redStatus = Status::query()->where('color', 'red')->orderBy('order')->first()
                        ?? Status::query()->where('order', 1)->first();

                    if ($redStatus) {
                        $record->status_id = $redStatus->id;
                        $record->status_color = $redStatus->color;
                    }
                }
            }
        });
    }

    public static function updateOverdueYellowStatuses(): int
    {
        $redStatus = Status::query()->where('color', 'red')->orderBy('order')->first()
            ?? Status::query()->where('order', 1)->first();

        if (! $redStatus) {
            return 0;
        }

        $yellowStatusIds = Status::query()->where('color', 'yellow')->pluck('id')->toArray();

        if (empty($yellowStatusIds)) {
            return 0;
        }

        $overdueRecords = static::query()
            ->whereNotNull('implementation_estimate')
            ->where('implementation_estimate', '<', now())
            ->where(function ($query) use ($yellowStatusIds) {
                $query->whereHas('latestStatusLog.status', function ($q) {
                    $q->where('color', 'yellow');
                })
                    ->orWhere(function ($q) use ($yellowStatusIds) {
                        $q->whereIn('status_id', $yellowStatusIds)
                            ->whereDoesntHave('latestStatusLog.status', function ($sub) {
                                $sub->where('color', '!=', 'yellow');
                            });
                    });
            })
            ->get();

        $updatedCount = 0;

        foreach ($overdueRecords as $record) {
            $latestColor = $record->latestStatusLog?->status?->color ?? $record->status?->color ?? $record->status_color;

            if (strtolower((string) $latestColor) !== 'yellow') {
                continue;
            }

            $record->status_id = $redStatus->id;
            $record->status_color = $redStatus->color;
            $record->saveQuietly();

            RegistrationStatus::query()
                ->where('registration_id', $record->id)
                ->whereIn('status_id', $yellowStatusIds)
                ->delete();

            $lastLog = RegistrationStatus::query()
                ->where('registration_id', $record->id)
                ->latest('id')
                ->first();

            if (! $lastLog || (int) $lastLog->status_id !== (int) $redStatus->id) {
                RegistrationStatus::create([
                    'registration_id' => $record->id,
                    'status_id' => $redStatus->id,
                    'user_id' => $record->users_id,
                ]);
            }

            $updatedCount++;
        }

        return $updatedCount;
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
