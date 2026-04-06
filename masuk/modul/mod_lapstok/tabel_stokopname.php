<?php
session_start();
if (empty($_SESSION['username']) and empty($_SESSION['passuser'])) {
    echo "<link href=../css/style.css rel=stylesheet type=text/css>";
    echo "<div class='error msg'>Untuk mengakses Modul anda harus login.</div>";
} else {
include "../../../configurasi/koneksi.php";

// Support both POST (AJAX) and GET (direct URL access)
$jenisobat = isset($_POST['jenisobat']) ? $_POST['jenisobat'] : (isset($_GET['jenisobat']) ? $_GET['jenisobat'] : '');
$tgl = isset($_POST['tgl_awal']) ? $_POST['tgl_awal'] : (isset($_GET['tgl']) ? $_GET['tgl'] : '');

if (empty($jenisobat) || empty($tgl)) {
    echo "<div class='alert alert-warning'>Parameter tidak lengkap. jenisobat=$jenisobat, tgl=$tgl</div>";
    exit;
}

?>
<table id="example10" class="table table-bordered table-striped">
    <thead>
        <tr>
            <th class="text-center">No</th>
            <th class="text-center">Kode Barang</th>
            <th class="text-center">Nama Obat</th>
            <th class="text-center">Satuan</th>

            <?php
                $lupa = $_SESSION['level'];
                if ($lupa == 'pemilik') {
                echo "<th class='text-center'>Stok Sistem</th>";
                }
             ?>

            <th class="text-center">Stok Fisik</th>
            <th class="text-center">Exp Date</th>
            <th class="text-center">jumlah</th>
            <th class="text-center">Submit</th>
        </tr>
    </thead>
    <tbody>
        <?php
        // OPTIMASI: Hanya 4 Query instead of N+1 queries!
        
        // Query 1: Ambil semua barang untuk jenis obat tersebut
        $query = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT * FROM barang a WHERE a.jenisobat='$jenisobat' and stok_barang>0 ORDER BY a.nm_barang");

        // Kumpulkan semua ID barang untuk query berikutnya
        $barang_ids = [];
        $barang_data = [];
        while ($row = mysqli_fetch_array($query)) {
            $barang_ids[] = $row['id_barang'];
            $barang_data[$row['id_barang']] = $row;
        }

        if (empty($barang_ids)) {
            echo "<tr><td colspan='9' class='text-center'>Tidak ada data barang untuk jenis obat tersebut</td></tr>";
        } else {
            $id_list = implode(',', $barang_ids);
            
            // Query 2: Ambil semua stok_opname yang sudah ada (untuk tanggal tersebut)
            $stok_opname_q = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT id_barang FROM stok_opname WHERE tgl_stokopname = '$tgl' AND id_barang IN ($id_list)");
            $sudah_stok_opname = [];
            while ($row = mysqli_fetch_array($stok_opname_q)) {
                $sudah_stok_opname[$row['id_barang']] = true;
            }

            // Query 3: Ambil total pembelian per barang (semua waktu)
            $beli_q = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT id_barang, SUM(qty_dtrbmasuk) as totalbeli FROM trbmasuk_detail WHERE id_barang IN ($id_list) GROUP BY id_barang");
            $total_beli = [];
            while ($row = mysqli_fetch_array($beli_q)) {
                $total_beli[$row['id_barang']] = $row['totalbeli'] ?: 0;
            }

            // Query 4: Ambil total penjualan per barang (semua waktu)
            $jual_q = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT id_barang, SUM(qty_dtrkasir) as totaljual FROM trkasir_detail WHERE id_barang IN ($id_list) GROUP BY id_barang");
            $total_jual = [];
            while ($row = mysqli_fetch_array($jual_q)) {
                $total_jual[$row['id_barang']] = $row['totaljual'] ?: 0;
            }

            // Tampilkan data
            $no = 1;
            $ada_data = false;
            foreach ($barang_data as $id_barang => $lihat) {
                // Skip jika sudah ada di stok_opname
                if (isset($sudah_stok_opname[$id_barang])) {
                    continue;
                }

                $beli = isset($total_beli[$id_barang]) ? $total_beli[$id_barang] : 0;
                $jual = isset($total_jual[$id_barang]) ? $total_jual[$id_barang] : 0;
                $selisih = $beli - $jual;
                $ada_data = true;
        ?>

                <tr>
                    <td class="text-center"><?= $no++; ?></td>
                    <td class="text-center"><?= $lihat['kd_barang']; ?></td>
                    <td class="text-left"><?= $lihat['nm_barang']; ?></td>
                    <td class="text-center"><?= $lihat['sat_barang']; ?></td>

                    <?php
                    $lupa = $_SESSION['level'];
                    if ($lupa == 'pemilik') {
                        echo "<td class='text-center'> $selisih </td>";
                    }
                    ?>


                    <td class="text-center">
                        <input type="number" min="0" class="form-control text-center" name="stok_fisik_<?= $no ?>" id="stok_fisik_<?= $no ?>" value="0">
                    </td>
                    <td class="text-center">
                        <input type="date" class="form-control text-center" name="exp_date_<?= $no ?>" id="exp_date_<?= $no ?>" >
                    </td>
                    <td class="text-center">
                        <input type="number" min="0" class="form-control text-center" name="jml_<?= $no ?>" id="jml_<?= $no ?>" value="0">
                    </td>
                    <td class="text-center">
                        <button type="button" id="pilih_<?= $no ?>" class="btn btn-primary btn-sm" onclick="javascript:simpan_stok_opname('<?= $no ?>')" data-id_barang="<?= $id_barang; ?>" data-kd_barang="<?= $lihat['kd_barang']; ?>" data-hrgsat_barang="<?= $lihat['hrgsat_barang']; ?>">
                            <i class="fa fa-fw fa-check"></i>
                            SIMPAN</button>
                    </td>
                </tr>

        <?php
            }
            
            if (!$ada_data) {
                echo "<tr><td colspan='9' class='text-center'>Semua barang sudah di-stok opname</td></tr>";
            }
        }
        ?>
    </tbody>
</table>
<?php
}
?>
<script>
    $(document).ready(function() {
        $('#example10').dataTable({
            "aLengthMenu": [
                [5, 25, 50, 75, -1],
                [5, 25, 50, 75, "All"]
            ],
            "iDisplayLength": 5
        });

    })
</script>
