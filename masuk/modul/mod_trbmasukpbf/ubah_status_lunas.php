<?php
include "../../../configurasi/koneksi.php";
session_start();
if (empty($_SESSION['username']) and empty($_SESSION['passuser'])) {
    echo "<link href=../css/style.css rel=stylesheet type=text/css>";
    echo "<div class='error msg'>Untuk mengakses Modul anda harus login.</div>";
} 
else {}

$module = $_GET['module'];
$act = $_GET['act'];
$count = $_POST['check'];

for ($i = 0; $i < count($count); $i++) {
    // echo $count[$i] . '<br>';

    mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE trbmasuk 
    SET carabayar='LUNAS',
    tgl_lunas=NOW(),
    petugas_lunas='$_SESSION[namalengkap]'    
    WHERE kd_trbmasuk = '$count[$i]'");
}
header('location:../../media_admin.php?module=trbmasukpbf');
