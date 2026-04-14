<?php

// Date_Default_timezone_set('Asia/jakarta');
// $server = "localhost";
// $user = "u479820688_sentosa";
// $password = "326167Dian&&";
// $database = "u479820688_sentosa";
// set_time_limit(1800);

// include_once "conn.php";
// ($GLOBALS["___mysqli_ston"] = mysqli_connect($server, $user, $password)) or die("Koneksi gagal");
// mysqli_select_db($GLOBALS["___mysqli_ston"], $database) or die("Database tidak ditemukan");
// $db = mysqli_connect('localhost', 'u479820688_sentosa', '326167Dian&&', 'u479820688_sentosa');

// // Koneksi server cabang
// $server2    = "localhost";
// $user2      = "u479820688_heru2";
// $password2  = "7390091979Dian&&";
// $database2  = "u479820688_kitamakmur";
// set_time_limit(1800);

// ($GLOBALS["___mysqli_ston2"] = mysqli_connect($server2, $user2, $password2)) or die("Koneksi gagal");
// mysqli_select_db($GLOBALS["___mysqli_ston2"], $database2) or die("Database tidak ditemukan");
// $db2 = mysqli_connect('localhost', 'u479820688_heru2', '7390091979Dian&&', 'u479820688_kitamakmur');

Date_Default_timezone_set('Asia/jakarta');
$server = "localhost";
$user = "root";
$password = "";
$database = "yasfi";
set_time_limit(1800);

include_once "conn.php";
($GLOBALS["___mysqli_ston"] = mysqli_connect($server, $user, $password)) or die("Koneksi gagal");
mysqli_select_db($GLOBALS["___mysqli_ston"], $database) or die("Database tidak ditemukan");
$db = mysqli_connect('localhost', 'root', '', 'yasfi');

// New connection for yasfi2
Date_Default_timezone_set('Asia/jakarta');
$server2 = "localhost"; // Assuming the same server
$user2 = "root";       // Assuming the same user
$password2 = "";       // Assuming the same password
$database2 = "yasfi2"; // New database name
set_time_limit(1800);

($GLOBALS["___mysqli_ston2"] = mysqli_connect($server2, $user2, $password2)) or die("Koneksi ke yasfi2 gagal");
mysqli_select_db($GLOBALS["___mysqli_ston2"], $database2) or die("Database yasfi2 tidak ditemukan");
$db2 = mysqli_connect('localhost', 'root', '', 'yasfi2');
?>
