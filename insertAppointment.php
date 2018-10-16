<?php
require 'include/general.inc.php';
require 'include/auth.inc.php';
require 'include/customer.class.php';

if (!isset($_SESSION['userId'])) {
    header("Location: /smartcity/login");
}
$appointment['userId'] = $_SESSION['userId'];
$appointment['title'] = $_POST['title'];
$appointment['address'] = $_POST['address'];
$appointment['city'] = $_POST['city'];
$appointment['lat'] = $_POST['latitude'];
$appointment['long'] = $_POST['longitude'];
$user = new customer();
$user->addAppointment($appointment);
header("Location: /smartcity");
?>