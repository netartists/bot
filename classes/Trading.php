<?php

// include api wrapper
include("api/wrapper.php");

// include pChart library
include("lib/pChart2.1.4/class/pData.class.php");
include("lib/pChart2.1.4/class/pDraw.class.php");
include("lib/pChart2.1.4/class/pImage.class.php");
include("lib/pChart2.1.4/class/pStock.class.php");

class tradingClass {

    private $numberOfPeriods = 7;

    /**
     * Checks whether the upward trend has been broken down trough
     *
     * x higher periods followed by y lower periods
     *
     * 5 minutes periods
     */
    function checkBrokenUptrend($currencyPair) {

        $currentTimestamp = time();
        $amountOfFollowingHigherPeriods = 5;
        $amountOfFollowingLowerPeriods = 2;
        $amountOfHigherPeriodsInARow = 0;
        $amountOfLowerPeriodsInARow = 0;
        $highTrend = false;

        for ($i=$this->numberOfPeriods; $i>=1; $i--) {

            $start = $currentTimestamp - ($i * 300);
            $end = $currentTimestamp - (($i-1) * 300);

            // better sleep, because API allows not so much requests (max. 6 per second)
            time_nanosleep(0, 200000000);

            // initialize API
            $poloniexApi = new poloniex(API_KEY, API_SECRET);
            $chartData = $poloniexApi->get_chart_data($currencyPair, $start, $end, 300);

            if (array_key_exists("candleStick", $chartData)) {

                if (($chartData["candleStick"][0]["close"] - $chartData["candleStick"][0]["open"]) > 0) {
                    // "higher";
                    $amountOfHigherPeriodsInARow++;
                    $amountOfLowerPeriodsInARow = 0;

                    if ($amountOfHigherPeriodsInARow >= $amountOfFollowingHigherPeriods) {
                        $highTrend = true;
                    }
                } else {
                    // "lower";
                    $amountOfLowerPeriodsInARow++;

                    if ($highTrend == false) {
                        $amountOfHigherPeriodsInARow = 0;
                    } else {

                        if ($amountOfLowerPeriodsInARow >= $amountOfFollowingLowerPeriods) {

                            // broken uptrend detected
                            return 1;
                        }
                    }
                }
            }
        }

        return 0;
    }

    /**
     * Checks whether the downward trend has been broken up trough
     *
     * x lower periods followed by y higher periods
     *
     * 5 minutes periods
     */
    function checkBrokenDowntrend($currencyPair) {

        $currentTimestamp = time();
        $amountOfFollowingLowerPeriods = 5;
        $amountOfFollowingHigherPeriods = 2;
        $amountOfLowerPeriodsInARow = 0;
        $amountOfHigherPeriodsInARow = 0;
        $lowTrend = false;

        for ($i=$this->numberOfPeriods; $i>=1; $i--) {

            $start = $currentTimestamp - ($i * 300);
            $end = $currentTimestamp - (($i-1) * 300);

            // better sleep, because API allows not so much requests (max. 6 per second)
            time_nanosleep(0, 200000000);

            // initialize API
            $poloniexApi = new poloniex(API_KEY, API_SECRET);
            $chartData = $poloniexApi->get_chart_data($currencyPair, $start, $end, 300);

            if (array_key_exists("candleStick", $chartData)) {

                if (($chartData["candleStick"][0]["close"] - $chartData["candleStick"][0]["open"]) < 0) {
                    // "lower";
                    $amountOfLowerPeriodsInARow++;
                    $amountOfHigherPeriodsInARow = 0;

                    if ($amountOfLowerPeriodsInARow >= $amountOfFollowingLowerPeriods) {
                        $lowTrend = true;
                    }
                } else {
                    // "higher";
                    $amountOfHigherPeriodsInARow++;

                    if ($lowTrend == false) {
                        $amountOfLowerPeriodsInARow = 0;
                    } else {

                        if ($amountOfHigherPeriodsInARow >= $amountOfFollowingHigherPeriods) {

                            // broken downtrend detected
                            return 1;
                        }
                    }
                }
            }
        }

        return 0;
    }

    /**
     * Gets chart data
     */
    function getChartData($currencyPair) {

        $currentTimestamp = time();
        $start = $currentTimestamp - (0.5 * 24 * 3600);
        $end = $currentTimestamp;

        // initialize API
        $poloniexApi = new poloniex(API_KEY, API_SECRET);
        $chartData = $poloniexApi->get_chart_data($currencyPair, $start, $end, 300);

        return $chartData;
    }

    /**
     * Draws chart to file
     */
    function drawChart($chartData, $currencyPair, $timestamp) {

        /* Create and populate the pData object */

        $dateArray = array();
        $openArray = array();
        $closeArray = array();
        $minArray = array();
        $maxArray = array();

        foreach ($chartData["candleStick"] as $candlestick) {
            array_push($dateArray, ""); // no x-axis text
            array_push($openArray, $candlestick["open"]);
            array_push($closeArray, $candlestick["close"]);
            array_push($minArray, $candlestick["low"]);
            array_push($maxArray, $candlestick["high"]);
        }

        $MyData = new pData();
        $MyData->addPoints($openArray,"Open");
        $MyData->addPoints($closeArray,"Close");
        $MyData->addPoints($minArray,"Min");
        $MyData->addPoints($maxArray,"Max");
        $MyData->setAxisDisplay(0,AXIS_FORMAT_CURRENCY,"BTC ");

        $MyData->addPoints($dateArray,"Time");
        $MyData->setAbscissa("Time");

        /* Create the pChart object */
        $myPicture = new pImage(700,230,$MyData);

        /* Turn of AAliasing */
        $myPicture->Antialias = FALSE;

        /* Draw the border */
        $myPicture->drawRectangle(0,0,699,229,array("R"=>0,"G"=>0,"B"=>0));

        /* Set the default font settings */
        $myPicture->setFontProperties(array("FontName"=>"lib/pChart2.1.4/fonts/pf_arma_five.ttf","FontSize"=>6));

        /* Define the chart area */
        $myPicture->setGraphArea(60,30,650,190);

        /* Draw the scale */
        $scaleSettings = array("GridR"=>200,"GridG"=>200,"GridB"=>200,"DrawSubTicks"=>TRUE,"CycleBackground"=>TRUE);
        $myPicture->drawScale($scaleSettings);

        /* Create the pStock object */
        $mystockChart = new pStock($myPicture,$MyData);

        /* Draw the stock chart */
        $stockSettings = array("BoxWidth"=>2,"BoxUpR"=>0,"BoxUpG"=>255,"BoxUpB"=>0,"BoxDownR"=>255,"BoxDownG"=>0,"BoxDownB"=>0,"ExtremityAlpha"=>0);
        $mystockChart->drawStockChart($stockSettings);

        /* Render the picture (choose the best way) */
        $myPicture->Render("../downloads/tmp/".$currencyPair."_".$timestamp.".png");

        return $currencyPair."_".$timestamp.".png";
    }
}