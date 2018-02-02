<?php


namespace TM;

//use PDO;

class Mysqlcheck
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

    public function checkCustomers($name){
        try {
            $sql="select cmrID from tmc_customers_tbl_cmr where cmrName like '%".$name."' or cmrComercial like  '%".$name."';";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (\PDOException $exception) {
            print_r($exception->getMessage());
        }

    }
    public function checkProcess($name){
        try {
            $sql="select pcsID from tmc_process_tbl_pcs where pcsDsc='".$name."';";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (\PDOException $exception) {
            print_r($exception->getMessage());
        }
    }

    public function checkBusiness($name,$cmrid){
        try {
            $process=$this->checkProcess($name);
            /*if(count($process)>0){
                $sql="select pcsID from tmc_process_tbl_pcs where pcsDsc like '%".$name."%';";
            }else{

            }*/

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (\PDOException $exception) {
            print_r($exception->getMessage());
        }

    }

}