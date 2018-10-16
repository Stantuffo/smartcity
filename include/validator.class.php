<?php


// -- Classe base per i campi ------------------------------------------------------------------------------------------- //

/**
 * Classe base (non istanziabile) per i gestori dei campi
 */
abstract class field
{

    // Varie versioni del valore del campo
    protected $dirty = null;
    protected $clean = null;
    protected $default = null;

    // Alcune proprietà del campo
    protected $is_empty = null;
    protected $required = false;

    // Informazioni sulla lunghezza del valore del campo (usato per le stringhe)
    protected $min_length = null;
    protected $max_length = null;

    // Informazioni sulla grandezza del valore del campo (usato per i numeri)
    protected $min_value = null;
    protected $max_value = null;

    // Set di valori ammessi e rifiutati per il campo (da implementare)
    protected $allow_values = array();
    protected $deny_values = array();

    // Eventuale errore della validazione del campo
    protected $error = null;

    // Costanti di errore del campo
    const _ERR_REQUIRED = 'REQUIRED';
    const _ERR_INVALID = 'INVALID';
    const _ERR_TOO_SHORT = 'TOO_SHORT';
    const _ERR_TOO_LONG = 'TOO_LONG';
    const _ERR_TOO_SMALL = 'TOO_SMALL';
    const _ERR_TOO_BIG = 'TOO_BIG';


    /**
     * Imposta il valore del campo
     *
     * @param Mixed $value
     * @return Void
     */
    public final function set_value($value)
    {

        // Se è attivato il magic_quotes_gpc, effettuo lo stripslashes
        if (ini_get('magic_quotes_gpc'))
            $value = stripslashes($value);

        // Elimino eventuali spazi iniziali e finali
        $this->dirty = trim($value);

        $this->is_empty = $this->dirty === '';

    }


    /**
     * Recupera il valore del campo non ancora validato
     *
     * @return Mixed
     */
    public final function get_value()
    {
        return $this->dirty;
    }


    /**
     * Recupera l'eventuale errore generato dalla validazione del campo
     *
     * @return Mixed
     */
    public final function get_error()
    {
        return $this->error;
    }


    /**
     * Recupera il valore del campo validato (valorizzato dopo la chiamata del metodo validate())
     *
     * @return Mixed
     */
    public final function get_clean()
    {
        return $this->clean;
    }


    /**
     * Formatta l'eventuale errore generato dalla validazione
     *
     * @param String $type
     * @param Mixed $extra
     * @return Void
     */
    protected final function _set_error($type, $extra = null)
    {
        $this->error = array('type' => $type, 'extra' => $extra);
    }


    /**
     * Interfaccia per la funzione di validazione
     *
     * @return Mixed
     */
    abstract public function validate();

}


// -- Classe per i campi stringa ---------------------------------------------------------------------------------------- //

/**
 * Classe per i campi String non obbligatori
 */
class string_field extends field
{

    /**
     * Costruttore della classe
     *
     * @param Int [Optional] $min_length
     * @param Int [Optional] $max_length
     * @param String [Optional] $def_value
     * @return Object
     */
    public function __construct($min_length = null, $max_length = null, $def_value = null)
    {

        // Controllo i valori dei parametri, se non sono validi li ignoro impostandoli a null
        if (!is_null($min_length) && !is_int($min_length)) {
            trigger_error("Il parametro min_length ({$min_length}) non � valido. Parametro ignorato", E_USER_WARNING);
            $min_length = null;
        }

        if (!is_null($max_length) && !is_int($max_length)) {
            trigger_error("Il parametro max_length ({$max_length}) non � valido. Parametro ignorato", E_USER_WARNING);
            $max_length = null;
        }

        if (!is_null($min_length) && !is_null($max_length) && $min_length > $max_length) {
            trigger_error("Il parametro min_length ({$min_length}) � maggiore del parametro max_length ({$max_length}). Parametri ignorati", E_USER_WARNING);
            $min_length = null;
            $max_length = null;
        }

        // Salvo i valori dei parametri nell'oggetto
        $this->min_length = is_null($min_length) ? null : (int)$min_length;
        $this->max_length = is_null($max_length) ? null : (int)$max_length;
        $this->default = $def_value;

    }

    /**
     * Metodo per la validazione dell'oggetto
     *
     * @return Bool
     */
    public function validate()
    {

        // Controllo la lunghezza massima della stringa
        if ($this->required && $this->is_empty) {
            $this->_set_error(parent::_ERR_REQUIRED);
            return false;
        }

        // Se è vuoto, ritorno il valore di default
        if ($this->is_empty) {
            $this->clean = $this->default;
            return true;
        }

        // Controllo la lunghezza minima della stringa
        if (!is_null($this->min_length) && strlen($this->dirty) < $this->min_length) {
            $this->_set_error(parent::_ERR_TOO_SHORT, $this->min_length);
            return false;
        }

        // Controllo la lunghezza massima della stringa
        if (!is_null($this->max_length) && strlen($this->dirty) > $this->max_length) {
            $this->_set_error(parent::_ERR_TOO_LONG, $this->max_length);
            return false;
        }

        $this->clean = $this->dirty;

        return true;

    }

}

