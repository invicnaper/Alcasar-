<?php
require('../lib/fpdf/fpdf.php');

class fichePDF extends FPDF {

	function Header()
	{

	}
	function Footer()
	{
		//Positionnement à 1,5 cm du bas
		$this->SetY(-15);
		//Arial italique 8
		$this->SetFont('Arial','I',8);
		//Couleur du texte en gris
		$this->SetTextColor(128);
		//Numéro de page
		$this->Cell(0,10,'Page '.$this->PageNo(),0,0,'C');
	}

	function lirefichier($fichier)
	{
		$this->AddPage();
		//Lecture des lignes du fichier
		$lines = file($fichier);
		$n = 1;
		foreach($lines as $line){
			//Times 12
			$this->SetFont('Times','',10);
			//Sortie du texte justifié
			$this->Cell(0,5,utf8_decode($line));
			$this->Ln();
			++$n;
			if ($n > (50)){ // on affiche 50 ligne par page soit 5 fiches usagers
				$this->AddPage();
				$n = 1;
			}
		}
	}
}

function getImportFile($importFileName, $format = "txt"){
	$importFile = "/tmp/$importFileName.pwd";
	if(is_file($importFile)&&is_readable($importFile)){
		if ($format=="txt"){
			//telechargement
			$taille=filesize($importFile);
			header("Content-Type: application/x-download");
			header("Content-Length: $taille");
			header("Content-Disposition: attachment; filename=\"$importFileName.txt\"");
			header("Cache-Control: private, max-age=0, must-revalidate");
			header("Pragma: public");
			header("Content-Type: application/force-download; filename=\"$importFileName.txt\"");
			ini_set("zlib.output_compression","0");
			readfile($importFile);
			exit();
		}elseif ($format=="pdf"){
			$pdf=new fichePDF();
			$pdf->lirefichier($importFile);
			$pdf->Output($importFileName.".pdf","D");
		}else{
			getImportFile($importFileName,"txt");
		}
	} else {
		return false;
	}
}
if (isset($_GET['file']) && $_GET['file']){
	if (isset($_GET['format'])){
		$format = $_GET['format'];
	} else {
		$format = "txt";
	}
	if (getImportFile($_GET['file'], $format)){
		//fichier en cour de téléchargement
	} else {
		echo "erreur 2 ";
	}
} else {
	echo "erreur 1 ";
}
?>
