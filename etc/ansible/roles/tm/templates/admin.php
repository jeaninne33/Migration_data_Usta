<?php
/**
 * Admin page for jaws
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

define('JAWS_SCRIPT', 'admin');
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

$request =& Jaws_Request::getInstance();
$gadget  = $request->get('gadget', 'post');
if (is_null($gadget)) {
    $gadget = $request->get('gadget', 'get');
    $gadget = !is_null($gadget) ? $gadget : '';
}

$action = $request->get('action', 'post');
if (is_null($action)) {
    $action = $request->get('action', 'get');
    $action = !is_null($action) ? $action : '';
}

$httpAuthEnabled = $GLOBALS['app']->Registry->Get('/config/http_auth') == 'true';
if ($httpAuthEnabled) {
    require_once JAWS_PATH . 'include/Jaws/HTTPAuth.php';
    $httpAuth = new Jaws_HTTPAuth();
}

// Check for login action is requested
if (!$GLOBALS['app']->Session->Logged())
{
    $loginMsg = '';
    if (($gadget == 'ControlPanel' && $action == 'Login') ||
        ($httpAuthEnabled && isset($_SERVER['PHP_AUTH_USER'])))
    {
        if ($httpAuthEnabled) {
            $httpAuth->AssignData();
            $user   = $httpAuth->getUsername();
            $passwd = $httpAuth->getPassword();
        } else {
            $user    = $request->get('username', 'post');
            $passwd  = $request->get('password', 'post');
            $crypted = $request->get('usecrypt', 'post');

            if ($GLOBALS['app']->Registry->Get('/crypt/enabled') == 'true' && isset($crypted)) {
                require_once JAWS_PATH . 'include/Jaws/Crypt.php';
                $JCrypt = new Jaws_Crypt();
                $JCrypt->Init();
                $passwd = $JCrypt->decrypt($passwd);
                if (Jaws_Error::IsError($passwd)) {
                    $passwd = '';
                }
            }
        }
        
        /*
         * Blocked user 
         */
        
        $model = & $GLOBALS['app']->LoadGadget('TMcore', 'AdminModel');
        
        $userdata = $model->GetUsersByName($user); // DESC ASC
        
        if($userdata['user_type'] == 3){
        	$GLOBALS['app']->Session->Logout();
        }else{

            //$param['auth_method'] = 'Default';

	        $param = $request->get(array('redirect_to', 'remember', 'auth_method'), 'post');

            $useLdap = $model->GetParameter('ldapAuthentication');
            if(!empty($useLdap)){
                if($useLdap['ptrValue'] == 'true'){
                    $param['auth_method'] = 'LDAP';
                }
            }

	        $login = $GLOBALS['app']->Session->Login($user,
	                                                 $passwd, 
	                                                 isset($param['remember']),
	                                                 $param['auth_method']);
	        if (!Jaws_Error::IsError($login)) {
	            // Can enter to Control Panel?
	            if ($GLOBALS['app']->Session->GetPermission('ControlPanel', 'default_admin')) {
	                $redirectTo = isset($param['redirect_to'])? $param['redirect_to'] : '';
	                if (substr($redirectTo, 0, 1) == '?') {
	                    $redirectTo = str_replace('&amp;', '&', $redirectTo);
	                } else {
	                    $redirectTo = "admin.php?gadget=TMcore";
	                }
	                header('Location: '.$redirectTo);
	                exit;
	            } else {
	                $GLOBALS['app']->Session->Logout();
	                $loginMsg = _t('GLOBAL_ERROR_LOGIN_NOTCP');
	            }
	        } else {
	            $loginMsg = $login->GetMessage();
	        }
        
        }
    }

    if ($httpAuthEnabled) {
        $httpAuth->showLoginBox();
    } else {
        // Init layout
        $GLOBALS['app']->InstanceLayout();
        $cpl = $GLOBALS['app']->LoadGadget('ControlPanel', 'AdminHTML');
        echo $cpl->ShowLoginForm($loginMsg);
    }

    exit;
}

// Can use Control Panel?
$GLOBALS['app']->Session->CheckPermission('ControlPanel', 'default_admin');

// Check for requested gadget
if (isset($gadget) && !empty($gadget)) {
    $ReqGadget = ucfirst($gadget);
    // Convert first letter to ucase to backwards compability
    if (Jaws_Gadget::IsValid($ReqGadget)) {
        $ReqAction = !empty($action) ? $action : 'Admin';
    } else {
        //Jaws_Error::Fatal('Invalid requested gadget');
        $redirectTo = "admin.php?gadget=TMman&action=OptIngresoTiempos";
        header('Location: '.$redirectTo);
    }
} else {
    $ReqGadget = 'TMcore';
    $ReqAction = 'Admin';
}

// Check for permission tu action to execute
//FIXME: I'm unsure about treat an action as a task, it could be useful
//   but I prefer not to do it. -ion
//$GLOBALS['app']->Session->CheckPermission($ReqGadget, $ReqAction);
$GLOBALS['app']->Session->CheckPermission($ReqGadget, 'default_admin');

$goGadget = $GLOBALS['app']->LoadGadget($ReqGadget, 'AdminHTML');
if (Jaws_Error::IsError($goGadget)) {
    Jaws_Error::Fatal("Error loading gadget: $ReqGadget");
}

$goGadget->SetAction($ReqAction);
$action = $goGadget->GetAction();
$IsReqActionStandAlone = $goGadget->isStandAloneAdmin($action);

// If requested action is `stand alone' just print it
if ($IsReqActionStandAlone) {
    $ReqResult = $goGadget->Execute();
} else {
    // Init layout
    $GLOBALS['app']->InstanceLayout();

    // If requested action
    if ($goGadget->IsAdmin($action)) {
        $GLOBALS['app']->Layout->LoadControlPanelHead();
        $ReqResult = $GLOBALS['app']->Layout->PutGadget($goGadget->GetName(), $action);
        $GLOBALS['app']->Layout->Populate($goGadget, true, $ReqResult);
    } else {
        //Jaws_Error::Fatal("Invalid operation: You can't execute requested action");
        $redirectTo = "admin.php?gadget=TMman&action=OptIngresoTiempos";
        header('Location: '.$redirectTo);
    }

    $GLOBALS['app']->Layout->LoadControlPanel($ReqGadget);
    $ReqResult = $GLOBALS['app']->Layout->Get();
}

// Send content to client
echo $ReqResult;

// Sync session
$GLOBALS['app']->Session->Synchronize();
$GLOBALS['log']->End();
