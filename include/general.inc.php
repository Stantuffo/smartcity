<?php

if (session_id() == '') session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// -- Costanti di sistema -------------------------------------------------------------------------- //

require 'config/config.php';


// -- Informazioni lingue ----------------------------------------------------------------------------------------------- //

require 'lang.inc.php';
define("LANG_CODE",                 $LANG_CODE);
define("LANGUAGE_FILE",             $LANGUAGE_FILE);
define("ERROR_FILE",                $ERROR_FILE);
date_default_timezone_set("Europe/Rome");

$old_doc_uri = preg_replace('/(\/|\\\)$/', '', $_SERVER['DOCUMENT_ROOT']);
$_SERVER['DOCUMENT_ROOT'] = realpath(dirname(__FILE__) . '/..');
$GLOBALS['DOCUMENT_URI'] = str_replace(
    strtolower(str_replace('/', '\\', $old_doc_uri)), '', strtolower(str_replace('/', '\\', $_SERVER['DOCUMENT_ROOT']))
);
$_SERVER['DOCUMENT_ROOT'] = realpath(dirname(__FILE__) . '/..');
$GLOBALS['DOCUMENT_URI'] = str_replace('\\', '/', $GLOBALS['DOCUMENT_URI']);
$GLOBALS['DOCUMENT_URI'] = preg_replace('/^(.+)\/?$/', '\\1', $GLOBALS['DOCUMENT_URI']);

define('URL_ROOT',                  $GLOBALS['DOCUMENT_URI'].'/');
define('BASEDIR',                   $_SERVER['DOCUMENT_ROOT'].'/');
define('CONFIGS',                   $_SERVER['DOCUMENT_ROOT'].'/config/');
define('INCLUDES',                  $_SERVER['DOCUMENT_ROOT'].'/include/');
define('BASEURL',                   "http://".$_SERVER['HTTP_HOST']);

require 'general_smarty.inc.php';

$smarty->assign('LANG_CODE',        LANG_CODE);

$msg_error = "";
$msg_success = "";
$msg_warning = "";
$errore = array();
$valore = array();


// -- Stringhe di errore e inizializzazione stringhe validatore --------------------------------------------------------- //
require_once 'error.inc.php';

// Da impostare a true prima della compulazione della pagina per evitare i notice di smarty
$GLOBALS['stop_tracking_error'] = false;

// -- Funzioni varie ---------------------------------------------------------------------------------------------------- //
require_once 'query.class.php';

// -- Informazioni e Istanza Database ----------------------------------------------------------------------------------- //
require_once 'db.class.php';
$dbh = new db(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME, true);
?>
