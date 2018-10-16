<?php

// ------------------------------------------------------------------------------------------------------------------ //
//  db.class.php v0.9                                                                                                 //
// ------------------------------------------------------------------------------------------------------------------ //


class db{

    const _VER = 0.9;

    // Array statico per contenere le varie istanze
    static $instances = array();

    // Informazioni di connessione al db
    private $connection = null;
    private $host = null;
    private $username = null;
    private $password = null;
    private $db_name = null;

    // Indica se l'istanza è connessa al db oppure no
    private $connected = false;

    private $info_queries = array();
    private $total_time = 0;

    private $debug = false;

    private $last_query_resource = null;
	private $mapped_instance = true;

    // -------------------------------------------------------------------------------------------------------------- //


    public static function reset_connections(){
        self::$instances = array();
    }

    /**
     * Recupera una istanza di una connessione creata
     *
     * @param String $db_name
     * @return db
     */
    public static function get_instance($db_name = null){

        // Se non è stata creata prima nessuna istanza, ritorno false
        if(count(self::$instances) == 0)
            return false;

        // Se non è stata selezionata nessuna connessione, ritorno la prima creata
        if(is_null($db_name)){
            reset(self::$instances);
            return current(self::$instances);
        }

        // Se è stato specificata una connessione, controllo che sia stata prima creata
        if(!isset(self::$instances[$db_name]))
            trigger_error('Non esiste nessuna istanza di connessione al server', E_USER_ERROR);

        // Ritorno l'oggetto associato al database
        return self::$instances[$db_name];
    }


    // -------------------------------------------------------------------------------------------------------------- //


    /**
     * Costruttore della classe
     *
     * @param String $host
     * @param String $username
     * @param String $password
     * @param String $db_name
     * @param Bool $connect
     * @param Bool $map_instance - flag che istruisce la classe di mappare o meno tra le istanze il db (e quindi ritornarlo con get_instance)
     */
    function __construct($host, $username, $password, $db_name, $connect = false, $map_instance = true){

        // Salvo le informazioni
        $this->host = $host;
        $this->username = $username;
        $this->password = $password;
        $this->db_name = $db_name;

        // Salvo l'oggetto nell'array statico associato al nome del database
        $this->mapped_instance = $map_instance;
        if($this->mapped_instance === true)
            self::$instances[$db_name] = $this;

        // Se è richiesta la connessione la effettuo
        if($connect)
            $this->connect();
    }

	/**
     * Clona l'istanza del database
     * @return db
     */
    public function clone_instance() {
        return new db($this->host, $this->username, $this->password, $this->db_name, true, false);
    }
    // -------------------------------------------------------------------------------------------------------------- //


    /**
     * Restituisce il tempo attuale al millisecondo
     *
     * @return Float
     */
    private function _microtime(){
        list($usec, $sec) = explode(' ', microtime());
        return ((float)$usec + (float)$sec);
    }



    // -------------------------------------------------------------------------------------------------------------- //


    /**
     * Effettua il test di connessione al server
     */
    public static function testconnect($host, $username, $password){

        // Cerco di stabilire una connessione al server
        return mysqli_connect($host, $username, $password, true);
    }



    // -------------------------------------------------------------------------------------------------------------- //


    /**
     * Effettua la connessione al database dell'istanza specificata
     */
    public function connect($return = false){

        // Creo una nuova connessione al server
        $this->connection = mysqli_connect($this->host, $this->username, $this->password, $this->db_name);

        if($this->connection === false){
            if($return){
                trigger_error(mysqli_error($this->connection), E_USER_WARNING);
                return false;
            }else
                trigger_error(mysqli_error($this->connection), E_USER_ERROR);
        }

        // Forzo l'uso dell'UTF-8
        if(mysqli_query($this->connection, 'SET NAMES \'utf8\'') === false){
            if($return){
                trigger_error(mysqli_error($this->connection), E_USER_WARNING);
                return false;
            }else
                trigger_error(mysqli_error($this->connection), E_USER_ERROR);
        }

        // Aumento la dimensione delle tabelle temporanee
        if(mysqli_query($this->connection, 'SET tmp_table_size = 134217728') === false){
            if($return){
                trigger_error(mysqli_error($this->connection), E_USER_WARNING);
                return false;
            }else
                trigger_error(mysqli_error($this->connection), E_USER_ERROR);
        }

        // Segno l'istanza come connessa al db
        $this->connected = true;

        return true;

    }


