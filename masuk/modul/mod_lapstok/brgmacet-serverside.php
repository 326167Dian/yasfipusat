<?php
include_once '../../../configurasi/koneksi.php';

if ($_GET['action'] == "table_data") {

    $conn = $GLOBALS["___mysqli_ston"];

    $columns = array(
        0 => 'a.id_barang',
        1 => 'a.kd_barang',
        2 => 'a.nm_barang',
        3 => 'a.stok_barang',
        4 => 'a.stok_buffer',
        5 => 't30',
        6 => 'q30',
        7 => 'a.sat_barang',
        8 => 'a.hrgsat_barang',
        9 => 'nilai_barang',
    );

    $startDateRaw = isset($_GET['start']) ? $_GET['start'] : date('Y-m-d', strtotime('-30 days'));
    $finishDateRaw = isset($_GET['finish']) ? $_GET['finish'] : date('Y-m-d');
    $startDate = date('Y-m-d', strtotime($startDateRaw));
    $finishDate = date('Y-m-d', strtotime($finishDateRaw));
    $startDateTime = $startDate . ' 00:00:00';
    $finishDateTime = $finishDate . ' 23:59:59';

    $baseWhere = " WHERE NOT EXISTS (
            SELECT 1 FROM trkasir_detail d
            WHERE d.id_barang = a.id_barang
              AND d.waktu BETWEEN '$startDateTime' AND '$finishDateTime'
        )";

    $querycount = $db->query("SELECT
                    COUNT(a.id_barang) AS jumlah,
                    SUM(a.hrgsat_barang * a.stok_barang) AS totalNilaiStok
                FROM barang a " . $baseWhere);

    $datacount = $querycount->fetch_array();

    $totalStok = $datacount['totalNilaiStok'];
    $totalData = $datacount['jumlah'];

    $totalFiltered = $totalData;

    $limit = isset($_POST['length']) ? (int) $_POST['length'] : 10;
    $start = isset($_POST['start']) ? (int) $_POST['start'] : 0;
    if ($limit < 1) {
        $limit = 10;
    }
    if ($start < 0) {
        $start = 0;
    }

    $orderColumn = isset($_POST['order'][0]['column']) ? (int) $_POST['order'][0]['column'] : 0;
    $order = isset($columns[$orderColumn]) ? $columns[$orderColumn] : 'a.id_barang';
    $dir = (isset($_POST['order'][0]['dir']) && strtolower($_POST['order'][0]['dir']) === 'asc') ? 'ASC' : 'DESC';

    $whereSearch = '';
    if (!empty($_POST['search']['value'])) {
        $search = mysqli_real_escape_string($conn, $_POST['search']['value']);
        $whereSearch = " AND (a.kd_barang LIKE '%$search%' OR a.nm_barang LIKE '%$search%')";

        $querycount = $db->query("SELECT COUNT(a.id_barang) AS jumlah FROM barang a " . $baseWhere . $whereSearch);
        $datacount = $querycount->fetch_array();
        $totalFiltered = $datacount['jumlah'];
    }

    $query = $db->query("SELECT
                a.id_barang,
                a.kd_barang,
                a.nm_barang,
                a.stok_barang,
                a.stok_buffer,
                a.sat_barang,
                a.hrgsat_barang,
                (a.hrgsat_barang * a.stok_barang) AS nilai_barang
            FROM barang a " . $baseWhere . $whereSearch . "
            ORDER BY $order $dir LIMIT $limit OFFSET $start");


    $data = array();
    if (!empty($query)) {
        $no = $start + 1;
        while ($value = $query->fetch_array()) {
            $nestedData['no'] = $no;
            $nestedData['kd_barang'] = $value['kd_barang'];
            $nestedData['nm_barang'] = $value['nm_barang'];
            $nestedData['stok_barang'] = $value['stok_barang'];
            $nestedData['stok_buffer'] = $value['stok_buffer'];
            $nestedData['t30'] = 0;
            $nestedData['q30'] = 0;
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
        "totalStok"         => $totalStok,
        "data"              => $data
    ];

    echo json_encode($json_data);
}
