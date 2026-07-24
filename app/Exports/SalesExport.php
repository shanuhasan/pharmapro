<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class SalesExport implements FromView, ShouldAutoSize
{
    protected $title;
    protected $headers;
    protected $rows;

    public function __construct($title, $headers, $rows)
    {
        $this->title = $title;
        $this->headers = $headers;
        $this->rows = $rows;
    }

    public function view(): View
    {
        return view('reports.exports.table', [
            'title' => $this->title,
            'headers' => $this->headers,
            'rows' => $this->rows
        ]);
    }
}