/**
 * Classe per i campi String obbligatori
 */
class req_string_field extends string_field
{

    /**
     * Costruttore della classe
     *
     * @param Int [Optional] $min_length
     * @param Int [Optional] $max_length
     * @return Object
     */
    public function __construct($min_length = null, $max_length = null)
    {

        // Richiamo il costruttore del parent e imposto a true il parametro required
        parent::__construct($min_length, $max_length, null);
        $this->required = true;

    }

}


// -- Classe per i campi intero ----------------------------------------------------------------------------------------- //

/**
 * Classe per i campi Int non obbligatori
 */
class int_field extends field
{

    /**
     * Costruttore della classe
     *
     * @param Int [Optional] $min_value
     * @param Int [Optional] $max_value
     * @param Int [Optional] $def_value
     * @return Object
     */
    public function __construct($min_value = null, $max_value = null, $def_value = null)
    {

        // Controllo i valori dei parametri, se non sono validi li ignoro impostandoli a null
        if (!is_null($min_value) && !is_int($min_value)) {
            trigger_error("Il parametro min_value ({$min_value}) non � valido. Parametro ignorato", E_USER_WARNING);
            $min_value = null;
        }

        if (!is_null($max_value) && !is_int($max_value)) {
            trigger_error("Il parametro max_value ({$max_value}) non � valido. Parametro ignorato", E_USER_WARNING);
            $max_value = null;
        }

        if (!is_null($min_value) && !is_null($max_value) && $min_value > $max_value) {
            trigger_error("Il parametro min_value ({$min_value}) � maggiore del parametro max_value ({$max_value}). Parametri ignorati", E_USER_WARNING);
            $min_value = null;
            $max_value = null;
        }

        if (!is_null($def_value) && !is_int($def_value)) {
            trigger_error("Il parametro def_value ({$def_value}) non � valido. Parametro ignorato", E_USER_WARNING);
            $def_value = null;
        }

        // Salvo i valori dei parametri nell'oggetto
        $this->min_value = is_null($min_value) ? null : (int)$min_value;
        $this->max_value = is_null($max_value) ? null : (int)$max_value;
        $this->default = is_null($def_value) ? null : (int)$def_value;

    }

    /**
     * Metodo per la validazione dell'oggetto
     *
     * @return Bool
     */
    public function validate()
    {

        // Controllo la lunghezza massima della stringa
        if ($this->required && $this->is_empty) {
            $this->_set_error(parent::_ERR_REQUIRED);
            return false;
        }

        // Se è vuoto prendo il valore di default
        if ($this->is_empty) {
            $this->clean = $this->default;
            return true;
        }

        // Controllo il formato
        if (!preg_match('/^(\-[0-9])?[0-9]*$/', $this->dirty)) {
            $this->_set_error(parent::_ERR_INVALID);
            return false;
        }

        // Converto il valore in intero
        $value = (int)$this->dirty;

        // Controllo la lunghezza minima della stringa
        if (!is_null($this->min_value) && $value < $this->min_value) {
            $this->_set_error(parent::_ERR_TOO_SMALL, $this->min_value);
            return false;
        }

        // Controllo la lunghezza massima della stringa
        if (!is_null($this->max_value) && $value > $this->max_value) {
            $this->_set_error(parent::_ERR_TOO_BIG, $this->max_value);
            return false;
        }

        $this->clean = $value;

        return true;

    }

}


/**
 * Classe per i campi Int obbligatori
 */
class req_int_field extends int_field
{

    /**
     * Costruttore della classe
     *
     * @param Int [Optional] $min_value
     * @param Int [Optional] $max_value
     * @return Object
     */
    public function __construct($min_length = null, $max_length = null)
    {

        // Richiamo il costruttore del parent e imposto a true il parametro required
        parent::__construct($min_length, $max_length, null);
        $this->required = true;

    }

}


// -- Classe per i campi float ------------------------------------------------------------------------------------------ //

/**
 * Classe per i campi Float non obbligatori
 */
class float_field extends field
{

