<?php


namespace TM;

use PDO;

class Usuarios
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
    public function fetchAllUsers(){
        try {
            $query = "SELECT   row_number() OVER (ORDER BY SOCCOD) +2 id,
                               CASE WHEN (SOCMAIL = 'mgonzalez@pgplegal.com' OR
                                         SOCMAIL = 'secretaria@pgplegal.com' OR
                                         SOCMAIL = 'secretaria2@pgplegal.com' OR
                                         SOCMAIL = 'sjimenez@pgplegal.com') AND
                                         (SOCABR = 'CMO' OR
                                         SOCABR = 'LSA' OR
                                         SOCABR = 'LVLA' OR
                                         SOCABR = 'MFSS' OR
                                         SOCABR = 'MRGM' OR
                                         SOCABR = 'SJO') THEN CONCAT(SOCABR,'.',LOWER(SOCMAIL)) ELSE LOWER(SOCMAIL) END AS username,
                               '0d2a5f9ebc8209902cdbb1f19d787188' AS passwd,
                               SOCNOM AS fname,
                               SOCNOM AS nickname,
                               CASE WHEN SOCNOM = 'SGA' THEN 'SGA'
                                    WHEN SOCNOM = 'YINNA MARCELA MARTÍNEZ RAMÍREZ' THEN 'YMMR'
                                    ELSE UPPER(SOCABR) END AS short_name,
                               CASE WHEN SOCTIP = 'S' OR SOCTIP = 'C' THEN 1 ELSE 2 END AS user_type,
                               CASE WHEN SOCTIP = 'S' OR SOCTIP = 'C' THEN
                                    CASE WHEN SOCIOS.NIVEL = 3 THEN 1
                                         WHEN SOCIOS.NIVEL = 2 THEN 2
                                         WHEN SOCIOS.NIVEL = 5 THEN 3
                                         WHEN SOCIOS.NIVEL = 1 THEN 4
                                         WHEN SOCIOS.NIVEL = 11 THEN 5
                                         WHEN SOCIOS.NIVEL = 7 THEN 6
                                         WHEN SOCIOS.NIVEL = 8 THEN 7
                                         ELSE NULL
                                    END
                                ELSE NULL END AS id_user_roll,
                               LOWER(SOCMAIL) AS email,
                               CASE WHEN SOCIOS.BAJA = 'A' THEN 1 ELSE 0 END AS enabled,
                               SOCFAL AS createtime,
                               SOCFBA AS updatetime
                        FROM SOCIOS
                        LEFT JOIN TABLA01 ON (SOCABR = T01USU)
                        LEFT JOIN NIVEL ON (SOCIOS.NIVEL = NIVEL.CODIGO);";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (\PDOException $exception) {
            return "Error ejecutando la consulta: " . $exception->getMessage();
        }
    }

    public function fetchAllUsersForExcel(){
        try {
            $query = "SELECT   row_number() OVER (ORDER BY SOCCOD) +2 id,
                               LOWER(SOCMAIL) AS usuario,
                               SOCNOM AS Nombre,
                               SOCNOM AS Nickname,
                               UPPER(SOCABR) AS short_name,
                               CASE WHEN SOCTIP = 'S' OR SOCTIP = 'C' THEN 'Usuario' ELSE 'Auxiliar' END AS TipoUsuario,
                               NIVEL.DESCRIPCION AS Rol,
                               LOWER(SOCMAIL) AS Email,
                               CASE WHEN SOCIOS.BAJA = 'A' THEN 'Activo' ELSE 'Inactivo' END AS Estado,
                               SOCFAL AS createtime,
                               SOCFBA AS updatetime
                        FROM SOCIOS
                        LEFT JOIN TABLA01 ON (SOCABR = T01USU)
                        LEFT JOIN NIVEL ON (SOCIOS.NIVEL = NIVEL.CODIGO);";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (\PDOException $exception) {
            return "Error ejecutando la consulta: " . $exception->getMessage();
        }
    }
}