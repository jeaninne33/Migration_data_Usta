<?php

require_once 'vendor/autoload.php';

use TM\Tiempos;

use TM\Clientes;

use TM\Asuntos;

use TM\Mysqlcheck;

/*$tiempos = new Tiempos();
$tiempos->countAll();*/

$name='CASTROL DEL PERU S.A.';
//$check=new MysqlCheck();

//var_dump(new MysqlCheck());die;
//var_dump($check->checkCustomers($name));die;
$asuntos=new Asuntos();
$check=new Mysqlcheck();
var_dump($check->checkCustomers($name));
var_dump($asuntos->countAll());
//$asuntos=$asuntos->fetchAllBusiness();
//var_dump(count($asuntos));
//$clientes=new Clientes();
//$datamysql=$clientes->fetchAllCustomersMySQL();

//var_dump(end($datamysql));
//var_dump($datamysql);