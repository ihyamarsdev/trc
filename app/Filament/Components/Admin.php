<?php

namespace App\Filament\Components;

use AlperenErsoy\FilamentExport\Actions\FilamentExportBulkAction;
use App\Filament\Components\Support\FormSchema;
use App\Filament\Components\Support\HasProgramMetadata;
use App\Filament\Components\Support\InfolistSchema;
use App\Filament\Components\Support\SharedSchema;
use App\Filament\Components\Support\StatusPalette;
use App\Filament\Enum\Periode;
use App\Filament\Enum\Program;
use App\Filament\User\Resources\Activity\ActivityResource;
use App\Models\RegistrationData;
use App\Models\RegistrationStatus;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Infolists;
use Filament\Infolists\Components\Actions\Action;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use JaOcero\ActivityTimeline\Components\ActivityDate;
use JaOcero\ActivityTimeline\Components\ActivityDescription;
use JaOcero\ActivityTimeline\Components\ActivityIcon;
use JaOcero\ActivityTimeline\Components\ActivityTitle;
use JaOcero\ActivityTimeline\Enums\IconAnimation;

class Admin
{
    use HasProgramMetadata;

    public static function getDifference(Get $get, Set $set): void
    {
        SharedSchema::getDifference($get, $set);
    }

    public static function formSchema(): array
    {
        $phoneRegex = '/^[+]*[(]{0,1}[0-9]{1,4}[)]{0,1}[-\s\.\/0-9]*$/';

        return [
            Tabs::make('Form')
                ->columnSpanFull()
                ->persistTabInQueryString()
                ->tabs([
                    Tab::make('Sekolah')
                        ->icon('heroicon-o-building-library')
                        ->schema([
                            Section::make(fn (Get $get) => self::meta($get)['nameRegister'])
                                ->description(fn (Get $get) => self::meta($get)['DescriptionRegister'])
                                ->icon('heroicon-o-flag')
                                ->columns(['default' => 1, 'sm' => 2, 'xl' => 4])
                                ->schema([
                                    Select::make('type')
                                        ->label('Program')
                                        ->options(Program::list()),
                                    Select::make('status_id')
                                        ->label('Status')
                                        ->preload()
                                        ->relationship(
                                            name: 'status',
                                            titleAttribute: 'name',
                                            modifyQueryUsing: fn (Builder $query) => $query
                                                ->orderBy('order')
                                        )
                                        ->searchable()
                                        ->placeholder('Pilih status...'),
                                    Select::make('periode')
                                        ->label('Periode')
                                        ->options(Periode::list()),
                                    TextInput::make('years')
                                        ->label('Tahun Ajaran')
                                        ->maxLength(255),
                                ]),

                            Section::make('Data Sekolah')
                                ->icon('heroicon-o-building-library')
                                ->columns(['default' => 1, 'sm' => 2])
                                ->schema([
                                    TextInput::make('schools')
                                        ->label('Nama Sekolah')
                                        ->maxLength(255)
                                        ->columnSpanFull(),
                                    TextInput::make('class')
                                        ->label('Kelas')
                                        ->maxLength(10),
                                    Select::make('education_level')
                                        ->label('Jenjang')
                                        ->options([
                                            'SD' => 'SD',
                                            'MI' => 'MI',
                                            'SMP' => 'SMP',
                                            'MTS' => 'MTS',
                                            'SMA' => 'SMA',
                                            'MA' => 'MA',
                                            'SMK' => 'SMK',
                                        ])
                                        ->native(false),
                                    Select::make('description')
                                        ->label('Keterangan')
                                        ->options([
                                            'ABK' => 'ABK',
                                            'NON-ABK' => 'NON ABK',
                                        ])
                                        ->native(false),
                                    Select::make('schools_type')
                                        ->label('Negeri / Swasta')
                                        ->options([
                                            'Negeri' => 'Negeri',
                                            'Swasta' => 'Swasta',
                                        ])
                                        ->native(false),
                                    TextInput::make('student_count')
                                        ->label('Jumlah Siswa')
                                        ->numeric(),
                                    ...SharedSchema::locationFields(),
                                ]),

                            Section::make('Kontak Sekolah')
                                ->icon('heroicon-o-user-group')
                                ->columns(['default' => 1, 'sm' => 2])
                                ->schema([
                                    TextInput::make('principal')
                                        ->label('Nama Kepala Sekolah')
                                        ->maxLength(255),
                                    TextInput::make('principal_phone')
                                        ->label('No Handphone Kepala Sekolah')
                                        ->telRegex($phoneRegex)
                                        ->maxLength(255),
                                    TextInput::make('curriculum_deputies')
                                        ->label('Wakakurikulum')
                                        ->maxLength(255),
                                    TextInput::make('curriculum_deputies_phone')
                                        ->label('No Handphone Wakakurikulum')
                                        ->telRegex($phoneRegex)
                                        ->maxLength(255),
                                    TextInput::make('counselor_coordinators')
                                        ->label('Koordinator BK')
                                        ->maxLength(255),
                                    TextInput::make('counselor_coordinators_phone')
                                        ->label('No Handphone Koordinator BK')
                                        ->telRegex($phoneRegex)
                                        ->maxLength(255),
                                    TextInput::make('proctors')
                                        ->label('Proktor')
                                        ->maxLength(255),
                                    TextInput::make('proctors_phone')
                                        ->label('No Handphone Proktor')
                                        ->telRegex($phoneRegex)
                                        ->maxLength(255),
                                ]),

                            Section::make('Pendaftaran')
                                ->icon('heroicon-o-calendar-days')
                                ->columns(['default' => 1, 'sm' => 2])
                                ->schema([
                                    DateTimePicker::make('date_register')
                                        ->label('Tanggal Pendaftaran')
                                        ->native(false)
                                        ->displayFormat('l, jS F Y H:i'),
                                    DateTimePicker::make('implementation_estimate')
                                        ->label('Estimasi Pelaksanaan')
                                        ->native(false)
                                        ->displayFormat('l, jS F Y H:i'),
                                ]),
                        ]),

                    Tab::make('Service')
                        ->icon('heroicon-o-wrench-screwdriver')
                        ->schema(FormSchema::serviceSections()),

                    Tab::make('Finance')
                        ->icon('heroicon-o-banknotes')
                        ->schema(FormSchema::financeSections()),
                ]),
        ];
    }

