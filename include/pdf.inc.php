<?php
/**
 * Ueberlagerte Klasse fuer die
 * Erstellung des PDF-Dokumentes
 */
class PDF extends FPDF
{

	var $tablewidths;
	var $footerset;
	var $headerset;

	/**
	 * Konstruktor
	 *
	 * @param $orientation: p oder portrait  = Hochformat
	 *                      l oder landscape = Querformat
	 *
	 *        $unit:        pt = Point
	 *                      mm = Millimeter
	 *                      cm = Zentimeter
	 *                      in = Inch
	 *
	 *        $format:      A3
	 *                      A4
	 *                      A5
	 *                      letter
	 *                      legal
	 */
	function __construct($orientation='P',$unit='mm',$format='A4')
	{
	    //Call parent constructor
	    parent::__construct($orientation,$unit,$format);
		//Initialization
	    $this->B=0;
	    $this->I=0;
	    $this->U=0;
	    $this->HREF='';
	}

	/**
	 * gibt eine Fusszeile aus
	 *
	 */
	function Footer()
	{
	    // Check if Footer for this page already exists (do the same for Header())
	    if(!isset($this->footerset[$this->page]) || !$this->footerset[$this->page]) {
	        $this->SetY(-30);
	        //Page number
	        $this->Cell(0,10,'Seite '.$this->PageNo().'/{nb}',0,0,'C');
	        // set footerset
	        $this->footerset[$this->page] = 1;
	    }
	}

	/**
	 * gibt eine Kopfzeile aus
	 *
	 */
	function Header()
	{
	    // Check if Header for this page already exists (do the same for Footer())
	    if(!isset($this->headerset[$this->page]) || !$this->headerset[$this->page]) {
	    $this->SetFont('Arial','B',10);
	        $this->SetY(25);
	        //Page number
	        $this->Cell(0,10,'',0,0,'C');
	        // set headerset
	        $this->SetY(100);
	        $this->headerset[$this->page] = 1;
	    }
	}

	/**
	 * Erzeugt eine Tabelle
	 * @param $datas       Array - 1. Zeile = Spaltenueberschrift
	 *                             x. Zeile = Eintraege
	 *        $lineheight  Zeilenhoehe
	 *        $aligns      Array - enthaelt die ausrichtung der Spalten (L=Left,R=Right,C=Center)
	 */
	function morepagestable($datas,$lineheight=12,$aligns)
	{
	    // some things to set and 'remember'
	    $l = $this->lMargin;
	    $startheight = $h = $this->GetY();
	    $startpage = $currpage = $this->page;

	    // calculate the whole width
	    foreach($this->tablewidths AS $width)
	    {
	        $fullwidth += $width;
	    }

	    // Now let's start to write the table
	    $r=0;
	    $markline=false;
	    foreach($datas AS $row => $data)
	    {
	   		$this->page = $currpage;
	        // write the horizontal borders
	        if($r<=1)
	        {
	        	$this->SetLineWidth(1.5);
	        	$this->Line($l,$h,$fullwidth+$l,$h);
	        }
	        else
	        {
	        	//$this->SetLineWidth(0.001);
	        	//$this->Line($l,$h,$fullwidth+$l,$h); NO-Line
	        }

	        //Farben fuer die zeilenmarkierung setzen
	        if($markline)
					$this->SetFillColor(230,230,230);
			else
					$this->SetFillColor(255,255,255);

			$markline=!$markline;

			// write the content and remember the height of the highest col
	        foreach($data AS $col => $txt)
	        {
	            $this->page = $currpage;
	            $this->SetXY($l,$h);
				$align=($r==0)?'C':$aligns[$col];
				if($r==0) $this->SetFont('Arial','B',$lineheight-2);
				else $this->SetFont('Arial','',$lineheight-2);

				//erste und zweite zeile nicht fuellen da sonst der rahmen verloren geht
				if($row==0)
				  $this->MultiCell($this->tablewidths[$col],$lineheight,$txt,0,$align,0);
				else
				  $this->MultiCell($this->tablewidths[$col],$lineheight,$txt,0,$align,1);

	            $l += $this->tablewidths[$col];

	            if($tmpheight[$row.'-'.$this->page] < $this->GetY())
	            {
	                $tmpheight[$row.'-'.$this->page] = $this->GetY();
	            }
	            if($this->page > $maxpage)
	                $maxpage = $this->page;
	        }

	        // get the height we were in the last used page
	        $h = $tmpheight[$row.'-'.$maxpage];
	        // set the "pointer" to the left margin
	        $l = $this->lMargin;
	        // set the $currpage to the last page
	        $currpage = $maxpage;
	        $r++;
	    }
	    // draw the borders
	    // we start adding a horizontal line on the last page
	    $this->page = $maxpage;
	    $this->SetLineWidth(1.5);
	     //Immer gleich
	    if($h<=660)
	    	$h=660;
	    else
	    	$h=810;

	    $this->Line($l,$h,$fullwidth+$l,$h);
	    // now we start at the top of the document and walk down
	    for($i = $startpage; $i <= $maxpage; $i++)
	    {
	        $this->page = $i;
	        $l = $this->lMargin;
	        $t  = ($i == $startpage) ? $startheight : $this->tMargin;
	        $lh = ($i == $maxpage)   ? $h : $this->h-$this->bMargin;
	        $this->SetLineWidth(1.5);
	        $this->Line($l,$t,$l,$lh);
	        $n=0;
	        $maxcol=count($this->tablewidths)-1;
	        foreach($this->tablewidths AS $width)
	        {
	            $l += $width;
	            if($n==$maxcol) $this->SetLineWidth(1.5);
	            else $this->SetLineWidth(0.5);
	            $this->Line($l,$t,$l,$lh);
	            $n++;
	        }
	    }
	// set it to the last page, if not it'll cause some problems
	$this->page = $maxpage;
	$this->SetY($h);
	}

}
?>
