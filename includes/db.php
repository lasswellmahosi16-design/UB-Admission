<?php
// Database configuration - change these to match your server
define('DB_HOST', '10.0.19.74');
define('DB_USER', 'mah01477');        // your MySQL username
define('DB_PASS', 'mah01477');            // your MySQL password
define('DB_NAME', 'db_mah01477');

$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

session_start();
?>
