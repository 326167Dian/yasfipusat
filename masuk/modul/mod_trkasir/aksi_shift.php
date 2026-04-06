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
$logFile = __DIR__ . '/aksi_shift_error.log';

function write_aksi_shift_log($logFile, $message)
{
	$timestamp = date('Y-m-d H:i:s');
	error_log("[$timestamp] $message" . PHP_EOL, 3, $logFile);
}

function run_query_or_fail_aksi_shift($conn, $sql)
{
	$result = mysqli_query($conn, $sql);
	if ($result === false) {
		throw new Exception('SQL Error: ' . mysqli_error($conn) . ' | Query: ' . $sql);
	}

	return $result;
}

$module= "trkasir";
$stt_aksi=$_POST['stt_aksi'];
if($stt_aksi == "buka_shift" || $stt_aksi == "tutup_shift"){
$act=$stt_aksi;
}else{
$act=$_GET['act'];
}

// Input admin
if ($module=='trkasir' AND $act=='buka_shift'){


    mysqli_query($GLOBALS["___mysqli_ston"], "INSERT INTO waktukerja(
										id_shift,
										petugas,
										jamkerja,
										peticash)
								 VALUES('$_POST[id_shift]',
								 		'$_POST[petugas]',
										'$_POST[jamkerja]',										
										'$_POST[peticash]')");
										
	mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE waktukerja SET status = 'ON'");
																			
	//echo "<script type='text/javascript'>alert('Transkasi berhasil ditambahkan !');window.location='../../media_admin.php?module=".$module."'</script>";
}

 //updata trkasir
 elseif ($module=='trkasir' AND $act=='ubah_trkasir'){

    mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE trkasir SET tgl_trkasir = '$_POST[tgl_trkasir]',
									petugas = '$_POST[petugas]',
									nm_pelanggan = '$_POST[nm_pelanggan]',									
									tlp_pelanggan = '$_POST[tlp_pelanggan]',
									alamat_pelanggan = '$_POST[alamat_pelanggan]',
									ttl_trkasir = '$_POST[ttl_trkasir]',
									diskon2 = '$_POST[diskon2]',
									dp_bayar = '$_POST[dp_bayar]',
									sisa_bayar = '$_POST[sisa_bayar]',
									ket_trkasir = '$_POST[ket_trkasir]',
									id_carabayar = '$_POST[id_carabayar]'
									WHERE id_trkasir = '$_POST[id_trkasir]'");
										
										
	//echo "<script type='text/javascript'>alert('Transkasi berhasil Ubah !');window.location='../../media_admin.php?module=".$module."'</script>";
	
 
}
//Hapus Proyek
elseif ($module=='trkasir' AND $act=='hapus'){
	$id_trkasir = $_GET['id'];
	mysqli_begin_transaction($conn);

	try {
		$ambildatainduk = run_query_or_fail_aksi_shift($conn, "SELECT id_trkasir, kd_trkasir FROM trkasir WHERE id_trkasir='$id_trkasir'");
		$r1 = mysqli_fetch_array($ambildatainduk);
		if (!$r1) {
			throw new Exception('Data trkasir tidak ditemukan');
		}
		$kd_trkasir = $r1['kd_trkasir'];

		$ambildatadetail = run_query_or_fail_aksi_shift($conn, "SELECT id_dtrkasir, kd_trkasir, id_barang, kd_barang, no_batch, qty_dtrkasir FROM trkasir_detail WHERE kd_trkasir='$kd_trkasir'");
		while ($r = mysqli_fetch_array($ambildatadetail)) {
			$id_dtrkasir = $r['id_dtrkasir'];
			$id_barang = $r['id_barang'];
			$kd_barang = $r['kd_barang'];
			$no_batch = $r['no_batch'];
			$qty_dtrkasir = (float) $r['qty_dtrkasir'];

			run_query_or_fail_aksi_shift($conn, "UPDATE barang SET stok_barang = (stok_barang + $qty_dtrkasir) WHERE id_barang = '$id_barang'");
			run_query_or_fail_aksi_shift($conn, "DELETE FROM trkasir_detail WHERE id_dtrkasir = '$id_dtrkasir'");
			run_query_or_fail_aksi_shift($conn, "DELETE FROM batch WHERE kd_transaksi = '$kd_trkasir' AND no_batch='$no_batch' AND kd_barang='$kd_barang' AND status = 'keluar'");
		}

		run_query_or_fail_aksi_shift($conn, "DELETE FROM trkasir WHERE id_trkasir = '$id_trkasir'");
		mysqli_commit($conn);
		echo "<script type='text/javascript'>alert('Data berhasil dihapus !');window.location='../../media_admin.php?module=".$module."'</script>";
	} catch (Exception $e) {
		mysqli_rollback($conn);
		write_aksi_shift_log(
			$logFile,
			'Hapus via aksi_shift gagal | id_trkasir=' . $id_trkasir .
			' | kd_trkasir=' . (isset($kd_trkasir) ? $kd_trkasir : '-') .
			' | detail=' . $e->getMessage()
		);
		echo "<script type='text/javascript'>alert('Gagal menghapus data !');window.location='../../media_admin.php?module=".$module."'</script>";
	}
}

}
?>
