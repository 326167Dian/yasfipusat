<?php
include "../../../configurasi/koneksi.php";

$conn = $GLOBALS["___mysqli_ston"];
$logFile = __DIR__ . '/simpandetail_tbm_error.log';

function write_tbm_nonpbf_log($logFile, $message)
{
	$timestamp = date('Y-m-d H:i:s');
	error_log("[$timestamp] $message" . PHP_EOL, 3, $logFile);
}

function run_query_or_fail_tbm_nonpbf($conn, $sql)
{
	$result = mysqli_query($conn, $sql);
	if ($result === false) {
		throw new Exception('SQL Error: ' . mysqli_error($conn) . ' | Query: ' . $sql);
	}

	return $result;
}

$kd_trbmasuk = $_POST['kd_trbmasuk'];
$id_barang = $_POST['id_barang'];
$kd_barang = $_POST['kd_barang'];
$nmbrg_dtrbmasuk = $_POST['nmbrg_dtrbmasuk'];
$qty_dtrbmasuk = isset($_POST['qty_dtrbmasuk']) ? (float) str_replace(',', '.', $_POST['qty_dtrbmasuk']) : 1;
$sat_dtrbmasuk = $_POST['sat_dtrbmasuk'];
$hrgsat_dtrbmasuk = isset($_POST['hrgsat_dtrbmasuk']) ? round((float) str_replace(',', '.', $_POST['hrgsat_dtrbmasuk']), 0) : 0;
$hrgjual_dtrbmasuk = isset($_POST['hrgjual_dtrbmasuk']) ? round((float) str_replace(',', '.', $_POST['hrgjual_dtrbmasuk']), 0) : 0;

$no_batch = $_POST['no_batch'];
$exp_date = date('Y-m-d', strtotime($_POST['exp_date']));
$datetime = date('Y-m-d H:i:s', time());

if ($_POST['exp_date'] == '') {
	$tgl_awal = date('Y-m-d');
	$exp_date = date('Y-m-d', strtotime('+720 days', strtotime($tgl_awal)));
} else {
	$exp_date = $_POST['exp_date'];
}

if ($qty_dtrbmasuk <= 0) {
	$qty_dtrbmasuk = 1;
}

mysqli_begin_transaction($conn);

