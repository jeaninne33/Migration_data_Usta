<?php

namespace TM;


class Connection
{
    /**
     * @var string
     */
    const HOST = '190.60.251.138';

    /**
     * @var string
     */
    const PORT = '49303';

    /**
     * @var string
     */
    const DB_NAME = 'tiemposhoras';

    /**
     * @var string
     */
    const USERNAME = 'sa';

    /**
     * @var string
     */
    const PASSWORD = 'Colombia1';

    /**
     * Se conecta a la base de datos remota en PGP y retorna un objeto PDO o una excepción
     * @return \PDO
     */
    public function connect() : \PDO
    {
        try {
            return new \PDO(
                "sqlsrv:Server=".self::HOST.",".self::PORT.";Database=".self::DB_NAME,
                self::USERNAME,
                self::PASSWORD,
                [
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
                ]
            );
        } catch (\PDOException $exception) {
            die("Error conectando al servidor: " . $exception->getMessage());
        }

    }

}