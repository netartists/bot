<?php

// include config
include("config.php");

// include classes
include ("classes/Trading.php");
include ("classes/Mail.php");
include ("classes/Trades.php");

echo "<h2>It works!</h2>";

// define currency pairs
// $currencyPairs = array("BTC_XMR", "BTC_GAME", "BTC_ETC", "BTC_ETH", "BTC_LTC");
// $currencyPairs = array("BTC_XMR");
$currencyPairs = array("");

// initiate
$trade = new Trading();

// check every single currency pair
foreach ($currencyPairs as $currencyPair) {

    // check for broken uptrend
    if ($trade->checkBrokenUptrend($currencyPair) == 1) {
    // if (1 == 1) {

        $imageName = $trade->drawChart($trade->getChartData($currencyPair), $currencyPair, time());

        $mailtext = '<html xmlns="http://www.w3.org/1999/xhtml">
                        <head>
                            <title>'.$currencyPair. ", UpTrend durchbrochen".'</title>
                        </head>
                         
                        <body>
                         
                            <h1>'.$currencyPair. ", UpTrend durchbrochen".'</h1>
                             
                            <img src="https://netartists.de/downloads/tmp/'.$imageName.'" alt="Chart" width="100%" style="display: block;">
                         
                        </body>
                      </html>';

        $betreff    = $currencyPair. ", UpTrend durchbrochen";

        $sendMail = new Mail();
        $sendMail->sendMail($mailtext, $betreff);
    }

    // check for broken downtrend
    if ($trade->checkBrokenDowntrend($currencyPair) == 1) {

        $imageName = $trade->drawChart($trade->getChartData($currencyPair), $currencyPair, time());

        $mailtext = '<html xmlns="http://www.w3.org/1999/xhtml">
                        <head>
                            <title>'.$currencyPair. ", DownTrend durchbrochen".'</title>
                        </head>
                         
                        <body>
                         
                            <h1>'.$currencyPair. ", DownTrend durchbrochen".'</h1>
                             
                            <img src="https://netartists.de/downloads/tmp/'.$imageName.'" alt="Chart" width="100%" style="display: block;">
                         
                        </body>
                      </html>';

        $betreff    = $currencyPair. ", DownTrend durchbrochen";

        $sendMail = new Mail();
        $sendMail->sendMail($mailtext, $betreff);
    }
}

$storeTrade = new Trades();
$storeTrade->name = 'test';
$storeTrade->storeTrade();















