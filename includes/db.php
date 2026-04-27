<?php
// Database connection settings — update DB_USER and DB_PASS to match your server
define('DB_HOST', '10.0.19.74');
define('DB_USER', 'mah01477');        // my MySQL username
define('DB_PASS', 'mah01477');            // my MySQL password
define('DB_NAME', 'db_mah01477');

// Connect to MySQL — this $conn variable is used on every page
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Stop the page and show an error if the connection fails
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Start the session so we can remember who is logged in across pages
session_start();
?>
