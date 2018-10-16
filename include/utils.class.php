<?php
/**
 * Created by PhpStorm.
 * User: Giulio De Giorgio
 * Company: Ayron srl
 * Date: 11/03/2015
 * Time: 11:15
 * Classe statica piena di utility
 */

class utils {
    
    static $standard_aliquota_iva = 22;

    /**
     * ###DEBUG UTILITY###
     * pre e poi print_r e poi exit (se non indicato diversamente)
     * @param $arr
     * @param $exit_after_print
     */
    public static function pre_print_r($arr, $exit_after_print = true) {
        echo "<pre>";
        print_r($arr);
        echo "</pre>";
        if($exit_after_print)
            exit;
    }
    
    /**
     * Verifica se la data passata rientra nella data di confronto
     * @param type $data
     * @param type $from
     * @param type $data_confronto
     */
    public static function valid_data($data, $from = true, $data_confronto = null){
        if($data_confronto == null){
            $data_confronto = date("Y-m-d");
        }
        
        if($from){
            if(self::get_giorni($data_confronto, $data) >= 0){
                return true;
            }
        } else {
            if(self::get_giorni($data, $data_confronto) >= 0){
                return true;
            }
        }
        
        return false;
    }
    
    /**
    * Recupero della stringa di ricerca
    */
    public static function query_string_ricerca($da_passare) {
        foreach($da_passare as $key) {
            if(!isset($_GET[$key])){
                continue;
            }

            $query_string[] = "{$key}=".urlencode($_GET[$key]);
        }
        return implode('&', $query_string);
    }
    
    /**
     * Ordina array in base ad una chiave
     * @param type $arr
     * @param type $col
     * @param type $dir
     * @return type 
     */
    public static function array_sort_by_column(&$arr, $col, $dir = SORT_ASC) {
        $sort_col = array();
        foreach ($arr as $key=> $row) {
            if(isset($row[$col])){
                $sort_col[$key] = $row[$col];
            }
        }

        array_multisort($sort_col, $dir, $arr);
        
        return $arr;
    }

    /**
     * Ritorna i giorni di differenza tra due date
     * @param string $data1
     * @param string $data2
     * @return int
     */
    public static function get_giorni($data1, $data2){
        $a_1 = explode('-',$data1);
        $a_2 = explode('-',$data2);
        $mktime1 = mktime(0, 0, 0, $a_1[1], $a_1[2], $a_1[0]);
        $mktime2 = mktime(0, 0, 0, $a_2[1], $a_2[2], $a_2[0]);
        $secondi = $mktime1 - $mktime2;
        $giorni = intval($secondi / 86400); /*ovvero (24ore*60minuti*60seconi)*/

        return $giorni;
    }
    
    /**
     * Ritorna i mesi di differenza tra due date
     * @param string $data1
     * @param string $data2
     * @return int
     */
    public static function get_mesi($data1, $data2){
        $a_1 = explode('-',$data1);
        $a_2 = explode('-',$data2);
        $mktime1 = mktime(12, 0, 0, $a_1[1], $a_1[2], $a_1[0]);
        $mktime2 = mktime(12, 0, 0, $a_2[1], $a_2[2], $a_2[0]);
        $date_diff = $mktime1 - $mktime2;
        $date_diff = floor(($date_diff / 60 / 60 / 24) / (365/12));

        return $date_diff;
    }

    /**
     * Differenza date
     * @param string $data_iniziale
     * @param string $data_finale
     * @return string
     */
    public static function delta_tempo($data_iniziale, $data_finale){
        $data1 = strtotime($data_iniziale);
        $data1 = date('Y-m-d H:i:s',$data1);
        
        if($data_finale != ""){
            $data2 = strtotime($data_finale);
        } else {
            $data2 = strtotime(date("Y-m-d H:i:s"));
        }
        $data2 = date('Y-m-d H:i:s',$data2);
        
        $datetime1 = new DateTime($data1);
        $datetime2 = new DateTime($data2);
        
        $interval = $datetime1->diff($datetime2);
        
        return $interval->format('%ag %H:%i:%s');
    }
    
    /**
     * Addizione tra orari
     * @param type $_pFirst
     * @param type $_pSecond
     * @return type 
     */
    function get_add_second($_pFirst, $_pSecond){
        $usc = explode(":", $_pFirst);
        $secs_usc = $usc[0]*3600 + $usc[1] * 60 + $usc[2];
        $entr = explode(":", $_pSecond);
        $secs_entr = $entr[0]*3600 + $entr[1] * 60 + $entr[2];

        return $secs_usc + $secs_entr;
    }
    
