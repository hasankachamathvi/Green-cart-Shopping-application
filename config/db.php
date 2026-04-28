<?php
if (!function_exists('connect_database')) {
    function connect_database(string $servername, string $username, string $password, string $dbname): ?mysqli {
        $conn = @new mysqli($servername, $username, $password, $dbname);
        if ($conn->connect_errno) {
            return null;
        }
        return $conn;
    }
}

// Prefer the AwardSpace host, but fall back to local WAMP so the app keeps working in development.
$conn = connect_database('fdb1029.awardspace.net', '4537812_greencart', 'p%#O3n9t420@qi7]', '4537812_greencart');

if (!$conn) {
    $conn = connect_database('localhost', 'root', '', 'shopping_cart_db');
}

if (!$conn) {
    die('Connection failed: unable to connect to either the remote host or the local database.');
}
