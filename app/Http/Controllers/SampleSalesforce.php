<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Filament\Components\SampleExcel;

class SampleSalesforce extends Controller
{
    public function download()
    {
        return Excel::download(new SampleExcel(), 'sample_import.xlsx');
    }
}
