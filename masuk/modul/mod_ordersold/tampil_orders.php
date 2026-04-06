<?php
session_start();
include "../../../configurasi/koneksi.php";
require('../../assets/pdf/fpdf.php');
include "../../../configurasi/fungsi_indotgl.php";
include "../../../configurasi/fungsi_rupiah.php";

$kdorders = $_GET['id'];

$query = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT * FROM orders WHERE kd_trbmasuk = '$kdorders'");
$res = mysqli_fetch_array($query);
$alamat = $db->query("select * from supplier where id_supplier='$res[id_supplier]' ");
$alt = $alamat->fetch_array();
//ambil header
$ah = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT * FROM setheader ");
$rh = mysqli_fetch_array($ah);

$pdf = new FPDF("P", "cm", "A4");

$pdf->SetMargins(1, 0.75, 1);
$pdf->AliasNbPages();
$pdf->AddPage();

$pdf->Image('../../images/logo_yasfi.png',3,1.45,2,2.5,'');
$pdf->ln(1);
$pdf->SetFont('helvetica', 'B', 24);
$pdf->SetTextColor(215, 0, 0);
$pdf->Cell(3.5, 0.4,'' , 0, 0, 'C');
$pdf->Cell(14, 0.4, $rh['satu'], 0, 1, 'C');

$pdf->ln(0.3);
$pdf->SetFont('Arial', 'B', 14);
$pdf->SetTextColor(0, 0, 0);
$pdf->Cell(3.5, 0.4,'' , 0, 0, 'C');
$pdf->Cell(14, 0.5,'No. SIPA : KS.08/1119/DPMPTSP/Apt/2023', 0, 1, 'C');
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(3.5, 0.4,'' , 0, 0, 'C');
$pdf->Cell(14, 0.5,'Jl. Ujung Harapan Kavling Assalam III RT. 002 RW 015 No.19A  ', 0, 1, 'C');
$pdf->Cell(3.5, 0.4,'' , 0, 0, 'C');
$pdf->Cell(14, 0.5,'Kelurahan Bahagia Kecamatan Babelan Kabupaten Bekasi 17612 ', 0, 1, 'C'); 

$pdf->SetLineWidth(0.15);
$pdf->Line(0.5, 4.15, 20.5, 4.15); //horisontal bawah

$pdf->ln(0.7);
$pdf->SetX(1);
$pdf->SetFont('Arial', 'B', 13);
$pdf->Cell(20.5, 0, 'SURAT PESANAN OBAT', 0, 0, 'C');


$pdf->ln(1);
$pdf->SetX(1);
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(2.5, 0, 'Nomor SP', 0, 0, 'L');
$pdf->Cell(0.5, 0, ':', 0, 0, 'L');
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(10, 0, $kdorders, 0, 0, 'L');

$pdf->ln(0.5);
$pdf->SetX(1);
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(2.5, 0, 'Tanggal', 0, 0, 'L');
$pdf->Cell(0.5, 0, ':', 0, 0, 'L');
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(10, 0, tgl_indo($res['tgl_trbmasuk']), 0, 0, 'L');

$pdf->ln(0.5);
$pdf->SetX(1);
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(2.5, 0, 'Kepada', 0, 0, 'L');
$pdf->Cell(0.5, 0, ':', 0, 0, 'L');
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(10, 0, $res['nm_supplier'], 0, 0, 'L');

    $text4 = substr($alt['alamat_supplier'], 0,75);
    $text5 = substr($alt['alamat_supplier'], 75,150);
    $text6 = strlen($alt['alamat_supplier']);
    
if($text6<75)
{
$pdf->ln(0.5);
$pdf->SetX(1);
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(2.5, 0, 'Alamat', 0, 0, 'L');
$pdf->Cell(0.5, 0, ':', 0, 0, 'L');
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(10, 0,$text4, 0, 0, 'L');
}
else
{
$pdf->ln(0.5);
$pdf->SetX(1);
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(2.5, 0, 'Alamat', 0, 0, 'L');
$pdf->Cell(0.5, 0, ':', 0, 0, 'L');
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(10, 0,$text4, 0, 1, 'L');

$pdf->ln(0.5);
$pdf->SetX(1);
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(2.5, 0, '', 0, 0, 'L');
$pdf->Cell(0.5, 0, '', 0, 0, 'L');
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(10, 0, $text5, 0, 1, 'L');
}

