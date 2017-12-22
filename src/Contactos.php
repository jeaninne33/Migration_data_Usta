<?php


namespace TM;

use PDO;

class Contactos
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
    public function fetchAllContacts(){
        try {
            $query = "SELECT row_number() OVER (ORDER BY M06COD) ctsID,
                             M06COD AS ctsCmrID,
                             CASE WHEN M06PC1 <> '' THEN M06PC1 ELSE M06PC2 END AS ctsName,
                             M06EMAIL AS ctsMail,
                             CONCAT(M06TF1,',',M06TF2) AS ctsTel
                      FROM CLIENTES WHERE M06PC1 <> '' OR M06PC2 <> '';";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (\PDOException $exception) {
            return "Error ejecutando la consulta: " . $exception->getMessage();
        }
    }

    public function fetchContacstFromPersonasTable(){
        try {
            $query = "SELECT CLIPER.M06COD AS ctsCmrID,
                               CONCAT(PERNOM,' ',PERAP1) AS ctsName,
                               PERCARGO AS ctsPosition,
                               CONCAT(PERTF1,CASE WHEN PERTF2 != '' THEN CONCAT(', ',PERTF2) ELSE '' END, CASE WHEN PERTF3 != '' THEN CONCAT(', ',PERTF3) ELSE '' END) AS ctsTel,
                               PERMOVIL AS ctsCel,
                               PEREMA AS ctsMail,
                               CONCAT (PEROBS,' ',PERATE, '', PERDOM) AS ctsNote
                        FROM CLIPER
                        INNER JOIN CLIENTES ON (CLIPER.M06COD = CLIENTES.M06COD)
                        INNER JOIN PERSONAS ON (CLIPER.PERCOD = PERSONAS.PERCOD)";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (\PDOException $exception) {
            return "Error ejecutando la consulta: " . $exception->getMessage();
        }
    }

    public function fetchAllContactsForExcel(){
        try {
            $query = "SELECT row_number() OVER (ORDER BY M06COD) ctsID,
                             M06NOM AS Cliente,
                             CASE WHEN M06PC1 <> '' THEN M06PC1 ELSE M06PC2 END AS NombreContacto,
                             M06EMAIL AS Email,
                             CONCAT(M06TF1,',',M06TF2) AS Telefono
                      FROM CLIENTES WHERE M06PC1 <> '' OR M06PC2 <> '';";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (\PDOException $exception) {
            return "Error ejecutando la consulta: " . $exception->getMessage();
        }
    }
}