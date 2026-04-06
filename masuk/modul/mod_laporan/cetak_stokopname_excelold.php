<?php
session_start();
include "../../../configurasi/koneksi.php";
include "../../../configurasi/fungsi_indotgl.php";
include "../../../configurasi/fungsi_rupiah.php";
$tgl_awal = $_GET['start'];
$tgl_akhir = $_GET['finish'];
header("Content-type: application/vnd-ms-excel");
header("Content-Disposition: attachment; filename=Data_barang.xls");

?>
<center>
    <h1>LAPORAN STOK OPNAME</h1>
</center>
<?php

// $tgl_awal = $_POST['tgl_awal'];
// $tgl_akhir = $_POST['tgl_akhir'];
$tglskrg = date('Y-m-d');
echo "Dicetak Oleh : ";

echo $_SESSION['namalengkap'];
echo " Tanggal : $tglskrg";

?>
<table border="1">
    <tr style="text-align: center; font-weight: bold;">
        <td>No</td>
        <td>Petugas</td>
        <td>Kode Barang</td>
        <td>Nama Barang</td>
        <td>Satuan</td>
        <td>Exp</td>
        <td>JmlED</td>
        <td>SS</td>
        <td>SF</td>
        <td>Selisih</td>
        <td>Waktu</td>
        <td>Harga</td>
        <td>Total</td>
    </tr>
    <?php
    $so = $db->query("select * from stok_opname 
      where tgl_stokopname between '$tgl_awal' and '$tgl_akhir'");
    $no = 1;
    while ($lihat = $so->fetch_array())
        { $obat = $db->query("select * from barang where kd_barang='$lihat[kd_barang]'");
          $tobat = $obat->fetch_array();
          $admin = $db->query("select * from admin where id_admin = '$lihat[id_admin]'");
          $tadmin = $admin->fetch_array();
            echo"
        <tr>
         <td>$no</td>
         <td>$tadmin[nama_lengkap]</td>
         <td>$lihat[kd_barang]</td>
         <td>$tobat[nm_barang]</td>
         <td>$tobat[sat_barang]</td>
         <td>$lihat[exp_date]</td>
         <td>$lihat[jml]</td>
         <td>$lihat[stok_sistem]</td>
         <td>$lihat[stok_fisik]</td>
         <td>$lihat[selisih]</td>
         <td>$lihat[tgl_current]</td>
         <td>$lihat[hrgsat_barang]</td>
         <td>$lihat[ttl_hrgbrg]</td>
         
        </tr>
        ";
        $no++;
        }
    ?>
</table>



