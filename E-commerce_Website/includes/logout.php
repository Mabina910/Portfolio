<?php
session_start();
$_SESSION = array();

session_destroy();

header("Location:../e-commerce.php");
exit();
?>