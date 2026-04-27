<?php
require_once '../includes/db.php';
// Destroy the admin session and redirect to admin login
session_destroy();
header('Location: login.php');
exit;
?>
