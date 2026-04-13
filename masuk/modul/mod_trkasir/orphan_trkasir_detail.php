<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['username']) && empty($_SESSION['passuser'])) {
    echo "<link href='../css/style.css' rel='stylesheet' type='text/css'>";
    echo "<div class='error msg'>Untuk mengakses Modul anda harus login.</div>";
    exit;
}

include "../../../configurasi/koneksi.php";
include "../../../configurasi/fungsi_rupiah.php";

$conn = $GLOBALS["___mysqli_ston"];

function h($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function get_orphan_kd_list($conn, $kdFilter = '')
{
    $whereFilter = "";
    if ($kdFilter !== '') {
        $kdEsc = mysqli_real_escape_string($conn, $kdFilter);
        $whereFilter = " AND td.kd_trkasir = '$kdEsc'";
    }

    $sql = "SELECT
                td.kd_trkasir,
                COUNT(*) AS total_item,
                SUM(td.hrgttl_dtrkasir) AS total_nilai,
                MIN(td.id_dtrkasir) AS id_detail_pertama
            FROM trkasir_detail td
            LEFT JOIN trkasir t ON t.kd_trkasir = td.kd_trkasir
            WHERE t.kd_trkasir IS NULL
              AND td.kd_trkasir <> ''
              $whereFilter
            GROUP BY td.kd_trkasir
            ORDER BY id_detail_pertama ASC";

    $result = mysqli_query($conn, $sql);
    if (!$result) {
        return array();
    }

    $rows = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }

    return $rows;
}

function insert_trkasir_header($conn, $kdTrkasir)
{
    $kdEsc = mysqli_real_escape_string($conn, $kdTrkasir);

    $sqlModelBaru = "INSERT INTO trkasir(
                        kd_trkasir,
                        petugas,
                        shift,
                        tgl_trkasir,
                        nm_pelanggan,
                        tlp_pelanggan,
                        alamat_pelanggan,
                        ttl_trkasir,
                        diskon2,
                        dp_bayar,
                        sisa_bayar,
                        ket_trkasir,
                        id_carabayar,
                        jenistx
                    )
                    SELECT
                        td.kd_trkasir,
                        'SYSTEM-RECOVERY',
                        0,
                        CURDATE(),
                        'UMUM/-',
                        '',
                        '',
                        SUM(td.hrgttl_dtrkasir),
                        0,
                        SUM(td.hrgttl_dtrkasir),
                        0,
                        'Auto recovery dari orphan trkasir_detail',
                        1,
                        1
                    FROM trkasir_detail td
                    WHERE td.kd_trkasir = '$kdEsc'
                      AND NOT EXISTS (
                          SELECT 1
                          FROM trkasir t
                          WHERE t.kd_trkasir = '$kdEsc'
                      )
                    GROUP BY td.kd_trkasir";

    if (mysqli_query($conn, $sqlModelBaru)) {
        return mysqli_affected_rows($conn) > 0;
    }

    $sqlModelLama = "INSERT INTO trkasir(
                        kd_trkasir,
                        tgl_trkasir,
                        nm_pelanggan,
                        tlp_pelanggan,
                        alamat_pelanggan,
                        ttl_trkasir,
                        dp_bayar,
                        sisa_bayar,
                        ket_trkasir,
                        id_carabayar
                    )
                    SELECT
                        td.kd_trkasir,
                        CURDATE(),
                        'UMUM/-',
                        '',
                        '',
                        SUM(td.hrgttl_dtrkasir),
                        SUM(td.hrgttl_dtrkasir),
                        0,
                        'Auto recovery dari orphan trkasir_detail',
                        1
                    FROM trkasir_detail td
                    WHERE td.kd_trkasir = '$kdEsc'
                      AND NOT EXISTS (
                          SELECT 1
                          FROM trkasir t
                          WHERE t.kd_trkasir = '$kdEsc'
                      )
                    GROUP BY td.kd_trkasir";

    if (mysqli_query($conn, $sqlModelLama)) {
        return mysqli_affected_rows($conn) > 0;
    }

    throw new Exception(mysqli_error($conn));
}

function ensure_kartu_stok($conn, $kdTrkasir)
{
    $kdEsc = mysqli_real_escape_string($conn, $kdTrkasir);

    $sql = "INSERT INTO kartu_stok(kode_transaksi, tgl_sekarang)
            SELECT '$kdEsc', NOW()
            FROM DUAL
            WHERE NOT EXISTS (
                SELECT 1
                FROM kartu_stok ks
                WHERE ks.kode_transaksi = '$kdEsc'
            )";

    @mysqli_query($conn, $sql);
}

