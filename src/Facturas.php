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

    public function fecthAllInvDocumentsAndInsert(){
        try {
            $query = " SELECT  row_number() OVER (ORDER BY MINNUM) AS invID,
                               FORMAT(MINFEC, 'yyyy-MM-dd') AS invDate,
                               CASE WHEN MINTIP = 'A' THEN FORMAT(MINFEC, 'yyyy-MM-dd') ELSE NULL END AS invPreDate,
                               M01CL1 AS invCmrID,
                               MINNUM AS invOficialNum,
                               CASE WHEN MINTIP = 'A' THEN MINNUM ELSE NULL END AS invPreinvNum,
                               CASE WHEN MINMON = 'COP' THEN 1
                                    WHEN MINMON = 'USD' THEN 2
                                    WHEN MINMON = 'EUR' THEN 3
                                    WHEN MINMON = 'PES' THEN 4
                               END AS invCurrency,
                               ABS(MINDTO) AS invDiscount,
                               CASE WHEN MINDTOPOR IS NOT NULL THEN MINDTOPOR ELSE 0 END AS discount_percent,
                               ABS(MINIVA) AS invTax,
                               CASE WHEN ABS(MINDER + MINIVA) = 0 THEN ABS(MINGAS) ELSE ABS(MINDER + MINIVA) END AS invTotal,
                               CASE WHEN MINRET IS NOT NULL THEN ABS(MINRET) ELSE 0 END AS invRteFte,
                               CASE WHEN MINPREICA IS NOT NULL THEN ABS(MINPREICA) ELSE 0 END AS invRteIca,
                               CASE WHEN MINPREIVA IS NOT NULL THEN ABS(MINPREIVA) ELSE 0 END AS invRteTax,
                               FORMAT(FECDESDE, 'yyyy-MM-dd') AS invStartDate,
                               FORMAT(FECHASTA, 'yyyy-MM-dd') AS invEndDate,
                               FORMAT(MINFEC + 30, 'yyyy-MM-dd') AS invDueDate,
                               '' AS invConcept,
                               CASE WHEN MINTIP = 'A' THEN 5
                                    WHEN MINTIP = 'F' THEN 1
                                    WHEN MINTIP = 'X' THEN 1
                                    WHEN MINTIP = 'C' THEN 1
                               END AS invStatus,
                               MINCLF AS invCmrDID,
                               CASE WHEN MINTIP = 'A' OR MINTIP = 'F' THEN 1
                                    WHEN MINTIP = 'X' THEN 3
                                    WHEN MINTIP = 'C' THEN 5
                               END AS tipo_documento_id,
                               MINPIV AS VAT
                        FROM MINUTAS
                        INNER JOIN ASUNTOS ON (MINUTAS.MINASU = ASUNTOS.M01ASU)
                        WHERE MINTIP IN ('A','F','C') AND (ABS(MINDER + MINIVA) > 0 OR MINGAS > 0)
                        ORDER BY MINNUM ASC;";
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