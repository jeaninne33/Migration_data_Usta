<?php


namespace TM;


class TiposAsuntos
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

    public function fetchAllBusinessTypes(){
        try {
            $query = "SELECT M02COD AS butID,
                             M02DES AS butDsc,
                             CASE WHEN BAJA = 'A' THEN 1 ELSE 2 END AS butStatus
                      FROM TIPASU;";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (\PDOException $exception) {
            return "Error ejecutando la consulta: " . $exception->getMessage();
        }
    }

    public function fetchAllBusinessTypesForExcel(){
        try {
            $query = "SELECT M02COD AS butID,
                             M02DES AS butDsc,
                             CASE WHEN BAJA = 'A' THEN 'Activo' ELSE 'Inactivo' END AS butStatus
                      FROM TIPASU;";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (\PDOException $exception) {
            return "Error ejecutando la consulta: " . $exception->getMessage();
        }
    }
}