$pdf->ln(0.5);
$pdf->SetX(1);
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(2.5, 0, 'No Telp', 0, 0, 'L');
$pdf->Cell(0.5, 0, ':', 0, 0, 'L');
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(10, 0,$alt['tlp_supplier'], 0, 1, 'L');

$pdf->SetLineWidth(0);
$pdf->ln(0.7);
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(1, 0.7, 'No.', 1, 0, 'C');
$pdf->Cell(10.5, 0.7, 'Nama Obat', 1, 0, 'C');
$pdf->Cell(2, 0.7, 'Satuan', 1, 0, 'C');
$pdf->Cell(2, 0.7, 'Jumlah', 1, 0, 'C');
$pdf->Cell(4, 0.7, 'Keterangan', 1, 0, 'C');


$no = 1;
$query1 = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT *
FROM ordersdetail
WHERE kd_trbmasuk = '$kdorders'");

while ($lihat = mysqli_fetch_array($query1)) {
    $qty = ($lihat['qtygrosir_dtrbmasuk'] == "") ? $lihat['qty_dtrbmasuk'] : $lihat['qtygrosir_dtrbmasuk'];
    $satuan = ($lihat['satgrosir_dtrbmasuk'] == "") ? $lihat['sat_dtrbmasuk'] : $lihat['satgrosir_dtrbmasuk'];
    
    $text1 = substr($lihat['nmbrg_dtrbmasuk'], 0,54);
    $text2 = substr($lihat['nmbrg_dtrbmasuk'], 55,108);
    $text3 = strlen($lihat['nmbrg_dtrbmasuk']);
    
    if ($text3 >54){
    $pdf->ln(0.7);
    $pdf->SetFont('Arial', '', 11);
    $pdf->Cell(1, 0.7, $no,'LTR', 0, 'C');
    $pdf->Cell(10.5, 0.7, $text1,'LTR', 0, 'L');
    $pdf->Cell(2, 0.7, $satuan,'LTR', 0, 'C');
    $pdf->Cell(2, 0.7, $qty,'LTR', 0, 'C');
    $pdf->Cell(4, 0.7, terbilang($qty),'LTR', 0, 'C');
    
    $pdf->ln(0.7);
    $pdf->SetFont('Arial', '', 11);
    $pdf->Cell(1, 0.7,'','LBR', 0, 'C');
    $pdf->Cell(10.5, 0.7,$text2,'LBR', 0, 'L');
    $pdf->Cell(2, 0.7,'','LBR', 0, 'C');
    $pdf->Cell(2, 0.7,'','LBR', 0, 'C');
    $pdf->Cell(4, 0.7,'','LBR', 0, 'C');
    
    }
    
    else{
    $pdf->ln(0.7);
    $pdf->SetFont('Arial', '', 11);
    $pdf->Cell(1, 0.7, $no, 1, 0, 'C');
    $pdf->Cell(10.5, 0.7, $text1, 1, 0, 'L');
    $pdf->Cell(2, 0.7, $satuan, 1, 0, 'C');
    $pdf->Cell(2, 0.7, $qty, 1, 0, 'C');
    $pdf->Cell(4, 0.7, terbilang($qty), 1, 0, 'C');}
    
    $no++;
}
$pdf->ln(2);
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(10, 0, '', 0, 0, 'R');
$pdf->Cell(9, 0, 'Bekasi, ' .  tgl_indo($res['tgl_trbmasuk']), 0, 1, 'C');

$pdf->ln(0.4);
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(10, 0, '', 0, 0, 'R');
$pdf->Cell(9, 0, 'Apoteker Pemesan,', 0, 0, 'C');

$pdf->ln(2);
$pdf->SetFont('Arial', 'BU', 12);
$pdf->Cell(10, 0, '', 0, 0, 'R');
$pdf->Cell(9, 0,'apt. Heru Khoerudin, S.Si.',0, 0, 'C');

$pdf->ln(0.4);
$pdf->SetFont('Arial', '', 8);
$pdf->Cell(10, 0, '', 0, 0, 'R');
$pdf->Cell(9, 0,'SIPA : KS.08/1119/DPMPTSP/Apt/2023', 0, 0, 'C');
$pdf->Output("order-prekursor_".$res['tgl_trbmasuk'], "I");