try {

//cek apakah barang sudah ada
$cekdetail = run_query_or_fail_tbm_nonpbf($conn, "SELECT * FROM trbmasuk_detail 
	WHERE kd_barang='$kd_barang' 
                AND kd_trbmasuk='$kd_trbmasuk'
                AND no_batch='$no_batch'");

$ketemucekdetail = mysqli_num_rows($cekdetail);
$rcek = mysqli_fetch_array($cekdetail);
if ($ketemucekdetail > 0) {

	$id_dtrbmasuk = $rcek['id_dtrbmasuk'];
	$qtylama = $rcek['qty_dtrbmasuk'];
	$ttlqty = $qtylama + $qty_dtrbmasuk;
	$ttlharga = $ttlqty * $hrgsat_dtrbmasuk;

	run_query_or_fail_tbm_nonpbf($conn, "UPDATE trbmasuk_detail SET qty_dtrbmasuk = '$ttlqty',
										hrgsat_dtrbmasuk = '$hrgsat_dtrbmasuk',
										hrgjual_dtrbmasuk = '$hrgjual_dtrbmasuk',
										hrgttl_dtrbmasuk = '$ttlharga',
										no_batch = '$no_batch',
										exp_date = '$exp_date'
										WHERE id_dtrbmasuk = '$id_dtrbmasuk'");

	//update stok
	$hrgsat_dtrbmasuk = round($hrgsat_dtrbmasuk);

	run_query_or_fail_tbm_nonpbf($conn, "UPDATE barang SET 
		stok_barang = (stok_barang + $qty_dtrbmasuk),
		sat_barang = '$sat_dtrbmasuk',
        hrgsat_barang = '$hrgsat_dtrbmasuk'
		WHERE id_barang = '$id_barang'");

	//cek apakah barang dengan no batch yang dimaksud sudah ada
	$cekbatchdetail = run_query_or_fail_tbm_nonpbf($conn, "SELECT no_batch, kd_transaksi,qty
                                                                FROM batch 
                                                                WHERE no_batch = '$no_batch' 
                                                                AND kd_transaksi = '$kd_trbmasuk' 
                                                                AND kd_barang = '$kd_barang'
                                                                AND status = 'masuk'");
	$ketemucekbatchdetail = mysqli_num_rows($cekbatchdetail);

	if ($ketemucekbatchdetail > 0) {
		//tarikstok dari batch
		$tampung = mysqli_fetch_array($cekbatchdetail);
		$qtybatchlama = $tampung['qty'];
		$qtybatchbaru = $qtybatchlama + $qty_dtrbmasuk;

		run_query_or_fail_tbm_nonpbf($conn, "UPDATE batch SET qty = '$qtybatchbaru' 
                    WHERE kd_transaksi = '$kd_trbmasuk' 
                          AND no_batch = '$no_batch'
                          AND kd_barang = '$kd_barang'
                          AND status = 'masuk'");
	}
	else {
		//masukkan data ke tabel batch
		run_query_or_fail_tbm_nonpbf($conn, "INSERT INTO batch(
										tgl_transaksi,
										no_batch,
										exp_date,
										qty,
										satuan,
										kd_transaksi,										
										kd_barang,
										status
										)
								  VALUES('$datetime',
								        '$no_batch',
										'$exp_date',
										'$qty_dtrbmasuk',
										'$sat_dtrbmasuk',
										'$kd_trbmasuk',
										'$kd_barang',
										'masuk'
										)");
	}			
} 

	else {

	$ttlharga = $qty_dtrbmasuk * $hrgsat_dtrbmasuk;

	run_query_or_fail_tbm_nonpbf($conn, "INSERT INTO trbmasuk_detail(kd_trbmasuk,
										id_barang,
										kd_barang,
										nmbrg_dtrbmasuk,
										qty_dtrbmasuk,
										sat_dtrbmasuk,
										hrgsat_dtrbmasuk,
										hrgjual_dtrbmasuk,
										hrgttl_dtrbmasuk,
										no_batch,
										exp_date)
								  VALUES('$kd_trbmasuk',
										'$id_barang',
										'$kd_barang',
										'$nmbrg_dtrbmasuk',
										'$qty_dtrbmasuk',
										'$sat_dtrbmasuk',
										'$hrgsat_dtrbmasuk',
										'$hrgjual_dtrbmasuk',
										'$ttlharga',
										'$no_batch',
										'$exp_date')");

	//update stok
	$hrgjual_barang = round($hrgjual_dtrbmasuk);
	$hrgjual_barang1=round($hrgjual_barang*1.05) ;
    $hrgjual_barang3=round((($hrgsat_dtrbmasuk*1.245)+1100),0) ;


	run_query_or_fail_tbm_nonpbf($conn, "UPDATE barang SET 
		stok_barang = (stok_barang + $qty_dtrbmasuk),
		sat_barang = '$sat_dtrbmasuk',
        hrgsat_barang = '$hrgsat_dtrbmasuk',
        hrgjual_barang='$hrgjual_barang',
        hrgjual_barang1 = '$hrgjual_barang1',
        hrgjual_barang3 = '$hrgjual_barang3'
		WHERE id_barang = '$id_barang'");

	//input ke tabel batch

	run_query_or_fail_tbm_nonpbf($conn, "INSERT INTO batch(
	                                    tgl_transaksi,
                                        no_batch,
                                        exp_date,
                                        qty,
                                        satuan,
                                        kd_transaksi,										
										kd_barang,
										status
										)
								  VALUES('$datetime',
								        '$no_batch',
										'$exp_date',
										'$qty_dtrbmasuk',
										'$sat_dtrbmasuk',
										'$kd_trbmasuk',
										'$kd_barang',
										'masuk'
										)");
}

mysqli_commit($conn);
} catch (Exception $e) {
	mysqli_rollback($conn);
	write_tbm_nonpbf_log(
		$logFile,
		'Simpan detail TBM non-PBF gagal | kd_trbmasuk=' . $kd_trbmasuk .
		' | id_barang=' . $id_barang .
		' | kd_barang=' . $kd_barang .
		' | qty=' . $qty_dtrbmasuk .
		' | no_batch=' . $no_batch .
		' | detail=' . $e->getMessage()
	);
	http_response_code(500);
	echo 'Gagal menyimpan detail pembelian';
}
