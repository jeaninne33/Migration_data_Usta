<?php


namespace TM;


class Areas
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
    public function fetchAllAreas(){
        try {
            $query = "SELECT DEPCOD AS id_practice_area,
                             DEPDES AS name,
                             CASE WHEN BAJA = 'A' THEN 1 ELSE 2 END AS _status
                      FROM DEPARTAMENTOS";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (\PDOException $exception) {
            return "Error ejecutando la consulta: " . $exception->getMessage();
        }
    }

    public function fetchAllAreasForExcel(){
        try {
            $query = "SELECT DEPCOD AS id_practice_area,
                             DEPDES AS Nombre,
                             CASE WHEN BAJA = 'A' THEN 'Activa' ELSE 'Inactiva' END AS Estado
                      FROM DEPARTAMENTOS";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (\PDOException $exception) {
            return "Error ejecutando la consulta: " . $exception->getMessage();
        }
    }
}