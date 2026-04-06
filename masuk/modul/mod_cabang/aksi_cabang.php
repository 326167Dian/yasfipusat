<?php
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
if ($module=='cabang' AND $act=='input_cabang'){

$cekganda=mysqli_query($GLOBALS["___mysqli_ston"], "SELECT * FROM cabang WHERE nm_cabang='$_POST[nm_cabang]'AND tlp_cabang='$_POST[tlp_cabang]'");
$ada=mysqli_num_rows($cekganda);
if ($ada > 0){
echo "<script type='text/javascript'>alert('Nama Cabang dengan nomor telepon ini sudah ada!');history.go(-1);</script>";
}else{

    mysqli_query($GLOBALS["___mysqli_ston"], "INSERT INTO cabang(nm_cabang, tlp_cabang, alamat_cabang, ket_cabang)
								 VALUES('$_POST[nm_cabang]','$_POST[tlp_cabang]','$_POST[alamat_cabang]','$_POST[ket_cabang]')");
										
										
	//echo "<script type='text/javascript'>alert('Data berhasil ditambahkan !');window.location='../../media_admin.php?module=".$module."'</script>";
	header('location:../../media_admin.php?module='.$module);

}
}
 //updata pelanggan
 elseif ($module=='cabang' AND $act=='update_cabang'){
 
     mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE cabang SET nm_cabang = '$_POST[nm_cabang]',
									tlp_cabang = '$_POST[tlp_cabang]',
									alamat_cabang = '$_POST[alamat_cabang]',
									ket_cabang = '$_POST[ket_cabang]'
									WHERE id_cabang = '$_POST[id]'");
									
	//echo "<script type='text/javascript'>alert('Data berhasil diubah !');window.location='../../media_admin.php?module=".$module."'</script>";
	header('location:../../media_admin.php?module='.$module);
	
}
//Hapus Proyek
elseif ($module=='cabang' AND $act=='hapus'){

  mysqli_query($GLOBALS["___mysqli_ston"], "DELETE FROM cabang WHERE id_cabang = '$_GET[id]'");
  //echo "<script type='text/javascript'>alert('Data berhasil dihapus !');window.location='../../media_admin.php?module=".$module."'</script>";
  header('location:../../media_admin.php?module='.$module);
}

}
?>
