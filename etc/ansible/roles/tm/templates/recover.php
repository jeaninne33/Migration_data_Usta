<?php
/**
 * Index page for jaws
 *
 * @category   Application
 * @package    Core
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Helgi �ormar <dufuz@php.net>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2005-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT & ~E_WARNING);

define('JAWS_SCRIPT', 'index');
define('BASE_SCRIPT', basename(__FILE__));
define('APP_TYPE',    'web');

// Redirect to the installer if JawsConfig can't be found.
$root = dirname(__FILE__);
if (!file_exists($root . '/config/JawsConfig.php')) {
    header('Location: install/index.php');
    exit;
} else {
    header("Cache-Control: no-cache, must-revalidate"); //HTTP 1.1
    header("Pragma: no-cache"); //HTTP 1.0
    header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
    header("Cache-Control: max-age=0");
    require $root . '/config/JawsConfig.php';
}

require_once JAWS_PATH . 'include/Jaws/InitApplication.php';
$GLOBALS['app']->loadClass('ACL', 'Jaws_ACL');

// Init layout...
$GLOBALS['app']->InstanceLayout();
$GLOBALS['app']->Layout->Load();

$IsIndex = false;
$objGadget = null;
$IsReqActionStandAlone = false;
$request =& Jaws_Request::getInstance();
// Get forwarded error from webserver
$ReqError = $request->get('http_error', 'get');
if (empty($ReqError) && $GLOBALS['app']->Map->Parse()) {
    $ReqGadget = $request->get('gadget');
    $ReqAction = $request->get('action');
    if (empty($ReqGadget)) {
        $IsIndex = true;
        $ReqGadget = $GLOBALS['app']->Registry->Get('/config/main_gadget');
    }

    if (!empty($ReqGadget)) {
        if (Jaws_Gadget::IsValid($ReqGadget)) {
            $objGadget = $GLOBALS['app']->LoadGadget($ReqGadget);
            if (Jaws_Error::IsError($objGadget)) {
                Jaws_Error::Fatal("Error loading gadget: $ReqGadget");
            }

            if ($GLOBALS['app']->Session->GetPermission($ReqGadget, 'default')) {
                $ReqAction = empty($ReqAction)? 'DefaultAction' : $ReqAction;
                $objGadget->SetAction($ReqAction);
                $ReqAction = $objGadget->GetAction();
                $GLOBALS['app']->SetMainRequest($IsIndex, $ReqGadget, $ReqAction);
            } else {
                $ReqError = '403';
            }
        } else {
            $ReqError = '404';
        }
    }
} else {
    $ReqError = empty($ReqError)? '404' : $ReqError;
    $ReqGadget = null;
    $ReqAction = null;
}

// Run auto-load methods before standalone actions too
$GLOBALS['app']->RunAutoload();

if (empty($ReqError)) {
    $ReqResult = '';
    if (!empty($objGadget)) {
        $ReqResult = $objGadget->Execute();
        if (Jaws_Error::isError($ReqResult)) {
            $ReqResult = $ReqResult->GetMessage();
            $GLOBALS['log']->Log(JAWS_LOG_ERROR, 'In '.$ReqGadget.'::'.$ReqAction.','.$ReqResult);
        }
        // we must check type of action after execute, because gadget can change it at runtime
        $IsReqActionStandAlone = $objGadget->IsStandAlone($ReqAction);
    }
} else {
    require_once JAWS_PATH . 'include/Jaws/HTTPError.php';
    $ReqResult = Jaws_HTTPError::Get($ReqError);
}

if (!$IsReqActionStandAlone) {
    $GLOBALS['app']->Layout->Populate($objGadget, $IsIndex, $ReqResult);
    $ReqResult = $GLOBALS['app']->Layout->Get();
}

// Send content to client
echo $ReqResult;

// Sync session
$GLOBALS['app']->Session->Synchronize();
$GLOBALS['log']->End();
