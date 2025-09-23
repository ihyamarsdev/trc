<?php

namespace App\Filament\User\Resources\Datacenter\Monitoring;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\RegistrationData;
use Filament\Resources\Resource;
use Filament\Tables\Grouping\Group;
use Illuminate\Support\Facades\Auth;
use App\Filament\Components\Datacenter;
use App\Models\ServiceProgressDatacenter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\User\Resources\ServiceProgressDatacenterResource\RelationManagers;
use App\Filament\User\Resources\Datacenter\Monitoring\ServiceProgressDatacenterResource\Pages;

class ServiceProgressDatacenterResource extends Resource
{
    protected static ?string $model = RegistrationData::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Progres Layanan';
    protected static ?string $title = 'Progres Layanan';
    protected static ?string $navigationLabel = 'Per Salesforce';
    protected static ?string $modelLabel = 'Progres Layanan Per Salesforce';
    protected static ?string $slug = 'services-progress';
    protected static ?int $navigationSort = 16;
    protected static bool $shouldRegisterNavigation = false;

    public static function canViewAny(): bool
    {
        return Auth::user()->hasRole(Datacenter::getRoles());
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('users.name')
                    ->label('User Salesforce')
                    ->searchable(),
                Tables\Columns\TextColumn::make('date_register')
                    ->label('Tanggal Pendaftaran')
                    ->date(),
                Tables\Columns\TextColumn::make('type')
                    ->label('Program'),
                Tables\Columns\TextColumn::make('implementation_estimate')
                    ->label('Estimasi Pelaksanaan')
                    ->date(),
                Tables\Columns\TextColumn::make('student_count')
                    ->label('Estimasi Jumlah Siswa')
                    ->numeric(),
                Tables\Columns\TextColumn::make('group')
                    ->label('Grup')
                    ->date(),
                Tables\Columns\TextColumn::make('bimtek')
                    ->label('Bimtek')
                    ->date(),
                Tables\Columns\TextColumn::make('implementer_count')
                    ->label('Pelaksanaan'),
                Tables\Columns\IconColumn::make('pm')
                    ->label('PM')
                    ->icon(fn (string $state): string => match ($state) {
                        'ya' => 'heroicon-s-check-circle',
                        'tidak' => 'heroicon-s-x-circle',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'ya' => 'success',
                        'tidak' => 'warning',
                    }),
                Tables\Columns\TextColumn::make('invoice_date')
                    ->label('Invoice')
                    ->date(),
                Tables\Columns\TextColumn::make('payment')
                    ->label('Pembayaran Via'),
                Tables\Columns\TextColumn::make('spk_sent')
                    ->label('SPK di Kirim')
                    ->date(),
                Tables\Columns\TextColumn::make('cb')
                    ->label('CB'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('periode')
                    ->label('Periode')
                    ->options([
                        'Januari - Juni' => 'Januari - Juni',
                        'Juli - Desember' => 'Juli - Desember',
                    ])
                    ->preload()
                    ->indicator('Periode'),
                Tables\Filters\SelectFilter::make('type')
                    ->label('Program')
                    ->options([
                        'anbk' => 'ANBK',
                        'apps' => 'APPS',
                        'snbt' => 'SNBT',
                    ])
                    ->preload()
                    ->indicator('Periode'),
                Tables\Filters\QueryBuilder::make()
                    ->constraints([
                        Tables\Filters\QueryBuilder\Constraints\DateConstraint::make('date_register')
                            ->label('Tanggal Pendaftaran'),
                    ]),
            ])
            ->actions([
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->groups([
                Group::make('users.name')
                    ->collapsible()
                    ->titlePrefixedWithLabel(false)
                    ->orderQueryUsing(
                        fn (Builder $query, string $direction) => $query->orderBy('date_register', 'asc')
                    ),
            ])
            ->defaultGroup('users.name');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListServiceProgressDatacenters::route('/'),
        ];
    }
}
