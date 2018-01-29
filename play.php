<?php

require_once 'vendor/autoload.php';

use TM\Tiempos;

use TM\Clientes;

use TM\Asuntos;

/*$tiempos = new Tiempos();
$tiempos->countAll();*/

$asuntos=new Asuntos();
//var_dump($asuntos->countAll());
$asuntos=$asuntos->fetchAllBusiness();
//var_dump(count($asuntos));
$clientes=new Clientes();
$datamysql=$clientes->fetchAllCustomersMySQL();

//var_dump(end($datamysql));
//var_dump($datamysql);