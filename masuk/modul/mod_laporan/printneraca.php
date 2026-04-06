<?php
session_start();
include "../../../configurasi/koneksi.php";
require('../../assets/pdf/fpdf.php');
include "../../../configurasi/fungsi_indotgl.php";
include "../../../configurasi/fungsi_rupiah.php";

if (empty($_SESSION['username']) and empty($_SESSION['passuser'])) {
    echo "<script>window.close();</script>";
    exit;
}

$d = $_GET['range'];
$de = explode("/", $d);
$awal = date("Y-m-d", strtotime($de[0]));
$akhir = date("Y-m-d", strtotime($de[1]));

$query1 = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT SUM(ttl_trkasir) AS penjualan FROM trkasir 
            WHERE tgl_trkasir BETWEEN '" . $awal . "' AND '" . $akhir . "'");
$query11 = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT SUM(ttl_trkasir) AS penjualan FROM trkasir 
            WHERE jenistx=1 and tgl_trkasir BETWEEN '" . $awal . "' AND '" . $akhir . "'");
$query12 = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT SUM(ttl_trkasir) AS penjualan FROM trkasir 
            WHERE jenistx=2 and tgl_trkasir BETWEEN '" . $awal . "' AND '" . $akhir . "'"); 
$query13 = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT SUM(ttl_trkasir) AS penjualan FROM trkasir 
            WHERE jenistx=3 and tgl_trkasir BETWEEN '" . $awal . "' AND '" . $akhir . "'"); 
$query14 = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT SUM(ttl_trkasir) AS penjualan FROM trkasir 
            WHERE jenistx=4 and tgl_trkasir BETWEEN '" . $awal . "' AND '" . $akhir . "'");             

$query2 = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT SUM(stok_barang*hrgsat_barang) AS aset_tdk_lancar FROM barang");

$query3 = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT SUM(trkasir.ttl_trkasir) AS piutang FROM trkasir 
            WHERE trkasir.id_carabayar = '3' AND trkasir.tgl_trkasir BETWEEN '" . $awal . "' AND '" . $akhir . "'");

$query4 = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT SUM(trbmasuk.ttl_trbmasuk) AS hutang FROM trbmasuk 
                WHERE trbmasuk.carabayar = 'KREDIT' AND trbmasuk.tgl_trbmasuk BETWEEN '" . $awal . "' AND '" . $akhir . "'");

$query5 = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT SUM(trbmasuk.ttl_trbmasuk) AS pembelian_cash FROM trbmasuk 
            WHERE trbmasuk.carabayar = 'LUNAS' AND trbmasuk.tgl_trbmasuk BETWEEN '" . $awal . "' AND '" . $akhir . "'");

$p = mysqli_fetch_array($query1);
$reg = mysqli_fetch_array($query11);
$grab = mysqli_fetch_array($query12);
$halodoc = mysqli_fetch_array($query13);
$market = mysqli_fetch_array($query14);
$o = mysqli_fetch_array($query5);
$x = mysqli_fetch_array($query3);
$y = mysqli_fetch_array($query4);
$asettdklancar = mysqli_fetch_array($query2);

$asetlancar = ($p['penjualan'] - $x['piutang'] - $o['pembelian_cash'] - $y['hutang']);
$neraca = ($p['penjualan']  - $y['hutang'] - $o['pembelian_cash']);

$pdf = new FPDF("P","cm","A4");
$pdf->SetMargins(1,0.3,1);
$pdf->AliasNbPages();
$pdf->AddPage();

$ah = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT * FROM setheader ");
$rh = mysqli_fetch_array($ah);

$pdf->Image('../../images/'.$rh['logo'],3,1,2,2.5,'');

$pdf->ln(1);
$pdf->SetFont('helvetica', 'B', 24);
$pdf->SetTextColor(0, 0, 0);
$pdf->Cell(3.5, 0.4,'' , 0, 0, 'C');
$pdf->Cell(14, 0.4, $rh['satu'], 0, 1, 'C');

$pdf->ln(0.3);
$pdf->SetFont('Arial', 'B', 14);
$pdf->SetTextColor(0, 0, 0);
$pdf->Cell(3.5, 0.4,'' , 0, 0, 'C');
$pdf->Cell(14, 0.5,$rh['lima'], 0, 1, 'C');
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(3.5, 0.4,'' , 0, 0, 'C');
$pdf->Cell(14, 0.5,'Jl. Ujung Harapan Kavling Assalam III RT. 002 RW 015 No.19A  ', 0, 1, 'C');
$pdf->Cell(3.5, 0.4,'' , 0, 0, 'C');
$pdf->Cell(14, 0.5,'Kelurahan Bahagia Kecamatan Babelan Kabupaten Bekasi 17612 ', 0, 1, 'C'); 

$pdf->SetLineWidth(0.15);
$pdf->Line(0.5, 3.6, 20.5, 3.6); //horisontal bawah

