<?php
$server = "localhost";
$dbUser = "root";
$dbPass = "";
$dbName = "teakoloccoy";

$connect = new mysqli($server, $dbUser, $dbPass, $dbName);
if ($connect->connect_error) {
    die("Connection failed: " . $connect->connect_error);
}

