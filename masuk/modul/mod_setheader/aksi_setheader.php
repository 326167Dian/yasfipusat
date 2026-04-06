<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
 if (empty($_SESSION['username']) AND empty($_SESSION['passuser'])){
  echo "<link href='style.css' rel='stylesheet' type='text/css'>
 <center>Untuk mengakses modul, Anda harus login <br>";
  echo "<a href=../../index.php><b>LOGIN</b></a></center>";
}
else{
include "../../../configurasi/koneksi.php";
include "../../../configurasi/fungsi_thumb.php";
include "../../../configurasi/library.php";

$module=$_GET['module'];
$act=$_GET['act'];

// Input admin
if ($module=='setheader' AND $act=='update_setheader'){
 
    $fupload_name =  $_FILES["fupload1"]["name"];
    $tipe_file    =  $_FILES["fupload1"]["type"];
    $ukuran_file  =  $_FILES["fupload1"]["size"];
    if (!empty($fupload_name)) {
        if ($tipe_file != "image/jpeg" AND $tipe_file != "image/pjpeg" AND $tipe_file != "image/png"){
            echo "<script>window.alert('Upload Gagal, Pastikan File Logo yang di Upload bertipe *.JPG atau *.PNG');
                window.location='../../media_admin.php?module=setheader'</script>";
            exit();
        } elseif ($ukuran_file > 1000000) {
            echo "<script>window.alert('Upload Gagal, Ukuran File Logo Maksimal 1 MB');
                window.location='../../media_admin.php?module=setheader'</script>";
            exit();
        } else {
            $vdir_upload = "../../images/";
            $vfile_upload = $vdir_upload . $fupload_name;
            move_uploaded_file($_FILES["fupload1"]["tmp_name"], $vfile_upload);
        }
    }
    
    // Upload Tanda Tangan
    $fupload_name2 = $_FILES["fupload2"]["name"];
    $tipe_file2    = $_FILES["fupload2"]["type"];
    $ukuran_file2  = $_FILES["fupload2"]["size"];
    if (!empty($fupload_name2)) {
        if ($tipe_file2 != "image/jpeg" AND $tipe_file2 != "image/pjpeg" AND $tipe_file2 != "image/png"){
            echo "<script>window.alert('Upload Gagal, Pastikan File Tanda Tangan yang di Upload bertipe *.JPG atau *.PNG');
                window.location='../../media_admin.php?module=setheader'</script>";
            exit();
        } elseif ($ukuran_file2 > 1000000) {
            echo "<script>window.alert('Upload Gagal, Ukuran File Tanda Tangan Maksimal 1 MB');
                window.location='../../media_admin.php?module=setheader'</script>";
            exit();
        } else {
            $vdir_upload = "../../images/";
            $vfile_upload = $vdir_upload . $fupload_name2;
            move_uploaded_file($_FILES["fupload2"]["tmp_name"], $vfile_upload);
        }
    }

    // Upload Stempel
    $fupload_name3 = $_FILES["fupload3"]["name"];
    $tipe_file3    = $_FILES["fupload3"]["type"];
    $ukuran_file3  = $_FILES["fupload3"]["size"];
    if (!empty($fupload_name3)) {
        if ($tipe_file3 != "image/jpeg" AND $tipe_file3 != "image/pjpeg" AND $tipe_file3 != "image/png"){
            echo "<script>window.alert('Upload Gagal, Pastikan File Stempel yang di Upload bertipe *.JPG atau *.PNG');
                window.location='../../media_admin.php?module=setheader'</script>";
            exit();
        } elseif ($ukuran_file3 > 1000000) {
            echo "<script>window.alert('Upload Gagal, Ukuran File Stempel Maksimal 1 MB');
                window.location='../../media_admin.php?module=setheader'</script>";
            exit();
        } else {
            $vdir_upload = "../../images/";
            $vfile_upload = $vdir_upload . $fupload_name3;
            move_uploaded_file($_FILES["fupload3"]["tmp_name"], $vfile_upload);
        }
    }

    $data = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT logo, tandatangan, stempel FROM setheader WHERE id_setheader = '$_POST[id]'");
    $row = mysqli_fetch_array($data);
    
    if($row['logo'] != 'mysifalogo.png' and $fupload_name !=''){
        unlink('../../images/'.$row['logo']);
    }
    if($row['tandatangan'] != '' and $fupload_name2 !=''){
        unlink('../../images/'.$row['tandatangan']);
    }
    if($row['stempel'] != '' and $fupload_name3 !=''){
        unlink('../../images/'.$row['stempel']);
    }

    if($fupload_name == ''){
        $fupload_name = $row['logo'];
    }
    if($fupload_name2 == ''){
        $fupload_name2 = $row['tandatangan'];
    }
    if($fupload_name3 == ''){
        $fupload_name3 = $row['stempel'];
    }
    
    mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE setheader SET satu = '$_POST[satu]',
        										dua         = '$_POST[dua]',
        										tiga        = '$_POST[tiga]',
        										empat       = '$_POST[empat]',
        										lima        = '$_POST[lima]',
        										enam        = '$_POST[enam]',
        										tujuh       = '$_POST[tujuh]',
        										delapan     = '$_POST[delapan]',
        										sembilan    = '$_POST[sembilan]',
        										sepuluh     = '$_POST[sepuluh]',
        										sebelas     = '$_POST[sebelas]',
        										duabelas    = '$_POST[duabelas]',
        										tigabelas   = '$_POST[tigabelas]',
        										logo        = '$fupload_name',
                                                tandatangan = '$fupload_name2',
                                                stempel     = '$fupload_name3'
        									WHERE id_setheader = '$_POST[id]'");
									
	echo "<script type='text/javascript'>alert('Data berhasil diubah !');window.location='../../media_admin.php?module=".$module."'</script>";
	//header('location:../../media_admin.php?module='.$module);
	
}

}
?>
