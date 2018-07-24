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
            print_r($exception->getMessage());
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
    public function InsertUser($name, $short_name){
        try {
            $sql="INSERT INTO users (username, passwd,email, user_type,nickname, fname,short_name, enabled, photo, admin_view)
                values ('".strtolower($short_name)."@ehernandez.com.pe','','".strtolower($short_name)."@ehernandez.com.pe',3,'".$name."','".$name."','".$short_name."', 1,'',1);";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            return $this->pdo->lastInsertId();
        } catch (\PDOException $exception) {
            print_r($exception->getMessage());
        }
    }

   

}