    public function close(){
        mysqli_close($this->connection);
        $this->connected = false;
        if($this->mapped_instance === true) {
            unset(self::$instances[$this->db_name]);
        }
    }



    // -------------------------------------------------------------------------------------------------------------- //


    /**
     * Recupera l'oggetto connection dell'istanza specificata
     *
     * @return MySQL_Connection_Resource
     */
    function get_connection(){ return $this->connection; }



    // -------------------------------------------------------------------------------------------------------------- //


    /**
     * Ritorna il nome del database dell'istanza
     *
     * @return String
     */
    function get_db_name(){ return $this->db_name; }



    // -------------------------------------------------------------------------------------------------------------- //
    /**
     * Formatta un array in stringa per la visualizzazione come parametri di una funzione
     *
     * @param Array $array
     * @return String
     */
    private function _var2func_arg($array){

        if(!count($array))
            return '';

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
                $ritornato[] = 'array('.$this->_var2func_arg($parametro).')';
            else{

                if(strlen($parametro) > 20)
                    $parametro = substr($parametro, 0, 20).' [...]';

                $ritornato[] = "'{$parametro}'";
            }
        }

        return implode(', ', $ritornato);
    }


    // -------------------------------------------------------------------------------------------------------------- //


    /**
     * Esegue la query ed effettua il fetch delle righe restituendo un array
     *
     * @param String $query
     * @return Array
     */
    function fquery($query, $index = null, $allowed_errors = array(), $trigger_errors = true){
        $res = $this->query($query, $allowed_errors, $trigger_errors);

        if($res === false){
            return $res;
        }
        $ritornato = array();
        while($riga = mysqli_fetch_assoc($res)) {
            if(is_null($index))
                $ritornato[] = $riga;
            else
                $ritornato[$riga[$index]] = $riga;
            unset($riga);
        }
        return $ritornato;
    }


    // -------------------------------------------------------------------------------------------------------------- //


    /**
     * Esegue la query
     *
     * @param String $query
     * @return MySql_Resource
     */
    function query($query, $allowed_errors = array(), $trigger_errors = true){
        // Se la connessione non è stata effettuata, la creo
        if(!$this->connected)
            $this->connect();

        $inizio = $this->_microtime();

        $resource = mysqli_query($this->connection, $query);
        if($resource === false) {
            if($trigger_errors === true)
                trigger_error(mysqli_error($this->connection).' - query: '.$query, E_USER_ERROR);
            else
                return false;
        }


        $fine = $this->_microtime();

        $backtrace = debug_backtrace();

        preg_match('/^(\s*)/s', $query, $matches);

        $durata = $fine-$inizio;

        // Incremento il numero di query effettuate
        $this->info_queries[$this->db_name][] = array(
            'query' => trim(str_replace($matches[1], "\n", $query)),
            'durata' => $durata,
            'file' => $backtrace[0]['file'],
            'line' => $backtrace[0]['line'],
            'function' => isset($backtrace[1]['function']) ? $backtrace[1]['function'].'('.$this->_var2func_arg($backtrace[1]['args']).')' : null,
        );

        $this->total_time += $durata;

        $this->last_query_resource = $resource;

        // Se si è verificato un errore non presente negli errori "accettati", genero un errore
        if($resource == false && !in_array(mysqli_errno($this->connection), $allowed_errors)){
            if($trigger_errors === true){
                trigger_error(mysqli_error($this->connection)." - {$backtrace[0]['file']} @ line {$backtrace[0]['line']} - Query: {$query} - ", E_USER_ERROR);
            } else {
                return false;
            }
        }
        return $resource;
    }



    // -------------------------------------------------------------------------------------------------------------- //


    /**
     * Inserisce il carattere di escape alla stringa selezionata
     *
     * @param String @string
     * @return String
     */
    public function escape($string){

        // Se la connessione non è stata effettuata, la creo
        if(!$this->connected)
            $this->connect();

        return mysqli_real_escape_string($this->connection, $string);

    }



    // -------------------------------------------------------------------------------------------------------------- //


    /**
     * Restituisce il numero di righe restituite dall'ultima query
     *
     * @return Int
     */
    function num_rows(){

        // Se la connessione non è stata effettuata, la creo
        if(is_null($this->last_query_resource))
            return false;

        return mysqli_num_rows($this->last_query_resource);

    }



    // -------------------------------------------------------------------------------------------------------------- //


    /**
     * Libera la risorsa MySql
     *
     * @return Boolean
     */
    function destroy(){
        // Se la connessione non è stata effettuata, la creo
        if(is_null($this->last_query_resource))
            return false;
        if(is_bool($this->last_query_resource)){
            return false;
        }
        return mysqli_free_result($this->last_query_resource);
    }


    
    // -------------------------------------------------------------------------------------------------------------- //


    /**
     * Libera la risorsa MySql
     *
     * @return Boolean
     */
    function dispose(){
        $this->destroy();
        $this->close();
    }
    

    // -------------------------------------------------------------------------------------------------------------- //


    /**
     * Estrae una riga come array associativo e passa alla seguente fino alla fine del resource
     *
     * @return unknown
     */
    function row($res = null){
        if(is_null($res)){
            $res = $this->last_query_resource;
        }
        // Se la connessione non è stata effettuata, la creo
        if(is_null($res))
            return false;
        return mysqli_fetch_assoc($res);
    }

    // -------------------------------------------------------------------------------------------------------------- //
    /**
     * Restituisce l'id inserito dall'ultima query
     *
     * @return Int
     */
    function insert_id(){
        // Se la connessione non è stata effettuata, la creo
        if(!$this->connected)
            $this->connect();
        return mysqli_insert_id($this->connection);
    }
    // -------------------------------------------------------------------------------------------------------------- //
    /**
     * Restituisce il numero delle query aggiornate
     *
     * @return Int
     */
    function affected_rows(){
        // Se la connessione non è stata effettuata, la creo
        if(!$this->connected)
            $this->connect();
        return mysqli_affected_rows($this->connection);
    }
    // -------------------------------------------------------------------------------------------------------------- //
    /**
     * Restutuisce l'errore dell'ultima query
     *
     * @return String
     */
    function error(){
        // Se la connessione non è stata effettuata, la creo
        if(!$this->connected)
            $this->connect();
        return mysqli_error($this->connection);
    }
    // -------------------------------------------------------------------------------------------------------------- //
    /**
     * Restituisce il codice dell'errore dell'ultima query
     *
     * @return Int
     */
    function errno(){
        // Se la connessione non è stata effettuata, la creo
        if(!$this->connected)
            $this->connect();
        return mysqli_errno($this->connection);
    }
    // -------------------------------------------------------------------------------------------------------------- //
    /**
     * Restituisce il nome dell'indice che ha generato l'errore 1062
     *
     * @return String
     */
    function get_duplicate_key(){
        // Se la connessione non è stata effettuata, la creo
        if(!$this->connected)
            $this->connect();
        if(mysqli_errno($this->connection) != 1062)
            return null;
        if(preg_match("/'([^']+)'$/", mysqli_error($this->connection), $matches))
            return $matches[1];
        else
            trigger_error("Impossibile recuperare il nome della chiave duplicata", E_USER_ERROR);
    }
    // -------------------------------------------------------------------------------------------------------------- //
    /**
     * Ritorna delle informazioni sulle query eseguite
     *
     * @return Array
     */
    public function get_stats(){ return $this->info_queries; }
    // -------------------------------------------------------------------------------------------------------------- //
    /**
     * Ritorna il numero di query eseguite
     *
     * @return Int
     */
    public function get_num_queries(){ return count($this->info_queries); }
    // -------------------------------------------------------------------------------------------------------------- //
    /**
     * Ritorna il temp totale di esecuzione delle query
     *
     * @return Float
     */
    public function get_time_queries(){ return $this->total_time; }
    // -------------------------------------------------------------------------------------------------------------- //
    /**
     * Attiva il debug
     *
     * @return Vois
     */
    public function debug(){
        $this->debug = true;
        ob_start("db::print_stats");
    }


    /**
     * Crea il corpo di una query per il SET
     * @param Array $dati Array associativo nome_campo => valore_campo
     * @return String La stringa da concatenare alla query dopo il comando SET
     */
    private function _compile_set_query($dati){
        $query = ' ';
        // Inserisco i dati della query
        foreach($dati as $key => $value){
            if(is_null($value)) {
                $query .= " {$key} = NULL,";
            } elseif(is_int($value) || is_float($value) || $value == "NOW()") {
                $query .= " {$key} = {$value},";
            } elseif(is_array($value)) {
                $query .= " {$key} IN (".implode(',', $value)."),";
            } else {
                $query .= " {$key} = '" . $this->escape($value) . "',";
            }
        }
        return substr($query, 0, -1);
    }
    /**
     * Effettua la INSERT dei dati passati come parametri
     * @param String $table_name Nome della tabella su cui effettuare la INSERT
     * @param Array $info Array associativo nome_campo => valore_campo
     * @param Array $add_user_and_date_fields Boolean
     * @return Int Restituisce l'id della riga inserita
     */
    function insert($table_name, $info, $trigger_errors = true){
        $temp = $this->last_query_resource;
        $set = $this->_compile_set_query($info);
        if($set == '') {
            if($trigger_errors === true)
                trigger_error('Si sta cercando di inserire dei dati vuoti nel database', E_USER_ERROR);
            return false;
        }
        $query = 'INSERT INTO '.$table_name . ' SET ' . $set;
        //utils::pre_print_r($query);
        $res = $this->query($query, array(), $trigger_errors);
        $this->last_query_resource = $temp;
        if($res === false) {
            return false;
        }
        return $this->insert_id();
    }

    /**
     * Effettua la DELETE filtrando per i dati passati come parametri
     * @param String $table_name Nome della tabella su cui effettuare la INSERT
     * @param Array $info Array associativo nome_campo => valore_campo
     * @param Boolean $multi - indica se la delete deve essere multipla o di una sola riga
     * @return Boolean Restituisce l'esito della delete
     */
    function remove($table_name, $info, $multi = false, $trigger_errors = true){
        $temp = $this->last_query_resource;
        $where = $this->_compile_where_query($info);
        if($where == '') {
            if($trigger_errors){
                trigger_error('Si sta cercando di effettuare una delete senza where', E_USER_ERROR);
            }
            return false;
        }
        $query = 'DELETE FROM ' . $table_name . ' WHERE '.$where .($multi === false ? ' LIMIT 1' : '');
        $this->query($query, $trigger_errors);
        $this->last_query_resource = $temp;
        return true;
    }


    /**
     * Effettua il REPLACE dei dati passati come parametri
     * @param String $table_name Nome della tabella su cui effettuare il REPLACE
     * @param Array $info Array associativo nome_campo => valore_campo
     * @param Array $add_user_and_date_fields Boolean
     * @return Int Restituisce l'id della riga inserita
     */
    function replace($table_name, $info, $add_user_and_date_fields = true){

        $temp = $this->last_query_resource;
        $set = $this->_compile_set_query($info);
        if($set == '') {
            trigger_error('Si sta cercando di inserire dei dati vuoti nel database', E_USER_ERROR);
        }
        $query = 'REPLACE INTO '.$table_name.' SET '.$set;
        $this->query($query);
        $this->last_query_resource = $temp;
        return $this->insert_id();
    }
    /**
     * Effettua l'UPDATE dei dati passati come parametri
     * @param String $table_name Nome della tabella su cui effettuare l'UPDATE
     * @param Array $info Array associativo nome_campo => valore_campo, il campo con nome 'id' verrà usato come condizione per identificare la riga da aggiornare
     * @param Boolean $add_user_and_date_fields Nome del campo da utilizzare come id
     * @return Boolean - esito dell'update
     */
    function updateByID($table_name, $info, $field, $trigger_errors = true){
        if(!isset($info[$field])) {
            if($trigger_errors === true)
                trigger_error('Il campo \'id\' non è valorizzato, update impossibile da fare');
            else
                return false;
        }
        // prendo l'id e lo
        $id = $info[$field];
        unset($info[$field]);
        $temp = $this->last_query_resource;
        $set = $this->_compile_set_query($info);
        $query = 'UPDATE ' . $table_name.' SET '.$set . ' WHERE ';
        if(is_array($id)) {
            $query .= $field." IN (".implode(',', $id).")";
        } else {
            $query .= $field." = " . $id;
        }
        $done = $this->query($query, array(), $trigger_errors);
        $this->last_query_resource = $temp;
        return $done;
    }

    /**
     * Effettua l'UPDATE dei dati passati come parametri
     * @param String $table_name Nome della tabella su cui effettuare l'UPDATE
     * @param Array $info Array associativo nome_campo => valore_campo, il campo con nome 'id' verrà usato come condizione per identificare la riga da aggiornare
     * @param Array $where Array associativo nome_campo => valore_campo, costituisce il WHERE della query
     * @param Boolean $add_user_and_date_fields Nome del campo da utilizzare come id
     * @return Boolean - esito dell'update
     */
    function updateWhere($table_name, $info, $where, $add_user_and_date_fields = true){
        $temp = $this->last_query_resource;
        $set = $this->_compile_set_query($info);
        $wheresql = $this->_compile_where_query($where);
        $query = 'UPDATE '.$table_name.' SET ' .$set.' WHERE '.$wheresql;
        $this->query($query);
        $this->last_query_resource = $temp;
    }

    /**
     * Effettua l'UPDATE dei dati passati come parametri
     * @param String $table_name Nome della tabella su cui effettuare l'UPDATE
     * @param Array $info Array associativo nome_campo => valore_campo, il campo con nome 'id' verrà usato come condizione per identificare la riga da aggiornare
     * @param String $id_field Nome del campo da utilizzare come id
     */
    function update($table_name, $info, $id_field = 'id'){
        $temp = $this->last_query_resource;
        if(empty($info[$id_field]))
            trigger_error('Il campo ' . $id_field . ' non è valorizzato');
        $id = $info[$id_field];
        unset($info[$id_field]);
        $query = 'UPDATE '.$table_name.' SET '.$this->_compile_set_query($info).' WHERE '.$id_field.' = ' . $id;
        $this->query($query);
        $this->last_query_resource = $temp;
    }

    /**
     * Effettua una INSERT ... ON DUPLICATE KEY UPDATE sulla tabella specificata
     * @param String $table_name Nome della tabella su cui effettuare l'operazione
     * @param Array $info_insert Array associativo nome_campo => valore_campo dei dati implicati nell'INSERT
     * @param Array $info_update Array associativo nome_campo => valore_campo dei dati implicati nell'eventuale UPDATE
     */
    function insert_update($table_name, $info_insert, $info_update = null){
        $temp = $this->last_query_resource;
        // Se i dati per l'update non sono specificati, copio gli stessi
        // utilizzati per l'insert
        if(is_null($info_update))
            $info_update = $info_insert;
        $query = 'INSERT INTO ' . $table_name .' SET '.$this->_compile_set_query($info_insert).' ON DUPLICATE KEY UPDATE '.$this->_compile_set_query($info_update);
        $this->query($query);
        $this->last_query_resource = $temp;
    }


    /**
     * Crea il corpo di una query per il SEARCH
     * @param Array $dati Array associativo nome_campo => valore_campo
     * @return String La stringa da concatenare alla query dopo il comando WHERE
     */
    private function _compile_where_query($dati){
        $query = '';
        // Inserisco i dati della query
        foreach($dati as $key => $value){
            if($query != '') {
                $query .= ' AND';
            }
            if(is_int($value) || is_float($value))
                $query .= ' '.$key.' = ' . $value;
            elseif(is_null($value))
                continue;
            else
                $query .= ' '. $key . ' = \''.db::escape($value).'\'';
        }
        return $query;
    }

    /**
     * Translate compare operators nicenames in sql language names
     * @param $name
     * @return string
     */
    private function _explain_compare_name($name) {
        switch($name) {
            case 'eq':
                return '=';
            case 'neq':
                return '!=';
            case 'lt':
                return '<';
            case 'lte':
                return '<=';
            case 'gt':
                return '>';
            case 'gte':
                return '>=';
            case 'in':
                return 'IN';
            case 'like':
                return 'LIKE';
        }
        return '=';
    }


    /**
     * Crea il corpo di una query per il SEARCH
     * @param Array $dati Array associativo nome_campo => valore_campo
     * @return String La stringa da concatenare alla query dopo il comando WHERE
     */
    private function _compile_advanced_query($dati, $logicalOperator){
        $query = '';
        // Inserisco i dati della query
        foreach($dati as $compareName => $datiCompare) {
            $compare = $this->_explain_compare_name($compareName);
            foreach ($datiCompare as $key => $value) {
                if ($query != '') {
                    $query .= ' ' . $logicalOperator;
                }
                if($compare === 'IN') {
                    $values = implode(',', $value);
                    $query .= ' ' . $key . ' ' . $compare. '('. $values .')';
                } elseif ($compare === 'LIKE') {
                    $query .= ' ' . $key. ' ' . $compare.' \'%' . db::escape($value) . '%\'';
                } elseif (is_int($value) || is_float($value))
                    $query .= ' ' . $key .' ' . $compare . ' ' . $value;
                elseif (is_null($value))
                    continue;
                else
                    $query .= ' ' . $key. ' ' . $compare.' \'' . db::escape($value) . '\'';
            }
        }
        return $query;
    }

    /**
     * Crea il corpo di una query per il SEARCH denormalized (LEFT JOIN)
     * @param Array $dati Array associativo nome_campo => valore_campo
     * @return String La stringa da concatenare alla query dopo il comando WHERE
     */
    private function _compile_denormalized_query($datia, $datib, $logicalOperator){
        $query = '';
        // Inserisco i dati della query
        foreach($datia as $compareName => $datiCompare) {
            $compare = $this->_explain_compare_name($compareName);

            foreach ($datiCompare as $key => $value) {
                if ($query != '') {
                    $query .= ' '.$logicalOperator;
                }
                if($compare == 'IN') {
                    $values = implode(',', $value);
                    $query .= ' a.' .$key.' '.$compare.'('.$values.')';
                } elseif (is_int($value) || is_float($value))
                    $query .= ' a.'.$key.' '.$compare.' '.$value;
                elseif (is_null($value))
                    continue;
                else
                    $query .= ' a.'.$key.' '.$compare.' \'' . db::escape($value) . '\'';
            }
        }

        // Inserisco i dati della query
        foreach($datib as $compareName => $datiCompare) {
            $compare = $this->_explain_compare_name($compareName);

            foreach ($datiCompare as $key => $value) {
                if ($query != '') {
                    $query .= ' '. $logicalOperator;
                }
                if($compare == 'IN') {
                    $values = implode(',', $value);
                    $query .= ' b.' .$key.' '.$compare.'('.$values.')';
                } elseif (is_int($value) || is_float($value))
                    $query .= ' b.'.$key.' '.$compare.' '.$value;
                elseif (is_null($value))
                    continue;
                else
                    $query .= ' b.'.$key.' '.$compare.' \'' . db::escape($value) . '\'';
            }
        }
        return $query;
    }

    /**
     * Effettua una ricerca sulla tabella specificata con i vincoli passati
     * @param String $table_name Nome della tabella su cui effettuare la ricerca
     * @param Array $info Array associativo nome_campo => valore_campo
     * @param Array $order Nome del campo da utilizzare come ordinamento
     * @param Integer $limit Nome del campo da utilizzare come limit
     * @param String $restrict_fields Nome del campo da utilizzare come limit
     * @return Array Restituisce un array contenente i dati recuperati
     */
    function find($table_name, $info, $order = null, $limit = null, $restrict_fields = "*"){
        return $this->fquery($this->search_query($table_name, $info, $order, $limit, $restrict_fields));
    }

    /**
     * Effettua una ricerca avanzata con raggruppamento delle condizioni attraverso l'operatore logico scelto (AND di default)
     * sulla tabella specificata con i vincoli passati
     * @param String $table_name Nome della tabella su cui effettuare la ricerca
     * @param Array $info Array associativo composto in questa struttura:
     *              key (eq|neq|lt|lte|gt|gte|like|in) - che indica la tipologia di confronto logico
     *              value (array)- array associativo composto da chiave -> valore
     * @param String $logical_operator Operatore logico
     * @param Array $order Nome del campo da utilizzare come ordinamento
     * @param Integer $limit Nome del campo da utilizzare come limit
     * @param Boolean $trigger_errors Indica se il sistema deve mostrare gli errori html o ritornare alla funzione l'esito della query
     * @return Array Restituisce un array contenente i dati recuperati
     */
    function findAdvanced($table_name, $info, $logical_operator = "AND", $order = null, $limit = null, $trigger_errors = true, $restrict_fiels='*'){
        $where = $this->_compile_advanced_query($info, $logical_operator);
        if($where != '') {
            $where = 'WHERE ' . $where;
        }
        $query = 'SELECT '.$restrict_fiels.' FROM '. $table_name . ' ' . $where . ' ' .(is_null($order) ? '' : 'ORDER BY ' . $order).' '.(is_null($limit) ? '' : 'LIMIT '.$limit);
        return $this->fquery($query, null, array(), $trigger_errors);
    }


    /**
     * Effettua una ricerca di una singola riga sulla tabella specificata con i vincoli passati
     * @param String $table_name Nome della tabella su cui effettuare la ricerca
     * @param Array $info Array associativo nome_campo => valore_campo
     * @param string $restrict_fields Nome del campo da utilizzare come ordinamento
     * @return Array Restituisce un array contenente i dati della singola riga
     */
    function findOne($table_name, $info, $restrict_fields = "*"){
        $this->query($this->search_query($table_name, $info, null, 1, $restrict_fields));
        if($this->num_rows() == 0)
            return null;
        return $this->row();
    }

    public function count($table_name, $info){
        $where = $this->_compile_where_query($info);
        if($where != '') {
            $where = 'WHERE ' . $where;
        }
        $query = 'SELECT COUNT(*) as count FROM '.$table_name.' '.$where;
        $this->query($query);
        return $this->row();
    }
    /**
     * Crea la query di ricerca sulla tabella specificata con i vincoli passati
     * @param String $table_name Nome della tabella su cui effettuare la ricerca
     * @param Array $info Array associativo nome_campo => valore_campo
     * @param Array $order Nome del campo da utilizzare come ordinamento
     * @param Integer $limit Nome del campo da utilizzare come limit
     * @param String $restrict_fields field da includere nella query
     * @return Array Restituisce un array contenente i dati recuperati
     */
    function search_query($table_name, $info, $order = null, $limit = null, $restrict_fields = "*"){
        $where = $this->_compile_where_query($info);
        if($where != '') {
            $where = 'WHERE ' . $where;
        }
        $query = 'SELECT '.$restrict_fields.' FROM '. $table_name .' '.$where .' '.(is_null($order) ? '' : 'ORDER BY '.$order).' '.(is_null($limit) ? '' : 'LIMIT '.$limit);
        return $query;
    }


    /**
     * Effettua una ricerca avanzata con denormalizzazione su di un'altra tabella per mezzo di left join
     * Il raggruppamento delle condizioni avviene attraverso l'operatore logico scelto (AND di default)
     * filtrando per mezzo dei vincoli passati nei paremetri $info_root e $info_left
     * @param String $table_root Nome della tabella root su cui effettuare la ricerca
     * @param String $table_left Nome della tabella di left join su cui effettuare la ricerca
     * @param Array $join_on Array associativo composto in questa struttura:
     *              root - indica la chiave della tabella root su cui fare il join
     *              left - indica la chiave della tabella left su cui fare il join
     * @param Array $info_root Array associativo composto in questa struttura:
     *              key (eq|neq|lt|lte|gt|gte) - che indica la tipologia di confronto logico
     *              value (array)- array associativo composto da chiave -> valore
     * @param Array $info_left Array associativo composto come $info_root
     * @param String $logical_operator Operatore logico
     * @param Array $order Nome del campo da utilizzare come ordinamento
     * @param Integer $limit Nome del campo da utilizzare come limit
     * @return Array Restituisce un array contenente i dati recuperati
     */
    function findAdvancedDenormalized($table_root, $table_left, $join_on, $info_root, $info_left, $logical_operator = "AND", $order = null, $limit = null){
        $where = $this->_compile_denormalized_query($info_root, $info_left, $logical_operator);
        if($where != '') {
            $where = 'WHERE ' . $where;
        }
        $joina = $join_on['root'];
        $joinb = $join_on['left'];
        $query = 'SELECT a.*, b.* FROM ' . $table_root .' as a LEFT JOIN '.$table_left.' as b ON a.'.$joina.'=b.'.$joinb. ' ' . $where . (is_null($order) ? '' : 'ORDER BY a.'.$order).' '.(is_null($limit) ? '' : 'LIMIT '.$limit);
        return $this->fquery($query);
    }
}

?>
