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
        $connection = new ConnectionMySQL();
        $this->pdo = $connection->connect();
        $customer=array();
    }

    /**
     * @param string $estado
     * @return array|string
     */
    public function fetchAllCustomersMySQL()
    {
        try {
            $query = "SELECT 
                       buzID, buzCmrID, buzCurID, buzPcsID,pcsDsc, cmrName, cmrComercial
                      FROM
                        tmc_business_rel_buz
                       inner join tmc_process_tbl_pcs on buzPcsID=pcsID
                       inner join tmc_customers_tbl_cmr on cmrID=buzCmrID";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll();

        } catch (\PDOException $exception) {
            return "Error ejecutando la consulta: " . $exception->getMessage();
        }
    }



    /*public function fetchAllCustomersForExcel(){
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
    }*/
}