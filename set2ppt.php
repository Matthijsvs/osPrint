<?php
require_once 'vendor/autoload.php';
require_once('functions.php');


use PhpOffice\PhpPresentation\PhpPresentation;
use PhpOffice\PhpPresentation\IOFactory;
use PhpOffice\PhpPresentation\Slide;
use PhpOffice\PhpPresentation\Style\Color;
use PhpOffice\PhpPresentation\Slide\AbstractBackground;
use PhpOffice\PhpPresentation\Style\Alignment;


// Create a shape (drawing)
$basefile = "base.odp";
$outfile = __DIR__ ."/sample.odp";

$set = dump_set($tmpfile);

#this will change the current working directory, so we have to process the set before this.
#$pptReader = IOFactory::createReader('ODPresentation');
$oPHPPresentation = new PhpPresentation(); #$pptReader->load($basefile);

$oPHPPresentation->getDocumentProperties()->setCreator('PHPOffice')
    ->setLastModifiedBy('Set2PPT')
    ->setTitle($set['title']);

$currentSlide = $oPHPPresentation->getActiveSlide();
$shape = $oSlide->createRichTextShape()
	->setHeight(25)
	->setWidth(940)
	->setOffsetX(10)
	->setOffsetY(10);
$shape->getActiveParagraph()->getAlignment()->setHorizontal( Alignment::HORIZONTAL_CENTER );
$textRun = $shape->createTextRun($set['title']);

foreach ($set['slides'] as $s){
	foreach ($s['contents'] as $key=>$c){
		$title= $s['title'];
		$oSlide = $oPHPPresentation->createSlide();  
		$oSlide->setName($title);

		$shape = $oSlide->createRichTextShape()
		->setHeight(45)
		->setWidth(940)
		->setOffsetX(10)
		->setOffsetY(10);
		$shape->getActiveParagraph()->getAlignment()->setHorizontal( Alignment::HORIZONTAL_CENTER );
		$textRun = $shape->createTextRun($title);
		$textRun->getFont()->setBold(true)
					   ->setSize(28)
					   ->setColor( new Color( '000' ) );

		$shape2 = $oSlide->createRichTextShape()
		->setHeight(640)
		->setWidth(940)
		->setOffsetX(10)
		->setOffsetY(100);
		$shape2->getActiveParagraph()->getAlignment()->setHorizontal( Alignment::HORIZONTAL_CENTER );
		$textRun = $shape2->createTextRun($c);
		$textRun->getFont()->setBold(false)->setSize(28);

	}
}

$oWriterODP = IOFactory::createWriter($oPHPPresentation, 'ODPresentation');
$oWriterODP->save($outfile);
#ob_end_clean();
#header("Content-Type: application/vnd.openxmlformats-officedocument.presentationml.presentation");
#header("Content-Disposition: attachment; filename=Presentation.pptx");

#$oWriterODP->save('php://output');
?>
