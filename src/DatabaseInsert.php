<?php


namespace TM;

use PDO;

class DatabaseInsert
{
    /**
     * @var \PDO
     */
    private $pdo;
    private $data;
    private $table;

    public function __construct(array $data, string $tableName)
    {
        $connectionInsert = new ConnectionInsert();
        $this->pdo = $connectionInsert->connect();
        $this->data = $data;
        $this->table = $tableName;
    }

    public function generateInserts(){
        $stringKeys = array();
        if(count($this->data)>0){
            $keys = array_keys($this->data[0]);
            foreach($keys as $k){
                if(is_string($k)){
                    $stringKeys[]=$k;
                }
            }
        }

        return $this->executeInsert($this->prepareQuery($stringKeys),$stringKeys);

    }

    private function prepareQuery(array $fields){

        var_dump('Preparando consulta de inserción de '.$this->table.'...');

        $columns = '';
        $values = '';

        foreach($fields as $flds){
            $columns .= $flds.', ';
            $values .= ':'.$flds.', ';
        }
        $columns = rtrim($columns,', ');
        $values = rtrim($values,', ');

        $query = "INSERT INTO $this->table (".$columns.") VALUES (".$values.");";

        return $query;
    }

    private function executeInsert(string $query, array $keys){

        var_dump('Ejecutando inserciones de '.$this->table.'...');

        $data = $this->data;

        foreach ($data as $row) {
            try {
                $temp = $row;
                $stmt = $this->pdo->prepare($query);
                foreach ($keys AS $key) {
                    if ($key == 'business_type_id' && $row[$key] == 0)
                        $row[$key] = null;
                    $stmt->bindParam(':' . $key, $row[$key]);
                }
                $stmt->execute();
            }catch(\PDOException $exception){

                $idReg = 0;
                switch($this->table){
                    case 'tmc_progress_tbl_pgs': $idReg = $temp['pgsID'].' usuario: '.$temp['pgsProID'];
                                                 break;
                    case 'expenses': $idReg = $temp['id_expense'].' usuario: '.$temp['pgsProID'];
                                              break;
                    default: break;
                }
                var_dump("Error ejecutando la consulta: " . $idReg .' - ' . $exception->getMessage());
                $this->logToFile('/vagrant/'.$this->table.'.log',' Registro: ' . $idReg .' - '.$exception->getMessage());
            }
        }

        return true;
    }

    private function logToFile($filename, $msg){
        // open file
        $fd = fopen($filename, "a");
        // write string
        fwrite($fd, $msg . "\n");
        // close file
        fclose($fd);
    }
}