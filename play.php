<?php

require_once 'vendor/autoload.php';

use TM\Clientes;
use TM\Asuntos;
use TM\ExcelGenerator;
use TM\Contactos;
use TM\Areas;
use TM\TiposAsuntos;
use TM\Usuarios;
use TM\Tarifas;
use TM\Tiempos;

$clientes = new Clientes();
$excelGenerator = new ExcelGenerator($clientes->fetchAllCustomersForExcel(),'/vagrant/Clientes.xlsx', 'Clientes');
$excelGenerator->GenerateExcelDocument();

$asuntos = new Asuntos();
$excelGenerator = new ExcelGenerator($asuntos->fetchAllBusinessFoxExcel(),'/vagrant/Asuntos.xlsx', 'Asuntos');
$excelGenerator->GenerateExcelDocument();

$contactos = new Contactos();
$excelGenerator = new ExcelGenerator($contactos->fetchAllContactsForExcel(),'/vagrant/Contactos.xlsx', 'Contactos');
$excelGenerator->GenerateExcelDocument();

$areas = new Areas();
$excelGenerator = new ExcelGenerator($areas->fetchAllAreasForExcel(),'/vagrant/Areas.xlsx', 'Areas');
$excelGenerator->GenerateExcelDocument();

$tiposAsuntos = new TiposAsuntos();
$excelGenerator = new ExcelGenerator($tiposAsuntos->fetchAllBusinessTypesForExcel(),'/vagrant/TiposAsuntos.xlsx', 'TiposAsuntos');
$excelGenerator->GenerateExcelDocument();

$usuarios = new Usuarios();
$excelGenerator = new ExcelGenerator($usuarios->fetchAllUsersForExcel(),'/vagrant/Usuarios.xlsx', 'Usuarios');
$excelGenerator->GenerateExcelDocument();

$tarifasAsunto = new Tarifas();
$excelGenerator = new ExcelGenerator($tarifasAsunto->fetchBusinessCategoryRatesForExcel(),'/vagrant/TarifasAsuntosCategoria.xlsx', 'TarifasAsuntosCategoria');
$excelGenerator->GenerateExcelDocument();

$excelGenerator = new ExcelGenerator($tarifasAsunto->fetchBusinessRatesForExcel(),'/vagrant/TarifasAsuntos.xlsx', 'TarifasAsuntos');
$excelGenerator->GenerateExcelDocument();

$excelGenerator = new ExcelGenerator($tarifasAsunto->fetchRatesByRolForExcel(),'/vagrant/TarifasCategorias.xlsx', 'TarifasCategorias');
$excelGenerator->GenerateExcelDocument();

$tiempos = new Tiempos();
$excelGenerator = new ExcelGenerator($tiempos->fetchAllTimesForExcel(),'/vagrant/Tiempos.xlsx', 'Tiempos');
$excelGenerator->GenerateExcelDocument();