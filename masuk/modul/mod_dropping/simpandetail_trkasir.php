<?php
session_start();
include "../../../configurasi/koneksi.php";

$conn = $GLOBALS["___mysqli_ston"];

$logFile = __DIR__ . '/simpandetail_trkasir_error.log';

function write_trkasir_log($logFile, $message)
{
    $timestamp = date('Y-m-d H:i:s');
    error_log("[$timestamp] $message" . PHP_EOL, 3, $logFile);
}

function run_query_or_fail($conn, $sql)
{
    $result = mysqli_query($conn, $sql);
    if ($result === false) {
        throw new Exception('SQL Error: ' . mysqli_error($conn) . ' | Query: ' . $sql);
    }

    return $result;
}

$kd_trkasir = $_POST['kd_trkasir'];
$id_dtrkasir = $_POST['id_dtrkasir'];
$id_barang = $_POST['id_barang'];
$kd_barang = $_POST['kd_barang'];
$nmbrg_dtrkasir = $_POST['nmbrg_dtrkasir'];
$qty_dtrkasir = isset($_POST['qty_dtrkasir']) ? (float) str_replace(',', '.', $_POST['qty_dtrkasir']) : 1;
$sat_dtrkasir = $_POST['sat_dtrkasir'];
$hrgjual_dtrkasir = $_POST['hrgjual_dtrkasir'];
$indikasi = $_POST['indikasi'];
if ($_SESSION['komisi'] == 'Y') {
    $tarik = $db->query("select komisi from barang where id_barang='$id_barang' ");
    $kom = $tarik->fetch_array();

    $komisi = $kom['komisi'] * $qty_dtrkasir;
} elseif ($_SESSION['komisi'] == 'N') {
    $komisi = 0;
}
$currentdate = date('Y-m-d', time());
$id_admin = $_POST['id_admin'];

$disc = $_POST['disc'];
$no_batch = $_POST['no_batch'];
$exp_date = $_POST['exp_date'];
$hrgdisc = $hrgjual_dtrkasir * (1 - ($disc / 100));
$tipe = $_POST['tipe'];

$trdroping = $_POST['trdroping'];

if ($qty_dtrkasir <= 0) {
    $qty_dtrkasir = 1;
}

mysqli_begin_transaction($conn);

