<?php

// include config
include("config.php");

// include classes
include ("classes/Analyse.php");
include ("classes/Mail.php");
include ("classes/Trades.php");

// ####################################

echo "<h1>TrendChecker</h1>";

// define currency pairs
// $currencyPairs = array("BTC_XMR", "BTC_GAME", "BTC_ETC", "BTC_ETH", "BTC_LTC");
$currencyPairs = array("BTC_XMR");
// $currencyPairs = array("");

// check every single currency pair for trading signals
foreach ($currencyPairs as $currencyPair) {

    $trade = new Analyse();
    $trade->checkBrokenUptrend($currencyPair);
    $trade->checkRowSignals($currencyPair);

    if ($trade->tradingSignal == true) {

        // store marked currency pairs
        $currentTrade = new Trades();
        $currentTrade->name = $trade->name;
        $currentTrade->provider = $trade->provider;
        $currentTrade->buyDate = $trade->buyDate;
        $currentTrade->sellDate = $trade->sellDate;
        $currentTrade->buyPrice = $trade->buyPrice;
        $currentTrade->sellPrice = $trade->sellPrice;
        $currentTrade->stopPrice = $trade->stopPrice;
        $currentTrade->tradingReason = $trade->tradingReason;
        $currentTrade->trailingStopDistance = $trade->trailingStopDistance;
        $currentTrade->storeTrade();
    }
}













