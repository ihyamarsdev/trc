<?php

namespace App\Filament\User\Resources\Activity\ActivityResource\Pages;

use App\Filament\User\Resources\Activity\ActivityResource;
use App\Models\RegistrationStatus;
use App\Models\Status;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use JaOcero\ActivityTimeline\Components\ActivityDate;
use JaOcero\ActivityTimeline\Components\ActivityDescription;
use JaOcero\ActivityTimeline\Components\ActivityIcon;
use JaOcero\ActivityTimeline\Components\ActivitySection;
use JaOcero\ActivityTimeline\Components\ActivityTitle;
use JaOcero\ActivityTimeline\Enums\IconAnimation;

class ViewActivities extends Page
{
    use InteractsWithRecord;

    protected static string $resource = ActivityResource::class;

    protected static string $view = 'filament.user.resources.registration-data-resource.pages.view-color-activities';

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);
    }

    public function activityTimelineInfolist(Infolist $infolist): Infolist
    {

        $logs = RegistrationStatus::query()
            ->where('registration_id', $this->record->id)
            ->with(['status', 'user'])
            ->orderBy(
                Status::select('order')
                    ->whereColumn('statuses.id', 'registration_statuses.status_id')
            )
            ->get();

        // if ($logs->isEmpty()) {
        //     // DB::transaction(function () {
        //     //     // pastikan status default ada

        //     // });
        //     $status = Status::query()
        //            ->where('order', 1)
        //            ->first();

        //     // simpan log pertama (pilih salah satu skema sesuai kolom tabel-mu)
        //     RegistrationStatus::create([
        //         'registration_id' => $this->record->id,
        //         'status_id'       => $status->id,
        //         'user_id'         => $this->record->users_id, // kalau ada kolom user_id
        //         'order'           => $status->order,

        //         // HANYA isi ini jika kolom-kolomnya memang ada di tabel registration_statuses
        //         'name'        => $status->name ?? null,
        //         'description' => $status->description ?? null,
        //         'color'       => $status->color ?? null,
        //         'category'    => $status->category ?? null,
        //     ]);

        // }

        // // ikon per kategori
        // $iconByCategory = [
        //     'akademik' => 'heroicon-m-academic-cap',
        //     'teknisi'  => 'heroicon-m-wrench-screwdriver',
        //     'finance'  => 'heroicon-m-banknotes',
        //     'general'  => 'heroicon-m-bolt',
        // ];

        // map warna DB -> warna Filament
        // $toFilamentColor = fn (?string $raw) => match (Str::lower((string)$raw)) {
        //     'yellow','kuning' => 'yellow',
        //     'blue','biru'     => 'blue',
        //     'green','hijau'   => 'green',
        //     'red','merah'     => 'red',
        //     default           => 'gray',
        // };

        // Build state timeline
        $categoryBySlug = [];
        $colorBySlug = [];
        $activities = [];

        foreach ($logs as $s) {
            $slug = Str::slug($s->status->name);
            // $color = $toFilamentColor($s->status->color);

            $title = "Status: <span class='font-semibold'>".e($s->status->name).'</span>';
            $desc = $s->status->description ? e($s->status->description) : '—';
            $date = $s->created_at->translatedFormat('l, d/m/Y H:i');

            $activities[] = [
                'title' => $title,
                'description' => $desc,
                'status' => $slug,
                'updated_at' => $date,
            ];

            $colorBySlug[$slug] = $s->status->color;
            $iconBySlug[$slug] = $s->status->icon;
        }

        return $infolist
            ->state(['activities' => $activities])
            ->schema([
                ActivitySection::make('activities')
                    ->label('Progres Tracking')
                    ->description('Tahapan aktif hingga status saat ini.')
                    ->schema([
                        ActivityTitle::make('title')
                            ->placeholder('No title is set')
                            ->allowHtml(),

                        ActivityDescription::make('description')
                            ->placeholder('No description is set')
                            ->allowHtml(),

                        ActivityDate::make('updated_at')
                            ->placeholder('No date is set.'),

                        ActivityIcon::make('status')
                            ->icon(fn (?string $state) => $iconBySlug[$state] ?? 'heroicon-m-clock')
                            ->animation(IconAnimation::Pulse)
                            ->color(fn (?string $state) => $colorBySlug[$state] ?? 'gray'),
                    ])
                    ->showItemsCount(8)
                    ->showItemsLabel('Lihat Lebih Banyak')
                    ->showItemsIcon('heroicon-m-chevron-down')
                    ->showItemsColor('gray')
                    ->aside(false)
                    ->headingVisible(true),
            ]);
    }
}
