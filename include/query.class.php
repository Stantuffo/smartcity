<?php

class query
{
    static $dbh = null;
    static $dbcontroller = null;

    /**
     * Costructor
     * @param type $data
     */
    function __construct()
    {
        $this->dbh = db::get_instance();
        $this->dbcontroller = db::get_controller_instance();
    }

    // Getter and setter
    public static function get_db()
    {
        if (self::$dbh == null) {
            self::set_db(db::get_instance());
        }
        return self::$dbh;
    }
    public static function set_db($db)
    {
        self::$dbh = $db;
    }

    /**
     * Fornisce le colonne della tabella
     * @param type $table
     * @return type
     */
    public static function get_show_field_table($table, $like = null, $returned = 'Field'){
        $query = "SHOW COLUMNS FROM " . $table . ($like != null ? " LIKE '" . $like . "'" : '');
        $return = self::get_db()->fquery($query);

        if ($returned == "Type") {
            preg_match('/enum\((.*)\)$/', $return[0]['Type'], $matches);
            $vals = explode(',', $matches[1]);
            for ($i = 0; $i < count($vals); $i++) {
                $vals[$i] = str_replace("'", '', $vals[$i]);
            }
            return $vals;
        }

        return $return[0]["Field"];
    }

    /**
     * Effettua una ricerca in una generica tabella
     * @author Maicol Cantagallo
     *
     * @param String $nome_tabella
     * @param Array $vincoli
     * @return Array
     */
    public static function search_in_table($nome_tabella, $vincoli = array(), $order_by = '', $other = '', $filter_fields = '*', $group_field = ''){
        $vincoli_sql = array();
        if (count($vincoli) > 0) {
            foreach ($vincoli as $chiave => $valore) {
                if (is_int($valore)) {
                    $vincoli_sql[] = "{$chiave} = " . (int)$valore;
                } elseif (is_array($valore) && count($valore) > 0) {
                    $vincoli_sql[] = "{$chiave} IN ('" . implode("','" . $valore) . "')";
                } elseif (trim($valore) != '') {
                    $vincoli_sql[] = "{$chiave} LIKE '%" . self::get_db()->escape(trim($valore)) . "%'";
                }
            }
        }

        $query = 'SELECT ' . $filter_fields . ' FROM ' . $nome_tabella;

        // Se ci sono dei vincoli li inserisco nella query
        if (count($vincoli_sql))
            $query .= " WHERE " . implode(' AND ', $vincoli_sql);

        // Se ci sono altri vincoli, li accodo
        if (!empty($other))
            $query .= (!count($vincoli_sql) ? ' WHERE 1' : '') . ' ' . trim($other);

        // Se richiesto inserisco l'order by
        if (trim($order_by) != '')
            $query .= " ORDER BY {$order_by}";

        self::get_db()->query($query);

        // Recupero i risultati
        $ritornato = array();
        while ($elemento = self::get_db()->row()) {
            if ($group_field != "") {
                $ritornato[$elemento[$group_field]] = $elemento;
            } else {
                $ritornato[] = $elemento;
            }

            unset($elemento);
        }
        return $ritornato;

    }

    /**
     * Controlla la presenza di un campo con quel valore per gli elementi attivi
     * @param string $tabella
     * @param string $nome_campo
     * @param string $valore_campo
     * @param mixed $escludi_id
     * @return boolean
     */
    public static function validate_campo($tabella, $nome_campo, $valore_campo, $escludi_id = null){
        if (!empty($valore_campo)) {
            $query = "
                SELECT			*
                FROM			{$tabella}
                WHERE			{$nome_campo} = '" . self::get_db()->escape($valore_campo) . "'
                " . (!is_null($escludi_id) ? "AND id != {$escludi_id}" : '') . "
                LIMIT 			1
            ";
            self::get_db()->query($query);

            return self::get_db()->num_rows() == 0;
        }

        return true;
    }

    /**
     * Inserisce le informazioni nel database
     * @param String $where
     * @param Array $info
     * @return Int
     */
    public static function insert_dati($where, $info, $db = null){
        if ($db == null) {
            $db_connect = self::get_db();
        } else {
            $db_connect = $db;
        }
        return $db_connect->insert($where, $info, true);
    }

    /**
     * Inserisce le informazioni nel database senza specificare chi ha fatto l'operazione
     *
     * @param String $where
     * @param Array $info
     * @return Int
     */
    public static function insert_dati_no_log($where, $info){
        return self::get_db()->insert($where, $info, false);
    }

    /**
     * Recupera le informazioni dal database
     * @param String $where
     * @param mixed $value
     * @param String $key
     * @param Boolean $limit_to_one_result
     * @return Array
     */
    public static function  get_info($where, $value, $key = 'id', $limit_to_one_result = true){
        $info = array();
        $info[$key] = $value;
        if ($limit_to_one_result) {
            return self::get_db()->findOne($where, $info);
        }

        return self::get_db()->find($where, $info);
    }

    /**
     * Aggiorna i dati sul database
     * @param String $where
     * @param Array $info
     * @return mixed
     */
    public static function update_dati($where, $info, $field){
        return self::get_db()->updateByID($where, $info, $field, true);
    }

    /**
     * Aggiorna i dati in base alle condizioni
     * @param type $table
     * @param type $info
     * @param type $where
     * @return type
     */
    public static function update_dati_where($table, $info, $where){
        return self::get_db()->updateWhere($table, $info, $where, true);
    }

    /**
     * Aggiorna i dati in base alle condizioni
     * @param type $table
     * @param type $info
     * @param type $where
     * @return type
     */
    public static function update_dati_where_no_log($table, $info, $where){
        return self::get_db()->updateWhere($table, $info, $where, false);
    }

    /**
     * Segna le informazioni come cancellate
     *
     * @param String $tabella
     * @param Int $id
     * @return Void
     */
    public static function delete_info($tabella, $id, $key = "id"){
        $query = "
            UPDATE 			{$tabella}
            SET				deleted = 1,
            WHERE 			" . $key . " " . (is_array($id) ? 'IN (' . implode(',', $id) . ')' : "= {$id}") . "
        ";
        self::get_db()->query($query);

        return null;
    }

    /**
     * Segna le informazioni come cancellate
     *
     * @param String $tabella
     * @param Int $id
     * @return Void
     */
    public static function delete_info_no_log($tabella, $id, $key = "id"){
        $query = "
            UPDATE 			{$tabella}
            SET				deleted = 1
            WHERE 			" . $key . " " . (is_array($id) ? 'IN (' . implode(',', $id) . ')' : "= {$id}") . "
        ";
        self::get_db()->query($query);

        return null;
    }

    /**
     * Cancellazione brutale della riga
     *
     * @param String $tabella
     * @param Int $id
     * @return Void
     */
    public static function brutal_delete($tabella, $id, $key = "id"){
        $query = "
            DELETE FROM		{$tabella}
            WHERE 		" . $key . " " . (is_array($id) ? 'IN (' . implode(',', $id) . ')' : "= {$id}") . "
        ";

        self::get_db()->query($query);

        return null;
    }

    /**
     * Cancellazione brutale della riga
     * @param String $tabella
     * @param Int $id
     * @param string $nome_campo
     * @return Void
     */
    public static function brutal_delete_multi($tabella, $where){
        $query = "DELETE FROM {$tabella}";
        $query .= " WHERE " . $where;
        self::get_db()->query($query);

        return null;
    }
}
?>
