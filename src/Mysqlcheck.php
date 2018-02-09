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
            $sql='SELECT cmrID FROM tmc_customers_tbl_cmr WHERE cmrName LIKE "'.$name.'" OR cmrComercial LIKE  "'.$name.'";';
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
            if(count($area)>0){
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
            if(count($user)>0){
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
            if(!empty($sql)) {
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute();
                return $this->pdo->lastInsertId();
            }
        } catch (\PDOException $exception) {
            print_r($exception->getMessage());
        }
    }

    public function CheckNameCustomer($name){

        switch ($name) {
            case "SEABORD MARINE":  $name="SEABORD MARINE LTD";
                break;
            case "CONSTRUTORA OAS S.A. SUCURSAL DEL PERU":  $name="CONSTRUCTORA OAS S.A. SUCURSAL DEL PERU";
                break;
            case "GIUSEPPE NATALINI SFORZA":  $name="GIUSEPPE NATALINI";
                break;
            case "VERONICA D' ORNELLAS RADZIWILL":  $name="VENONICA D' ORNELLAS";
                break;
            case "OZINKA PERU":  $name="OZINCA PERU";
                break;
            case "RAFAEL SALAZAR":  $name="RAFAEL SALAZAR BUENO";
                break;
            case "JUAN GUERRA":  $name="JUAN GUERRA NAVARRO";
                break;
            case "MARTA D´URSO":  $name="MARTA DEL VALLE D'URSO PRIETO DE MADUEÑO";
                break;
            case "VILMA MEDANIC":  $name="VILMA MEDANIC LUPIS";
                break;
            case "IAM ADVISORS":  $name="IAM ADVISORS (CASO FAMILIA RICCI)";
                break;
            case "INVERSIONES MAJE S.A.C.":  $name="INVERSIONES MAJE S.A.";
                break;
            case "SEALED AIR":  $name="SEALED AIR CORPORATION";
                break;
            case "CARLO DE FERRARI":  $name="CARLO DE FERRARI BRIGNOLE";
                break;
            case "JOSE MACIA":  $name="JOSE MACIA PORTELL";
                break;
            case "FARO CAPITAL SAFI S.A.":  $name="FARO CAPITAL SAF S.A.";
                break;
            case "TRAVELERS CASUALTY AND SURETY COMPANY OF AMERICA":  $name="TRAVELERS";
                break;
            case "COLIMAN AVOCADO":  $name="COLIMAN AVOCADOS S.A.C.";
                break;
            case "01LABS S.A.C.":  $name="01 LABS S.A.C.";
                break;
            case "AGROPECUARIA PAMAJOSA S.A.C.":  $name="AGROPECUARIA PAMAJOSA S.A.";
                break;
            case "ANDERS ZIEDEK":  $name="ANDERS ZIEDEK WERNER HANS";
                break;
            case "PROCTER & GAMBLE INDUSTRIAL PERU S.R.L.":  $name="PROCTER & GAMBLE PERU S.R.L.";
                break;
        }
        return $name;
    }
    public function CheckNameProcess($name){
        switch ($name) {
            case "MATRIZ DE CONTINGENCIAS ZAÑA":  $name="MATRIZ DE CONTINGENCIA ZAÑA";
                break;
            case "PROCESO JUDICIAL- MG NATURA PERU S.A.C.":  $name="PROCESO JUDICIAL - MG NATURA PERU S.A.C.";
                break;
            case "GENERALES":  $name="GENERAL";
                break;
            case "REGULARIZACIÓN ADMISIONES TEMPORALES  INFORME PLANEAMIENTO TRIBUTARIO EXPORTACION":  $name="REGULARIZACIÓN ADMISIONES TEMPORALES";
                break;
            case "COBRO DE DEUDA - D'COMIDA S.A.C.":  $name="COBRO DE DEUDA - D'COMIDA S.A.C.";
                break;
            case "Personal.":  $name="PERSONAL";
                break;
            case "REVISION ESTRUCUTRA TRIBUTARIA 2017":  $name="REVISION ESTRUCTURA TRIBUTARIA 2017";
                break;
            case "IMPLICANCIAS TRIBUTARIASC SERVICIOS DEL EXTERIOR":  $name="IMPLICANCIAS TRIBUTARIAS SERVICIOS DEL EXTERIOR";
                break;
            case "HORARIO DIFERENCIADO.":  $name="HORARIO DIFERENCIADO";
                break;
        }
        return $name;
    }


    public function InsertArea($name){
        try {
            $sql="INSERT INTO practice_areas (name, _status) values ('".$name."', 1);";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            return $this->pdo->lastInsertId();
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

    public function checkBusiness($nameC,$nameOT){
        try {
            $customer=$this->checkCustomers($nameC);
            if(count($customer)>0){//si se encontro el cliente
                $process=$this->checkProcess($nameOT);// se busca elk nombre de la orden de trabajo
                $cmrid=$customer[0]['cmrID'];
                $num=count($process);
                if($num>0){
                    if($num>1) {
                        $i=0;
                        $band=false;
                       while($i<$num && !$band) {
                           $sql = "SELECT buzID, buzCurID FROM tmc_business_rel_buz WHERE  buzCmrID=$cmrid and buzPcsID=" . $process[$i]['pcsID'] . ";";
                           $stmt = $this->pdo->prepare($sql);
                           $stmt->execute();
                           $business = $stmt->fetchAll();
                           if (count($business) > 0) {
                               $band = true;
                           }
                           $i++;
                       }
                       if($band){
                           return $business;
                       }else{
                           return array('error' => '102', 'desc' => "No existe el asunto para ese cliente en la BD");
                       }
                    }else{
                        $sql = "SELECT buzID, buzCurID FROM tmc_business_rel_buz WHERE  buzCmrID=$cmrid and buzPcsID=" . $process[0]['pcsID'] . ";";
                        $stmt = $this->pdo->prepare($sql);
                        $stmt->execute();
                        $business = $stmt->fetchAll();
                        if (count($business) > 0) {
                            return $business;
                        }else{
                            return array('error' => '102', 'desc' => "No existe el asunto para ese cliente en la BD");
                        }
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