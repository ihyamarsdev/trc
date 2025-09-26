<?php

namespace App\Filament\User\Resources\Finance\FinanceResource\Pages;

use Filament\Actions;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Actions\Action;
use App\Models\RegistrationData;
use Filament\Infolists\Infolist;
use App\Filament\Components\Finance;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use App\Filament\User\Resources\Finance\FinanceResource;
use Filament\Tables;

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
                        ->url(fn (RegistrationData $record) => route('rasyidu.anbk.download', $record))
                        ->openUrlInNewTab(),
                    Action::make('spk_edunesia')
                        ->label('SPK EDUNESIA')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->url(fn (RegistrationData $record) => route('edunesia.anbk.download', $record))
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
                                Notification::make()
                                    ->title('Terjadi Error ' . $th->getMessage())
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
                                Notification::make()
                                    ->title('Terjadi Error ' . $th->getMessage())
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
                                                ->label('Quantity')
                                                ->live(1000)
                                                ->numeric()
                                                ->afterStateUpdated(function (Get $get, Set $set) {
                                                    $set('amount_invoice', abs((float) $get('qty_invoice') * (float) $get('unit_price')));
                                                    $set('subtotal_invoice', abs((float) $get('amount_invoice')));
                                                    $set('total_invoice', abs((float) $get('subtotal_invoice')));
                                                }),
                                            TextInput::make('unit_price')
                                                ->label('Unit Price')
                                                ->prefix('Rp')
                                                ->live(1000)
                                                ->currencyMask(thousandSeparator: ',', decimalSeparator: '.', precision: 0)
                                                ->afterStateUpdated(function (Get $get, Set $set) {
                                                    $set('amount_invoice', abs((float) $get('qty_invoice') * (float) $get('unit_price')));
                                                    $set('subtotal_invoice', abs((float) $get('amount_invoice')));
                                                    $set('total_invoice', abs((float) $get('subtotal_invoice')));
                                                }),
                                            TextInput::make('amount_invoice')
                                                ->label('Amount')
                                                ->prefix('Rp')
                                                ->currencyMask(thousandSeparator: ',', decimalSeparator: '.', precision: 0)
                                                ->numeric()
                                                ->readOnly(),
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
                                                    '0.02' => 'PPH 23',
                                                    '0.11' => 'PPN',
                                                    '0' => 'None',
                                                ])
                                                ->live(1000)
                                                ->reactive()
                                                ->afterStateUpdated(function (Get $get, Set $set) {
                                                    $set('total_invoice', (float) $get('subtotal_invoice') + ((float) $get('subtotal_invoice') * (float) $get('option_tax')));
                                                    if ($get('option_tax') == '0.02') {
                                                        $set('tax_rate', "2");
                                                        $set('sales_tsx', "0");
                                                    } elseif ($get('option_tax') == '0.11') {
                                                        $set('tax_rate', "0");
                                                        $set('sales_tsx', "11");
                                                    } elseif ($get('option_tax') == '0') {
                                                        $set('tax_rate', "0");
                                                        $set('sales_tsx', "0");
                                                    }
                                                }),
                                            Group::make([
                                                TextInput::make('tax_rate')
                                                    ->label('PPH 23')
                                                    ->suffix('%')
                                                    ->readOnly()
                                                    ->live(1000),
                                                TextInput::make('sales_tsx')
                                                    ->label('PPN')
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
                                $record->tax_rate = $data['tax_rate'];
                                $record->sales_tsx = $data['sales_tsx'];
                                $record->subtotal_invoice = $data['subtotal_invoice'];
                                $record->total_invoice = $data['total_invoice'];
                                $record->save();

                                Notification::make()
                                    ->title('Berhasil Download Invoice')
                                    ->success()
                                    ->send();
                            } catch (\Throwable $th) {
                                Notification::make()
                                    ->title('Terjadi Error ' . $th->getMessage())
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
                                                ->label('Quantity')
                                                ->live(1000)
                                                ->numeric()
                                                ->afterStateUpdated(function (Get $get, Set $set) {
                                                    $set('amount_invoice', abs((float) $get('qty_invoice') * (float) $get('unit_price')));
                                                    $set('subtotal_invoice', abs((float) $get('amount_invoice')));
                                                    $set('total_invoice', abs((float) $get('subtotal_invoice')));
                                                }),
                                            TextInput::make('unit_price')
                                                ->label('Unit Price')
                                                ->prefix('Rp')
                                                ->live(1000)
                                                ->currencyMask(thousandSeparator: ',', decimalSeparator: '.', precision: 0)
                                                ->afterStateUpdated(function (Get $get, Set $set) {
                                                    $set('amount_invoice', abs((float) $get('qty_invoice') * (float) $get('unit_price')));
                                                    $set('subtotal_invoice', abs((float) $get('amount_invoice')));
                                                    $set('total_invoice', abs((float) $get('subtotal_invoice')));
                                                }),
                                            TextInput::make('amount_invoice')
                                                ->label('Amount')
                                                ->prefix('Rp')
                                                ->currencyMask(thousandSeparator: ',', decimalSeparator: '.', precision: 0)
                                                ->numeric()
                                                ->readOnly(),
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
                                                    '0.02' => 'PPH 23',
                                                    '0.11' => 'PPN',
                                                    '0' => 'None',
                                                ])
                                                ->live(1000)
                                                ->reactive()
                                                ->afterStateUpdated(function (Get $get, Set $set) {
                                                    $set('total_invoice', (float) $get('subtotal_invoice') + ((float) $get('subtotal_invoice') * (float) $get('option_tax')));
                                                    if ($get('option_tax') == '0.02') {
                                                        $set('tax_rate', "2");
                                                        $set('sales_tsx', "0");
                                                    } elseif ($get('option_tax') == '0.11') {
                                                        $set('tax_rate', "0");
                                                        $set('sales_tsx', "11");
                                                    } elseif ($get('option_tax') == '0') {
                                                        $set('tax_rate', "0");
                                                        $set('sales_tsx', "0");
                                                    }
                                                }),
                                            Group::make([
                                                TextInput::make('tax_rate')
                                                    ->label('PPH 23')
                                                    ->suffix('%')
                                                    ->readOnly()
                                                    ->live(1000),
                                                TextInput::make('sales_tsx')
                                                    ->label('PPN')
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
                                $record->tax_rate = $data['tax_rate'];
                                $record->sales_tsx = $data['sales_tsx'];
                                $record->subtotal_invoice = $data['subtotal_invoice'];
                                $record->total_invoice = $data['total_invoice'];
                                $record->save();

                                Notification::make()
                                    ->title('Berhasil Download Invoice')
                                    ->success()
                                    ->send();
                            } catch (\Throwable $th) {
                                Notification::make()
                                    ->title('Terjadi Error ' . $th->getMessage())
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
