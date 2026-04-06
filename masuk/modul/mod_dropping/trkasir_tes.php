<?php
session_start();
if (empty($_SESSION['username']) AND empty($_SESSION['passuser'])){
  echo "<link href=../css/style.css rel=stylesheet type=text/css>";
  echo "<div class='error msg'>Untuk mengakses Modul anda harus login.</div>";
}
else{

$aksi="modul/mod_trkasir/aksi_trkasir.php";
$aksi_trkasir = "masuk/modul/mod_trkasir/aksi_trkasir.php";

$act = isset($_GET['act']) ? $_GET['act'] : '';

switch($act){
  // Tampil Penjualan
  default:
     /* $tgl_awal = date('Y-m-d');

      $tampil_trkasir = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT * FROM trkasir  
        where tgl_trkasir ='$tgl_awal' ");*/


      $tgl_awal = date('Y-m-d');
      $tgl_kemarin = date('Y-m-d', strtotime('-1 days', strtotime($tgl_awal)));
      $tgl_akhir = date('Y-m-d', strtotime('-180 days', strtotime($tgl_awal)));
      
    //   $tampil_trkasir = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT * FROM trkasir  
    //                                 WHERE tgl_trkasir BETWEEN '$tgl_akhir' AND '$tgl_kemarin' ORDER BY id_trkasir DESC");
      
	?>		
			
		<div class="box box-primary box-solid table-responsive">
				<div class="box-header with-border">
					<h3 class="box-title">TRANSAKSI PENJUALAN SEBELUMNYA</h3>
					<div class="box-tools pull-right">
						<button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                    </div><!-- /.box-tools -->
				</div>
				<div class="box-body">
					
                    <a class ="btn btn-warning btn-flat" href="#"></a>
                    <small>* Pembayaran belum lunas</small>
                    <div></div>
				<!--	<a  class ="btn  btn-warning  btn-flat" href="?module=trkasir&act=penjualansebelum">PENJUALAN SEBELUMNYA</a>
					<small>* Pembayaran belum lunas</small> -->
					<br><br>
					
					
					<table id="yasfi" class="table table-bordered table-striped table-responsive" >
						<thead>
							<tr>
								<th>No</th>
								<th>Kode</th>
								<th>Tanggal</th>
								<th>Pelanggan</th>
								<th>Kode Order</th>
								<th>Cara Bayar</th>
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
               
                $(document).ready(function () {
                    $("#yasfi").DataTable({
                        processing: true,
                        serverSide: true,
                        ajax: {
                            "url": "modul/mod_trkasir/penjualansebelum_serverside.php?action=table_data",
                            "dataType": "JSON",
                            "type": "POST"
                        },
                        "rowCallback": function(row, data, index) {
                            // let q = (data['hrgjual_barang'] - data['hrgsat_barang']) / data['hrgsat_barang'];

                            // if(q <= 0.3){
                            //     $(row).find('td:eq(6)').css('background-color', '#ff003f');
                            //     $(row).find('td:eq(6)').css('color', '#ffffff');
                            // } else if(q > 0.3 && q <= 1){
                            //     $(row).find('td:eq(6)').css('background-color', '#f39c12');
                            //     $(row).find('td:eq(6)').css('color', '#ffffff');

                            // } else if(q > 1 && q <= 2){
                            //     $(row).find('td:eq(6)').css('background-color', '#00ff3f');
                            //     $(row).find('td:eq(6)').css('color', '#ffffff');

                            // } else if(q > 2){
                            //     $(row).find('td:eq(6)').css('background-color', '#00bfff');
                            //     $(row).find('td:eq(6)').css('color', '#ffffff');

                            // }

                        },
                        columns: [{
                            "data": "no",
                            "className": 'text-center',
                        },
                            {
                                "data": "kd_trkasir"
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
    break;
	
	}
}
?>