    /**
     * Sottrazione tra orari
     * @param type $_pFirst
     * @param type $_pSecond
     * @return type 
     */
    function get_diff_second($_pFirst, $_pSecond){
        $usc = explode(":", $_pFirst);
        $secs_usc = $usc[0]*3600 + $usc[1] * 60 + $usc[2];
        $entr = explode(":", $_pSecond);
        $secs_entr = $entr[0]*3600 + $entr[1] * 60 + $entr[2];

        return $secs_usc - $secs_entr;
    }

    /**
     * Toglie i giorni da una data
     * @param bool|string $data
     * @param $days
     * @return int
     */
    public static function get_sub_days($data, $days, $format = "Y-m-d"){
        return date($format, strtotime('-'.$days.' day', strtotime($data)));
    }

    /**
     * Aggiunge i giorni da una data
     * @param bool|string $data
     * @param $days
     * @return int
     */
    public static function get_add_days($data, $days, $format = "Y-m-d"){
        return date($format, strtotime('+'.$days.' day', strtotime($data)));
    }

    /**
     * Controlla l'esistenza effettiva della mail tra i server smtp
     * @param string $email
     * @return boolean
     */
    public static function check_email_smtp($to, $from = "noreply@puntorigenera.it"){
        require_once('mailqueue.class.php');
        $SMTP_Validator = new mailqueue();
        return $SMTP_Validator->validate($to, $from);
    }
    
    /**
     * Controlla la validità del numero
     * @param type $tel
     */
    public static function check_valid_tel($tel){
        $numbersOnly = ereg_replace("[^0-9]", "", $tel);
        $numberOfDigits = strlen($numbersOnly);
        if ($numberOfDigits == 7 or $numberOfDigits == 10) {
            return true;
        } 
        
        return false;
    }
    
    /**
     * Controlla la validità del link
     * @param type $link
     */
    public static function check_valid_link($link){
        if(preg_match( '/^(http|https):\\/\\/[a-z0-9_]+([\\-\\.]{1}[a-z_0-9]+)*\\.[_a-z]{2,5}'.'((:[0-9]{1,5})?\\/.*)?$/i' ,$link)){
            return true;
        }
        
        return false;
    }
    
    /**
     * Controlla la validità del codifice fiscale
     * @param CodiceFiscale $cf
     * @return CodiceFiscale 
     */
    public static function check_valid_cf($codice_fiscale, $check_cf_exist = null){
        require_once('codicefiscale.class.php');
        
        $cf = new CodiceFiscale();
        $cf->SetCF($codice_fiscale);
        
        $return = array();
        if($cf->GetCodiceValido()){
            $return['sesso'] = $cf->GetSesso();
            $return['comune_nascita'] = $cf->GetComuneNascita();
            $return['anno'] = $cf->GetAANascita();
            $return['mese'] = $cf->GetMMNascita();
            $return['giorno'] = $cf->GetGGNascita();
        } else {
            $return['error'] = $cf->GetErrore();
        }
        
        // Verifico se già esiste il cf
        if($check_cf_exist != null){
            if(!query::validate_campo(TBL_GEST_ANAGRAFICA_CLIENTI, "codice_fiscale", $codice_fiscale, $check_cf_exist)){
                $return['error'] = "Codice fiscale già esistente nell'anagrafica clienti";
            }
        }

        return $return;
    }
    
