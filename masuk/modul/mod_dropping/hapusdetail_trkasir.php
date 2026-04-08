<?php
session_start();
include "../../../configurasi/koneksi.php";

$conn = $GLOBALS["___mysqli_ston"];
$conn2 = $GLOBALS["___mysqli_ston2"];
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

function delete_dropping_from_yasfi2_hapusdetail($connTarget, $kdTrdropping)
{
	if ($kdTrdropping == '') {
		return;
	}

	$kdTrdroppingTarget = mysqli_real_escape_string($connTarget, $kdTrdropping);
	run_query_or_fail_hapusdetail_trkasir($connTarget, "DELETE FROM dropping_detail WHERE kd_trbmasuk = '$kdTrdroppingTarget'");
	run_query_or_fail_hapusdetail_trkasir($connTarget, "DELETE FROM trbmasuk_detail WHERE kd_trbmasuk = '$kdTrdroppingTarget'");
	run_query_or_fail_hapusdetail_trkasir($connTarget, "DELETE FROM kartu_stok WHERE kode_transaksi = '$kdTrdroppingTarget'");
	run_query_or_fail_hapusdetail_trkasir($connTarget, "DELETE FROM dropping WHERE kd_trbmasuk = '$kdTrdroppingTarget'");
	run_query_or_fail_hapusdetail_trkasir($connTarget, "DELETE FROM trbmasuk WHERE kd_trbmasuk = '$kdTrdroppingTarget'");
}

$id_dtrkasir = isset($_POST['id_dtrkasir']) ? $_POST['id_dtrkasir'] : '';

if ($id_dtrkasir == '') {
	http_response_code(400);
	echo 'ID detail tidak valid';
	exit;
}

$useConn2 = false;
mysqli_begin_transaction($conn);

try {
	$id_dtrkasir_safe = mysqli_real_escape_string($conn, $id_dtrkasir);
	$ambildata = run_query_or_fail_hapusdetail_trkasir($conn, "SELECT * FROM dropping_detail WHERE id_dtrkasir='$id_dtrkasir_safe' LIMIT 1");
	$r = mysqli_fetch_array($ambildata);
	if (!$r) {
		mysqli_commit($conn);
		echo '';
		exit;
	}

	$id_barang = $r['id_barang'];
	$qty_dtrkasir = (float) $r['qty_dtrkasir'];
	$kd_trkasir = $r['kd_trkasir'];
	$kd_trkasir_safe = mysqli_real_escape_string($conn, $kd_trkasir);
	$no_batch = mysqli_real_escape_string($conn, $r['no_batch']);
	$kd_barang = mysqli_real_escape_string($conn, $r['kd_barang']);

	run_query_or_fail_hapusdetail_trkasir($conn, "UPDATE barang SET stok_barang = (stok_barang + $qty_dtrkasir) WHERE id_barang = '$id_barang'");
	run_query_or_fail_hapusdetail_trkasir($conn, "DELETE FROM dropping_detail WHERE id_dtrkasir = '$id_dtrkasir_safe'");
	run_query_or_fail_hapusdetail_trkasir($conn, "DELETE FROM komisi_pegawai WHERE id_dtrkasir = '$id_dtrkasir_safe'");
	run_query_or_fail_hapusdetail_trkasir($conn, "DELETE FROM batch WHERE kd_transaksi = '$kd_trkasir_safe' AND no_batch='$no_batch' AND kd_barang='$kd_barang' AND status = 'keluar'");

	$sumDetail = run_query_or_fail_hapusdetail_trkasir($conn, "SELECT COALESCE(SUM(hrgttl_dtrkasir), 0) AS total, COUNT(*) AS jumlah FROM dropping_detail WHERE kd_trkasir = '$kd_trkasir_safe'");
	$sumRow = mysqli_fetch_assoc($sumDetail);
	$sisaDetail = (int) $sumRow['jumlah'];
	$totalBaru = (float) $sumRow['total'];

	if ($sisaDetail > 0) {
		run_query_or_fail_hapusdetail_trkasir($conn, "UPDATE dropping SET ttl_trkasir = '$totalBaru' WHERE kd_trkasir = '$kd_trkasir_safe'");
	} else {
		$cekTrdropping = run_query_or_fail_hapusdetail_trkasir($conn, "SELECT kd_trdropping FROM trdropping WHERE kd_trkasir = '$kd_trkasir_safe' LIMIT 1");
		$syncRow = mysqli_fetch_assoc($cekTrdropping);

		if ($syncRow && !empty($syncRow['kd_trdropping'])) {
			$kd_trdropping = $syncRow['kd_trdropping'];
			$kd_trdropping_safe = mysqli_real_escape_string($conn, $kd_trdropping);
			mysqli_begin_transaction($conn2);
			$useConn2 = true;
			delete_dropping_from_yasfi2_hapusdetail($conn2, $kd_trdropping);
			run_query_or_fail_hapusdetail_trkasir($conn, "DELETE FROM trdropping WHERE kd_trdropping = '$kd_trdropping_safe' OR kd_trkasir = '$kd_trkasir_safe'");
		} else {
			run_query_or_fail_hapusdetail_trkasir($conn, "DELETE FROM trdropping WHERE kd_trkasir = '$kd_trkasir_safe'");
		}

		run_query_or_fail_hapusdetail_trkasir($conn, "DELETE FROM dropping WHERE kd_trkasir = '$kd_trkasir_safe'");
		run_query_or_fail_hapusdetail_trkasir($conn, "DELETE FROM kartu_stok WHERE kode_transaksi = '$kd_trkasir_safe'");
	}

	$stokResult = run_query_or_fail_hapusdetail_trkasir($conn, "SELECT stok_barang FROM barang WHERE id_barang = '$id_barang' LIMIT 1");
	$stokRow = mysqli_fetch_assoc($stokResult);

	mysqli_commit($conn);
	if ($useConn2) {
		mysqli_commit($conn2);
	}

	echo $stokRow ? $stokRow['stok_barang'] : '';
} catch (Exception $e) {
	mysqli_rollback($conn);
	if ($useConn2) {
		mysqli_rollback($conn2);
	}
	write_hapusdetail_trkasir_log(
		$logFile,
		'Hapus detail kasir gagal | id_dtrkasir=' . $id_dtrkasir . ' | detail=' . $e->getMessage()
	);
	http_response_code(500);
	echo 'Gagal menghapus detail kasir';
}

?>
