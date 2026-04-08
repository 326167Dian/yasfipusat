<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['username']) and empty($_SESSION['passuser'])) {
    echo "<link href=../css/style.css rel=stylesheet type=text/css>";
    echo "<div class='error msg'>Untuk mengakses Modul anda harus login.</div>";
} else {
	include "../configurasi/fungsi_generate_kode.php";

    $aksi = "modul/mod_dropping/aksi_dropping.php";
    $aksi_trkasir = "masuk/modul/mod_dropping/aksi_dropping.php";
    

    $act = isset($_GET['act']) ? $_GET['act'] : '';

    switch ($act) {
        case "tambah":
        case "ubah":
            include __DIR__ . "/../mod_trkasir/trkasir.php";
            break;

        // Tampil Penjualan
        default:
            $tgl_awal = date('Y-m-d');
            $tanpatgl = substr($tgl_awal, 0, 8);
            $awalbulan = $tanpatgl . '01';
            $tampil_trkasir = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT * FROM trdropping join 
            dropping on dropping.kd_trkasir = trdropping.kd_trkasir ORDER BY trdropping.waktu DESC");
        
?>


            <div class="box box-primary box-solid table-responsive">
                <div class="box-header with-border">
                    <h3 class="box-title">DROPPING BARANG</h3>
                    <div class="box-tools pull-right">
                        <button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                    </div><!-- /.box-tools -->
                </div>
                <div class="box-body table-responsive">
                    <a class='btn  btn-success btn-flat' href='?module=dropping&act=tambah_dropping'>TAMBAH DROPPING</a>
                    <hr>
                    
                    <table id="rekap" class="table table-bordered table-striped">
                        <thead style="text-align:center; text-transform:uppercase;">
                            <tr>
                                <th >No</th>
                                <th style="text-align:center">No Droping</th>
                                <th style="text-align:center">No Transaksi</th>
                                <!--<th>shift</th>-->
                                <!--<th>Jenis Transaksi</th>-->
                                <th>petugas</th>
                                <th>Tanggal</th>
                                <th>Pelanggan</th>
                                <th style="text-align:center">No Pesanan</th>
                                <th style="text-align:center">TUJUAN</th>
                                <th>Total</th>
                                <th width="70">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                    
                    
                </div>
                
            </div>

            <script>
                $(document).ready(function() {
                    $("#rekap").DataTable({
                        processing: true,
                        serverSide: true,
                        ajax: {
                            "url": "modul/mod_dropping/trdroping_serverside.php?action=table_data",
                            "dataType": "JSON",
                            "type": "POST"
                        },
                        "rowCallback": function(row, data, index) {
                             let q = (data['nm_carabayar']);
                             let r = (data['shift']);
                             let s = (data['jenistx']);
                            

                        },
                        columns: [{
                                "data": "no",
                                "className": 'text-center',
                            },
                            {
                                "data": "kd_trdropping"
                            },
                            {
                                "data": "kd_trkasir"
                            },
                            // {
                            //     "data": "shift",
                            //     "className": 'text-center',
                            // },
                            // {
                            //     "data": "jenistx",
                            //     "className": 'text-center',
                            // },
                            {
                                "data": "petugas",
                                "className": 'text-center',
                            },
                            {
                                "data": "tgl_trkasir",
                                "className": 'text-center',
                            },
                            {
                                "data": "nm_pelanggan",
                            },
                            {
                                "data": "kodetx",
                            },
                            {
                                "data": "nm_carabayar",
                            },
                            {
                                "data": "ttl_trkasir",
                                "className": 'text-right',
                                "render": function(data, type, row) {
                                    return formatRupiah(data);
                                }
                            },
                            {
                                "data": "pilih",
                                "className": 'text-center'
                            },
                        ],

                    })

                });
            </script>

        <?php

            break ;
            case "tambah_dropping":
         
                 //cek apakah ada kode transaksi ON berdasarkan user
                $cekkd = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT * FROM kdtk WHERE id_admin='$_SESSION[idadmin]' AND stt_kdtk='ON'");
                $ketemucekkd = mysqli_num_rows($cekkd);
                $hcekkd = mysqli_fetch_array($cekkd);
                $petugas = $_SESSION['namalengkap'];


                if ($ketemucekkd > 0) {
                    $kdtransaksi = $hcekkd['kd_trkasir'];
                } else {
                    $kdunik = date('dmyHis');
                    $kdtransaksi = "DRP-" . $kdunik;
                    $cekkd2 = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT * FROM kdtk WHERE kd_trkasir='$kdtransaksi'");
                    $ketemucekkd2 = mysqli_num_rows($cekkd2);
                    if ($ketemucekkd2 > 0) {
                        $kdunik2 = date('dmyHis')+1;
                        $kdtransaksi = "DRP-" . $kdunik2;
                    }
                    
                    mysqli_query($GLOBALS["___mysqli_ston"], "INSERT INTO kdtk(kd_trkasir,id_admin) VALUES('$kdtransaksi','$_SESSION[idadmin]')");
                }

                $tglharini = date('Y-m-d');
                $trdroping = 'tambah';

               // $tampil_jenisobat = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT * FROM jenis_obat ORDER BY idjenis ");

                echo "<small>F1 => Simpan Detail || F3 => Simpan Transaksi</small>";
                echo "
		  <div class='box box-primary box-solid table-responsive'>
				<div class='box-header with-border'>
					<h3 class='box-title'>TAMBAH DROPPING BARANG</h3>
					<div class='box-tools pull-right'>
						<button class='btn btn-box-tool' data-widget='collapse'><i class='fa fa-minus'></i></button>
                    </div><!-- /.box-tools -->
				</div>
				<div class='box-body table-responsive'>
				
						<form onsubmit='return false;' method=POST action='$aksi?module=dropping&act=input_dropping' enctype='multipart/form-data' class='form-horizontal'>
						
						       <input type=hidden name='id_trkasir' id='id_trkasir' value='0'>
							   <input type=hidden name='kd_trkasir' id='kd_trkasir' value='$kdtransaksi'>
							   <input type=hidden name='stt_aksi' id='stt_aksi' value='input_trkasir'>
							   <input type=hidden name='petugas' id='petugas' value='$petugas'>
							   <input type=hidden name='shift' id='shift' value='$shift'>
							   <input type=hidden name='level' id='level' value='$_SESSION[level]'>
						       <input type='hidden' name='trdroping' id='trdroping' value='$trdroping'>
						<div class='col-lg-6'>
							  
								<div class='form-group'>
							  
									<label class='col-sm-4 control-label'>Tanggal </label>
										<div class='col-sm-6'>
											<div class='input-group date'>
												<div class='input-group-addon'>
													<span class='glyphicon glyphicon-th'></span>
												</div>
													<input type='text' class='datepicker' name='tgl_trkasir' id='tgl_trkasir' required='required' value='$tglharini' autocomplete='off'>
											</div>
										</div>
										
									<label class='col-sm-4 control-label'>Kode Transaksi</label>        		
										<div class='col-sm-6'>
											<input type=text name='kd_hid' id='kd_hid' class='form-control' required='required' value='$kdtransaksi' autocomplete='off' Disabled>
										</div>
									<label class='col-sm-4 control-label'>Kode Order</label>        		
										<div class='col-sm-6'>
											<textarea name='kodetx' id='kodetx' class='form-control' rows='1'></textarea>
										</div>	
									<label class='col-sm-4 control-label'>Pelanggan</label>        		
										<div class='col-sm-6'>
											<input type=text name='nm_pelanggan' id='nm_pelanggan' class='typeahead form-control' autocomplete='off'>
										</div>
										
									<label class='col-sm-4 control-label'>Telepon</label>        		
										<div class='col-sm-6'>
											<input type=text name='tlp_pelanggan' id='tlp_pelanggan' class='form-control' autocomplete='off'>
										</div>
										
									<label class='col-sm-4 control-label'>Alamat</label>        		
										<div class='col-sm-6'>
											<textarea name='alamat_pelanggan' id='alamat_pelanggan' class='form-control' rows='2'></textarea>
										</div>
										
									<label class='col-sm-4 control-label'>Catatan</label>        		
										<div class='col-sm-6'>
											<textarea name='ket_trkasir' id='ket_trkasir' class='form-control' rows='2'></textarea>
										</div>
									
									
								</div>
							  
						</div>
						
						<div class='col-lg-6'>
						
						
								<input type=hidden name='id_barang' id='id_barang'>
								<input type=hidden name='stok_barang' id='stok_barang' >
								<input type=hidden name='id_admin' id='id_admin' value='$_SESSION[idadmin]'>
								<input type=hidden name='komisi_dtrkasir' id='komisi_dtrkasir'>
								<input type=hidden name='level' id='level' value='$_SESSION[level]'>
								
								<div class='form-group'>
								
									<label class='col-sm-4 control-label'>Jenis Transaksi</label>        		
									 <div class='col-sm-7'>									    
										    <select name='jns_transaksi' id='jns_transaksi' class='form-control'>										     
										        <option value='8'>Mutasi</option>
										        
										    </select>										
									 </div>	
									 	
									<label class='col-sm-4 control-label'>Kode Barang</label>        		
									 <div class='col-sm-7'>
									 <div class='input-group'>
										<input type=text name='kd_barang' id='kd_barang' class='form-control' autocomplete='off'>
										<div class='input-group-addon'>
											<button type=button data-toggle='modal' data-target='#ModalItem' href='#' id='kode'><span class='glyphicon glyphicon-search'></span></button>
										</div>
										</div>
									 </div>
									 
									<label class='col-sm-4 control-label'>Nama Barang</label>        		
											<div class='col-sm-7'>
													<input type=text name='nmbrg_dtrkasir' id='nmbrg_dtrkasir' class='form-control' autocomplete='off'>
											</div>
											
									<label class='col-sm-4 control-label'>ETALASE</label>        		
											<div class='col-sm-7'>
													<select class='form-control' name='jenisobat' id='jenisobat'>
													    <option></option>
													    ";

                                        while ($rj = mysqli_fetch_array($tampil_jenisobat)) {
                                            echo "<option value='$rj[jenisobat]'>$rj[jenisobat]</option>";
                                        }

                                        echo "
													</select>
											</div>
											
														
									<label class='col-sm-4 control-label'>Qty</label>        		
										<div class='col-sm-7'>
											<input type='number' name='qty_dtrkasir' id='qty_dtrkasir' class='form-control' autocomplete='off'>
										</div>
									
									";
                            $lupa = $_SESSION['level'];
                            if ($lupa == 'pemilik') {
                                echo "
									<label class='col-sm-4 control-label'>Satuan</label>        		
										<div class='col-sm-7'>
											<input type=text name='sat_dtrkasir' id='sat_dtrkasir' class='form-control' autocomplete='off'>
										</div>
										
									<label class='col-sm-4 control-label'>Harga</label>        		
										<div class='col-sm-7'>
											<input type=text name='hrgjual_dtrkasir' id='hrgjual_dtrkasir' class='form-control' autocomplete='off'>
										</div>";
                            } else {
                                echo "
									<label class='col-sm-4 control-label'>Satuan</label>        		
										<div class='col-sm-7'>
											<input type=text name='sat_dtrkasir' id='sat_dtrkasir' class='form-control' autocomplete='off' disabled>
										</div>
										
									<label class='col-sm-4 control-label'>Harga</label>        		
										<div class='col-sm-7'>
											<input type=text name='hrgjual_dtrkasir' id='hrgjual_dtrkasir' class='form-control' autocomplete='off' disabled>
										</div>";
                }
                echo "
									<label class='col-sm-4 control-label'>Disc</label>        		
										<div class='col-sm-7'>
											<input type=text name='disc' id='disc' class='form-control' autocomplete='off'>											
										</div>
										
									<label class='col-sm-4 control-label'>batch</label>        		
										<div class='col-sm-7'>
										    <div class='input-group'>
    											<input type=text name='no_batch' id='no_batch' class='form-control' autocomplete='off'>
    											<div class='input-group-addon'>
        											<button type=button data-toggle='modal' data-target='#ModalBatch' href='#' id='caribatch'><span class='glyphicon glyphicon-search'></span></button>
        										</div>
        									</div>
										</div>
                                    
                                    									
									<label class='col-sm-4 control-label'>Exp. Date</label>        		
										<div class='col-sm-7'>
											<input type='date' class='datepicker' name='exp_date' id='exp_date' required='required' autocomplete='off'>
											</p>
												<div class='buttons'>
													<button type='button' class='btn btn-success right-block' onclick='simpan_detail();'>[F1] SIMPAN DETAIL</button>
												</div>
										</div>	
										
								</div>
								
						</div>
						</form>
							  
				</div> 
				
				<div id='tabeldata'>
				
			</div>";
            
            break;

    }
}
?>
<!-- Modal itemmat -->
<div id="ModalItem" class="modal fade" role="dialog">
    <div class="modal-lg modal-dialog" style="width:90%">
        <div class="modal-content table-responsive">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">PILIH ITEM BARANG</h4>

                <div id="box">
                    <CENTER><strong>MySIFA PROFIT ANALYSIS</strong></CENTER><br>
                    <center><button type="button" class="btn btn-info">PROFIT>30%</button>
                        <button type="button" class="btn btn-success">PROFIT = 25 - 30 % </button>
                        <button type="button" class="btn btn-warning">PROFIT = 20 - 25%"</button>
                        <button type="button" class="btn btn-danger">PROFIT < 20% </button>
                    </center>
                </div>
            </div>



            <div class="modal-body table-responsive">
                <table id="example" class="table table-condensed table-bordered table-striped table-hover">

                    <thead>
                        <tr class="judul-table">
                            <th style="vertical-align: middle; background-color: #008000; text-align: center; ">No</th>
                            <th style="vertical-align: middle; background-color: #008000; text-align: left; ">Kode</th>
                            <th style="vertical-align: middle; background-color: #008000; text-align: left; ">Nama Barang</th>
                            <th style="vertical-align: middle; background-color: #008000; text-align: right; ">Qty</th>
                            <th style="vertical-align: middle; background-color: #008000; text-align: center; ">Satuan</th>
                            <th style="vertical-align: middle; background-color: #008000; text-align: center; ">Harga Sat</th>
                            <th style="vertical-align: middle; background-color: #008000; text-align: center; ">Harga</th>
                            <th style="vertical-align: middle; background-color: #008000; text-align: center; ">Komisi</th>
                            <th style="vertical-align: middle; background-color: #008000; text-align: center; ">Komposisi</th>
                            <th style="vertical-align: middle; background-color: #008000; text-align: center; ">Pilih</th>
                        </tr>
                    </thead>
                    <tbody>
                         <?php
                            // 		 $no = 1;
                            // 		 $tampil_dproyek = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT * FROM barang ORDER BY id_barang ASC");
                            // 		 while ($rd = mysqli_fetch_array($tampil_dproyek)) {
                            // 		 	$Q = intval(($rd['hrgjual_barang'] - $rd['hrgsat_barang']) / $rd['hrgsat_barang']);
                            // 		 	$harga1 = format_rupiah($rd['hrgjual_barang']);
                            // 		 	$komisi = format_rupiah($rd['komisi']);
                            // 		 	$hargabeli = format_rupiah(intval($rd['hrgsat_barang']));

                            // 		 	echo "<tr style='font-size: 13px;'>
                            // 		 				     <td align=center>$no</td>
                            // 		 					 <td width='20px'>$rd[kd_barang]</td>
                            // 		 					 <td>$rd[nm_barang]</td>
                            // 		 					 <td align=right id='stok_barang'><div id='stok_$rd[id_barang]'>$rd[stok_barang]</div></td>
                            // 		 					 <td align=center>$rd[sat_barang]</td>";
                            // 		 	$lupa = $_SESSION['level'];
                            // 		 	if ($lupa == 'pemilik'){
                            // 		 		echo "	 <td align=center>$hargabeli</td></td>";
                            // 		 	}
                            // 		 	if ($Q <= 0.3) {
                            // 		 		echo " <td style='background-color:#ff003f;'align='center'><strong>$harga1</strong></td> ";
                            // 		 	} elseif ($Q > 0.3 && $Q <= 1) {
                            // 		 		echo "  <td style='background-color:#f39c12;' align='center'><strong>$harga1</strong></td>";
                            // 		 	} elseif ($Q > 1 && $Q <= 2) {
                            // 		 		echo "  <td style='background-color:#00ff3f;' align='center'><strong>$harga1</strong></td>";
                            // 		 	} elseif ($Q > 2) {
                            // 		 		echo "  <td style='background-color:#00bfff;' align='center'><strong>$harga1</strong></td>";
                            // 		 	}
                            // 		 	echo "

                            // 		 					 <td align=right>$komisi</td>
                            // 		 					 <td align=center>$rd[indikasi]</td>
                            // 		 					 <td align=center>

                            // 		  <button class='btn btn-xs btn-info' id='pilihbarang'
                            // 		 	 data-id_barang='$rd[id_barang]'
                            // 		 	 data-kd_barang='$rd[kd_barang]'
                            // 		 	 data-nm_barang='$rd[nm_barang]'
                            // 		 	 data-stok_barang='$rd[stok_barang]'
                            // 		 	 data-sat_barang='$rd[sat_barang]'
                            // 		 	 data-jenisobat ='$rd[jenisobat]'
                            // 		 	 data-indikasi='$rd[indikasi]'
                            // 		 	 data-hrgjual_barang='$rd[hrgjual_barang]'
                            // 		 	 data-hrgjual_barang1='$rd[hrgjual_barang1]'
                            // 		 	 data-hrgjual_barang2='$rd[hrgjual_barang2]'
                            // 		 	 data-hrgjual_barang3='$rd[hrgjual_barang3]'
                            // 		 	 data-komisi='$rd[komisi]'>
                            // 		 	 <i class='fa fa-check'></i>
                            // 		 	 </button>

                            // 		 					</td>
                            // 		 				</tr>";
                            // 		 	$no++;
                            // 		 }
                            echo "</tbody></table>";
                            ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<!-- end modal item -->

