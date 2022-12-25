<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$text = 'Lorem 
ipsum 
velit
dolor
long word is this and sample also
and this too
even word
sitp
grammer
amet,
   extra';
$word_processed = array();
$words = explode("\n", $text);
$org_count = count($words);
$count = $org_count/2;

require_once('fpdf/fpdf.php');
require_once('fpdf/extension.php');


if($org_count > 20)
{
    $pdf = new FPDF('P','mm',array(440,$count*38));
}
else{
    $pdf = new FPDF('P','mm',array(440,440));
}
//$pdf->AddPage();

$pdf->SetFont('Arial','',5);
$font_size = 6;
$pcount = 1;
$pairs = array();
$item_count = 0;
foreach ($words as $key => $value) {
    $font_size = 6;
    $value = ltrim($value,' ');
    $value = rtrim($value,' ');
    while( $pdf->GetStringWidth( utf8_decode( $value ) ) < 180 ){
        $font_size+=1;
        $pdf->SetFont('Arial','',$font_size);
    }
    $buffer = array();
    $cell_H = $font_size/3.5;
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
        $item_count+=1;
        $pairs = array();
    }
}
if(!empty($pairs))
{
    $item_count+=1;
    array_push($word_processed,$pairs);
}

echo "<pre>";
print_r($word_processed);
echo "</pre>";


foreach ($word_processed as $key => $array) {
    foreach ($array as $word => $attributes) {
        //print_r($array)."<br>";
    }
}

$filename="file.pdf";
//header('Content-type: application/pdf');

//$pdf->Output('I',$filename);