<?php  
if(!isset($_SESSION)){
    @session_start();
}

if(!isset($_SESSION['Language'])){
    $_SESSION['Language'] = "Italiano";
    $_SESSION['LangCode'] = "it";
    $_SESSION['IDLANG'] = 5;
}

// Language file 
$LANG_CODE      = $_SESSION['LangCode'];
$LANGUAGE_FILE 	= $_SESSION['Language'].".conf";
$ERROR_FILE		= $_SESSION['Language'].".error.ini";
?>
