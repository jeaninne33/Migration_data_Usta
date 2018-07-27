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
            print_r($exception->getMessage().' sql: '.$sql.' <br>'); //die;
          //  $result=['error'=>1, 'msj'=>$exception->getMessage()."sql: .$sql.<br>"];
           // return $result;
        }
    }
    public function InsertCorrecto($var, $log, $msj, $output){
        try {
            if(isset($var['error'])) {//hubo error en la insercion     
                $log->error("  \r\n".$var['msj']." \r\n");
                return '';
            }
            $log->info("  \r\n".$msj. $var."  \r\n");
            $output.='<tr><td>';
            $output.=$msj. $var;
            $output.='</td></tr>';
            return  $output;
        } catch (\PDOException $exception) {
            print_r($exception->getMessage().' sql: '.$sql.' <br>');
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