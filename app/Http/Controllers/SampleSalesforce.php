<?php

namespace App\Http\Controllers;

use App\Filament\Components\SampleExcel;
use Maatwebsite\Excel\Facades\Excel;

class SampleSalesforce extends Controller
{
    public function download()
    {
        return Excel::download(new SampleExcel, 'sample_import.xlsx');
    }
}
