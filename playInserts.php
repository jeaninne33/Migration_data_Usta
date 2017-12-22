<?php

require_once 'vendor/autoload.php';

use TM\Clientes;
use TM\Asuntos;
use TM\Contactos;
use TM\Areas;
use TM\TiposAsuntos;
use TM\Usuarios;
use TM\Tarifas;
use TM\Tiempos;
use TM\DatabaseInsert;
use TM\DescripcionesAsuntos;
use TM\TipoGasto;
use TM\TableCleaner;
use TM\Gastos;
use TM\Facturas;

/*$tableCleaner = new TableCleaner();

if($tableCleaner->cleanTables()) {

    $clientes = new Clientes();
    $clientesArray = $clientes->fetchAllCustomers();
    $dataBaseInsert = new DatabaseInsert($clientesArray, 'tmc_customers_tbl_cmr');

    if ($dataBaseInsert->generateInserts()) {
        $descripcionesAsuntos = new DescripcionesAsuntos();
        $descripcionesAsuntosArray = $descripcionesAsuntos->fetchAllBusinessDescriptions();
        $dataBaseInsert = new DatabaseInsert($descripcionesAsuntosArray, 'tmc_process_tbl_pcs');

        if ($dataBaseInsert->generateInserts()) {
            $areas = new Areas();
            $areasArray = $areas->fetchAllAreas();
            $dataBaseInsert = new DatabaseInsert($areasArray, 'practice_areas');

            if ($dataBaseInsert->generateInserts()) {
                $contactos = new Contactos();
                $contactosArray = $contactos->fetchAllContacts();
                $dataBaseInsert = new DatabaseInsert($contactosArray, 'tmc_contacts_tbl_cts');

                if($dataBaseInsert->generateInserts()) {

                    $contactosArray = $contactos->fetchContacstFromPersonasTable();
                    $dataBaseInsert = new DatabaseInsert($contactosArray, 'tmc_contacts_tbl_cts');

                    if ($dataBaseInsert->generateInserts()) {
                        $tiposAsunto = new TiposAsuntos();
                        $tiposAsuntoArray = $tiposAsunto->fetchAllBusinessTypes();
                        $dataBaseInsert = new DatabaseInsert($tiposAsuntoArray, 'tmc_businesstype_tbl_but');

                        if ($dataBaseInsert->generateInserts()) {
                            $tiposGasto = new TipoGasto();
                            $tiposGastoArray = $tiposGasto->fetchAllExpensesTypes();
                            $dataBaseInsert = new DatabaseInsert($tiposGastoArray, 'tipo_gasto');

                            if ($dataBaseInsert->generateInserts()) {
                                $usuarios = new Usuarios();
                                $usuariosArray = $usuarios->fetchAllUsers();
                                $dataBaseInsert = new DatabaseInsert($usuariosArray, 'users');

                                if ($dataBaseInsert->generateInserts()) {
                                    $asuntos = new Asuntos();
                                    $asuntosArray = $asuntos->fetchAllBusiness();
                                    $dataBaseInsert = new DatabaseInsert($asuntosArray, 'tmc_business_rel_buz');

                                    if ($dataBaseInsert->generateInserts()) {

                                        $tarifas = new Tarifas();
                                        $tarifasArray = $tarifas->fetchRatesByRol();
                                        $dataBaseInsert = new DatabaseInsert($tarifasArray, 'rate_category');

                                        if ($dataBaseInsert->generateInserts()) {
                                            $tarifasArray = $tarifas->fetchBusinessRates();
                                            $dataBaseInsert = new DatabaseInsert($tarifasArray, 'tmc_rates_tbl_rts');

                                            if ($dataBaseInsert->generateInserts()) {
                                                $tarifasArray = $tarifas->fetchBusinessCategoryRates();
                                                $dataBaseInsert = new DatabaseInsert($tarifasArray, 'tmc_rates_tbl_rts');
                                            }

                                            if ($dataBaseInsert->generateInserts()) {

                                                $gastos = new Gastos();
                                                if ($gastos->fetchAndInsertAllExpenses()) {
                                                    $tiempos = new Tiempos();

                                                    if($tiempos->fetchAndInsertAllTimes()){*/
                                                        $facturas = new Facturas();
                                                        $facturas->fecthAllInvDocumentsAndInsert();
                                                    /*}
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}*/

