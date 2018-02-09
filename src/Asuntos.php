<?php


namespace TM;
use TM\Mysqlcheck;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\LineFormatter;


class Asuntos
{
    /**
     * @var \PDO
     */
    private $pdo;

    public function __construct()
    {
        $connection = new Connection();
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

    public function fetchAllBusiness(){

        try {
            //buscamos el maximo UID de los registros del año 2017
            $query = "SELECT TOP 1 [uid] FROM [tiemposhoras].[dbo].[registro] where [registro].[FECHA]>='2017-01-01 00:00:00' ORDER BY [uid] DESC";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute();
            $maxID = $stmt->fetchAll();

            //buscamos el minimo UID de los registros del año 2017
            $query2 = "SELECT TOP 1 [uid] FROM [tiemposhoras].[dbo].[registro] where [registro].[FECHA]>='2017-01-01 00:00:00' ORDER BY [uid] ASC";
            $stmt2 = $this->pdo->prepare($query2);
            $stmt2->execute();
            $minID = $stmt2->fetchAll();

            $totalTimes = doubleval($maxID[0]['uid']);
            $minID = doubleval($minID[0]['uid']);
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
            $countInsert=0;
            $countError=0;
            for($i= $totalTimes; $i>= $minID ; $i--) {
                $query = "SELECT   
                               [registro].[INICIO]
                              ,[registro].[TERMINO]
                              ,[registro].[T_TRANS]
                              ,[registro].[DECIMAL1]
                              ,[registro].[CODCLI]
                              ,[registro].[CLIENTE]
                              ,[registro].[ORDEN]
                              ,[registro].[AREA]
                              ,[registro].[T_CLI]  
                              ,[registro].[DETALLE] 
                              ,[registro].[FECHA]
                              ,[registro].[ABOGADO]
                              ,[registro].[INICIAL]
                              ,[registro].[TARIFA]
                              ,[registro].[TOTAL]
                              ,[registro].[NORDEN]
                              ,[facturacion].[INICIO] AS inicio_fac
                              ,[facturacion].[TERMINO] AS fin_fac
                              ,[facturacion].[T_TRANS] AS tiempo_fac
                              ,[facturacion].[DECIMAL1] AS deci_fac
                              ,[registro].[ABOGADO] AS abo_fac
                              ,[registro].[INICIAL] as ini_fac
                              ,[facturacion].[CODCLI] AS codcli_fac
                              ,[facturacion].[CLIENTE] AS cliente_fac
                              ,[facturacion].[ORDEN] AS orden_fac
                              ,[facturacion].[AREA] AS area_fac
                              ,[facturacion].[T_CLI]  AS modo_fac
                              ,[facturacion].[FECHA] AS fecha_fac
                              ,[facturacion].[TARIFA] AS tarifa_fac
                              ,[facturacion].[TOTAL] AS total_fac
                              ,[facturacion].[NORDEN] AS codorden_fac
                              ,[registro].[uid] AS clave_res
                              ,[facturacion].[uid] AS clave_fac
                          FROM [tiemposhoras].[dbo].[registro]
                          INNER JOIN [tiemposhoras].[dbo].[facturacion] ON ([facturacion].[padre]=[registro].[UID])
                          WHERE [registro].[NORDEN]!=0  AND [registro].[CODCLI]!='' AND [registro].[uid]=$i AND [registro].[FECHA]>='2017-01-01 00:00:00'
                           ;
                          ";
                $stmt = $this->pdo->prepare($query);
                $stmt->execute();
                $times = $stmt->fetchAll();
                $check=new Mysqlcheck();
                if(is_array($times) && count($times)>0) {
                    if ($times[0]['CLIENTE'] != $times[0]['cliente_fac']) {//si es diferente el cliente en facturacion
                        $nameC = trim($times[0]['cliente_fac']);
                    } else {
                        $nameC = trim($times[0]['CLIENTE']);
                    }
                    $nameC=$check->CheckNameCustomer($nameC); // se arregla el nombre del cliente
                    if ($times[0]['ORDEN'] != $times[0]['orden_fac']) {//si es diferente la orden de trabajo en facturacion
                        $nameOT = trim($times[0]['orden_fac']);
                    } else {
                        $nameOT = trim($times[0]['ORDEN']);
                    }
                    $nameOT=$check->CheckNameProcess($nameOT);//se arregla en nombre del asunto
                    if ($times[0]['AREA'] != $times[0]['area_fac']) {//si es diferente el area en facturacion
                        $nameArea = trim($times[0]['area_fac']);
                    } else {
                        $nameArea = trim($times[0]['AREA']);
                    }
                    if ($times[0]['ABOGADO'] != $times[0]['area_fac']) {//si es diferente el abogado en facturacion
                        $nameUser = trim($times[0]['ABOGADO']);
                        $short_name = trim($times[0]['INICIAL']);
                    } else {
                        $nameUser = trim($times[0]['abo_fac']);
                        $short_name = trim($times[0]['ini_fac']);
                    }
                    if ($times[0]['TARIFA'] != $times[0]['tarifa_fac'] && $times[0]['tarifa_fac'] > 0) {//si es diferente el tarifa en facturacion
                        $tarifa = doubleval($times[0]['tarifa_fac']);
                    } else {
                        $tarifa = doubleval($times[0]['TARIFA']);
                    }
                    if ($times[0]['TOTAL'] != $times[0]['total_fac'] && $times[0]['total_fac'] > 0) {//si es diferente el total en facturacion
                        $total = doubleval($times[0]['total_fac']);
                    } else {
                        $total = doubleval($times[0]['TOTAL']);
                    }
                    //validamos que existan los registros en la base de datos
                    $checkUser = $check->checkUser($nameUser, $short_name);
                    $checkBusiness = $check->checkBusiness($nameC, $nameOT);
                    $checkArea = $check->checkArea($nameArea);
                    if (count($checkBusiness) > 0) {// si existe el asunto
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
                                }
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
                        } else {
                            $countError++;
                            $times[0]['error'] = $checkBusiness['error'];
                            $log->error("\r\n".$countError.'; No se pudo insertar el registro; '.$checkBusiness['desc'].'; UID; '.$times[0]['clave_res'].';'.$nameC.';'.$nameOT.";\r\n");
                        }
                    }
                }else{
                   // $log->error("\r\n".'; No se existe el id;'.$i."\r\n");
                }

            }//fin for
        } catch (\PDOException $exception) {
            return "Error ejecutando la consulta: " . $exception->getMessage().' - '.$exception->getLine();
        }
    }

}