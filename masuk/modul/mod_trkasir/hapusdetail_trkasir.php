<?php
session_start();
include "../../../configurasi/koneksi.php";

$conn = $GLOBALS["___mysqli_ston"];
$logFile = __DIR__ . '/hapusdetail_trkasir_error.log';

function write_hapusdetail_trkasir_log($logFile, $message)
{
	$timestamp = date('Y-m-d H:i:s');
	error_log("[$timestamp] $message" . PHP_EOL, 3, $logFile);
}

function run_query_or_fail_hapusdetail_trkasir($conn, $sql)
{
	$result = mysqli_query($conn, $sql);
	if ($result === false) {
		throw new Exception('SQL Error: ' . mysqli_error($conn) . ' | Query: ' . $sql);
	}

	return $result;
}

$id_dtrkasir = $_POST['id_dtrkasir'];

mysqli_begin_transaction($conn);

try {
	$ambildata = run_query_or_fail_hapusdetail_trkasir($conn, "SELECT * FROM trkasir_detail WHERE id_dtrkasir='$id_dtrkasir'");
	$r = mysqli_fetch_array($ambildata);
	if (!$r) {
		throw new Exception('Data detail kasir tidak ditemukan');
	}

	$id_barang = $r['id_barang'];
	$qty_dtrkasir = (float) $r['qty_dtrkasir'];
	$kd_trkasir = $r['kd_trkasir'];
	$no_batch = $r['no_batch'];
	$kd_barang = $r['kd_barang'];

	run_query_or_fail_hapusdetail_trkasir($conn, "UPDATE barang SET stok_barang = (stok_barang + $qty_dtrkasir) WHERE id_barang = '$id_barang'");
	run_query_or_fail_hapusdetail_trkasir($conn, "DELETE FROM trkasir_detail WHERE id_dtrkasir = '$id_dtrkasir'");
	run_query_or_fail_hapusdetail_trkasir($conn, "DELETE FROM komisi_pegawai WHERE id_dtrkasir = '$id_dtrkasir'");
	run_query_or_fail_hapusdetail_trkasir($conn, "DELETE FROM batch WHERE kd_transaksi = '$kd_trkasir' AND no_batch='$no_batch' AND kd_barang='$kd_barang' AND status = 'keluar'");

    
	mysqli_commit($conn);
} catch (Exception $e) {
	mysqli_rollback($conn);
	write_hapusdetail_trkasir_log(
		$logFile,
		'Hapus detail kasir gagal | id_dtrkasir=' . $id_dtrkasir . ' | detail=' . $e->getMessage()
	);
	http_response_code(500);
	echo 'Gagal menghapus detail kasir';
}


?>
