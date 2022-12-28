<?php

use function PHPSTORM_META\type;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


function wh_log($log_msg)
{
    $log_filename = "log";
    if (!file_exists($log_filename)) {
        // create directory/folder uploads.
        mkdir($log_filename, 777, true);
    }
    $log_file_data = $log_filename . '/log_' . date('d-M-Y') . '.log';
    // if you don't add `FILE_APPEND`, the file will be erased each time you add a log
    file_put_contents($log_file_data, $log_msg . "\n", FILE_APPEND);
}


if(isset($_POST['text']) && $_POST['text'] != '')
{
    $text = $_POST['text'];
    $text = ltrim($text);
    $text = rtrim($text);
    //wh_log("Input Trimmed");
    //wh_log($text);
}
else{
    header('location:index.php');
}

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

// $text = 'Gr0ß Dölln
// Gr0ß Dölln
// Gr0ß Dölln
// Gr0ß Dölln
// Gr0ß Dölln
// Gr0ß Dölln';



// $text = $_POST['text'];
// $text = ltrim($text);
// $text = rtrim($text);

$word_processed = array();
$words = explode("\n", $text);

wh_log('============');
//$var = json_encode($words,JSON_PRETTY_PRINT);
//wh_log(gettype($var));
//wh_log(json_encode($words,JSON_PRETTY_PRINT));
wh_log(' - - - - - - - - - - - - - - ');
//ob_start();

/* PART 1 - START */

// THIS portin determines each word's font and cell width.

$pdf->SetFont('Arial','B',5);
$font_size = 6;
$pcount = 1;
$pairs = array();
foreach ($words as $key => $value) {
//    wh_log('Key :'.$key." | Value:".$value);
    $font_size = 6;
    $value = ltrim($value,' ');
    $value = rtrim($value,' ');
    wh_log('Key :'.$key." | Value:".$value);
    while( $pdf->GetStringWidth( utf8_decode( $value ) ) < 187 ){
        $font_size+=1;
        $pdf->SetFont('Arial','B',$font_size);
    }
    $buffer = array();
    $cell_H = $font_size/2.2;
    $buffer['word'] = $value;
    $buffer['font'] = $font_size;
    $buffer['cell_height'] = $cell_H;
    array_push($pairs,$buffer);
//    $pairs[$value] = $buffer;
//    wh_log(json_encode($pairs,JSON_PRETTY_PRINT));
//    wh_log('Push :'.$key);
    //print_r($pairs);
    $pdf->SetFont('Arial','B',5);
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
wh_log("- - - - - - - - - - - - - - ");
wh_log(json_encode($word_processed,JSON_PRETTY_PRINT));
wh_log("------------------------");
$page_Height = 0;

foreach ($word_processed as $key => $array) {
    //wh_log("key:".$key." | value:".$value);
    $buffer_cell_H = 0;
    foreach ($array as $word => $attributes)
    {
        $buffer_cell_H = $attributes['cell_height']>$buffer_cell_H?$attributes['cell_height']:$buffer_cell_H;
    }
    $page_Height += $buffer_cell_H;
}
wh_log("- - - - - - - - - - - - - - ");
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
    wh_log("Page Height:".$page_Height);
}
else{
    $pdf = new FPDF('P','mm',array(440,440));
    wh_log("Page Height: 440");
}
wh_log("- - - - - - - - - - - - - - ");
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
        $pdf->Cell(190,$buffer_cell_H,utf8_decode($attributes['word']),1,0,'L');
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