    /**
     * Costruttore della classe
     *
     * @param Float [Optional] $min_value
     * @param Float [Optional] $max_value
     * @param Float [Optional] $def_value
     * @return Object
     */
    public function __construct($min_value = null, $max_value = null, $def_value = null)
    {

        // Controllo i valori dei parametri, se non sono validi li ignoro impostandoli a null
        if (!is_null($min_value) && !is_int($min_value) && !is_float($min_value)) {
            trigger_error("Il parametro min_value ({$min_value}) non � valido. Parametro ignorato", E_USER_WARNING);
            $min_value = null;
        }

        if (!is_null($max_value) && !is_int($max_value) && !is_float($min_value)) {
            trigger_error("Il parametro max_value ({$max_value}) non � valido. Parametro ignorato", E_USER_WARNING);
            $max_value = null;
        }

        if (!is_null($min_value) && !is_null($max_value) && $min_value > $max_value) {
            trigger_error("Il parametro min_value ({$min_value}) � maggiore del parametro max_value ({$max_value}). Parametri ignorati", E_USER_WARNING);
            $min_value = null;
            $max_value = null;
        }

        if (!is_null($def_value) && !is_int($def_value) && !is_float($def_value)) {
            trigger_error("Il parametro def_value ({$def_value}) non � valido. Parametro ignorato", E_USER_WARNING);
            $def_value = null;
        }

        // Salvo i valori dei parametri nell'oggetto
        $this->min_value = is_null($min_value) ? null : (float)$min_value;
        $this->max_value = is_null($max_value) ? null : (float)$max_value;
        $this->default = is_null($def_value) ? null : (float)$def_value;

    }


    /**
     * Metodo per la validazione dell'oggetto
     *
     * @return Bool
     */
    public function validate()
    {

        $this->dirty = str_replace(",", ".", $this->dirty);
        $this->dirty = str_replace("€", "", $this->dirty);

        // Controllo la lunghezza massima della stringa
        if ($this->required && $this->is_empty) {
            $this->_set_error(parent::_ERR_REQUIRED);
            return false;
        }

        // Se è vuoto
        if ($this->is_empty) {
            $this->clean = $this->default;
            return true;
        }

        // Controllo il formato
        if (!is_numeric($this->dirty)) {
            $this->_set_error(parent::_ERR_INVALID);
            return false;
        }

        // Converto il valore in float
        $value = (float)$this->dirty;

        // Controllo la lunghezza minima della stringa
        if (!is_null($this->min_value) && $value < $this->min_value) {
            $this->_set_error(parent::_ERR_TOO_SMALL, $this->min_value);
            return false;
        }

        // Controllo la lunghezza massima della stringa
        if (!is_null($this->max_value) && $value > $this->max_value) {
            $this->_set_error(parent::_ERR_TOO_BIG, $this->max_value);
            return false;
        }

        $this->clean = $value;

        return true;

    }

}


/**
 * Classe per i campi Int obbligatori
 */
class req_float_field extends float_field
{

    /**
     * Costruttore della classe
     *
     * @param Float [Optional] $min_value
     * @param Float [Optional] $max_value
     * @return Object
     */
    public function __construct($min_length = null, $max_length = null)
    {

        // Richiamo il costruttore del parent e imposto a true il parametro required
        parent::__construct($min_length, $max_length, null);
        $this->required = true;

    }

}


// -- Classe per il codice fiscale -------------------------------------------------------------------------------------- //

/**
 * Classe per i campi Codice Fiscale non obbligatori
 */
class cf_field extends string_field
{

    /**
     * Metodo per la validazione dell'oggetto
     *
     * @return Bool
     */
    public function validate()
    {

        // Controllo se è richiesto
        if ($this->required && $this->is_empty) {
            $this->_set_error(parent::_ERR_REQUIRED);
            return false;
        }

        // Se è vuoto, ritorno il valore di default
        if ($this->is_empty) {
            $this->clean = null;
            return true;
        }

        // Il validatore del parent salva il valore corretto nella proprietà clean
        $value = $this->dirty;

        // Controllo il formato del codice fiscale
        if (!self::check($value)) {
            $this->_set_error(parent::_ERR_INVALID);
            return false;
        }

        $this->clean = strtoupper($value);

        return true;

    }


    /**
     * Funzione di controllo per la verifica del codice fiscale
     *
     * @param String $stringa
     * @return Bool
     */
    static public function check($stringa){
        
        require_once("codicefiscale.class.php");
        
        $cf = new CodiceFiscale();
        $cf->SetCF($stringa); 
        
        if(!$cf->GetCodiceValido()){
            return false;
        }

        return true;
    }
}


/**
 * Classe per i campi Codice Fiscale  obbligatori
 */
class req_cf_field extends cf_field
{

    /**
     * Costruttore della classe
     *
     * @return Object
     */
    public function __construct()
    {

        // Richiamo il costruttore del parent
        parent::__construct();
        $this->required = true;

    }

}


// -- Classe per il codice fiscale aziende ------------------------------------------------------------------------------ //

/**
 * Classe per i campi Codice Fiscale per le aziende non obbligatori
 */
class cf_azienda_field extends string_field
{

    /**
     * Metodo per la validazione dell'oggetto
     *
     * @return Bool
     */
    public function validate()
    {

        // Controllo se è richiesto
        if ($this->required && $this->is_empty) {
            $this->_set_error(parent::_ERR_REQUIRED);
            return false;
        }

        // Se è vuoto, ritorno il valore di default
        if ($this->is_empty) {
            $this->clean = null;
            return true;
        }

        // Il validatore del parent salva il valore corretto nella proprietà clean
        $value = $this->dirty;

        // Controllo il formato del codice fiscale
        if (!cf_field::check($value) && !piva_field::check($value)) {
            $this->_set_error(parent::_ERR_INVALID);
            return false;
        }

        $this->clean = strtoupper($value);

        return true;

    }

}


