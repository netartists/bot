<?php

/**
 * Analyse class
 *
 * Class provides functions to analyse charts
 *
 * @author     René Sonntag, info@netartists.de
 * @version    1.0
 */

// include api wrapper
include("api/wrapper.php");

// include pChart library
include("lib/pChart2.1.4/class/pData.class.php");
include("lib/pChart2.1.4/class/pDraw.class.php");
include("lib/pChart2.1.4/class/pImage.class.php");
include("lib/pChart2.1.4/class/pStock.class.php");

class Analyse
{

    private $numberOfPeriods = 7;
    private $numberOfPeriodsTrend = 16;
    private $periodSeconds = 1800;

    public $name = '';
    public $provider = 'Poloniex';
    public $buyDate = '';
    public $sellDate = '';
    public $buyPrice = '';
    public $sellPrice = '';
    public $stopPrice = '';
    public $tradingReason = '';
    public $trailingStopDistance = '10';
    public $tradingSignal = false;

    public $upperTrendBroken = false;
    public $lowerTrendBroken = false;
    public $upperRowBroken = false;
    public $lowerRowBroken = false;

    /**
     * Checks currency pairs for trend signals
     *
     * $currencyPairs array Array of currency pairs
     */
    public function checkTrendSignals($currencyPair)
    {

        // general properties
        $this->name = $currencyPair;

        // get chart data
        $currentTimestamp = time();
        $start = $currentTimestamp - ($this->numberOfPeriodsTrend * $this->periodSeconds);
        $end = $currentTimestamp;
        // initialize API
        $poloniexApi = new poloniex(API_KEY, API_SECRET);
        $chartData = $poloniexApi->get_chart_data($currencyPair, $start, $end, $this->periodSeconds);

        /*
        echo "<pre>";
        echo print_r($chartData);
        echo "<pre>";
        */

        $this->checkBrokenUprow($chartData);
        $this->checkBrokenDownrow($chartData);

        $this->checkBrokenUprow($chartData);
        $this->checkBrokenDownrow($chartData);

        if ($this->upperTrendBroken && $this->upperRowBroken) {
            $this->tradingReason = 'Obere Trendlinie durchbrochen';
            $this->tradingSignal = true;
        }

        if ($this->lowerTrendBroken && $this->lowerRowBroken) {
            $this->tradingReason = 'Untere Trendlinie durchbrochen';
            $this->tradingSignal = true;
        }

        /*
        // check for broken upper trendline
        if ($this->checkBrokenUpperTrendline($chartData)) {

            $this->tradingReason = 'Obere Trendlinie durchbrochen';
            $this->tradingSignal = true;

            // // send mail
            // // $imageName = $this->drawChart($this->getChartData($currencyPair), $currencyPair, time());
            // // $mailtext = 'UpTrend durchbrochen';
            // // $betreff    = $currencyPair. ", UpTrend durchbrochen";
            // // $sendMail = new Mail();
            // // $sendMail->sendMail($mailtext, $betreff, $currencyPair, $imageName);
        }

        // check for broken lower trendline
        if ($this->checkBrokenLowerTrendline($chartData)) {

            $this->tradingReason = 'Untere Trendlinie durchbrochen';
            $this->tradingSignal = true;

            // send mail
            // $imageName = $this->drawChart($this->getChartData($currencyPair), $currencyPair, time());
            // $mailtext = 'DownTrend durchbrochen';
            // $betreff    = $currencyPair. ", DownTrend durchbrochen";
            // $sendMail = new Mail();
            // $sendMail->sendMail($mailtext, $betreff, $currencyPair, $imageName);
        }
        */
    }

