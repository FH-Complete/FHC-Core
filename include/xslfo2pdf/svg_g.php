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
class SVG_G extends SVG_StyleObject {
  
  public function process(DOMNode $node, $sargs="") {
    $this->initLocalSizeAttribute($node, "transform");
    $transform = $this->getContext("transform");

    $index = strpos($transform, "(");
    $func = substr($transform, 0, $index);
    $paramStr = substr($transform, $index+1, strlen($transform)-$index-2);
    $params = explode(",", $paramStr);
    $pdf = $this->getPdf();
    $xOrig = $this->getContext("xOrig");
    $yOrig = $this->getContext("yOrig");
	
    if ($func) {
      switch ($func) {
      case "translate":
	$xdiff = $this->calcInternalValue($params[0]);
	$ydiff = $this->calcInternalValue($params[1]);
	$this->setContext("xOrig", $xOrig+$xdiff);
	$this->setContext("yOrig", $yOrig+$ydiff  );
	//echo "translate:".$xdiff.":".$ydiff."<br>";
	break;
    case "rotate":
      $angle = $params[0] * -1;      
      if (sizeof($params) > 1) {
	$pdf->Rotate($angle, $params[1], $params[2]);
      }
      else {	
	$x = $this->getContext("x")+$xOrig;
	$y = $this->getContext("y")+$yOrig;
	$pdf->Rotate($angle, $x, $y);
      }
      break;
      //    case "skewX":
      //    case "skewY":
      default:
	echo "Function not supported:$func<br>";
      }
    }
    $this->processChildNodes($node, FO_SVG::$CHILDNODES);

    //restore group options
    switch ($func) {
    case "translate":
      $this->setContext("xOrig", $xOrig);
      $this->setContext("yOrig", $yOrig);
      break;
    case "rotate":
      $pdf->Rotate(0);
      break;
      //    case "skewX":
      //    case "skewY":
    }
  }
}
?>