$info = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'repair') {
    $repairType = isset($_POST['repair_type']) ? $_POST['repair_type'] : 'all';
    $kdInput = isset($_POST['kd_trkasir']) ? trim($_POST['kd_trkasir']) : '';

    $targetRows = ($repairType === 'single' && $kdInput !== '')
        ? get_orphan_kd_list($conn, $kdInput)
        : get_orphan_kd_list($conn);

    if (count($targetRows) < 1) {
        $info = "Tidak ada data orphan yang perlu diperbaiki.";
    } else {
        $fixed = 0;
        $skipped = 0;
        $failedList = array();

        foreach ($targetRows as $row) {
            $kd = $row['kd_trkasir'];

            try {
                $inserted = insert_trkasir_header($conn, $kd);
                if ($inserted) {
                    $fixed++;
                    ensure_kartu_stok($conn, $kd);
                } else {
                    $skipped++;
                }
            } catch (Exception $e) {
                $failedList[] = $kd . " (" . $e->getMessage() . ")";
            }
        }

        $info = "Perbaikan selesai. Berhasil: " . $fixed . ", dilewati: " . $skipped . ".";
        if (count($failedList) > 0) {
            $error = "Gagal pada " . count($failedList) . " KD: " . implode("; ", $failedList);
        }
    }
}

$orphans = get_orphan_kd_list($conn);
$totalOrphanKd = count($orphans);
$totalOrphanItem = 0;
$totalOrphanNilai = 0;
foreach ($orphans as $orphan) {
    $totalOrphanItem += (int) $orphan['total_item'];
    $totalOrphanNilai += (float) $orphan['total_nilai'];
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Deteksi Orphan Trkasir Detail</title>
    <link rel="stylesheet" href="../../bootstrap/css/bootstrap.min.css">
    <style>
        body { padding: 20px; }
        .summary-box { margin-bottom: 15px; }
        .table > thead > tr > th { text-transform: uppercase; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <h3>Deteksi Orphan Trkasir Detail</h3>
        <p>Halaman ini menampilkan data <strong>trkasir_detail</strong> yang belum punya header pasangan di <strong>trkasir</strong>, lalu bisa diperbaiki otomatis.</p>

        <?php if ($info !== "") { ?>
            <div class="alert alert-success"><?php echo h($info); ?></div>
        <?php } ?>

        <?php if ($error !== "") { ?>
            <div class="alert alert-danger"><?php echo h($error); ?></div>
        <?php } ?>

        <div class="row summary-box">
            <div class="col-sm-3"><div class="well"><strong>Total KD Orphan</strong><br><?php echo number_format($totalOrphanKd); ?></div></div>
            <div class="col-sm-3"><div class="well"><strong>Total Item Orphan</strong><br><?php echo number_format($totalOrphanItem); ?></div></div>
            <div class="col-sm-6"><div class="well"><strong>Total Nilai Orphan</strong><br>Rp <?php echo format_rupiah($totalOrphanNilai); ?></div></div>
        </div>

        <form method="post" class="form-inline" onsubmit="return confirm('Jalankan perbaikan semua data orphan?');">
            <input type="hidden" name="action" value="repair">
            <input type="hidden" name="repair_type" value="all">
            <button type="submit" class="btn btn-success btn-flat" <?php echo ($totalOrphanKd < 1 ? 'disabled' : ''); ?>>PERBAIKI SEMUA ORPHAN</button>
        </form>

        <hr>

        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>KD Trkasir</th>
                        <th>Total Item Detail</th>
                        <th>Total Nilai</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($totalOrphanKd < 1) { ?>
                        <tr>
                            <td colspan="5" class="text-center">Tidak ada data orphan.</td>
                        </tr>
                    <?php } else {
                        $no = 1;
                        foreach ($orphans as $row) { ?>
                            <tr>
                                <td class="text-center"><?php echo $no; ?></td>
                                <td><?php echo h($row['kd_trkasir']); ?></td>
                                <td class="text-right"><?php echo number_format((int) $row['total_item']); ?></td>
                                <td class="text-right">Rp <?php echo format_rupiah((float) $row['total_nilai']); ?></td>
                                <td class="text-center">
                                    <form method="post" style="display:inline;" onsubmit="return confirm('Perbaiki KD <?php echo h($row['kd_trkasir']); ?> ?');">
                                        <input type="hidden" name="action" value="repair">
                                        <input type="hidden" name="repair_type" value="single">
                                        <input type="hidden" name="kd_trkasir" value="<?php echo h($row['kd_trkasir']); ?>">
                                        <button type="submit" class="btn btn-xs btn-primary">Perbaiki KD Ini</button>
                                    </form>
                                </td>
                            </tr>
                        <?php
                            $no++;
                        }
                    } ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
