<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

//$text1 = 'Lorem ipsum dolor sit amet, velit aliquid omittantur pri ut, ad veritus suavitate eam. Id vim sint pericula salutatus, ne labores comprehensam vim. Id eos nullam appetere invidunt. Quo scaevola comprehensam at, cu facilisi temporibus pri. Vix ne elit reque dicta. Pro ut populo epicuri platonem, adhuc ornatus adipiscing id cum. Nec no omnes indoctum facilisis, choro possim adolescens mei ad. Tamquam nostrud appetere te eos, labore deserunt no est, sed in copiosae scaevola. Eum in tota definitionem, integre similique et nec. Ea eum tractatos neglegentur. Delectus intellegebat usu ea, eam at erat propriae, mundi sensibus persecuti eam ei. Ne facete commune sed. Iriure comprehensam eum ex, eos te dolore impetus recusabo. In case debet sit, per at volutpat salutatus rationibus. Sed id vitae sapientem forensibus, officiis periculis deseruisse at vel, sea altera eirmod ponderum ad. At erroribus consequat honestatis his, dicit percipitur has ne, modus oratio democritum in usu. Noluisse quaerendum no vis. Cum regione alterum ex, no semper delicata instructior nam, amet idque at pri. Quo te eius omnesque appareat, elitr molestiae mei eu. His tempor possit appetere ad, at ius corrumpit prodesset neglegentur. His ei iusto pertinax interesset.';
//$text = 'Lorem ipsum dolor sit amet, velit aliquid omittantur pri ut, ad veritus suavitate eam. Id vim sint pericula salutatus ne labores comprehensam vim. Id eos nullam appetere invidunt ne labores comprehensam vim. Id eos nullam appetere invidunt';

$text = $_POST['text'];

$words = explode("\n", $text);
$org_count = count($words);
$count = $org_count/2;
require_once('fpdf/fpdf.php');
require_once('fpdf/extension.php');

ob_start();

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

$y_axis = 5;
$x_axis = 30;
$pdf->SetY($y_axis);
foreach ($words as $key => $value) {
        $value = ltrim($value,' ');
        $value = rtrim($value,' ');
        while( $pdf->GetStringWidth( utf8_decode( $value ) ) < 180 ){
            $font_size+=1;
            $pdf->SetFont('Arial','',$font_size);
        }
        $cell_H = $font_size/3.5;
        $pdf->SetY($y_axis);
        $pdf->SetX($x_axis);
        //$pdf->Cell(190,$cell_H,utf8_decode($value),1,0,'L');
        $pdf->SetFont('Arial','',5);
        if($x_axis==30)
        {
            $x_axis = 220;
        }
        else if($x_axis == 220){
            $x_axis = 30;
            $y_axis += 10;
            //$pdf->Ln(10);
        }
}

$filename="file.pdf";
header('Content-type: application/pdf');
ob_clean();
$pdf->Output('I',$filename);