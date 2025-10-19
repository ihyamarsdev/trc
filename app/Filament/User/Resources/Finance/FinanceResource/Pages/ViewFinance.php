<?php

namespace App\Filament\User\Resources\Finance\FinanceResource\Pages;

use id;
use Filament\Tables;
use Filament\Actions;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Illuminate\Support\Str;
use Filament\Actions\Action;
use App\Models\RegistrationData;
use Filament\Infolists\Infolist;
use Illuminate\Support\Facades\Log;
use App\Filament\Components\Finance;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Radio;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use App\Filament\User\Resources\Finance\FinanceResource;

class ViewFinance extends ViewRecord
{
    protected static string $resource = FinanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
                Actions\EditAction::make(),
                Tables\Actions\ActionGroup::make([
                    Action::make('spk_rasyidu')
                        ->label('SPK RASYIDUU')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->url(function (RegistrationData $record) {
                            $type = Str::of($record->type)->lower()->value();
                            // fallback kalau tipe tidak dikenal
                            if (! in_array($type, ['apps','anbk','snbt'], true)) {
                                $type = 'anbk';
                            }

                            $routeName = "rasyidu.{$type}.download";

                            // Opsional: jika takut route tidak ada, beri fallback:
                            if (! Route::has($routeName)) {
                                $routeName = 'rasyidu.anbk.download';
                            }

                            return route($routeName, $record);
                        })
                        ->openUrlInNewTab(),
                    Action::make('spk_edunesia')
                        ->label('SPK EDUNESIA')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->url(function (RegistrationData $record) {
                            $type = Str::of($record->type)->lower()->value();
                            // fallback kalau tipe tidak dikenal
                            if (! in_array($type, ['apps','anbk','snbt'], true)) {
                                $type = 'anbk';
                            }

                            $routeName = "edunesia.{$type}.download";

                            // Opsional: jika takut route tidak ada, beri fallback:
                            if (! Route::has($routeName)) {
                                $routeName = 'edunesia.anbk.download';
                            }

                            return route($routeName, $record);
                        })
                        ->openUrlInNewTab(),
                ])
                ->label('SPK')
                ->color('primary')
                ->button(),
                Tables\Actions\ActionGroup::make([
                    Action::make('form_kwitansi_rasyidu')
                                            ->label('Kwitansi RASYIDUU')
                                            ->icon('heroicon-o-arrow-down-tray')
                                            ->form([
                                                TextInput::make('schools')
                                                    ->label('Sekolah')
                                                    ->dehydrateStateUsing(fn (string $state): string => Str::upper($state))
                                                    ->default(fn (RegistrationData $record) => $record->schools)
                                                    ->readOnly(),
                                                TextInput::make('detail_kwitansi')
                                                    ->label('Guna Pembayaran')
                                                    ->dehydrateStateUsing(fn (string $state): string => Str::upper($state))
                                                    ->helperText('Contoh: 146 Paket Program TRY OUT Ujian Tertulis Berbasis Komputer (UTBK SNBT)'),
                                            ])
                                            ->action(function (RegistrationData $record, array $data) {
                                                try {
                                                    $record->detail_kwitansi = $data['detail_kwitansi'];
                                                    $record->save();

                                                    Notification::make()
                                                        ->title('Berhasil Download Kwitansi')
                                                        ->success()
                                                        ->send();
                                                } catch (\Throwable $th) {
                                                    Log::channel('download')->error('Download error', [
                                                        'message' => $th->getMessage(),
                                                        'exception' => get_class($th),
                                                        'file' => $th->getFile(),
                                                        'line' => $th->getLine(),
                                                        'code' => $th->getCode(),
                                                        'trace' => $th->getTraceAsString(),
                                                        'user_id' => Auth::id(),
                                                        'url' => request()->fullUrl(),
                                                    ]);

                                                    Notification::make()
                                                        ->title('Terjadi Error Download')
                                                        ->body('Silakan coba lagi atau hubungi admin.')
                                                        ->danger()
                                                        ->send();
                                                } finally {
                                                    redirect()->route('rasyidu.kwitansi.download', $record);
                                                }

                                            })
                                            ->openUrlInNewTab(),
                    Action::make('form_kwitansi_edunesia')
                                            ->label('Kwitansi EDUNESIA')
                                            ->icon('heroicon-o-arrow-down-tray')
                                            ->form([
                                                TextInput::make('schools')
                                                    ->label('Sekolah')
                                                    ->default(fn (RegistrationData $record) => $record->schools)
                                                    ->readOnly(),
                                                TextInput::make('detail_kwitansi')
                                                    ->label('Guna Pembayaran')
                                                    ->helperText('Contoh: 146 Paket Program TRY OUT Ujian Tertulis Berbasis Komputer (UTBK SNBT)'),
                                            ])
                                            ->action(function (RegistrationData $record, array $data) {
                                                try {
                                                    $record->detail_kwitansi = $data['detail_kwitansi'];
                                                    $record->save();

                                                    Notification::make()
                                                        ->title('Berhasil Download Kwitansi')
                                                        ->success()
                                                        ->send();
                                                } catch (\Throwable $th) {
                                                    Log::channel('download')->error('Download error', [
                                                        'message' => $th->getMessage(),
                                                        'exception' => get_class($th),
                                                        'file' => $th->getFile(),
                                                        'line' => $th->getLine(),
                                                        'code' => $th->getCode(),
                                                        'trace' => $th->getTraceAsString(),
                                                        'user_id' => Auth::id(),
                                                        'url' => request()->fullUrl(),
                                                    ]);

                                                    Notification::make()
                                                        ->title('Terjadi Error Download')
                                                        ->body('Silakan coba lagi atau hubungi admin.')
                                                        ->danger()
                                                        ->send();
                                                } finally {
                                                    redirect()->route('edunesia.kwitansi.download', $record);
                                                }
                                            })
                                            ->openUrlInNewTab(),
                ])
                ->label('Kwitansi')
                ->color('primary')
                ->button(),
                Tables\Actions\ActionGroup::make([
                    Action::make('form_invoice_rasyidu')
                                            ->label('Invoice RASYIDU')
                                            ->icon('heroicon-o-arrow-down-tray')
                                            ->form([
                                                Section::make('Invoice')
                                                    ->schema([
                                                        Fieldset::make('')
                                                            ->schema([
                                                                TextInput::make('schools')
                                                                    ->label('Sekolah')
                                                                    ->dehydrateStateUsing(fn (string $state): string => Str::upper($state))
                                                                    ->default(fn (RegistrationData $record) => $record->schools)
                                                                    ->readOnly(),
                                                                TextInput::make('number_invoice')
                                                                    ->prefix('#')
                                                                    ->label('Nomor Invoice')
                                                                    ->live()
                                                                    ->numeric(),
                                                                TextInput::make('detail_invoice')
                                                                    ->label('Deskripsi')
                                                                    ->dehydrateStateUsing(fn (string $state): string => Str::upper($state))
                                                                    ->helperText('Contoh: Try Out Asesmen Nasional (AKM)'),
                                                            ])->columns(1),

                                                        Fieldset::make('')
                                                            ->schema([
                                                                TextInput::make('qty_invoice')
                                                                    ->label('Jumlah')
                                                                    ->live(1000)
                                                                    ->numeric()
                                                                    ->afterStateUpdated(function (Get $get, Set $set) {
                                                                        $set('amount_invoice', abs((float) $get('qty_invoice') * (float) $get('unit_price')));
                                                                        $set('subtotal_invoice', abs((float) $get('amount_invoice')));
                                                                        $set('total_invoice', abs((float) $get('subtotal_invoice')));
                                                                    }),
                                                                TextInput::make('unit_price')
                                                                    ->label('Harga Satuan')
                                                                    ->prefix('Rp')
                                                                    ->live(1000)
                                                                    ->currencyMask(thousandSeparator: ',', decimalSeparator: '.', precision: 0)
                                                                    ->afterStateUpdated(function (Get $get, Set $set) {
                                                                        $set('subtotal_invoice', abs((float) $get('qty_invoice') * (float) $get('unit_price')));
                                                                        $set('total_invoice', abs((float) $get('subtotal_invoice')));
                                                                    }),
                                                                TextInput::make('subtotal_invoice')
                                                                    ->label('Sub Total')
                                                                    ->prefix('Rp')
                                                                    ->currencyMask(thousandSeparator: ',', decimalSeparator: '.', precision: 0)
                                                                    ->numeric()
                                                                    ->readOnly(),

                                                            ])->columns(2),

                                                        Fieldset::make('Pajak')
                                                            ->schema([
                                            Radio::make('option_tax')
                                                                    ->label('Pilih Opsi Pajak')
                                                                    ->options([
                                                                        '0.02' => 'PPH',
                                                                        '0.11' => 'PPN',
                                                                        '0' => 'None',
                                                                    ])
                                                                    ->live(1000)
                                                                    ->reactive()
                                                                    ->afterStateUpdated(function (Get $get, Set $set) {
                                                                        $set('total_invoice', (float) $get('subtotal_invoice') + ((float) $get('subtotal_invoice') * (float) $get('option_tax')));
                                                                        if ($get('option_tax') == '0.02') {
                                                                            $set('ppn', "2");
                                                                            $set('pph', "0");
                                                                        } elseif ($get('option_tax') == '0.11') {
                                                                            $set('ppn', "0");
                                                                            $set('pph', "11");
                                                                        } elseif ($get('option_tax') == '0') {
                                                                            $set('ppn', "0");
                                                                            $set('pph', "0");
                                                                        }
                                                                    }),
                                            Group::make([
                                                                    TextInput::make('ppn')
                                                                        ->label('PPN')
                                                                        ->suffix('%')
                                                                        ->readOnly()
                                                                        ->live(1000),
                                                                    TextInput::make('pph')
                                                                        ->label('PPH')
                                                                        ->suffix('%')
                                                                        ->readOnly()
                                                                        ->live(1000),
                                            ]),

                                        ])->columns(2),

                                                        Fieldset::make('')
                                                            ->schema([
                                                                TextInput::make('total_invoice')
                                                                    ->label('Total')
                                                                    ->prefix('Rp')
                                                                    ->currencyMask(thousandSeparator: ',', decimalSeparator: '.', precision: 0)
                                                                    ->readOnly(),
                                        ])->columns(2),
                                                ])
                                            ])
                                            ->action(function (RegistrationData $record, array $data) {
                                                try {
                                                    $record->number_invoice = $data['number_invoice'];
                                                    $record->detail_invoice = $data['detail_invoice'];
                                                    $record->qty_invoice = $data['qty_invoice'];
                                                    $record->unit_price = $data['unit_price'];
                                                    $record->amount_invoice = $data['amount_invoice'];
                                                    $record->ppn = $data['ppn'];
                                                    $record->pph = $data['pph'];
                                                    $record->subtotal_invoice = $data['subtotal_invoice'];
                                                    $record->total_invoice = $data['total_invoice'];
                                                    $record->save();

                                                    Notification::make()
                                                        ->title('Berhasil Download Invoice')
                                                        ->success()
                                                        ->send();
                                                } catch (\Throwable $th) {
                                                    Log::channel('download')->error('Download error', [
                                                        'message' => $th->getMessage(),
                                                        'exception' => get_class($th),
                                                        'file' => $th->getFile(),
                                                        'line' => $th->getLine(),
                                                        'code' => $th->getCode(),
                                                        'trace' => $th->getTraceAsString(),
                                                        'user_id' => Auth::id(),
                                                        'url' => request()->fullUrl(),
                                                    ]);

                                                    Notification::make()
                                                        ->title('Terjadi Error Download')
                                                        ->body('Silakan coba lagi atau hubungi admin.')
                                                        ->danger()
                                                        ->send();
                                                } finally {
                                                    redirect()->route('rasyidu.invoice.download', $record);
                                                }
                                            })
                                            ->openUrlInNewTab(),
                    Action::make('form_invoice_edunesia')
                                            ->label('Invoice EDUNESIA')
                                            ->icon('heroicon-o-arrow-down-tray')
                                            ->form([
                                                Section::make('Invoice')
                                                    ->schema([
                                                        Fieldset::make('')
                                                            ->schema([
                                                                TextInput::make('schools')
                                                                    ->label('Sekolah')
                                                                    ->dehydrateStateUsing(fn (string $state): string => Str::upper($state))
                                                                    ->default(fn (RegistrationData $record) => $record->schools)
                                                                    ->readOnly(),
                                                                TextInput::make('number_invoice')
                                                                    ->prefix('#')
                                                                    ->label('Nomor Invoice')
                                                                    ->live()
                                                                    ->numeric(),
                                                                TextInput::make('detail_invoice')
                                                                    ->label('Deskripsi')
                                                                    ->helperText('Contoh: Try Out Asesmen Nasional (AKM)'),
                                                            ])->columns(1),

                                                        Fieldset::make('')
                                                            ->schema([
                                                                TextInput::make('qty_invoice')
                                                                    ->label('Jumlah')
                                                                    ->live(1000)
                                                                    ->numeric()
                                                                    ->afterStateUpdated(function (Get $get, Set $set) {
                                                                        $set('amount_invoice', abs((float) $get('qty_invoice') * (float) $get('unit_price')));
                                                                        $set('subtotal_invoice', abs((float) $get('amount_invoice')));
                                                                        $set('total_invoice', abs((float) $get('subtotal_invoice')));
                                                                    }),
                                                                TextInput::make('unit_price')
                                                                    ->label('Harga Satuan')
                                                                    ->prefix('Rp')
                                                                    ->live(1000)
                                                                    ->currencyMask(thousandSeparator: ',', decimalSeparator: '.', precision: 0)
                                                                    ->afterStateUpdated(function (Get $get, Set $set) {
                                                                        $set('subtotal_invoice', abs((float) $get('qty_invoice') * (float) $get('unit_price')));
                                                                        $set('total_invoice', abs((float) $get('subtotal_invoice')));
                                                                    }),
                                                                TextInput::make('subtotal_invoice')
                                                                    ->label('Sub Total')
                                                                    ->prefix('Rp')
                                                                    ->currencyMask(thousandSeparator: ',', decimalSeparator: '.', precision: 0)
                                                                    ->numeric()
                                                                    ->readOnly(),

                                                            ])->columns(2),

                                                        Fieldset::make('Pajak')
                                                            ->schema([
                                            Radio::make('option_tax')
                                                                    ->label('Pilih Opsi Pajak')
                                                                    ->options([
                                                                        '0.02' => 'PPH',
                                                                        '0.11' => 'PPN',
                                                                        '0' => 'None',
                                                                    ])
                                                                    ->live(1000)
                                                                    ->reactive()
                                                                    ->afterStateUpdated(function (Get $get, Set $set) {
                                                                        $set('total_invoice', (float) $get('subtotal_invoice') + ((float) $get('subtotal_invoice') * (float) $get('option_tax')));
                                                                        if ($get('option_tax') == '0.02') {
                                                                            $set('ppn', "2");
                                                                            $set('pph', "0");
                                                                        } elseif ($get('option_tax') == '0.11') {
                                                                            $set('ppn', "0");
                                                                            $set('pph', "11");
                                                                        } elseif ($get('option_tax') == '0') {
                                                                            $set('ppn', "0");
                                                                            $set('pph', "0");
                                                                        }
                                                                    }),
                                            Group::make([
                                                                    TextInput::make('ppn')
                                                                        ->label('PPN')
                                                                        ->suffix('%')
                                                                        ->readOnly()
                                                                        ->live(1000),
                                                                    TextInput::make('pph')
                                                                        ->label('PPH')
                                                                        ->suffix('%')
                                                                        ->readOnly()
                                                                        ->live(1000),
                                            ]),

                                        ])->columns(2),

                                                        Fieldset::make('')
                                                            ->schema([
                                                    TextInput::make('total_invoice')
                                                                            ->label('Total')
                                                                            ->prefix('Rp')
                                                                            ->currencyMask(thousandSeparator: ',', decimalSeparator: '.', precision: 0)
                                                                            ->readOnly(),
                                        ])->columns(2),
                                                ])
                                            ])
                                            ->action(function (RegistrationData $record, array $data) {
                                                try {
                                                    $record->number_invoice = $data['number_invoice'];
                                                    $record->detail_invoice = $data['detail_invoice'];
                                                    $record->qty_invoice = $data['qty_invoice'];
                                                    $record->unit_price = $data['unit_price'];
                                                    $record->amount_invoice = $data['amount_invoice'];
                                                    $record->ppn = $data['ppn'];
                                                    $record->pph = $data['pph'];
                                                    $record->subtotal_invoice = $data['subtotal_invoice'];
                                                    $record->total_invoice = $data['total_invoice'];
                                                    $record->save();

                                                    Notification::make()
                                                        ->title('Berhasil Download Invoice')
                                                        ->success()
                                                        ->send();
                                                } catch (\Throwable $th) {
                                                    Log::channel('download')->error('Download error', [
                                                        'message' => $th->getMessage(),
                                                        'exception' => get_class($th),
                                                        'file' => $th->getFile(),
                                                        'line' => $th->getLine(),
                                                        'code' => $th->getCode(),
                                                        'trace' => $th->getTraceAsString(),
                                                        'user_id' => Auth::id(),
                                                        'url' => request()->fullUrl(),
                                                    ]);

                                                    Notification::make()
                                                        ->title('Terjadi Error Download')
                                                        ->body('Silakan coba lagi atau hubungi admin.')
                                                        ->danger()
                                                        ->send();
                                                } finally {
                                                    redirect()->route('edunesia.invoice.download', $record);
                                                }
                                            })
                                            ->openUrlInNewTab()
                ])
                ->label('Invoice')
                ->color('primary')
                ->button(),
            ];
    }

    public function mount(int | string $record): void
    {
        $this->record = $this->resolveRecord($record);
    }

    public function infolist(Infolist $infolist): Infolist
    {
        $record = $this->record;
        return $infolist
            ->schema(Finance::infolist(record: $record));
    }
}
