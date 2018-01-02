<?php

namespace App\Admin\Extensions;

use Encore\Admin\Grid\Exporters\AbstractExporter;
use Maatwebsite\Excel\Facades\Excel;
use Encore\Admin\Grid;

class ExcelExpoter extends AbstractExporter
{
    private $title;


    public function __construct(array $title = [])
    {
        $this->title = $title;
        parent::__construct();
    }

    public function export()
    {
        Excel::create('Filename', function ($excel) {
            $excel->sheet('Sheetname', function ($sheet) {
                $rows = [];
                $this->chunk(function ($records) use (&$rows) {
                    $rows[] = $this->title;
                    foreach ($records as $record) {
                        $record = $record->getAttributes();
                        $arr = [];
                        foreach($this->title as $value){
                            $arr[$value] =  $record[$value];
                        }
                        $rows[] = $arr;
                    }
                });
                \Log::info($rows);
                $sheet->rows($rows);
            });
        })->export('xls');
    }
}