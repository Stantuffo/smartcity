<?php
/**
 * Gestione delle credenziali di accesso allo script
 */

// -- Controllo delle credenziali in sessione --------------------------------------------------------------------------- //
if (session_id() == '') session_start();

// Se manca una sola delle credenziali, non posso dare l'autorizzazione
/*
if ((empty($_SESSION['userId'])) && RL == true) {
    // Cancello le eventuali credenziali dalla sessione
    unset($_SESSION);

    // Redirigo sulla pagina di login
    header("Location: {$GLOBALS['DOCUMENT_URI']}/login");
    exit;
}*/

$smarty->assign('lang', 				$_SESSION['LangCode']);

// -- Creo le costanti delle credenziali -------------------------------------------------------------------------------- //

if(isset($_SESSION['user'])) {
    $smarty->assign('name',             $_SESSION['user']['customers_firstname']);
    $smarty->assign('lastname',         $_SESSION['user']['customers_lastname']);
    $smarty->assign('email',            $_SESSION['user']['customers_email_address']);
    $smarty->assign('phone',            $_SESSION['user']['customers_telephone']);
    $smarty->assign('birth',            $_SESSION['user']['customers_dob']);
    $smarty->assign('profile_image',    $_SESSION['user']['customers_picture']);
    $smarty->assign('created', date('d/m/Y', $_SESSION['user']['created_at']));
    $shoppingcart = new product();
    $cart = $shoppingcart->get_shoppingcart($_SESSION['user']['customers_id']);
    $smarty->assign('cart', $cart);
    if (!isset($wnumber)){
        $wishlist = $shoppingcart->get_wishlist($_SESSION['user']['customers_id']);
        $wnumber = 0;
        foreach ($wishlist as $item){
            $wnumber++;
        }
        $smarty->assign('wnumber', $wnumber);
    }
    //utils::pre_print_r($cart, false);

    define('USER_NAME',                 !empty($_SESSION['user']) ? $_SESSION['user'] : null);
    define('USER_DEALER',               !empty($_SESSION['is_dealer']) ? $_SESSION['is_dealer'] : null);
    define('USER_ID',                   !empty($_SESSION['idutente']) ? $_SESSION['idutente'] : null);

    // Inserisco le credenziali in Smarty
    $smarty->assign('USER_ID',          USER_ID);
    $smarty->assign('USER_NAME',        USER_NAME);
    $smarty->assign('USER_DEALER',      USER_DEALER);
}

?>