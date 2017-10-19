<?php

// includes

class Trades {

    protected $mysqli;

    public $name;
    public $wkn;
    public $provider;
    public $buyPrice;
    public $sellPrice;
    public $stopPrice;
    public $trailingStopDistance;

    /**
     * initiate db connection
     */
    function __construct() {

        $this->mysqli = new mysqli("localhost", "root", "", "botdb");

        if ($this->mysqli->connect_errno) {
            die("Verbindung fehlgeschlagen: " . $this->mysqli->connect_error);
        }
    }

    /**
     * Stores a trade in db
     */
    function storeTrade() {

        $name = ($this->name) ? $this->name : 'NULL';
        $wkn = ($this->wkn) ? $this->wkn : 'NULL';
        $provider = ($this->provider) ? $this->provider : 'NULL';
        $buyPrice = ($this->buyPrice) ? $this->buyPrice : 'NULL';
        $sellPrice = ($this->sellPrice) ? $this->sellPrice : 'NULL';
        $stopPrice = ($this->stopPrice) ? $this->stopPrice : 'NULL';
        $trailingStopDistance = ($this->trailingStopDistance) ? $this->trailingStopDistance : 'NULL';

        $sql = "INSERT INTO depot (name,wkn,provider,buyPrice,sellPrice,stopPrice,trailingStopDistance) VALUES ('"
                  .$name."','"
                  .$wkn."','"
                  .$provider."',"
                  .$buyPrice.","
                  .$sellPrice.","
                  .$stopPrice.","
                  .$trailingStopDistance.")";

        $this->mysqli->query($sql);
    }
}