    /**
     * @return array{activities: array<mixed>, colorByState: array<string, string>, iconByState: array<string, string>}
     */
    public static function getActivityTimelineData(Model $record): array
    {
        $logs = RegistrationStatus::query()
            ->where('registration_id', $record->id)
            ->join('statuses', 'registration_statuses.status_id', '=', 'statuses.id')
            ->select('registration_statuses.*')
            ->with(['status:id,name,description,color,icon,order', 'user:id,name'])
            ->orderBy('registration_statuses.created_at')
            ->orderBy('statuses.order')
            ->orderBy('registration_statuses.id')
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

            $userName = $log->user?->name ? ' (oleh: '.e($log->user->name).')' : '';
            $title = "Status: <span class='font-semibold'>".e($status->name).'</span>'.$userName;
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

        return compact('activities', 'colorByState', 'iconByState');
    }

    /**
     * Build the activity timeline infolist for an action modal.
     */
    public static function buildActivityInfolist(Infolists\Infolist $infolist, Model $record): Infolists\Infolist
    {
        $data = self::getActivityTimelineData($record);

        return $infolist
            ->state(['activities' => $data['activities']])
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
                            ->icon(fn (?string $state) => $data['iconByState'][$state] ?? 'heroicon-m-clock')
                            ->animation(IconAnimation::Pulse)
                            ->color(fn (?string $state) => $data['colorByState'][$state] ?? 'gray'),
                    ]),
            ]);
    }

    public static function columns(): array
    {
        return SharedSchema::columns();
    }

    public static function infolist(Model $record): array
    {
        return InfolistSchema::compose(
            $record,
            [InfolistSchema::PART_SERVICE, InfolistSchema::PART_FINANCE, InfolistSchema::PART_DOCUMENTS],
            self::activityAction(),
        );
    }

    protected static function activityAction(): Action
    {
        return Action::make('viewActivityModal')
            ->label('Lihat Riwayat Activity')
            ->modalHeading('Riwayat Activity Sekolah')
            ->modalSubmitAction(false)
            ->modalCancelActionLabel('Tutup')
            ->modalWidth('xl')
            ->extraModalFooterActions([
                Action::make('openFullActivityPage')
                    ->label('Buka Halaman Full Activity')
                    ->icon('heroicon-m-arrow-top-right-on-square')
                    ->url(fn ($record) => ActivityResource::getUrl('activities', ['record' => $record]))
                    ->openUrlInNewTab(),
            ])
            ->infolist(fn (Infolists\Infolist $infolist, Model $record) => self::buildActivityInfolist($infolist, $record));
    }

    public static function filters(): array
    {
        return [
            Tables\Filters\Filter::make('date_filter')
                ->label('Filter Tanggal')
                ->form([
                    Select::make('date_field')
                        ->label('Kategori Tanggal')
                        ->options([
                            'date_register' => 'Tanggal Pendaftaran',
                            'implementation_estimate' => 'Tanggal Pelaksanaan',
                            'payment_date' => 'Tanggal Pembayaran',
                        ])
                        ->default('date_register')
                        ->columnSpan(2),
                    DatePicker::make('date_from')
                        ->label('Dari Tanggal')
                        ->native(false),
                    DatePicker::make('date_until')
                        ->label('Sampai Tanggal')
                        ->native(false),
                ])
                ->columns(2)
                ->query(function (Builder $query, array $data): Builder {
                    $field = $data['date_field'] ?? 'date_register';

                    if (! in_array($field, ['date_register', 'implementation_estimate', 'payment_date'], true)) {
                        $field = 'date_register';
                    }

                    return $query
                        ->when(
                            $data['date_from'],
                            fn (Builder $q, $date) => $q->whereDate($field, '>=', $date),
                        )
                        ->when(
                            $data['date_until'],
                            fn (Builder $q, $date) => $q->whereDate($field, '<=', $date),
                        );
                })
                ->indicateUsing(function (array $data): array {
                    $indicators = [];
                    $fieldLabels = [
                        'date_register' => 'Pendaftaran',
                        'implementation_estimate' => 'Pelaksanaan',
                        'payment_date' => 'Pembayaran',
                    ];
                    $fieldLabel = $fieldLabels[$data['date_field'] ?? 'date_register'] ?? 'Pendaftaran';

                    if ($data['date_from'] ?? null) {
                        $indicators[] = "Tgl {$fieldLabel} Dari: ".Carbon::parse($data['date_from'])->format('d/m/Y');
                    }
                    if ($data['date_until'] ?? null) {
                        $indicators[] = "Tgl {$fieldLabel} Sampai: ".Carbon::parse($data['date_until'])->format('d/m/Y');
                    }

                    return $indicators;
                }),
            Tables\Filters\SelectFilter::make('users')
                ->label('Salesforce')
                ->relationship('users', 'name')
                ->searchable()
                ->preload()
                ->indicator('Salesforce'),
            Tables\Filters\SelectFilter::make('type')
                ->label('Program')
                ->options(Program::list())
                ->preload()
                ->indicator('Program'),
            Tables\Filters\SelectFilter::make('periode')
                ->label('Periode')
                ->options(Periode::list())
                ->preload()
                ->indicator('Periode'),
            Tables\Filters\SelectFilter::make('status_color')
                ->label('Status Warna')
                ->options([
                    'red' => 'Merah',
                    'yellow' => 'Kuning',
                    'blue' => 'Biru',
                    'green' => 'Hijau',
                ])
                ->preload()
                ->indicator('Status Warna')
                ->query(function (Builder $query, array $data) {
                    if (empty($data['value'])) {
                        return;
                    }

                    $query->whereHas(
                        'status',
                        fn (Builder $q) => $q->where('color', $data['value'])
                    );
                }),
            Tables\Filters\SelectFilter::make('years')
                ->label('Tahun Ajaran')
                ->options(function () {
                    // Mengambil daftar tahun unik yang benar-benar ada di database
                    return RegistrationData::query() // Ganti Sales dengan nama Model Anda
                        ->whereNotNull('years')
                        ->distinct()
                        ->orderBy('years', 'desc')
                        ->pluck('years', 'years')
                        ->toArray();
                })
                ->searchable()
                ->preload()
                ->indicator('Tahun Ajaran'),
        ];
    }

    public static function actions(): array
    {
        return [
            Tables\Actions\ViewAction::make(),
            // Tables\Actions\EditAction::make(),
        ];
    }

    public static function bulkActions(): array
    {
        return [
            Tables\Actions\BulkActionGroup::make([
                Tables\Actions\DeleteBulkAction::make(),
                FilamentExportBulkAction::make('Export')
                    ->withColumns(self::TextColumns())
                    ->disableTableColumns()
                    ->formatStates([
                        'type' => fn (?Model $record) => strtoupper($record->type),
                    ]),
            ]),
        ];
    }

    public static function getRoles(): array
    {
        return ['admin'];
    }

    public static function TextColumns(): array
    {
        return [
            // --- Sales ---
            TextColumn::make('type')->label('Program'),
            TextColumn::make('periode')->label('Periode'),
            TextColumn::make('years')->label('Tahun'),
            TextColumn::make('date_register')
                ->label('Tanggal Pendaftaran')
                ->formatStateUsing(fn ($state) => $state ? Carbon::parse($state)->translatedFormat('l, j F Y H:i') : '-'),
            TextColumn::make('provinces')->label('Provinsi'),
            TextColumn::make('regencies')->label('Kota / Kabupaten'),
            TextColumn::make('area')->label('Wilayah'),
            TextColumn::make('district')->label('Kecamatan'),
            TextColumn::make('student_count')->label('Jumlah Siswa'),
            TextColumn::make('implementation_estimate')
                ->label('Estimasi Pelaksanaan')
                ->formatStateUsing(fn ($state) => $state ? Carbon::parse($state)->translatedFormat('l, j F Y H:i') : '-'),
            TextColumn::make('curriculum_deputies')->label('Wakakurikulum'),
            TextColumn::make('curriculum_deputies_phone')->label('No. HP Wakakurikulum'),
            TextColumn::make('counselor_coordinators')->label('Koordinator BK'),
            TextColumn::make('counselor_coordinators_phone')->label('No. HP Koordinator BK'),
            TextColumn::make('proctors')->label('Proktor'),
            TextColumn::make('proctors_phone')->label('No. HP Proktor'),

            TextColumn::make('schools')->label('Sekolah'),
            TextColumn::make('class')->label('Kelas'),
            TextColumn::make('education_level')->label('Jenjang'),
            TextColumn::make('description')->label('Keterangan'),
            TextColumn::make('schools_type')->label('Negeri / Swasta'),

            TextColumn::make('principal')->label('Kepala Sekolah'),
            TextColumn::make('principal_phone')->label('No. HP Kepala Sekolah'),

            // --- Akademik & Teknisi ---
            TextColumn::make('group')
                ->label('Tanggal Group')
                ->formatStateUsing(fn ($state) => $state ? Carbon::parse($state)->translatedFormat('l, j F Y') : '-')
                ->sortable(),
            TextColumn::make('bimtek')
                ->label('Tanggal Bimtek')
                ->formatStateUsing(fn ($state) => $state ? Carbon::parse($state)->translatedFormat('l, j F Y') : '-')
                ->sortable(),
            TextColumn::make('account_count_created')->label('Akun Dibuat'),
            TextColumn::make('implementer_count')->label('Jumlah Pelaksana'),
            TextColumn::make('difference')->label('Selisih'),
            TextColumn::make('students_download')->label('Unduhan Siswa'),
            TextColumn::make('schools_download')->label('Unduhan Sekolah'),
            TextColumn::make('pm')->label('PM'),
            TextColumn::make('counselor_consultation_date')
                ->label('Konsultasi BK')
                ->formatStateUsing(fn ($state) => $state ? Carbon::parse($state)->translatedFormat('l, j F Y') : '-'),
            TextColumn::make('student_consultation_date')
                ->label('Konsultasi Siswa')
                ->formatStateUsing(fn ($state) => $state ? Carbon::parse($state)->translatedFormat('l, j F Y') : '-'),

            // --- Finance (utama) ---
            TextColumn::make('price')->label('Harga'),
            TextColumn::make('total')->label('Total'),
            TextColumn::make('total_net')->label('Total Netto'),
            TextColumn::make('invoice_date')
                ->label('Tgl Invoice')
                ->formatStateUsing(fn ($state) => $state ? Carbon::parse($state)->translatedFormat('j F Y') : '-')
                ->sortable(),
            TextColumn::make('spk')
                ->label('Tgl SPK')
                ->formatStateUsing(fn ($state) => $state ? Carbon::parse($state)->translatedFormat('j F Y') : '-')
                ->sortable(),
            TextColumn::make('payment_date')
                ->label('Tgl Pembayaran')
                ->formatStateUsing(fn ($state) => $state ? Carbon::parse($state)->translatedFormat('j F Y') : '-')
                ->sortable(),
            TextColumn::make('payment_name')->label('Nama Pembayaran'),

            // --- Finance (Opsi, SS, Pajak) ---
            TextColumn::make('option_price')->label('Opsi Jumlah'),
            TextColumn::make('real_count')->label('Jumlah Siswa Real'),
            TextColumn::make('support_school')->label('SS (Support Sekolah)'),
            TextColumn::make('net_2')->label('Harga NET'),
            TextColumn::make('tax_rates')
                ->label('Pilihan Pajak')
                ->state(fn (Model $record): string => implode(', ', SharedSchema::taxRateLabels($record->tax_rates, $record->tax_custom_rate))),
            TextColumn::make('tax_amount')->label('Nominal Pajak'),
            TextColumn::make('invoice_amount')->label('Nominal Invoice'),

            // --- Invoice detail ---
            TextColumn::make('detail_invoice')->label('Detail Invoice'),
            TextColumn::make('number_invoice')->label('Nomor Invoice'),
            TextColumn::make('qty_invoice')->label('Qty Invoice'),
            TextColumn::make('unit_price')->label('Harga Satuan'),
            TextColumn::make('amount_invoice')->label('Jumlah Invoice'),
            TextColumn::make('ppn')->label('PPN'),
            TextColumn::make('pph')->label('PPH'),
            TextColumn::make('subtotal_invoice')->label('Subtotal Invoice'),
            TextColumn::make('total_invoice')->label('Total Invoice'),

            TextColumn::make('detail_kwitansi')->label('Detail Kwitansi'),
            TextColumn::make('difference_total')->label('Total Selisih'),

            TextColumn::make('subtotal_2')->label('Subtotal 2'),
        ];
    }
}