    /**
     * Checks whether the upper trendline has been broken trough
     *
     * Divide chartData in two Parts
     * Get highest candles of each part
     * Connect the two points to get a linear equation
     * Calculate trend value for last candle
     * Compare calculated trend value with value of last candle
     *
     * @param chartData
     * @return boolean
     */
    public function checkBrokenUpperTrendline($chartData)
    {
        if (array_key_exists('candleStick', $chartData)) {

            $countCandlesticks = count($chartData['candleStick']);
            if ($countCandlesticks % 2 != 0) {
                $countCandlesticks = $countCandlesticks - 1;
            }

            // get highest closing value in $chartData part one
            $highestValuePartOne = 0;
            $highestDatePartOne = '';

            for ($i = 0; $i < ($countCandlesticks / 2)+1; $i++) {
                if ($chartData['candleStick'][$i]['high'] > $highestValuePartOne) {
                    $highestValuePartOne = $chartData['candleStick'][$i]['high'];
                    $highestDatePartOne = $chartData['candleStick'][$i]['date'];
                }
            }

            // get highest closing value in $chartData part two
            $highestValuePartTwo = 0;
            $highestDatePartTwo = '';

            for ($i = ($countCandlesticks / 2)+1; $i <= $countCandlesticks - 2; $i++) {
                if ($chartData['candleStick'][$i]['high'] > $highestValuePartTwo) {
                    $highestValuePartTwo = $chartData['candleStick'][$i]['high'];
                    $highestDatePartTwo = $chartData['candleStick'][$i]['date'];
                }
            }

            // build linear equation
            $equationParameters = $this->findLinearEquation($highestValuePartOne, $highestValuePartTwo, $highestDatePartOne, $highestDatePartTwo);

            // check if second last candle broke the trend
            // y = m * x + b
            // x is date
            // calculate trend y
            $secondLastHigh = false;
            $secondLastCandle = $chartData['candleStick'][count($chartData['candleStick'])-2];
            $calculatedTrendValueSecondLastCandle = ($equationParameters['m'] * $secondLastCandle['date']) + $equationParameters['b'];

            if ($secondLastCandle['close'] > $calculatedTrendValueSecondLastCandle) {
                $secondLastHigh = true;
            }

            // check if last candle broke the trend
            // y = m * x + b
            // x is date
            // calculate trend y
            $lastCandle = $chartData['candleStick'][count($chartData['candleStick'])-1];
            $calculatedTrendValueLastCandle = ($equationParameters['m'] * $lastCandle['date']) + $equationParameters['b'];

            if ($lastCandle['close'] > $calculatedTrendValueLastCandle && $secondLastHigh == true) {
                $this->upperTrendBroken = true;
                return true;
            }
        }

        return false;
    }


    /**
     * Checks whether the lower trendline has been broken trough
     *
     * Divide chartData in two Parts
     * Get lowest candles of each part
     * Connect the two points to get a linear equation
     * Calculate trend value for last candle
     * Compare calculated trend value with value of last candle
     *
     * @param chartData
     * @return boolean
     */
    public function checkBrokenLowerTrendline($chartData)
    {
        if (array_key_exists('candleStick', $chartData)) {

            $countCandlesticks = count($chartData['candleStick']);
            if ($countCandlesticks % 2 != 0) {
                $countCandlesticks = $countCandlesticks - 1;
            }

            // get lowest closing value in $chartData part one
            $lowestValuePartOne = 999999999999;
            $lowestDatePartOne = '';

            for ($i = 0; $i < ($countCandlesticks / 2)+1; $i++) {
                if ($chartData['candleStick'][$i]['low'] < $lowestValuePartOne) {
                    $lowestValuePartOne = $chartData['candleStick'][$i]['low'];
                    $lowestDatePartOne = $chartData['candleStick'][$i]['date'];
                }
            }

            // get lowest closing value in $chartData part two
            $lowestValuePartTwo = 999999999999;
            $lowestDatePartTwo = '';

            for ($i = ($countCandlesticks / 2)+1; $i <= $countCandlesticks - 2; $i++) {
                if ($chartData['candleStick'][$i]['low'] < $lowestValuePartTwo) {
                    $lowestValuePartTwo = $chartData['candleStick'][$i]['low'];
                    $lowestDatePartTwo = $chartData['candleStick'][$i]['date'];
                }
            }

            // build linear equation
            $equationParameters = $this->findLinearEquation($lowestValuePartOne, $lowestValuePartTwo, $lowestDatePartOne, $lowestDatePartTwo);

            // check if second last candle broke the trend
            // y = m * x + b
            // x is date
            // calculate trend y
            $secondLastLow = false;
            $secondLastCandle = $chartData['candleStick'][count($chartData['candleStick'])-2];
            $calculatedTrendValueSecondLastCandle = ($equationParameters['m'] * $secondLastCandle['date']) + $equationParameters['b'];

            if ($secondLastCandle['close'] < $calculatedTrendValueSecondLastCandle) {
                $secondLastLow = true;
            }

            // check if last candle broke the trend
            // y = m * x + b
            // x is date
            // calculate trend y
            $lastCandle = $chartData['candleStick'][count($chartData['candleStick'])-1];
            $calculatedTrendValueLastCandle = ($equationParameters['m'] * $lastCandle['date']) + $equationParameters['b'];

            if ($lastCandle['close'] < $calculatedTrendValueLastCandle && $secondLastLow == true) {
                $this->lowerTrendBroken = true;
                return true;
            }
        }

        return false;
    }

