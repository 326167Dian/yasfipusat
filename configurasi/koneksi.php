<?php

date_default_timezone_set('Asia/jakarta');
$server = "localhost";
$user = "u725913413_heru";
$password = "7390091979Dian&&";
$database = "u725913413_yasfi";
set_time_limit(1800);

include_once "conn.php";
($GLOBALS["___mysqli_ston"] = mysqli_connect($server, $user, $password)) or die("Koneksi gagal");
mysqli_select_db($GLOBALS["___mysqli_ston"], $database) or die("Database tidak ditemukan");
$db = mysqli_connect('localhost', 'u725913413_heru', '7390091979Dian&&', 'u725913413_yasfi');

// Koneksi server cabang
$server2    = "localhost";
$user2      = "u725913413_heru2";
$password2  = "7390091979Dian&&";
$database2  = "u725913413_yasfi2";
set_time_limit(1800);

($GLOBALS["___mysqli_ston2"] = mysqli_connect($server2, $user2, $password2)) or die("Koneksi gagal");
mysqli_select_db($GLOBALS["___mysqli_ston2"], $database2) or die("Database tidak ditemukan");
$db2 = mysqli_connect('localhost', 'u725913413_heru2', '7390091979Dian&&', 'u725913413_yasfi2');

// Date_Default_timezone_set('Asia/jakarta');
// $server = "localhost";
// $user = "root";
// $password = "";
// $database = "yasfi";
// set_time_limit(1800);

// include_once "conn.php";
// ($GLOBALS["___mysqli_ston"] = mysqli_connect($server, $user, $password)) or die("Koneksi gagal");
// mysqli_select_db($GLOBALS["___mysqli_ston"], $database) or die("Database tidak ditemukan");
// $db = mysqli_connect('localhost', 'root', '', 'yasfi');

// // New connection for yasfi2
// Date_Default_timezone_set('Asia/jakarta');
// $server2 = "localhost"; // Assuming the same server
// $user2 = "root";       // Assuming the same user
// $password2 = "";       // Assuming the same password
// $database2 = "yasfi2"; // New database name
// set_time_limit(1800);

// ($GLOBALS["___mysqli_ston2"] = mysqli_connect($server2, $user2, $password2)) or die("Koneksi ke yasfi2 gagal");
// mysqli_select_db($GLOBALS["___mysqli_ston2"], $database2) or die("Database yasfi2 tidak ditemukan");
// $db2 = mysqli_connect('localhost', 'root', '', 'yasfi2');
?>
