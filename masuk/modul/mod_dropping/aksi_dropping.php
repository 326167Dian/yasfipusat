<?php
error_reporting(0);
session_start();
if (empty($_SESSION['username']) and empty($_SESSION['passuser'])) {
	echo "<link href='style.css' rel='stylesheet' type='text/css'>
 <center>Untuk mengakses modul, Anda harus login <br>";
	echo "<a href=../../index.php><b>LOGIN</b></a></center>";
} else {
	include "../../../configurasi/koneksi.php";
	include "../../../configurasi/fungsi_thumb.php";
	include "../../../configurasi/library.php";
	include "../../../configurasi/fungsi_generate_kode.php";
	
	$conn = $GLOBALS["___mysqli_ston"];
	$conn2 = $GLOBALS["___mysqli_ston2"];
	$logFile = __DIR__ . '/aksi_trkasir_error.log';

	function write_aksi_trkasir_log($logFile, $message)
	{
		$timestamp = date('Y-m-d H:i:s');
		error_log("[$timestamp] $message" . PHP_EOL, 3, $logFile);
	}

	function run_query_or_fail_aksi_trkasir($conn, $sql)
	{
		$result = mysqli_query($conn, $sql);
		if ($result === false) {
			throw new Exception('SQL Error: ' . mysqli_error($conn) . ' | Query: ' . $sql);
		}

		return $result;
	}

	function sync_dropping_to_yasfi2($connSource, $connTarget, $kdTrdropping, $kdTrkasir)
	{
		$kdTrkasirSource = mysqli_real_escape_string($connSource, $kdTrkasir);
		$kdTrdroppingTarget = mysqli_real_escape_string($connTarget, $kdTrdropping);
		$timestamp = date('Y-m-d H:i:s');

		mysqli_begin_transaction($connTarget);

		try {
			$headerResult = run_query_or_fail_aksi_trkasir($connSource, "SELECT * FROM dropping WHERE kd_trkasir = '$kdTrkasirSource' LIMIT 1");
			$header = mysqli_fetch_assoc($headerResult);
			if (!$header) {
				throw new Exception('Data dropping untuk sinkronisasi tidak ditemukan');
			}

			run_query_or_fail_aksi_trkasir($connTarget, "DELETE FROM dropping_detail WHERE kd_trbmasuk = '$kdTrdroppingTarget'");
			run_query_or_fail_aksi_trkasir($connTarget, "DELETE FROM trbmasuk_detail WHERE kd_trbmasuk = '$kdTrdroppingTarget'");
			run_query_or_fail_aksi_trkasir($connTarget, "DELETE FROM kartu_stok WHERE kode_transaksi = '$kdTrdroppingTarget'");
			run_query_or_fail_aksi_trkasir($connTarget, "DELETE FROM dropping WHERE kd_trbmasuk = '$kdTrdroppingTarget'");
			run_query_or_fail_aksi_trkasir($connTarget, "DELETE FROM trbmasuk WHERE kd_trbmasuk = '$kdTrdroppingTarget'");
			
			$setheader = run_query_or_fail_aksi_trkasir($connSource, "SELECT satu FROM setheader");
            $head = mysqli_fetch_assoc($setheader);
            
			$tglTrbmasuk = !empty($header['tgl_trkasir']) ? $header['tgl_trkasir'] : date('Y-m-d');
			$petugas = mysqli_real_escape_string($connTarget, isset($header['petugas']) ? $header['petugas'] : '');
			$nmSupplierRaw = trim(isset($header['nm_pelanggan']) ? $header['nm_pelanggan'] : '') !== '' ? $header['nm_pelanggan'] : 'Dropping Barang';
// 			$nmSupplier = mysqli_real_escape_string($connTarget, $nmSupplierRaw);
			$nmSupplier = mysqli_real_escape_string($connTarget, $head['satu']);
			$tlpSupplier = mysqli_real_escape_string($connTarget, isset($header['tlp_pelanggan']) ? $header['tlp_pelanggan'] : '');
			$alamatTrbmasuk = mysqli_real_escape_string($connTarget, isset($header['alamat_pelanggan']) ? $header['alamat_pelanggan'] : '');
			$ketParts = array_filter(array(
				'Dropping dari yasfi',
				'Ref: ' . $kdTrkasir,
				isset($header['ket_trkasir']) ? $header['ket_trkasir'] : ''
			));
			$ketTrbmasuk = mysqli_real_escape_string($connTarget, implode(' | ', $ketParts));

			run_query_or_fail_aksi_trkasir($connTarget, "INSERT INTO dropping(
				id_resto,
				petugas,
				kd_trbmasuk,
				kd_orders,
				tgl_trbmasuk,
				id_supplier,
				nm_supplier,
				tlp_supplier,
				alamat_trbmasuk,
				ttl_trbmasuk,
				dp_bayar,
				sisa_bayar,
				ket_trbmasuk,
				jatuhtempo,
				carabayar,
				jenis,
				tgl_lunas,
				petugas_lunas
			) VALUES(
				'pusat',
				'$petugas',
				'$kdTrdroppingTarget',
				'$kdTrkasirSource',
				'$tglTrbmasuk',
				'0',
				'$nmSupplier',
				'$tlpSupplier',
				'$alamatTrbmasuk',
				'" . (float) $header['ttl_trkasir'] . "',
				'" . (float) $header['dp_bayar'] . "',
				'" . (float) $header['sisa_bayar'] . "',
				'$ketTrbmasuk',
				'$tglTrbmasuk',
				'mutasi dari yasfi',
				'nonpbf',
				'$tglTrbmasuk',
				'$petugas'
			)");

			run_query_or_fail_aksi_trkasir($connTarget, "INSERT INTO kartu_stok(kode_transaksi, tgl_sekarang) VALUES('$kdTrdroppingTarget', '$timestamp')");

			$detailResult = run_query_or_fail_aksi_trkasir($connSource, "SELECT td.*, b.konversi, b.hna, b.hrgsat_barang, b.hrgjual_barang
				FROM dropping_detail td
				LEFT JOIN barang b ON b.id_barang = td.id_barang
				WHERE td.kd_trkasir = '$kdTrkasirSource'");

			$detailCount = 0;
			while ($detail = mysqli_fetch_assoc($detailResult)) {
				$kdBarang = mysqli_real_escape_string($connTarget, $detail['kd_barang']);
				$targetBarangResult = run_query_or_fail_aksi_trkasir($connTarget, "SELECT id_barang, konversi, hna, hrgsat_barang, hrgjual_barang
					FROM barang
					WHERE kd_barang = '$kdBarang'
					LIMIT 1");
				$targetBarang = mysqli_fetch_assoc($targetBarangResult);

				$idBarangTarget = $targetBarang ? (int) $targetBarang['id_barang'] : (int) $detail['id_barang'];
				$konversi = $targetBarang && (int) $targetBarang['konversi'] > 0 ? (int) $targetBarang['konversi'] : ((int) $detail['konversi'] > 0 ? (int) $detail['konversi'] : 1);
				$qty = (float) $detail['qty_dtrkasir'];
				$qtyGrosir = $konversi > 0 ? max(1, (int) round($qty / $konversi)) : max(1, (int) round($qty));
				$hnaSat = $targetBarang && (float) $targetBarang['hna'] > 0 ? (float) $targetBarang['hna'] : (float) $detail['hna'];
				$hargaDasar = $targetBarang && (float) $targetBarang['hrgsat_barang'] > 0 ? (float) $targetBarang['hrgsat_barang'] : (float) $detail['hrgjual_dtrkasir'];
				$hargaJual = (float) $detail['hrgjual_dtrkasir'] > 0 ? (float) $detail['hrgjual_dtrkasir'] : ($targetBarang ? (float) $targetBarang['hrgjual_barang'] : 0);
				$diskon = isset($detail['disc']) ? (float) $detail['disc'] : 0;
				$total = (float) $detail['hrgttl_dtrkasir'] > 0 ? (float) $detail['hrgttl_dtrkasir'] : ($qty * $hargaJual);
				$namaBarang = mysqli_real_escape_string($connTarget, $detail['nmbrg_dtrkasir']);
				$satuan = mysqli_real_escape_string($connTarget, $detail['sat_dtrkasir']);
				$noBatch = mysqli_real_escape_string($connTarget, isset($detail['no_batch']) ? $detail['no_batch'] : '');
				$expDate = !empty($detail['exp_date']) ? $detail['exp_date'] : '0000-00-00';
				$tipe = isset($detail['tipe']) ? (int) $detail['tipe'] : 0;

				run_query_or_fail_aksi_trkasir($connTarget, "INSERT INTO dropping_detail(
					kd_trbmasuk,
					id_barang,
					kd_barang,
					nmbrg_dtrbmasuk,
					qty_dtrbmasuk,
					qty_grosir,
					hnasat_dtrbmasuk,
					diskon,
					sat_dtrbmasuk,
					konversi,
					hrgsat_dtrbmasuk,
					hrgjual_dtrbmasuk,
					hrgttl_dtrbmasuk,
					no_batch,
					exp_date,
					waktu,
					tipe
				) VALUES(
					'$kdTrdroppingTarget',
					'$idBarangTarget',
					'$kdBarang',
					'$namaBarang',
					'$qty',
					'$qtyGrosir',
					'$hnaSat',
					'$diskon',
					'$satuan',
					'$konversi',
					'$hargaDasar',
					'$hargaJual',
					'$total',
					'$noBatch',
					'$expDate',
					'$timestamp',
					'$tipe'
				)");

				$detailCount++;
			}

			if ($detailCount <= 0) {
				throw new Exception('Detail trkasir untuk dropping tidak ditemukan');
			}

			mysqli_commit($connTarget);
		} catch (Exception $e) {
			mysqli_rollback($connTarget);
			throw $e;
		}
	}
	$jenistx = $db->query("select * from dropping_detail where kd_trkasir='$_POST[kd_trkasir]' group by kd_trkasir ");
	$jnstx = $jenistx->fetch_array();
	

	$module = "trkasir";
	$stt_aksi = $_POST['stt_aksi'];
	if ($stt_aksi == "input_trkasir" || $stt_aksi == "ubah_trkasir") {
		$act = $stt_aksi;
	} else {
		$act = $_GET['act'];
	}
	
	// Input admin
	if ($module == 'trkasir' and $act == 'input_trkasir') {
        
        $cariitem = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT * FROM dropping_detail WHERE kd_trkasir = '$_POST[kd_trkasir]'");
        $countItem = mysqli_num_rows($cariitem);

        if($countItem <= 0){
            $data['message'] = 'failed';
			echo json_encode($data);
        } else {
    		$insert = mysqli_query($GLOBALS["___mysqli_ston"], "INSERT INTO dropping(
    										kd_trkasir,	
    										petugas,
    										shift,																		
    										tgl_trkasir,																			
    										nm_pelanggan,										
    										tlp_pelanggan,
    										alamat_pelanggan,
    										kodetx,
    										ttl_trkasir,
    										diskon2,
    										dp_bayar,
    										sisa_bayar,
    										ket_trkasir,
    										id_carabayar,
											jenistx
    										)
    								 VALUES('$_POST[kd_trkasir]',
    								 		'$_POST[petugas]',
    								 		'$_POST[shift]',
    										'$_POST[tgl_trkasir]',										
    										'$_POST[nm_pelanggan]',
    										'$_POST[tlp_pelanggan]',
    										'$_POST[alamat_pelanggan]',
    										'$_POST[kodetx]',
    										'$_POST[ttl_trkasir]',
    										'$_POST[diskon2]',
    										'$_POST[dp_bayar]',
    										'$_POST[sisa_bayar]',
    										'$_POST[ket_trkasir]',
    										'$_POST[id_carabayar]',
											'$jnstx[tipe]'
    										
    										)");
            
            $datetime = date('Y-m-d H:i:s', time());
            mysqli_query($GLOBALS["___mysqli_ston"], "INSERT INTO kartu_stok(kode_transaksi, tgl_sekarang) VALUES('$_POST[kd_trkasir]','$datetime')");
            mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE kdtk SET stt_kdtk = 'OFF' WHERE id_admin = '$_SESSION[idadmin]'AND kd_trkasir = '$_POST[kd_trkasir]'");
    		
    		
    		if ($insert) {
    			# code...
    
    //             $ambildatainduk = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT * FROM trkasir WHERE id_trkasir='$_GET[id]'");
                $ambildatainduk = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT * FROM dropping WHERE kd_trkasir='$_POST[kd_trkasir]'");
    			$r1 = mysqli_fetch_array($ambildatainduk);
    			$kd_trkasir = $r1['kd_trkasir'];
    			
    			//loop data detail
    			$ambildatadetail = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT * FROM dropping_detail WHERE kd_trkasir='$kd_trkasir'");
    			while ($r = mysqli_fetch_array($ambildatadetail)) {
                    mysqli_query($GLOBALS["___mysqli_ston"], "INSERT INTO trkasir_restore(
    						kd_trkasir, petugas, shift, tgl_trkasir, nm_pelanggan, tlp_pelanggan, alamat_pelanggan,
    						ttl_trkasir, dp_bayar, diskon1, diskon2, sisa_bayar, ket_trkasir, id_carabayar, id_barang,
    						kd_barang, nmbrg_dtrkasir, qty_dtrkasir, sat_dtrkasir, hrgjual_dtrkasir, hrgttl_dtrkasir)
    					VALUES(
    						'$r1[kd_trkasir]','$r1[petugas]','$r1[shift]','$r1[tgl_trkasir]','$r1[nm_pelanggan]','$r1[tlp_pelanggan]','$r1[alamat_pelanggan]','$r1[ttl_trkasir]','$r1[dp_bayar]','$r1[diskon1]','$r1[diskon2]','$r1[sisa_bayar]','$r1[ket_trkasir]','$r1[id_carabayar]','$r[id_barang]','$r[kd_barang]','$r[nmbrg_dtrkasir]','$r[qty_dtrkasir]','$r[sat_dtrkasir]','$r[hrgjual_dtrkasir]','$r[hrgttl_dtrkasir]')");
    
                }
                
try {
                    if ($_POST['id_carabayar'] == 4 and $_POST['trdroping'] == 'tambah') {

                        $kd_trdroping = kode_dropping();
                        run_query_or_fail_aksi_trkasir($conn, "INSERT INTO trdropping (
                                                                    kd_trdropping,
                                                                    kd_trkasir,
                                                                    waktu)
                                                                VALUES(
                                                                    '$kd_trdroping',
                                                                    '$_POST[kd_trkasir]',
                                                                    '$datetime')");

                        sync_dropping_to_yasfi2($conn, $conn2, $kd_trdroping, $_POST['kd_trkasir']);

                        $data['message'] = 'droping';
                        echo json_encode($data);
                        die();
                    } else {
                        $data['message'] = 'success';
                        echo json_encode($data);
                    }
                } catch (Exception $e) {
                    write_aksi_trkasir_log(
                        $logFile,
                        'Sinkronisasi dropping gagal | kd_trkasir=' . $_POST['kd_trkasir'] .
                        ' | detail=' . $e->getMessage()
                    );
                    $data['message'] = 'failed';
                    echo json_encode($data);
                }
    		
    		} else {
    			$data['message'] = 'failed';
    			echo json_encode($data);
    		}

        }
		//echo "<script type='text/javascript'>alert('Transkasi berhasil ditambahkan !');window.location='../../media_admin.php?module=".$module."'</script>";
	}

	//updata trkasir
	elseif ($module == 'trkasir' and $act == 'ubah_trkasir') {

		$ubah = mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE dropping SET tgl_trkasir = '$_POST[tgl_trkasir]',
									petugas = '$_POST[petugas]',
									nm_pelanggan = '$_POST[nm_pelanggan]',									
									tlp_pelanggan = '$_POST[tlp_pelanggan]',
									alamat_pelanggan = '$_POST[alamat_pelanggan]',
									kodetx = '$_POST[kodetx]',
									ttl_trkasir = '$_POST[ttl_trkasir]',
									diskon2 = '$_POST[diskon2]',
									dp_bayar = '$_POST[dp_bayar]',
									sisa_bayar = '$_POST[sisa_bayar]',
									ket_trkasir = '$_POST[ket_trkasir]',
									id_carabayar = '$_POST[id_carabayar]'
									WHERE id_trkasir = '$_POST[id_trkasir]'");


        if($ubah){
            try {
                if ($_POST['id_carabayar'] == 4 and $_POST['trdroping'] == 'ubah') {
                    $trkasir = run_query_or_fail_aksi_trkasir($conn, "SELECT kd_trkasir FROM dropping WHERE id_trkasir = '$_POST[id_trkasir]' LIMIT 1");
                    $trk = mysqli_fetch_array($trkasir);

                    if (!$trk) {
                        throw new Exception('Data trkasir tidak ditemukan saat ubah dropping');
                    }

                    $cekDropping = run_query_or_fail_aksi_trkasir($conn, "SELECT kd_trdropping FROM trdropping WHERE kd_trkasir = '$trk[kd_trkasir]' LIMIT 1");
                    $dropRow = mysqli_fetch_array($cekDropping);

                    if ($dropRow) {
                        $kd_trdroping = $dropRow['kd_trdropping'];
                    } else {
                        $kd_trdroping = kode_dropping();
                        run_query_or_fail_aksi_trkasir($conn, "INSERT INTO trdropping (kd_trdropping, kd_trkasir, waktu)
                            VALUES('$kd_trdroping', '$trk[kd_trkasir]', '" . date('Y-m-d H:i:s') . "')");
                    }

                    sync_dropping_to_yasfi2($conn, $conn2, $kd_trdroping, $trk['kd_trkasir']);

                    $data['message'] = 'droping';
                    echo json_encode($data);
                } else {
                    $data['message'] = 'success';
                    echo json_encode($data);
                }
            } catch (Exception $e) {
                write_aksi_trkasir_log(
                    $logFile,
                    'Sinkronisasi ubah dropping gagal | id_trkasir=' . $_POST['id_trkasir'] .
                    ' | kd_trkasir=' . $_POST['kd_trkasir'] .
                    ' | detail=' . $e->getMessage()
                );
                $data['message'] = 'failed';
                echo json_encode($data);
            }		
    	} else {
    		$data['message'] = 'failed';
    		echo json_encode($data);
    	}
		//echo "<script type='text/javascript'>alert('Transkasi berhasil Ubah !');window.location='../../media_admin.php?module=".$module."'</script>";


	}
	//Hapus Proyek
	elseif ($module == 'trkasir' and $act == 'hapus') {

		if ($_SESSION['level'] != 'pemilik') {
			echo "<script type='text/javascript'>window.location='../../media_admin.php?module=" . $module . "'</script>";
		} else {
			$id_trkasir = $_GET['id'];
			$kd_trdropping = isset($_GET['droping']) ? $_GET['droping'] : '';
			if ($kd_trdropping != '') {
				mysqli_begin_transaction($conn2);
			}
			mysqli_begin_transaction($conn);

			try {
				if ($kd_trdropping != '') {
					run_query_or_fail_aksi_trkasir($conn, "DELETE FROM trdropping WHERE kd_trdropping = '$kd_trdropping'");
					run_query_or_fail_aksi_trkasir($conn2, "DELETE FROM dropping_detail WHERE kd_trbmasuk = '$kd_trdropping'");
					run_query_or_fail_aksi_trkasir($conn2, "DELETE FROM trbmasuk_detail WHERE kd_trbmasuk = '$kd_trdropping'");
					run_query_or_fail_aksi_trkasir($conn2, "DELETE FROM kartu_stok WHERE kode_transaksi = '$kd_trdropping'");
					run_query_or_fail_aksi_trkasir($conn2, "DELETE FROM dropping WHERE kd_trbmasuk = '$kd_trdropping'");
					run_query_or_fail_aksi_trkasir($conn2, "DELETE FROM trbmasuk WHERE kd_trbmasuk = '$kd_trdropping'");
				}
				$ambildatainduk = run_query_or_fail_aksi_trkasir($conn, "SELECT * FROM dropping WHERE id_trkasir='$id_trkasir'");
				$r1 = mysqli_fetch_array($ambildatainduk);
				if (!$r1) {
					throw new Exception('Data trkasir tidak ditemukan');
				}

				$kd_trkasir = $r1['kd_trkasir'];
				$ambildatadetail = run_query_or_fail_aksi_trkasir($conn, "SELECT * FROM dropping_detail WHERE kd_trkasir='$kd_trkasir'");
				while ($r = mysqli_fetch_array($ambildatadetail)) {

					$id_dtrkasir = $r['id_dtrkasir'];
					$id_barang = $r['id_barang'];
					$qty_dtrkasir = (float) $r['qty_dtrkasir'];

					run_query_or_fail_aksi_trkasir($conn, "INSERT INTO trkasir_restore(
						kd_trkasir, petugas, shift, tgl_trkasir, nm_pelanggan, tlp_pelanggan, alamat_pelanggan,
						ttl_trkasir, dp_bayar, diskon1, diskon2, sisa_bayar, ket_trkasir, id_carabayar, id_barang,
						kd_barang, nmbrg_dtrkasir, qty_dtrkasir, sat_dtrkasir, hrgjual_dtrkasir, hrgttl_dtrkasir)
					VALUES(
						'$r1[kd_trkasir]','$r1[petugas]','$r1[shift]','$r1[tgl_trkasir]','$r1[nm_pelanggan]','$r1[tlp_pelanggan]','$r1[alamat_pelanggan]','$r1[ttl_trkasir]','$r1[dp_bayar]','$r1[diskon1]','$r1[diskon2]','$r1[sisa_bayar]','$r1[ket_trkasir]','$r1[id_carabayar]','$r[id_barang]','$r[kd_barang]','$r[nmbrg_dtrkasir]','$r[qty_dtrkasir]','$r[sat_dtrkasir]','$r[hrgjual_dtrkasir]','$r[hrgttl_dtrkasir]')");

					run_query_or_fail_aksi_trkasir($conn, "UPDATE barang SET stok_barang = (stok_barang + $qty_dtrkasir) WHERE id_barang = '$id_barang'");
					run_query_or_fail_aksi_trkasir($conn, "DELETE FROM dropping_detail WHERE id_dtrkasir = '$id_dtrkasir'");
					run_query_or_fail_aksi_trkasir($conn, "DELETE FROM komisi_pegawai WHERE id_dtrkasir = '$id_dtrkasir'");
					run_query_or_fail_aksi_trkasir($conn, "DELETE FROM batch WHERE kd_transaksi = '$r[kd_trkasir]' AND no_batch='$r[no_batch]' AND kd_barang='$r[kd_barang]' AND status = 'keluar'");
				}

				run_query_or_fail_aksi_trkasir($conn, "DELETE FROM dropping WHERE id_trkasir = '$id_trkasir'");
				run_query_or_fail_aksi_trkasir($conn, "DELETE FROM kartu_stok WHERE kode_transaksi = '$kd_trkasir'");
				mysqli_commit($conn);
				if ($kd_trdropping != '') {
					mysqli_commit($conn2);
				}
				
				if($kd_trdropping != ''){
				    
				    echo "<script type='text/javascript'>alert('Data berhasil dihapus !');window.location='../../media_admin.php?module=dropping'</script>";
				} else {
				    
				    echo "<script type='text/javascript'>alert('Data berhasil dihapus !');window.location='../../media_admin.php?module=" . $module . "'</script>";
				}
			} catch (Exception $e) {
				mysqli_rollback($conn);
				if ($kd_trdropping != '') {
					mysqli_rollback($conn2);
				}
				write_aksi_trkasir_log(
					$logFile,
					'Hapus trkasir gagal | id_trkasir=' . $id_trkasir .
					' | kd_trkasir=' . (isset($kd_trkasir) ? $kd_trkasir : '-') .
					' | detail=' . $e->getMessage()
				);
				echo "<script type='text/javascript'>alert('Gagal menghapus data !');window.location='../../media_admin.php?module=" . $module . "'</script>";
			}
		}
	}
}
