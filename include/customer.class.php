<?php
/**
 * Classe Cliente
 */

class customer
{
    private $rawdata = null;
    private $table = "user";

    /**
     * Constructor della classe
     */
    function __construct()
    {
        $this->rawdata = query::get_show_field_table($this->table);
    }


    /**
     * Check login customer
     * @param $email
     * @param $password
     * @return bool
     */
    public function check_login_customer($email, $password)
    {


        $sql = "SELECT * FROM user WHERE email = $email AND password = $password";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            // output data of each row
            while($row = $result->fetch_assoc()) {
                echo "id: " . $row["id"]. " - Name: " . $row["firstname"]. " " . $row["lastname"]. "<br>";
            }
        } else {
            echo "0 results";
        }
        $conn->close();
        /*$result = query::search_in_table($this->table, "", "", " AND email = '" . $email . "' AND password = '" . md5($password) . "'");
        if (count($result) > 0) {

            return array(
                "success" => true,
                "data" => $result[0]
            );
        }

        return array(
            "success" => false
        );*/
    }

    /**
     * Restituisce le informazioni di un cliente
     * @param type $id
     * @return int
     */
    public function get_customer_by_id($id, $filter = null)
    {
        $result = query::get_info($this->table, $id, "customers_id");
        if (count($result) > 0) {
            if ($filter != null) {
                return $result[$filter];
            }

            return $result;
        }
        return $result;
    }

    public function get_impegni($customer_id)
    {
        return query::get_info("appointment", $customer_id, "usrId", false);
    }

    public function GetDrivingDistance($lat1, $lat2, $long1, $long2)
    {
        $url = "https://maps.googleapis.com/maps/api/distancematrix/json?origins=" . $lat1 . "," . $long1 . "&destinations=" . $lat2 . "," . $long2 . "&mode=driving&language=pl-PL";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_PROXYPORT, 3128);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $response = curl_exec($ch);
        curl_close($ch);
        $response_a = json_decode($response, true);
        $distText = $response_a['rows'][0]['elements'][0]['distance']['text'];
        $distVal = $response_a['rows'][0]['elements'][0]['distance']['value'];
        $timeText = $response_a['rows'][0]['elements'][0]['duration']['text'];
        $timeVal = $response_a['rows'][0]['elements'][0]['distance']['value'];

        return array('distanceText' => $distText, 'timeText' => $timeText, 'distanceValue' => $distVal, 'timeValue' => $timeVal);
    }

    public function addAppointment($appointment)
    {
        return query::insert_dati("appointment", array(
            "usrId" => $appointment['userId'],
            "title" => $appointment['title'],
            "address" => $appointment['address'],
            "city" => $appointment['city'],
            "lat" => $appointment['lat'],
            "lon" => $appointment['long']
        ));
    }

    /**
     * Overload della funzione setter delle proprietà dell'oggetto
     * @param String $name
     * @param Mixed $value
     * @return Void
     */
    public function __set($name, $value)
    {
        if (!isset($this->rawdata[$name])) {
            $this->_fatal("la proprietà " . $name . " non esiste");
        }

        if (is_string($value)) {
            $this->rawdata[$name] = $value;
        } else {
            $this->rawdata[$name] = intval($value);
        }
    }

    /**
     * Overload della funzione getter delle proprietà dell'oggetto
     * @param String $name
     * @return Mixed
     */
    public function __get($name)
    {
        if (!isset($this->rawdata[$name])) {
            $this->_fatal("la proprietà " . $name . " non esiste");
        }

        return $this->rawdata[$name];
    }

    /**
     * Funzione per gli errori
     *
     * @param String $errore
     */
    private function _fatal($errore)
    {
        trigger_error('[class::' . __CLASS__ . '] ERRORE: ' . $errore, E_USER_ERROR);
    }
}