    /**
     * Verifica che il codice fiscale corrisponde con nome e cognome inserito
     * @param type $codice_fiscale
     * @param type $name
     * @param type $surname
     */
    public static function check_valid_cf_name($codice_fiscale, $name, $surname){
        $name = str_replace("'", "", $name);
        $surname = str_replace("'", "", $surname);
        $len_cons = 3;
        $finally_surname = "";
        $finally_name = "";
        $vowels = array("a", "e", "i", "o", "u", "A", "E", "I", "O", "U", " ");
        $cons_surname = str_replace($vowels, "", $surname);
        $cons_name = str_replace($vowels, "", $name);
        
        // Definizione cognome
        if(strlen($cons_surname) < $len_cons){
            $cons_less = ($len_cons-strlen($cons_surname));
            preg_match_all('/[aouie]/i', $surname, $vowels_surname);
            if(count($vowels_surname[0]) >= $cons_less){
                for($i=0; $i<$cons_less; $i++){
                    $cons_surname .= $vowels_surname[0][$i];
                }
            } else {
                for($i=0; $i<count($vowels_surname[0]); $i++){
                    $cons_surname .= $vowels_surname[0][$i];
                }
            }
            
            if(strlen($cons_surname) < $len_cons){
                for($i=0; $i<=count($len_cons-strlen($cons_surname)); $i++){
                    $cons_surname .= "x";
                }
            }
        }
        
        $finally_surname = strtolower(substr($cons_surname, 0, 3));
        
        // Definizione nome
        if(strlen($cons_name) < $len_cons){
            $cons_less = ($len_cons-strlen($cons_name));
            preg_match_all('/[aouie]/i', $name, $vowels_name);
            if(count($vowels_name[0]) >= $cons_less){
                for($i=0; $i<$cons_less; $i++){
                    $cons_name .= $vowels_name[0][$i];
                }
            } else {
                for($i=0; $i<count($vowels_name[0]); $i++){
                    $cons_name .= $vowels_name[0][$i];
                }
            }
            
            if(strlen($cons_name) < $len_cons){
                for($i=0; $i<count($len_cons-strlen($cons_name)); $i++){
                    $cons_name .= "x";
                }
            }
            
            $finally_name = strtolower($cons_name);
        } else {
            if(strlen($cons_name) > $len_cons){
                $finally_name = strtolower(substr($cons_name, 0, 1).substr($cons_name, 2, 1).substr($cons_name, 3, 1));
            } else {
                $finally_name = strtolower($cons_name);
            }
        }
        
        if(substr(strtolower($codice_fiscale), 0, 6) == $finally_surname.$finally_name){
            return true;
        }
        
        return false;
    }
    
    /**
     * Controlla la validità della partita iva
     * @param type $partita_iva
     */
    public static function check_valid_pi($partita_iva, $check_pi_exist = null){
        $country = substr($partita_iva, 0, 2);
        $vatnum = substr($partita_iva, 2, strlen($partita_iva)-1);
        
        $client = new SoapClient("http://ec.europa.eu/taxation_customs/vies/checkVatService.wsdl");
        
        try {
            $check = $client->checkVat(array(
              'countryCode' => $country,
              'vatNumber' => $vatnum
            ));
        } catch (Exception $e) {
        }
        
        // Siccome vengono controllate solo le partite iva comunitarie, allora se mi restituisce false devo verificare che non esista veramente oppure si tratta di una partita iva non comunitaria
        if(!isset($check) || !$check->valid){
            $check->valid = true;
            if($partita_iva == ''){
                $check->valid = false;
            }

            //la p.iva deve essere lunga definita di caratteri per nazione
            switch(strtoupper($country)){
                // Italia
                case 'IT':
                    if(strlen($vatnum) != 11){
                        $check->valid = false;
                    } else
                    
                    // La p.iva italiana deve avere solo cifre
                    if(!ereg("^[0-9]+$", $vatnum)){
                        $check->valid = false;
                    } else {
                        $primo=0;
                        for($i=0; $i<=9; $i+=2){
                            $primo+= ord($vatnum[$i])-ord('0');
                        }

                        for($i=1; $i<=9; $i+=2 ){
                            $secondo=2*(ord($vatnum[$i])-ord('0'));

                            if($secondo>9){
                                $secondo=$secondo-9;
                            }
                            $primo+=$secondo;
                        }
                        if((10-$primo%10)%10 != ord($vatnum[10])-ord('0')){
                            $check->valid = false;
                        }
                    }
                    break;
                // Austria
                case 'AT':
                    if(strlen($vatnum) != 9){
                        $check->valid = false;
                    }
                    break;
                // Belgio
                case 'BE':
                    if(strlen($vatnum) != 9 && strlen($vatnum) != 10){
                        $check->valid = false;
                    }
                    break;
                // Bulgaria
                case 'BG':
                    if(strlen($vatnum) != 9 && strlen($vatnum) != 10){
                        $check->valid = false;
                    }
                    break;
                // Croazia
                case 'HR':
                    if(strlen($vatnum) != 11){
                        $check->valid = false;
                    }
                    break;
                // Cipro
                case 'CY':
                    if(strlen($vatnum) != 9){
                        $check->valid = false;
                    }
                    break;
                // Repubblica Ceca
                case 'CZ':
                    if(strlen($vatnum) != 8 && strlen($vatnum) != 9 && strlen($vatnum) != 10){
                        $check->valid = false;
                    }
                    break;
                // Danimarca
                case 'DK':
                    if(strlen($vatnum) != 8){
                        $check->valid = false;
                    }
                    break;
                // Estonia
                case 'EE':
                    if(strlen($vatnum) != 9){
                        $check->valid = false;
                    }
                    break;
                // Finlandia
                case 'FI':
                    if(strlen($vatnum) != 8){
                        $check->valid = false;
                    }
                    break;
                // Francia
                case 'FR':
                    break;
                //Germania
                case 'DE':
                    if(strlen($vatnum) != 9){
                        $check->valid = false;
                    }
                    break;
                // Grecia
                case 'EL':
                    if(strlen($vatnum) != 9){
                        $check->valid = false;
                    }
                    break;
                // Ungheria
                case 'HU':
                    if(strlen($vatnum) != 8){
                        $check->valid = false;
                    }
                    break;
                // Irlanda
                case 'IE':
                    break;
                // Lettonia
                case 'LV':
                    if(strlen($vatnum) != 11){
                        $check->valid = false;
                    }
                    break;
                // Lituania
                case 'LT':
                    if(strlen($vatnum) != 9 && strlen($vatnum) != 12){
                        $check->valid = false;
                    }
                    break;
                // Lussemburgo
                case 'LU':
                    if(strlen($vatnum) != 8){
                        $check->valid = false;
                    }
                    break;
                // Malta
                case 'MT':
                    if(strlen($vatnum) != 8){
                        $check->valid = false;
                    }
                    break;
                //Paesi Bassi
                case 'NL':
                    if(strlen($vatnum) != 12){
                        $check->valid = false;
                    }
                    break;
                // Polonia
                case 'PL':
                    if(strlen($vatnum) != 10){
                        $check->valid = false;
                    }
                    break;
                // Portogallo
                case 'PT':
                    if(strlen($vatnum) != 9){
                        $check->valid = false;
                    }
                    break;
                // Romania
                case 'RO':
                    if(strlen($vatnum) != 10){
                        $check->valid = false;
                    }
                    break;
                // Slovacchia
                case 'SK':
                    if(strlen($vatnum) != 10){
                        $check->valid = false;
                    }
                    break;
                // Slovenia
                case 'SI':
                    if(strlen($vatnum) != 8){
                        $check->valid = false;
                    }
                    break;
                // Spagna
                case 'ES':
                    if(strlen($vatnum) != 9){
                        $check->valid = false;
                    }
                    break;
                // Svezia
                case 'SE':
                    if(strlen($vatnum) != 12){
                        $check->valid = false;
                    }
                    break;
                // Regno Unito e isola di man
                case 'GB':
                    break;
                
                default:
                    $check->valid = false;
            }
        }
        
        // Verifico se già esiste il pi
        if($check_pi_exist != null){
            if(!query::validate_campo("customers", "partita_iva", $partita_iva, $check_pi_exist)){
                $check->valid = false;
            }
        }
        
        return $check;
    }

