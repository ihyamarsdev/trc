<?php

namespace App\Filament\User\Resources\Activity\ActivityResource\Pages;

use App\Filament\Components\ActivitySection;
use App\Filament\Components\Support\StatusPalette;
use App\Filament\User\Resources\Activity\ActivityResource;
use App\Models\RegistrationStatus;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use JaOcero\ActivityTimeline\Components\ActivityDate;
use JaOcero\ActivityTimeline\Components\ActivityDescription;
use JaOcero\ActivityTimeline\Components\ActivityIcon;
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
            ->with(['status:id,name,description,color,icon,order', 'user:id,name'])
            ->orderBy('created_at')
            ->orderBy('id')
            ->get();

        $colorByState = [];
        $iconByState = [];
        $activities = [];

        foreach ($logs as $log) {
            $status = $log->status;

            if (! $status) {
                continue;
            }

            $state = 'status-'.$status->id;

            $title = "Status: <span class='font-semibold'>".e($status->name).'</span>';
            $description = $status->description ? e($status->description) : '—';
            $date = $log->created_at?->translatedFormat('l, d/m/Y H:i') ?? '-';

            $activities[] = [
                'title' => $title,
                'description' => $description,
                'status' => $state,
                'updated_at' => $date,
            ];

            $colorByState[$state] = StatusPalette::color($status->order);
            $iconByState[$state] = StatusPalette::icon($status->order);
        }

        if ($activities === []) {
            $activities[] = [
                'title' => 'Status: <span class="font-semibold">Belum ada riwayat</span>',
                'description' => 'Belum ada log status untuk data ini.',
                'status' => 'no-status',
                'updated_at' => '-',
            ];

            $colorByState['no-status'] = 'gray';
            $iconByState['no-status'] = 'heroicon-m-clock';
        }

        return $infolist
            ->state(['activities' => $activities])
            ->schema([
                ActivitySection::make('activities')
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
                            ->icon(fn (?string $state) => $iconByState[$state] ?? 'heroicon-m-clock')
                            ->animation(IconAnimation::Pulse)
                            ->color(fn (?string $state) => $colorByState[$state] ?? 'gray'),
                    ]),
            ]);
    }
}