/**
 * Classe per i campi Codice Fiscale  obbligatori
 */
class req_cf_azienda_field extends cf_field
{

    /**
     * Costruttore della classe
     *
     * @return Object
     */
    public function __construct()
    {

        // Richiamo il costruttore del parent
        parent::__construct();
        $this->required = true;

    }

}

// -- Classe per la partita IVA ----------------------------------------------------------------------------------------- //

/**
 * Classe per i campi Partita IVA o CF non obbligatori
 */
class picf_field extends string_field
{

    /**
     * Metodo per la validazione dell'oggetto
     *
     * @return Bool
     */
    public function validate()
    {

        // Controllo se è richiesto
        if ($this->required && $this->is_empty) {
            $this->_set_error(parent::_ERR_REQUIRED);
            return false;
        }

        // Se è vuoto, ritorno il valore di default
        if ($this->is_empty) {
            $this->clean = null;
            return true;
        }

        // Il validatore del parent salva il valore corretto nella proprietà clean
        $value = $this->dirty;

        // Controllo il formato del codice fiscale
        if (!self::check($value)) {
            $this->_set_error(parent::_ERR_INVALID);
            return false;
        }

        $this->clean = strtoupper($value);

        return true;

    }


    /**
     * Funzione di controllo per la verifica della partita IVA
     *
     * @param String $stringa
     * @return Bool
     */
    static public function check($stringa){

        // Controllo il formato
        $stringa = trim($stringa);
        $stringa = str_replace('.', '', $stringa);

        // se la lunghezza è di 16 allora è un CF
        if(strlen($stringa) == 16){
            return cf_field::check($stringa);
        } else {
            $check = utils::check_valid_pi($stringa);
            if (!$check->valid) {
                return false;
            }
        }

        return true;

    }
}


/**
 * Classe per i campi Partita IVA obbligatori
 */
class req_picf_field extends picf_field
{

    /**
     * Costruttore della classe
     *
     * @return Object
     */
    public function __construct()
    {

        // Richiamo il costruttore del parent
        parent::__construct();
        $this->required = true;
    }

}


// -- Classe per la partita IVA ----------------------------------------------------------------------------------------- //

/**
 * Classe per i campi Partita IVA non obbligatori
 */
class piva_field extends string_field
{

    /**
     * Metodo per la validazione dell'oggetto
     *
     * @return Bool
     */
    public function validate()
    {

        // Controllo se è richiesto
        if ($this->required && $this->is_empty) {
            $this->_set_error(parent::_ERR_REQUIRED);
            return false;
        }

        // Se è vuoto, ritorno il valore di default
        if ($this->is_empty) {
            $this->clean = null;
            return true;
        }

        // Il validatore del parent salva il valore corretto nella proprietà clean
        $value = $this->dirty;

        // Controllo il formato del codice fiscale
        if (!self::check($value)) {
            $this->_set_error(parent::_ERR_INVALID);
            return false;
        }

        $this->clean = strtoupper($value);

        return true;

    }


    /**
     * Funzione di controllo per la verifica della partita IVA
     *
     * @param String $stringa
     * @return Bool
     */
    static public function check($stringa)
    {

        // Controllo il formato
        $stringa = trim($stringa);
        $stringa = str_replace('.', '', $stringa);

        $check = utils::check_valid_pi($stringa);
        if(!$check->valid){
            return false;
        }

        return true;

    }
}


/**
 * Classe per i campi Partita IVA obbligatori
 */
class req_piva_field extends piva_field
{

    /**
     * Costruttore della classe
     *
     * @return Object
     */
    public function __construct()
    {

        // Richiamo il costruttore del parent
        parent::__construct();
        $this->required = true;

    }

}


// -- Classe per l'indirizzo email -------------------------------------------------------------------------------------- //

/**
 * Classe per i campi Email non obbligatori
 */
class email_field extends string_field
{

    /**
     * Metodo per la validazione dell'oggetto
     *
     * @return Bool
     */
    public function validate()
    {

        // Controllo se è richiesto
        if ($this->required && $this->is_empty) {
            $this->_set_error(parent::_ERR_REQUIRED);
            return false;
        }

        // Se è vuoto, ritorno il valore di default
        if ($this->is_empty) {
            $this->clean = null;
            return true;
        }

        // Il validatore del parent salva il valore corretto nella proprietà clean
        $value = $this->dirty;

        // Controllo il formato della email
        if (!self::check($value)) {
            $this->_set_error(parent::_ERR_INVALID);
            return false;
        }

        $this->clean = strtolower($value);

        return true;

    }


    /**
     * Funzione di controllo per la verifica degli indirizzi email
     *
     * @param String $stringa
     * @return Bool
     */
    static public function check($stringa)
    {
        // Controllo il formato
        if (!preg_match('/^[a-z0-9][_\.a-z0-9-]+@([a-z0-9][0-9a-z-]+\.)+([a-z]{2,4})$/', $stringa))
            return false;
        
        // Controlla l'esistenza della email
        /*if(!utils::check_email_smtp($stringa)){
            return false;
        }*/

        return true;
    }
}