    /**
     * Controllo numerico
     * @param string $_pStr
     * @return boolean
     */
    public static function check_numeric($_pStr){
        $return = false;
        for ($i = 0; $i < strlen($_pStr); $i++) {
            if (is_numeric($_pStr{$i})) {
                $return = true;
            }
        }
        return $return;
    }

    /**
     * Funzione che riordina l'array
     * @param array
     * @param array
     * @return array
     */
    public static function array_msort($array, $cols){
        $colarr = array();
        foreach ($cols as $col => $order) {
            $colarr[$col] = array();
            foreach ($array as $k => $row) {
                $colarr[$col]['_' . $k] = strtolower($row[$col]);
            }
        }
        $eval = 'array_multisort(';
        foreach ($cols as $col => $order) {
            $eval .= '$colarr[\'' . $col . '\'],' . $order . ',';
        }
        $eval = substr($eval, 0, -1) . ');';
        eval($eval);
        $ret = array();
        foreach ($colarr as $col => $arr) {
            foreach ($arr as $k => $v) {
                $k = substr($k, 1);
                if (!isset($ret[$k])) $ret[$k] = $array[$k];
                $ret[$k][$col] = $array[$k][$col];
            }
        }
        return $ret;
    }

    /**
     * Somma i minuti ad un orario
     * @param string $time
     * @param int $additional_minutes
     * @return String
     */
    public static function sum_time($time, $additional_minutes){
        $second_add = $additional_minutes * 60;
        $temp_date = strtotime($time) + $second_add;
        return date("H:i:s", $temp_date);
    }

    /**
     * Generate random string
     */
    public static function generateRandomString($_pNChar, $onlyUpper = false){
        if ($onlyUpper) {
            $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        } else {
            $alphabet = "abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ0123456789";
        }
        $pass = array(); //remember to declare $pass as an array
        $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
        for ($i = 0; $i < $_pNChar; $i++) {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }
        return implode($pass); //turn the array into a string
    }

