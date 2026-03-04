<?php
require __DIR__ . '/vendor/autoload.php';

$classes = [
    'Filament\Forms\Components\Section',
    'Filament\Schemas\Components\Section',
    'Filament\Forms\Components\Fieldset',
    'Filament\Schemas\Components\Fieldset',
    'Filament\Forms\Get',
    'Filament\Schemas\Components\Utilities\Get',
    'Filament\Forms\Set',
    'Filament\Schemas\Components\Utilities\Set',
    'Filament\Forms\Components\Radio',
    'Filament\Forms\Components\DatePicker',
];

foreach ($classes as $class) {
    if (class_exists($class)) {
        echo "$class EXISTS\n";
    } else {
        echo "$class NOT FOUND\n";
    }
}
