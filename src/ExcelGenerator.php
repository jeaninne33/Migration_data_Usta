<?php


namespace TM;

use PHPExcel;

class ExcelGenerator
{

    private $data;
    private $path;
    private $title;
    private $objPHPExcel;

    public function __construct(array $data, string $path, string $title)
    {
        $this->data = $data;
        $this->path = $path;
        $this->title = $title;
        $this->objPHPExcel = new PHPExcel();
    }

    public function GenerateExcelDocument(){
        $this->objPHPExcel->getProperties()
                          ->setCreator("Time Manager")
                          ->setLastModifiedBy("Time Manager")
                          ->setTitle($this->title)
                          ->setSubject($this->title)
                          ->setDescription($this->title)
                          ->setKeywords($this->title)
                          ->setCategory($this->title);
        $general_style = array('borders' => array('inside' => array('style' => \PHPExcel_Style_Border::BORDER_HAIR,
                                                                    'color' => array('argb' => 'FFDDDDDD')),
                                                                    'outline' => array('style' => \PHPExcel_Style_Border::BORDER_HAIR,'color' => array('argb' => 'FFDDDDDD'))));

        $this->objPHPExcel->getActiveSheet()->getStyle()->applyFromArray($general_style);
        $this->objPHPExcel->setActiveSheetIndex(0);
        $this->objPHPExcel->getActiveSheet()->setTitle($this->title);

        if($this->DefineColumns()){
            if($this->CompleteValues()){
                if($this->SaveExcelInPath()){
                    print_r("Archivo ".$this->title." generado correctamente\n");
                }
            }
        }

    }


    private function DefineColumns(){

        $stringKeys = array();
        if(count($this->data)>0){
            $keys = array_keys($this->data[0]);
            foreach($keys as $k){
                if(is_string($k)){
                    $stringKeys[]=$k;
                }
            }
        }
        $colummLetter1 = 'A';
        $colummLetter2 = '';
        $totalColumns = count($stringKeys);
        try {
            for ($i = 0; $i < $totalColumns; $i++) {
                $this->objPHPExcel->getActiveSheet()->getColumnDimension($colummLetter1 . $colummLetter2)->setWidth(25);
                $this->objPHPExcel->getActiveSheet()->setCellValue($colummLetter1 . $colummLetter2 . '1', $stringKeys[$i]);
                if ($colummLetter1 != 'Z' && $colummLetter2 == '')
                    $colummLetter1++;
                else {
                    $colummLetter1 = 'A';
                    if ($colummLetter2 != 'Z')
                        $colummLetter2++;
                    else {
                        $colummLetter2 = 'A';
                    }
                }
            }
            return true;
        }catch(Exception $e){
            print_r($e->getMessage());
            return false;
        }
    }

    private function CompleteValues(){
        $count = 2;
        try {
            $data = $this->data;
            foreach ($data as $row) {
                $colummLetter1 = 'A';
                $colummLetter2 = '';
                $totalColumns = count($row)/2;
                for ($i = 0; $i < $totalColumns; $i++) {
                    $this->objPHPExcel->getActiveSheet()->setCellValue($colummLetter1 . $colummLetter2 . $count, $row[$i]);
                    if ($colummLetter1 != 'Z' && $colummLetter2 == '')
                        $colummLetter1++;
                    else {
                        $colummLetter1 = 'A';
                        if ($colummLetter2 != 'Z')
                            $colummLetter2++;
                        else {
                            $colummLetter2 = 'A';
                        }
                    }
                }
                $count++;
            }
            return true;
        }catch(Exception $e){
            print_r($e->getMessage());
            return false;
        }
    }

    private function SaveExcelInPath(){
        try {
            $objWriter = \PHPExcel_IOFactory::createWriter($this->objPHPExcel, 'Excel2007');
            $objWriter->save($this->path);
            return true;
        }catch(Exception $e){
            print_r($e);
            return false;
        }
    }

}