/**
 * Classe per i campi Email obbligatori
 */
class req_email_field extends email_field
{

    /**
     * Costruttore della classe
     *
     * @return Object
     */
    public function __construct()
    {

        // Richiamo il costruttore del parent
        parent::__construct();
        $this->required = true;

    }

}


// -- Classe per la password -------------------------------------------------------------------------------------- //

/**
 * Classe per i campi Password non obbligatori
 */
class password_field extends string_field
{

    /**
     * Metodo per la validazione dell'oggetto
     *
     * @return Bool
     */
    public function validate()
    {

        // Controllo se è richiesto
        if ($this->required && $this->is_empty) {
            $this->_set_error(parent::_ERR_REQUIRED);
            return false;
        }

        // Se è vuoto, ritorno il valore di default
        if ($this->is_empty) {
            $this->clean = null;
            return true;
        }

        // Il validatore del parent salva il valore corretto nella proprietà clean
        $value = $this->dirty;

        // Controllo il formato del codice fiscale
        if (!self::check($value)) {
            $this->_set_error(parent::_ERR_INVALID);
            return false;
        }

        $this->clean = strtolower($value);

        return true;

    }


    /**
     * Funzione di controllo per la verifica degli indirizzi email
     *
     * @param String $stringa
     * @return Bool
     */
    static public function check($stringa)
    {

        // Controllo il formato
        if (!preg_match('/^[a-z0-9A-Z]{5,10}$/', $stringa))
            return false;

        return true;

    }
}


/**
 * Classe per i campi Password obbligatori
 */
class req_password_field extends password_field
{

    /**
     * Costruttore della classe
     *
     * @return Object
     */
    public function __construct()
    {

        // Richiamo il costruttore del parent
        parent::__construct();
        $this->required = true;

    }

}


/**
 * Classe per i campi Password non obbligatori
 */
class complex_password_field extends string_field
{

    /**
     * Metodo per la validazione dell'oggetto
     *
     * @return Bool
     */
    public function validate()
    {

        // Controllo se è richiesto
        if ($this->required && $this->is_empty) {
            $this->_set_error(parent::_ERR_REQUIRED);
            return false;
        }

        // Se è vuoto, ritorno il valore di default
        if ($this->is_empty) {
            $this->clean = null;
            return true;
        }

        // Il validatore del parent salva il valore corretto nella proprietà clean
        $value = $this->dirty;

        // Controllo il formato del codice fiscale
        if (!self::check($value)) {
            $this->_set_error(parent::_ERR_INVALID);
            return false;
        }

        $this->clean = $value;
        return true;

    }


    /**
     * Funzione di controllo per la verifica degli indirizzi email
     *
     * @param String $stringa
     * @return Bool
     */
    static public function check($stringa)
    {
        // Controllo il formato
//        if (!preg_match('/^[[:punct:]*a-z0-9A-Z]{4,30}$/', $stringa))
        if (preg_match('/\s+/', $stringa) || strlen($stringa) < 4)
            return false;

        return true;

    }
}


/**
 * Classe per i campi Password obbligatori
 */
class req_complex_password_field extends complex_password_field
{

    /**
     * Costruttore della classe
     *
     * @return Object
     */
    public function __construct()
    {

        // Richiamo il costruttore del parent
        parent::__construct();
        $this->required = true;

    }

}

// -- Classe per stringhe numeriche ------------------------------------------------------------------------------------- //

/**
 * Classe per i campi numerici non obbligatori
 */
class numeric_field extends string_field
{

    /**
     * Metodo per la validazione dell'oggetto
     *
     * @return Bool
     */
    public function validate()
    {

        if (parent::validate() === false)
            return false;

        $value = $this->dirty;

        // Controllo il formato del codice fiscale
        if (!preg_match('/^[0-9]*$/', $value)) {
            $this->_set_error(parent::_ERR_INVALID);
            return false;
        }

        $this->clean = $value;

        return true;

    }

}


/**
 * Classe per i campi numerici obbligatori
 */
class req_numeric_field extends numeric_field
{

    /**
     * Costruttore della classe
     *
     * @return Object
     */
    public function __construct()
    {

        // Richiamo il costruttore del parent
        parent::__construct();
        $this->required = true;

    }

}


// -- Classe per i campi data ---------------------------------------------------------------------------------------- //

/**
 * Classe per i campi data non obbligatori
 * Il formato di ingresso della data è DD/MM/YYYY
 * Il formato validato RIMANE DD/MM/YYYY viene messa a disposizione una funzione
 * to_YMD_format per convertire la data nel formato di mysql
 */
class dateDMY_field extends field
{

    /**
     * Costruttore della classe
     * @return Object
     */
    public function __construct($def_value = null)
    {

        $this->default = $def_value;

    }

