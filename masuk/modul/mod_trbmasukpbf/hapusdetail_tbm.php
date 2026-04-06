<?php
include "../../../configurasi/koneksi.php";

$conn = $GLOBALS["___mysqli_ston"];
$logFile = __DIR__ . '/hapusdetail_tbm_error.log';

function write_hapus_tbm_log($logFile, $message)
{
	$timestamp = date('Y-m-d H:i:s');
	error_log("[$timestamp] $message" . PHP_EOL, 3, $logFile);
}

function run_query_or_fail_hapus_tbm($conn, $sql)
{
	$result = mysqli_query($conn, $sql);
	if ($result === false) {
		throw new Exception('SQL Error: ' . mysqli_error($conn) . ' | Query: ' . $sql);
	}

	return $result;
}

$id_dtrbmasuk = $_POST['id_dtrbmasuk'];

mysqli_begin_transaction($conn);

try {
	$ambildata = run_query_or_fail_hapus_tbm($conn, "SELECT * FROM trbmasuk_detail WHERE id_dtrbmasuk='$id_dtrbmasuk'");
	$r = mysqli_fetch_array($ambildata);

	if (!$r) {
		throw new Exception('Data detail pembelian tidak ditemukan');
	}

	$id_barang = $r['id_barang'];
	$qty_dtrbmasuk = (float) $r['qty_dtrbmasuk'];
	$kd_trbmasuk = $r['kd_trbmasuk'];
	$no_batch = $r['no_batch'];
	$kd_barang = $r['kd_barang'];

	run_query_or_fail_hapus_tbm($conn, "UPDATE barang SET stok_barang = (stok_barang - $qty_dtrbmasuk) WHERE id_barang = '$id_barang'");

	run_query_or_fail_hapus_tbm($conn, "DELETE FROM trbmasuk_detail WHERE id_dtrbmasuk = '$id_dtrbmasuk'");
	run_query_or_fail_hapus_tbm($conn, "DELETE FROM batch WHERE kd_transaksi = '$kd_trbmasuk' AND no_batch='$no_batch' AND kd_barang='$kd_barang' AND status = 'masuk'");

	mysqli_commit($conn);
} catch (Exception $e) {
	mysqli_rollback($conn);
	write_hapus_tbm_log(
		$logFile,
		'Hapus detail TBM gagal | id_dtrbmasuk=' . $id_dtrbmasuk . ' | detail=' . $e->getMessage()
	);
	http_response_code(500);
	echo 'Gagal menghapus detail pembelian';
}
?>
