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
            $sql="SELECT cmrID FROM tmc_customers_tbl_cmr WHERE cmrName LIKE '%".$name."' OR cmrComercial LIKE  '%".$name."';";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (\PDOException $exception) {
            print_r($exception->getMessage());
        }

    }
    public function checkProcess($name){
        try {
            $sql="SELECT pcsID FROM tmc_process_tbl_pcs WHERE pcsDsc='".$name."';";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (\PDOException $exception) {
            print_r($exception->getMessage());
        }
    }

    public function checkArea($name){
        try {
            $sql="SELECT id_practice_area FROM practice_areas WHERE name='".$name."';";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            $area=$stmt->fetchAll();
            if($area>0){
                return $area;
            }else{
                return array('error'=>'103','desc'=>"No existe el area en la BD");
            }
        } catch (\PDOException $exception) {
            print_r($exception->getMessage());
        }
    }

    public function checkUser($name,$short_name){
        try {
            $sql="SELECT id FROM users WHERE short_name='$short_name' OR fname LIKE '%".$name."%';";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            $user=$stmt->fetchAll();
            if($user>0){
                return $user;
            }else{
                return array('error'=>'104','desc'=>"No existe el usuario en la BD");
            }
        } catch (\PDOException $exception) {
            print_r($exception->getMessage());
        }
    }

    public function InsertTime($sql){
        try {
            if(!empty($sql))
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
        } catch (\PDOException $exception) {
            print_r($exception->getMessage());
        }
    }

    public function checkBusiness($nameC,$nameOT){
        try {
            $customer=$this->checkCustomers($nameC);
            if(count($customer)>0){//si se encontro el cliente
                $process=$this->checkProcess($nameOT);// se busca elk nombre de la orden de trabajo
                $cmrid=$customer[0]['cmrID'];
                if(count($process)>0){
                    $sql="SELECT buzID, buzCurID FROM tmc_business_rel_buz WHERE  buzCmrID=$cmrid and buzPcsID=".$process[0]['pcsID'].";";
                    $stmt = $this->pdo->prepare($sql);
                    $stmt->execute();
                    $business=$stmt->fetchAll();
                    if(count($business)>0){
                        return $business;
                    }else{
                        return array('error'=>'102','desc'=>"No existe el asunto para ese cliente en la BD");
                    }
                }else{
                    return array('error'=>'101','desc' =>"No existe el nombre del asunto en la BD");
                }
            }else{
                return array('error'=>'100', 'desc'=>"No existe el cliente en la BD");
            }
        } catch (\PDOException $exception) {
            print_r($exception->getMessage());
        }

    }

}