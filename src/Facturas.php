<?php


namespace TM;


class Facturas
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

    public function getInvoicesBoleta($fecha){
        try {
            $query = ";";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute();
            $invoices = $stmt->fetchAll();

            $totalInvoices = count($invoices);

            for($i=0; $i<$totalInvoices; $i++){
                var_dump('Ajustando registro de factura '.($i+1).' de '.$totalInvoices);

                $foreign_key = $invoices[$i]['invOficialNum'];

                $tipoDocumento = '';
                switch($tipoDocumento){
                    case 1: if($invoices[$i]['invStatus'] == 1)
                                $tipoDocumento = 'F';
                            else
                                $tipoDocumento = 'A';
                            break;
                    case 5: $tipoDocumento = 'C';
                            break;
                    default:break;
                }

                $query = "SELECT MINTXT 
                          FROM MINULIN
                          WHERE MINNUM = $foreign_key AND MINTIP = '$tipoDocumento'";

                $stmt = $this->pdo->prepare($query);
                $stmt->execute();
                $invConcepts = $stmt->fetchAll();

                $concepto = '';
                foreach($invConcepts AS $invc){
                    $concepto .= str_replace("\n"," ", $invc['MINTXT'].' ');
                }

                $invoices[$i][17] = $concepto;
                $invoices[$i]['invConcept'] = $concepto;

                $finalInvoiceInsert[0]= $invoices[$i];
                $dataBaseInsert = new DatabaseInsert($finalInvoiceInsert, 'tmi_invoice_tbl_inv');
                $dataBaseInsert->generateInserts();
            }

            return true;

        }catch (\PDOException $exception) {
            return "Error ejecutando la consulta: " . $exception->getMessage().' - '.$exception->getLine();
        }
    }

    public function countInvoices()
    {
        
    }
}