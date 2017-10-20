<?php

class Trades {

    protected $mysqli;

    public $name;
    public $provider;
    public $buyDate;
    public $sellDate;
    public $buyPrice;
    public $sellPrice;
    public $stopPrice;
    public $tradingReason;
    public $trailingStopDistance;

    /**
     * initiate db connection
     */
    function __construct() {

        $this->mysqli = new mysqli('localhost', DB_USER, DB_PW, DB_NAME);

        if ($this->mysqli->connect_errno) {
            die('Verbindung fehlgeschlagen: ' . $this->mysqli->connect_error);
        }
    }

    /**
     * Stores a trade in db
     */
    function storeTrade() {

        $name = ($this->name) ? $this->name : 'NULL';
        $provider = ($this->provider) ? $this->provider : 'NULL';
        $buyDate = ($this->buyDate) ? $this->buyDate : 'NULL';
        $sellDate = ($this->sellDate) ? $this->sellDate : 'NULL';
        $buyPrice = ($this->buyPrice) ? $this->buyPrice : 'NULL';
        $sellPrice = ($this->sellPrice) ? $this->sellPrice : 'NULL';
        $stopPrice = ($this->stopPrice) ? $this->stopPrice : 'NULL';
        $tradingReason = ($this->tradingReason) ? $this->tradingReason : 'NULL';
        $trailingStopDistance = ($this->trailingStopDistance) ? $this->trailingStopDistance : 'NULL';

        $sql = "INSERT INTO depot (name,provider,buyDate,sellDate,buyPrice,sellPrice,stopPrice,tradingReason,trailingStopDistance) VALUES ('"
                  .$name."','"
                  .$provider."',"
                  .$buyDate.","
                  .$sellDate.","
                  .$buyPrice.","
                  .$sellPrice.","
                  .$stopPrice.",'"
                  .$tradingReason."',"
                  .$trailingStopDistance.")";

        $this->mysqli->query($sql);
    }

    /**
     * Buys a currency pair
     */
    function buyCurrencyPair() {

    }

    /**
     * Sells a currency pair
     */
    function sellCurrencyPair() {

    }
}