<?php
session_start();
include_once '../../../configurasi/koneksi.php';

if ($_GET['action'] == "table_data") {

    $columns = array(
        0 => 'id_trbmasuk',
        1 => 'kd_trbmasuk',
        2 => 'petugas',
        3 => 'tgl_trbmasuk',
        4 => 'nm_supplier',
        5 => 'ket_trbmasuk',
        6 => 'sisa_bayar',
        7 => 'carabayar',
        8 => 'id_trbmasuk'
    );

    $querycount = $db->query("SELECT count(id_trbmasuk) as jumlah FROM trbmasuk WHERE id_resto = 'pusat' AND jenis = 'nonpbf'");
    $datacount = $querycount->fetch_array();

    $totalData = $datacount['jumlah'];

    $totalFiltered = $totalData;

    $limit = isset($_POST['length']) ? (int)$_POST['length'] : 10;
    $start = isset($_POST['start']) ? (int)$_POST['start'] : 0;
    $orderColumn = isset($_POST['order']['0']['column']) ? (int)$_POST['order']['0']['column'] : 0;
    $order = isset($columns[$orderColumn]) ? $columns[$orderColumn] : $columns[0];
    $dir = (isset($_POST['order']['0']['dir']) && strtolower($_POST['order']['0']['dir']) === 'asc') ? 'ASC' : 'DESC';

    $orderBy = "$order $dir";
    if ($dir == 'DESC') {
        $orderBy = "($order = '' OR $order IS NULL) DESC, $order DESC";
    }

    if (empty($_POST['search']['value'])) {
        $query = $db->query("SELECT *
            FROM trbmasuk
            WHERE trbmasuk.id_resto = 'pusat' AND trbmasuk.jenis = 'nonpbf'
            ORDER BY $orderBy LIMIT $limit OFFSET $start");
    } else {
        $search = $_POST['search']['value'];
        $query = $db->query("SELECT * 
            FROM trbmasuk
            WHERE trbmasuk.id_resto = 'pusat' AND trbmasuk.jenis = 'nonpbf'
                        AND kd_trbmasuk LIKE '%$search%'
                        OR petugas LIKE '%$search%'
                        OR tgl_trbmasuk LIKE '%$search%'
                        OR nm_supplier LIKE '%$search%'
                        OR ket_trbmasuk LIKE '%$search%'
                        OR sisa_bayar LIKE '%$search%'
                        OR carabayar LIKE '%$search%'
            ORDER BY $orderBy LIMIT $limit OFFSET $start");

        $querycount = $db->query("SELECT count(id_trbmasuk) as jumlah 
            FROM trbmasuk
            WHERE trbmasuk.id_resto = 'pusat' AND trbmasuk.jenis = 'nonpbf'
                        AND trbmasuk.kd_trbmasuk LIKE '%$search%'
                        OR trbmasuk.petugas LIKE '%$search%'
                        OR trbmasuk.tgl_trbmasuk LIKE '%$search%'
                        OR trbmasuk.nm_supplier LIKE '%$search%'
                        OR trbmasuk.ket_trbmasuk LIKE '%$search%'
                        OR trbmasuk.sisa_bayar LIKE '%$search%'
                        OR trbmasuk.carabayar LIKE '%$search%'");

        $datacount = $querycount->fetch_array();
        $totalFiltered = $datacount['jumlah'];
    }

    $data = array();
    // $totalNilaiBarang = 0;
    if (!empty($query)) {
        $no = $start + 1;
        while ($value = $query->fetch_array()) {
            // for column
            $nestedData['no'] = $no;
            $nestedData['kd_trbmasuk'] = $value['kd_trbmasuk'];
            $nestedData['petugas'] = $value['petugas'];
            $nestedData['tgl_trbmasuk'] = $value['tgl_trbmasuk'];
            $nestedData['nm_supplier'] = $value['nm_supplier'];
            $nestedData['ket_trbmasuk'] = $value['ket_trbmasuk'];
            $nestedData['sisa_bayar'] = $value['sisa_bayar'];
            $nestedData['carabayar'] = $value['carabayar'];
            $nestedData['aksi'] = "<a href='?module=trbmasuk&act=ubah&id=$value[id_trbmasuk]' title='EDIT' class='btn btn-warning btn-xs'>TAMPIL</a>";
            $data[] = $nestedData;
            $no++;
        }
    }

    $json_data = [
        "draw"              => intval($_POST['draw']),
        "recordsTotal"      => intval($totalData),
        "recordsFiltered"   => intval($totalFiltered),
        "data"              => $data
    ];

    echo json_encode($json_data);
}
