<?php


class AddressHandler
{

    private const DEFAULT_LATLNG = 5000;

    public const ERROR_MYSQLI_QUERY_MSG = 'Error in mysqli query';
    public const ERROR_MYSQLI_QUERY_CODE = 964;
    public const ERROR_MYSQLI_CONNECT_MSG = 'Error in mysqli connection';
    public const ERROR_MYSQLI_CONNECT_CODE = 458;


    public function __construct(mysqli $db)
    {

        $create = "CREATE TABLE IF NOT EXISTS all_addresses (
        `addressID` INT(11) NOT NULL AUTO_INCREMENT ,
        `uid` VARCHAR(255) NOT NULL ,
        `postalCode` VARCHAR(8) NOT NULL ,
        `countryCode` VARCHAR(2) NOT NULL ,
        `state` VARCHAR(255) NOT NULL ,
        `district` VARCHAR(255) NOT NULL ,
        `postalLocation` VARCHAR(255) NOT NULL ,
        `createdAt` DATETIME NOT NULL ,
        `extras` JSON NULL ,
        `lat` DOUBLE NOT NULL default " . self::DEFAULT_LATLNG . " ,
        `lng` DOUBLE NOT NULL default " . self::DEFAULT_LATLNG . " ,
        PRIMARY KEY (`addressID`)
        );";


        if ($db->connect_errno) {
            throw new Exception(self::ERROR_MYSQLI_CONNECT_MSG, self::ERROR_MYSQLI_CONNECT_CODE);
        } elseif (!$db->query($create)) {
            throw new Exception(self::ERROR_MYSQLI_QUERY_MSG, self::ERROR_MYSQLI_QUERY_CODE);
        }
        $db->options(MYSQLI_OPT_INT_AND_FLOAT_NATIVE, TRUE);
        $this->db = $db;
    }

    public function addAddress(string $uid, string $postalCode, string  $countryCode, string  $state, string  $district, string  $postalLocation, array $extras = null, float $lat = self::DEFAULT_LATLNG, float $lng = self::DEFAULT_LATLNG)
    {
        $this->_escape($postalCode);
        $this->_escape($countryCode);
        $this->_escape($state);
        $this->_escape($district);
        $this->_escape($postalLocation);
        
        $currentTime = $this->_getCurrentTimeForMySQL();
        $ej = ($extras != null) ? ("'" . json_encode($extras) . "'") : 'null';
        $query = "INSERT INTO all_addresses (`uid`, `postalCode`, `countryCode`, `state`, `district`, `postalLocation`, `lat`, `lng`, `extras`, `createdAt`)
        VALUES ('$uid', '$postalCode', '$countryCode', '$state', '$district', '$postalLocation', '$lat', '$lng', $ej, '$currentTime')";

        if (!$this->db->query($query)) {
            throw new Exception(self::ERROR_MYSQLI_QUERY_MSG, self::ERROR_MYSQLI_QUERY_CODE);
        }
    }

    public function getAddressesByUid(string $uid)
    {
        return $this->_getAddresses('uid', $uid);
    }

    public function getAddressesByDistrict(string $district)
    {
        return $this->_getAddresses('district', $district);
    }

    public function getAddressesByState(string $state)
    {
        return $this->_getAddresses('state', $state);
    }

    public function getAddressByAddressID(int $addressID)
    {
        return $this->_getAddresses('addressID', $addressID)[0];
    }

    private function _getAddresses(string $key, string $value)
    {
        $this->_escape($key);
        $this->_escape($value);

        $query = "SELECT * FROM all_addresses WHERE `$key` = '$value'";

        $res = $this->db->query($query);
        if (!$res) {
            throw new Exception(self::ERROR_MYSQLI_QUERY_MSG, self::ERROR_MYSQLI_QUERY_CODE);
        }
        $addresses = [];
        while ($row = $res->fetch_assoc()) {
            $row['extras'] = (array) json_decode($row['extras']);
            $addresses[] = $row;
        }
        return $addresses;
    }

    public function removeAddress(int $addressID)
    {
        $query = "DELETE FROM all_addresses WHERE `addressID` = '$addressID'";
        if (!$this->db->query($query)) {
            throw new Exception(self::ERROR_MYSQLI_QUERY_MSG, self::ERROR_MYSQLI_QUERY_CODE);
        }
    }

    public function updateAddress(int $addressID, string $postalCode, string $countryCode, string $state, string $district, string  $postalLocation, array $extras = null, float $lat = self::DEFAULT_LATLNG, float $lng = self::DEFAULT_LATLNG)
    {
        $this->_escape($postalCode);
        $this->_escape($countryCode);
        $this->_escape($state);
        $this->_escape($district);
        $this->_escape($postalLocation);
        $query = "UPDATE all_addresses SET 
        `postalCode` = '$postalCode',
        `countryCode` = '$countryCode',
        `state` = '$state',
        `district` = '$district',
        `postalLocation` = '$postalLocation'";
        
        if ($lat != self::DEFAULT_LATLNG) {
            $query .= ",`lat` = '$lat'";
        }
        if ($lng != self::DEFAULT_LATLNG) {
            $query .= ",`lng` = '$lng'";
        }
        if ($extras != null) {
            $query .= ",`extras` = '" . json_encode($extras) ."'";
        }
        $query .= " WHERE `addressID` = '$addressID'";
        if (!$this->db->query($query)) {
            throw new Exception(self::ERROR_MYSQLI_QUERY_MSG, self::ERROR_MYSQLI_QUERY_CODE);
        }
    }

    private function _getCurrentTimeForMySQL()
    {
        return date('Y-m-d H:i:s', time());
    }

    private function _escape(&$var)
    {
        $var = htmlspecialchars($var);
        $var = $this->db->real_escape_string($var);
        // $var = $this->db->real_escape_string($var);
    }
}
