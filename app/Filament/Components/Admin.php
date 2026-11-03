<?php

namespace App\Filament\Components;

use App\Filament\Components\Support\ExportBulkAction;
use App\Filament\Components\Support\FormSchema;
use App\Filament\Components\Support\HasProgramMetadata;
use App\Filament\Components\Support\InfolistSchema;
use App\Filament\Components\Support\SharedSchema;
use App\Filament\Components\Support\StatusPalette;
use App\Filament\Enum\OptionGroup;
use App\Filament\User\Resources\Activity\ActivityResource;
use App\Models\Option;
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
use Illuminate\Support\Str;
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
                                    FormSchema::optionSelect('type', 'Program', OptionGroup::Program),
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
                                    FormSchema::optionSelect('periode', 'Periode', OptionGroup::Periode),
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
                                    FormSchema::optionSelect('education_level', 'Jenjang', OptionGroup::Jenjang),
                                    FormSchema::optionSelect('description', 'Keterangan', OptionGroup::Keterangan),
                                    FormSchema::optionSelect('schools_type', 'Negeri / Swasta', OptionGroup::SchoolType),
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
                        ->columnSpan(['sm' => 2]),
                    DatePicker::make('date_from')
                        ->label('Dari Tanggal')
                        ->native(false),
                    DatePicker::make('date_until')
                        ->label('Sampai Tanggal')
                        ->native(false),
                ])
                ->columns(['default' => 1, 'sm' => 2])
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
                ->options(Option::list(OptionGroup::Program, onlyActive: false))
                ->preload()
                ->indicator('Program'),
            Tables\Filters\SelectFilter::make('periode')
                ->label('Periode')
                ->options(Option::list(OptionGroup::Periode, onlyActive: false))
                ->preload()
                ->indicator('Periode'),
            Tables\Filters\SelectFilter::make('status_color')
                ->label('Status Warna')
                ->multiple()
                ->options([
                    'red' => 'Merah',
                    'yellow' => 'Kuning',
                    'blue' => 'Biru',
                    'green' => 'Hijau',
                ])
                ->preload()
                ->indicator('Status Warna')
                ->query(function (Builder $query, array $data) {
                    if (empty($data['values'])) {
                        return;
                    }

                    $query->whereHas(
                        'status',
                        fn (Builder $q) => $q->whereIn('color', $data['values'])
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
                ExportBulkAction::make('Export')
                    ->groupedColumns(self::exportColumnGroups())
                    ->disableTableColumns(),
            ]),
        ];
    }

    public static function getRoles(): array
    {
        return ['admin'];
    }

    /**
     * Export columns per group, in the order they appear in the export modal and the file.
     *
     * @return array<string, array<int, TextColumn>>
     */
    public static function exportColumnGroups(): array
    {
        $date = fn (string $name, string $label, string $format = 'l, j F Y'): TextColumn => TextColumn::make($name)
            ->label($label)
            ->formatStateUsing(fn ($state) => $state ? Carbon::parse($state)->translatedFormat($format) : '-');

        return [
            'Pendaftaran' => [
                self::optionColumn('type', 'Program', OptionGroup::Program, upper: true),
                self::optionColumn('periode', 'Periode', OptionGroup::Periode, upper: true),
                TextColumn::make('years')->label('Tahun'),
                TextColumn::make('users.name')->label('Salesforce'),
                TextColumn::make('status.name')->label('Status'),
                $date('date_register', 'Tanggal Pendaftaran', 'l, j F Y H:i'),
                $date('implementation_estimate', 'Estimasi Pelaksanaan', 'l, j F Y H:i'),
                TextColumn::make('student_count')->label('Jumlah Siswa'),
                TextColumn::make('notes')->label('Catatan'),
            ],
            'Sekolah & Lokasi' => [
                TextColumn::make('schools')->label('Sekolah'),
                TextColumn::make('class')->label('Kelas'),
                self::optionColumn('education_level', 'Jenjang', OptionGroup::Jenjang),
                self::optionColumn('description', 'Keterangan', OptionGroup::Keterangan),
                self::optionColumn('schools_type', 'Negeri / Swasta', OptionGroup::SchoolType),
                TextColumn::make('provinces')->label('Provinsi'),
                TextColumn::make('regencies')->label('Kota / Kabupaten'),
                TextColumn::make('area')->label('Wilayah'),
                TextColumn::make('district')->label('Kecamatan'),
            ],
            'Kontak Sekolah' => [
                TextColumn::make('principal')->label('Kepala Sekolah'),
                TextColumn::make('principal_phone')->label('No. HP Kepala Sekolah'),
                TextColumn::make('curriculum_deputies')->label('Wakakurikulum'),
                TextColumn::make('curriculum_deputies_phone')->label('No. HP Wakakurikulum'),
                TextColumn::make('counselor_coordinators')->label('Koordinator BK'),
                TextColumn::make('counselor_coordinators_phone')->label('No. HP Koordinator BK'),
                TextColumn::make('proctors')->label('Proktor'),
                TextColumn::make('proctors_phone')->label('No. HP Proktor'),
            ],
            'Service' => [
                TextColumn::make('account_count_created')->label('Jumlah Akun Dibuat'),
                TextColumn::make('implementer_count')->label('Jumlah Akun Pelaksanaan'),
                TextColumn::make('difference')->label('Selisih'),
                $date('group', 'Grup'),
                $date('bimtek', 'Bimtek'),
                $date('counselor_consultation_date', 'Konsul BK'),
                $date('student_consultation_date', 'Konsul Siswa'),
                TextColumn::make('schools_download')->label('Download Sekolah'),
                TextColumn::make('students_download')->label('Download Siswa'),
                TextColumn::make('pm')->label('PM'),
            ],
            'Finance: Nominal & Pajak' => [
                TextColumn::make('option_price')
                    ->label('Dasar Jumlah')
                    ->formatStateUsing(fn (?string $state): string => InfolistSchema::optionLabel($state)),
                TextColumn::make('real_count')->label('Jumlah Siswa Real'),
                TextColumn::make('price')->label('Harga SPJ'),
                TextColumn::make('support_school')->label('SS (Support Sekolah)'),
                TextColumn::make('net_2')->label('Harga NET'),
                TextColumn::make('tax_rates')
                    ->label('Pilihan Pajak')
                    ->state(fn (Model $record): string => implode(', ', SharedSchema::taxRateLabels($record->tax_rates, $record->tax_custom_rate))),
                TextColumn::make('tax_amount')->label('Nominal Pajak'),
                TextColumn::make('total')->label('Total Dana Sesuai SPJ'),
                TextColumn::make('total_received')->label('Total Uang Masuk Rekening'),
                TextColumn::make('total_support_school')->label('Realisasi SS'),
                $date('support_school_date', 'Tgl SS Dikirim', 'j F Y'),
                TextColumn::make('total_net')->label('Total Net'),
            ],
            'Finance: Invoice & Pembayaran' => [
                $date('invoice_date', 'Tanggal Invoice', 'j F Y'),
                TextColumn::make('invoice_amount')->label('Nominal Invoice'),
                self::optionColumn('payment_name', 'Pembayaran Via', OptionGroup::PaymentVia),
                self::optionColumn('bank_name', 'Bank', OptionGroup::Bank),
                $date('payment_date', 'Jadwal Pembayaran', 'j F Y'),
                $date('spk', 'Jadwal SPK', 'j F Y'),
            ],
            'Dokumen (Kwitansi & Invoice)' => [
                TextColumn::make('detail_kwitansi')->label('Detail Kwitansi'),
                TextColumn::make('number_invoice')->label('Nomor Invoice'),
                TextColumn::make('detail_invoice')->label('Detail Invoice'),
                TextColumn::make('qty_invoice')->label('Qty Invoice'),
                TextColumn::make('unit_price')->label('Harga Satuan'),
                TextColumn::make('amount_invoice')->label('Jumlah Invoice'),
                TextColumn::make('pph')->label('PPH'),
                TextColumn::make('ppn')->label('PPN'),
                TextColumn::make('subtotal_invoice')->label('Subtotal Invoice'),
                TextColumn::make('total_invoice')->label('Total Invoice'),
            ],
        ];
    }

    /**
     * Column that exports the label of the stored master-data choice, like the table and infolist show it.
     */
    private static function optionColumn(string $name, string $label, OptionGroup $group, bool $upper = false): TextColumn
    {
        return TextColumn::make($name)
            ->label($label)
            ->formatStateUsing(function (?string $state) use ($group, $upper): string {
                $text = Option::label($group, $state) ?? (string) $state;

                return $upper ? Str::upper($text) : $text;
            });
    }
}
