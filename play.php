<?php

require_once 'vendor/autoload.php';

use TM\Asuntos;


$asuntos=new Asuntos();

var_dump($asuntos->fetchAllBusiness());
