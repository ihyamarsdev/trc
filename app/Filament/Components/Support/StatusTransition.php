<?php

namespace App\Filament\Components\Support;

use App\Filament\User\Resources\Service\ServiceResource;
use App\Models\RegistrationData;
use App\Models\RegistrationStatus;
use App\Models\Status;
use App\Models\User;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Writes the activity log and the automatic status changes.
 *
 * Statuses do not have to follow each other in order (a school may be paid up front and go from yellow straight
 * to green), so the log is a plain append-only list of "this record is now in status X": nothing here deletes an entry.
 */
class StatusTransition
{
    /**
     * Fields that must be filled before a red record turns yellow by itself, keyed by their label.
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

    private const COLOR_LABEL = ['red' => 'Merah', 'yellow' => 'Kuning', 'blue' => 'Biru', 'green' => 'Hijau'];

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
    public static function log(RegistrationData $record, ?int $userId, ?string $note = null): void
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
        ]);
    }

    /**
     * Put many records in one status at once, e.g. a whole district that is run on the same day.
     * Records already in that status are left alone. Two queries per 500 records, however many are picked.
     *
     * @param  Collection<int, RegistrationData>  $records
     * @return array{changed: int, unchanged: int}
     */
    public static function changeMany(Collection $records, Status $to, ?int $userId, ?string $note = null): array
    {
        $toChange = $records->filter(fn (RegistrationData $record): bool => (int) $record->status_id !== (int) $to->id);

        DB::transaction(function () use ($toChange, $to, $userId, $note): void {
            $now = now();
            $note = 'Ubah massal'.(filled($note) ? ': '.$note : '');

            foreach ($toChange->pluck('id')->chunk(500) as $ids) {
                RegistrationData::query()->whereKey($ids)->update([
                    'status_id' => $to->id,
                    'status_color' => $to->color,
                    'updated_at' => $now,
                ]);

                RegistrationStatus::query()->insert($ids->map(fn (int $id): array => [
                    'registration_id' => $id,
                    'status_id' => $to->id,
                    'user_id' => $userId,
                    'note' => $note,
                    'created_at' => $now,
                    'updated_at' => $now,
                ])->all());
            }
        });

        return ['changed' => $toChange->count(), 'unchanged' => $records->count() - $toChange->count()];
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
            || strtolower((string) $current->color) !== 'red'
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

    public static function label(Status $status): string
    {
        return self::COLOR_LABEL[strtolower((string) $status->color)] ?? (string) $status->color;
    }
}
