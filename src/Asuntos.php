<?php


namespace TM;
use TM\Mysqlcheck;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;


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
            $query = "SELECT TOP 1 [uid] FROM [tiemposhoras].[dbo].[registro] ORDER BY [uid] DESC";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute();
            $maxID = $stmt->fetchAll();

            $totalTimes = doubleval($maxID[0]['uid']);
            // create a log channel
            $log = new Logger('name');
            $log->pushHandler(new StreamHandler('path/to/your.log', Logger::WARNING));
            /*
             * // add records to the log
                $log->warning('Foo');
                $log->error('Bar');
             * */
            // add records to the log
            for($i= $totalTimes; $i> 0 ; $i--) {
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
                          WHERE [registro].[NORDEN]!=0  AND [registro].[CODCLI]!='' AND [registro].[uid]=$i
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
                    if ($times[0]['ORDEN'] != $times[0]['orden_fac']) {//si es diferente la orden de trabajo en facturacion
                        $nameOT = trim($times[0]['orden_fac']);
                    } else {
                        $nameOT = trim($times[0]['ORDEN']);
                    }
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
                            if (count($checkUser) > 0) {// si existe el usuario
                                if (!isset($checkUser['error'])) {
                                    if (count($checkArea) > 0) {// si existe el area
                                        if (!isset($checkUser['error'])) {
                                            $pgsBuzId = $checkBusiness[0]['buzID'];
                                            $pgsCurID = $checkBusiness[0]['buzCurID'];
                                            $pgsProID = $checkUser[0]['id'];
                                            $minuts = doubleval($times[0]['T_TRANS']) * 60;
                                            $minutswork = doubleval($times[0]['tiempo_fac']) * 60;
                                            $pgsDateWork = new \DateTime($times[0]['FECHA']);
                                            $pgsDateWork = $pgsDateWork->format('Y-m-d');
                                            $sql = "INSERT INTO tmc_progress_tbl_pgs ( pgsBuzID, pgsProID, original_user_id, pgsMinutsWork, pgsMinuts, pgsDateWork, pgsDetails,
                                              pgsHourRate, pgsTotal, pgsCurID, pgsStatus, pgsInvoiceble, migration) 
                                              VALUES ($pgsBuzId,$pgsProID,$pgsProID,$minutswork,$minuts,'" . $pgsDateWork . "','" . $times[0]['DETALLE'] . "',$tarifa,$total, $pgsCurID, 4, 1, 1)";
                                            $inserts[$times[0]['clave_res']] = $sql;
                                            var_dump($inserts);
                                        } else {
                                            $times[0]['error'] = $checkBusiness['error'];
                                            $arrayErrors[] = $times[0];
                                        }
                                    }
                                } else {
                                    $times[0]['error'] = $checkBusiness['error'];
                                    $arrayErrors[] = $times[0];
                                }
                            }

                        } else {
                            $times[0]['error'] = $checkBusiness['error'];
                            $arrayErrors[] = $times[0];
                        }
                    }
                }

            }//fin for
            echo '<pre>';
            var_dump($arrayErrors, $inserts);die;
            echo '<pre>';
        } catch (\PDOException $exception) {
            return "Error ejecutando la consulta: " . $exception->getMessage().' - '.$exception->getLine();
        }
    }

    /**
     * @return array|string
     */
    public function fetchAllBusinessFoxExcel(){
        try {
            $query =  "SELECT  M01ASU AS buzID,
                               M06NOM AS Cliente,
                               M01NOM AS Asunto,
                               M02DES AS TipoAsunto,
                               M01STI2 AS ReposnsableFacturacion,
                               M01REA AS codigo_externo_primario,
                               CASE WHEN ASUNTOS.BAJA = 'A' THEN 'Activo' ELSE 'Inactivo' END AS Estado,
                               CASE WHEN ASUNTOS.MODOFACTURACION = 'B' THEN 'Horas Limite de horas*'
                                    WHEN ASUNTOS.MODOFACTURACION = 'C' THEN 'Cuotas*'
                                    WHEN ASUNTOS.MODOFACTURACION = 'H' THEN 'Por horas'
                                    WHEN ASUNTOS.MODOFACTURACION = 'I' THEN 'Por Hitos o Etapas'
                                    WHEN ASUNTOS.MODOFACTURACION = 'L' THEN 'Libre'
                                    WHEN ASUNTOS.MODOFACTURACION = 'P' THEN 'Monto fijo'
                                    WHEN ASUNTOS.MODOFACTURACION = 'X' THEN 'Por Horas Con CAP'
                               END AS FormaFacturacion,
                               DEPDES AS Area,
                               CONCAT(OBSERVACIONES2,' ',OBSERVACIONES3) AS Notas,
                               CASE WHEN M01LEN = 'ESP' THEN 'Español' ELSE 'Ingles' END AS IdiomaAsunto,
                               CASE WHEN ASUNTOS.MODOFACTURACION = 'P' OR ASUNTOS.MODOFACTURACION = 'X' THEN M01PRS ELSE 0 END AS Tarifa,
                               M01MONFRA AS Moneda,
                               CASE WHEN M01FAC = 'N' THEN 'No Facturable' ELSE 'Facturable' END AS Facturable,
                               M01FEC AS created
                        FROM ASUNTOS
                        INNER JOIN CLIENTES ON (M01CL1 = M06COD)
                        LEFT JOIN DEPARTAMENTOS ON (M01DTO = DEPCOD)
                        LEFT JOIN TIPASU ON (M01TIP = M02COD);";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (\PDOException $exception) {
            return "Error ejecutando la consulta: " . $exception->getMessage();
        }
    }
}