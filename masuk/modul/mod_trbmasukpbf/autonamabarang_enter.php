<?php
include "../../../configurasi/koneksi.php";

$key = $_POST['nm_barang'];

$ubah = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT * FROM barang WHERE nm_barang = '$key'");

// Get the latest diskon from trbmasuk_detail for this item
$diskon_result = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT diskon FROM trbmasuk_detail WHERE nmbrg_dtrbmasuk = '$key' ORDER BY id_dtrbmasuk DESC LIMIT 1");
$diskon_row = mysqli_fetch_array($diskon_result);
$latest_diskon = $diskon_row ? $diskon_row['diskon'] : "0";

$json = [];
while($re=mysqli_fetch_array($ubah)){
$json[] = array(
            'id_barang'=> $re['id_barang'],
			'nm_barang'=> $re['nm_barang'],
			'stok_barang'=> $re['stok_barang'],
			'sat_barang'=> $re['sat_barang'],
			'sat_grosir'=> $re['sat_grosir'],
			'indikasi'=> $re['indikasi'],
			'konversi'=> $re['konversi'],
			'hrgjual_barang'=> $re['hrgjual_barang'],
			'hrgsat_barang'=> $re['hrgsat_barang'],
			'hna'=> $re['hna'],
			'kd_barang'=> $re['kd_barang'],
			'diskon'=> $latest_diskon
			);
}
echo json_encode($json);
?>
