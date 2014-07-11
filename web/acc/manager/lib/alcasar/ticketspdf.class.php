<?php

require_once('../lib/fpdf/fpdf.php');
/*
TODO :
- réécriture au format PHP5
- Ajout d'une fonction pour le choix des logos à ajouter
- Meilleur calcule du découpage de la page lors de l"utilisation de format de papier plus atypique
- Ajout des commentaires au format PHPDoc
- Intégration au package Alcasar for alcasar V3.
*/

/**
 * Alcasar
 *
 * class for Alcasar web interface
 *
 * @package		Alcasar
 * @author		Alcasar Dev Team (steweb57)
 * @copyright	Copyright (c) 2013 , www.alcasar.net
 * @license		GPL V3
 * @link		http://www.alcasar.net
 * @version		0.1
 */

class ticketsPDF extends FPDF
{
	/****************************************************************
	*																*
	*					Private properties							*
	*																*
	*****************************************************************/
	var $_COUNTX	= 0;		// Current x ticket position
	var $_COUNTY	= 1;		// Current y ticket position
	//var $_image		= array();	// Images to add to each ticket	
	var $_title;				// Title text for each ticket
	var $_footer;				// Footer text for each ticket
	var $_X;					// Number of tickets horizontally
	var $_Y;					// Number of tickets vertically
	var $_width;				// Ticket width
	var $_height;				// Ticket height
	var $_margin	= 5;		// Margin
	var $_padding	= 5;		// Padding
	
	/**
	* Constructor method : <b>ticketsPDF(int x,int y)</b>
	*
	* <p>Create the class instance</p>
	*
	* @access public
	* @param int $x, $y
	* @return void
	*/
	function ticketsPDF($x = 1, $y = 1) {
		
		// For the moment, only "P", "mm", "A4" format
		parent::FPDF('P', 'mm', 'A4');
		$this->AddPage();
		/*
		$x = number of tickets horizontally
		$y = number of tickets vertically
		*/
		$this->_X = $x;
		$this->_Y = $y;
		
		$this->_width = (int)((210 - ($this->_margin*2))/$this->_X);
		$this->_height = (int)((297 - ($this->_margin*2))/$this->_Y);
	}
	
