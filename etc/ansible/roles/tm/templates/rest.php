<?php
/**
 * REST page for jaws.
 *
 * @category   Application
 * @package    Core
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @copyright  2005-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT & ~E_WARNING);

define('JAWS_SCRIPT', 'rest');
define('BASE_SCRIPT', basename(__FILE__));
define('PATH_SCRIPT', dirname(__FILE__));
define('APP_TYPE',    'rest');

$current = dirname(__FILE__);
// Redirect to the installer if JawsConfig can't be found.
if (!file_exists($current . '/config/JawsConfig.php')) {
    header("Location: install.php");
}

require_once $current  . '/include/Jaws/InitApplication.php';
require_once JAWS_PATH . 'include/Jaws/GadgetHTML.php';

Jaws_URLMapping::Parse($_SERVER['QUERY_STRING'], 'rest.php');

if (isset($_GET['gadget'])) {
    $gadget = $_GET['gadget'];
} else {
    $gadget = isset($_POST['gadget']) ? $_POST['gadget'] : '';
}

if (isset($_GET['action'])) {
    $action = $_GET['action'];
} else {
    $action = isset($_POST['action']) ? $_POST['action'] : '';
}

// Check for requested gadget
if (!empty($gadget)) {
    // Convert first letter to ucase to backwards compability
    $ReqGadget = ucfirst($gadget);
    require_once JAWS_PATH . 'include/Jaws/GadgetREST.php';
    if (!Jaws_Gadget::IsValid($ReqGadget)) {
        Jaws_Error::Fatal("Invalid requested gadget", __FILE__, __LINE__);
    }

    $ReqAction = !empty($action) ? $action : '';
} else {
    $ReqGadget = $GLOBALS['app']->Registry->Get('/config/main_gadget');
    if (!$ReqGadget) {
        Jaws_Error::Fatal("No default gadget is set, please activate a gadget in the control panel.");
    }

    $ReqAction = 'DefaultAction';
}

if (empty($ReqGadget)) {
    Jaws_Error::Fatal("Empty gadget, Registry or a missed table problem", __FILE__, __LINE__);
}

$goGadget = $GLOBALS['app']->LoadGadget($ReqGadget, 'REST');
if (Jaws_Error::IsError($goGadget)) {
    Jaws_Error::Fatal("Error loading gadget: $ReqGadget", __FILE__, __LINE__);
}

$goGadget->SetAction($ReqdAction);
// If requested action...
if (!$goGadget->IsNormal($goGadget->GetAction())) {
    Jaws_Error::Fatal("Invalid operation: You can't execute requested action", __FILE__, __LINE__);
}

echo $goGadget->Execute();