<!-- modal batch -->
<div id="ModalBatch" class="modal fade" role="dialog">
    <div class="modal-lg modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">PILIH NOMOR BATCH</h4>
            </div>
	  
            <div class="modal-body table-responsive">
		        <table id="table_batch" class="table table-condensed table-bordered table-striped table-hover" >
		            <thead>
						<tr class="judul-table">
							<th style="vertical-align: middle; background-color: #008000; text-align: center; ">No</th>
							<th style="vertical-align: middle; background-color: #008000; text-align: left; ">Kode Barang</th>
							<th style="vertical-align: middle; background-color: #008000; text-align: left; ">Nama Barang</th>
							<th style="vertical-align: middle; background-color: #008000; text-align: center; ">Nomor Batch</th>
							<th style="vertical-align: middle; background-color: #008000; text-align: center; ">Exp Date</th>
							<th style="vertical-align: middle; background-color: #008000; text-align: center; ">Qty</th>
							<th style="vertical-align: middle; background-color: #008000; text-align: center; ">Pilih</th>
                        </tr>
					</thead>
					<tbody>
					
					</tbody>
				</table>
            </div>
        </div>
    </div>
</div>
<!-- end modal batch -->

<script type="text/javascript">
    $(function() {
        $(".datepicker").datepicker({
            format: 'yyyy-mm-dd',
            autoclose: true,
            todayHighlight: true,
        });
    });
