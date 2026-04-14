<?php
session_start();
if (empty($_SESSION['username']) and empty($_SESSION['passuser'])) {
    echo "<link href='style.css' rel='stylesheet' type='text/css'>
 <center>Untuk mengakses modul, Anda harus login <br>";
    echo "<a href=../../index.php><b>LOGIN</b></a></center>";
} else {
    if ($_SESSION['level'] != 'pemilik') {
        echo "<script type='text/javascript'>alert('Anda tidak berhak mengakses halaman ini.');window.location='../../media_admin.php?module=home'</script>";
        exit;
    }

    include "../../../configurasi/koneksi.php";
    include "../../../configurasi/fungsi_thumb.php";
    include "../../../configurasi/library.php";

    $module = $_GET['module'];
    $act = $_GET['act'];

    if ($module == 'kdtk' and $act == 'input_kdtk') {
        $kd_trkasir = trim($_POST['kd_trkasir']);
        $id_admin = (int)$_POST['id_admin'];
        $stt_kdtk = ($_POST['stt_kdtk'] == 'OFF') ? 'OFF' : 'ON';

        if ($kd_trkasir == '' || $id_admin <= 0) {
            echo "<script type='text/javascript'>alert('Data belum lengkap.');history.go(-1);</script>";
            exit;
        }

        $cek = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT id_kdtk FROM kdtk WHERE kd_trkasir='$kd_trkasir' LIMIT 1");
        if (mysqli_num_rows($cek) > 0) {
            echo "<script type='text/javascript'>alert('Kode transaksi sudah ada.');history.go(-1);</script>";
            exit;
        }

        // Mendukung struktur lama yang belum AUTO_INCREMENT pada id_kdtk.
        $qmax = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT MAX(id_kdtk) AS max_id FROM kdtk");
        $rmax = mysqli_fetch_array($qmax);
        $next_id = (int)$rmax['max_id'] + 1;

        mysqli_query($GLOBALS["___mysqli_ston"], "INSERT INTO kdtk(id_kdtk, kd_trkasir, id_admin, stt_kdtk)
            VALUES('$next_id', '$kd_trkasir', '$id_admin', '$stt_kdtk')");

        header('location:../../media_admin.php?module=' . $module);
    } elseif ($module == 'kdtk' and $act == 'update_kdtk') {
        $id_kdtk = (int)$_POST['id_kdtk'];
        $kd_trkasir = trim($_POST['kd_trkasir']);
        $id_admin = (int)$_POST['id_admin'];
        $stt_kdtk = ($_POST['stt_kdtk'] == 'OFF') ? 'OFF' : 'ON';

        if ($id_kdtk <= 0 || $kd_trkasir == '' || $id_admin <= 0) {
            echo "<script type='text/javascript'>alert('Data belum lengkap.');history.go(-1);</script>";
            exit;
        }

        $cek = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT id_kdtk FROM kdtk WHERE kd_trkasir='$kd_trkasir' AND id_kdtk!='$id_kdtk' LIMIT 1");
        if (mysqli_num_rows($cek) > 0) {
            echo "<script type='text/javascript'>alert('Kode transaksi sudah dipakai data lain.');history.go(-1);</script>";
            exit;
        }

        mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE kdtk
            SET kd_trkasir='$kd_trkasir',
                id_admin='$id_admin',
                stt_kdtk='$stt_kdtk'
            WHERE id_kdtk='$id_kdtk'");

        header('location:../../media_admin.php?module=' . $module);
    } elseif ($module == 'kdtk' and $act == 'hapus') {
        $id_kdtk = (int)$_GET['id'];
        if ($id_kdtk > 0) {
            mysqli_query($GLOBALS["___mysqli_ston"], "DELETE FROM kdtk WHERE id_kdtk='$id_kdtk'");
        }

        header('location:../../media_admin.php?module=' . $module);
    }
}
