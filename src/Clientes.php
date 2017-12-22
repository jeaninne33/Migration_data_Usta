<?php


namespace TM;

use PDO;

class Clientes
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
     * @param string $estado
     * @return array|string
     */
    public function fetchAllCustomers()
    {
        try {
            $query = "SELECT M06COD AS cmrID, 
                             M06NOM AS cmrName,
                             M06NOM AS cmrComercial,
                             M06DOM AS cmrAddress,
                             M06POB AS cmrCity,
                             M06PRO AS cmrState,
                             M06CIF AS cmrNit,
                             M06FEC AS created,
                             M06OBS AS notes,
                             CASE WHEN M06LEN = 'ESP' THEN 1 ELSE 0 END AS cmrLang,
                             CASE WHEN BAJA = 'A' THEN 1 ELSE 0 END AS cmrStatus,
                             M06PAI AS cmrCountry,
                             CONCAT(M06TF1,', ',M06TF2,', ',M06FAX) AS cmrPhone,
                             CASE WHEN M06TIP = 1 THEN 3 ELSE 1 END AS cmrCttID,
                             CASE WHEN M06ORI = 'N' THEN 1 ELSE 2 END AS cmrNacional,
                             CASE WHEN M06RETICA = 'S' THEN 1 ELSE 0 END AS enableReteICA,
                             M06COD AS codigo_externo_primario,
                             M06FEA AS modified
                      FROM CLIENTES";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (\PDOException $exception) {
            return "Error ejecutando la consulta: " . $exception->getMessage();
        }
    }

    public function fetchAllCustomersForExcel(){
        try {
            $query = "SELECT M06COD AS cmrID, 
                             M06NOM AS Nombre,
                             M06PAI AS Pais,
                             M06PRO AS Departamento,
                             M06POB AS Ciudad,
                             M06DOM AS Dirección,
                             M06CIF AS NIT,
                             CONCAT(M06TF1,', ',M06TF2,', ',M06FAX) AS Telefono,
                             CASE WHEN M06TIP = 1 THEN 'Persona Jurídica' ELSE 'Persona Natural' END AS Regimen,
                             CASE WHEN M06LEN = 'ESP' THEN 'Español' ELSE 'Ingles' END AS Idioma,
                             M06OBS AS Notas,
                             CASE WHEN BAJA = 'A' THEN 'Activo' ELSE 'Inactivo' END AS Estado,
                             CASE WHEN M06ORI = 'N' THEN 'Nacional' ELSE 'Extranjero' END AS cmrNacional,
                             CASE WHEN M06RETICA = 'S' THEN 'Si' ELSE 'No' END AS AplicaReteica,
                             M06FEC AS created,
                             M06FEA AS modified
                      FROM CLIENTES";
            $stmt = $this->pdo->prepare($query);
            //$stmt->bindParam(':limite', $limite, 2);
            //$stmt->bindParam(':estado', $estado, 2);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (\PDOException $exception) {
            return "Error ejecutando la consulta: " . $exception->getMessage();
        }
    }
}