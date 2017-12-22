<?php


namespace TM;


class Tarifas
{
    /**
     * @var \PDO
     */
    private $pdo;
    private $pdoMysql;

    public function __construct()
    {
        $connection = new Connection();
        $this->pdo = $connection->connect();
        $connectionMysql = new ConnectionInsert();
        $this->pdoMysql = $connectionMysql->connect();
    }

    /**
     * @return array|string
     */
    public function fetchBusinessCategoryRates(){
        try {
            $query = "SELECT M06COD AS rtsCmrID,
                             TARIFEXPED.M01ASU AS rtsBuzID,
                             TARIFA AS rtsRate,
                             CASE WHEN TARIFEXPED.NIVEL = 3 THEN 1
                                  WHEN TARIFEXPED.NIVEL = 2 THEN 2
                                  WHEN TARIFEXPED.NIVEL = 5 THEN 3
                                  WHEN TARIFEXPED.NIVEL = 1 THEN 4
                                  WHEN TARIFEXPED.NIVEL = 11 THEN 5
                                  WHEN TARIFEXPED.NIVEL = 7 THEN 6
                                  WHEN TARIFEXPED.NIVEL = 8 THEN 7
                             END AS roll_id,
                             CASE WHEN MONEDA = 'COP' THEN 1
                                  WHEN MONEDA = 'USD' THEN 2
                                  WHEN MONEDA = 'EUR' THEN 3
                                  WHEN MONEDA = 'PES' THEN 4 
                             END AS rtsCurID,
                             CASE WHEN TARIFEXPED.BAJA = 'A' THEN 1 ELSE 2 END AS rtsStatus
                      FROM TARIFEXPED
                      INNER JOIN ASUNTOS ON (TARIFEXPED.M01ASU = ASUNTOS.M01ASU)
                      INNER JOIN CLIENTES ON (ASUNTOS.M01CL1 = CLIENTES.M06COD)
                      INNER JOIN NIVEL ON (TARIFEXPED.NIVEL = NIVEL.CODIGO)
                      WHERE NIVEL != 10;";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute();
            $rates = $stmt->fetchAll();

            $finalRates = array();

            $totalRates = count($rates);

            for($i=0; $i<$totalRates; $i++){
                $foreignKey = $rates[$i]['rtsBuzID'];
                $query = "SELECT buzPcsID FROM tmc_business_rel_buz 
                          WHERE buzID = $foreignKey";
                $stmt = $this->pdoMysql->prepare($query);
                $stmt->execute();
                $pcsID = $stmt->fetchAll();

                $rates[$i]['rtsBuzID'] = $pcsID[0]['buzPcsID'];
                $rates[$i][0] = $pcsID[0]['buzPcsID'];

                $finalRates[] = $rates[$i];
            }

            return $finalRates;

        } catch (\PDOException $exception) {
            return "Error ejecutando la consulta: " . $exception->getMessage();
        }
    }

    /**
     * @return array|string
     */
    public function fetchBusinessCategoryRatesForExcel(){
        try {
            $query = "SELECT M06NOM AS Cliente,
                             M01NOM AS Asunto,
                             DESCRIPCION AS Nivel,
                             TARIFA AS Tarifa,
                             MONEDA AS Moneda,
                             CASE WHEN TARIFEXPED.BAJA = 'A' THEN 'Activa' ELSE 'Inactiva' END AS Estado
                      FROM TARIFEXPED
                      INNER JOIN ASUNTOS ON (TARIFEXPED.M01ASU = ASUNTOS.M01ASU)
                      INNER JOIN CLIENTES ON (ASUNTOS.M01CL1 = CLIENTES.M06COD)
                      INNER JOIN NIVEL ON (TARIFEXPED.NIVEL = NIVEL.CODIGO)
                      WHERE NIVEL != 10;";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (\PDOException $exception) {
            return "Error ejecutando la consulta: " . $exception->getMessage();
        }
    }

    /**
     * @return array|string
     */
    public function fetchBusinessRates(){
        try {
            $activeUsers = $this->GetActiveUsers();

            $query = "SELECT M06COD AS rtsCmrID,
                             TARIFEXPED.M01ASU AS rtsBuzID,
                             TARIFA AS rtsRate,
                             0 AS rtsProID,
                             CASE WHEN MONEDA = 'COP' THEN 1
                                  WHEN MONEDA = 'USD' THEN 2
                                  WHEN MONEDA = 'EUR' THEN 3
                                  WHEN MONEDA = 'PES' THEN 4 
                             END AS rtsCurID,
                             CASE WHEN TARIFEXPED.BAJA = 'A' THEN 1 ELSE 2 END AS rtsStatus
                      FROM TARIFEXPED
                      INNER JOIN ASUNTOS ON (TARIFEXPED.M01ASU = ASUNTOS.M01ASU)
                      INNER JOIN CLIENTES ON (ASUNTOS.M01CL1 = CLIENTES.M06COD)
                      INNER JOIN NIVEL ON (TARIFEXPED.NIVEL = NIVEL.CODIGO)
                      WHERE NIVEL != 10;";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute();
            $rates = $stmt->fetchAll();

            $finalRates = array();

            $totalRates = count($rates);

            for($i=0; $i<$totalRates; $i++){
                $foreignKey = $rates[$i]['rtsBuzID'];
                $query = "SELECT buzPcsID FROM tmc_business_rel_buz 
                          WHERE buzID = $foreignKey";
                $stmt = $this->pdoMysql->prepare($query);
                $stmt->execute();
                $pcsID = $stmt->fetchAll();

                $rates[$i]['rtsBuzID'] = $pcsID[0]['buzPcsID'];
                $rates[$i][0] = $pcsID[0]['buzPcsID'];

                foreach($activeUsers AS $actu){
                    $rates[$i][3] = $actu['id'];
                    $rates[$i]['rtsProID'] = $actu['id'];
                    $finalRates[] = $rates[$i];
                }
            }

            return $finalRates;

        } catch (\PDOException $exception) {
            return "Error ejecutando la consulta: " . $exception->getMessage();
        }
    }