try {
if ($id_dtrkasir == "" || $id_dtrkasir == null) {

    //cek apakah barang sudah ada
    $cekdetail = run_query_or_fail($conn, "SELECT id_barang, kd_barang, kd_trkasir, id_dtrkasir, qty_dtrkasir 
    FROM dropping_detail WHERE kd_barang='$kd_barang' AND kd_trkasir='$kd_trkasir' and no_batch='$no_batch'");

    $ketemucekdetail = mysqli_num_rows($cekdetail);
    $rcek = mysqli_fetch_array($cekdetail);
    if ($ketemucekdetail > 0) {

        $id_dtrkasir = $rcek['id_dtrkasir'];
        $qtylama = $rcek['qty_dtrkasir'];
        $ttlqty = $qtylama + $qty_dtrkasir;
        $ttlharga = $ttlqty * $hrgdisc;

        run_query_or_fail($conn, "UPDATE dropping_detail SET qty_dtrkasir = '$ttlqty',
										hrgjual_dtrkasir = '$hrgjual_dtrkasir',
										hrgttl_dtrkasir = '$ttlharga',
                                        komisi = '$komisi'
										WHERE id_dtrkasir = '$id_dtrkasir' and kd_barang='$kd_barang'");
        //update batch
        //cari qty batch lama
          $cariqtybatch = run_query_or_fail($conn, "SELECT qty FROM batch 
        WHERE kd_transaksi = '$kd_trkasir' 
              AND no_batch = '$no_batch'
              AND kd_barang = '$kd_barang'
              AND status = 'keluar'");
        $ketemucariqtybatch = mysqli_fetch_array($cariqtybatch);
        $qtybatchlama = $ketemucariqtybatch['qty']; 
        $qtybatchbaru = $qtybatchlama + $qty_dtrkasir;
        
          run_query_or_fail($conn, "UPDATE batch SET qty = '$qtybatchbaru' 
                    WHERE kd_transaksi = '$kd_trkasir' 
                          AND no_batch = '$no_batch'
                          AND status = 'keluar'");

        //update stok
        //cek tambah stok
        $tambahstok = run_query_or_fail($conn, "select id_dtrkasir, kd_trkasir, qty_dtrkasir 
        from dropping_detail where kd_trkasir='$kd_trkasir' and kd_barang='$kd_barang'");
        $ketemutambahstok = mysqli_fetch_array($tambahstok);
        // if($angka==$ttlqty) {
        run_query_or_fail($conn, "UPDATE barang SET 
            stok_barang = (stok_barang - $qty_dtrkasir)
            WHERE id_barang = '$id_barang' and kd_barang='$kd_barang'");
        //                  }
        // else{}

        if ($_SESSION['komisi'] == 'Y') {
            if ($_SESSION['penjualansebelum'] == 'Y') {
                $ttlkomisi = $ttlqty * $komisi;
                run_query_or_fail($conn, "UPDATE komisi_pegawai SET ttl_komisi = '$ttlkomisi' 
            WHERE id_dtrkasir='$id_dtrkasir'");
            } else {
                $ttlkomisi = $ttlqty * $komisi;
                run_query_or_fail($conn, "UPDATE komisi_pegawai SET ttl_komisi = '$ttlkomisi' 
            WHERE id_dtrkasir='$id_dtrkasir' AND id_admin='$_SESSION[idadmin]'");
            }
        }
    }
    //kalau barang belum ada
    else {

            
            $ttlharga = $qty_dtrkasir * $hrgdisc;
    
    
            run_query_or_fail($conn, "INSERT INTO dropping_detail(kd_trkasir,
    										id_barang,
    										kd_barang,
    										nmbrg_dtrkasir,
    										qty_dtrkasir,
    										sat_dtrkasir,
    										hrgjual_dtrkasir,
    										disc,
    										no_batch,
    										exp_date,										
    										hrgttl_dtrkasir,
    										tipe,
                                            komisi,
                                            idadmin)
    								  VALUES('$kd_trkasir',
    										'$id_barang',
    										'$kd_barang',
    										'$nmbrg_dtrkasir',
    										'$qty_dtrkasir',
    										'$sat_dtrkasir',
    										'$hrgjual_dtrkasir',
    										'$disc',
    										'$no_batch',
    										'$exp_date',										
    										'$ttlharga',
    										'$tipe',
                                            '$komisi',
                                            '$id_admin'
    										)");
    
            $insertid_dtrkasir = mysqli_insert_id($conn);
    
            //proses input batch jika no batch kosong
            $datetime = date('Y-m-d H:i:s', time());
            if ($no_batch != "") {
                // Input batch
                run_query_or_fail($conn, "INSERT INTO batch(
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
    										'$qty_dtrkasir',
    										'$sat_dtrkasir',
    										'$kd_trkasir',
    										'$kd_barang',
    										'keluar'
    										)");
            }
    
            //cek transaksi sukses
            $cekmasuk = run_query_or_fail($conn, "select id_dtrkasir, kd_trkasir from dropping_detail 
            where kd_trkasir='$kd_trkasir'");
            $ketemucekmasuk = mysqli_fetch_array($cekmasuk);
            if ($ketemucekmasuk > 0) {
                //update stok
                run_query_or_fail($conn, "UPDATE barang SET 
                stok_barang = (stok_barang - $qty_dtrkasir)
                WHERE id_barang = '$id_barang' and kd_barang='$kd_barang'");
    
                if ($_SESSION['komisi'] == 'Y') {
                    if ($_SESSION['penjualansebelum'] == 'Y') {
                        // $cariidadmin = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT * FROM komisi_pegawai WHERE kd_trkasir = '$kd_trkasir'");
                        // $getAdmin = mysqli_fetch_array($cariidadmin);
    
                        $ttlkomisi = $qty_dtrkasir * $komisi;
                        run_query_or_fail($conn, "INSERT INTO komisi_pegawai (kd_trkasir, id_dtrkasir, id_admin, ttl_komisi, tgl_komisi, status_komisi)
                VALUES('$kd_trkasir', '$insertid_dtrkasir', '$id_admin', '$ttlkomisi', '$currentdate', 'on')");
                    } else {
                        $ttlkomisi = $qty_dtrkasir * $komisi;
                        run_query_or_fail($conn, "INSERT INTO komisi_pegawai (kd_trkasir, id_dtrkasir, id_admin, ttl_komisi, tgl_komisi, status_komisi)
                VALUES('$kd_trkasir', '$insertid_dtrkasir', '$_SESSION[idadmin]', '$ttlkomisi', '$currentdate', 'on')");
                    }
                }
            } 
            
        
    }
} else {
    //
    $cekdetail = run_query_or_fail($conn, "SELECT * FROM dropping_detail 
    WHERE id_dtrkasir='$id_dtrkasir'");
    $rcek = mysqli_fetch_array($cekdetail);
    $id_dtrkasir = $rcek['id_dtrkasir'];
    $qtylama = $rcek['qty_dtrkasir'];
    $qtybaru = $qtylama + $qty_dtrkasir;
    $ttlharga = $qtybaru * $hrgjual_dtrkasir;

    run_query_or_fail($conn, "UPDATE dropping_detail SET qty_dtrkasir = '$qtybaru',
										hrgjual_dtrkasir = '$hrgjual_dtrkasir',
										hrgttl_dtrkasir = '$ttlharga'
										WHERE id_dtrkasir = '$id_dtrkasir'");

    //update batch
    //cari qty batch lama
    $cariqtybatch = run_query_or_fail($conn, "SELECT qty FROM batch 
    WHERE kd_transaksi = '$kd_trkasir' 
          AND no_batch = '$no_batch'
          AND kd_barang = '$kd_barang'
          AND status = 'keluar'");
    $ketemucariqtybatch = mysqli_fetch_array($cariqtybatch);
    $qtybatchlama = $ketemucariqtybatch['qty'];
    $qtybatchbaru = $qtybatchlama + $qty_dtrkasir;
    run_query_or_fail($conn, "UPDATE batch SET qty = '$qtybatchbaru' 
                WHERE kd_transaksi = '$kd_trkasir' 
                      AND no_batch = '$no_batch'
                      AND kd_barang = '$kd_barang'
                      AND status = 'keluar'");
                      
    //update stok
    //cek untuk update
    $cekmasuk2 = run_query_or_fail($conn, "SELECT * FROM dropping_detail 
    WHERE id_dtrkasir='$id_dtrkasir'");
    $ceklagi = mysqli_fetch_array($cekmasuk2);
    // if($ceklagi == $qtybaru) {
    run_query_or_fail($conn, "UPDATE barang SET 
            stok_barang = (stok_barang - $qty_dtrkasir)
            WHERE id_barang = '$id_barang' and kd_barang='$kd_barang'");
    // }
    // else{}

    if ($_SESSION['komisi'] == 'Y') {
        if ($_SESSION['penjualansebelum'] == 'Y') {
            $ttlkomisi = $qtybaru * $komisi;
            run_query_or_fail($conn, "UPDATE komisi_pegawai SET ttl_komisi = '$ttlkomisi' 
            WHERE id_dtrkasir='$id_dtrkasir'");
        } else {
            $ttlkomisi = $qtybaru * $komisi;
            run_query_or_fail($conn, "UPDATE komisi_pegawai SET ttl_komisi = '$ttlkomisi' 
            WHERE id_dtrkasir='$id_dtrkasir' AND id_admin='$_SESSION[idadmin]'");
        }
    }
}

mysqli_commit($conn);
} catch (Exception $e) {
    mysqli_rollback($conn);
    write_trkasir_log(
        $logFile,
        'Simpan detail gagal | kd_trkasir=' . $kd_trkasir .
        ' | id_barang=' . $id_barang .
        ' | kd_barang=' . $kd_barang .
        ' | qty=' . $qty_dtrkasir .
        ' | user=' . (isset($_SESSION['username']) ? $_SESSION['username'] : '-') .
        ' | detail=' . $e->getMessage()
    );
    http_response_code(500);
    echo 'Gagal menyimpan detail transaksi';
}
