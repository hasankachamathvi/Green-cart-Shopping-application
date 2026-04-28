<?php
$servername = "fdb1029.awardspace.net";
$username   = "4537812_greencart";
$password   = "p%#O3n9t420@qi7]";
$dbname     = "4537812_greencart";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
