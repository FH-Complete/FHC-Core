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
class SVG_Object extends FO_Object {
  function initLocalStyleAttribute(DOMNode $node) {
    $st = $this->getAttribute($node, "style");	
    
    $styles = explode(";", $st);
    foreach ($styles as $style) {
      $params = explode(":", $style);
      $this->setLocalContext($params[0], $params[1]);      
    }
  }

  function initStyleAttribute(DOMNode $node) {
    $st = $this->getAttribute($node, "style");	
    
    $styles = explode(";", $st);
    foreach ($styles as $style) {
      $params = explode(":", $style);
      $this->setContext($params[0], $params[1]);     
    }
  }
}

class SVG_StyleObject extends SVG_Object {

  function initLocalSizeAttribute(DOMNode $node, $key, $to="mm", $from="pt"){
    parent::initLocalSizeAttribute($node, $key, $to, $from);
  }

  function initSizeAttribute(DOMNode $node, $key, $to="mm", $from="pt"){
    parent::initSizeAttribute($node, $key, $to, $from);
  }

  function getLocalSizeAttribute(DOMNode $node, $key, $to="mm", $from="pt"){
    return parent::getLocalSizeAttribute($node, $key, $to, $from);
  }

  function getSizeAttribute(DOMNode $node, $key, $to="mm", $from="pt"){
    return parent::getSizeAttribute($node, $key, $to, $from);
  }

  function calcInternalValue($value, $to = "mm", $from="pt") {
    return parent::calcInternalValue($value, $to, $from);
  }

  function parse(DOMNode $node) {
    $pdf = $this->getPdf();
    $buf = $pdf->startCapture();
    $this->initStyleAttribute($node);
    $this->initSizeAttribute($node, "x");
    $this->initSizeAttribute($node, "y");
    $this->initLocalSizeAttribute($node, "width");
    $this->initLocalSizeAttribute($node, "height");
    $this->initAttribute($node, "fill");
    $this->initAttribute($node, "stroke");
    $this->initSizeAttribute($node, "stroke-width");
    
    $fill = $this->getContext("fill");	  
    $stroke = $this->getContext("stroke");
    $strokeWidth = $this->getContext("stroke-width");
    $sargs = "";    
    $oldFillColor = $pdf->GetFillColor();
    $oldDrawColor = $pdf->GetDrawColor();
    $oldLineWidth = $pdf->GetLineWidth();
    if ($fill == "none") {
      $fill = NULL;
    }
    if ($stroke == "none") {
      $stroke = NULL;
    }
    if ($fill) {
      list($r, $g, $b) = $this->parseColor($fill);
      $pdf->setFillColor($r, $g, $b);
      $sargs .= "F";
    }
    if ($stroke) {
      list($r, $g, $b) = $this->parseColor($stroke);
      $pdf->setDrawColor($r, $g, $b);
      $sargs .= "D";
    }
    if ($strokeWidth) {
      $pdf->SetLineWidth($strokeWidth);
    }

    $this->process($node, $sargs);

    if ($fill) {
      list($r, $g, $b) = $this->parseColor($oldFillColor);      
      $pdf->setFillColor($r, $g, $b);
    }
    if ($stroke) {
      list($r, $g, $b) = $this->parseColor($oldStrokeColor);
      $pdf->setDrawColor($r, $g, $b);
    }
    if ($strokeWidth) {
      $pdf->SetLineWidth($oldLineWidth);
    }    
    $buf = $pdf->endCapture($buf);
    //echo get_class($this).":$buf<br>";
    $pdf->appendBuffer($buf);
  }
  function process(DOMNode $node, $sargs="")
  {
  }
}

class FO_SVG extends SVG_Object {

  static $CHILDNODES = array(
				     SVG_Circle,
				     SVG_Rect,
				     SVG_Ellipse,
				     SVG_Line,
				     SVG_Polygon,
				     SVG_G,
				     SVG_Text,
				     SVG_Path
				     );
  
  function parse(DOMNode $node) {
    $this->initLocalSizeAttribute($node, "width", "mm", "pt");
    $this->initLocalSizeAttribute($node, "height", "mm", "pt");    
    $this->setContext("xOrig", $this->getContext("x"));
    $this->setContext("yOrig", $this->getContext("y"));
    $this->setContext("x", 0);
    $this->setContext("y", 0);
    $pdf = $this->getPdf();
    $this->processChildNodes($node, self::$CHILDNODES);
    $this->setContext("y", $this->getContext("yOrig")+$this->getContext("height"));
    $this->setContext("x", $this->getContext("xOrig"));
  }
}
?>