    /**
     * Ritorna l'array impaginato
     */
    public static function personal_paginate($dati, $_pNpage)
    {
        global $smarty;
        $multipagina = new multipage($smarty, 'personal_paginate', $_pNpage);
        return $multipagina->exec($dati);
    }

    /**
     * Ritorna i nomi dei mesi
     * @return string
     */
    public static function get_label_mesi()
    {

        $array_mesi = array();

        $array_mesi['01'] = "Gennaio";
        $array_mesi['02'] = "Febbraio";
        $array_mesi['03'] = "Marzo";
        $array_mesi['04'] = "Aprile";
        $array_mesi['05'] = "Maggio";
        $array_mesi['06'] = "Giugno";
        $array_mesi['07'] = "Luglio";
        $array_mesi['08'] = "Agosto";
        $array_mesi['09'] = "Settembre";
        $array_mesi['10'] = "Ottobre";
        $array_mesi['11'] = "Novembre";
        $array_mesi['12'] = "Dicembre";

        return $array_mesi;
    }

    /**
     * Ritorna i nomi dei mesi
     * @return string
     */
    public static function get_label_settimana($day)
    {

        $array_week = array();

        $array_week['0'] = "Domenica";
        $array_week['1'] = "Lunedì";
        $array_week['2'] = "Martedì";
        $array_week['3'] = "Mercoledì";
        $array_week['4'] = "Giovedì";
        $array_week['5'] = "Venerdì";
        $array_week['6'] = "Sabato";

        return $array_week[$day];
    }

    /**
     * Ritorna le date di inizio e fine della settimana
     * @param type $_pData
     * @return type
     */
    public static function get_inizio_fine_settimana($_pData)
    {

        list($giorno, $mese, $anno) = explode('/', $_pData);

        $w = date('w', mktime(0, 0, 0, $mese, $giorno, $anno));
        $day['W'] = date('W', mktime(0, 0, 0, $mese, $giorno, $anno));

        $giorni = array(0 => 'Domenica', 1 => 'Lunedì', 2 => 'Martedì', 3 => 'Mercoledì',
            4 => 'Giovedì', 5 => 'Venerdì', 6 => 'Sabato');

        $day['giorno'] = $giorni[$w];
        $day['anno'] = $anno;

        if ($w == 0) {
            $day['lunedi'] = date('d/m/Y', mktime(0, 0, 0, $mese, $giorno - 6, $anno));
            $day['domenica'] = date('d/m/Y', mktime(0, 0, 0, $mese, $giorno, $anno));
        } else {
            $day['lunedi'] = date('d/m/Y', mktime(0, 0, 0, $mese, $giorno - $w + 1, $anno));
            $day['domenica'] = date('d/m/Y', mktime(0, 0, 0, $mese, $giorno - $w + 7, $anno));
        }

        return $day;
    }

    /**
     * Controlla la differenza tra l'ora di lavoro attuale e quello di prima in modo da togliere
     * 0.25 ogni volta che parte un nuovo range di time di lavoro
     * @param type $_pLastTime
     * @param type $_pTime
     * @return type
     */
    public static function check_differenza_time($_pLastTime, $_pTime)
    {
        if ($_pLastTime === 0) {
            return 15;
        } else {
            $part = explode(":", $_pLastTime);
            $arr = explode(":", $_pTime);
            $mktimeLast = mktime($part[0], $part[1], $part[2], 0, 0, 0);
            $mktimeNow = mktime($arr[0], $arr[1], $arr[2], 0, 0, 0);

            if ($mktimeNow > $mktimeLast) {
                $diff = $mktimeNow - $mktimeLast;
            } else {
                $diff = $mktimeLast - $mktimeNow;
            }

            $tempo = $diff / 60;

            return $tempo;
        }
    }

    /**
     * Appende ad un url/uri una querystring
     * @author Maicol Cantagallo
     *
     * @param String $url
     * @param String $query_string
     * @return String
     */
    public static function append_query_string($url, $query_string)
    {

        if (strpos($url, '?') === false)
            return "{$url}?{$query_string}";

        return "{$url}&{$query_string}";
    }

