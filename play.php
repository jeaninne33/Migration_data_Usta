<?php

require_once 'vendor/autoload.php';

use TM\Inscripciones;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
set_time_limit(0);


error_reporting (E_ALL & ~ E_DEPRECATED & ~ E_NOTICE);
//  Read your Excel workbook
//var_dump($asuntos->fetchAllBusiness());
?>

<html>
 <head>
  <title>Import Excel to Mysql using PHPExcel in PHP</title>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>
  <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" rel="stylesheet" />
  <style>
  body
  {
   margin:0;
   padding:0;
   background-color:#f1f1f1;
  }
  .box
  {
   width:1100px;
   border:1px solid #ccc;
   background-color:#fff;
   border-radius:5px;
   margin-top:100px;
  }
  
  </style>
 </head>
 <body>
  <div class="container box">
   <h3 align="center">Import Excel to Mysql using PHPExcel in PHP</h3><br />
   <form method="post" enctype="multipart/form-data">
    <label>Select Excel File</label>
    <input type="file" name="excel" id="excel" />
    <br />
    <input type="submit" name="import" class="btn btn-info" value="Import" />
   </form>
   <br />
   <br />
   <?php
  
   $inscripciones=new Inscripciones();
   $output = '';
   try{
            if(isset($_POST["import"])){
                    $extension = end(explode(".", $_FILES["excel"]["name"])); // For getting Extension of selected file
                    $allowed_extension = array("xls", "xlsx", "csv"); //allowed extension
                    
                    if(in_array($extension, $allowed_extension)) //check selected file extension is present in allowed extension array
                    {
                        $file = $_FILES["excel"]["tmp_name"]; // getting temporary source of excel file
                        //Aquí es donde seleccionamos nuestro csv
                        $fname = $_FILES['excel']['name'];
                      //  echo 'Cargando nombre del archivo: '.$fname.' ';
                        //include("PHPExcel/IOFactory.php"); // Add PHPExcel Library in this code
                        $objPHPExcel = PHPExcel_IOFactory::load($file); // create object of PHPExcel library by using load() method and in load method define path of selected file

                        $output .= "<label class='text-success'>Data Inserted</label><br /><table class='table table-bordered'>";
                        
                        $objPHPExcel->setActiveSheetIndex(0);
                        $worksheet=$objPHPExcel->getActiveSheet();
                        $output.= $inscripciones->fetchAllinscripcion($worksheet);
                            
                    $output .= '</table>';
                    }
                    else
                    {
                        $output = '<label class="text-danger">Invalid EXTENSION File</label>'; //if non excel file then
                    }
            }
        }catch(Exception $e)
        {
            die('Error loading file "'.pathinfo($fname,PATHINFO_BASENAME).'": '.$e->getMessage());
        }
        echo $output;
   ?>
  </div>
 </body>
</html>