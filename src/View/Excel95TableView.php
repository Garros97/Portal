<?php
namespace App\View;

class Excel95TableView extends PhpSpreadsheetView
{
    protected function _getWriter(\PhpOffice\PhpSpreadsheet\Spreadsheet $data)
    {
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xls($data);
        return $writer;
    }
}