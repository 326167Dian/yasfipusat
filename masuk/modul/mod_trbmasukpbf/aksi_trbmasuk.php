<?php
error_reporting(0);
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

$conn = $GLOBALS["___mysqli_ston"];
$logFile = __DIR__ . '/aksi_trbmasuk_error.log';

function write_aksi_trbmasuk_log($logFile, $message)
{
	$timestamp = date('Y-m-d H:i:s');
	error_log("[$timestamp] $message" . PHP_EOL, 3, $logFile);
}

function run_query_or_fail_aksi_trbmasuk($conn, $sql)
{
	$result = mysqli_query($conn, $sql);
	if ($result === false) {
		throw new Exception('SQL Error: ' . mysqli_error($conn) . ' | Query: ' . $sql);
	}

	return $result;
}

$module= "trbmasukpbf";
$stt_aksi=$_POST['stt_aksi'];
if($stt_aksi == "input_trbmasuk" || $stt_aksi == "ubah_trbmasuk"){
$act=$stt_aksi;
}else{
$act=$_GET['act'];
}


// Input admin
if ($module=='trbmasukpbf' AND $act=='input_trbmasuk'){

    mysqli_query($GLOBALS["___mysqli_ston"], "INSERT INTO 
										trbmasuk(id_resto,
										kd_trbmasuk,
										tgl_trbmasuk,
										id_supplier,
										petugas,
										nm_supplier,
										tlp_supplier,
										alamat_trbmasuk,
										ttl_trbmasuk,
										dp_bayar,
										sisa_bayar,
										ket_trbmasuk,
										jatuhtempo,
										carabayar,
										jenis)
								 VALUES('pusat',
										'$_POST[kd_trbmasuk]',
										'$_POST[tgl_trbmasuk]',
										'$_POST[id_supplier]',
										'$_POST[petugas]',
										'$_POST[nm_supplier]',
										'$_POST[tlp_supplier]',
										'$_POST[alamat_trbmasuk]',
										'$_POST[ttl_trkasir]',
										'$_POST[dp_bayar]',
										'$_POST[sisa_bayar]',
										'$_POST[ket_trbmasuk]',
										'$_POST[jatuhtempo]',
										'$_POST[carabayar]',
										'pbf'
										)");
										
	mysqli_query($GLOBALS["___mysqli_ston"], "INSERT INTO kartu_stok(kode_transaksi) VALUES('$_POST[kd_trbmasuk]')");
	
	mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE kdbm SET stt_kdbm = 'OFF' WHERE id_admin = '$_SESSION[idadmin]' AND id_resto = 'pusat' AND kd_trbmasuk = '$_POST[kd_trbmasuk]'");
										
	if($_POST['carabayar'] == 'LUNAS'){ 
		mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE trbmasuk SET tgl_lunas = NOW(), petugas_lunas = '$_SESSION[namalengkap]' WHERE id_trbmasuk = '$_POST[id_trbmasuk]'");
	}									
	//echo "<script type='text/javascript'>alert('Transkasi berhasil ditambahkan !');window.location='../../media_admin.php?module=".$module."'</script>";
	

}
 //updata trbmasukpbf
 elseif ($module=='trbmasukpbf' AND $act=='ubah_trbmasuk'){
 

    mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE trbmasuk SET tgl_trbmasuk = '$_POST[tgl_trbmasuk]',
									id_supplier = '$_POST[id_supplier]',
									nm_supplier = '$_POST[nm_supplier]',
									tlp_supplier = '$_POST[tlp_supplier]',
									alamat_trbmasuk = '$_POST[alamat_trbmasuk]',
									ttl_trbmasuk = '$_POST[ttl_trkasir]',
									dp_bayar = '$_POST[dp_bayar]',
									sisa_bayar = '$_POST[sisa_bayar]',
									ket_trbmasuk = '$_POST[ket_trbmasuk]',
									jatuhtempo = '$_POST[jatuhtempo]',
									carabayar = '$_POST[carabayar]'
									WHERE id_trbmasuk = '$_POST[id_trbmasuk]'");
										
	if($_POST['carabayar'] == 'LUNAS'){
		mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE trbmasuk SET tgl_lunas = NOW(), petugas_lunas = '$_SESSION[namalengkap]' WHERE id_trbmasuk = '$_POST[id_trbmasuk]'");
	}
										
	
}
//Hapus Proyek
elseif ($module=='trbmasukpbf' AND $act=='hapus'){
	$id_trbmasuk = $_GET['id'];
	mysqli_begin_transaction($conn);

	try {
		$ambildatainduk = run_query_or_fail_aksi_trbmasuk($conn, "SELECT id_trbmasuk, kd_trbmasuk FROM trbmasuk WHERE id_trbmasuk='$id_trbmasuk'");
		$r1 = mysqli_fetch_array($ambildatainduk);
		if (!$r1) {
			throw new Exception('Data trbmasuk tidak ditemukan');
		}

		$kd_trbmasuk = $r1['kd_trbmasuk'];
		$ambildatadetail = run_query_or_fail_aksi_trbmasuk($conn, "SELECT * FROM trbmasuk_detail WHERE kd_trbmasuk='$kd_trbmasuk'");
		while ($r = mysqli_fetch_array($ambildatadetail)) {
			$id_dtrbmasuk = $r['id_dtrbmasuk'];
			$id_barang = $r['id_barang'];
			$qty_dtrbmasuk = (float) $r['qty_dtrbmasuk'];
			$no_batch = $r['no_batch'];
			$kd_barang = $r['kd_barang'];

			run_query_or_fail_aksi_trbmasuk($conn, "UPDATE barang SET stok_barang = (stok_barang - $qty_dtrbmasuk) WHERE id_barang = '$id_barang'");
			run_query_or_fail_aksi_trbmasuk($conn, "DELETE FROM batch WHERE kd_transaksi = '$kd_trbmasuk' AND no_batch='$no_batch' AND kd_barang='$kd_barang' AND status = 'masuk'");
			run_query_or_fail_aksi_trbmasuk($conn, "DELETE FROM trbmasuk_detail WHERE id_dtrbmasuk = '$id_dtrbmasuk'");
		}

		run_query_or_fail_aksi_trbmasuk($conn, "DELETE FROM trbmasuk WHERE id_trbmasuk = '$id_trbmasuk'");
		run_query_or_fail_aksi_trbmasuk($conn, "DELETE FROM kartu_stok WHERE kode_transaksi = '$kd_trbmasuk'");
		mysqli_commit($conn);
		echo "<script type='text/javascript'>alert('Data berhasil dihapus !');window.location='../../media_admin.php?module=".$module."'</script>";
	} catch (Exception $e) {
		mysqli_rollback($conn);
		write_aksi_trbmasuk_log(
			$logFile,
			'Hapus trbmasuk gagal | id_trbmasuk=' . $id_trbmasuk .
			' | kd_trbmasuk=' . (isset($kd_trbmasuk) ? $kd_trbmasuk : '-') .
			' | detail=' . $e->getMessage()
		);
		echo "<script type='text/javascript'>alert('Gagal menghapus data !');window.location='../../media_admin.php?module=".$module."'</script>";
	}
}

}
?>
