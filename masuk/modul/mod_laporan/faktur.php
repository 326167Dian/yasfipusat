<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (empty($_SESSION['username']) AND empty($_SESSION['passuser'])){
    echo "<link href=../css/style.css rel=stylesheet type=text/css>";
    echo "<div class='error msg'>Untuk mengakses Modul anda harus login.</div>";
}
else {

    include "../../../configurasi/koneksi.php";
    require('../../assets/pdf/fpdf.php');
    include "../../../configurasi/fungsi_indotgl.php";
    include "../../../configurasi/fungsi_rupiah.php";

//ambil header
    $ah = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT * FROM setheader ");
    $rh = mysqli_fetch_array($ah);

    $kd_trkasir = isset($_GET['kd_trkasir']) ? mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $_GET['kd_trkasir']) : '';
    if ($kd_trkasir == '') {
        exit('Kode transaksi dropping tidak ditemukan.');
    }

    $dt = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT dropping.*, carabayar.nm_carabayar, trdropping.kd_trdropping
        FROM dropping
        LEFT JOIN carabayar ON dropping.id_carabayar = carabayar.id_carabayar
        LEFT JOIN trdropping ON trdropping.kd_trkasir = dropping.kd_trkasir
        WHERE dropping.kd_trkasir='$kd_trkasir'
        LIMIT 1");
    $r1 = mysqli_fetch_array($dt);

    if (!is_array($r1)) {
        exit('Data dropping tidak ditemukan di database yasfi.');
    }

    $niltransaksi = isset($r1['ttl_trkasir']) ? (float) $r1['ttl_trkasir'] : 0;
    $no_faktur = !empty($r1['kd_trdropping']) ? $r1['kd_trdropping'] : $r1['kd_trkasir'];

    $detailTable = 'dropping_detail';
    $jumlahdetail = mysqli_num_rows(mysqli_query($GLOBALS["___mysqli_ston"], "SELECT * FROM dropping_detail WHERE kd_trkasir='$kd_trkasir'"));
    if ($jumlahdetail <= 0) {
        $detailTable = 'trkasir_detail';
        $jumlahdetail = mysqli_num_rows(mysqli_query($GLOBALS["___mysqli_ston"], "SELECT * FROM trkasir_detail WHERE kd_trkasir='$kd_trkasir'"));
    }

// $ukuran1 = 14.7; //setingan kertas
// $ukuran2 = 5.4; //garis akhir tabel
//$ukuran1 = 33; //setingan kertas
//$ukuran2 = 43; //garis akhir tabel

//$tambahukuran = $jumlahdetail * 0.4;
//$tinggikertas = $ukuran1 + $tambahukuran;
//$posisigaris = $ukuran2 + $tambahukuran;


    $pdf = new FPDF("P", "cm", "A4");
    $pdf->SetMargins(0, 0.8, 0);
    $pdf->AliasNbPages();
    $pdf->AddPage();

    $pdf->Image('../../images/'.$rh['logo'],0.9,0.8,2,2.5,'');
