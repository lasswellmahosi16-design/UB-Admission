<?php
require_once 'includes/db.php';
session_destroy();
header('Location: login.php?logged_out=1');
exit;
?>
