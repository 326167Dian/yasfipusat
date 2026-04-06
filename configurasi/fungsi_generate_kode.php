<?php
    include "koneksi.php";
    
    function kode_dropping(){
        $date = date('dmyHis', time());
        $kd_trdropping = 'DRP-'.$date;
        
        $stmt = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT kd_trdropping, RIGHT(kd_trdropping, 2) AS kode_int 
        FROM trdropping WHERE LEFT(kd_trdropping, 12) LIKE '%$kd_trdropping%' ORDER BY kd_trdropping DESC LIMIT 1");
        $cek = mysqli_num_rows($stmt);
        
        if ($cek > 0) {
            $r = mysqli_fetch_array($stmt);
            $kode = $kd_trdropping.str_pad($r['kode_int'] + 1, 2, '0', STR_PAD_LEFT);
        } else {
            $kode = $kd_trdropping.'01';
        }
        return $kode;
    }
?>