    /**
     * Metodo per la validazione dell'oggetto
     *
     * @return Bool
     */
    public function validate()
    {

        // Controllo se è richiesto
        if ($this->required && $this->is_empty) {
            $this->_set_error(parent::_ERR_REQUIRED);
            return false;
        }

        // Se è vuoto, ritorno il valore di default
        if ($this->is_empty) {
            $this->clean = $this->default;
            return true;
        }

        $value = $this->dirty;

        // Controllo il formato della data
        if (!self::check($value)) {
            $this->_set_error(parent::_ERR_INVALID);
            return false;
        }

        $this->clean = $this->dirty;

        return true;

    }

    /* Funzione di controllo per la verifica delle date
    *
    * @param Date $stringa
    * @return Bool
    */
    static public function check($dateDMY)
    {

        $matches = array();

        if (!preg_match("/([0-3]?[0-9])[^0-9]([0-1]?[0-9])[^0-9]([0-9]{4})/", $dateDMY, $matches)) {
            return false;
        }

        $new_dateDMY = date("Ymd", mktime("12", "00", "00", $matches[2],
            $matches[1], $matches[3]));

        $dateDMY_formattata =
            str_pad($matches[3], 4, '0', STR_PAD_LEFT) .
            str_pad($matches[2], 2, '0', STR_PAD_LEFT) .
            str_pad($matches[1], 2, '0', STR_PAD_LEFT);

        if ($dateDMY_formattata != $new_dateDMY)
            return false;

        return true;

    }

    /**
     * Metodo per la conversione in formato YMD per mysql
     * @param string
     * @return string
     */
    static public function to_YMD_format($dateDMY)
    {

        $matches = array();
        if (!preg_match("/([0-3]?[0-9]).([0-1]?[0-9]).([0-9]{4})/", $dateDMY, $matches)) {
            return NULL;
        }

        return $matches[3] . "-" . str_pad($matches[2], 2, "0", STR_PAD_LEFT) . "-" .
        str_pad($matches[1], 2, "0", STR_PAD_LEFT);

    }
    
    static public function to_DMY_format($dateYMD)
    {

        $matches = array();
        if (!preg_match("/([0-9]{4}).([0-1]?[0-9]).([0-3]?[0-9])/", $dateYMD, $matches)) {
            return NULL;
        }

        return str_pad($matches[3], 2, "0", STR_PAD_LEFT) . "/" . str_pad($matches[2], 2, "0", STR_PAD_LEFT) . "/" . str_pad($matches[1], 4, "0", STR_PAD_LEFT);

    }
    
    static public function to_MDY_format($dateYMD)
    {

        $matches = array();
        if (!preg_match("/([0-9]{4}).([0-1]?[0-9]).([0-3]?[0-9])/", $dateYMD, $matches)) {
            return NULL;
        }

        return str_pad($matches[2], 2, "0", STR_PAD_LEFT) . "/" . str_pad($matches[3], 2, "0", STR_PAD_LEFT) . "/" . str_pad($matches[1], 4, "0", STR_PAD_LEFT);

    }
    
    static public function to_YMDHIS_format($dateDMY)
    {
        
        $date = explode(" ", $dateDMY);

        $matches = array();
        if (!preg_match("/([0-3]?[0-9]).([0-1]?[0-9]).([0-9]{4})/", $date[0], $matches)) {
            return NULL;
        }

        return $matches[3] . "-" . str_pad($matches[2], 2, "0", STR_PAD_LEFT) . "-" .
        str_pad($matches[1], 2, "0", STR_PAD_LEFT). " ".$date[1];

    }

}


/**
 * Classe per i campi data obbligatori
 */
class req_dateDMY_field extends dateDMY_field
{

    /**
     * Costruttore della classe
     * @return Object
     */
    public function __construct()
    {

        // Richiamo il costruttore del parent e imposto a true il parametro required
        parent::__construct();
        $this->required = true;

    }

}


/**
 * Classe per i campi DateTime non obbligatori
 */
class datetime_field extends string_field
{

    /**
     * Metodo per la validazione dell'oggetto
     *
     * @return Bool
     */
    public function validate()
    {

        // Controllo se è richiesto
        if ($this->required && $this->is_empty) {
            $this->_set_error(parent::_ERR_REQUIRED);
            return false;
        }

        // Se è vuoto, ritorno il valore di default
        if ($this->is_empty) {
            $this->clean = null;
            return true;
        }

        // Il validatore del parent salva il valore corretto nella proprietà clean
        $value = $this->dirty;

        // Controllo il formato dell'orario
        if (!self::check($value)) {
            $this->_set_error(parent::_ERR_INVALID);
            return false;
        }

        $this->clean = strtolower($value);

        return true;

    }