    /**
     * @return array|string
     */
    public function fetchBusinessRatesForExcel(){
        try {

            $activeUsers = $this->GetActiveUsers();

            $query = "SELECT M06NOM AS Cliente,
                             M01NOM AS Asunto,
                             0 AS Usuario,
                             TARIFA AS Tarifa,
                             MONEDA AS Moneda,
                             CASE WHEN TARIFEXPED.BAJA = 'A' THEN 'Activa' ELSE 'Inactiva' END AS Estado
                      FROM TARIFEXPED
                      INNER JOIN ASUNTOS ON (TARIFEXPED.M01ASU = ASUNTOS.M01ASU)
                      INNER JOIN CLIENTES ON (ASUNTOS.M01CL1 = CLIENTES.M06COD)
                      INNER JOIN NIVEL ON (TARIFEXPED.NIVEL = NIVEL.CODIGO)
                      WHERE NIVEL = 10;";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute();
            $rates = $stmt->fetchAll();

            $finalRates = array();

            foreach($rates AS $rts){
                foreach($activeUsers AS $actu){
                    $rts[2] = $actu['SOCNOM'];
                    $rts['Usuario'] = $actu['SOCNOM'];
                    $finalRates[] = $rts;
                }
            }

            return $finalRates;

        } catch (\PDOException $exception) {
            return "Error ejecutando la consulta: " . $exception->getMessage();
        }
    }

    public function fetchRatesByRol(){
        try {
            $query = "SELECT CASE WHEN NIVEL = 3 THEN 1
                                  WHEN NIVEL = 2 THEN 2
                                  WHEN NIVEL = 5 THEN 3
                                  WHEN NIVEL = 1 THEN 4
                                  WHEN NIVEL = 11 THEN 5
                                  WHEN NIVEL = 7 THEN 6
                                  WHEN NIVEL = 8 THEN 7
                             END AS user_roll_id,
                             TARIFASNIVEL.TARIFA AS rate,
                             CASE WHEN TIPOTARIFAS.MONEDA = 'COP' THEN 1
                                  WHEN TIPOTARIFAS.MONEDA = 'USD' THEN 2
                                  WHEN TIPOTARIFAS.MONEDA = 'EUR' THEN 3
                                  WHEN TIPOTARIFAS.MONEDA = 'PES' THEN 4
                             END AS currency_id,
                             CASE WHEN TARIFASNIVEL.BAJA = 'A' THEN 1 ELSE 2 END AS status
                      FROM TARIFASNIVEL
                      INNER JOIN TIPOTARIFAS ON (TIPOTARIFA = TIPOTARIFAS.CODIGO)
                      WHERE NIVEL NOT IN (4,6,10);";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (\PDOException $exception) {
            return "Error ejecutando la consulta: " . $exception->getMessage();
        }
    }

    public function fetchRatesByRolForExcel(){
        try {
            $query = "SELECT NIVEL.DESCRIPCION AS Nivel,
                             TARIFASNIVEL.TARIFA AS Tarifa,
                             TIPOTARIFAS.MONEDA AS Moneda,
                             CASE WHEN TARIFASNIVEL.BAJA = 'A' THEN 'Activa' ELSE 'Inactiva' END AS Estado
                      FROM TARIFASNIVEL
                      INNER JOIN NIVEL ON (NIVEL = NIVEL.CODIGO)
                      INNER JOIN TIPOTARIFAS ON (TIPOTARIFA = TIPOTARIFAS.CODIGO)
                      WHERE NIVEL != 10;";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (\PDOException $exception) {
            return "Error ejecutando la consulta: " . $exception->getMessage();
        }
    }

    private function GetActiveUsers(){
        try {

            $query = "SELECT row_number() OVER (ORDER BY SOCCOD) +2 id,
                             SOCNOM
                      FROM SOCIOS
                      WHERE (SOCTIP = 'S' OR SOCTIP = 'C') AND SOCIOS.BAJA = 'A';";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll();

        } catch (\PDOException $exception) {
            return "Error ejecutando la consulta: " . $exception->getMessage();
        }
    }
}