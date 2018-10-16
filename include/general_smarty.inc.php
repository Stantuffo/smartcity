<?php
################ SMARTY ##################
define('SMARTY_DIR', BASEDIR . '/include/smarty/libs/');
define('WEBTHESIGN_DIR', BASEDIR);

require_once(SMARTY_DIR . 'Smarty.class.php');
//require_once(SMARTY_DIR . 'SmartyPaginate.class.php');
//require 'multipage.smarty.php';

// smarty configuration
$smarty = new Smarty();
$smarty->template_dir = ROOT.'/templates';
$smarty->config_dir = ROOT.'/include/smarty/configs';
$smarty->compile_dir = ROOT.'/include/smarty/template_c';
$smarty->cache_dir = ROOT.'/include/smarty/cache';

#---------------------------Passo le Costanti-----------------

$smarty->assign('LANG_CODE',                LANG_CODE);
$smarty->assign('LANGUAGE_FILE',            LANGUAGE_FILE);

// required connect
//SmartyPaginate::connect();

// set items per page
//SmartyPaginate::setLimit(25);

// Tolgo dall'url il parametro del Paginate
/*$temp = $_GET;
if(isset($temp[SmartyPaginate::getUrlVar()]))
	unset($temp[SmartyPaginate::getUrlVar()]);

$query_string = array();


foreach($temp as $chiave => $valore) {
	if(is_array($valore)) {
		foreach($valore as $key => $val) {
			$str = "{$chiave}[{$key}]={$val}";
			$query_string[] = $str;
		}
	} else {
		$query_string[] = "{$chiave}={$valore}";
	}
}

$query_string = implode('&', $query_string);

SmartyPaginate::setUrl($_SERVER['PHP_SELF'].($query_string != '' ? '?'.$query_string : ''));
SmartyPaginate::setPageLimit(7);

// Funzione wrapper per l'utilizzo di SmartyPaginate
function paginate($dati) {
	global $smarty;
	$multipagina = new multipage($smarty);
	return $multipagina->exec($dati);
}

// Funzione wrapper per l'utilizzo di SmartyPaginate
function paginate_query($query) {
	global $smarty;

	$multipagina = new multipage($smarty);

	// Recupero il numero di risultati
	$query_count = "SELECT IFNULL(COUNT(*), 0) as num FROM ({$query}) AS query";
	db::get_instance()->query($query_count);
	$info = db::get_instance()->row();

	list($offset, $limit) = $multipagina->fetch($info['num']);

	$query .= " LIMIT {$offset}, {$limit}";
	return db::get_instance()->fquery($query);
}*/

?>