    /**
     * Funzione di controllo per la verifica dei datetime
     *
     * @param String $stringa
     * @return Bool
     */
    static public function check($stringa)
    {
        if(strlen($stringa) === 16) {
            $stringa .= ":00";
        }
        if(strlen($stringa) !== 19) {
            return false;
        }
        $datastringa = substr($stringa, 0, 10);
        $timestringa = substr($stringa, 11, 8);

        $matches = array();

        if (!preg_match("/([0-3]?[0-9])[^0-9]([0-1]?[0-9])[^0-9]([0-9]{4})/", $datastringa, $matches)) {
            return false;
        }

        $new_dateDMY = date("Ymd", mktime("12", "00", "00", $matches[2],
            $matches[1], $matches[3]));

        $dateDMY_formattata =
            str_pad($matches[3], 4, '0', STR_PAD_LEFT) .
            str_pad($matches[2], 2, '0', STR_PAD_LEFT) .
            str_pad($matches[1], 2, '0', STR_PAD_LEFT);

        if ($dateDMY_formattata != $new_dateDMY) {
            return false;
        }

        // Controllo il formato
        if (!preg_match('/^([0-1][0-9]|2[0-3]):([0-5][0-9]):([0-5][0-9])$/', $timestringa)) {
            return false;
        }
        return true;
    }

    /**
     * Metodo per la conversione in formato YMD H:M:S per mysql
     *
     * @return Date
     */
    static public function to_SQL_format($dtime)
    {

        $dateDMY = substr($dtime, 0, 10);
        $matches = array();
        if (!preg_match("/([0-3]?[0-9]).([0-1]?[0-9]).([0-9]{4})/", $dateDMY, $matches)) {
            return NULL;
        }
        $ydm = $matches[3] . "-" . str_pad($matches[2], 2, "0", STR_PAD_LEFT) . "-" . str_pad($matches[1], 2, "0", STR_PAD_LEFT);
        return $ydm . substr($dtime, 10);
    }

    /**
     * Metodo per la conversione dal formato YMD H:M:S di mysql al formato D/M/Y H:M:S
     *
     * @return Date
     */
    static public function from_SQL_format($dtime)
    {
        $dateYMD = substr($dtime, 0, 10);
        $dmy = substr($dateYMD, 8, 2) . "/" . substr($dateYMD, 5, 2) . "/" . substr($dateYMD, 0, 4);
        return $dmy . substr($dtime, 10);
    }
}


/**
 * Classe per i campi DateTime obbligatori
 */
class req_datetime_field extends datetime_field
{

    /**
     * Costruttore della classe
     *
     * @return Object
     */
    public function __construct()
    {

        // Richiamo il costruttore del parent
        parent::__construct();
        $this->required = true;
    }

}

// -- Classe per l'orario -------------------------------------------------------------------------------------- //
/**
 * Classe per i campi Time non obbligatori
 */
class time_field extends string_field{
    private $has_seconds = true;
    public function __construct($has_seconds = true){
        $this->has_seconds = $has_seconds;
    }

    /**
     * Metodo per la validazione dell'oggetto
     *
     * @return Bool
     */
    public function validate(){

        // Controllo se è richiesto
        if ($this->required && $this->is_empty) {
            $this->_set_error(parent::_ERR_REQUIRED);
            return false;
        }

        // Se è vuoto, ritorno il valore di default
        if ($this->is_empty) {
            $this->clean = null;
            return true;
        }

        // Il validatore del parent salva il valore corretto nella proprietà clean
        $value = $this->dirty;

        // Controllo il formato dell'orario
        if (!self::check($value, $this->has_seconds)) {
            $this->_set_error(parent::_ERR_INVALID);
            return false;
        }

        $this->clean = strtolower($value);

        return true;
    }


    /**
     * Funzione di controllo per la verifica degli orari
     *
     * @param String $stringa
     * @return Bool
     */
    static public function check($stringa, $has_seconds = true){

        // Controllo il formato
        if($has_seconds) {
            if (!preg_match('/^([0-1][0-9]|2[0-3]):([0-5][0-9]):([0-5][0-9])$/', $stringa))
                return false;
        } else {
            if (!preg_match('/^([0-1][0-9]|2[0-3]):([0-5][0-9])$/', $stringa))
                return false;
        }

        return true;
    }
}


/**
 * Classe per i campi Time obbligatori
 */
class req_time_field extends time_field{

    /**
     * Costruttore della classe
     *
     * @return Object
     */
    public function __construct(){

        // Richiamo il costruttore del parent
        parent::__construct();
        $this->required = true;

    }
}

// -- Classe validatore ------------------------------------------------------------------------------------------------- //

/**
 * Classe handler per i campi
 */
class validator
{

    private $dirty = null;
    private $clean = array();
    private $values = array();
    static $error_string = array();

    public $fields = array();
    public $errors = array();


    /**
     * Costruttore del validatore
     *
     * @param Array $dirty
     * @return Void
     */
    public function __construct($dirty){
        // La classe accetta solo array da validare
        if (!is_array($dirty))
            trigger_error("Il parametro passato non è un array valido", E_USER_ERROR);

        $this->dirty = $dirty;
    }