    /**
     * Consente di effettuare l'upload di un file e ritorna il path in cui è stato fatto l'upload
     *
     * @param string $sub_dir : sottodirectory della dir "allegati"
     * @param array $file : array $_FILE
     * @param mixed $original_value valore originale del campo, se già presente
     * @return mixed
     */
    public static function upload_image($sub_dir, $file, $nome_campo, $original_value = null, $type_permission = array(), $row = null, $col = null, $max_width = null, $max_height = null)
    {

        $path_immagini = ATTACH_NAME . $sub_dir;
        
        // Cartella di destinazione se non esiste la creo
        if(!file_exists($path_immagini)){
            mkdir($path_immagini, 0777);
        }

        $percorso = "";
        $ritornato = $original_value;
        
        if (($file[$nome_campo]['size'] != 0 && $file[$nome_campo]['size'] <= MAX_BYTE_SIZE_UPLOAD_FILE) || $file[$nome_campo]['name'] == "") {

            if ($row != null && $col != null) {
                $filename = $file[$nome_campo]['name'][$row][$col];
                $filerror = $file[$nome_campo]['error'][$row][$col];
                $percorso = $file[$nome_campo]['tmp_name'][$row][$col];
            } else {
                $filename = $file[$nome_campo]['name'];
                $filerror = $file[$nome_campo]['error'];
                $percorso = $file[$nome_campo]['tmp_name'];
            }
            
            // Controllo la dimensione dell'alterzza e larghezza
            if($percorso != ""){
                $image_info = getimagesize($percorso);
                $image_width = $image_info[0];
                $image_height = $image_info[1];

                if($max_width != null){
                    if($image_width > $max_width){
                        return "error_width";
                    }
                }
                if($max_height != null){
                    if($image_height > $max_height){
                        return "error_height";
                    }
                }
            }

            $ext = pathinfo($filename, PATHINFO_EXTENSION);

            if ($filename != "") {
                if (count($type_permission) > 0) {

                    if (in_array($ext, $type_permission)) {
                        list($usec, $sec) = explode(" ", microtime());
                        $microtime_float = ((float)$usec + (float)$sec);

                        if (($filerror != UPLOAD_ERR_NO_FILE)) {
                            $ritornato = $path_immagini . $microtime_float . utils::generateRandomString(15) . "." . $ext;
                            move_uploaded_file($percorso, $ritornato);
                        }
                        return $ritornato;
                    } else {
                        return "error_type";
                    }

                } else {
                    list($usec, $sec) = explode(" ", microtime());
                    $microtime_float = ((float)$usec + (float)$sec);

                    if (($filerror != UPLOAD_ERR_NO_FILE)) {
                        $ritornato = $path_immagini . $microtime_float . utils::generateRandomString(15) . "." . $ext;
                        move_uploaded_file($percorso, $ritornato);
                    }
                    return $ritornato;
                }
            }
        } else {
            return "error_size";
        }

        return $ritornato;

    }
    
    /**
     * Upload file on server
     * @param type $file
     * @param type $file_destination
     * @param type $allowed_files
     * @return string 
     */
    public static function upload_file($file, $file_destination, $allowed_files = array()){
        $return = array("status" => true, "reason" => "");
        $ext = strtolower(substr($file['name'], strrpos($file['name'], "."), strlen($file['name'])-strrpos($file['name'], ".")));
        
        // Controllo l'upload del file
        switch($file['error']){
            // Nessun errore
            case UPLOAD_ERR_OK:
                // Controllo che il file sia stato uploadato (via PHP)
                if(!is_uploaded_file($file['tmp_name'])){
                    $return = array("status" => false, "reason" => "Si è verificato un errore durante l'upload del file");

                // Controllo l'estensione del file //if(!preg_match('/\.csv$/i',$_FILES['upload_bolla']['name'])){
                }elseif(count($allowed_files) > 0){
                    if(!in_array($ext,$allowed_files)){
                        $return = array("status" => false, "reason" => "Il tipo di file non è supportato");
                    }
                }
            break;

            // Nessun file
            case UPLOAD_ERR_NO_FILE:
                $return = array("status" => false, "reason" => "Selezionare il file");
            break;

            // Errore upload
            case UPLOAD_ERR_PARTIAL:
            case UPLOAD_ERR_NO_TMP_DIR:
            case UPLOAD_ERR_CANT_WRITE:
            case UPLOAD_ERR_EXTENSION:
                $return = array("status" => false, "reason" => "Si è verificato un errore durante l'upload del file");
            break;

            // Dimensione file
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                $return = array("status" => false, "reason" => "Il file scelto è troppo grande");
            break;
        }
        
        if($return["status"] == true){
            if(move_uploaded_file($file['tmp_name'], $file_destination) == false){
                $return = array("status" => false, "reason" => "Impossibile copiare il file nella cartella");
            }
        }
        
        return $return;
    }

// ---------------------------------------------------------------------------------------------------------------------- //

