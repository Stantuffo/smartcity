<?php
require 'include/general.inc.php';
require 'include/auth.inc.php';

$smarty->assign('head_placeholder', FRONT_DIR.'/head.tpl');
$smarty->assign('header_placeholder', FRONT_DIR.'/header.tpl');
$smarty->assign('home_body_placeholder', FRONT_DIR.'/newAppointment.tpl');
$smarty->assign('footer_placeholder', FRONT_DIR.'/footer.tpl');
$smarty->display(FRONT_DIR.'/index.tpl');
?>