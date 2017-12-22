<?php


namespace TM;


class Gastos
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
    public function fetchAndInsertAllExpenses(){
        try {
            $query = "SELECT M11NRR AS id_expense,
                             M11ASU AS business_id,
                             M20DES AS tipo_gasto_id,
                             FORMAT(M11FEC, 'yyyy-MM-dd') AS date,
                             M11TXT AS notes,
                             M11USR AS user_id,
                             M11IMP AS amount,
                             CASE WHEN MINNUM = 0 THEN 1 ELSE 4 END AS status_expense,
                             CASE WHEN M11MON = 'COP' THEN 1
                                  WHEN M11MON = 'USD' THEN 2
                                  WHEN M11MON = 'EUR' THEN 3
                                  WHEN M11MON = 'PES' THEN 4
                             END AS currency_id,
                             CASE WHEN M20NFC = 'N' THEN 1 ELSE 0 END AS invoiceable,
                             FORMAT(M11FEG, 'yyyy-MM-dd') AS created
                      FROM MOVICONT
                      INNER JOIN CONTABL ON (M11M20 = M20COD)
                      WHERE M11M20 != 1 AND M11NRR != 325781;";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute();
            $expenses = $stmt->fetchAll();

            $usersObj = new Usuarios();
            $users = $usersObj->fetchAllUsers();

            $tipoGastoObj = new TipoGasto();
            $tipoGasto = $tipoGastoObj->fetchAllExpensesTypes();

            $totalExpenses = count($expenses);

            for($i=0; $i<$totalExpenses ; $i++) {
                var_dump('Ajustando registro de gastos '.($i+1).' de '.$totalExpenses);

                foreach($users as $usr){
                    if (strtoupper($expenses[$i]['user_id']) == strtoupper($usr['short_name'])) {
                        $expenses[$i]['user_id'] = $usr['id'];
                        $expenses[$i][5] = $usr['id'];
                        break;
                    }
                }

                foreach($tipoGasto as $tg){
                    if($tg['descripcion']== $expenses[$i]['tipo_gasto_id']){
                        $expenses[$i]['tipo_gasto_id'] = $tg['id_tipo_gasto'];
                        $expenses[$i][2] = $tg['id_tipo_gasto'];
                    }
                }

                $finalExpenseInsert[0]= $expenses[$i];
                $dataBaseInsert = new DatabaseInsert($finalExpenseInsert, 'expenses');
                $dataBaseInsert->generateInserts();

            }

            return true;
        } catch (\PDOException $exception) {
            return "Error ejecutando la consulta: " . $exception->getMessage();
        }
    }

    /**
     * @return array|string
     */
    public function fetchAllExpensesForExcel(){
        try {
            $query = "SELECT M11NRR AS id_expense,
                             M06NOM AS Cliente,
                             M01NOM AS Asunto,
                             M20DES AS TipoGasto,
                             M20COD AS CodigoGasto,
                             FORMAT(M11FEC, 'yyyy-MM-dd') AS Fecha,
                             '' AS Detalle,
                             M11USR AS Usuario,
                             M11IMP AS Total,
                             CASE WHEN MINNUM = 0 THEN 'Activo' ELSE 'Facturado' END AS Estado,
                             M11MON AS Moneda,
                             'Facturable' AS Facturable,
                             FORMAT(M11FEG, 'yyyy-MM-dd') AS created
                      FROM MOVICONT
                      INNER JOIN ASUNTOS ON (M11ASU = M01ASU)
                      INNER JOIN CLIENTES ON (M01CL1 = M06COD)
                      INNER JOIN CONTABL ON (M11M20 = M20COD)
                      WHERE M11M20 != 1 AND M11NRR != 325781;";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute();
            $expenses = $stmt->fetchAll();

            $totalExpenses = count($expenses);

            $finalTimes = array();
            $i=1;
            foreach($expenses AS $e) {
                $foreignKey = $e['id_expense'];
                $query = "SELECT M10TXT FROM HISTORIC 
                          WHERE CONNRR = $foreignKey";
                $stmt = $this->pdo->prepare($query);
                $stmt->execute();
                $details = $stmt->fetchAll();
                $detailsText = '';
                foreach($details as $det){
                    $detailsText .= str_replace("\n"," ", $det['M10TXT'].' ');
                }
                $t[11] = $detailsText;
                $t['Detalle'] = $detailsText;
                $finalTimes[]=$t;
                var_dump('Ajustando registro de gastos '.$i.' de '.$totalExpenses);
                $i++;
            }

            return $finalTimes;
        } catch (\PDOException $exception) {
            return "Error ejecutando la consulta: " . $exception->getMessage();
        }
    }
}