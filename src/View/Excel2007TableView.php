<?php
namespace App\View;

class Excel2007TableView extends PhpSpreadsheetView
{
    protected function _getWriter(\PhpOffice\PhpSpreadsheet\Spreadsheet $data)
    {
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($data);
        return $writer;
    }
}