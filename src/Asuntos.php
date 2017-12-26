<?php


namespace TM;

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
    public function fetchAllBusiness(){
        try {
            $query = "SELECT   [registro].[INICIO]
                              ,[registro].[TERMINO]
                              ,[registro].[T_TRANS]
                              ,[registro].[DECIMAL1]
                              ,[registro].[CODCLI]
                              ,[registro].[CLIENTE]
                              ,[registro].[ORDEN]
                              ,[registro].[AREA]
                              ,[registro].[T_CLI]  
                              ,[registro].[FECHA]
                              ,[registro].[TARIFA]
                              ,[registro].[TOTAL]
                              ,[registro].[NORDEN]
                               ,[facturacion].[INICIO] as inicio_fac
                              ,[facturacion].[TERMINO] as fin_fac
                              ,[facturacion].[T_TRANS] as tiempo_fac
                              ,[facturacion].[DECIMAL1] as deci_fac
                              ,[facturacion].[CODCLI] as codcli_fac
                              ,[facturacion].[CLIENTE] as cliente_fac
                              ,[facturacion].[ORDEN] as orden_fac
                              ,[facturacion].[AREA] as area_fac
                              ,[facturacion].[T_CLI]  as modo_fac
                              ,[facturacion].[FECHA] as fecha_fac
                              ,[facturacion].[TARIFA] as tarifa_fac
                              ,[facturacion].[TOTAL] as total_fac
                              ,[facturacion].[NORDEN] as codorden_fac
                          FROM [tiemposhoras].[dbo].[registro]
                          left join [facturacion] on ([padre]=[registro].[UID] and ([registro].[T_TRANS]!=[facturacion].[T_TRANS] or [facturacion].[NORDEN]!= [registro].[NORDEN]))
                          WHERE [registro].[NORDEN]!=0  AND [registro].[CODCLI]!='' ;";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute();

            $business = $stmt->fetchAll();

            $descripcionesObj = new DescripcionesAsuntos();
            $descripciones = $descripcionesObj->fetchAllBusinessDescriptions();

            $usersObj = new Usuarios();

            $users = $usersObj->fetchAllUsers();

            $totalBusiness = count($business);

            $patron = array ('á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U');

            for($i = 0; $i<$totalBusiness; $i++){
                var_dump('Ajustando registro de asuntos '.($i+1).' de '.$totalBusiness);
                foreach($descripciones AS $desc){
                    if(strtoupper(str_replace(array_keys($patron), array_values($patron), $business[$i]['buzPcsID']))==strtoupper(str_replace(array_keys($patron), array_values($patron), $desc['pcsDsc']))){
                        $business[$i]['buzPcsID'] = $desc['pcsID'];
                        $business[$i][2] = $desc['pcsID'];
                        break;
                    }
                }
                foreach($users as $usr){
                    if($usr['short_name'] != '' && $usr['short_name'] != null) {
                        if ($business[$i]['buzResponsable'] == $usr['short_name']) {
                            $business[$i]['buzResponsable'] = $usr['id'];
                            $business[$i][5] = $usr['id'];
                            break;
                        }
                    }else{
                        $business[$i]['buzResponsable'] = null;
                        $business[$i][5] = null;
                    }
                }
                if($business[$i]['practice_area_id']==0 || $business[$i]['practice_area_id']==''){
                    $business[$i]['practice_area_id'] = null;
                }

                $foreignKey = $business[$i]['buzID'];
                $query = "SELECT REPLACE(M01TEX, CHAR(13), ' ') AS M01TEX 
                          FROM ASULIN
                          WHERE M01ASU = $foreignKey";
                $stmt = $this->pdo->prepare($query);
                $stmt->execute();

                $details = $stmt->fetchAll();
                $detailsText = '';
                foreach($details as $det){
                    $detailsText .= $det['M01TEX'].' ';
                }
                if($detailsText != '') {
                    $business[$i][11] = $business[$i][11] . ' ' . $detailsText;
                    $times[$i]['buzNotes'] = $business[$i]['buzNotes'].' '.$detailsText;
                }
            }

            return $business;
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