    /**
     * Controlla l'array $_FILE
     *
     * @param array $file array $_FILE
     * @return true se non ci sono errori, altimenti la stringa d'errore
     */
    public static function check_file($file)
    {

        $errore = $file['immagine']['error'];
        $ritornato = true;

        if (($errore == UPLOAD_ERR_INI_SIZE) || ($errore == UPLOAD_ERR_FORM_SIZE)) {
            $ritornato = "File troppo grande";
        }

        if ($errore == UPLOAD_ERR_PARTIAL) {
            $ritornato = "Trasferimento parziale";
        }

        if ($errore == UPLOAD_ERR_NO_TMP_DIR) {
            $ritornato = "Cartella temporanea non trovata";
        }

        return $ritornato;

    }


// ---------------------------------------------------------------------------------------------------------------------- //
    
    /**
    * Ridimensiona un'immagine inserendolo nel buffer
    *
    * @param String $src_image_file
    * @param Int $width
    * @param Int $height
    * @param String &$err_str
    * @return Bool
    */
   function resize_image_buffer($src_image_file, $width = "", $height = "", $crop = false, &$err_str){

       ob_start();

       $accepted_mime = array(
           'image/jpeg',
           'image/gif',
           'image/bmp',
           'image/png',
       );

       $err_str = '';

       // Recupero le informazioni
       $info = getimagesize($src_image_file);
       if($info === false){
           $err_str = 'Formato immagine non valido';
           return false;
       }

       list($src_w, $src_h) = $info;
       $src_mimetype = strtolower($info['mime']);
       // Controllo il formato dell'immagine
       if(!in_array($src_mimetype, $accepted_mime)){
           $err_str = 'Formato immagine non valido';
           return false;
       }

       // Creo l'immagine sorgente da file
       switch($src_mimetype){
           case 'image/jpeg': $src_image = imagecreatefromjpeg($src_image_file); break;
           case 'image/gif': $src_image = imagecreatefromgif($src_image_file); imagesavealpha($src_image, true); break;
           case 'image/bmp': $src_image = imagecreatefromwbmp($src_image_file); break;
           case 'image/png': $src_image = imagecreatefrompng($src_image_file); imagesavealpha($src_image, true);  break;
       }
       if($src_image === false){
           $err_str = 'Errore durante la creazione del resource immagine sorgente';
           return false;
       }

       if($width == ""){
           $width = $src_w;
       }

       if($height == ""){
           $height = $src_h;
       }

       if($crop){
           $lato = min($src_w, $src_h);
           $crop_x = $src_w/2 - $lato/2;
           $crop_y = $src_h/2 - $lato/2;

           $dst_image = imagecreatetruecolor($lato, $lato);
           if($dst_image === false){
               $err_str = 'Errore durante la creazione del resource immagine destinazione';
               return false;
           }

           // Effettuo il crop
           if(!imagecopyresampled($dst_image, $src_image, 0, 0, $crop_x, $crop_y, $lato, $lato, $lato, $lato)){
               $err_str = 'Errore durante il crop dell\'immagine';
               return false;
           }

           // Creo una nuova immagine con sfondo bianco
           $dst_new_image = imagecreatetruecolor($width, $height);
           if($dst_new_image === false){
               $err_str = 'Errore durante la creazione del resource immagine destinazione';
               return false;
           }
           $bianco = imagecolorallocate($dst_new_image, 255, 255, 255);
           imagefill($dst_new_image, 0, 0, $bianco);

           // ridimensiono l'immagine
           if(!imagecopyresampled($dst_new_image, $dst_image, 0, 0, 0, 0, $width, $height, $lato, $lato)){
               $err_str = 'Errore durante il ridimensionamento dell\'immagine';
               return false;
           }
       } else {
           $dst_new_image = $src_image;
       }

       // Prendo l'estensione del file di destinazione
       if(!preg_match('/([^\.]+)$/', $src_image_file, $matches)){
           $err_str = 'Impossibile recuperare l\'estensione del file';
           return false;
       }

       // Salvo l'immagine
       $ext = strtolower($matches[1]);
       switch($ext){
           case 'jpg':
           case 'jpe':
           case 'jpeg':
               imagejpeg($dst_new_image);
           break;

           case 'gif':
               imagegif($dst_new_image);
           break;

           case 'bmp':
               image2wbmp($dst_new_image);
           break;

           case 'png':
               imagepng($dst_new_image);
           break;

           default:
               $err_str = 'Impossibile riconoscere il tipo di immagine destinazione';
               return false;
           break;
       }

       $return_image = ob_get_contents();

       ob_clean();

       return $return_image;
   }


