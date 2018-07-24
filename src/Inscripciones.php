<?php


namespace TM;
use TM\Mysqlcheck;
use TM\Inserts;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\LineFormatter;


class Inscripciones
{
    /**
     * @var \PDO
     */
    private $pdo;

    public function __construct()
    {
        $connection = new ConnectionMySQL();
        $this->pdo = $connection->connect();

    }

    /**
     * @return array|string
     */
    public function countAll()
    {
        try {
            $sql = "SELECT COUNT(*) as total FROM [tiemposhoras].[dbo].[registro]";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (\PDOException $exception) {
            print_r($exception->getMessage());
        }
    }

    public function fetchAllinscripcion($worksheet){

        try {
            
            // create a log
            $log = new Logger('Files');

            $formatter = new LineFormatter(null, null, false, true);
            $infoHandler = new StreamHandler('info.log', Logger::INFO, false);
            $infoHandler->setFormatter($formatter);

            $errorHandler = new StreamHandler('error.log', Logger::ERROR);
            $errorHandler->setFormatter($formatter);

            // This will have messages
            $log->pushHandler($infoHandler);
            // This will have only ERROR messages
            $log->pushHandler($errorHandler);
            //se instancian las clases
            $check=new Mysqlcheck();//para validar si existe se instancia la clase
            $inserts=new Inserts();//para validar si existe se instancia la clase
            $countInsert=0;
            $countError=0;
            $highestRow = $worksheet->getHighestRow();//total de registros filas de la hoja
            $output='';
           // echo $worksheet->getHighestColumn()."<br>";
            for($row=2; $row<=$highestRow; $row++)//se recorre todo el archivo excel
            {
                
                $periodo =$worksheet->getCellByColumnAndRow(0, $row)->getValue();
                $campus =$worksheet->getCellByColumnAndRow(1, $row)->getValue();
                $division = $worksheet->getCellByColumnAndRow(2, $row)->getValue();
                $facultad = $worksheet->getCellByColumnAndRow(3, $row)->getValue();
                $programa = $worksheet->getCellByColumnAndRow(4, $row)->getValue();
                $tipo_doc = $worksheet->getCellByColumnAndRow(5, $row)->getValue();

                $num_docu = $worksheet->getCellByColumnAndRow(6, $row)->getValue();
                $apellidos = $worksheet->getCellByColumnAndRow(7, $row)->getValue();
                $nombres = $worksheet->getCellByColumnAndRow(8, $row)->getValue();

                $nacionalidad = $worksheet->getCellByColumnAndRow(9, $row)->getValue();
                $modalidad = $worksheet->getCellByColumnAndRow(12, $row)->getValue();
                $institucion_origen = $worksheet->getCellByColumnAndRow(13, $row)->getValue();
                 
                $institucion_destino = $worksheet->getCellByColumnAndRow(15, $row)->getValue();
                $pais_destino = $worksheet->getCellByColumnAndRow(16, $row)->getValue();
                $fuentes_financiacion = $worksheet->getCellByColumnAndRow(20, $row)->getValue();
                 
                if(!empty($periodo) && !empty($campus) && !empty($division) && !empty($facultad) && !empty($programa) && !empty($tipo_doc) && !empty($num_docu) && !empty($apellidos) 
                && !empty($nombres) && !empty($nacionalidad) && !empty($institucion_origen) && !empty($institucion_destino)){
   
                 /*   $output .= '<tr><td>';
                   $output.='<br> i: '.$row.'<br>' ;
                    $output .= 'periodo '.$periodo.' campus '.$campus.' divi '.$division.' facultad '.$facultad.' programa '.$programa.' tipo_doc '.$tipo_doc.' num_docu '.$num_docu.' apellidos '.$apellidos;
                    $output .='nombres '.$nombres.' nacionalidad '.$nacionalidad.' modalidad '.$modalidad.' institucion_origen '.$institucion_origen.' institucion_destino '.$institucion_destino.' pais_destino '.$pais_destino.' fuentes_financiacion '.$fuentes_financiacion;
                    $output .= '</td></tr>'; */
               // echo 'periodo '.$periodo.' campus '.$campus.' divi '.$division.' facultad '.$facultad.' programa '.$programa.' tipo_doc '.$tipo_doc.' num_docu '.$num_docu.' apellidos '.$apellidos.'<br>';
               // echo 'nombres '.$nombres.' nacionalidad '.$nacionalidad.' modalidad '.$modalidad.' institucion_origen '.$institucion_origen.' institucion_destino '.$institucion_destino.' pais_destino '.$pais_destino.' fuentes_financiacion '.$fuentes_financiacion.'<br>';
               
                ///echo '<br><br>';

                 

                  /*   $nameC = trim($times[0]['cliente_fac']);
                    $tarifa = doubleval($times[0]['TARIFA']); */
                
                    //validamos que existan los registros en la base de datos
                    $checkperiod = $check->checkPeriod($periodo);
              /*       var_dump($checkperiod);
                   echo '<pre>'; */
                   //var_dump($periodo, count($checkperiod), $checkperiod==0,  $checkmodalidad, $checkcampus);die;
                   //se valida el periodo
                    if ($checkperiod==0) {// si no existe el periodo 
                        //se prepatra la data para la creacion del periodo
                        $periodo1=explode('-',$periodo);
                        if($periodo1[1]==1){//si es el primer semestre del año
                            $fecha_ip=$periodo1[0].'-01-01';//fecha inicio peridoo
                            $fecha_fp=$periodo1[0].'-06-30';//fecha fin peridoo
                        }else{//si es el segundo semestre del año
                            $fecha_ip=$periodo1[0].'-07-01';//fecha inicio peridoo
                            $fecha_fp=$periodo1[0].'-12-31';//fecha fin peridoo
                        }
                        $sql="INSERT INTO periodo (nombre, fecha_desde, fecha_hasta,migration) values ('".$periodo."','$fecha_ip','$fecha_fp', 1);";
                        $periodo_id=$inserts->InsertGeneral($sql);
                        $log->info("\r\n".'Periodo Insertado con exito; id; '. $periodo_id."\r\n");
                    } else {// si existe el periodo
                        $periodo_id=$checkperiod[0]['id'];
                       // $log->error("\r\n".$countError.'; No se pudo insertar el periodo; '.$checkBusiness['desc'].'; UID; '.$times[0]['clave_res'].';'.$nameC.';'.$nameOT.";\r\n");
                    }
                    //se valida la modalidad
                    $checkModalidad = $check->checkTipoModalidad($modalidad);
                    if ($checkModalidad==0) {// si no existe la modalidad
                        //se prepatra la data para la creacion de la modalidad
                        $sql="INSERT INTO tipo_modalidad (nombre, promedio, tipo,migration) values ('".$modalidad."',0, 0, 1);";
                        $modalidad_id=$inserts->InsertGeneral($sql);
                        $log->info("\r\n".'Modalidad Insertada con exito; id; '. $modalidad_id."\r\n");
                    } else {// si existe el periodo
                        $modalidad_id=$checkModalidad[0]['id'];
                    }
                    //se valida la institucion destino
                    $checkinstitucion = $check->checkInstitution($institucion_destino);
                    if ($checkinstitucion==0) {// si no existe la institucion_destino
                        //se prepatra la data para la creacion de la institucion
                        $sql="INSERT INTO institucion (nombre, tipo_institucion_id, migration) values ('".$institucion_destino."',7, 1);";
                        $institucion_id=$inserts->InsertGeneral($sql);
                        $log->info("\r\n".'Institucion Insertada con exito; id; '. $institucion_id."\r\n");
                        
                    } else {// si existe el periodo
                        $institucion_id=$checkinstitucion[0]['id'];
                    }
                    //se valida el campus de destino
                    $checkCampusdestino=$check->firstCampus($institucion_id);
                    if ($checkCampusdestino==0) {// si no existe el campus destino
                        //se prepatra la data para la creacion del campus destino
                        $consulta="select ciudad.id from ciudad 
                        inner join departamento on departamento_id=departamento.id
                        inner join pais on pais.id=departamento.pais_id
                        where pais.nombre like '$pais_destino'
                        limit 1";
                        $ciudad_id=$inserts->consulta($consulta);
                        $ciudad_id= $ciudad_id[0]['id'];
                        $sql="INSERT INTO campus (nombre, institucion_id, ciudad_id,principal) values ('Sede Principal',$institucion_id, $ciudad_id,1);";
                        $campus_destino_id=$inserts->InsertGeneral($sql);
                        $log->info("\r\n".'Campus Insertado con exito; id; '. $campus_id."\r\n");
                        
                    } else {// si existe un campus
                        $campus_destino_id=$checkCampusdestino[0]['id'];
                    }

                   
                  
                    if (!isset($checkBusiness['error'])) {
                        if (count($checkUser) > 0) {// se comprueba el usuario
                            if (isset($checkUser['error'])) {// SI NO EXISTE EL USUARIO
                                $pgsProID=$check->InsertUser($nameUser, $short_name);//se inserta el usuario
                                $log->info("\r\n".'; Usuario Insertado con exito; id: '.  $pgsProID.';'.$nameUser.';'.$short_name."\r\n");
                            } else {
                                $pgsProID = $checkUser[0]['id'];
                                /*$countError++;
                                $times[0]['error'] = $checkUser['error'];
                                $log->error("\r\n".$countError.'; No se pudo insertar el registro; '.$checkUser['desc'].'; UID; '.$times[0]['clave_res'].';'.$nameC.';'.$nameOT.";".$nameUser.';'.$short_name."\r\n");
                                    */
                            // }
                                    if (count($checkArea) > 0) {//
                                        if (isset($checkArea['error'])) {//si no existe el area
                                            $practice_area_id=$check->InsertArea($nameArea);//se inserta el area
                                            $log->info("\r\n".'; Area de practica creada con exito; id: '.  $practice_area_id.'; name: '.$nameArea."\r\n");
                                        }else{//si existe el area
                                            $practice_area_id=$checkArea[0]['id_practice_area'];
                                        }
                                        if($nameC=='VARIOS'){//si el cliente es varios el registro se almacena como no facturable
                                            $pgsInvoiceble=2;
                                        }else{
                                            $pgsInvoiceble=1;
                                        }
                                        $pgsBuzId = $checkBusiness[0]['buzID'];
                                        $pgsCurID = $checkBusiness[0]['buzCurID'];

                                        $minuts = doubleval($times[0]['T_TRANS']) * 60;
                                        $minutswork = doubleval($times[0]['tiempo_fac']) * 60;
                                        if($total==0 && $tarifa>0){
                                            $total=($minutswork/60)*$tarifa;
                                        }
                                        $pgsDateWork = new \DateTime($times[0]['FECHA']);
                                        $pgsDateWork = $pgsDateWork->format('Y-m-d');
                                        $sql = "INSERT INTO tmc_progress_tbl_pgs ( pgsBuzID, pgsProID, original_user_id, pgsMinutsWork, pgsMinuts, pgsDateWork, pgsDetails,
                                            pgsHourRate, pgsTotal, pgsCurID, pgsStatus, practice_area_id,pgsInvoiceble, migration) 
                                            VALUES ($pgsBuzId,$pgsProID,$pgsProID,$minutswork,$minuts,'" . $pgsDateWork . "','" . $times[0]['DETALLE'] . "',$tarifa,$total, $pgsCurID, 4,$practice_area_id, $pgsInvoiceble, 1);";
                                        $countInsert++;
                                        var_dump('id: '.$i.' - Count: '.$countInsert.' - '.$sql."\r\n");
                                        $pgsID=$check->InsertTime($sql);

                                        $log->info("\r\n".$countInsert.'; Registro Insertado; pgsID;'. $pgsID.'; UID; '.$times[0]['clave_res'].';'.$nameC.';'.$nameOT.";\r\n");
                                    }

                           }
                    
                       }
                    }else{
                    // $log->error("\r\n".'; No se existe el id;'.$i."\r\n");
                    } 
                 }  //fin si no esta vacia la fila 
            } //fin for 
            return   $output;

        } catch (\PDOException $exception) {
            return "Error ejecutando la consulta: " . $exception->getMessage().' - '.$exception->getLine();
        }
    }

}