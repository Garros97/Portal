<?php
namespace App\View;

use Cake\ORM\Query;
use Cake\View\View;

/**
 * Base class for all PhpSpreadsheet based views.
 * @package App\View
 */
abstract class PhpSpreadsheetView extends View
{
    /**
     * For projects with more courses no extra sheets filtered per course will be generated.
     * Use filtering in Excel as needed.
     */
    const MAX_COURSE_SHEETS = 40;


    public function render($view = null, $layout = null)
    {
        if (!isset($this->viewVars['query'])) {
            throw new \LogicException('view-var "query" must be defined!');
        }
        if (!isset($this->viewVars['export-type'])) {
            throw new \LogicException('view-var "export-type" must be defined!');
        }

        $data = $this->_createWorkbook($this->viewVars['query']);
        $writer = $this->_getWriter($data);
        ob_start();
        $writer->save('php://output');
        $content = ob_get_clean();
        /*$this->response = $this->response->withBody(new \Cake\Http\CallbackStream(function () use ($writer) {
            $writer->save('php://output');
        }));*/
        //$this->response->body($content); //TODO: Use callback stream and immutable responses (see above, but that is somehow broken)
        return $content;
    }

    /**
     * @param \PhpOffice\PhpSpreadsheet\Spreadsheet $data
     * @return \PhpOffice\PhpSpreadsheet\Writer\IWriter
     */
    protected abstract function _getWriter(\PhpOffice\PhpSpreadsheet\Spreadsheet $data);

    /**
     * Build a \PhpOffice\PhpSpreadsheet\Spreadsheet instance from a query.
     *
     * Column titles are taken from the result set array keys.
     *
     * @param Query $query
     * @return \PhpOffice\PhpSpreadsheet\Spreadsheet
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    private function _createWorkbook($query)
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $title = 'Datenexport';
        if (isset($this->viewVars['title'])) {
            $title = substr($this->viewVars['title'], 0, 31);
        }
        $spreadsheet->getProperties()->setTitle('Datenexport');
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle($this->_cleanTitle($title));

        $i = 2; //header is row 1
        if ($this->viewVars['export-type'] === 'ratings') {
            $headers = array();
            $headers[] = "Gruppenname";
            foreach ($query->all() as $group_name => $ratings) {
                $row = array();
                $row[] = $group_name;
                foreach ($ratings as $rating) {
                    if (!in_array($rating['Teilaufgabe'], $headers)) $headers[] = $rating['Teilaufgabe']; // add column headers if not already added
                    $row[] = $rating['value'];
                }
                $sheet->fromArray($row,null,"A$i", true);
                $i++;
            }
            $sheet->fromArray($headers);
        } else {
            $headers = array_keys($query->all()->first());
            $sheet->fromArray($headers);
            foreach ($query as $row) {
                $sheet->fromArray(array_values($row), null, "A$i", true); // enable "strictNullComparison" to avoid that fields containing '0' get converted to ''
                $i++;
            }
        }

        if (isset($this->viewVars['courses']) && count($this->viewVars['courses']) <= self::MAX_COURSE_SHEETS) { //limit this to a sensible amount to avoid timeout for the JuniorStudium
            if ($this->viewVars['export-type'] === 'participants') {
                //create more sheet, filtered to each course
                $dataCache = collection($query->toArray()); //TODO: Add general caching to export queries? (Is the query issued multiple times?)
                foreach ($this->viewVars['courses'] as $id => $name) {
                    $sheet = $spreadsheet->createSheet();
                    $sheet->setTitle($this->_cleanTitle($name));
                    $sheet->fromArray($headers);

                    $filteredQuery = $dataCache->filter(function ($row) use ($name) {
                        return $row[$name] != 0;
                    });

                    $i = 2; //header is row 1
                    foreach ($filteredQuery as $row) {
                        unset($row['courses']);
                        $sheet->fromArray(array_values($row), null, "A$i");
                        $i++;
                    }
                }
            }
        }

        return $spreadsheet;
    }

    //clean a string so that \PhpOffice\PhpSpreadsheet\Spreadsheet is happy with it as a sheet title
    private function _cleanTitle($str)
    {
        //$str = filter_var($str, FILTER_UNSAFE_RAW, FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH); //remove non-ascii chars
        $str = str_replace(['*', ':', '/', '\\', '?', '[', ']'], '_', $str); //these chars are not allowed
        $str = substr($str, 0, 31); //max length is 31
        return $str;
    }
}