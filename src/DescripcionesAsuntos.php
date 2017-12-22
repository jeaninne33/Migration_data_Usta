<?php


namespace TM;

use PDO;

class DescripcionesAsuntos
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
    public function fetchAllBusinessDescriptions(){
        try {
            $query = "SELECT row_number() OVER (ORDER BY M01NOM) pcsID,
                             M01NOM AS pcsDsc
                      FROM ASUNTOS
                      WHERE M01NOM IS NOT NULL
                      GROUP BY M01NOM
                      ORDER BY M01NOM ASC;";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (\PDOException $exception) {
            return "Error ejecutando la consulta: " . $exception->getMessage();
        }
    }
}