<?php

/**
 * Simple database output
 *
 * @author     René Sonntag, info@netartists.de
 * @version    1.0
 */

// include config
include("config.php");

// ####################################

echo "<h1>Current Trades</h1>";

$mysqli = new mysqli('localhost', DB_USER, DB_PW, DB_NAME);

if ($mysqli->connect_errno) {
    die('Verbindung fehlgeschlagen: ' . $mysqli->connect_error);
}

$sql = "SELECT * FROM depot";
$result = $mysqli->query($sql);

echo "<table border=1 cellspacing=0 cellpadding=10>";
echo "<th>id</th>";
echo "<th>name</th>";
echo "<th>provider</th>";
echo "<th>buyDate</th>";
echo "<th>sellDate</th>";
echo "<th>buyPrice</th>";
echo "<th>sellPrice</th>";
echo "<th>stopPrice</th>";
echo "<th>tradingReason</th>";
echo "<th>trailingStopDistance</th>";

while($row = $result->fetch_object()) {
    echo "<tr>";
        echo "<td>".$row->id."</td>";
        echo "<td>".$row->name."</td>";
        echo "<td>".$row->provider."</td>";
        echo "<td>".$row->buyDate."</td>";
        echo "<td>".$row->sellDate."</td>";
        echo "<td>".$row->buyPrice."</td>";
        echo "<td>".$row->sellPrice."</td>";
        echo "<td>".$row->stopPrice."</td>";
        echo "<td>".$row->tradingReason."</td>";
        echo "<td>".$row->trailingStopDistance."</td>";
    echo "</tr>";
}
echo "</table>";











