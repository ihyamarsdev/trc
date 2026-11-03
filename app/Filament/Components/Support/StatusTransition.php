<?php

namespace App\Filament\Components\Support;

use App\Filament\User\Resources\Service\ServiceResource;
use App\Models\RegistrationData;
use App\Models\RegistrationStatus;
use App\Models\Status;
use App\Models\User;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;

/**
 * The one place that decides which status changes are allowed and writes the activity log.
 *
 * Flow: merah (Sales) -> kuning (Sales, data wajib lengkap) -> biru (Service) -> hijau (Finance).
 * A change may move one colour forward or one colour back; skipping a colour is only possible
 * through {@see self::override()}, which needs a reason and is flagged in the log.
 * The log is append-only: nothing here deletes an earlier entry.
 */
class StatusTransition
{
    /**
     * Fields that must be filled before a record may leave red, keyed by their label.
     *
     * @var array<string, string>
     */
    public const REQUIRED_FIELDS = [
        'principal' => 'Nama Kepala Sekolah',
        'principal_phone' => 'No Handphone Kepala Sekolah',
        'student_count' => 'Jumlah Siswa',
        'schools' => 'Sekolah',
        'implementation_estimate' => 'Estimasi Pelaksanaan',
        'curriculum_deputies' => 'Wakakurikulum',
        'curriculum_deputies_phone' => 'No Handphone Wakakurikulum',
    ];

    private const RANK = ['red' => 0, 'yellow' => 1, 'blue' => 2, 'green' => 3];

    private const COLOR_LABEL = ['red' => 'Merah', 'yellow' => 'Kuning', 'blue' => 'Biru', 'green' => 'Hijau'];

    /**
     * Why `$from` -> `$to` is refused, or null when it is allowed.
     *
     * @param  array<string, mixed>  $attributes  the record's values as they will be saved
     */
    public static function denial(?Status $from, Status $to, array $attributes): ?string
    {
        if ($from === null) {
            return self::initialDenial($to, $attributes);
        }

        $fromRank = self::rank($from);
        $toRank = self::rank($to);

        if ($fromRank === null || $toRank === null || $fromRank === $toRank) {
            return null;
        }

        if (abs($toRank - $fromRank) > 1) {
            return "Status tidak boleh melompat dari {$from->name} (".self::label($from).") ke {$to->name} (".self::label($to).'). Ubah bertahap per warna, atau gunakan Override Status (khusus admin) dengan alasan.';
        }

        if ($fromRank === 0 && $toRank === 1) {
            return self::incompleteMessage($attributes);
        }

        return null;
    }

    /**
     * A new record may only start red, or yellow when the required data is already complete.
     *
     * @param  array<string, mixed>  $attributes
     */
    public static function initialDenial(Status $to, array $attributes): ?string
    {
        return match (self::rank($to)) {
            null, 0 => null,
            1 => self::incompleteMessage($attributes),
            default => 'Data baru harus dimulai dari status Merah atau Kuning. Buat dulu, lalu ubah bertahap atau gunakan Override Status (khusus admin).',
        };
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<int, string> labels of the required fields that are still blank
     */
    public static function missingFields(array $attributes): array
    {
        return collect(self::REQUIRED_FIELDS)
            ->reject(fn (string $label, string $field): bool => filled($attributes[$field] ?? null))
            ->values()
            ->all();
    }

    /**
     * Append the record's current status to its log unless the latest entry already says so.
     */
    public static function log(RegistrationData $record, ?int $userId, ?string $note = null, bool $override = false): void
    {
        if (empty($record->status_id)) {
            return;
        }

        $lastStatusId = RegistrationStatus::query()
            ->where('registration_id', $record->id)
            ->latest('id')
            ->value('status_id');

        if ((int) $lastStatusId === (int) $record->status_id) {
            return;
        }

        RegistrationStatus::create([
            'registration_id' => $record->id,
            'status_id' => $record->status_id,
            'user_id' => $userId,
            'note' => $note,
            'is_override' => $override,
        ]);
    }

    /**
     * Admin bypass of the flow. The reason is mandatory and shown in the activity timeline.
     */
    public static function override(RegistrationData $record, Status $to, string $reason, ?int $userId): void
    {
        DB::transaction(function () use ($record, $to, $reason, $userId): void {
            $record->forceFill(['status_id' => $to->id, 'status_color' => $to->color])->save();

            self::log($record, $userId, $reason, override: true);
        });
    }

    /**
     * A yellow record whose implementation date has passed goes back to red. The yellow entry stays in the log.
     */
    public static function revertOverdue(RegistrationData $record, Status $red): void
    {
        DB::transaction(function () use ($record, $red): void {
            $record->forceFill(['status_id' => $red->id, 'status_color' => $red->color])->saveQuietly();

            self::log($record, $record->users_id, 'Melewati estimasi pelaksanaan');
        });
    }

    /**
     * A red record whose required fields are all filled becomes yellow by itself, so Sales never has to remember
     * to change it. Not while the implementation date is already past: the overdue job would send it straight back to red.
     */
    public static function promoteIfComplete(RegistrationData $record, ?int $userId): bool
    {
        $current = Status::find($record->status_id);

        if ($current === null
            || self::rank($current) !== 0
            || self::missingFields($record->getAttributes()) !== []
            || $record->implementation_estimate->isPast()) {
            return false;
        }

        $yellow = Status::query()->where('color', 'yellow')->orderBy('order')->first();

        if ($yellow === null) {
            return false;
        }

        DB::transaction(function () use ($record, $yellow, $userId): void {
            $record->forceFill(['status_id' => $yellow->id, 'status_color' => $yellow->color])->saveQuietly();

            self::log($record, $userId, 'Otomatis: data wajib sudah lengkap');
        });

        Notification::make()
            ->title('Data Sekolah '.$record->schools.' memasuki status '.$yellow->name)
            ->icon('heroicon-o-document-text')
            ->success()
            ->actions([
                Action::make('Lihat')
                    ->url(ServiceResource::getUrl('view', ['record' => $record], panel: 'user'))
                    ->openUrlInNewTab(),
            ])
            ->sendToDatabase(User::query()->whereHas('roles', fn ($query) => $query->where('name', 'service'))->get());

        return true;
    }

    private static function rank(Status $status): ?int
    {
        return self::RANK[strtolower((string) $status->color)] ?? null;
    }

    public static function label(Status $status): string
    {
        return self::COLOR_LABEL[strtolower((string) $status->color)] ?? (string) $status->color;
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private static function incompleteMessage(array $attributes): ?string
    {
        $missing = self::missingFields($attributes);

        return $missing === []
            ? null
            : 'Status Kuning hanya bisa dipilih jika data wajib sudah lengkap. Belum terisi: '.implode(', ', $missing).'.';
    }
}