//HEADER 1
    $pdf->SetLineWidth(0.1);
    $pdf->Line(0.7, 4.2, 20.5, 4.2); //horisontal bawah


    $pdf->SetLineWidth(0.05);
    $pdf->Line(0.7, 5.7, 20.5, 5.7); //garis tabel atas
    $pdf->Line(0.7, 6.3, 20.5, 6.3); //garis tabel bawah

    $pdf->ln(0.1);
    $pdf->SetX(3.5);
    $pdf->SetFont('Arial', 'B', 11);
    $pdf->Cell(16.5, 0.5,$rh['satu'], 0, 1, 'L');

    $pdf->ln(0.1);
    $pdf->SetX(3.5);
    $pdf->SetFont('Arial', 'b', 10);
    $pdf->Cell(8, 0.1, $rh['lima'], 0, 0, 'L');


    $pdf->ln(0.3);
    $pdf->SetX(3.5);
    $pdf->SetFont('Arial', '', 9);
    $pdf->Cell(8, 0.2, $rh['dua'], 0, 1, 'L');

    $pdf->ln(0.1);
    $pdf->SetX(3.5);
    $pdf->SetFont('Arial', '', 9);
    $pdf->Cell(8, 0.2, 'Kabupaten Bekasi', 0, 1, 'L');

    $pdf->ln(0.1);
    $pdf->SetX(3.5);
    $pdf->SetFont('Arial', '', 9);
    $pdf->Cell(8, 0.2, 'Whatsapp : '.$rh['enam'], 0, 1, 'L');


    $pdf->ln(0.4);
    $pdf->SetX(0.6);
    $pdf->SetFont('Arial', '', 9);
    $pdf->Cell(4, 0.1, '', 0, 0, 'L');
    $pdf->SetFont('Arial', '', 9);
    $pdf->Cell(0.3, 0.1, '', 0, 0, 'L');
    $pdf->SetFont('Arial', '', 9);
    $pdf->Cell(10, 0.1, '', 0, 0, 'L');
    $pdf->SetFont('Arial', '', 9);
    $pdf->Cell(2, 0.2, 'No. Dropping', 0, 0, 'L');
    $pdf->Cell(0.3, 0.2, ':', 0, 0, 'R');
    $pdf->Cell(6, 0.2, $no_faktur, 0, 1, 'L');

    $pdf->ln(0.2);
    $pdf->SetX(0.6);
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(4, 0.1, "Surat Dropping Barang", 0, 0, 'L');
    $pdf->SetFont('Arial', '', 9);
    $pdf->Cell(0.3, 0.1, '', 0, 0, 'L');
    $pdf->SetFont('Arial', '', 9);
    $pdf->Cell(10, 0.1, '', 0, 0, 'L');
    $pdf->Cell(2, 0.2, 'Tgl Dropping', 0, 0, 'L');
    $pdf->Cell(0.3, 0.2, ':', 0, 0, 'R');
    $pdf->Cell(6, 0.2, tgl_indo($r1['tgl_trkasir']), 0, 1, 'L');

    $pdf->ln(0.2);
    $pdf->SetX(0.6);
    $pdf->SetFont('Arial', '', 9);
    $pdf->Cell(4, 0.1, '', 0, 0, 'L');
    $pdf->SetFont('Arial', '', 9);
    $pdf->Cell(0.3, 0.1, '', 0, 0, 'L');
    $pdf->Cell(10, 0.1, '', 0, 0, 'L');
    $pdf->Cell(2, 0.2, 'Kasir', 0, 0, 'L');
    $pdf->Cell(0.3, 0.2, ':', 0, 0, 'R');
    $pdf->Cell(6, 0.2, $r1['petugas'], 0, 1, 'L');

    $pdf->ln(0.6);
    $pdf->SetX(0.6);
    $pdf->SetFont('Arial', '', 9);
    $pdf->Cell(2, 0.1, 'Kepada Yth', 0, 1, 'L');

    $pdf->ln(0.2);
    $pdf->SetX(0.6);
    $pdf->SetFont('Arial', '', 9);
    $pdf->Cell(20, 0.2, $r1['nm_pelanggan'], 0, 1, 'L');

    $pdf->ln(0.2);
    $pdf->SetX(0.6);
    $pdf->SetFont('Arial', '', 9);
    $pdf->Cell(3.3, 0.2, strip_tags($r1['alamat_pelanggan']), 0, 1, 'L');

    $pdf->ln(0.3);
    $pdf->SetX(0.6);
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->Cell(1, 0.4, 'No.', 0, 0, 'C');
    $pdf->Cell(6, 0.4, 'Nama Barang', 0, 0, 'C');
    $pdf->Cell(2, 0.4, 'Batch', 0, 0, 'C');
    $pdf->Cell(2, 0.4, 'ED', 0, 0, 'C');
    $pdf->Cell(2, 0.4, 'Sat', 0, 0, 'C');
    $pdf->Cell(1, 0.4, 'Qty', 0, 0, 'C');
    $pdf->Cell(2, 0.4, 'Harga', 0, 0, 'R');
    $pdf->Cell(2, 0.4, 'Disc', 0, 0, 'C');
    $pdf->Cell(2, 0.4, 'Sub total', 0, 1, 'R');

    $pdf->ln(0.3);
    $pdf->SetX(0.6);
    $pdf->SetFont('Times', '', 9);

    $no = 1;
    $query = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT * FROM dropping_detail WHERE kd_trkasir='$kd_trkasir'
	ORDER BY nmbrg_dtrkasir ASC");

    $st = array();
    $gt = 0;
    $subtotal = format_rupiah(0);
    $garis = 6.1;
    while ($r2 = mysqli_fetch_array($query)) {
        $st[] = (float) $r2['hrgttl_dtrkasir'];
        $gt = array_sum($st);
        // $disc = (($gt-$r1['ttl_tr5kasir'])/$gt)*100;
        $disc = format_rupiah($r2['disc']);
        $tagihan = format_rupiah($r1['ttl_trkasir']);
        $subtotal = format_rupiah($gt);
        $pdf->SetX(0.6);

        $text1 = substr($r2['nmbrg_dtrkasir'], 0, 60);
        $text2 = substr($r2['nmbrg_dtrkasir'], 60, 120);
        $text3 = strlen($r2['nmbrg_dtrkasir']);

        if ($text3 > 60) {
            $pdf->Cell(1, 0.4, $no . '.', 0, 0, 'C');
            $pdf->Cell(6, 0.4, $text1, 0, 0, 'L');
            $pdf->Cell(2, 0.4, $r2['no_batch'], 0, 0, 'C');
            $pdf->Cell(2, 0.4, $r2['exp_date'], 0, 0, 'C');
            $pdf->Cell(2, 0.4, $r2['sat_dtrkasir'], 0, 0, 'C');
            $pdf->Cell(1, 0.4, $r2['qty_dtrkasir'], 0, 0, 'C');
            $pdf->Cell(2, 0.4, format_rupiah($r2['hrgjual_dtrkasir']), 0, 0, 'R');
            $pdf->Cell(2, 0.4, $disc, 0, 0, 'C');
            $pdf->Cell(2, 0.4, format_rupiah($r2['hrgttl_dtrkasir']), 0, 1, 'R');

            $pdf->SetX(0.6);
            $pdf->Cell(1, 0.4, '', 0, 0, 'C');
            $pdf->Cell(6, 0.4, $text2, 0, 0, 'L');
            $pdf->Cell(2, 0.4, '', 0, 0, 'L');
            $pdf->Cell(2, 0.4, '', 0, 0, 'L');
            $pdf->Cell(2, 0.4, '', 0, 0, 'R');
            $pdf->Cell(1, 0.4, '', 0, 0, 'L');
            $pdf->Cell(2, 0.4, '', 0, 0, 'R');
            $pdf->Cell(2, 0.4, '', 0, 0, 'C');
            $pdf->Cell(2, 0.4, '', 0, 1, 'R');

            $garis = $garis + 1.2;
        } else {
            $pdf->Cell(1, 0.4, $no . '.', 0, 0, 'C');
            $pdf->Cell(6, 0.4, $r2['nmbrg_dtrkasir'], 0, 0, 'L');
            $pdf->Cell(2, 0.4, $r2['no_batch'], 0, 0, 'C');
            $pdf->Cell(2, 0.4, $r2['exp_date'], 0, 0, 'C');
            $pdf->Cell(2, 0.4, $r2['sat_dtrkasir'], 0, 0, 'C');
            $pdf->Cell(1, 0.4, $r2['qty_dtrkasir'], 0, 0, 'C');
            $pdf->Cell(2, 0.4, format_rupiah($r2['hrgjual_dtrkasir']), 0, 0, 'R');
            $pdf->Cell(2, 0.4, $disc, 0, 0, 'C');
            $pdf->Cell(2, 0.4, format_rupiah($r2['hrgttl_dtrkasir']), 0, 1, 'R');

            $garis = $garis + 0.6;
        }

        $no++;
    }
    $garis = $garis + 0.4;
    $pdf->SetX(0.5);
    $pdf->SetFont('Arial', 'U');
    $pdf->Cell(21, 0.2, '...............................................................................................................................................................................................................................', 0, 0, 'C');

    $pdf->ln(0.4);
    $pdf->SetX(0.6);
    $pdf->SetFont('Arial', '', 9);
    $pdf->Cell(8, 0.3, 'Terbilang :', 0, 0, 'L');
    $pdf->SetFont('Arial', '', 9);
    $pdf->Cell(9, 0.3, 'Total Transaksi : ', 0, 0, 'R');
    $pdf->Cell(3, 0.3, $subtotal, 0, 1, 'R');

    $diskonfaktur = $gt - $r1['ttl_trkasir'];
    $terbilangText = trim(terbilang((int) $niltransaksi));
    $terbilangLines = explode("\n", wordwrap($terbilangText, 55, "\n", true));

    if (count($terbilangLines) > 1) {
        $pdf->SetX(0.6);
        $pdf->SetFont('Arial', 'I', 9);
        $pdf->Cell(8, 0.4, $terbilangLines[0], 0, 1, 'L');

        $pdf->SetX(0.6);
        $pdf->SetFont('Arial', 'I', 9);
        $pdf->Cell(8, 0.4, implode(' ', array_slice($terbilangLines, 1)) . ' Rupiah.', 0, 0, 'L');

        $pdf->SetFont('Arial', '', 9);
        

    } else {
        $pdf->SetX(0.6);
        $pdf->SetFont('Arial', 'I', 11);
        $pdf->Cell(8, 0.4, $terbilangText . ' Rupiah.', 0, 0, 'L');

        $pdf->SetFont('Arial', '', 9);
        

    }

    if ($r1['id_carabayar'] == 1) {
        $dana = format_rupiah($niltransaksi);
    } elseif ($r1['id_carabayar'] == 2)
        {$dana = 'Menunggu Validasi'; } 
      elseif ($r1['id_carabayar'] == 3)
       {$dana = 'Belum diterima'; }

    $pdf->SetX(0.6);
    $pdf->SetFont('Arial', 'B', 9);
    
    $pdf->SetX(0.6);
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->Cell(17, 0.4, '', 0, 0, 'R');
    $pdf->Cell(3, 0.4, '', 0, 1, 'R');

    $pdf->ln(0.4);
    $pdf->SetX(0.6);
    $pdf->SetFont('Arial', '', 9);
    $pdf->Cell(10, 0.4, 'Catatan/Ketentuan :', 'LTR', 0, 'C');
    $pdf->Cell(5, 0.4, 'Penerima', 0, 0, 'C');
    $pdf->Cell(5, 0.4, 'Dicetak Oleh', 0, 0, 'C');

    $pdf->ln(0.4);
    $pdf->SetX(0.6);
    $pdf->SetFont('Arial', '', 9);
    $pdf->Cell(2, 0.4, 'Transaksi', 'L', 0, 'L');
    $pdf->Cell(0.3, 0.4, ':', 0, 0, 'L');
    $pdf->Cell(2, 0.4, $r1['nm_carabayar'], 0, 0, 'L');
    $pdf->Cell(2, 0.4, '', 0, 0, 'L');
    $pdf->Cell(0.3, 0.4, '', 0, 0, 'L');
    $pdf->Cell(3.4, 0.4, '', 'R', 1, 'L');

    $pdf->ln(0.1);
    $pdf->SetX(0.6);
    $pdf->SetFont('Arial', '', 9);
    $pdf->Cell(2, 0.1, 'Surat Dropping perpindahan', 'L', 0, 'L');
    $pdf->Cell(0.3, 0.1, '', 0, 0, 'L');
    $pdf->Cell(7.7, 0.1, '', 'R', 1, 'L');

    $pdf->ln(0.1);
    $pdf->SetX(0.6);
    $pdf->SetFont('Arial', '', 9);
    $pdf->Cell(10, 0.1, '', 'LBR', 0, 'L');
    $pdf->Cell(10, 0.1, '', 0, 1, 'L');

    $pdf->ln(0.5);
    $pdf->SetX(0.6);
    $pdf->SetFont('Arial', '', 9);
    $pdf->Cell(10, 0.3, '', 0, 0, 'L');
    $pdf->Cell(5, 0.3, '( ____________________ )', 0, 0, 'C');

    $petugas = $_SESSION['namalengkap'];
    $pdf->SetFont('Arial', '', 9);
    $pdf->Cell(5, 0.3, $petugas, 0, 1, 'C');

    $pdf->ln(0.1);
    $pdf->SetX(0.6);
    $pdf->SetFont('Arial', '', 9);
    $pdf->Cell(15, 0.3, '', 0, 0, 'L');
   


    $pdf->Output("faktur_dropping_" . $no_faktur . "_" . $r1['tgl_trkasir'], "I");
}
?>

