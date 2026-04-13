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

function escape_sql_value_hapusdetail_trkasir($conn, $value)
{
	if ($value === null) {
		return "NULL";
	}

	return "'" . mysqli_real_escape_string($conn, (string) $value) . "'";
}

function backup_deleted_detail_to_hist($conn, $detailRow, $deletedById, $deletedByUsername, $deletedByName, $deletedAt)
{
	$cekHistTable = run_query_or_fail_hapusdetail_trkasir($conn, "SHOW TABLES LIKE 'trkasir_detail_hist'");
	if (mysqli_num_rows($cekHistTable) < 1) {
		throw new Exception("Tabel trkasir_detail_hist tidak ditemukan");
	}

	$histColumnsRes = run_query_or_fail_hapusdetail_trkasir($conn, "SHOW COLUMNS FROM trkasir_detail_hist");
	$histColumns = array();
	while ($col = mysqli_fetch_assoc($histColumnsRes)) {
		$histColumns[] = $col['Field'];
	}

	$insertData = array();
	foreach ($detailRow as $key => $value) {
		if (in_array($key, $histColumns, true)) {
			$insertData[$key] = $value;
		}
	}

	if (in_array('deleted_by_id', $histColumns, true)) {
		$insertData['deleted_by_id'] = $deletedById;
	}
	if (in_array('deleted_by_username', $histColumns, true)) {
		$insertData['deleted_by_username'] = $deletedByUsername;
	}
	if (in_array('deleted_by_name', $histColumns, true)) {
		$insertData['deleted_by_name'] = $deletedByName;
	}
	if (in_array('deleted_at', $histColumns, true)) {
		$insertData['deleted_at'] = $deletedAt;
	}
	if (in_array('delete_source', $histColumns, true)) {
		$insertData['delete_source'] = 'hapusdetail_trkasir.php';
	}

	if (count($insertData) < 1) {
		throw new Exception("Tidak ada kolom yang cocok untuk backup ke trkasir_detail_hist");
	}

	$columns = array();
	$values = array();
	foreach ($insertData as $col => $val) {
		$columns[] = "`$col`";
		$values[] = escape_sql_value_hapusdetail_trkasir($conn, $val);
	}

	$sqlInsertHist = "INSERT INTO trkasir_detail_hist (" . implode(',', $columns) . ") VALUES (" . implode(',', $values) . ")";
	run_query_or_fail_hapusdetail_trkasir($conn, $sqlInsertHist);
}

$id_dtrkasir = isset($_POST['id_dtrkasir']) ? (int) $_POST['id_dtrkasir'] : 0;
$deletedById = isset($_SESSION['idadmin']) ? (int) $_SESSION['idadmin'] : null;
$deletedByUsername = isset($_SESSION['username']) ? $_SESSION['username'] : '';
$deletedByName = isset($_SESSION['namalengkap']) ? $_SESSION['namalengkap'] : '';
$deletedAt = date('Y-m-d H:i:s');

if ($id_dtrkasir < 1) {
	http_response_code(400);
	echo 'Parameter id_dtrkasir tidak valid';
	exit;
}

mysqli_begin_transaction($conn);

try {
	$ambildata = run_query_or_fail_hapusdetail_trkasir($conn, "SELECT * FROM trkasir_detail WHERE id_dtrkasir='$id_dtrkasir' LIMIT 1");
	$r = mysqli_fetch_array($ambildata);
	if (!$r) {
		throw new Exception('Data detail kasir tidak ditemukan');
	}

	backup_deleted_detail_to_hist($conn, $r, $deletedById, $deletedByUsername, $deletedByName, $deletedAt);

	$id_barang = $r['id_barang'];
	$qty_dtrkasir = (float) $r['qty_dtrkasir'];
	$kd_trkasir = $r['kd_trkasir'];
	$no_batch = isset($r['no_batch']) ? $r['no_batch'] : '';
	$kd_barang = $r['kd_barang'];

	run_query_or_fail_hapusdetail_trkasir($conn, "UPDATE barang SET stok_barang = (stok_barang + $qty_dtrkasir) WHERE id_barang = '$id_barang'");
	run_query_or_fail_hapusdetail_trkasir($conn, "DELETE FROM trkasir_detail WHERE id_dtrkasir = '$id_dtrkasir'");
	run_query_or_fail_hapusdetail_trkasir($conn, "DELETE FROM komisi_pegawai WHERE id_dtrkasir = '$id_dtrkasir'");
	run_query_or_fail_hapusdetail_trkasir($conn, "DELETE FROM batch WHERE kd_transaksi = '$kd_trkasir' AND no_batch='$no_batch' AND kd_barang='$kd_barang' AND status = 'keluar'");

	$stokResult = run_query_or_fail_hapusdetail_trkasir($conn, "SELECT stok_barang FROM barang WHERE id_barang = '$id_barang' LIMIT 1");
	$stokRow = mysqli_fetch_assoc($stokResult);
	$stokTerbaru = $stokRow ? $stokRow['stok_barang'] : '';

    
	mysqli_commit($conn);
	echo $stokTerbaru;
} catch (Exception $e) {
	mysqli_rollback($conn);
	write_hapusdetail_trkasir_log(
		$logFile,
		'Hapus detail kasir gagal | id_dtrkasir=' . $id_dtrkasir . ' | user=' . $deletedByUsername . ' | detail=' . $e->getMessage()
	);
	http_response_code(500);
	echo 'Gagal menghapus detail kasir';
}


?>
