<?php

#error_reporting(-1);
#ini_set('display_errors', 'On');

require_once('functions.php');
require_once 'vendor/autoload.php';


$set = dump_set($tmpfile);
define('K_PATH_IMAGES', getcwd());
// create new PDF document
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
// set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor("osPrint");

$pdf->SetHeaderData("/logo.png", 25, "Liturgie Elimkerk Ridderkerk",  $set['title'], array(0,64,255), array(0,64,128));
$pdf->setFooterData(array(0,64,0), array(0,64,128));
// set header and footer fonts
$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
// set default monospaced font
//$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
// set margins
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
// set auto page breaks
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
// set image scale factor
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
// ---------------------------------------------------------
// set default font subsetting mode
$pdf->setFontSubsetting(true);
// Set font
$pdf->SetFont('dejavusans', '', 14, '', true);
// Add a page
// This method has several options, check the source code documentation for more information.
$pdf->AddPage();
// print a block of text using Write()

foreach ($set['slides'] as $s){
	$pdf->SetFont('','B',14);
	$pdf->Write(0, $s['title'], '', 0, 'L', true, 0, false, false, 0);
	foreach ($s['contents'] as $key=>$c){
		if ($s['type']=="song")	{
			$pdf->SetFont('','B',13);
			$pdf->Write(0, $key, '', 0, 'C', true, 0, false, false, 0);
		}
		$pdf->SetFont('','',12);
		$pdf->Write(0, $c, '', 0, 'L', true, 0, false, false, 0);
	}
}

ob_end_clean();
$pdf->Output('liturgie.pdf', 'I');


?>
