<?php


namespace TM;

class AsuntosMySQL
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
    public function fetchAllBusiness(){
        try {
            $query = "SELECT M01ASU AS buzID,
                             M01CL1 AS buzCmrID,
                             M01NOM AS buzPcsID,
                             M01TIP AS business_type_id,
                             M01FEC AS created,
                             M01STI2 AS buzResponsable,
                             M01REA AS expediente,
                             CASE WHEN BAJA = 'A' THEN 1 ELSE 2 END AS buzStatus,
                             CASE WHEN MODOFACTURACION = 'B' THEN 1
                                  WHEN MODOFACTURACION = 'C' THEN 1
                                  WHEN MODOFACTURACION = 'H' THEN 1
                                  WHEN MODOFACTURACION = 'I' THEN 4
                                  WHEN MODOFACTURACION = 'L' THEN 9
                                  WHEN MODOFACTURACION = 'P' THEN 3
                                  WHEN MODOFACTURACION = 'X' THEN 6
                             END AS buzImoID,
                             CASE WHEN M01MONFRA = 'COP' THEN 1
                                  WHEN M01MONFRA = 'USD' THEN 2
                                  WHEN M01MONFRA = 'EUR' THEN 3
                                  WHEN M01MONFRA = 'PES' THEN 4
                             END AS buzCurID,
                             M01DTO AS practice_area_id,
                             CONCAT(OBSERVACIONES2,' ',OBSERVACIONES3) AS buzNotes,
                             CASE WHEN M01LEN = 'ESP' THEN 1 ELSE 2 END AS language,
                             CASE WHEN MODOFACTURACION = 'P' OR MODOFACTURACION = 'X' THEN M01PRS ELSE 0 END AS buzMonthlyFixRate,
                             CASE WHEN M01FAC = 'N' THEN 1 ELSE 0 END AS buzNoInv
                      FROM ASUNTOS
                      WHERE M01ASU != 6786;";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute();

            $business = $stmt->fetchAll();

            /*$descripcionesObj = new DescripcionesAsuntos();
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
            }*/

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