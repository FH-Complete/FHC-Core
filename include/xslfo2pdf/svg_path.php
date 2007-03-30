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
class SVG_Path extends SVG_StyleObject {

  public function process(DOMNode $node, $sargs="") {
    $this->initLocalAttribute($node, "d");
    $xOrig = $this->getContext("xOrig");
    $yOrig = $this->getContext("yOrig");

    $d = $this->getContext("d");
    //replace spacial characters
    $d = preg_replace("/[\n,]/i", " ", $d);
    //add spaces except on points
    $d = preg_replace("/([AMLZHVCSQTA])([\d-]+)/i", "\${1} \${2}", $d);
    $d = preg_replace("/(\d+)([AMLZHVCSQTA])/i", "\${1} \${2}", $d);
    $d = preg_replace("/\s\s/i", " ", $d);
    $params = explode(" " , $d);
    $x = $xOrig+$this->getContext("x");
    $y = $yOrig+$this->getContext("y");
    $ix = $x;
    $iy = $y;
    $pdf = $this->getPdf();
    $pdf->_Point($x, $y);
    $buf = $pdf->startCapture();
    for($i=0; $i<sizeof($params); ) {
      $lastI = $i;
      if ($params[$i] == "") {
	$i++;
	continue;
      }
      switch ($params[$i]) {
      case "M":
	//move absolute
	$x = $this->getSize($params[$i+1])+$xOrig;
	$y = $this->getSize($params[$i+2])+$yOrig;
	$pdf->_Point($x, $y);
	$ix = $x;
	$iy = $y;
	$i += 3;
	break;
      case "m":
	//move relative
	$x += $this->getSize($params[$i+1]);
	$y += $this->getSize($params[$i+2]);
	$pdf->_Point($x, $y);
	$ix = $x;
	$iy = $y;
	$i += 3;
	break;
      case "z":
      case "Z":
	//closepath
	$pdf->ClosePath($sargs);
	$x = $ix;
	$y = $iy;
	++$i;
	break;
      case "L":	
	//absolute lineto
	$x2 = $this->getSize($params[$i+1])+$xOrig;
	$y2 = $this->getSize($params[$i+2])+$yOrig;
	//echo "Line:$x:$y:$x2:$y2:$sargs<br>";
	$pdf->_Line($x2, $y2);
	$x = $x2;
	$y = $y2;
	$i += 3;
	break;
      case "l":
	//relative lineto
	$x2 = $this->getSize($params[$i+1])+$x;
	$y2 = $this->getSize($params[$i+2])+$y;
	$pdf->_Line($x2, $y2);
	$x = $x2;
	$y = $y2;
	$i += 3;
	break;
      case "H":	
	//absolute horizontal lineto
	$x2 = $this->getSize($params[$i+1])+$xOrig;
	$pdf->_Line($x2, $y);
	$x = $x2;
	$i += 2;
	break;
      case "h":
	//relative horizontal lineto
	$x2 = $this->getSize($params[$i+1]);
	$pdf->_Line($x+$x2, $y);
	$x += $x2;
	$i += 2;
	break;
      case "V":	
	//absolute vertical lineto
	$y2 = $this->getSize($params[$i+1])+$yOrig;
	$pdf->_Line($x, $y2);
	$y = $y2;
	$i += 2;
	break;
      case "v":
	//relative vertical lineto
	$y2 = $this->getSize($params[$i+1]);
	$pdf->_Line($x, $y+$y2);
	$y += $y2;
	$i += 2;
	break;
      case "C":
	//absolute cubic bezier
	$x1 = $this->getSize($params[$i+1])+$xOrig;
	$y1 = $this->getSize($params[$i+2])+$yOrig;
	$x2 = $this->getSize($params[$i+3])+$xOrig;
	$y2 = $this->getSize($params[$i+4])+$yOrig;
	$x = $this->getSize($params[$i+5])+$xOrig;
	$y = $this->getSize($params[$i+6])+$yOrig;
	$pdf->_Curve($x1, $y1, $x2, $y2, $x, $y);
	$i += 7;
	break;
      case "c":
	//relative cubic bezier
	$x1 = $this->getSize($params[$i+1])+$x;
	$y1 = $this->getSize($params[$i+2])+$y;
	$x2 = $this->getSize($params[$i+3])+$x;
	$y2 = $this->getSize($params[$i+4])+$y;
	$x += $this->getSize($params[$i+5]);
	$y += $this->getSize($params[$i+6]);
	$pdf->_Curve($x1, $y1, $x2, $y2, $x, $y);
	$i += 7;
	break;
      case "S":
	//TODO: fix
	//absolute cubic bezier with first control reflection
	$x2 = $this->getSize($params[$i+1])+$xOrig;
	$y2 = $this->getSize($params[$i+2])+$yOrig;
	$x = $this->getSize($params[$i+3])+$xOrig;
	$y = $this->getSize($params[$i+4])+$yOrig;
	$pdf->_CurveRef1($x2, $y2, $x, $y);
	$i += 5;
	break;
      case "s":
	//TODO: fix
	//relative cubic bezier with first control point reflection
	$x2 = $this->getSize($params[$i+1])+$x;
	$y2 = $this->getSize($params[$i+2])+$y;
	$x += $this->getSize($params[$i+3]);
	$y += $this->getSize($params[$i+4]);
	$pdf->_CurveRef1($x2, $y2, $x, $y);
	$i += 5;
	break;
      case "Q":
	//absolute quadratic bezier
	$x1 = $this->getSize($params[$i+1])+$xOrig;
	$y1 = $this->getSize($params[$i+2])+$yOrig;
	$ex = $this->getSize($params[$i+3])+$xOrig;
	$ey = $this->getSize($params[$i+4])+$yOrig;
	list($x1, $y1, $x2, $y2, $x, $y) = 
	  $this->quadratic2CubicBezier($x, $y, $x1, $y1, $ex, $ey);
	//echo "q2c:$x,$y,$x1,$y2,$ex,$ey => $x1,$y1,$x2,$y2,$x,$y<br>";
	$pdf->_Curve($x1, $y1, $x2, $y2, $x, $y);
	$i += 5;
	break;
      case "q":
	//relative quadratic bezier
	$x1 = $this->getSize($params[$i+1])+$x;
	$y1 = $this->getSize($params[$i+2])+$y;
	$ex = $x+$this->getSize($params[$i+3]);
	$ey = $y+$this->getSize($params[$i+4]);
	list($x1, $y2, $x2, $y2, $x, $y) = 
	  $this->quadratic2CubixBezier($x, $y, $x1, $y2, $ex, $ey);
	$pdf->_Curve($x1, $y1, $x2, $y2, $x, $y);
	$i += 5;
	break;
      case "T":
	//absolute cubic bezier with first control reflection
	//TODO: implement
	$i += 3;
	break;
      case "t":
	//relative cubic bezier with first control point reflection
	//TODO: implement
	$i += 3;
	break;      
      case "A":
	$rx = $this->getSize($params[$i+1]);
	$ry = $this->getSize($params[$i+2]);
	$anglex = -$params[$i+3];
	$fa = $params[$i+4];
	$fs = $params[$i+5];
	$ex = $this->getSize($params[$i+6])+$xOrig;
	$ey = $this->getSize($params[$i+7])+$yOrig;
	//only draw arcs if a difference between begin and endpoint       
	if ($ex-$x != 0 || $ey-$y != 0) {	  	
	  //echo "Arc:x=$x:y=$y:ex=$ex:ey=$ey:fa=$fa:fs=$fs:angle=$anglex:rx=$rx:ry=$ry<br>";
	  list($cx, $cy, $rx, $ry, $alpha_x, $start_angle, $delta_angle) = 
	    $this->endpoint2Centric($x, $y, $ex, $ey, $fa, $fs, $anglex, $rx, $ry);
	  $pdf->_Ellipse($cx, $cy, $rx, $ry, $alpha_x, $start_angle, 
	  	$delta_angle+$start_angle);
	}
	$x = $ex;
	$y = $ey;
	$i += 8;
	break;
      case "a":
	$rx = $this->getSize($params[$i+1]);
	$ry = $this->getSize($params[$i+2]);
	$anglex = -$params[$i+3];
	$fa = $params[$i+4];
	$fs = $params[$i+5];
	$ex = $x+$this->getSize($params[$i+6]);
	$ey = $y+$this->getSize($params[$i+7]);
	//only draw arcs if a difference between begin and endpoint
	if ($ex-$x != 0 || $ey-$y != 0) {	  
	  //echo "Arc:x=$x:y=$y:ex=$ex:ey=$ey:fa=$fa:fa=$fs:angle=$anglex:rx=$rx:ry=$ry<br>";
	  list($cx, $cy, $rx, $ry, $alpha_x, $start_angle, $delta_angle) = 
	   $this->endpoint2Centric($x, $y, $ex, $ey, $fa, $fs, 0, $rx, $ry);
	  //echo "Arc2:$cx:$cy:$rx:$ry:$alpha_x:$start_angle:$delta_angle<br>";
	  //$pdf->Rotate($anglex);
	  //TODO: fix rotation to x axis
	  $pdf->_Ellipse($cx, $cy, $rx, $ry, $alpha_x, $start_angle, 
	  		$delta_angle+$start_angle);
	  //$pdf->Rotate(0);
	}
	$x = $ex;
	$y = $ey;
	$i += 8;
	break;
      default:
	if ($lastParam) {
	  //echo "useLast:$lastParam:".$params[$i-1].":".$params[$i]."<br>";
	  //add previous command again
	  $i--;
	  $params[$i] = $lastParam;
	}
	else {
	  $this->NotYetSupported("Pathdata:".$params[$i]);
	}
      }
      $lastParam = $params[$lastI];
     }
    $pdf->_Style($sargs);
    $buf = $pdf->endCapture($buf);
    //echo $buf."<br>";
    $pdf->appendBuffer($buf);
  }

