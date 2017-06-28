<?PHP /*
xslfo2pdf
Copyright (C) 2005       Tegonal GmbH

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.

Contact:
mike.toggweiler@tegonal.com
http://xslf2pdf.tegonal.com
*/ ?>
<?PHP
require_once("fpdf.php");


class MyPdf extends FPDF {

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
	}

  /**
   * Additional getter methods
   **/
  function GetFontFamily() {
    return $this->FontFamily;
  }

  function GetFontStyle() {
    return $this->FontStyle;
  }

  function GetFontSize() {
    return $this->FontSize;
  }
  function GetFontSizePt() {
    return $this->FontSizePt;
  }
  function GetDrawColor() {
    return $this->DrawColor;
  }
  function GetFillColor() {
    return $this->FillColor;
  }
  function GetTextColor() {
    return $this->TextColor;
  }

  function GetPageWidth() {
    return $this->w-$this->rMargin-$this->lMargin;
  }

  function GetLineWidth() {
    return $this->LineWidth;
  }

  function FontExists($family, $style='') {
    $fontkey=$family.$style;
    return isset($this->fonts[$fontkey]) ||
      isset($this->CoreFonts[$fontkey]);
  }

  /**
   * Methods to capture output
   */
  function startCapture() {
    if($this->state==2) {
      $buf = $this->pages[$this->page];
      $this->pages[$this->page] = "";
      return $buf;
    }
    else {
      $buf = $this->buffer;
      $this->buffer = "";
      return $buf;
    }
  }

  function endCapture($buffer) {
    if($this->state==2) {
      $buf = $this->pages[$this->page];
      $this->pages[$this->page] = $buffer;
      return $buf;
    }
    else {
      $buf = $this->buffer;
      $this->buffer = $buffer;
      return $buf;
    }
  }

  function appendBuffer($buffer) {
    if($this->state==2) {
      $this->pages[$this->page] .= $buffer;
    }
    else {
      $this->buffer .= $buffer;
    }

  }

  /**
   * Get the number of characters having space whithin the
   * given width, respecting not to break words
   */
  function GetNumberOfChars($width, $s, $fontsize=null) {
    //Get width of a string in the current font
    //	$s=(string)$s;
    if($fontsize==null)
    	$fontsize=$this->FontSize;
    //echo "width:$width s:$s font:$fontsize";
    $cw=&$this->CurrentFont['cw'];
    $w=0;
    $wordPos = 0;
    $l=strlen($s);
    $width = $width*1000/$fontsize;
    for($i=0;$i<$l;$i++) {
      $w+=$cw[$s{$i}];
      if ($s{$i} == ' ') {
	$wordPos = $i;
      }
      if ($w >= $width) {
	if ($wordPos > 0) {
	  return $wordPos;
	}
	else {
	  return -1;
	}
      }
    }
    return (($w <= $width)?$l:-1);
  }

  /**
   * Write string regarding to \n as line breaks
   * Return an array containing number of line, width and height
   * of this textual block. The align paramtere accepts L, R and C
   * get values using:
   * list($width, $height, $no, $firstX, $firstY, $lastX, $lastYl) = $pdf->Text2();
   **/
  function Text2($x, $y, $text, $align="L", $lineHeight=NULL, $xNewLine=NULL, $width=NULL) {
    if (!$xNewLine) {
      $xNewLine = $x;
    }

    $lines = explode('\n', $text);
    $height = ($lineHeight)?$lineHeight:$this->FontSize;
    //oesi - add parameter width for set the content-width of fo:block
    if($width!=NULL)
    {
    	$pw = $xNewLine+$width;
    }
    else
    	$pw = $this->GetPageWidth();
    //echo "x: $x width: $width pw: $pw<br>";
    $nb = 0;
    $maxWidth = 0;
    $sx = $x;
    $sy = $y;
    foreach($lines as $line)
    {

		$width = $this->GetStringWidth($line);
		do
      	{
			//$w=$this->w-$this->rMargin-$x;
			$w = $pw-$x; //oesi - changed
			//echo "w:$w<br>";
			//$wmax=($w-2*$this->cMargin)*1000/$this->FontSize;
			$noc = $this->GetNumberOfChars($w, $line);
			//echo "noc:$noc xNewLine: $xNewLine x:$x w:$w strlen:".strlen($line)." line:$line<br>";
			if ($noc == -1)
			{
				if ($x == $xNewLine)
				{
	    			//word has not enough space on one line, draw it beyond borders
	    			$noc = strlen($line);
	  			}
	  			else
	  			{
	  				//echo "NEWLINE";
	    			if ($nb == 0)
	    			{
	      				$sy += $height;
	      				$sx = $xNewLine;
	    			}
	    			//add newline and try again
	    			$y += $height;
	    			$nb++;
	    			$x = $xNewLine;
	    			continue;
	  			}
			}

			$showLine = substr($line, 0, $noc);
			$textWidth = $this->GetStringWidth($showLine);
			//echo "showline: $showLine<br>";
			//echo "nb:$nb x:$x w:$w textwidth:$textWidth showline:$showLine<br>";
			switch ($align)
			{
				case "R":
					$tx = $pw-$textWidth;
					break;
				case "C":
					$tx = ($pw-$x-$textWidth)/2 + $x;
					break;
				case "L":
				default:
					$tx = $x;
			}

			$this->Text($tx, $y+$height, $showLine);
			$line = trim(substr($line, $noc));
			$width = $this->GetStringWidth($line);

			if ($textWidth > $maxWidth)
			{
			  $maxWidth = $textWidth;
			}
			$y += $height;
			$nb++;
			//oesi - wenn er die zeileumbricht dann soll in der naechsten zeile um
			//1 eingerueckt werden sonst schaut des in einer tablle nicht gut aus
			//$this->GetStringWidth(" ") statt '1' is bessa - MP
			if($width!=0)
				$x = $xNewLine+$this->GetStringWidth(" ");
			else
				$x = $xNewLine;
		} while ($width > 0);
    }
    //$this->y = $y;
    $tot_height = $nb * $height;

    return array($maxWidth, $tot_height, $nb, $sx, $sy, $tx+$textWidth, $y-$height);
  }

  /*
   This extension allows to set a dash pattern and draw dashed lines or rectangles.
   $pdf->SetDash(4,2); //4mm on, 2mm off
   $pdf->Rect(20,30,170,20);
   $pdf->SetDash(); //restore no dash
  */
  function SetDash($black=false,$white=false)
  {
    if($black and $white)
      $s=sprintf('[%.3f %.3f] 0 d',$black*$this->k,$white*$this->k);
    else
      $s='[] 0 d';
    $this->_out($s);
  }

  /**
   * Draw polygons
   * $pdf->Polygon(array(50,115,150,115,100,20),'FD');
   **/
  function Polygon($points, $style='D')
  {
    //Draw a polygon
    if($style=='F')
      $op='f';
    elseif($style=='FD' or $style=='DF')
      $op='b';
    else
      $op='s';

    $h = $this->h;
    $k = $this->k;

    $points_string = '';
    for($i=0; $i<count($points); $i+=2){
      $points_string .= sprintf('%.2f %.2f', $points[$i]*$k, ($h-$points[$i+1])*$k);
      if($i==0)
	$points_string .= ' m ';
      else
	$points_string .= ' l ';
    }
    $this->_out($points_string . $op);
  }

  /**
   * Rounded rect
   * $pdf->RoundedRect(70, 30, 68, 46, 3.5, 'DF');
   */
  function RoundedRect($x, $y, $w, $h,$r, $style = '')
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
    $this->_out(sprintf('%.2f %.2f m',($x+$r)*$k,($hp-$y)*$k ));
    $xc = $x+$w-$r ;
    $yc = $y+$r;
    $this->_out(sprintf('%.2f %.2f l', $xc*$k,($hp-$y)*$k ));

    $this->_Arc($xc + $r*$MyArc, $yc - $r, $xc + $r, $yc - $r*$MyArc,
		$xc + $r, $yc);
    $xc = $x+$w-$r ;
    $yc = $y+$h-$r;
    $this->_out(sprintf('%.2f %.2f l',($x+$w)*$k,($hp-$yc)*$k));
    $this->_Arc($xc + $r, $yc + $r*$MyArc, $xc + $r*$MyArc, $yc + $r, $xc,
		$yc + $r);
    $xc = $x+$r ;
    $yc = $y+$h-$r;
    $this->_out(sprintf('%.2f %.2f l',$xc*$k,($hp-($y+$h))*$k));
    $this->_Arc($xc - $r*$MyArc, $yc + $r, $xc - $r, $yc + $r*$MyArc,
		$xc - $r, $yc);
    $xc = $x+$r ;
    $yc = $y+$r;
    $this->_out(sprintf('%.2f %.2f l',($x)*$k,($hp-$yc)*$k ));
    $this->_Arc($xc - $r, $yc - $r*$MyArc, $xc - $r*$MyArc, $yc - $r, $xc,
		$yc - $r);
    $this->_out($op);
  }

  /**
   * Rotate content. End rotation by call to Rotate(0)
   * $pdf->Rotate($angle,$x,$y);
   * $pdf->Text($x,$y,$txt);
   * $pdf->Rotate(0);
   */
  function Rotate($angle,$x=-1,$y=-1) {
    if($x==-1)
      $x=$this->x;
    if($y==-1)
      $y=$this->y;
    if($this->angle!=0)
      $this->_out('Q');
    $this->angle=$angle;
    if($angle!=0)
      {
        $angle*=M_PI/180;
        $c=cos($angle);
        $s=sin($angle);
        $cx=$x*$this->k;
        $cy=($this->h-$y)*$this->k;
        $this->_out(sprintf('q %.5f %.5f %.5f %.5f %.2f %.2f cm 1 0 0 1 %.2f %.2f cm',$c,$s,-$s,$c,$cx,$cy,-$cx,-$cy));
      }
  }

  // Draws a cubic B�zier curve from last draw point
  // Parameters:
  // - x1, y1: Control point 1
  // - x2, y2: Control point 2
  // - x3, y3: End point
  function CubicBezier($x1, $y1, $x2, $y2, $x3, $y3, $style='D') {
    if($style=='F')
      $op='f';
    elseif($style=='FD' or $style=='DF')
      $op='B';
    else
      $op='S';
    $this->_Curve($x1, $y1, $x2, $y2, $x3, $y3);
    $this->_out($op);
  }

  // Draws an ellipse
  // Parameters:
  // - x0, y0: Center point
  // - rx, ry: Horizontal and vertical radius (if ry = 0, draws a circle)
  // - angle: Orientation angle (anti-clockwise)
  // - astart: Start angle
  // - afinish: Finish angle
  // - style: Style of ellipse (draw and/or fill: D, F, DF, FD, C (D + close))
  // - line_style: Line style for ellipse. Array like for SetLineStyle
  // - fill_color: Fill color. Array with components (red, green, blue)
  // - nSeg: Ellipse is made up of nSeg B�zier curves
  function Ellipse($x0, $y0, $rx, $ry = 0, $angle = 0, $astart = 0, $afinish = 360, $style = '', $fill_color = null, $nSeg = 8) {
    if ($rx) {
      if (!(false === strpos($style, 'F')) && $fill_color) {
	list($r, $g, $b) = $fill_color;
	$this->SetFillColor($r, $g, $b);
      }
      switch ($style) {
      case 'F':
	$op = 'f';
	$line_style = null;
	break;
      case 'FD': case 'DF':
	$op = 'B';
	break;
      case 'C':
	$op = 's'; // small 's' means closing the path as well
	break;
      default:
	$op = 'S';
	break;
      }

      if ($rx) {
      if (!$ry)
	$ry = $rx;
      $rx *= $this->k;
      $ry *= $this->k;
      if ($nSeg < 2)
	$nSeg = 2;

      $astart = deg2rad((float) $astart);
      $afinish = deg2rad((float) $afinish);
      $totalAngle = $afinish - $astart;

      $dt = $totalAngle/$nSeg;
      $dtm = $dt/3;

      $x0 *= $this->k;
      $y0 = ($this->h - $y0) * $this->k;
      if ($angle != 0) {
	$a = -deg2rad((float) $angle);
	$this->_out(sprintf('q %.2f %.2f %.2f %.2f %.2f %.2f cm', cos($a), -1 * sin($a), sin($a), cos($a), $x0*10, $y0*10));
	$x0 = 0;
	$y0 = 0;
      }

      $t1 = $astart;
      $a0 = $x0 + ($rx * cos($t1));
      $b0 = $y0 + ($ry * sin($t1));
      $c0 = -$rx * sin($t1);
      $d0 = $ry * cos($t1);
      $this->_Point($a0 / $this->k, $this->h - ($b0 / $this->k));
      for ($i = 1; $i <= $nSeg; $i++) {
	// Draw this bit of the total curve
	$t1 = ($i * $dt) + $astart;
	$a1 = $x0 + ($rx * cos($t1));
	$b1 = $y0 + ($ry * sin($t1));
	$c1 = -$rx * sin($t1);
	$d1 = $ry * cos($t1);
	$this->_Curve(($a0 + ($c0 * $dtm)) / $this->k,
		      $this->h - (($b0 + ($d0 * $dtm)) / $this->k),
		      ($a1 - ($c1 * $dtm)) / $this->k,
		      $this->h - (($b1 - ($d1 * $dtm)) / $this->k),
		      $a1 / $this->k,
		      $this->h - ($b1 / $this->k));
	$a0 = $a1;
	$b0 = $b1;
	$c0 = $c1;
	$d0 = $d1;
      }
      if ($angle !=0) {
	$this->_out('Q');
      }
      }
      $this->_out($op);
    }
  }

  function ClosePath($style="D") {
    if($style=='F')
      $op='f';
    elseif($style=='FD' or $style=='DF')
      $op='B';
    else
      $op='S';

    $this->_out(sprintf('h %s', $op));
  }

  function _Style($style="D") {
    if($style=='F')
      $op='f';
    elseif($style=='FD' or $style=='DF')
      $op='B';
    else
      $op='S';

    $this->_out(sprintf('%s', $op));
  }

  // Draws a circle
  // Parameters:
  // - x0, y0: Center point
  // - r: Radius
  // - astart: Start angle
  // - afinish: Finish angle
  // - style: Style of circle (draw and/or fill) (D, F, DF, FD, C (D + close))
  // - line_style: Line style for circle. Array like for SetLineStyle
  // - fill_color: Fill color. Array with components (red, green, blue)
  // - nSeg: Ellipse is made up of nSeg B�zier curves
  function Circle($x0, $y0, $r, $astart = 0, $afinish = 360, $style = '', $fill_color = null, $nSeg = 8) {
    $this->Ellipse($x0, $y0, $r, 0, 0, $astart, $afinish, $style, $line_style, $fill_color, $nSeg);
  }


  // Sets a draw point
  // Parameters:
  // - x, y: Point
  function _Point($x, $y) {
    $this->_out(sprintf('%.2f %.2f m', $x * $this->k, ($this->h - $y) * $this->k));
  }

  // Draws a B�zier curve from last draw point
  // Parameters:
  // - x1, y1: Control point 1
  // - x2, y2: Control point 2
  // - x3, y3: End point
  function _Curve($x1, $y1, $x2, $y2, $x3, $y3) {
    $this->_out(sprintf('%.2f %.2f %.2f %.2f %.2f %.2f c', $x1 * $this->k, ($this->h - $y1) * $this->k, $x2 * $this->k, ($this->h - $y2) * $this->k, $x3 * $this->k, ($this->h - $y3) * $this->k));
  }


  // Draws a cubic B�zier curve from last draw point
  // Parameters:
  // - x2, y2: Control point 2
  // - x3, y3: End point

  function _CurveRef1($x2, $y2, $x3, $y3) {
    $this->_out(sprintf('%.2f %.2f %.2f %.2f v', $x2 * $this->k, ($this->h - $y2) * $this->k, $x3 * $this->k, ($this->h - $y3) * $this->k));
  }

  // Draws a cubic B�zier curve from last draw point
  // Parameters:
  // - x1, y1: Control point 1
  // - x3, y3: End point

  function _CurveRef2($x1, $y1, $x3, $y3) {
    $this->_out(sprintf('%.2f %.2f %.2f %.2f y', $x1 * $this->k, ($this->h - $y1) * $this->k, $x3 * $this->k, ($this->h - $y3) * $this->k));
  }

  // Draws a line from last draw point
  // Parameters:
  // - x, y: End point
  function _Line($x, $y) {
    $this->_out(sprintf('%.2f %.2f l', $x * $this->k, ($this->h - $y) * $this->k));
  }


    // Draws an ellipse
  // Parameters:
  // - x0, y0: Center point
  // - rx, ry: Horizontal and vertical radius (if ry = 0, draws a circle)
  // - angle: Orientation angle (anti-clockwise)
  // - astart: Start angle
  // - afinish: Finish angle
  // - style: Style of ellipse (draw and/or fill: D, F, DF, FD, C (D + close))
  // - line_style: Line style for ellipse. Array like for SetLineStyle
  // - fill_color: Fill color. Array with components (red, green, blue)
  // - nSeg: Ellipse is made up of nSeg B�zier curves
  function _Ellipse($x0, $y0, $rx, $ry = 0, $angle = 0, $astart = 0, $afinish = 360, $nSeg = 8) {
    if ($rx) {
      if (!$ry)
	$ry = $rx;
      $rx *= $this->k;
      $ry *= $this->k;
      if ($nSeg < 2)
	$nSeg = 2;

      $astart = deg2rad((float) $astart);
      $afinish = deg2rad((float) $afinish);
      $totalAngle = $afinish - $astart;

      $dt = $totalAngle/$nSeg;
      $dtm = $dt/3;

      $x0 *= $this->k;
      $y0 = ($this->h - $y0) * $this->k;
      if ($angle != 0) {
	//$a = -deg2rad((float) $angle);
	//$this->_out(sprintf('q %.2f %.2f %.2f %.2f %.2f %.2f cm', cos($a), -1 * sin($a), sin($a), cos($a), $x0*10, $y0*10));
	//echo "X:$x0:$y0:$a:".cos($a).":".sin($a)."<br>";
	//echo "X2:$x0:$y0<br>";
	//$x0 -=  cos($a);
	//$y0 -= $ry * sin($a);
      }

      $t1 = $astart;
      $a0 = $x0 + ($rx * cos($t1));
      $b0 = $y0 + ($ry * sin($t1));
      $c0 = -$rx * sin($t1);
      $d0 = $ry * cos($t1);
      //$this->_Point($a0 / $this->k, $this->h - ($b0 / $this->k));
      for ($i = 1; $i <= $nSeg; $i++) {
	// Draw this bit of the total curve
	$t1 = ($i * $dt) + $astart;
	$a1 = $x0 + ($rx * cos($t1));
	$b1 = $y0 + ($ry * sin($t1));
	$c1 = -$rx * sin($t1);
	$d1 = $ry * cos($t1);
	$this->_Curve(($a0 + ($c0 * $dtm)) / $this->k,
		      $this->h - (($b0 + ($d0 * $dtm)) / $this->k),
		      ($a1 - ($c1 * $dtm)) / $this->k,
		      $this->h - (($b1 - ($d1 * $dtm)) / $this->k),
		      $a1 / $this->k,
		      $this->h - ($b1 / $this->k));
	$a0 = $a1;
	$b0 = $b1;
	$c0 = $c1;
	$d0 = $d1;
      }
      if ($angle !=0) {
	//$this->_out('Q');
      }
    }
  }

}

?>
