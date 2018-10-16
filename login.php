<?php
require 'include/general.inc.php';
require 'include/auth.inc.php';
require 'include/customer.class.php';
if (isset($_SESSION['userId'])){
    header('Location: /smartcity');
    exit();
}
if (isset($_POST['email']) && isset($_POST['password'])) {
    $servername = "localhost";
    $username = "smartcity";
    $password = "smartcity";
    $dbname = "smartcity";
    $conn = new mysqli($servername, $username, $password, $dbname);
    $email = $_POST['email'];
    $password = md5($_POST['password']);
    $customer = new customer();
    $query = "SELECT * FROM user WHERE email = '" . $email . "' AND password = '" . $password . "'";
    $result = $conn->query($query);
    $loginSuccess = "";
    if ($result->num_rows == 1) {
        $loginSuccess = true;
        $usr = $result->fetch_assoc();
    } else {
        header('Location: /smartcity/login');
    }
    $conn->close();

    if ($loginSuccess) {
        if (session_id() == '') session_start();
        $_SESSION['userId'] = $usr['id'];
        $_SESSION['userName'] = $usr['first_name'];
        $_SESSION['userSurname'] = $usr['last_name'];
        $_SESSION['usrLat'] = $usr['usrLat'];
        $_SESSION['usrLong'] = $usr['usrLong'];
    } else {
        header('Location: /smartcity/login');
    }
}
if (isset($_SESSION['userId'])) {
    header('Location: /smartcity');
}
$smarty->assign('msg_error', $msg_error);
$smarty->assign('msg_success', $msg_success);
$smarty->assign('msg_warning', $msg_warning);
$smarty->assign('errore', $errore);
$smarty->assign('valore', $valore);
$smarty->assign('head_placeholder', FRONT_DIR . '/head.tpl');
$smarty->assign('header_placeholder', FRONT_DIR . '/header.tpl');
$smarty->assign('home_body_placeholder', FRONT_DIR . '/login.tpl');
$smarty->assign('footer_placeholder', FRONT_DIR . '/footer.tpl');
$smarty->display(FRONT_DIR . '/index.tpl');
?>