</script>

<script>
    $(document).ready(function() {
        if ($('#trdroping').length && $('#trdroping').val() == '') {
            $('#trdroping').val('tambah');
        }

        tabel_detail();
        $("#example").DataTable();
        
        var trdroping = $('#trdroping').val();
        if(trdroping != ''){
            getcabang();
            document.querySelector("#jns_transaksi option[value='8']").selected = true;
            
        }
    });

    $('select[name="jns_transaksi"]').on('change', function() {
        var jns_transaksi = $('select[name="jns_transaksi"]').val();
        var kd_barang = $('#kd_barang').val();
        let nm_barang = $('#nmbrg_dtrkasir').val();

        if (kd_barang != '') {

            $.ajax({
                url: 'modul/mod_dropping/autonamabarang_enter.php',
                type: 'post',
                data: {
                    'nm_barang': nm_barang
                },
            }).success(function(response) {
                let data = $.parseJSON(response);
                // let data = JSON.parse(response)
                let qty_default = "1";

                for (let i = 0; i < data.length; i++) {
                    data = data[i];
                    document.getElementById('id_barang').value = data.id_barang;
                    document.getElementById('kd_barang').value = data.kd_barang;
                    document.getElementById('nmbrg_dtrkasir').value = data.nm_barang;
                    document.getElementById('stok_barang').value = data.stok_barang;
                    document.getElementById('qty_dtrkasir').value = qty_default;
                    document.getElementById('sat_dtrkasir').value = data.sat_barang;
                    document.getElementById('hrgjual_dtrkasir').value = data.hrgsat_barang;
                }

            });
        }
    });

    // Autocomplete nama obat
    $('#nmbrg_dtrkasir').typeahead({
        source: function(query, process) {
            return $.post('modul/mod_dropping/autonamabarang.php', {
                query: query
            }, function(data) {

                data = $.parseJSON(data);
                return process(data);

            });
        }
    });

    // event enter nama obat
    $(document).ready(function() {
        $('#nmbrg_dtrkasir').on('keydown', function(e) {
            if (e.which == 13) {
                let nm_barang = $('#nmbrg_dtrkasir').val();
                let jns_transaksi = $('select[name="jns_transaksi"]').val();
                let trdroping = $('#trdroping').val();
                
                $.ajax({
                    url: 'modul/mod_dropping/autonamabarang_enter.php',
                    type: 'post',
                    data: {
                        'nm_barang': nm_barang
                    },
                }).success(function(response) {
                    let data = $.parseJSON(response);
                    // let data = JSON.parse(response)
                    let qty_default = "1";
                    
                    for (let i = 0; i < data.length; i++) {
                        data = data[i];
                        document.getElementById('id_barang').value = data.id_barang;
                        document.getElementById('kd_barang').value = data.kd_barang;
                        document.getElementById('nmbrg_dtrkasir').value = data.nm_barang;
                        document.getElementById('stok_barang').value = data.stok_barang;
                        document.getElementById('qty_dtrkasir').value = qty_default;
                        document.getElementById('sat_dtrkasir').value = data.sat_barang;
                        document.getElementById('hrgjual_dtrkasir').value = data.hrgsat_barang;
                    }

                });
            }
        })
    });

    $('#nmbrg_dtrkasir_enter').on('click', function() {
        let nm_barang = $('#nmbrg_dtrkasir').val();
        let jns_transaksi = $('select[name="jns_transaksi"]').val();
        let trdroping = $('#trdroping').val();
        
        $.ajax({
            url: 'modul/mod_dropping/autonamabarang_enter.php',
            type: 'post',
            data: {
                'nm_barang': nm_barang
            },
        }).success(function(response) {
            let data = $.parseJSON(response);
            // let data = JSON.parse(response)
            let qty_default = "1";

            for (let i = 0; i < data.length; i++) {
                data = data[i];
                document.getElementById('id_barang').value = data.id_barang;
                document.getElementById('kd_barang').value = data.kd_barang;
                document.getElementById('nmbrg_dtrkasir').value = data.nm_barang;
                document.getElementById('stok_barang').value = data.stok_barang;
                document.getElementById('qty_dtrkasir').value = qty_default;
                document.getElementById('sat_dtrkasir').value = data.sat_barang;
                document.getElementById('hrgjual_dtrkasir').value = data.hrgsat_barang;
            }

        });
    })

    $(document).on('click', '#kode', function() {
        $("#example").DataTable().destroy();

        $("#example").DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                "url": "modul/mod_dropping/barang-serverside.php?action=table_data",
                "dataType": "JSON",
                "type": "POST"
            },
            "rowCallback": function(row, data, index) {
                return row;
            },
            columns: [{
                    "data": "no",
                    "className": 'text-center',
                },
                {
                    "data": "kd_barang"
                },
                {
                    "data": "nm_barang"
                },
                {
                    "data": "stok_barang",
                    "className": 'text-center',
                },
                {
                    "data": "sat_barang",
                    "className": 'text-center',
                },
                {
                    "data": "hrgsat_barang",
                    "className": 'text-right',
                    "visible": false,
                    "render": function(data, type, row) {
                        return formatRupiah(data);
                    }
                },
                {
                    "data": "hrgsat_barang",
                    "className": 'text-right',
                    "render": function(data, type, row) {
                        return formatRupiah(data);
                    }
                },
                {
                    "data": "komisi",
                    "className": 'text-right',
                    "render": function(data, type, row) {
                        return formatRupiah(data);
                    }
                },
                {
                    "data": "indikasi",
                    "className": 'text-center'
                },
                {
                    "data": "pilih",
                    "className": 'text-center'
                },
            ],
            "footerCallback": function(row, data, start, end, display) {
                // console.log(row);
            }
        })

    });

    $(document).on('click', '#pilihbarang', function() {

        var id_barang = $(this).data('id_barang');
        var kd_barang = $(this).data('kd_barang');
        var nm_barang = $(this).data('nm_barang');
        var stok_barang = $(this).data('stok_barang');
        var sat_barang = $(this).data('sat_barang');
        var hrgsat_barang = $(this).data('hrgsat_barang');
        var jenisobat = $(this).data('jenisobat');
        var komisi_dtrkasir = $(this).data('komisi');
        var qty_default = "1";
        let jns_transaksi = $('select[name="jns_transaksi"]').val();


        document.getElementById('id_barang').value = id_barang;
        document.getElementById('kd_barang').value = kd_barang;
        document.getElementById('nmbrg_dtrkasir').value = nm_barang;
        document.getElementById('stok_barang').value = stok_barang;
        document.getElementById('qty_dtrkasir').value = qty_default;
        document.getElementById('sat_dtrkasir').value = sat_barang;
        document.getElementById('hrgjual_dtrkasir').value = hrgsat_barang;
        document.getElementById('komisi_dtrkasir').value = komisi_dtrkasir;
        $('#jenisobat').val(jenisobat).change();

        // $.ajax({
        // 	url: 'modul/mod_dropping/gettabel_barang.php',
        // 	type: 'post',
        // 	data: {
        // 		'id_barang': id_barang
        // 	},
        // 	dataType: 'JSON',
        // 	success: function(response) {
        // 		document.getElementById('id_barang').value = response[0].id_barang;
        // 		document.getElementById('kd_barang').value = response[0].kd_barang;
        // 		document.getElementById('nmbrg_dtrkasir').value = response[0].nm_barang;
        // 		document.getElementById('stok_barang').value = response[0].stok_barang;

        // 		document.getElementById('qty_dtrkasir').value = qty_default;
        // 		document.getElementById('sat_dtrkasir').value = response[0].sat_barang;
        // 		document.getElementById('hrgjual_dtrkasir').value = response[0].hrgjual_barang;
        // 		document.getElementById('indikasi').value = response[0].indikasi;

        // 		$('#jenisobat').val(response[0].jenisobat).change();
        // 		document.getElementById('komisi_dtrkasir').value = response[0].komisi;
        // 	}

        // });

        //hilangkan modal
        $(".close").click();

    });


    function simpan_detail() {

        let kd_trkasir = document.getElementById('kd_trkasir').value;
        let id_barang = document.getElementById('id_barang').value;
        let kd_barang = document.getElementById('kd_barang').value;
        let nmbrg_dtrkasir = document.getElementById('nmbrg_dtrkasir').value;
        let stok_barang = document.getElementById('stok_barang').value;
        let qty_dtrkasir = document.getElementById('qty_dtrkasir').value;
        let sat_dtrkasir = document.getElementById('sat_dtrkasir').value;
        let hrgjual_dtrkasir = document.getElementById('hrgjual_dtrkasir').value;
        let disc = document.getElementById('disc').value;
        let no_batch = document.getElementById('no_batch').value;
        let exp_date = document.getElementById('exp_date').value;
        let jenisobat = document.getElementById('jenisobat').value;
        let komisi_dtrkasir = document.getElementById('komisi_dtrkasir').value;
        let id_admin = document.getElementById('id_admin').value;

        var trdroping = document.getElementById('trdroping').value;
        if(trdroping != ''){
            var jns_transaksi = 8;
        } else {
            var jns_transaksi = $('select[name="jns_transaksi"]').val();
            
        }
        
        if (nmbrg_dtrkasir == "") {
            alert('Belum ada Item terpilih');
        } else if (qty_dtrkasir == "") {
            alert('Qty tidak boleh kosong');
        } else if (parseInt(stok_barang) < parseInt(qty_dtrkasir)) {
            alert('Stok barang tidak mencukupi');
        } else if (parseInt(disc) > 100) {
            alert('Input Diskon lebih kecil dari 100');
        }    
        else if (level == "petugas" && jenisobat == "OKT") {
  			alert('Obat ini hanya bisa di proses Apoteker');
        } else {


            $.ajax({

                type: 'post',
                url: "modul/mod_dropping/simpandetail_trkasir.php",
                data: {
                    'kd_trkasir': kd_trkasir,
                    'id_barang': id_barang,
                    'kd_barang': kd_barang,
                    'nmbrg_dtrkasir': nmbrg_dtrkasir,
                    'qty_dtrkasir': qty_dtrkasir,
                    'sat_dtrkasir': sat_dtrkasir,
                    'hrgjual_dtrkasir': hrgjual_dtrkasir,
                    'disc': disc,
                    'no_batch': no_batch,
                    'exp_date': exp_date,
                    'jenisobat': jenisobat,
                    'komisi_dtrkasir': komisi_dtrkasir,
                    'id_admin': id_admin,
                    'tipe': jns_transaksi,
                    'trdroping': trdroping,
                },
                success: function(data) {
                    //alert('Tambah data detail berhasil');
                    document.getElementById("id_barang").value = "";
                    document.getElementById("kd_barang").value = "";
                    document.getElementById("nmbrg_dtrkasir").value = "";
                    document.getElementById("qty_dtrkasir").value = "";
                    document.getElementById("sat_dtrkasir").value = "";
                    document.getElementById("hrgjual_dtrkasir").value = "";
                    document.getElementById("disc").value = "";
                    document.getElementById("no_batch").value = "";
                    document.getElementById("exp_date").value = "";
                    document.getElementById("komisi_dtrkasir").value = "";

                    // $('#jenisobat').val().change();
                    tabel_detail();

                    let displayStok = stok_barang - qty_dtrkasir;
                    $('#stok_' + id_barang).html(displayStok);
                }
            });
        }
    }



    $(document).on('click', '#hapusdetail', function() {

        var id_dtrkasir = $(this).data('id_dtrkasir');
        var id_barang = $(this).data('id_barang');

        $.ajax({
            type: 'post',
            url: "modul/mod_dropping/hapusdetail_trkasir.php",
            data: {
                id_dtrkasir: id_dtrkasir
            },

            success: function(data) {
                //setelah simpan data, tabel_detail data terbaru
                //alert('Hapus data detail berhasil');
                tabel_detail();
                $('#stok_' + id_barang).html(data);
                //hilangkan modal
                $(".close").click();
            }
        });

    });



    //fungsi tabel detail
    function tabel_detail() {

        var kd_trkasir = document.getElementById('kd_trkasir').value;
        var stt_aksi = document.getElementById('stt_aksi').value;
        var trdroping = document.getElementById('trdroping').value;

        $.ajax({
            url: 'modul/mod_dropping/tbl_detail.php',
            type: 'post',
            data: {
                'kd_trkasir': kd_trkasir,
                'stt_aksi': stt_aksi,
                'trdroping': trdroping
            },
            success: function(data) {
                $('#tabeldata').html(data);
            }

        });
    }

    //auto pelanggan
    $('#nm_pelanggan').typeahead({
        source: function(query, process) {
            return $.get('modul/mod_dropping/autopelanggan.php', {
                query: query
            }, function(data) {

                console.log(data);
                data = $.parseJSON(data);
                return process(data);

            });
        }
    });


    //enter pelanggan
    $('#nm_pelanggan').keydown(function(e) {
        if (e.which == 13) { // e.which == 13 merupakan kode yang mendeteksi ketika anda   // menekan tombol enter di keyboard
            //letakan fungsi anda disini

            var nm_pelanggan = $("#nm_pelanggan").val();
            $.ajax({
                url: 'modul/mod_dropping/autopelanggan_enter.php',
                type: 'post',
                data: {
                    'nm_pelanggan': nm_pelanggan
                },
            }).success(function(data) {

                var json = data;
                //replace array [] menjadi ''
                var res1 = json.replace("[", "");
                var res2 = res1.replace("]", "");
                //INI CONTOH ARRAY JASON const json = '{"result":true, "count":42}';
                datab = JSON.parse(res2);
                document.getElementById('nm_pelanggan').value = datab.nm_pelanggan;
                document.getElementById('tlp_pelanggan').value = datab.tlp_pelanggan;
                document.getElementById('alamat_pelanggan').value = datab.alamat_pelanggan;
            });

        }
    });

    $('#kd_barang').keydown(function(e) {
        if (e.which == 13) { // e.which == 13 merupakan kode yang mendeteksi ketika anda   // menekan tombol enter di keyboard
            //letakan fungsi anda disini

            var kd_brg = $("#kd_barang").val();
            $.ajax({
                url: 'modul/mod_dropping/autobarang.php',
                type: 'post',
                data: {
                    'kd_brg': kd_brg
                },
            }).success(function(data) {

                var json = data;
                //replace array [] menjadi ''
                var res1 = json.replace("[", "");
                var res2 = res1.replace("]", "");
                //INI CONTOH ARRAY JASON const json = '{"result":true, "count":42}';
                datab = JSON.parse(res2);
                document.getElementById('id_barang').value = datab.id_barang;
                document.getElementById('nmbrg_dtrkasir').value = datab.nm_barang;
                document.getElementById('stok_barang').value = datab.stok_barang;
                document.getElementById('qty_dtrkasir').value = "1";
                document.getElementById('sat_dtrkasir').value = datab.sat_barang;
                document.getElementById('hrgjual_dtrkasir').value = datab.hrgsat_barang;
                document.getElementById('indikasi').value = datab.indikasi;

            });

        }
    });


    function simpan_transaksi() {

        var id_trkasir = document.getElementById('id_trkasir').value;
        var kd_trkasir = document.getElementById('kd_trkasir').value;
        var petugas = document.getElementById('petugas').value;
        var shift = document.getElementById('shift').value;
        var tgl_trkasir = document.getElementById('tgl_trkasir').value;
        var nm_pelanggan = document.getElementById('nm_pelanggan').value;
        var tlp_pelanggan = document.getElementById('tlp_pelanggan').value;
        var alamat_pelanggan = document.getElementById('alamat_pelanggan').value;
        var kodetx = document.getElementById('kodetx').value;
        var ttl_trkasir = document.getElementById('ttl_trkasir').value;
        var diskon2 = document.getElementById('diskon2').value;
        var dp_bayar = document.getElementById('dp_bayar').value;
        var sisa_bayar = document.getElementById('sisa_bayar').value;
        var ket_trkasir = document.getElementById('ket_trkasir').value;
        var stt_aksi = document.getElementById('stt_aksi').value;
        var id_carabayar = document.getElementById('id_carabayar').value;


        var ttl_trkasir1 = ttl_trkasir.replace(".", "");
        var dp_bayar1 = dp_bayar.replace(".", "");
        var sisa_bayar1 = sisa_bayar.replace(".", "");

        var ttl_trkasir1x = ttl_trkasir1.replace(".", "");
        var dp_bayar1x = dp_bayar1.replace(".", "");
        var sisa_bayar1x = sisa_bayar1.replace(".", "");

        var trdroping = document.getElementById('trdroping').value;
        
       

            $.ajax({

                type: 'post',
                url: "modul/mod_dropping/aksi_dropping.php",
                dataType: 'json',
                data: {
                    'id_trkasir': id_trkasir,
                    'kd_trkasir': kd_trkasir,
                    'tgl_trkasir': tgl_trkasir,
                    'petugas': petugas,
                    'shift': shift,
                    'nm_pelanggan': nm_pelanggan,
                    'tlp_pelanggan': tlp_pelanggan,
                    'alamat_pelanggan': alamat_pelanggan,
                    'kodetx': kodetx,
                    'ttl_trkasir': ttl_trkasir1x,
                    'diskon2': diskon2,
                    'dp_bayar': dp_bayar1x,
                    'sisa_bayar': sisa_bayar1x,
                    'ket_trkasir': ket_trkasir,
                    'stt_aksi': stt_aksi,
                    'id_carabayar': id_carabayar,
                    'trdroping': trdroping,
                },
                success: function(data) {
                    console.log(data);
                    if (data.message == 'success') {
                        window.open('modul/mod_laporan/struk.php?kd_trkasir=' + kd_trkasir, 'nama window', 'width=400,height=700,toolbar=no,location=no,directories=no,status=no,menubar=no, scrollbars=no,resizable=yes,copyhistory=no');
                        alert('Proses berhasil !');
                        window.location = 'media_admin.php?module=dropping';

                    } else if (data.message == 'droping') {
                        alert('Proses berhasil !');
                        window.location = 'media_admin.php?module=dropping';
                    } else {
                        window.location.reload();
                        
                    }

                }

                //	success: function(data) {

                //	window.open('modul/mod_laporan/struk.php?kd_trkasir='+kd_trkasir, 'nama window','
                //  	width=400,height=700,toolbar=no,location=no,directories=no,status=no,menubar=no,
                //  	scrollbars=no,resizable=yes,copyhistory=no');
                //	alert('Proses berhasil !');window.location='media_admin.php?module=dropping';

                //	}
            });
        

    }

    $(document).on('click', '#caribatch', function() {
	    let kd_barang = $('#kd_barang').val();
		$("#table_batch").DataTable().destroy();

		$("#table_batch").DataTable({
			processing: false,
			serverSide: true,
			ajax: {
				"url": "modul/mod_dropping/batch_serverside.php?action=table_data&id="+kd_barang,
				"dataType": "JSON",
				"type": "POST"
			},
			
			columns: [{
					"data": "no",
					"className": 'text-center',
				},
				{
					"data": "kd_barang"
				},
				{
					"data": "nm_barang"
				},
				{
					"data": "no_batch",
					"className": 'text-center',
				},
				{
					"data": "exp_date",
					"className": 'text-center',
				},
				{
					"data": "qty",
					"className": 'text-center',
				},
				{
					"data": "pilih",
					"className": 'text-center'
				},
			],
			
		})

	});
	
	$(document).on('click', '#pilihbatch', function(){
        var id_batch    = $(this).data('id_batch');
        var kd_barang   = $(this).data('kd_barang');
	    var nm_barang   = $(this).data('nm_barang');
	    var no_batch    = $(this).data('no_batch');
	    var exp_date    = $(this).data('exp_date');
	    
	    document.getElementById("no_batch").value = no_batch;
	    document.getElementById("exp_date").value = exp_date;
	    
	    $(".close").click();
    });

    $(document).on('change', '#id_carabayar', function(e){
        var id_carabayar = $(this).val();
        if(id_carabayar == '4'){
            
            $.ajax({
                url: 'modul/mod_dropping/autocabang.php',
                type: 'post',
            }).success(function(response) {
                let data = $.parseJSON(response);
                
                for (let i = 0; i < data.length; i++) {
                    data = data[i];
    
                    document.getElementById('nm_pelanggan').value = data.nm_cabang;
                    document.getElementById('tlp_pelanggan').value = data.tlp_cabang;
                    document.getElementById('alamat_pelanggan').value = data.alamat_cabang;
                    
                }
    
            });
        }
    });
    
    function getcabang() {
        $.ajax({
            url: 'modul/mod_dropping/autocabang.php',
            type: 'post',
        }).success(function(response) {
            let data = $.parseJSON(response);
                                
            for (let i = 0; i < data.length; i++) {
                data = data[i];
                    
                document.getElementById('nm_pelanggan').value = data.nm_cabang;
                document.getElementById('tlp_pelanggan').value = data.tlp_cabang;
                document.getElementById('alamat_pelanggan').value = data.alamat_cabang;
                                    
            }
                    
        });
    }
    
    function cetakstruk() {

        var kd_trkasir = document.getElementById('kd_trkasir').value;

        //window.open("modul/mod_laporan/struk.php?kd_trkasir="+kd_trkasir,"_blank");
        window.open('modul/mod_laporan/struk.php?kd_trkasir=' + kd_trkasir, 'nama window', 'width=400,height=700,toolbar=no,location=no,directories=no,status=no,menubar=no, scrollbars=no,resizable=yes,copyhistory=no');

    }

    function cetakstrukresep() {

        var kd_trkasir = document.getElementById('kd_trkasir').value;

        //window.open("modul/mod_laporan/struk.php?kd_trkasir="+kd_trkasir,"_blank");
        window.open('modul/mod_laporan/strukresep.php?kd_trkasir=' + kd_trkasir, 'nama window', 'width=400,height=700,toolbar=no,location=no,directories=no,status=no,menubar=no, scrollbars=no,resizable=yes,copyhistory=no');

    }
    document.addEventListener('keydown', function(event) {
        if (event.key === 'F1' || event.keyCode === 112) {
            event.preventDefault(); // Mencegah help browser muncul
            simpan_detail();
        }
    });
	
	document.addEventListener('keydown', function(event) {
        if (event.key === 'F2' || event.keyCode === 113) {
            event.preventDefault(); // Mencegah help browser muncul
            $('#dp_bayar').focus();
        }
    });
    
    document.addEventListener('keydown', function(event) {
        if (event.key === 'F3' || event.keyCode === 114) {
            event.preventDefault(); // Mencegah help browser muncul
            // simpan_detail();
            simpan_transaksi();
        }
    });
</script>