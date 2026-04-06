<?php
include "../../../configurasi/koneksi.php";

$key = $_POST['nm_barang'];

$ubah = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT * FROM barang WHERE nm_barang = '$key'");


$json = [];
while($re=mysqli_fetch_array($ubah)){
$json[] = array(
            'id_barang'=> $re['id_barang'],
			'nm_barang'=> $re['nm_barang'],
            'jenisobat'=> $re['jenisobat'],
			'stok_barang'=> $re['stok_barang'],
			'sat_barang'=> $re['sat_barang'],
			'indikasi'=> $re['indikasi'],
			'hrgsat_barang'=> $re['hrgsat_barang'],
			'hrgjual_barang'=> $re['hrgsat_barang'],
            'hrgjual_barang1'=> $re['hrgsat_barang'],
            'hrgjual_barang2'=> $re['hrgsat_barang'],
            'hrgjual_barang3'=> $re['hrgsat_barang'],
            'hrgjual_barang4'=> $re['hrgsat_barang'],
            'hrgjual_barang5'=> $re['hrgsat_barang'],
			'kd_barang'=> $re['kd_barang'],
			'komisi'=> $re['komisi'],
			);
}
echo json_encode($json);
?>