$pdf->SetLineWidth(0.05);
$pdf->SetFont('Arial','B',14);
$pdf->ln(0.3);
$pdf->Cell(19,0.7,"NERACA LABA RUGI",0,1,'C');
$pdf->SetFont('Arial','',10);
$pdf->Cell(19,0.5,"Periode : ".tgl_indo($awal)." s/d ".tgl_indo($akhir),0,1,'C');
$pdf->ln(0.5);

$pdf->SetFont('Arial','B',10);
$pdf->Cell(1, 0.7, 'No', 1, 0, 'C');
$pdf->Cell(12, 0.7, 'Keterangan', 1, 0, 'L');
$pdf->Cell(6, 0.7, 'Nilai', 1, 1, 'R');

$pdf->SetFont('Arial','',10);

// 1. Penjualan
$pdf->Cell(1, 0.7, '1.', 1, 0, 'C');
$pdf->Cell(12, 0.7, 'Total Penjualan', 1, 0, 'L');
$pdf->SetFont('Arial','B',10);
$pdf->Cell(6, 0.7, 'Rp ' . format_rupiah($p['penjualan']), 1, 1, 'R');
$pdf->SetFont('Arial','',10);

// Breakdown
$pdf->SetFont('Arial','',9);
$pdf->Cell(1, 0.5, '', 'L', 0, 'C');
$pdf->Cell(12, 0.5, '   - Penjualan Reguler', 'L', 0, 'L');
$pdf->Cell(6, 0.5, 'Rp ' . format_rupiah($reg['penjualan']), 'R', 1, 'R');

$pdf->Cell(1, 0.5, '', 'L', 0, 'C');
$pdf->Cell(12, 0.5, '   - Penjualan Grab Health', 'L', 0, 'L');
$pdf->Cell(6, 0.5, 'Rp ' . format_rupiah($grab['penjualan']), 'R', 1, 'R');

$pdf->Cell(1, 0.5, '', 'L', 0, 'C');
$pdf->Cell(12, 0.5, '   - Penjualan Halodoc', 'L', 0, 'L');
$pdf->Cell(6, 0.5, 'Rp ' . format_rupiah($halodoc['penjualan']), 'R', 1, 'R');

$pdf->Cell(1, 0.5, '', 'L', 0, 'C');
$pdf->Cell(12, 0.5, '   - Penjualan Marketplace', 'L', 0, 'L');
$pdf->Cell(6, 0.5, 'Rp ' . format_rupiah($market['penjualan']), 'R', 1, 'R');
$pdf->Cell(19, 0, '', 'T', 1);

$pdf->SetFont('Arial','',10);

// 2. Pembelian Cash
$pdf->Cell(1, 0.7, '2.', 1, 0, 'C');
$pdf->Cell(12, 0.7, 'Pembelian Cash', 1, 0, 'L');
$pdf->Cell(6, 0.7, 'Rp ' . format_rupiah($o['pembelian_cash']), 1, 1, 'R');

// 3. Piutang
$pdf->Cell(1, 0.7, '3.', 1, 0, 'C');
$pdf->Cell(12, 0.7, 'Piutang (Total Penjualan Belum Dibayar)', 1, 0, 'L');
$pdf->Cell(6, 0.7, 'Rp ' . format_rupiah($x['piutang']), 1, 1, 'R');

// 4. Hutang
$pdf->Cell(1, 0.7, '4.', 1, 0, 'C');
$pdf->Cell(12, 0.7, 'Hutang (Total Pembelian Belum Dibayar)', 1, 0, 'L');
$pdf->Cell(6, 0.7, 'Rp ' . format_rupiah($y['hutang']), 1, 1, 'R');

// 5. Total Asset Lancar
$pdf->Cell(1, 0.7, '5.', 1, 0, 'C');
$pdf->Cell(12, 0.7, 'Total Asset Lancar', 1, 0, 'L');
$pdf->Cell(6, 0.7, 'Rp ' . format_rupiah($asetlancar), 1, 1, 'R');

// 6. Total Asset Tidak Lancar
$pdf->Cell(1, 0.7, '6.', 1, 0, 'C');
$pdf->Cell(12, 0.7, 'Total Asset Tidak Lancar', 1, 0, 'L');
$pdf->Cell(6, 0.7, 'Rp ' . format_rupiah($asettdklancar['aset_tdk_lancar']), 1, 1, 'R');

// 7. Neraca Laba/Rugi
$pdf->SetFont('Arial','B',10);
$pdf->Cell(1, 0.7, '7.', 1, 0, 'C');
$pdf->Cell(12, 0.7, 'Neraca Laba/Rugi', 1, 0, 'L');
$pdf->Cell(6, 0.7, 'Rp ' . format_rupiah($neraca), 1, 1, 'R');

$pdf->ln(1);
$pdf->SetFont('Arial','',9);
$pdf->Cell(10,0.5,"Dicetak Oleh : ".$_SESSION['namalengkap'],0,0,'L');
$pdf->Cell(9,0.5,"Tanggal Cetak : ".date('d-m-Y H:i:s'),0,1,'R');

$pdf->Output("Laporan_Neraca_Laba_Rugi.pdf","I");
?>