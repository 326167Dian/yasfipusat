<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['username']) and empty($_SESSION['passuser'])) {
    echo "<link href=../css/style.css rel=stylesheet type=text/css>";
    echo "<div class='error msg'>Untuk mengakses Modul anda harus login.</div>";
} else {
	include "../configurasi/fungsi_generate_kode.php";

    $aksi = "modul/mod_trkasir/aksi_trkasir.php";
    $aksi_trkasir = "masuk/modul/mod_trkasir/aksi_trkasir.php";
    

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
            trkasir on trkasir.kd_trkasir = trdropping.kd_trkasir ORDER BY trdropping.waktu DESC");
        
?>


            <div class="box box-primary box-solid table-responsive">
                <div class="box-header with-border">
                    <h3 class="box-title">DROPPING BARANG</h3>
                    <div class="box-tools pull-right">
                        <button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                    </div><!-- /.box-tools -->
                </div>
                <div class="box-body table-responsive">
                    <a class='btn  btn-success btn-flat' href='?module=dropping&act=tambah&droping=tambah'>TAMBAH DROPPING</a>
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

            break;

    }
}
?>
