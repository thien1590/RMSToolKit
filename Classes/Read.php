<?php
require_once "Classes/PHPExcel.php";

class Read{
    public $file;

    function __construct($file=null)
    {
        $this->file = $file;
    }

    public function getData($file=null)
    {
        $file = $file?$file:$this->file;
        if(!$file) return false;

        $objFile = PHPExcel_IOFactory::identify($file);
        $objData = PHPExcel_IOFactory::createReader($objFile);
        $objData->setReadDataOnly(true);
        $objPHPExcel = $objData->load($file);
        $sheet = $objPHPExcel->setActiveSheetIndex(0);
        $Totalrow = $sheet->getHighestRow();
        $LastColumn = $sheet->getHighestColumn();
        $TotalCol = PHPExcel_Cell::columnIndexFromString($LastColumn);

        $param = array();
        for ($j = 0; $j < $TotalCol; $j++) {
            $param[$j] = $sheet->getCellByColumnAndRow($j, 1)->getValue();;
        }

        $data = [];

        for ($i = 2; $i <= $Totalrow; $i++) {
            for ($j = 0; $j < $TotalCol; $j++) {
                $data[$i - 2][$param[$j]] = $sheet->getCellByColumnAndRow($j, $i)->getValue();;
            }
        }

        return $data;
    }
}