  /**
   * This function converts from a quadratic to a cubis bezier
   * It returns an array of
   * array($x1, $y1, $x2, $y2, $endx, $endy)
   **/
  private function quadratic2CubicBezier($startx, $starty, $x1, $y1, $endx, $endy) {
    $cx1 = ($x1 - $startx) * 2/3 + $startx;
    $cy1 = ($y1 - $starty) * 2/3 + $starty;
    $cx2 = $endx - ($endx - $x1)*2/3;
    $cy2 = $endy - ($endy - $y1)*2/3;
    return array($cx1, $cy1, $cx2, $cy2, $endx, $endy);
  }

  /**
   * For an explenation see
   * <a href="http://www.w3.org/TR/SVG/implnote.html#ArcImplementationNotes"></a>
   * An array consisting of the following elements will be returned
   * array(cx, cy, rx, ry, alpha_x, start_angle, delta_angle)
   **/
  private function endpoint2Centric($x1, $y1, $x2, $y2, $fa, $fs, $angle, $rx, $ry) {
    $angler = deg2rad($angle);
    //initialization
    $cosa = cos($angler);
    $sina = sin($angler);
    
    //step 1
    $xd = ($x1-$x2)/2;
    $yd = ($y1-$y2)/2;
    $x_ = $cosa*$xd + $sina*$yd;
    $y_ = $cosa*$yd - $sina*$xd;
    //echo "1:$x_:$y_<br>";

    //step2
    $rxq = $rx*$rx;
    $ryq = $ry*$ry;
    $y_q = $y_*$y_;
    $x_q = $x_*$x_;
    $val = ($rxq*$ryq-$rxq*$y_q-$ryq*$x_q)/
      ($rxq*$y_q+$ryq*$x_q);    
    if ($val >= 0) {
      $c = sqrt($val);
    }
    else {
      //TODO: check how to proceed if negative number
      $c = sqrt(-$val);
    }
    if ($fa == $fs) {
      $c *= -1;
    }    
    $cx_ = $c * (($rx*$y_)/$ry);
    $cy_ = $c * -(($ry*$x_)/$rx);
    //echo "2:$cx_:$cy_<br>";

    //step3
    $cx = $cosa*$cx_ - $sina*$cy_ + ($x1+$x2)/2;
    $cy = $sina*$cx_ + $cosa*$cy_ + ($y1+$y2)/2;
    //echo "3:$cx:$cy<br>";

    //step 4
    $fx = ($x_-$cx_)/$rx;
    $fy = ($y_-$cy_)/$ry;
    $angle1 = $this->vectorAngle(1, 0, $fx, $fy);
    $angle2 = $this->vectorAngle($fx, $fy, (-$x_-$cx_)/$rx, (-$y_-$cy_)/$ry);
    $angle2 %= 360;
    if ($fs == 0 && $angle2 > 0) {
      $angle2 -= 360;
    }
    else if ($fs == 1 && $angle2 < 0) {
      $angle2 += 360;
    }
    //echo "4:$angle:$angle2<br>";
    return array($cx, $cy, $rx, $ry, $angle, -$angle1, -$angle2);
  }

  /**
   * Calculate angle between two vectors
   */
  private function vectorAngle($ux, $uy, $vx, $vy) {
    if ($ux + $uy == 0 || $vx + $vy == 0) {
      return 0;
    }
    $uxq = $ux*$ux;
    $uyq = $uy*$uy;
    $vxq = $vx*$vx;
    $vyq = $vy*$vy;
    $val = ($ux*$vx+$uy*$vy)/
      (sqrt($uxq+$uyq)*sqrt($vxq+$vyq));    
    $angle = rad2deg(acos($val));
    if ($ux*$vy-$uy*$vx < 0) {
      $angle *= -1;
    }
    return $angle;
  }

  private function getSize($param) {
    return $this->calcInternalValue($param);
  }
}
?>