    /**
     * Aggiunge un nuovo elemento dell'array associativo da validare
     *
     * @param String $name
     * @param mixed $field_object
     * @return Void
     */
    public function add_field($name, $field_object)
    {

        // I campi possono essere solo appartenenti alla famiglia della classe field
        if (!($field_object instanceof field))
            trigger_error("L'oggetto passato non è un'istanza della classe 'field'", E_USER_ERROR);

        // Controllo che non sia già stato inserito uno stesso parametro
        if (isset($this->fields[$name]))
            trigger_error("Il parametro '{$name}' è stato già inserito", E_USER_WARNING);

        // Copio l'istanza dell'oggetto passato (se il campo non è presente, emulo l'esistenza passando una stringa vuota)
        $this->fields[$name] = $this->_assign_field(isset($this->dirty[$name]) ? $this->dirty[$name] : '', $field_object);

    }


    /**
     * Inserisce l'oggetto nella struttura del validatore
     *
     * @param Mixed $value
     * @param Object $field_object
     * @return Mixed
     */
    private function _assign_field($value, $field_object){
        // Se sono arrivato alla fine del ramo, tirorno l'oggetto
        if (!is_array($value)) {
            $returned_object = clone $field_object;
            $returned_object->set_value($value);
            return $returned_object;
        }

        // Se � un array lo percorro e richiamo ricorsivamente la funzione
        $ritornato = array();

        foreach ($value as $sub_name => $sub_value)
            $ritornato[$sub_name] = $this->_assign_field($sub_value, $field_object);

        return $ritornato;
    }


    /**
     * Valida gli oggetti contenuti nella struttura
     *
     * @param Mixed $value
     * @param String $name
     * @return Mixed
     */
    private function _validate($field, $name = null)
    {

        // Se sono arrivato alla foglia, valido l'oggetto
        if (!is_array($field)) {
            $validation = $field->validate();

            // Se la validazione non è andata a buon fine, genero l'errore
            if (!$validation) {
                $this->errors[] = array_merge(array('name' => $name[0], 'path' => $name), $field->get_error());
                return null;
                // Recupero l'errore
            } else {
                return $field->get_clean();
            }
        }

        // Se è un array lo percorro e richiamo ricorsivamente la funzione
        $ritornato = array();

        if (is_null($name)) {
            $name = array();
        }

        foreach ($field as $sub_name => $sub_field) {
            $sub_name_tree = $name;
            $sub_name_tree[] = $sub_name;

            $ritornato[$sub_name] = $this->_validate($sub_field, $sub_name_tree);
        }

        return $ritornato;
    }


    /**
     * Imposta il set di stringhe utilizzate per creare la stringa di errore dei vari campi
     *
     * @param Array $string_array
     * @return Void
     */
    static function set_error_string($string_array)
    {

        self::$error_string = $string_array;

    }


    /**
     * Imposta una singola stringadi errore
     *
     * @param String $field_name
     * @param String $error_type
     * @param String $error_msg
     * @return Void
     */
    static function set_single_error_string($field_name, $error_type, $error_msg){
        self::$error_string[$field_name . '.' . $error_type] = $error_msg;
    }


    private function _set_value_path2array($path, $value, &$array){
        if (count($path) < 2) {
            reset($path);
            $array[current($path)] = $value;
            return null;
        }

        $step = array_slice($path, 0, 1);
        $path = array_slice($path, 1);

        $this->_set_value_path2array($path, $value, $array[$step[0]]);

        return null;
    }


    /**
     * Restituisce le stringhe di errori associati ai vari campi
     *
     * @return Array
     */
    public function get_error(){
        $ritornato = array();

        foreach ($this->errors as $error) {

            $stringa_errore = '';

            if (isset(self::$error_string["{$error['name']}.{$error['type']}"]))
                $stringa_errore = str_replace('%1', $error['extra'], self::$error_string["{$error['name']}.{$error['type']}"]);
            elseif (isset(self::$error_string[$error['type']]))
                $stringa_errore = str_replace('%1', $error['extra'], self::$error_string[$error['type']]);
            else
                $stringa_errore = "Field: {$error['name']} - Error: {$error['type']}";


            $this->_set_value_path2array($error['path'], $stringa_errore, $ritornato);

        }

        return $ritornato;
    }


    private function _get_value($fields){

        if (!is_array($fields))
            return $fields->get_value();

        $ritornato = array();
        foreach ($fields as $index => $field)
            $ritornato[$index] = $this->_get_value($field);

        return $ritornato;
    }


    /**
     * Ritorna i valori dei campi inseriti
     * @return Array
     */
    public function get_value()
    {

        return $this->_get_value($this->fields);

    }


    /**
     * Ritorna i valori dei campi validati e formattati
     * @return Array
     */
    public function get_clean()
    {

        // Valido gli oggetti
        $this->clean = $this->_validate($this->fields);

        // Se ci sono errori ritorno false
        if (count($this->errors))
            return false;

        // Ritorno l'array "pulito"
        return $this->clean;
    }

}


?>