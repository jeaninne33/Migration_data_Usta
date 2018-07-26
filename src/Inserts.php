<?php


namespace TM;

//use PDO;

class Inserts
{
    /**
     * @var \PDO
     */
    private $pdo;

    public function __construct()
    {
        $connectionInsert = new ConnectionMySQL();
        $this->pdo = $connectionInsert->connect();
    }
 
    public function InsertGeneral($sql){
        try {
            if(!empty($sql)) {
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute();
                return $this->pdo->lastInsertId();
            }
        } catch (\PDOException $exception) {
            print_r($exception->getMessage().' sql: '.$sql.' <br>');die;
        }
    }

   
    public function Consulta($sql){
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            $result=$stmt->fetchAll();
            return $result;
        } catch (\PDOException $exception) {
            print_r($exception->getMessage());
        }
    }


   

}