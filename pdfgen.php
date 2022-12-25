<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


// if(!isset($_POST['text']))
// {
//     header('location:index.php');
    
// }
// else{
//     $text = $_POST['text'];
//     $text = ltrim($text);
//     $text = rtrim($text);
// }

require_once('fpdf/fpdf.php');
require_once('fpdf/extension.php');
$pdf = new FPDF();

// $text = 'Lorem 
// ipsum 
// velit
// dolor
// long word is this and sample also
// and this too
// even word
// sitp
// grammer
// amet,
//    extra';



$text = $_POST['text'];
$text = ltrim($text);
$text = rtrim($text);

$word_processed = array();
$words = explode("\n", $text);

//ob_start();

/* PART 1 - START */

// THIS portin determines each word's font and cell width.

$pdf->SetFont('Arial','',5);
$font_size = 6;
$pcount = 1;
$pairs = array();
foreach ($words as $key => $value) {

    $font_size = 6;
    $value = ltrim($value,' ');
    $value = rtrim($value,' ');
    while( $pdf->GetStringWidth( utf8_decode( $value ) ) < 187 ){
        $font_size+=1;
        $pdf->SetFont('Arial','',$font_size);
    }
    $buffer = array();
    $cell_H = $font_size/2.2;
    $buffer['font'] = $font_size;
    $buffer['cell_height'] = $cell_H;
    $pairs[$value] = $buffer;
    //print_r($pairs);
    $pdf->SetFont('Arial','',5);
    if($pcount < 2)
    {

        $pcount += 1;
    }
    elseif($pcount == 2)
    {
        $pcount = 1;
        array_push($word_processed,$pairs);
        $pairs = array();
    }
}
if(!empty($pairs))
{
    array_push($word_processed,$pairs);
}

$page_Height = 0;

foreach ($word_processed as $key => $array) {
    $buffer_cell_H = 0;
    foreach ($array as $word => $attributes)
    {
        $buffer_cell_H = $attributes['cell_height']>$buffer_cell_H?$attributes['cell_height']:$buffer_cell_H;
    }
    $page_Height += $buffer_cell_H;
}

// echo "<pre>";
// print_r($word_processed);
// echo "</pre>";
$page_Height += 20;

/* PART 1 - END */

/* PART 2 - START */

// This portion will start generation of pdf and insertion of cells
if(count($words)>6)
{
    $pdf = new FPDF('P','mm',array(440,$page_Height));
}
else{
    $pdf = new FPDF('P','mm',array(440,440));
}
$pdf->AddPage();
$pdf->SetAutoPageBreak(0);
$y_axis = 5;
$x_axis = 30;

foreach ($word_processed as $key => $array) {
    $buffer_cell_H = 0;
    foreach ($array as $word => $attributes) 
    {
        $buffer_cell_H = $attributes['cell_height']>$buffer_cell_H?$attributes['cell_height']:$buffer_cell_H;
    }
    foreach ($array as $word => $attributes) {
        $pdf->SetY($y_axis);
        $pdf->SetX($x_axis);
        $pdf->SetFont('Arial','',$attributes['font']);
        $pdf->Cell(190,$buffer_cell_H,utf8_decode($word),1,0,'L');
        //$pdf->Cell(190,$buffer_cell_H,round($attributes['cell_height'],1),1,0,'L');
        // $pdf->Cell(190,$buffer_cell_H,round($buffer_cell_H,1),1,0,'L');
        if($x_axis==30)
        {
            $x_axis = 220;
        }
        else if($x_axis == 220){
            $x_axis = 30;
            $y_axis += $buffer_cell_H;
            //$pdf->Ln(10);
        }
    }
}

/* PART 2 - END */

$filename="file.pdf";
//header('Content-type: application/pdf');
//ob_clean();
$pdf->Output('I',$filename);