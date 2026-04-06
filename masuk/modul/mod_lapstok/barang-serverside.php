<?php
include_once '../../../configurasi/koneksi.php';

if ($_GET['action'] == "table_data") {

    $conn = $GLOBALS["___mysqli_ston"];

    $columns = array(
        0 => 'a.id_barang',
        1 => 'a.kd_barang',
        2 => 'a.nm_barang',
        3 => 'a.stok_barang',
        4 => 't30',
        5 => 't60',
        6 => 'gr',
        7 => 'q30',
        8 => 'a.sat_barang',
        9 => 'a.hrgsat_barang',
        10 => 'nilai_barang'
    );

    $querycount = $db->query("SELECT count(id_barang) as jumlah, SUM(hrgsat_barang*stok_barang) as totalNilaiStok FROM barang");
    $datacount = $querycount->fetch_array();

    $totalData = $datacount['jumlah'];

    $totalFiltered = $totalData;
    $totalNilaiStok = $datacount['totalNilaiStok'];

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

    $startDateRaw = isset($_GET['start']) ? $_GET['start'] : date('Y-m-d', strtotime('-30 days'));
    $finishDateRaw = isset($_GET['finish']) ? $_GET['finish'] : date('Y-m-d');
    $startDate = date('Y-m-d', strtotime($startDateRaw));
    $finishDate = date('Y-m-d', strtotime($finishDateRaw));
    $tgl60 = date('Y-m-d', strtotime('-30 days', strtotime($startDate)));

    $whereSearch = '';
    if (!empty($_POST['search']['value'])) {
        $search = mysqli_real_escape_string($conn, $_POST['search']['value']);
        $whereSearch = " WHERE a.kd_barang LIKE '%$search%'
                OR a.nm_barang LIKE '%$search%'
                OR a.stok_barang LIKE '%$search%'
                OR a.sat_barang LIKE '%$search%'
                OR a.hrgsat_barang LIKE '%$search%'";
    }

    $startDateTime = $startDate . ' 00:00:00';
    $finishDateTime = $finishDate . ' 23:59:59';
    $tgl60DateTime = $tgl60 . ' 00:00:00';

    $query = null;
    $rowBuffer = array();
    $computedColumns = array('t30', 't60', 'gr', 'q30');
    $isComputedSort = in_array($order, $computedColumns);

    if ($isComputedSort) {
        $fromQuery = "
            FROM barang a
            LEFT JOIN (
                SELECT d.kd_barang,
                       SUM(CASE WHEN d.waktu BETWEEN '$startDateTime' AND '$finishDateTime' THEN 1 ELSE 0 END) AS cnt30,
                       COUNT(d.id_dtrkasir) AS cnt60,
                       SUM(CASE WHEN d.waktu BETWEEN '$startDateTime' AND '$finishDateTime' THEN d.qty_dtrkasir ELSE 0 END) AS qty30
                FROM trkasir_detail d
                WHERE d.waktu BETWEEN '$tgl60DateTime' AND '$finishDateTime'
                GROUP BY d.kd_barang
            ) ps ON ps.kd_barang = a.kd_barang
        ";

        $selectQuery = "
            SELECT a.id_barang,
                   a.kd_barang,
                   a.nm_barang,
                   a.stok_barang,
                   COALESCE(ps.cnt30, 0) AS t30,
                   (COALESCE(ps.cnt60, 0) - COALESCE(ps.cnt30, 0)) AS t60,
                   COALESCE(ps.qty30, 0) AS q30,
                   CASE
                       WHEN (COALESCE(ps.cnt60, 0) - COALESCE(ps.cnt30, 0)) = 0 THEN 0
                       ELSE ROUND(((COALESCE(ps.cnt30, 0) / (COALESCE(ps.cnt60, 0) - COALESCE(ps.cnt30, 0))) * 100) - 100)
                   END AS gr,
                   a.sat_barang,
                   a.hrgsat_barang,
                   (a.hrgsat_barang * a.stok_barang) AS nilai_barang
        ";

        $query = $db->query($selectQuery . $fromQuery . $whereSearch . " ORDER BY $order $dir LIMIT $limit OFFSET $start");
    } else {
        $query = $db->query("SELECT a.id_barang,
                a.kd_barang,
                a.nm_barang,
                a.stok_barang,
                a.sat_barang,
                a.hrgsat_barang,
                (a.hrgsat_barang * a.stok_barang) AS nilai_barang
            FROM barang a " . $whereSearch . " ORDER BY $order $dir LIMIT $limit OFFSET $start");

        if (!empty($query)) {
            while ($row = $query->fetch_array()) {
                $rowBuffer[] = $row;
            }
        }
    }

    if (!empty($whereSearch)) {
        $querycount = $db->query("SELECT COUNT(a.id_barang) as jumlah FROM barang a " . $whereSearch);
        $datacount = $querycount->fetch_array();
        $totalFiltered = $datacount['jumlah'];
    }

    $data = array();
    if ($isComputedSort) {
        if (!empty($query)) {
            $no = $start + 1;
            while ($value = $query->fetch_array()) {
                $nestedData['no'] = $no;
                $nestedData['kd_barang'] = $value['kd_barang'];
                $nestedData['nm_barang'] = $value['nm_barang'];
                $nestedData['stok_barang'] = $value['stok_barang'];
                $nestedData['t30'] = $value['t30'];
                $nestedData['t60'] = $value['t60'];
                $nestedData['gr'] = $value['gr'];
                $nestedData['q30'] = ($value['q30'] <= 0) ? 0 : $value['q30'];
                $nestedData['satuan'] = $value['sat_barang'];
                $nestedData['harga_beli'] = $value['hrgsat_barang'];
                $nestedData['nilai_barang'] = $value['nilai_barang'];
                $nestedData['kartu_stok'] = "<a href='?module=lapstok&act=edit&id=$value[kd_barang]' title='Riwayat' class='btn btn-warning btn-xs'>Riwayat</a>";
                $data[] = $nestedData;
                $no++;
            }
        }
    } else {
        $aggMap = array();

        if (!empty($rowBuffer)) {
            $kdList = array();
            foreach ($rowBuffer as $row) {
                $kdList[] = "'" . mysqli_real_escape_string($conn, $row['kd_barang']) . "'";
            }

            $inList = implode(',', $kdList);
            if (!empty($inList)) {
                $aggQuery = $db->query("SELECT d.kd_barang,
                        SUM(CASE WHEN d.waktu BETWEEN '$startDateTime' AND '$finishDateTime' THEN 1 ELSE 0 END) AS cnt30,
                        COUNT(d.id_dtrkasir) AS cnt60,
                        SUM(CASE WHEN d.waktu BETWEEN '$startDateTime' AND '$finishDateTime' THEN d.qty_dtrkasir ELSE 0 END) AS qty30
                    FROM trkasir_detail d
                    WHERE d.waktu BETWEEN '$tgl60DateTime' AND '$finishDateTime'
                      AND d.kd_barang IN ($inList)
                    GROUP BY d.kd_barang");

                if (!empty($aggQuery)) {
                    while ($agg = $aggQuery->fetch_array()) {
                        $aggMap[$agg['kd_barang']] = $agg;
                    }
                }
            }
        }

        $no = $start + 1;
        foreach ($rowBuffer as $value) {
            $agg = isset($aggMap[$value['kd_barang']]) ? $aggMap[$value['kd_barang']] : null;
            $t30 = $agg ? (int)$agg['cnt30'] : 0;
            $t60 = $agg ? ((int)$agg['cnt60'] - (int)$agg['cnt30']) : 0;
            $q30 = $agg ? (float)$agg['qty30'] : 0;
            $gr = ($t60 == 0) ? 0 : round((($t30 / $t60) * 100) - 100);

            $nestedData['no'] = $no;
            $nestedData['kd_barang'] = $value['kd_barang'];
            $nestedData['nm_barang'] = $value['nm_barang'];
            $nestedData['stok_barang'] = $value['stok_barang'];
            $nestedData['t30'] = $t30;
            $nestedData['t60'] = $t60;
            $nestedData['gr'] = $gr;
            $nestedData['q30'] = ($q30 <= 0) ? 0 : $q30;
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
        "totalStok"         => intval($totalNilaiStok),
        "data"              => $data
    ];

    echo json_encode($json_data);
}
