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
            $countInsert=1;
            $countError=0;
            $highestRow = $worksheet->getHighestRow();//total de registros filas de la hoja
            $tabla="<label class='text-success'>DATA INSERT</label><table class='table table-bordered'>";
            echo  $tabla; 
            $countexisteinsrip=0;
            $existe= "<label class='text-success'></label>";
           // echo $worksheet->getHighestColumn()."<br>";
            for($row=2; $row<=$highestRow; $row++)//se recorre todo el archivo excel
            {
                $output='';
                $periodo =trim($worksheet->getCellByColumnAndRow(0, $row)->getValue());
                $campus =trim($worksheet->getCellByColumnAndRow(1, $row)->getValue());
                $division =trim($worksheet->getCellByColumnAndRow(2, $row)->getValue());
                $facultad =  trim($worksheet->getCellByColumnAndRow(3, $row)->getValue());
                $programa =  trim($worksheet->getCellByColumnAndRow(4, $row)->getValue());
                $tipo_doc =  trim($worksheet->getCellByColumnAndRow(5, $row)->getValue());
                $num_docu = trim($worksheet->getCellByColumnAndRow(6, $row)->getValue(), " \t\n\r\0\x0B");
                $num_docu=str_replace(".", '', $num_docu);
                $apellidos = trim($worksheet->getCellByColumnAndRow(7, $row)->getValue());
                $nombres =   trim($worksheet->getCellByColumnAndRow(8, $row)->getValue());
                $nacionalidad =  trim($worksheet->getCellByColumnAndRow(9, $row)->getValue());
                $cargo =  trim($worksheet->getCellByColumnAndRow(10, $row)->getValue());
                $tipo_interchage = trim($worksheet->getCellByColumnAndRow(11, $row)->getValue());
                $modalidad =  trim($worksheet->getCellByColumnAndRow(12, $row)->getValue());
                $institucion_origen = str_replace("'", '', preg_replace("([^a-zA-ZñÑáéíóúÁÉÍÓÚ\s\W])", '', trim( $worksheet->getCellByColumnAndRow(13, $row)->getValue(), " \t\n\r\0\x0B")));
                $pais_origen = preg_replace("([^a-zA-ZñÑáéíóúÁÉÍÓÚ\s\W])", '', trim($worksheet->getCellByColumnAndRow(14, $row)->getValue(), " \t\n\r\0\x0B"));
                $institucion_destino = str_replace("'", '', preg_replace("([^a-zA-ZñÑáéíóúÁÉÍÓÚ\s\W])", '', trim($worksheet->getCellByColumnAndRow(15, $row)->getValue(), " \t\n\r\0\x0B")));
                $pais_destino = preg_replace("([^a-zA-ZñÑáéíóúÁÉÍÓÚ\s\W])", '', trim($worksheet->getCellByColumnAndRow(16, $row)->getValue(), " \t\n\r\0\x0B"));
                $campus_destino = trim($worksheet->getCellByColumnAndRow(17, $row)->getValue());
                $intensidad_horaria = trim($worksheet->getCellByColumnAndRow(20, $row)->getValue());
                $fuentes_financiacion = trim( $worksheet->getCellByColumnAndRow(21, $row)->getValue());

                // var_dump( $periodo, $campus,$division,$facultad,$programa,$tipo_doc,$num_docu,$apellidos,$nombres,$nacionalidad,$institucion_origen,$institucion_destino, $pais_destino);
                // return die;
                if(!empty($periodo) && !empty($campus) && !empty($division) && !empty($facultad) && !empty($programa) && !empty($tipo_doc) && !empty($num_docu) && $num_docu!='ND' && !empty($apellidos) 
                && !empty($nombres) && !empty($nacionalidad) && !empty($institucion_origen) && !empty($institucion_destino) && !empty( $pais_destino)){
                    $output .= '<tr><td>';
                    $output .= "Fila de excel= " . $row;
                    $output .= '</td></tr>';
                    /*   $nameC = trim($times[0]['cliente_fac']);
                        $tarifa = doubleval($times[0]['TARIFA']); */
                    //validamos que existan los registros en la base de datos
                    $checkperiod = $check->checkPeriod($periodo);
                    //se valida el periodo   1
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
                        $starDate=$fecha_ip;
                        $endDate= $fecha_fp;
                        $sql="INSERT INTO periodo (nombre, fecha_desde, fecha_hasta,migration) values ('".$periodo."','$fecha_ip','$fecha_fp', 1);";
                        $periodo_id=$inserts->InsertGeneral($sql);
                        $log->info("  \r\n".'Periodo Insertado con exito; id; '. $periodo_id."  \r\n");
                        $output.='<tr><td>';
                        $output.="Periodo Insertado con exito; id; '. $periodo_id.";
                        $output.='</td></tr>';
                    } else {// si existe el periodo
                        $periodo_id=$checkperiod[0]['id'];
                        $starDate=$checkperiod[0]['fecha_desde'];
                        $endDate= $checkperiod[0]['fecha_hasta'];
                        // $log->error("  \r\n".$countError.'; No se pudo insertar el periodo; '.$checkBusiness['desc'].'; UID; '.$times[0]['clave_res'].';'.$nameC.';'.$nameOT.";\r\n");
                    } 
                    if(empty($periodo_id)){
                        goto end;
                    }   
                    //se valida la modalidad 2
                    $checkModalidad = $check->checkTipoModalidad($modalidad);
                    if ($checkModalidad==0) { // si no existe la modalidad
                        $modalidad = mb_strtoupper( $modalidad, 'utf-8');
                        //se prepatra la data para la creacion de la modalidad
                        $sql="INSERT INTO tipo_modalidad (nombre, promedio, tipo,migration) values ('".$modalidad."',0, 0, 1);";
                        $modalidad_id=$inserts->InsertGeneral($sql);
                        //$output.=$inserts->InsertCorrecto( $modalidad_id, $log,"Modalidad Insertada con exito; id;", $output );
                        $log->info("  \r\n".'Modalidad Insertada con exito'. $modalidad_id."  \r\n");
                        $output.='<tr><td>';
                        $output.='Modalidad Insertada con exito'.$modalidad_id;
                        $output.='</td></tr>';
                    } else {// si existe la modalidad
                        $modalidad_id=$checkModalidad[0]['id'];
                    }
                    if(empty($modalidad_id)){
                        goto end;
                    }
                    //se valida la institucion origen  PARA INTERIN
                    $checkinstitucion_origen = $check->checkInstitution( $institucion_origen);

                    if ( $checkinstitucion_origen == 0) { // si no existe la institucion_destino
                        //se prepatra la data para la creacion de la institucion
                        $pais = $check->checkCountry( $pais_origen);
                        $pais_origen_id = $pais[0]['id'];
                        $institucion_origen = mb_strtoupper( $institucion_origen, 'utf-8');
                        $sql = "INSERT INTO institucion (nombre, tipo_institucion_id, migration,pais_id) values ('" . $institucion_origen . "',7, 1,   $pais_origen_id);";
                        $institucion_origen_id = $inserts->InsertGeneral($sql);
                        $log->info("  \r\n" . 'Institucion Origen Insertada con exito; id; ' . $institucion_origen_id . "  \r\n");
                        $output .= '<tr><td>';
                        $output .= "Institucion Origen Insertada con exito; id; '.  $institucion_origen_id";
                        $output .= '</td></tr>';
                    } else { // si existe la institucion
                        $institucion_origen_id = $checkinstitucion_origen[0]['id'];
                    }
                    if (empty( $institucion_origen_id)) {
                        goto end;
                    }
                    //se valida la institucion destino 3
                    $checkinstitucion = $check->checkInstitution($institucion_destino);
                   
                    if ($checkinstitucion==0) { // si no existe la institucion_destino
                        //se prepatra la data para la creacion de la institucion
                        $pais=$check->checkCountry($pais_destino);
                        $pais_destino_id= $pais[0]['id'];
                        $institucion_destino = mb_strtoupper( $institucion_destino, 'utf-8');
                        $sql="INSERT INTO institucion (nombre, tipo_institucion_id, migration,pais_id) values ('".$institucion_destino."',7, 1, $pais_destino_id);";
                        $institucion_id=$inserts->InsertGeneral($sql);
                        $log->info("  \r\n".'Institucion Insertada con exito; id; '. $institucion_id."  \r\n");
                        $output.='<tr><td>';
                        $output.="Institucion Insertada con exito; id; '. $institucion_id";
                        $output.='</td></tr>';
                    } else {// si existe la institucion
                        $institucion_id=$checkinstitucion[0]['id'];
                    }
                    if(empty($institucion_id)){
                        goto end;
                    }
                    //se valida el tipo de movilidad
                    if( $check->checkTipoInterchange( $tipo_interchage)===0){//si es movilidad saliente
                        $campus_origen= $campus;
                        $tipoInterchange=0;
                        $rol_id=17;
                    }elseif(  $check->checkTipoInterchange($tipo_interchage) ===1 ) { //si es movilidad entrante
                        $campus_origen = $campus_destino;
                        $campus_destino = $campus;
                        $tipoInterchange = 1;
                        $rol_id = 18;
                    }
                    //se valida si existe el campus de origen
                    if (!empty($campus_origen)) {
                        $checkCampusOrigen = $check->checkCampus($campus_origen, $institucion_origen_id);
                        $campus_nombre = $campus_origen;
                    } else {
                        $campus_nombre =  mb_strtoupper('Sede Principal', 'utf-8');
                        $checkCampusOrigen = $check->firstCampus( $institucion_origen_id);
                    }
                    //se valida el campus de oRIGEN 3.1
                    if ($checkCampusOrigen==0) {// si no existe el campus origen
                        //se prepatra la data para la creacion del campus origen
                        $consulta= "select ciudad.id from ciudad 
                        inner join pais on pais.id=ciudad.pais_id
                        where pais.nombre like 'colombia'
                        ORDER BY ciudad.nombre ASC
                        limit 1";
                        $ciudad_id=$inserts->consulta($consulta);
                        $ciudad_id= $ciudad_id[0]['id'];
                        $campus = mb_strtoupper($campus, 'utf-8');
                        $sql="INSERT INTO campus (nombre, institucion_id, ciudad_id,principal) values ('$campus_origen',$institucion_origen_id, $ciudad_id,0);";
                        $campus_origen_id=$inserts->InsertGeneral($sql);
                        $log->info("  \r\n".'Campus origen Insertado con exito; id; '. $campus_origen_id."  \r\n");
                        $output.='<tr><td>';
                        $output.="Campus origen Insertado con exito; id; '. $campus_origen_id";
                        $output.='</td></tr>';
                    } else {// si existe un  campus origen
                        $campus_origen_id=$checkCampusOrigen[0]['id'];
                    }
                    if(empty( $campus_origen_id)){
                        goto end;
                    }

                    //se valida el campus de destino 3.2
                    if(!empty($campus_destino)){
                        $checkCampusdestino = $check->checkCampus( $campus_destino, $institucion_id);
                        $campus_nombre= $campus_destino;
                    }else{
                        $campus_nombre =  mb_strtoupper( 'Sede Principal', 'utf-8'); 
                        $checkCampusdestino = $check->firstCampus($institucion_id);
                    }
                    if ($checkCampusdestino==0) {// si no existe el campus destino
                        //se prepatra la data para la creacion del campus destino
                        $consulta="select ciudad.id from ciudad 
                        inner join pais on pais.id=ciudad.pais_id
                        where pais.nombre like '%$pais_destino%'
                        ORDER BY ciudad.nombre ASC
                        limit 1";
                        $ciudad_id=$inserts->consulta($consulta);
                        $ciudad_id= $ciudad_id[0]['id'];
                        //  var_dump( $consulta);
                        $sql="INSERT INTO campus (nombre, institucion_id, ciudad_id,principal) values ('$campus_nombre',$institucion_id, $ciudad_id,1);";
                        $campus_destino_id=$inserts->InsertGeneral($sql);
                        $log->info("  \r\n".'Campus destino Insertado con exito; id; '. $campus_destino_id."  \r\n");
                        $output.='<tr><td>';
                        $output.="'Campus destino Insertado con exito; id; '. $campus_destino_id";
                        $output.='</td></tr>';
                    } else {// si existeel campus destino
                        $campus_destino_id=$checkCampusdestino[0]['id'];
                    }
                    if(empty( $campus_destino_id)){
                        goto end;
                    }

                    //se valida la programacion de la modalidad 4
                    $checkprogramacion=$check->checkModalidad($periodo_id,$institucion_id, $modalidad_id,$institucion_origen_id);
                    if ($checkprogramacion==0) { // si no existe la programacion de la modalidad
                        if(!empty($intensidad_horaria) && is_numeric( $intensidad_horaria)){//si no es vacia la intensaidad horaria y es numerica
                            if( $intensidad_horaria>7){
                                $intensidad_horaria =7 * 12; //se convierten los dias por horas x semana  
                            }else{
                                $intensidad_horaria = $intensidad_horaria * 12;    //se convierten los dias por horas x semana  
                            }
                        }else{
                            $intensidad_horaria='';
                        }
                        //se prepatra la data para la creacion de la programacion de la modalidad
                        $sql="INSERT INTO modalidad (periodo_id, tipo_modalidad_id,migration, intensidad_horaria,institucion_destino_id,institucion_origen_id) 
                                values ($periodo_id, $modalidad_id, 1,'$intensidad_horaria' ,$institucion_id, $institucion_origen_id);";
                        $programacion_modalidad_id=$inserts->InsertGeneral($sql);
                        $log->info("  \r\n".'Programación de la modalidad Insertada con exito; id; '. $programacion_modalidad_id."  \r\n");
                        $output.='<tr><td>';
                        $output.="Programación de la modalidad Insertada con exito; id;  $programacion_modalidad_id";
                        $output.='</td></tr>';
                    } else {// si existe la programacion de la modalidad
                        $programacion_modalidad_id= $checkprogramacion[0]['id'];
                    }
                    if(empty( $programacion_modalidad_id)){
                        goto end;
                    }
                    //se valida si existe el usuario 5 y 6
                    $checkuser=$check->checkUsersDatos($num_docu);
                    if ($checkuser==0) {// si no existe el usuario
                        //se prepatra la data para la creacion de el usuario
                        $tipo_documento_id= $check->checkTipoDocumento( $tipo_doc);
                        $paisO = $check->checkCountry( $pais_origen);
                        $pais_origen_id = $paisO[0]['id'];
                        $nombres =  mb_strtoupper( $nombres, 'utf-8');
                        $apellidos =  mb_strtoupper( $apellidos, 'utf-8');
                        $cargo =  mb_strtoupper( $cargo, 'utf-8'); 
                        $sql="INSERT INTO datos_personales (nombres, apellidos,numero_documento, tipo_documento_id, nacionalidad_id, cargo)
                                     values ('$nombres','$apellidos', '$num_docu',  $tipo_documento_id, $pais_origen_id, '$cargo');";
                        $datos_personales_id=$inserts->InsertGeneral($sql);
                        $log->info("  \r\n".'DATOS PERSONALES Insertados con exito; id; '. $datos_personales_id."  \r\n");
                        $output.='<tr><td>';
                        $output.="DATOS PERSONALES Insertados con exito; id; ". $datos_personales_id;
                        $output.='</td></tr>';
                        $nombre=$nombres.' '.$apellidos;
                        $pass= crypt('cambiar123');
                        $sql="INSERT INTO users (name, email,password, datos_personales_id, activo, migration) values ('$nombre', '$num_docu@usta.edu.co','$pass',  $datos_personales_id, 0,1);";
                        $user_id=$inserts->InsertGeneral($sql);
                        $log->info("  \r\n".'usuario Insertado con exito; id; '. $user_id."  \r\n");
                        $output.='<tr><td>';
                        $output.="usuario Insertado con exito; id;  $user_id";
                        $output.='</td></tr>';
                        $sql="INSERT INTO model_has_roles (model_id,role_id, model_type) values ($user_id, $rol_id, '');";
                        $role_id=$inserts->InsertGeneral($sql);
                        $log->info("  \r\n".'ROL estudiante Insertado con exito; id; '. $role_id."  \r\n");
                        $output.='<tr><td>';
                        $output.="ROL estudiante Insertado con exito; id;  $role_id";
                        $output.='</td></tr>';
                    } else {// si existe el usuario
                        $datos_personales_id= $checkuser[0]['id'];
                        $user_id=$check->checkUsers($datos_personales_id);
                        $user_id=$user_id[0]['id'];
                    }
                    if(empty( $user_id)){
                        goto end;
                    }
                    //se verifica que exista la división en relacion al usuario usuario 7.0
                    //se valida el tipo de movilidad
                    if ($check->checkTipoInterchange($tipo_interchage) === 0) { //si es movilidad saliente
                        $checkdivision = $check->checkdivision($division, $campus_origen_id);
                        $campuss_id = $campus_origen_id;
                    } elseif ($check->checkTipoInterchange($tipo_interchage) === 1) { //si es movilidad entrante
                        $checkdivision = $check->checkdivision($division, $campus_destino_id);
                        $campuss_id = $campus_destino_id;
                    }
                    if ($checkdivision == 0) { // si no existe la division
                        //se prepatra la data para la creacion la division
                        $sql = "INSERT INTO division (nombre, campus_id) values ('$division', $campuss_id);";
                        $division_id = $inserts->InsertGeneral($sql);
                        $log->info("  \r\n" . 'Division Insertada con exito; id; ' . $division_id . "  \r\n");
                        $output .= '<tr><td>';
                        $output .= "'Division Insertada con exito; id; '. $division_id";
                        $output .= '</td></tr>';
                    } else { // si existe la Division
                        $division_id = $checkdivision[0]['id'];
                    }
                    if (empty($division_id)) {
                        goto end;
                    }
                    //se valida el tipo de movilidad
                    if ($check->checkTipoInterchange($tipo_interchage) === 0) { //si es movilidad saliente
                        $checkfacultad = $check->checkfacultad($facultad, $campus_origen_id);
                        $campuss_id = $campus_origen_id;
                    } elseif ($check->checkTipoInterchange($tipo_interchage) === 1) { //si es movilidad entrante
                        $checkfacultad = $check->checkfacultad($facultad, $campus_destino_id);
                        $campuss_id = $campus_destino_id;
                    }
                    //se verifica que exista la facultad en relacion al usuario usuario 7
                    if ($checkfacultad==0) {// si no existe la facultad
                        //se prepatra la data para la creacion la facultad
                        $sql="INSERT INTO facultad (nombre, campus_id,tipo_facultad_id, division_id) values ('$facultad', $campuss_id, 1, $division_id );";
                        $facultad_id=$inserts->InsertGeneral($sql);
                        $log->info("  \r\n".'Facultad Insertada con exito; id; '. $facultad_id."  \r\n");
                        $output.='<tr><td>';
                        $output.="'Facultad Insertada con exito; id; '. $facultad_id";
                        $output.='</td></tr>';
                    } else {// si existe la facultad
                        $facultad_id=$checkfacultad[0]['id'];
                    }
                    if(empty($facultad_id)){
                        goto end;
                    }

                    //se verifica que exista el programa del  usuario 7.1
                    $checkprograma=$check->checkprograma($programa);
                    if ($checkprograma==0) {// si no existe el programa
                        //se prepatra la data para la creacion el programa
                        $sql="INSERT INTO programa (nombre, facultad_id) values ('$programa',$facultad_id);";
                        $programa_id=$inserts->InsertGeneral($sql);
                        $log->info("  \r\n".'PROGRAMA Insertado con exito; id; '. $programa_id."  \r\n");
                        $output.='<tr><td>';
                        $output.="'PROGRAMA Insertado con exito; id; '. $programa_id";
                        $output.='</td></tr>';
                    } else {// si existe el programa
                        $programa_id=$checkprograma[0]['id'];
                    }
                    if(empty($programa_id)){
                        goto end;
                    }
                    //se valida si existe el programa relacionado con el usuario 7.2 
                    $checkuser_programa=$check->checkUserProgram($user_id,$programa_id );
                    if ($checkuser_programa==0) {// si no existe programa relacionado con el usuario
                        //se prepatra la data para la creacion programa relacionado con el usuario
                        $sql="INSERT INTO user_programa (user_id, programa_id) values ($user_id,$programa_id);";
                        $programa_user_id=$inserts->InsertGeneral($sql);
                        $log->info("  \r\n".'USER_PROGRAMA Insertados con exito; id; '. $programa_user_id."  \r\n");
                        $output.='<tr><td>';
                        $output.="USER_PROGRAMA Insertados con exito; id; '. $programa_user_id";
                        $output.='</td></tr>';
                    } else {// si existe programa relacionado con el usuario
                        $programa_user_id=$checkuser_programa[0]['id'];
                    }
                    if ($check->checkTipoInterchange($tipo_interchage) === 0) { //si es movilidad saliente
                        $checkuser_campus = $check->checkUserCampus($user_id, $campus_origen_id);
                        $campuss_id = $campus_origen_id;
                        $programavalue= 'programa_origen_id';
                    } elseif ($check->checkTipoInterchange($tipo_interchage) === 1) { //si es movilidad entrante
                        $checkuser_campus = $check->checkUserCampus($user_id, $campus_destino_id);
                        $campuss_id = $campus_destino_id;
                        $programavalue = 'programa_destino_id';
                    }
                    //se valida si existe el CAMPUS relacionado con el usuario 7.3 
                    if ($checkuser_campus==0) {// si no existe CAMPUS relacionado con el usuario
                        //se prepatra la data para la creacion CAMPUS relacionado con el usuario
                        $sql="INSERT INTO user_campus (user_id, campus_id) values ($user_id, $campuss_id);";
                        $campus_user_id=$inserts->InsertGeneral($sql);
                        $log->info("  \r\n".'USER_CAMPUS Insertados con exito; id; '. $campus_user_id."  \r\n");
                        $output.='<tr><td>';
                        $output.="USER_CAMPUS Insertados con exito; id; '. $campus_user_id";
                        $output.='</td></tr>';
                    } else {// si existe CAMPUS relacionado con el usuario
                        $campus_user_id=$checkuser_campus[0]['id'];
                    }
                
                    //se valida si existe la inscripcion 8
                    $checkinscripcion=$check->checkinscripcion($user_id, $campus_origen_id, $periodo_id, $programacion_modalidad_id, $institucion_id, $campus_destino_id);
                    if ($checkinscripcion==0) {// si no existe la inscripcion
                        //se prepatra la data para la creacion la inscripcion
                        $sql="INSERT INTO inscripcion (user_id, campus_id,periodo_id,modalidad_id, institucion_destino_id, tipo, estado_id,
                                        $programavalue, fecha_inicio, fecha_fin, migration, campus_destino_id ) 
                                        values ($user_id,$campus_origen_id, $periodo_id, $programacion_modalidad_id,$institucion_id, 
                                        $tipoInterchange, 16, $programa_id, '$starDate', '$endDate', 1 ,  $campus_destino_id);";
                        $inscripcion_id=$inserts->InsertGeneral($sql);
                        $log->info("  \r\n".'La Inscripcion ha sido Insertada con exito; id; '. $inscripcion_id." ; # $countInsert; \r\n");
                        $output.='<tr><td>';
                        $output.="La Inscripcion ha sido Insertada con exito; id; '. $inscripcion_id # $countInsert";
                        $output.='</td></tr>';
                        $countInsert++;
                    } else {// si existe la inscripcion
                        $inscripcion_id=$checkinscripcion[0]['id'];
                        $countexisteinsrip++;
                        $msj="Fila excel No. $row ; ya estiste la inscripción con el id; $inscripcion_id  estudiante; $nombres   $apellidos ci; $num_docu user_id; $user_id periodo_id; $periodo_id modalidad; $programacion_modalidad_id institucion_id; $institucion_id ";
                        $log->error("  \r\n".$msj." \r\n");
                        $output.='<tr><td>';
                        $output.=  $msj;
                        $output.='</td></tr>';
                    }
                    
                    if(!empty($fuentes_financiacion)){
                    //se valida si existe la fuente de financiacion 9
                        $checkfuentefinanciacion=$check->checkfuenteFinanciacion($fuentes_financiacion);
                        if ($checkfuentefinanciacion==0) {// si no existe la fuente de financiacion
                            //se prepatra la data para la creacion la fuente de financiacion
                            $sql="INSERT INTO fuente_financiacion (nombre, tipo, migration ) 
                            values ('$fuentes_financiacion',0, 1 );";
                            $fuente_financiacion_id=$inserts->InsertGeneral($sql);
                            $log->info("  \r\n".'La Fuente de financiacion ha sido Insertada con exito; id; '. $fuente_financiacion_id."  \r\n");
                            $output.='<tr><td>';
                            $output.="La Fuente de financiacion ha sido Insertada con exito; id; '. $fuente_financiacion_id";
                            $output.='</td></tr>';
                        } else {// si existe la la fuente de financiacion
                            $fuente_financiacion_id=$checkfuentefinanciacion[0]['id'];
                        }
                        if(empty( $fuente_financiacion_id) || empty($inscripcion_id)){
                            goto end;
                        }
                        $checkfinanciacion=$check->checkFinanciacion($fuente_financiacion_id, $inscripcion_id);
                        if ($checkfinanciacion==0) {// si no existe la financiacion
                            //se prepatra la data para la creacion la financiacion
                            $sql="INSERT INTO financiacion (fuente_financiacion_id, inscripcion_id, migration) 
                            values ($fuente_financiacion_id,$inscripcion_id,1 );";
                            $financiacion_id=$inserts->InsertGeneral($sql);
                            $log->info("  \r\n".'La financiacion ha sido Insertada con exito; id; '. $financiacion_id."  \r\n");
                            $output.='<tr><td>';
                            $output.="La financiacion ha sido Insertada con exito; id; '.$financiacion_id";
                            $output.='</td></tr>';
                        }

                    }
                    end:
                       // $output.='';
                    echo  $output;                
                  
                } elseif (empty($periodo) || empty($campus) || empty($division) || empty($facultad) || empty($programa) || empty($tipo_doc) || empty($num_docu) || empty($apellidos)
                    || empty($nombres) || empty($nacionalidad) || empty($institucion_origen) || empty($institucion_destino) || empty($pais_destino)) {
                    $msj = "Fila excel No. $row ; error campos vacios ; periodo;  $periodo  campus;  $campus  division:  $division facultad:  
                            $programa:programa  tipo_doc: $tipo_doc  numero docuemnto: $num_docu" ;
                    $log->error("  \r\n" . $msj . " \r\n");
                  } elseif ($num_docu=='ND') {
                    $msj = "Fila excel No. $row ; error el estudiante no posee CEDULA ND ; periodo;  $periodo  campus;  $campus  division:  $division facultad:  
                            $programa:programa  tipo_doc: $tipo_doc  numero docuemnto: $num_docu" ;
                    $log->error("  \r\n" . $msj . " \r\n");
                }  //fin si no esta vacia la fila 
            //fin si no esta vacia la fila 
            } //fin for 
            echo '</table>';
            return   true;

        } catch (\PDOException $exception) {
            return "Error ejecutando la consulta: " . $exception->getMessage().' - '.$exception->getLine();
        }
    }

}