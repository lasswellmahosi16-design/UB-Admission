<?php
require_once 'includes/db.php';
// Destroy the session to log the student out, then redirect to login
session_destroy();
header('Location: login.php');
exit;
?>