    /**
     * Ridimensiona un'immagine
     *
     * @param String $src_image_file
     * @param String $dst_image_file
     * @param Int $width
     * @param Int $height
     * @param String &$err_str
     * @return Bool
     */
    public static function resize_image($src_image_file, $dst_image_file, $width, $height, &$err_str){

        $accepted_mime = array(
            'image/jpeg',
            'image/gif',
            'image/bmp',
            'image/png',
        );

        $err_str = '';

        // Recupero le informazioni
        $info = getimagesize($src_image_file);
        if ($info === false) {
            $err_str = 'Formato immagine non valido';
            return false;
        }

        list($src_w, $src_h) = $info;
        $src_mimetype = strtolower($info['mime']);
        // Controllo il formato dell'immagine
        if (!in_array($src_mimetype, $accepted_mime)) {
            $err_str = 'Formato immagine non valido';
            return false;
        }

        // se l'immagine è più piccola la copio direttamente
        if ($src_w < $width && $src_h < $height) {

            if (!copy($src_image_file, $dst_image_file)) {
                $err_str = 'Errore durante la copia dell\'immagine';
                return false;
            }

            return true;
        }

        // Mi calcolo le proporzioni dell'immagine
        $src_ratio = $src_w / $src_h;
        $dst_ratio = $width / $height;

        // L'immagine sorgente è più schiacciata rispetto a quella di destinazione
        if ($dst_ratio < $src_ratio) {
            // Ricalcolo la nuova dimensione i base alla larghezza
            $dst_w = $width;
            $dst_h = (int)($width / $src_ratio);

            // L'immagine sorgente è più alta rispetto a quella di destinazione
        } else {
            // Ricalcolo la nuova dimensione i base al'altezza
            $dst_h = $height;
            $dst_w = (int)($height * $src_ratio);
        }

        // Creo una nuova immagine con sfondo bianco
        $dst_image = imagecreatetruecolor($dst_w, $dst_h);
        if ($dst_image === false) {
            $err_str = 'Errore durante la creazione del resource immagine destinazione';
            return false;
        }
        $bianco = imagecolorallocate($dst_image, 255, 255, 255);
        imagefill($dst_image, 0, 0, $bianco);

        // Creo l'immagine sorgente da file
        switch ($src_mimetype) {
            case 'image/jpeg':
                $src_image = imagecreatefromjpeg($src_image_file);
                break;
            case 'image/gif':
                $src_image = imagecreatefromgif($src_image_file);
                break;
            case 'image/bmp':
                $src_image = imagecreatefromwbmp($src_image_file);
                break;
            case 'image/png':
                $src_image = imagecreatefrompng($src_image_file);
                imagealphablending($dst_image, false);
                imagesavealpha($dst_image, true);
                break;
        }
        if ($src_image === false) {
            $err_str = 'Errore durante la creazione del resource immagine sorgente';
            return false;
        }

        // ridimensiono l'immagine
        if (!imagecopyresampled($dst_image, $src_image, 0, 0, 0, 0, $dst_w, $dst_h, $src_w, $src_h)) {
            $err_str = 'Errore durante il ridimensionamento dell\'immagine';
            return false;
        }

        // Prendo l'estensione del file di destinazione
        if (!preg_match('/([^\.]+)$/', $dst_image_file, $matches)) {
            $err_str = 'Impossibile recuperare l\'estensione del file';
            return false;
        }

        // Salvo l'immagine
        $ext = strtolower($matches[1]);
        switch ($ext) {
            case 'jpg':
            case 'jpe':
            case 'jpeg':
                $save = imagejpeg($dst_image, $dst_image_file, 80);
                break;

            case 'gif':
                $save = imagegif($dst_image, $dst_image_file);
                break;

            case 'bmp':
                $save = image2wbmp($dst_image, $dst_image_file);
                break;

            case 'png':
                $save = imagepng($dst_image, $dst_image_file);
                break;

            default:
                $err_str = 'Impossibile riconoscere il tipo di immagine destinazione';
                return false;
                break;
        }

        if (!$save) {
            $err_str = 'Si è verificato un errore durante il salvataggio dell\'immagine';
        }

        return true;
    }

    public static function string_starts_with($string, $match) {
        return substr($string, 0, strlen($match)) === $match;
    }

    /**
     * Restituisce il testo di una stringa compresa tra due stringhe
     * Esempio:
     * $str = "XXHello WorldYY";
     * $r = getBetween("XX", "YY", $str);
     * Restituisce "Hello World"
     */
    static function getBetween($start = "", $end = "", $str)
    {
        $temp1 = strpos($str, $start) + strlen($start);
        $result = substr($str, $temp1, strlen($str));
        $dd = strpos($result, $end);
        if ($dd == 0) {
            $dd = strlen($result);
        }
        return substr($result, 0, $dd);
    }
}