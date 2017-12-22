<?php


namespace TM;


class TipoGasto
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

    public function fetchAllExpensesTypes(){
        try {
            $query = "SELECT row_number() OVER (ORDER BY M20COD) id_tipo_gasto,
                             M20DES AS descripcion,
                             M20COD AS short_name
                      FROM CONTABL
                      WHERE M20DES != 'HONORARIOS';";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (\PDOException $exception) {
            return "Error ejecutando la consulta: " . $exception->getMessage();
        }
    }

    public function fetchAllExpensesTypesForExcel(){
        try {
            $query = "SELECT row_number() OVER (ORDER BY M20COD) id_tipo_gasto,
                             M20DES AS descripcion,
                             M20COD AS short_name
                      FROM CONTABL
                      WHERE M20DES != 'HONORARIOS';";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (\PDOException $exception) {
            return "Error ejecutando la consulta: " . $exception->getMessage();
        }
    }
}