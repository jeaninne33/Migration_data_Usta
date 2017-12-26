<?php


namespace TM;

use PDO;

class Tiempos
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
    public function fetchAndInsertAllTimes(){
        try {
            $query = "SELECT TOP 1 M11NRR FROM MOVICONT ORDER BY M11NRR DESC";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute();
            $maxID = $stmt->fetchAll();

            $totalTimes = doubleval($maxID[0]['M11NRR']);

            $usersObj = new Usuarios();
            $users = $usersObj->fetchAllUsers();

            $timesCounter = 1;
            for($i= $totalTimes; $i> 0 ; $i--) {
                $query = "SELECT M11NRR AS pgsID,
                                 M11ASU AS pgsBuzID,
                                 FORMAT(M11FEC, 'yyyy-MM-dd') AS pgsDateWork,
                                 M11IMP AS pgsTotal,
                                 CASE WHEN MINNUM = 0 THEN 1 ELSE 4 END AS pgsStatus,
                                 CASE WHEN M11USR = 'CER' THEN 'CERH' ELSE M11USR END AS pgsProID,
                                 M11MIND AS pgsMinutsWork,
                                 M11MIND - M11MINDTO AS pgsMinuts,
                                 M11IMB AS pgsHourRate,
                                 CASE WHEN M11MON = 'COP' THEN 1
                                      WHEN M11MON = 'USD' THEN 2
                                      WHEN M11MON = 'EUR' THEN 3
                                      WHEN M11MON = 'PES' THEN 4
                                 END AS pgsCurID,
                                 M11TXT AS pgsDetails,
                                 M11FEG AS created,
                                 1 AS migration
                          FROM MOVICONT
                          WHERE M11M20 = 1 AND M11NRR != 325781 AND M11NRR = $i;";
                $stmt = $this->pdo->prepare($query);
                $stmt->execute();
                $times = $stmt->fetchAll();
                if(isset($times[0]['pgsID'])) {
                    if($times[0]['pgsID']!='') {
                        var_dump('Ajustando registro de tiempo ' . $timesCounter . ' de ' . $totalTimes);
                        foreach ($users as $usr) {
                            if (strtoupper($times[0]['pgsProID']) == strtoupper($usr['short_name'])) {
                                $times[0]['pgsProID'] = $usr['id'];
                                $times[0][5] = $usr['id'];
                                break;
                            }
                        }

                        $finalTimesInsert[0] = $times[0];
                        $dataBaseInsert = new DatabaseInsert($finalTimesInsert, 'tmc_progress_tbl_pgs');
                        $dataBaseInsert->generateInserts();
                    }

                }
                $timesCounter++;
            }

            return true;
        } catch (\PDOException $exception) {
            return "Error ejecutando la consulta: " . $exception->getMessage();
        }
    }

    public function fetchAllTimesForExcel(){
        try {
            $query = "SELECT M11NRR AS pgsID,
                             M06NOM AS Cliente,
                             M01NOM AS Asunto,
                             FORMAT(M11FEC, 'yyyy-MM-dd') AS Fecha,
                             M11IMP AS Total,
                             CASE WHEN MINNUM = 0 THEN 'Activo' ELSE 'Facturado' END AS Estado,
                             M11USR AS Usuario,
                             ROUND(M11MIND/60,2) AS TiempoCobrado,
                             ROUND((M11MIND - M11MINDTO)/60,2) AS TiempoTrabajado,
                             M11IMB AS Tarifa,
                             M11MON AS Moneda,
                             '' AS Detalle,
                             FORMAT(M11FEG, 'yyyy-MM-dd') AS created
                      FROM MOVICONT
                      INNER JOIN ASUNTOS ON (M11ASU = M01ASU)
                      INNER JOIN CLIENTES ON (M01CL1 = M06COD)
                      WHERE M11M20 = 1 AND M11NRR != 325781;";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute();
            $times = $stmt->fetchAll();

            $totalTimes = count($times);

            $finalTimes = array();
            $i=1;
            foreach($times AS $t) {
                $foreignKey = $t['pgsID'];
                $query = "SELECT M10TXT FROM HISTORIC 
                          WHERE M10LIN != 0 AND CONNRR = $foreignKey";
                $stmt = $this->pdo->prepare($query);
                $stmt->execute();
                $details = $stmt->fetchAll();
                $detailsText = '';
                foreach($details as $det){
                    $detailsText .= str_replace("\n"," ", $det['M10TXT'].' ');
                }
                $t[11] = $detailsText;
                $t['Detalle'] = $detailsText;
                $finalTimes[]=$t;
                var_dump('Ajustando registro de tiempo '.$i.' de '.$totalTimes);
                $i++;
            }

            return $finalTimes;
        } catch (\PDOException $exception) {
            return "Error ejecutando la consulta: " . $exception->getMessage();
        }
    }

    public function countAll()
    {
        try {
            $sql = "SELECT COUNT(*) FROM facturacion";
            $stmt = $this->pdo->query($sql);
            $stmt->execute();
            print_r($stmt->fetchAll());
        } catch (\PDOException $exception) {
            print_r($exception->getMessage());
        }
    }
}