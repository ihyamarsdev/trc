<?php

namespace App\Filament\User\Resources\Finance\Forms;


use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Database\Eloquent\Builder;



class FinanceForm
{
    public static function schema(): array
    {
         return [
            Section::make("Status")
                ->schema([
                    Select::make("status_id")
                        ->label("Status")
                        ->preload()
                        ->relationship(
                            name: "status",
                            titleAttribute: "name",
                            modifyQueryUsing: fn(Builder $query) => $query
                                ->where("order", ">", 9)
                                ->orderBy("order"),
                        )
                        ->searchable()
                        ->placeholder("Pilih status...")
                        ->columnSpan(1),
                ])
                ->columns(2),
            Section::make()
                ->description()
                ->schema([
                    Fieldset::make("")->schema([
                        TextInput::make("account_count_created")
                            ->label("Jumlah Akun Dibuat")
                            ->disabled(),
                        TextInput::make("implementer_count")
                            ->label("Jumlah Pelaksanaan")
                            ->disabled(),
                    ]),

                    Fieldset::make("Nominal")->schema([
                        TextInput::make("price")
                            ->label("Harga SPJ")
                            ->prefix("Rp")
                            ->live(1000)
                            ->reactive()
                            ->currencyMask(
                                thousandSeparator: ",",
                                decimalSeparator: ".",
                                precision: 0,
                            )
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                $price = (float) $get("price");
                                $net = 0;

                                if ($price >= 0 && $price <= 100000) {
                                    $net = 25000;
                                } elseif ($price > 100000 && $price <= 150000) {
                                    $net = 50000;
                                } elseif ($price > 150000 && $price <= 200000) {
                                    $net = 75000;
                                } elseif ($price > 200000) {
                                    $net = 100000;
                                }

                                $set("net", $net);
                                $set("mitra_net", $net);
                                $set("ss_net", $net);
                                $set("dll_net", $net);
                            }),
                        TextInput::make("net_2")
                            ->label("Harga NET")
                            ->prefix("Rp")
                            ->live(1000)
                            ->reactive()
                            ->currencyMask(
                                thousandSeparator: ",",
                                decimalSeparator: ".",
                                precision: 0,
                            )
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                $set(
                                    "total_net",
                                    abs(
                                        (float) $get("implementer_count") *
                                        (float) $get("net_2"),
                                    ),
                                );
                            }),
                    ]),

                    Fieldset::make("Opsi")->schema([
                        Radio::make("option_price")
                            ->label("Pilih Opsi")
                            ->options(function (Get $get): array {
                                return [
                                    "implementer_" .
                                    $get(
                                        "implementer_count",
                                    ) => "JUMLAH PELAKSANAAN",
                                    "account_" .
                                    $get(
                                        "account_count_created",
                                    ) => "JUMLAH AKUN",
                                ];
                            })
                            ->live(1000)
                            ->reactive()
                            ->afterStateUpdated(function (Get $get, Set $set, $state, ) {
                                preg_match("/\d+/", $state, $matches);
                                $count = (float) ($matches[0] ?? 0);

                                $set("total", (float) $get("price") * $count);
                            }),
                    ]),

                    Fieldset::make("Total")->schema([
                        TextInput::make("total")
                            ->label("Total Dana Sesuai SPJ")
                            ->prefix("Rp")
                            ->currencyMask(
                                thousandSeparator: ",",
                                decimalSeparator: ".",
                                precision: 0,
                            )
                            ->readOnly(),
                        TextInput::make("total_net")
                            ->label("Total Net")
                            ->prefix("Rp")
                            ->currencyMask(
                                thousandSeparator: ",",
                                decimalSeparator: ".",
                                precision: 0,
                            )
                            ->readOnly(),
                    ]),

                    Fieldset::make("")
                        ->label("TRC")
                        ->schema([
                            TextInput::make("student_count_1")
                                ->label("Selisih Siswa TRC")
                                ->numeric()
                                ->live(debounce: 1000)
                                ->afterStateUpdated(function (Get $get, Set $set, ) {
                                    $set(
                                        "subtotal_1",
                                        (float) $get("student_count_1") *
                                        (float) $get("net"),
                                    );
                                }),
                            TextInput::make("net")
                                ->label("Satuan")
                                ->live(debounce: 1000)
                                ->prefix("Rp")
                                ->currencyMask(
                                    thousandSeparator: ",",
                                    decimalSeparator: ".",
                                    precision: 0,
                                )
                                ->numeric()
                                ->afterStateUpdated(function (Get $get, Set $set, ) {
                                    $set(
                                        "subtotal_1",
                                        (float) $get("student_count_1") *
                                        (float) $get("net"),
                                    );
                                }),
                            TextInput::make("subtotal_1")
                                ->label("Subtotal")
                                ->prefix("Rp")
                                ->currencyMask(
                                    thousandSeparator: ",",
                                    decimalSeparator: ".",
                                    precision: 0,
                                )
                                ->numeric()
                                ->readOnly(),
                        ])
                        ->columns(3),

