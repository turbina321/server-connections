<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use DB;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;


class CargaInicialExport implements FromView
{
    protected $data;

    function __construct($obj) {
            $this->data = $obj;
    }
    public function view(): View
    {
        return view('excel', [
            'data' => $this->data
        ]);
    }
}