	/****************************************************************
	*																*
	*					Private methods								*
	*																*
	*****************************************************************/
	function _ticketHeader()
	{
		$currentX = $this->_margin + (($this->_COUNTX - 1) * $this->_width ) + $this->_padding;
		$currentY = $this->_margin + (($this->_COUNTY - 1) * $this->_height ) + $this->_padding;
		$this->SetXY($currentX, $currentY);
		
		$this->SetFont('Arial','B',9);
		$this->SetTextColor(250,1,10);
		$this->Cell($this->_width-10,10,$this->_title,0,1,'C');
	}
	function _ticketFooter()
	{
		$currentX = $this->_margin + (($this->_COUNTX - 1) * $this->_width ) + $this->_padding;
		$currentY = (($this->_COUNTY) * $this->_height) - 5;
		$this->SetXY($currentX, $currentY);
		
		$this->SetTextColor(0);
		$this->SetFont('Arial','',9);
		$this->Cell($this->_width-10,10,$this->_footer,0,0,'C');
	}
	function _addTicketsImages()
	{
		// Add Alcasar Logo
		$currentX = $this->_margin + (($this->_COUNTX - 1) * $this->_width ) + $this->_padding;
		$currentY = $this->_margin + (($this->_COUNTY - 1) * $this->_height ) + $this->_padding;
		$this->Image('../../../images/logo-alcasar.png',$currentX,$currentY,20);
		// Add other logo
		$currentX = $this->_margin + (($this->_COUNTX) * $this->_width ) - $this->_padding - 15;
		$this->Image('../../../images/organisme.png',$currentX,$currentY,15);
		
	}
	//fonction arc de cercle
	function _Arc($x1, $y1, $x2, $y2, $x3, $y3)
	{
		$h = $this->h;
		$this->_out(sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c ', $x1*$this->k, ($h-$y1)*$this->k,
			$x2*$this->k, ($h-$y2)*$this->k, $x3*$this->k, ($h-$y3)*$this->k));
	}
	// gestion automatique du format UFT8
	function _cleanUTF8($txt='')
	{
		if (mb_detect_encoding($txt)=="UTF-8")
		{
			//exit(utf8_decode($txt));
			return utf8_decode($txt);
		} else {
			
			//exit($txt);
			return $txt;
		}
	}
	/****************************************************************
	*																*
	*					Public methods								*
	*																*
	*****************************************************************/	
	
	//fonction rectangle
	//Rectangle : x, y : coin supérieur gauche du rectangle.w, h : largeur et hauteur. r : rayon des coins arrondis.
	//style : comme celui de Rect() : F, D (défaut), FD ou DF. 
	function RoundedRect($x, $y, $w, $h, $r, $style = '')
	{
		$k = $this->k;
		$hp = $this->h;
		if($style=='F')
			$op='f';
		elseif($style=='FD' or $style=='DF')
			$op='B';
		else
			$op='S';
		$MyArc = 4/3 * (sqrt(2) - 1);
		$this->_out(sprintf('%.2F %.2F m',($x+$r)*$k,($hp-$y)*$k ));
		$xc = $x+$w-$r ;
		$yc = $y+$r;
		$this->_out(sprintf('%.2F %.2F l', $xc*$k,($hp-$y)*$k ));

		$this->_Arc($xc + $r*$MyArc, $yc - $r, $xc + $r, $yc - $r*$MyArc, $xc + $r, $yc);
		$xc = $x+$w-$r ;
		$yc = $y+$h-$r;
		$this->_out(sprintf('%.2F %.2F l',($x+$w)*$k,($hp-$yc)*$k));
		$this->_Arc($xc + $r, $yc + $r*$MyArc, $xc + $r*$MyArc, $yc + $r, $xc, $yc + $r);
		$xc = $x+$r ;
		$yc = $y+$h-$r;
		$this->_out(sprintf('%.2F %.2F l',$xc*$k,($hp-($y+$h))*$k));
		$this->_Arc($xc - $r*$MyArc, $yc + $r, $xc - $r, $yc + $r*$MyArc, $xc - $r, $yc);
		$xc = $x+$r ;
		$yc = $y+$r;
		$this->_out(sprintf('%.2F %.2F l',($x)*$k,($hp-$yc)*$k ));
		$this->_Arc($xc - $r, $yc - $r*$MyArc, $xc - $r*$MyArc, $yc - $r, $xc, $yc - $r);
		$this->_out($op);
	}

	function newTickets()
	{
		$this->_COUNTX++;
		if ($this->_COUNTX > $this->_X) {
			// Row full, we start a new one
			$this->_COUNTX=1;
			$this->_COUNTY++;
			if ($this->_COUNTY > $this->_Y) {
				// End of page reached, we start a new one
				$this->_COUNTY=1;
				$this->AddPage();
			}
		}
		
		$this->_ticketHeader();	
		$this->_ticketFooter();
		
		$currentX = $this->_margin + (($this->_COUNTX - 1) * $this->_width ) + $this->_padding;
		$currentY = $this->_margin + (($this->_COUNTY - 1) * $this->_height ) + $this->_padding;
		$this->SetXY($currentX, $currentY+10);
		
		//création du cadre arrondi qui entoure le ticket d'impression
		//x, y : coin supérieur gauche du rectangle.w, h : largeur et hauteur. r : rayon des coins arrondis.
		//style : comme celui de Rect() : F, D (défaut), FD ou DF. 
		$RoundedRectX = (($this->_COUNTX - 1) * ($this->_width))+($this->_width/6);
		$this->RoundedRect($RoundedRectX, $currentY+10, ($this->_width-($this->_width/4)), $this->_height/2, 3.5, 'D');
		
		$this->_addTicketsImages();
	}
	function addInfos($title, $value)
	{
		$currentX = $this->_margin + (($this->_COUNTX - 1) * $this->_width ) + $this->_padding;
		$this->SetX($currentX);
		
		$this->SetTextColor(0);
		$this->SetFont('Arial','',9);
		$this->Cell(($this->_width/2)-5,5,$title,0,0,'R');
		$this->SetFont('Arial','B',9);
		$this->Cell(($this->_width/2)-5,5,$value,0,1,'L');
	}
	function addComment($txt, $align = "J")
	{
		$currentX = $this->_margin + (($this->_COUNTX - 1) * $this->_width ) + $this->_padding;
		$this->SetX($currentX);
		
		$this->SetTextColor(0);
		$this->SetFont('Arial','',8);
				
		//$this->Cell($this->_width-10,5,$txt,0,1,$align);
		$this->MultiCell($this->_width - $this->_padding - 5, 5, $txt, 0, $align);
	}
	function setTicketsTitle($txt)
	{
		$this->_title = $txt;
	}
	function setTicketsFooter($txt)
	{
		$this->_footer = $txt;
	}
	function AcceptPageBreak()
	{
		return false;
	}
	function Cell($w, $h=0, $txt='', $border=0, $ln=0, $align='', $fill=false, $link='')
	{
		$txt = $this->_cleanUTF8($txt);
		parent::Cell($w, $h, $txt, $border, $ln, $align, $fill, $link);
	}
	/*
	//For futur use
	function addTicketsImage($file, $x, $y, $w)
	{
		$this->_image[] = array('file'=>$file, 'x'=>$x, 'y'=>$y, 'w'=>$w);
	}
	*/
	
	/*
	Function Ln($h=null)
	{
		parent::Ln($h);
		
		$currentX = $this->_margin + (($this->_COUNTX - 1) * $this->_width ) + $this->_padding;
		$this->SetX($currentX);
	}
	*/
}
?>