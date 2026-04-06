<?php
include "../../../configurasi/koneksi.php";

$ubah = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT * FROM cabang");


$json = [];
while($re=mysqli_fetch_array($ubah)){
$json[] = array(
            'id_cabang'     => $re['id_cabang'],
			'nm_cabang'     => $re['nm_cabang'],
            'tlp_cabang'    => $re['tlp_cabang'],
			'alamat_cabang' => $re['alamat_cabang'],
			'ket_cabang'    => $re['ket_cabang']
			);
}
echo json_encode($json);
?>