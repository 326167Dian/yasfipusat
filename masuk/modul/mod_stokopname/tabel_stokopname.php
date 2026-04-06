<?php
include "../../../configurasi/koneksi.php";

// Terima data dari POST (AJAX) atau GET (direct access)
// support multiple parameter names: shift, start, tgl
$shift = isset($_POST['shift']) ? $_POST['shift'] : (isset($_GET['shift']) ? $_GET['shift'] : '1');
$tgl_awal = isset($_POST['tgl_awal']) ? $_POST['tgl_awal'] : (isset($_GET['start']) ? $_GET['start'] : (isset($_GET['tgl']) ? $_GET['tgl'] : ''));

if (empty($tgl_awal)) {
    echo "<div class='alert alert-warning'>Silakan pilih tanggal terlebih dahulu.</div>";
    exit;
}

// Debug: Tampilkan nilai yang diterima
echo "DEBUG: shift=$shift, tgl_awal=$tgl_awal<br>";

?>
<table id="example10" class="table table-bordered table-striped">
    <thead>
        <tr>
            <th class="text-center">No</th>
            <th class="text-center">Kode Barang</th>
            <th class="text-center">Nama Obat</th>
            <th class="text-center">Satuan</th>
            <th class="text-center">Rak Obat</th>
            <th class="text-center">Qty Terjual</th>
            <th class="text-center">Stok Sistem</th>
            <th class="text-center">Stok Fisik</th>
            <th class="text-center">Submit</th>
        </tr>
    </thead>
    <tbody>
        <?php
        // OPTIMASI: Hanya 4 Query instead of N+1 queries!
        
        // Query 1: Ambil semua barang yang terjual pada tanggal & shift tersebut
        $query = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT trkasir_detail.id_barang, trkasir_detail.kd_barang, barang.nm_barang, barang.sat_barang, barang.jenisobat, barang.hrgsat_barang,
            SUM(trkasir_detail.qty_dtrkasir) as ttlqty 
            FROM trkasir_detail 
            JOIN trkasir ON trkasir_detail.kd_trkasir = trkasir.kd_trkasir
            JOIN barang ON trkasir_detail.id_barang = barang.id_barang
            WHERE trkasir.tgl_trkasir = '$tgl_awal' AND trkasir.shift = '$shift'
            GROUP BY trkasir_detail.kd_barang
            ORDER BY barang.nm_barang");

        // Kumpulkan semua ID barang untuk query berikutnya
        $barang_ids = [];
        $barang_data = [];
        while ($row = mysqli_fetch_array($query)) {
            $barang_ids[] = $row['id_barang'];
            $barang_data[$row['id_barang']] = $row;
        }

        if (empty($barang_ids)) {
            echo "<tr><td colspan='9' class='text-center'>Tidak ada data penjualan pada tanggal tersebut</td></tr>";
        } else {
            $id_list = implode(',', $barang_ids);
            
            // Query 2: Ambil semua stok_opname yang sudah ada (untuk tanggal & shift tersebut)
            $stok_opname_q = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT id_barang FROM stok_opname 
                WHERE shift = '$shift' AND tgl_stokopname = '$tgl_awal' AND id_barang IN ($id_list)");
            $sudah_stok_opname = [];
            while ($row = mysqli_fetch_array($stok_opname_q)) {
                $sudah_stok_opname[$row['id_barang']] = true;
            }

            // Query 3: Ambil total pembelian per barang (semua waktu)
            $beli_q = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT id_barang, SUM(qty_dtrbmasuk) as totalbeli 
                FROM trbmasuk_detail WHERE id_barang IN ($id_list) GROUP BY id_barang");
            $total_beli = [];
            while ($row = mysqli_fetch_array($beli_q)) {
                $total_beli[$row['id_barang']] = $row['totalbeli'] ?: 0;
            }

            // Query 4: Ambil total penjualan per barang (semua waktu)
            $jual_q = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT id_barang, SUM(qty_dtrkasir) as totaljual 
                FROM trkasir_detail WHERE id_barang IN ($id_list) GROUP BY id_barang");
            $total_jual = [];
            while ($row = mysqli_fetch_array($jual_q)) {
                $total_jual[$row['id_barang']] = $row['totaljual'] ?: 0;
            }

            // Tampilkan data
            $no = 1;
            foreach ($barang_data as $id_barang => $lihat) {
                // Skip jika sudah ada di stok_opname
                if (isset($sudah_stok_opname[$id_barang])) {
                    continue;
                }

                $beli = isset($total_beli[$id_barang]) ? $total_beli[$id_barang] : 0;
                $jual = isset($total_jual[$id_barang]) ? $total_jual[$id_barang] : 0;
                $selisih = $beli - $jual;
                $terjual = $lihat['ttlqty'];
        ?>

                <tr>
                    <td class="text-center"><?= $no++; ?></td>
                    <td class="text-center"><?= $lihat['kd_barang']; ?></td>
                    <td class="text-left"><?= $lihat['nm_barang']; ?></td>
                    <td class="text-center"><?= $lihat['sat_barang']; ?></td>
                    <td class="text-center"><?= $lihat['jenisobat']; ?></td>
                    <td class="text-center"><?= $terjual; ?></td>
                    <td class="text-center"><?= $selisih; ?></td>
                    <td class="text-center">
                        <input type="number" min="0" class="form-control text-center" name="stok_fisik_<?= $no ?>" id="stok_fisik_<?= $no ?>" value="0">
                    </td>
                    <td class="text-center">
                        <button type="button" id="pilih_<?= $no ?>" class="btn btn-primary btn-sm" onclick="javascript:simpan_stok_opname('<?= $no ?>')" 
                            data-id_barang="<?= $id_barang; ?>" 
                            data-kd_barang="<?= $lihat['kd_barang']; ?>" 
                            data-hrgsat_barang="<?= $lihat['hrgsat_barang']; ?>"
                            data-shift="<?= $shift; ?>">
                            <i class="fa fa-fw fa-check"></i>
                            SIMPAN</button>
                    </td>
                </tr>

        <?php
            }
            
            if ($no == 1) {
                echo "<tr><td colspan='9' class='text-center'>Semua barang sudah di-stok opname</td></tr>";
            }
        }
        ?>
    </tbody>
</table>

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