                    Fieldset::make("")
                        ->label("MITRA")
                        ->schema([
                            TextInput::make("mitra_difference")
                                ->label("Selisih Siswa Sekolah")
                                ->numeric()
                                ->live(debounce: 1000)
                                ->afterStateUpdated(function (Get $get, Set $set, ) {
                                    $set(
                                        "mitra_subtotal",
                                        (float) $get("mitra_difference") *
                                        (float) $get("mitra_net"),
                                    );
                                }),
                            TextInput::make("mitra_net")
                                ->label("Satuan")
                                ->live(debounce: 1000)
                                ->prefix("Rp")
                                ->currencyMask(
                                    thousandSeparator: ",",
                                    decimalSeparator: ".",
                                    precision: 0,
                                )
                                ->numeric()
                                ->afterStateUpdated(function (Get $get, Set $set, ) {
                                    $set(
                                        "mitra_subtotal",
                                        (float) $get("mitra_difference") *
                                        (float) $get("mitra_net"),
                                    );
                                }),
                            TextInput::make("mitra_subtotal")
                                ->label("Subtotal")
                                ->prefix("Rp")
                                ->currencyMask(
                                    thousandSeparator: ",",
                                    decimalSeparator: ".",
                                    precision: 0,
                                )
                                ->numeric()
                                ->readOnly(),

                            TextInput::make("ss_difference")
                                ->label("SS")
                                ->numeric()
                                ->live(debounce: 1000)
                                ->afterStateUpdated(function (Get $get, Set $set, ) {
                                    $set(
                                        "ss_subtotal",
                                        (float) $get("ss_difference") *
                                        (float) $get("ss_net"),
                                    );
                                })
                                ->readOnly(),
                            TextInput::make("ss_net")
                                ->label("Satuan")
                                ->live(debounce: 1000)
                                ->prefix("Rp")
                                ->currencyMask(
                                    thousandSeparator: ",",
                                    decimalSeparator: ".",
                                    precision: 0,
                                )
                                ->numeric()
                                ->afterStateUpdated(function (Get $get, Set $set, ) {
                                    $set(
                                        "ss_subtotal",
                                        (float) $get("ss_difference") *
                                        (float) $get("ss_net"),
                                    );
                                }),
                            TextInput::make("ss_subtotal")
                                ->label("Subtotal")
                                ->prefix("Rp")
                                ->currencyMask(
                                    thousandSeparator: ",",
                                    decimalSeparator: ".",
                                    precision: 0,
                                )
                                ->numeric()
                                ->readOnly(),

                            TextInput::make("dll_difference")
                                ->label("Lain-lain")
                                ->numeric()
                                ->live(debounce: 1000)
                                ->afterStateUpdated(function (Get $get, Set $set, ) {
                                    $set(
                                        "dll_subtotal",
                                        (float) $get("dll_difference") *
                                        (float) $get("dll_net"),
                                    );
                                }),
                            TextInput::make("dll_net")
                                ->label("Satuan")
                                ->live(debounce: 1000)
                                ->prefix("Rp")
                                ->currencyMask(
                                    thousandSeparator: ",",
                                    decimalSeparator: ".",
                                    precision: 0,
                                )
                                ->numeric()
                                ->afterStateUpdated(function (Get $get, Set $set, ) {
                                    $set(
                                        "subtotal_1",
                                        (float) $get("student_count_1") *
                                        (float) $get("net"),
                                    );
                                }),
                            TextInput::make("dll_subtotal")
                                ->label("Subtotal")
                                ->prefix("Rp")
                                ->currencyMask(
                                    thousandSeparator: ",",
                                    decimalSeparator: ".",
                                    precision: 0,
                                )
                                ->numeric()
                                ->readOnly(),
                        ])
                        ->columns(3),

                    Fieldset::make("")->schema([
                        DatePicker::make("invoice_date")
                            ->label("Invoice")
                            ->native(false)
                            ->displayFormat("l, jS F Y"),
                        DatePicker::make("payment_date")
                            ->label("Jadwal Pembayaran")
                            ->native(false)
                            ->displayFormat("l, jS F Y"),
                        DatePicker::make("spk")
                            ->label("Jadwal SPK")
                            ->native(false)
                            ->displayFormat("l, jS F Y"),
                        Select::make("payment_name")
                            ->label("Pembayaran Via")
                            ->options([
                                "SIPLAH" => "SIPLAH",
                                "SI/TF" => "SI / TF",
                                "CASH" => "CASH",
                            ]),
                    ]),
                ])
                ->columns(2),
        ];
    }
}
