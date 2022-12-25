<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);



require_once('fpdf/fpdf.php');
require_once('fpdf/extension.php');

ob_start();


$pdf = new FPDF('P','mm','A4');
$pdf->AddPage();

$pdf->SetFont('Arial','',5);
$font_size = 6;

$text = 'Lorem';

while( $pdf->GetStringWidth( utf8_decode( $text ) ) < 38){
    $font_size+=1;
    $pdf->SetFont('Arial','',$font_size);
}

$cell_H = $font_size/3.5;
$y_axis = $font_size/40;
$x_axis = 0;
$pdf->SetY($y_axis);
$pdf->SetX($x_axis);

$pdf->Cell(40,$cell_H,$text.$font_size,1,0,'L');

$filename="file.pdf";
header('Content-type: application/pdf');
ob_clean();
$pdf->Output('I',$filename);