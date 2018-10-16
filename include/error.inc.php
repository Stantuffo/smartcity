<?php 

// Array contenente gli errori generati durante l'esecuzione dello script
$GLOBALS['ERROR_BUFFER'] = array();

// Indirizzi email dei destinatari degli errori
$GLOBALS['ERROR_DEST'] = array('simon.bucciarelli@gmail.com');

// Indirizzi email dei destinatari degli errori
$GLOBALS['TOP_ERRNO'] = null;
$GLOBALS['TOP_ERRSTR'] = null;

// Carico il file INI
$GLOBALS['ERROR_ARRAY'] = parse_ini_file(INCLUDES."/smarty/configs/".$ERROR_FILE);
$error_section 	= parse_ini_file(INCLUDES."/smarty/configs/".$ERROR_FILE,true);
$ERROR_VALIDATOR = $error_section['VALIDATOR'];

// Imposto l'handler per gli errori 
set_error_handler('err');

// Alla fine dell'esecuzione dello script controllo gli errori (solo per i computer della rete)
if(!preg_match('/^192\.168\.0\.[0-9]{1,3}$/', $_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] != '127.0.0.1'){
    register_shutdown_function('shutdown');
}

/**************************************************************************************************************************/
/*                                                                                                                        */
/*   Funzioni                                                                                                             */
/*                                                                                                                        */
/**************************************************************************************************************************/

/**
 * Handler per gli errori
 *
 * @param Int $errno
 * @param String $errstr
 * @param String $errfile
 * @param Int $errline
 * @return Void
 */
function err($errno, $errstr, $errfile, $errline) {
    $pos1 = strpos($errfile, "/public_html/smarty/templates_c/");
    $pos2 = strpos($errfile, "/public_html/smarty/");
    if (($pos1 !== false) && ($pos2 !== false)) {
    } else {
        error_log ("$errfile ($errline): $errno => $errstr");
    }

    if($errno == E_STRICT || !error_reporting()){
        return null;
    }

    $backtrace = debug_backtrace(); 

    store_error(
        $errno,
        $errstr,
        $errfile,
        $errline,
        array_reverse(array_slice($backtrace, 2)),
        $_SERVER['REMOTE_ADDR']
    );

    if(!in_array($errno, array(E_ERROR, E_USER_ERROR))){
        return null;
    }



    header("Location: ".WEBPATH."/404.php");
    exit;
}

/**
 * Salva l'errore in un buffer globale
 *
 * @param Int $errno
 * @param String $errstr
 * @param String $errfile
 * @param Int $errline
 * @return Void
 */
function store_error($errno, $errstr, $errfile, $errline, $backtrace, $remote_addr){

    // Assegno agli errori una etichetta
    $error_types = array(
        E_ERROR			=> 'critical',
        E_WARNING		=> 'warning',
        E_PARSE			=> 'critical',
        E_NOTICE		=> 'notice',
        E_CORE_ERROR		=> 'critical',
        E_CORE_WARNING		=> 'warning',
        E_COMPILE_ERROR		=> 'critical',
        E_COMPILE_WARNING	=> 'warning',
        E_USER_ERROR		=> 'critical',
        E_USER_WARNING		=> 'warning',
        E_USER_NOTICE		=> 'notice'
    );
    if($errno == E_DEPRECATED) {
        return;
    }
    // Salvo l'errore nel buffer
    $GLOBALS['ERROR_BUFFER'][] = array(
        'type' => isset($error_types[$errno]) ? $error_types[$errno] : "undefined {$errno}",
        'mess' => $errstr,
        'line' => "{$errfile} @ line {$errline}",
        'backtrace' => $backtrace,
        'remote_addr' => $remote_addr,
        'level' => error_reporting(),
    );

    if($GLOBALS['TOP_ERRNO'] < $errno){
        $GLOBALS['TOP_ERRNO'] = $errno;
        $GLOBALS['TOP_ERRSTR'] = isset($error_types[$errno]) ? $error_types[$errno] : "undefined {$errno}";
    }

    // Se è un computer della rete, visualizzo i notice a video
    if(preg_match('/^192\.168\.0\.[0-9]{1,3}$/', $_SERVER['REMOTE_ADDR']) || $_SERVER['REMOTE_ADDR'] == '127.0.0.1'){
        global $smarty;
        $smarty->assign('ERRORS', $GLOBALS['ERROR_BUFFER']);
    } 
}

/**
 * Funzione eseguita alla fine dello script per inviare gli errori via email
 *
 * @return null
 */
function shutdown(){
    // Se sono su un computer locale o se non non stati generati errori, non mando l'email
    if(preg_match('/^192\.168\.0\.[0-9]{1,3}$/', $_SERVER['REMOTE_ADDR']) || count($GLOBALS['ERROR_BUFFER'])==0)
        return null;

    // Creo la mail di errore
    $email = array();

    $email[] =
        str_pad("- Info ", 80, '-', STR_PAD_RIGHT)."\n"
        ."\nRequest URI:\thttp://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}"
        ."\nReferer:\t".(!empty($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '[no referer]')
        ."\nIP:\t\t{$_SERVER['REMOTE_ADDR']}"
        ."\nDate:\t\t".date('H:i:s d/m/Y')
        ."\n"
    ;

    foreach($GLOBALS['ERROR_BUFFER'] as $errore){
        $email[] =
            str_pad("- ".ucfirst($errore['type'])." ", 80, '-', STR_PAD_RIGHT)."\n"
            .backtrace2string($errore['backtrace'])
            ."\nFile:\t\t{$errore['line']}"
            ."\nMessage:\t{$errore['mess']}"
            ."\n"
        ;
    }

    $dominio = $_SERVER['HTTP_HOST'];

    // Invio mail
    @mail(
        implode(',', $GLOBALS['ERROR_DEST']), 
        "{$GLOBALS['TOP_ERRSTR']}", 
        implode("\n\n\n", $email),
        "From: ".EMAIL_NOTIFICATION_SW
    );
}

/**
 * Trasformazione dell'array del backtrace in una stringa
 *
 * @param Array $backtrace
 * @return String
 */
function backtrace2string($backtrace){
    $ritornato = array();
    if(is_array($backtrace) && count($backtrace)){
        foreach($backtrace as $step){
            $ritornato[] =  "File:\t\t{$step['file']} @ line {$step['line']}\n" .(!empty($step['function']) ? "Function: \t{$step['function']}(".var2func_arg($step['args']).")\n" : '');
        }
    } else {
        return '';
    }

    return "\n".implode("\n", $ritornato);
}

/**
 * Formatta un array in stringa per la visualizzazione come parametri di una funzione
 * 
 * @param Array $array
 * @return String
 */
function var2func_arg($array){
    if(!count($array)){
        return '';
    }

    $ritornato = array();
    foreach($array as $parametro){
        if(is_bool($parametro))
            $ritornato[] = $parametro ? 'true' : 'false';
        elseif(is_null($parametro))
            $ritornato[] = 'null';
        elseif(is_object($parametro))
            $ritornato[] = '[object:'.get_class($parametro).']';
        elseif(is_int($parametro) || is_float($parametro) || is_double($parametro))
            $ritornato[] = $parametro;
        elseif(is_array($parametro))
            $ritornato[] = 'array('.var2func_arg($parametro).')';
        else{
            if(strlen($parametro) > 20)
                $parametro = substr($parametro, 0, 20).' [...]';

            $ritornato[] = "'{$parametro}'";
        }
    }

    return implode(', ', $ritornato);
}
	
?>
