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

$subdomain = explode(".",$_SERVER["SERVER_NAME"]);
$root = '/vagrant/subscribers/'.$subdomain[0];
$configPath = $root.'/config/JawsConfig.php';

define('SUBDOMAIN', $configPath);

define('JAWS_SCRIPT', 'admin');
define('BASE_SCRIPT', basename(__FILE__));
define('APP_TYPE',    'web');

// Redirect to the installer if JawsConfig can't be found.
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

require_once $root . '/include/Jaws/InitApplication.php';
$GLOBALS['app']->loadClass('ACL', 'Jaws_ACL');