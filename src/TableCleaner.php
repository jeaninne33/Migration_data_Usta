<?php


namespace TM;


class TableCleaner
{
    /**
     * @var \PDO
     */
    private $pdo;

    public function __construct()
    {
        $connectionInsert = new ConnectionInsert();
        $this->pdo = $connectionInsert->connect();
    }

    /**
     * @return bool
     */
    public function cleanTables(){
        try {
            $query = "DELETE FROM practice_areas;";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute();

            $query = "ALTER TABLE practice_areas AUTO_INCREMENT = 1;";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute();

            $query = "DELETE FROM tipo_gasto;";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute();

            $query = "ALTER TABLE tipo_gasto AUTO_INCREMENT = 1;";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute();

            $query = "INSERT INTO  tmi_invoicemode_tbl_imo (imoID, imoDsc, imoStatus, imoTS) VALUES ('9',  'Facturación Libre',  '1', CURRENT_TIMESTAMP);";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute();

            $query = "INSERT INTO tmc_currency_tbl_cur (curID, curShort, curDsc, curExchangeRate, curStatus, decimales, curTS) VALUES (NULL, 'PES', 'Pesos con centavos', '0', '1', '0', CURRENT_TIMESTAMP);";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute();

            $query = "UPDATE  user_roll SET  roll_description =  'Asociado' WHERE  user_roll.user_roll_id =3;";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute();

            $query = "UPDATE  user_roll SET  roll_description =  'Socio Senior' WHERE  user_roll.user_roll_id =4;";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute();

            $query = "INSERT INTO user_roll (user_roll_id, roll_description, user_type_val, deleted, monto_max_gasto_id) VALUES (5, 'Socio Juinor',  1,  0, NULL), (6, 'Practicante', 1, 0, NULL), (7, 'Arquitecta', 1, 0, NULL);";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute();

            $query = "INSERT INTO invoice_types (id, name, short_name) VALUES (5, 'Cuenta de Cobro', NULL);";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute();

            return true;

        } catch (\PDOException $exception) {
            var_dump("Error ejecutando la consulta: " . $exception->getMessage());
            return false;
        }
    }
}