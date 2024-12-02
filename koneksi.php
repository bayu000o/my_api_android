<?php

$hostName = "localhost";
$userName = "root";
$password = "";
$dbName = "donasipedulyy";

$koneksi = mysqli_connect($hostName, $userName, $password, $dbName);

if ($koneksi->connect_error) {
    die("Koneksi database gagal: " . $koneksi->connect_error);
}
?>
