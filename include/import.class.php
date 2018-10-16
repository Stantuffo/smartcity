<?php
/**
 * Maicol Cantagallo
 * Classe per la gestione degli import file su Ayron
 */
require_once "db.class.php";
require_once "utils.class.php";

abstract class import_abstract {
    
    protected $fields = array();
    protected $req_fields = array();
   
    /*SAVE

     * // Controllo i campi richiesti
        foreach($this->req_fields as $nome)
            if(is_null($this->fields[$nome])) {
                trigger_error("[class::".__CLASS__."] Errore: la proprietà '{$nome}' è obbligatoria", E_USER_ERROR);
                return false;
            }     */
    
    /**
     * Constructor
     */
    function __construct($data){
        foreach($this->fields as $name => $values){
            if(isset($data[$name]) && is_null($values)){
                $this->fields[$name] = $data[$name];
            }
        }
    }
    
    /**
     * Getters
     * @param $name
     * @return mixed
     */
    public function __get($name) {
        $name = iconv('utf-8', 'ascii//TRANSLIT', strtolower($name)); // verifica se funziona
        if(!array_key_exists($name, $this->fields)) {
            trigger_error("[class::".__CLASS__."] Errore: la proprietà '{$name}' non esiste", E_USER_ERROR);
            return null;
        }

        return $this->fields[$name];
    }
    
    /**
     * Setters
     * @param type $name
     * @param type $value
     */
    public function __set($name, $value){
        $name = iconv('utf-8', 'ascii//TRANSLIT', strtolower($name)); // verifica se funziona
        $this->fields[$name] = $value;
    }
    
    /**
     * Ritorna il valore di un elemento
     * @param type $name
     * @return type
     */
    public function getVals(){
        return $this->fields;
    }
    
    /**
     * A seconda del tipo di import imposto le variabili richieste
     * @return boolean|\importOrdini
     */
    public static function init($type, $file, &$feed){
        $error = "";
        
        switch($type) {
            // Import ordini fornitore
            case 'ordini_fornitore':
                $path = realpath(ATTACH_NAME.DIR_IMPORT_CSV_ORDINI)."/".date('YmdHis')."-{$file['upload']['name']}";
                if(!self::validUploadFile($file, $path, $error)){
                    $feed = $error;
                    return false;
                }
                $data = self::readDataFile($path);
                $import = new importOrdiniFornitore($data);
                break;

            default:
                trigger_error("[class::".__CLASS__."] Errore: il tipo di import non è supportato", E_USER_ERROR);
                return false;
                break;

        }

        return $import;
    }
    
    /**
     * Lettura contenuto file
     * @param type $path
     */
    public static function readDataFile($path){
        $return = array();
        if(($handle = fopen($path, "r")) !== FALSE) {
            $row = 0;
            
            // Prendo l'estensione del file
            $ext = pathinfo($path, PATHINFO_EXTENSION);
            
            switch($ext){
                case "csv":
                    $process_any_value = false; // Se io escludo tutti i dati inerenti all'intestazione dell'ordine presenti nel file, una volta che sono arrivato all'header devo condierare tutti i dati successivi, anche se sono vuoti
                    while(($data = fgetcsv($handle, 0, ";")) !== FALSE) {
                        if(!empty($data)){
                            $break = false;
                            for($k=0; $k<count($data); $k++){
                                if(($data[$k] != "" && !empty($data[$k]) && $data[$k] != "Cliente" && $data[$k] != "Destinatario") || $process_any_value){
                                    if($row == 0){
                                        if($data[$k] != "" && !empty($data[$k])){
                                            $process_any_value = true;
                                            $data[$k] = iconv('utf-8', 'ascii//TRANSLIT', strtolower(str_replace('"','', str_replace("'","", str_replace(",",".", $data[$k])))));
                                            $header[$k] = $data[$k];
                                            $return[$data[$k]] = array();
                                        }
                                    } elseif(isset($header[$k])){
                                        $return[$header[$k]][] = str_replace('"','', str_replace("'","", str_replace(",",".", $data[$k])));
                                    }
                                } else {
                                    $break = true;
                                    break;
                                }
                            }
                            
                            if(!$break){
                                $row++;
                            }
                        }
                    }
                    break;
            }
            
            fclose($handle);
        }
        
        return $return;
    }
    
    /**
     * Valida l'upload del file
     * @param type $file
     */
    public static function validUploadFile($file, $path, &$error){
        // Controllo l'upload del file
        switch($file['upload']['error']){

            // Nessun errore
            case UPLOAD_ERR_OK:
                // Controllo che il file sia stato uploadato (via PHP)
                if(!is_uploaded_file($file['upload']['tmp_name'])){
                    $error = "Si è verificato un errore durante l'upload del file";
                    return false;

                // Controllo l'estensione del file
                }elseif(!preg_match('/\.csv$/i',$file['upload']['name'])){
                    $error = "Sono supportati solamente i file .CSV";
                    return false;
                }
            break;

            // Nessun file
            case UPLOAD_ERR_NO_FILE:
                $error = "Selezionare il file";
                return false;
            break;

            // Errore upload
            case UPLOAD_ERR_PARTIAL:
            case UPLOAD_ERR_NO_TMP_DIR:
            case UPLOAD_ERR_CANT_WRITE:
            case UPLOAD_ERR_EXTENSION:
                $error = "Si è verificato un errore durante l'upload del file";
                return false;
            break;

            // Dimensione file
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                $error = "Il file è troppo grande";
                return false;
            break;
        }

        // Copio il file
        if(move_uploaded_file($file['upload']['tmp_name'], $path) == false){
            $error = "Impossibile copiare il file";
            return false;
        }
        
        return true;
    }
}

class importOrdiniFornitore extends import_abstract {

    protected $fields = array(
        'codice cliente' => null,
        'ns. codice' => null,
        'descrizione' => null,
        'omaggio' => null,
        'omaggi' => null,
        'sc.1' => null,
        'sc.2' => null,
        'sc.3' => null,
        'sc.4' => null,
        'sc.5' => null,
        'netto' => null,
        'prezzo pubblico' => null,
        'listino' => null,
        'codice iva' => null,
        'aliquota iva' => null,
        'quantita' => null,
        'barcode' => null,
        'importo' => null
    );

    protected $req_fields = array(
        'quantita',
        'importo',
        'barcode',
    );
}