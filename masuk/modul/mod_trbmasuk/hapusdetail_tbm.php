<?php
include "../../../configurasi/koneksi.php";

$conn = $GLOBALS["___mysqli_ston"];
$logFile = __DIR__ . '/hapusdetail_tbm_error.log';

function write_hapusdetail_tbm_log($logFile, $message)
{
    $timestamp = date('Y-m-d H:i:s');
    error_log("[$timestamp] $message" . PHP_EOL, 3, $logFile);
}

function run_query_or_fail_hapusdetail_tbm($conn, $sql)
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
    $ambildata = run_query_or_fail_hapusdetail_tbm($conn, "SELECT * FROM trbmasuk_detail WHERE id_dtrbmasuk='$id_dtrbmasuk'");
    $r = mysqli_fetch_array($ambildata);
    if (!$r) {
        throw new Exception('Data detail pembelian tidak ditemukan');
    }

    $id_barang = $r['id_barang'];
    $qty_dtrbmasuk = (float) $r['qty_dtrbmasuk'];
    $kd_trbmasuk = $r['kd_trbmasuk'];
    $no_batch = $r['no_batch'];
    $kd_barang = $r['kd_barang'];

    run_query_or_fail_hapusdetail_tbm($conn, "UPDATE barang SET stok_barang = (stok_barang - $qty_dtrbmasuk) WHERE id_barang = '$id_barang'");

    run_query_or_fail_hapusdetail_tbm($conn, "INSERT INTO trbmasuk_detail_hist (
                                                    kd_trbmasuk,
                                                    kd_orders,
                                                    id_barang,
                                                    kd_barang,
                                                    nmbrg_dtrbmasuk,
                                                    qty_dtrbmasuk,
                                                    sat_dtrbmasuk,
                                                    qty_grosir,
                                                    satgrosir_dtrbmasuk,
                                                    hnasat_dtrbmasuk,
                                                    diskon,
                                                    konversi,
                                                    hrgsat_dtrbmasuk,
                                                    hrgjual_dtrbmasuk,
                                                    hrgttl_dtrbmasuk,
                                                    no_batch,
                                                    exp_date,
                                                    waktu,
                                                    tipe
                                                    )
                                                VALUES (
                                                    '$r[kd_trbmasuk]',
                                                    '$r[kd_orders]',
                                                    '$r[id_barang]',
                                                    '$r[kd_barang]',
                                                    '$r[nmbrg_dtrbmasuk]',
                                                    '$r[qty_dtrbmasuk]',
                                                    '$r[sat_dtrbmasuk]',
                                                    '$r[qty_grosir]',
                                                    '$r[satgrosir_dtrbmasuk]',
                                                    '$r[hnasat_dtrbmasuk]',
                                                    '$r[diskon]',
                                                    '$r[konversi]',
                                                    '$r[hrgsat_dtrbmasuk]',
                                                    '$r[hrgjual_dtrbmasuk]',
                                                    '$r[hrgttl_dtrbmasuk]',
                                                    '$r[no_batch]',
                                                    '$r[exp_date]',
                                                    '$r[waktu]',
                                                    '$r[tipe]'
                                                    )");

    run_query_or_fail_hapusdetail_tbm($conn, "DELETE FROM trbmasuk_detail WHERE id_dtrbmasuk = '$id_dtrbmasuk'");
    run_query_or_fail_hapusdetail_tbm($conn, "DELETE FROM batch WHERE kd_transaksi = '$kd_trbmasuk' AND no_batch='$no_batch' AND kd_barang='$kd_barang' AND status = 'masuk'");

    mysqli_commit($conn);
} catch (Exception $e) {
    mysqli_rollback($conn);
    write_hapusdetail_tbm_log(
        $logFile,
        'Hapus detail TBM non-PBF gagal | id_dtrbmasuk=' . $id_dtrbmasuk . ' | detail=' . $e->getMessage()
    );
    http_response_code(500);
    echo 'Gagal menghapus detail pembelian';
}

?>
