<?php

namespace App\Filament\User\Resources\Datacenter\Monitoring;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use App\Models\AnbkJumsis;
use App\Models\SchoolYear;
use Filament\Tables\Table;
use App\Models\RegistrationData;
use Filament\Resources\Resource;
use Filament\Tables\Grouping\Group;
use Illuminate\Support\Facades\Auth;
use App\Filament\Components\Datacenter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\Summarizers\Count;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\User\Resources\AnbkJumsisResource\RelationManagers;
use App\Filament\User\Resources\Datacenter\Monitoring\AnbkJumsisResource\Pages;

class AnbkJumsisResource extends Resource
{
    protected static ?string $model = RegistrationData::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Data Kuning';
    protected static ?string $title = 'Jumlah Siswa Per SF';
    protected static ?string $navigationLabel = 'ANBK';
    protected static ?string $modelLabel = 'Jumlah Siswa Per SF';
    protected static ?string $slug = 'anbk-jumsis';
    protected static bool $shouldRegisterNavigation = true;

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
            ->deferLoading()
            ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'anbk')->orderBy('date_register', 'asc'))
            ->columns([
                Tables\Columns\TextColumn::make('users.name')
                    ->label('User'),
                Tables\Columns\TextColumn::make('schools')
                    ->label('Sekolah')
                    ->summarize(Count::make()->label('')),
                Tables\Columns\TextColumn::make('student_count')
                    ->label('Jumlah Siswa')
                    ->summarize(Sum::make()->label('')),
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
                Tables\Filters\SelectFilter::make('school_years_id')
                    ->label('Tahun Ajaran')
                    ->options(SchoolYear::all()->pluck('name', 'id'))
                    ->preload()
                    ->searchable(),
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
                    ->titlePrefixedWithLabel(false),
            ])
            ->defaultGroup('users.name')
            ->groupingSettingsHidden()
            ->groupsOnly();
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
            'index' => Pages\ListAnbkJumses::route('/'),
        ];
    }
}
