<?php
include_once '../../../configurasi/koneksi.php';

if ($_GET['action'] == "table_data") {

    $conn = $GLOBALS["___mysqli_ston"];

    $columns = array(
        0 => 'x.id_barang',
        1 => 'x.kd_barang',
        2 => 'x.nmbrg_dtrkasir',
        3 => 'b.stok_barang',
        4 => 'b.stok_buffer',
        5 => 'x.t30',
        6 => 'x.q30',
        7 => 'x.om30',
        8 => 'l30',
        9 => 'b.sat_barang',
        10 => 'b.hrgsat_barang',
        11 => 'nilai_barang'
    );
    $limit = isset($_POST['length']) ? (int) $_POST['length'] : 10;
    $start = isset($_POST['start']) ? (int) $_POST['start'] : 0;
    if ($limit < 1) {
        $limit = 10;
    }
    if ($start < 0) {
        $start = 0;
    }

    $orderColumn = isset($_POST['order'][0]['column']) ? (int) $_POST['order'][0]['column'] : 0;
    $order = isset($columns[$orderColumn]) ? $columns[$orderColumn] : 'x.id_barang';
    $dir = (isset($_POST['order'][0]['dir']) && strtolower($_POST['order'][0]['dir']) === 'asc') ? 'ASC' : 'DESC';

    $startDateRaw = isset($_GET['start']) ? $_GET['start'] : date('Y-m-d', strtotime('-30 days'));
    $finishDateRaw = isset($_GET['finish']) ? $_GET['finish'] : date('Y-m-d');
    $startDate = date('Y-m-d', strtotime($startDateRaw));
    $finishDate = date('Y-m-d', strtotime($finishDateRaw));
    $startDateTime = $startDate . ' 00:00:00';
    $finishDateTime = $finishDate . ' 23:59:59';

    $baseFrom = "
        FROM (
            SELECT d.id_barang,
                   d.kd_barang,
                   MAX(d.nmbrg_dtrkasir) AS nmbrg_dtrkasir,
                   COUNT(d.id_dtrkasir) AS t30,
                   SUM(d.qty_dtrkasir) AS q30,
                   SUM(d.hrgttl_dtrkasir) AS om30
            FROM trkasir_detail d
            WHERE d.waktu BETWEEN '$startDateTime' AND '$finishDateTime'
            GROUP BY d.id_barang, d.kd_barang
            HAVING COUNT(d.id_dtrkasir) > 5 AND COUNT(d.id_dtrkasir) < 11
        ) x
        LEFT JOIN barang b ON b.id_barang = x.id_barang
    ";

    $whereSearch = '';
    if (!empty($_POST['search']['value'])) {
        $search = mysqli_real_escape_string($conn, $_POST['search']['value']);
        $whereSearch = " WHERE x.kd_barang LIKE '%$search%'
            OR x.nmbrg_dtrkasir LIKE '%$search%'";
    }

    $queryTotal = $db->query("SELECT COUNT(*) AS jumlah " . $baseFrom);
    $dataTotal = $queryTotal->fetch_array();
    $totalData = isset($dataTotal['jumlah']) ? (int) $dataTotal['jumlah'] : 0;
    $totalFiltered = $totalData;

    if (!empty($whereSearch)) {
        $queryFiltered = $db->query("SELECT COUNT(*) AS jumlah " . $baseFrom . $whereSearch);
        $dataFiltered = $queryFiltered->fetch_array();
        $totalFiltered = isset($dataFiltered['jumlah']) ? (int) $dataFiltered['jumlah'] : 0;
    }

    $query = $db->query("SELECT x.id_barang,
            x.kd_barang,
            x.nmbrg_dtrkasir,
            x.t30,
            x.q30,
            x.om30,
            COALESCE(b.stok_barang, 0) AS stok_barang,
            COALESCE(b.stok_buffer, 0) AS stok_buffer,
            COALESCE(b.sat_barang, '') AS sat_barang,
            COALESCE(b.hrgsat_barang, 0) AS hrgsat_barang,
            (x.om30 - (x.q30 * COALESCE(b.hrgsat_barang, 0))) AS l30,
            (COALESCE(b.hrgsat_barang, 0) * COALESCE(b.stok_barang, 0)) AS nilai_barang
        " . $baseFrom . $whereSearch . "
        ORDER BY $order $dir LIMIT $limit OFFSET $start");

    $queryTotals = $db->query("SELECT
            COALESCE(SUM(x.om30), 0) AS totalOm30,
            COALESCE(SUM(x.om30 - (x.q30 * COALESCE(b.hrgsat_barang, 0))), 0) AS totalL30,
            COALESCE(SUM(COALESCE(b.hrgsat_barang, 0) * COALESCE(b.stok_barang, 0)), 0) AS totalStok
        " . $baseFrom);
    $rowTotals = $queryTotals->fetch_array();
    $totalOm30 = isset($rowTotals['totalOm30']) ? $rowTotals['totalOm30'] : 0;
    $totalL30 = isset($rowTotals['totalL30']) ? $rowTotals['totalL30'] : 0;
    $totalStok = isset($rowTotals['totalStok']) ? $rowTotals['totalStok'] : 0;

    $data = array();
    if (!empty($query)) {
        $no = $start + 1;
        while ($value = $query->fetch_array()) {
            $nestedData['no'] = $no;
            $nestedData['kd_barang'] = $value['kd_barang'];
            $nestedData['nm_barang'] = $value['nmbrg_dtrkasir'];
            $nestedData['stok_barang'] = $value['stok_barang'];
            $nestedData['stok_buffer'] = $value['stok_buffer'];
            $nestedData['t30'] = $value['t30'];
            $nestedData['q30'] = $value['q30'];
            $nestedData['om30'] = $value['om30'];
            $nestedData['l30'] = round($value['l30']);
            $nestedData['satuan'] = $value['sat_barang'];
            $nestedData['harga_beli'] = $value['hrgsat_barang'];
            $nestedData['nilai_barang'] = $value['nilai_barang'];
            $nestedData['kartu_stok'] = "<a href='?module=lapstok&act=edit&id=$value[kd_barang]' title='Riwayat' class='btn btn-warning btn-xs'>Riwayat</a>";
            $data[] = $nestedData;
            $no++;
        }
    }

    $json_data = [
        "draw"              => intval($_POST['draw']),
        "recordsTotal"      => intval($totalData),
        "recordsFiltered"   => intval($totalFiltered),
        "totalOm30"         => $totalOm30,
        "totalL30"          => $totalL30,
        "totalStok"         => $totalStok,
        "data"              => $data
    ];

    echo json_encode($json_data);
}
