<?php
require 'include/general.inc.php';
require 'include/auth.inc.php';
require 'include/customer.class.php';

if (!isset($_SESSION['userId'])) {
    header("Location: /smartcity/login");
    exit();
}

$userId = $_SESSION['userId'];
$usrLat = $_SESSION['usrLat'];
$usrLong = $_SESSION['usrLong'];
$user = new customer();
$impegniTemp = $user->get_impegni($userId);
$batteryLevel = rand (0 , 100);
$remainingKms = 250/100*$batteryLevel;

$impegni = array();
foreach ($impegniTemp as $impegnoTemp){
    $datiNavigazione = $user->GetDrivingDistance($usrLat, $impegnoTemp['lat'], $usrLong, $impegnoTemp['lon']);
    $impegnoTemp['datiNavigazione'] = $datiNavigazione;
    $impegni[] = $impegnoTemp;
}

//utils::pre_print_r($impegni, false);

$smarty->assign('remainingKms', $remainingKms);
$smarty->assign('batteryLevel', $batteryLevel);
$smarty->assign('impegni', $impegni);
$smarty->assign('head_placeholder', FRONT_DIR.'/head.tpl');
$smarty->assign('header_placeholder', FRONT_DIR.'/header.tpl');
$smarty->assign('home_body_placeholder', FRONT_DIR.'/home_body.tpl');
$smarty->assign('footer_placeholder', FRONT_DIR.'/footer.tpl');
$smarty->display(FRONT_DIR.'/index.tpl');
?>