    /**
     * Checks whether the upward row has been broken down trough
     *
     * x higher periods followed by y lower periods
     *
     * 5 minutes periods
     */
    public function checkBrokenUprow($chartData)
    {
        $amountOfFollowingHigherPeriods = 5;
        $amountOfFollowingLowerPeriods = 2;

        $amountOfHigherPeriodsInARow = 0;
        $amountOfLowerPeriodsInARow = 0;
        $highTrend = false;

        for ($i = $this->numberOfPeriods; $i >= 1; $i--) {

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

                            // broken uprow detected
                            $this->upperRowBroken = true;
                            return true;
                        }
                    }
                }
            }
        }

        return false;
    }

    /**
     * Checks whether the downward row has been broken up trough
     *
     * x lower periods followed by y higher periods
     *
     * 5 minutes periods
     */
    public function checkBrokenDownrow($chartData)
    {
        $amountOfFollowingLowerPeriods = 5;
        $amountOfFollowingHigherPeriods = 2;
        $amountOfLowerPeriodsInARow = 0;
        $amountOfHigherPeriodsInARow = 0;
        $lowTrend = false;

        for ($i = $this->numberOfPeriods; $i >= 1; $i--) {

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

                            // broken downrow detected
                            $this->lowerRowBroken = true;
                            return true;
                        }
                    }
                }
            }
        }

        return false;
    }

    /**
     * Gets chart data
     */
    private function getChartData($currencyPair)
    {

        $currentTimestamp = time();
        $start = $currentTimestamp - (0.5 * 24 * 3600);
        $end = $currentTimestamp;

        // initialize API
        $poloniexApi = new poloniex(API_KEY, API_SECRET);
        $chartData = $poloniexApi->get_chart_data($currencyPair, $start, $end, $this->periodSeconds);

        return $chartData;
    }

    /**
     * Draws chart to file
     */
    private function drawChart($chartData, $currencyPair, $timestamp)
    {

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
        $MyData->addPoints($openArray, "Open");
        $MyData->addPoints($closeArray, "Close");
        $MyData->addPoints($minArray, "Min");
        $MyData->addPoints($maxArray, "Max");
        $MyData->setAxisDisplay(0, AXIS_FORMAT_CURRENCY, "BTC ");

        $MyData->addPoints($dateArray, "Time");
        $MyData->setAbscissa("Time");

        /* Create the pChart object */
        $myPicture = new pImage(700, 230, $MyData);

        /* Turn of AAliasing */
        $myPicture->Antialias = FALSE;

        /* Draw the border */
        $myPicture->drawRectangle(0, 0, 699, 229, array("R" => 0, "G" => 0, "B" => 0));

        /* Set the default font settings */
        $myPicture->setFontProperties(array("FontName" => "lib/pChart2.1.4/fonts/pf_arma_five.ttf", "FontSize" => 6));

        /* Define the chart area */
        $myPicture->setGraphArea(60, 30, 650, 190);

        /* Draw the scale */
        $scaleSettings = array("GridR" => 200, "GridG" => 200, "GridB" => 200, "DrawSubTicks" => TRUE, "CycleBackground" => TRUE);
        $myPicture->drawScale($scaleSettings);

        /* Create the pStock object */
        $mystockChart = new pStock($myPicture, $MyData);

        /* Draw the stock chart */
        $stockSettings = array("BoxWidth" => 2, "BoxUpR" => 0, "BoxUpG" => 255, "BoxUpB" => 0, "BoxDownR" => 255, "BoxDownG" => 0, "BoxDownB" => 0, "ExtremityAlpha" => 0);
        $mystockChart->drawStockChart($stockSettings);

        /* Render the picture (choose the best way) */
        $myPicture->Render("../downloads/tmp/" . $currencyPair . "_" . $timestamp . ".png");

        return $currencyPair . "_" . $timestamp . ".png";
    }

    /**
     * Builds linear equation
     *
     * @param $valuePartOne
     * @param $valuePartTwo
     * @param $datePartOne
     * @param $datePartTwo
     *
     * @return array
     */
    public function findLinearEquation($valuePartOne, $valuePartTwo, $datePartOne, $datePartTwo)
    {
        // y = m * x + b

        // m = (y2 - y1) / (x2 - x1)
        $equationParameters['m'] = ($valuePartTwo - $valuePartOne) / ($datePartTwo - $datePartOne);

        // b = y - m * x
        $equationParameters['b'] = $valuePartOne - ($equationParameters['m'] * $datePartOne);

        return $equationParameters;
    }
}