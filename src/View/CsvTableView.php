<?php
namespace App\View;

class CsvTableView extends PhpSpreadsheetView
{
    protected function _getWriter(\PhpOffice\PhpSpreadsheet\Spreadsheet $data)
    {
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Csv($data);
        return $writer;
    }
}