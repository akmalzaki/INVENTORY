<?php
// Start the session.
session_start();
if(!isset($_SESSION['user'])) header('location: login.php');
$_SESSION['table'] = 'suppliers';
$_SESSION['redirect_to'] = 'supplier-add.php';
$user = $_SESSION['user'];
?>
