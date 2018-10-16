<?php
// Folder
define("ROOT",                                      $_SERVER['DOCUMENT_ROOT']."/smartcity");
define("WEBPATH",                                   (isset($_SERVER['HTTPS']) ? "https" : "http")."://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT']."/smartcity");
define("FRONT_DIR",                                 ROOT.'/templates/front');
define("ADMIN_DIR",                                 ROOT.'/templates/admin');
define("IMG_DIR",                                   ROOT.'/images');
define("LANG_ROOT",                                 ROOT.'/include/smarty/configs');

// DB
define('DB_HOST',                                   'localhost');
define('DB_NAME',                                   'smartcity');
define('DB_USER',                                   'smartcity');
define('DB_PASSWORD',                               'smartcity');

?>