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

class FO_LayoutObject extends FO_FlowContainer {
  var $_oldFont;
  var $_oldFontStyle;
  var $_oldColor;
  var $_buffer;
  
  function initDefaultAttributes($node) {
    //read attributes
    $this->initLocalAttribute($node, "border-top-style");
    $this->initLocalAttribute($node, "border-right-style");
    $this->initLocalAttribute($node, "border-bottom-style");
    $this->initLocalAttribute($node, "border-left-style");
    $this->initLocalAttribute($node, "border-style");
    $this->initLocalAttribute($node, "border-top-color");
    $this->initLocalAttribute($node, "border-right-color");
    $this->initLocalAttribute($node, "border-bottom-color");
    $this->initLocalAttribute($node, "border-left-color");
    $this->initLocalAttribute($node, "border-color");
    $this->initLocalSizeAttribute($node, "border-top-width");
    $this->initLocalSizeAttribute($node, "border-right-width");
    $this->initLocalSizeAttribute($node, "border-bottom-width");
    $this->initLocalSizeAttribute($node, "border-left-width");
    $this->initLocalSizeAttribute($node, "border-width");
    $this->initAttribute($node, "font-style");
    $this->initAttribute($node, "font-weight");
    $this->initSizeAttribute($node, "font-size", "pt");
    $this->initAttribute($node, "font-family");
    $this->initAttribute($node, "color");
    $this->initLocalSizeAttribute($node, "width");
    $this->initLocalSizeAttribute($node, "height");
    $this->initLocalAttribute($node, "background-color");
    $this->initLocalAttribute($node, "background-image");
    $this->initLocalSizeAttribute($node, "space-before.optimum");
    $this->initLocalSizeAttribute($node, "space-after.optimum");
    $this->initLocalAttribute($node, "break-before");
    $this->initLocalAttribute($node, "break-after");    
    $this->initSizeAttribute($node, "line-height");
    $this->initLocalSizeAttribute($node, "padding-top");
    //$this->initLocalSizeAttribute($node, "padding-bottom");
    $this->initLocalSizeAttribute($node, "padding-left");
    //$this->initLocalSizeAttribute($node, "padding-right");
  }

  //oesi - convertiert die daten von utf8 nach latin1 und ersetzt 'EURO' durch das eurosymbol
  function convert($str)
  {
  	//echo str_replace('EURO',chr(128),utf8_decode($str));
  	return str_replace('EURO',chr(128),utf8_decode($str));
  }
	
  function parse(DOMNode $node) {
    //set default attributes
    $this->initDefaultAttributes($node);
    $this->initAttributes($node);
    $this->initialize();
    $acceptPageBreak = $this->getContext("acceptPageBreak");
    
    $pos = $this->getPosition();
    list($x, $y, $width, $height) = $pos[0];
    list($xOrig, $yOrig, $width, $height) = $pos[1];

    //automatic page break if component exceeds page limits
    $pdf = $this->getPdf();
    if ($yOrig > $pdf->PageBreakTrigger && 
	$pdf->AcceptPageBreak() && 
	$acceptPageBreak) {
	$pdf->AddPage();
	$this->handleEvent("sync-position");
	$this->parse($node);
	return;
    }

	//update to inner position
    $this->setContext("x", $x);
    $this->setContext("y", $y);
    
	//draw us
    $this->setColor();
    $this->setFont();    

    $this->startCapture();
    foreach($node->childNodes as $child) {
      if ($child->nodeType == self::NODE_TYPE_TEXT) {
	$this->preParseContent($child->textContent);
	//oesi - add function utf8_decode for special chars (umlaut)
	$this->processContent($this->convert($child->nodeValue));
	$this->postParseContent($child->textContent);
      }
      else {
	$this->processChildNode($child, $this->getChildNodes());
      }
    }
    
    //oesi - hack for ExternalGraphic Tag to show without content
    if($this instanceof FO_ExternalGraphic)
       $this->processContent('');
    //endhack

    $contentBuffer = $this->endCapture();	
    
    //update to outer position
    $this->setContext("x", $xOrig);
    $this->setContext("y", $yOrig);
        
    //recalc positions
    $pos2 = $this->getPosition();
    //merge with X and y values of the original psoition
    $pos2[0][0] = $pos[0][0];
    $pos2[0][1] = $pos[0][1];
    $pos2[1][0] = $pos[1][0];
    $pos2[1][1] = $pos[1][1];

    //automatic page break if component exceeds page limits
    $pdf = $this->getPdf();
    if ($pos2[1][3] < $pdf->PageBreakTrigger && 
	$pos2[1][3]+$this->getContext("y") > $pdf->PageBreakTrigger && 
	$pdf->AcceptPageBreak() && $acceptPageBreak) {
      $pdf->AddPage();
      $this->handleEvent("sync-position");
      $this->parse($node);
      return;
    }
    
    $this->drawBordersAndBackground($pos2);
        
    //update to outer positions
    $this->setLocalContext("width", $pos2[1][2]);
    $this->setLocalContext("height", $pos2[1][3]);

    //append child buffer
    $this->appendBuffer($contentBuffer);

    $this->closeDown();
  }

  /**
   * Initialize additional attributes
   **/
  function initAttributes(DOMNode $node) {
    //do nothing
  }

  function preParseContent($content) {
  }
  
  function postParseContent($textcontent) {
  }
  
  /**
   * Draw borders and backgrounds according to the positions
   * May be overwritten to specify behaviour
   */
  function drawBordersAndBackground($pos) {
    list($x, $y, $width, $height) = $pos[1];
    $this->drawBackground($x, $y, $width, $height);
    $this->drawBorders($x, $y, $width, $height);    
  }

  function getChildNodes() {
    //no child nodes per default
    return array();
  }

  function initialize() {
    $break_before = $this->getContext("break-before");
    $this->handleBreak($break_before);
  }  

  function closeDown() {
     $pdf = $this->getPdf();
     if ($this->_oldFont) {
       $pdf->SetFont($this->_oldFont, $this->_oldFontStyle, 
		     $this->_oldFontSize);
     }
     else if ($this->_oldFontSize) {
       $pdf->SetFontSize($this->oldFontSize);
     }

     if ($this->_oldColor) {
       $this->setTextColor($this->_oldColor, $pdf);
     }

     $space_after = $this->getContext("space-after.optimum");
//     echo "Space-after:".$space_after.":".get_class($this).":".$this->getContext("height")."<br>";
     if ($space_after) {
       $this->setLocalContext("height", $this->getContext("height") + 
			      $space_after);
     }
     $break_after = $this->getContext("break-after");
     $this->handleBreak($break_after);
  }

  function startCapture() {
    if ($this->_buffer) {
      echo "Already captureing<br>";
      return;
    }
    $this->_buffer = $this->getPdf()->startCapture();
  }

  function endCapture() {
    if (!$this->_buffer) {      
      return;
    }    
    $partBuffer = $this->getPdf()->endCapture($this->_buffer);
    $this->_buffer = NULL;
    return $partBuffer;
  }

  function appendBuffer($buffer) {
    if (!$buffer) {
      //echo "Nothing to append<br>";
      return;
    }
    $this->getPdf()->appendBuffer($buffer);
  }

  function getPosition() {
    $space_before = $this->getContext("space-before.optimum");    
    $height = $this->getContext("line-height");
    $pdf = $this->getPdf();    
    
    $bw_top = $this->getContext("border-top-width");
    $bw_left = $this->getContext("border-left-width");
    $bw_right = $this->getContext("border-right-width");
    $bw_bottom = $this->getContext("border-bottom-width");    
    $padding_left = $this->getContext("padding-left");    
    $padding_top = $this->getContext("padding-top");    

    $bw = $this->getContext("border-width");
    $xx = $this->getContext("x");
    $yy = $this->getContext("y");

    $height2 = $this->getContext("height");    
    if (!$height || $height < $height2) {            
      $height = $height2;
    }
    $width = $this->getContext("width");
    if (!$bw_top) {$bw_top = $bw;}
    if (!$bw_bottom) {$bw_bottom = $bw;}
    if (!$bw_right) {$bw_right = $bw;}
    if (!$bw_left) {$bw_left = $bw;}
    
    sscanf($bw_top, "%f%s", $wt, $unit);
    sscanf($bw_left, "%f%s", $wl, $unit);
    sscanf($bw_right, "%f%s", $wr, $unit);
    sscanf($bw_bottom, "%f%s", $wb, $unit);
    $xx += $wl;
    $yy += $wt;
    $height += $wt+$wb;
    $width += $wl+$wr;
    
    if ($space_before) {
      //echo "Spacebefore:$space_before<br>";
      $yy += $space_before;
      $height += $space_before;
    }
    if ($padding_left) {
      $xx += $padding_left;
    }
    if ($padding_top) {
      $yy += $padding_top;
    }

    return 
      array(
	    //inner coordinates
	    array($xx, $yy, $width-$wl-$wr, $height-$wt-$wb), 	    
	    //outer coordinates
	    array($xx-$wl, $yy-$wr, $width, $height));     
  }
  
  function setColor() {
    $pdf = $this->getPdf();
    $this->_oldColor = $pdf->GetTextColor();
    $color = $this->getContext("color");    
    if ($color) {
      $this->setTextColor($color, $pdf);
    }
  }

  function setFont() {
    $pdf = $this->getPdf();      
    $this->_oldFont = $pdf->GetFontFamily();
    $this->_oldFontStyle = $pdf->GetFontStyle();      
    $this->_oldFontSize = $pdf->GetFontSizePt();
    $weight = $this->getContext("font-weight");
    $style = $this->getContext("font-style");
    $family = $this->getContext("font-family");
    $size = $this->getContext("font-size");
    if ($family) {
      $f = $family;
    }
    else {
      $f = $this->_oldFont;
    }    
    if ($weight || $style) {
      if ($weight) {
	$st = "B";
      }
      if ($style) {
	//TODO: check which styles are supported
      }
    } 
    else {
      $st = $this->_oldFontStyle;
    }
    if ($size) {
      $sz = $size;
    }
    else {
      $sz = $this->_oldFontSize;
    }   
    if ($pdf->FontExists($f, $st)) {     
      $pdf->SetFont($f, $st, $sz);
    }
    else if ($pdf->FontExists($this->_oldFont, $st)) {     
      $pdf->SetFont($this->_oldFont, $st, $sz);
    }      
    else {
      //adjust only size
      $pdf->SetFontSize($sz);
    }
  }

  function drawBorders($x, $y, $width, $height) {
    $bs_top = $this->getContext("border-top-style");
    $bs_left = $this->getContext("border-left-style");
    $bs_right = $this->getContext("border-right-style");
    $bs_bottom = $this->getContext("border-bottom-style");
    $bs = $this->getContext("border-style");
    $bc_top = $this->getContext("border-top-color");
    $bc_left = $this->getContext("border-left-color");
    $bc_right = $this->getContext("border-right-color");
    $bc_bottom = $this->getContext("border-bottom-color");    
    $bc = $this->getContext("border-color");    
    $bw_top = $this->getContext("border-top-width");
    $bw_left = $this->getContext("border-left-width");
    $bw_right = $this->getContext("border-right-width");
    $bw_bottom = $this->getContext("border-bottom-width");    
    $bw = $this->getContext("border-width");
    
    $pdf = $this->getPdf();
    if (!$bs_top) {$bs_top = $bs;}
    if (!$bs_bottom) {$bs_bottom = $bs;}
    if (!$bs_right) {$bs_right = $bs;}
    if (!$bs_left) {$bs_left = $bs;}
    if (!$bc_top) {$bc_top = $bc;}
    if (!$bc_bottom) {$bc_bottom = $bc;}
    if (!$bc_right) {$bc_right = $bc;}
    if (!$bc_left) {$bc_left = $bc;}
    if (!$bw_top) {$bw_top = $bw;}
    if (!$bw_bottom) {$bw_bottom = $bw;}
    if (!$bw_right) {$bw_right = $bw;}
    if (!$bw_left) {$bw_left = $bw;}

    sscanf($bw_top, "%f%s", $wt, $unit);
    sscanf($bw_left, "%f%s", $wl, $unit);
    sscanf($bw_right, "%f%s", $wr, $unit);
    sscanf($bw_bottom, "%f%s", $wb, $unit);
    $wt /= 2;    
    $wl /= 2;
    $wr /= 2;
    $wb /= 2;

    $width -= $wl+$wr;
    $height -= $wt+$wb;
    $x += $wl;
    $y += $wt;
    $this->drawLine($x, $y, $x+$width, $y, $bs_top, $bc_top, 
		    $bw_top, $pdf);
    $this->drawLine($x, $y, $x, $y+$height, $bs_left, 
		    $bc_left, $bw_left,$pdf);
    $this->drawLine($x, $y+$height, $x+$width, $y+$height, 
		    $bs_bottom, $bc_bottom, $bw_bottom, $pdf);
    $this->drawLine($x+$width, $y, $x+$width, $y+$height, 
		    $bs_right, $bc_right,$bw_right, $pdf);
  }

  function drawBackground($x, $y, $width, $height) {
    $pdf = $this->GetPdf();
    $bg_c = $this->getContext("background-color");
    $bg_img = $this->getContext("background-image");
    if ($bg_c) {
      $oldColor = $pdf->GetFillColor();
      list($r, $g, $b) = $this->parseColor($bg_c);
      $pdf->SetFillColor($r, $g, $b);
      $pdf->Rect($x, $y, $width, $height, "F");
      list($r, $g, $b) = $this->parseColor($oldColor);
      $pdf->SetFillColor($r, $g, $b);
    }   
    else if ($bg_img) {
      $this->NotYetSupported("background-image");
    }
  }
   
  function drawLine($x, $y, $x2, $y2, $style, $color, $width, &$pdf) {
     $oldColor = $pdf->GetDrawColor();
     $oldLineWidth = $pdf->GetLineWidth();
     if ($width) {
       $pdf->SetLineWidth($width);
     }
     $this->setDrawColor($color, $pdf);
     switch ($style) {
     case "dotted":
       $pdf->SetDash(0.5, 0.5); //1mm on, 1mm off
       $pdf->Line($x, $y, $x2, $y2);
       $pdf->SetDash(); //restore no dash
       return;
     case "dashed":
       $pdf->SetDash(2,2); //2mm on, 2mm off
       $pdf->Line($x, $y, $x2, $y2);
       $pdf->SetDash(); //restore no dash
       return;
     case "solid":
       $pdf->Line($x, $y, $x2, $y2);
       return;
     case "double":
     case "groove":
     case "ridge":
     case "inset":
     case "outset":
       //TODO:implement
       echo "border style '$style' not yet supported<br>";
     case "none":
     case "hidden":
     default:
       //do nothing
       return;
     }
     $this->setDrawColor($oldColor, $pdf);
     $this->setLineWidth($oldLineWidth);
   }

   function setDrawColor($color, &$pdf) {
     if ($color == '') {
       return;
     }     
     list($r, $g, $b) = $this->parseColor($color);   
     $pdf->SetDrawColor($r, $g, $b);
   }

   function setTextColor($color, &$pdf) {
     if ($color == '') {
       return;
     }     
     list($r, $g, $b) = $this->parseColor($color);   
     $pdf->SetTextColor($r, $g, $b);
   }   

   function handleBreak($break) {     
     if (!$break) {
       return;
     }
     $pdf = $this->getPdf();
     switch($break) {
     case "page":
       $pdf->AddPage();
       $this->handleEvent("sync-position");       
       return;
     default:
       $this->NotYetSupported("Break:$break");
     }       
   }
}

class FO_LayoutMasterSet extends FO_Object {
	var $name;

	function parse(DOMNode $node) {
	  $this->name = $node->attributes->getNamedItem("master-name");
	  $this->addReference($this, $this->name);
	}
}

class FO_PageSequence extends FO_Object {
  static $CHILDNODES = array(
				     'FO_Flow'
				     );
  
  function parse(DOMNode $node) {
    $masterRef = $node->attributes->getNamedItem("master-reference");
    if ($masterRef) {
      $master = $this->resolveReference('FO_LayoutMasterSet', $masterRef);
      //TODO: do something with this master
    }
    $pdf = $this->getPdf();
    $pdf->AddPage();
    $this->handleEvent("sync-position");
    $this->processChildNodes($node, self::$CHILDNODES);
  }  
}

class FO_FlowContainer extends FO_Object {

  function postParse(FO_Object $obj) {
    $acceptPageBreak = $this->getContext("acceptPageBreak");
    $this->setLocalContext("width", $obj->getContext("width"));
    $height =  $this->getContext("height")+$obj->getContext("height");
    $this->setLocalContext("height", $height);    
    $y = $this->getContext("y")+$obj->getContext("height");
    $pdf = $this->getPdf();
    if ($height < $pdf->PageBreakTrigger && 
	$y > $pdf->PageBreakTrigger && 
	$pdf->AcceptPageBreak() && 
	$acceptPageBreak) {

      $pdf->AddPage();
      $this->handleEvent("sync-position");
      //echo "Page break on .".get_class($obj)."<br>";
    }
    else {    
      $this->setContext("y", $y);    
      //echo "Move :".get_class($obj).":".$y.":".$obj->getContext("height")."<br>;";
    }
  }
}

class FO_Flow extends FO_FlowContainer {
  static $CHILDNODES = array(
				     'FO_Block',
				     'FO_Table',
				     'FO_BlockContainer',
				     'FO_TableAndCaption',
				     'FO_ListBlock'
				     );

  function parse(DOMNode $node) {
    //TODO: use attributes
    $this->processChildNodes($node, self::$CHILDNODES);
  }  
}

class FO_BlockContainer extends FO_LayoutObject {
  static $CHILDNODES = array(
				     'FO_Block',
				     'FO_BlockContainer',
				     'FO_TableAndCaption',
				     'FO_Table',
				     'FO_ListBlock');

  function initAttributes(DOMNode $node) {
    $this->initLocalSizeAttribute($node, "position");
    $this->initLocalSizeAttribute($node, "top");
    $this->initLocalSizeAttribute($node, "left");
    $this->initLocalSizeAttribute($node, "height");
    $this->initLocalSizeAttribute($node, "width");
  }
  
  function getChildNodes() {
    return self::$CHILDNODES;
  }

  function parse(DOMNode $node) {
    $this->initAttributes($node);
    $position = $this->getContext("position");
    if ($position == "absolute"){
      //init absolute positions
      $top = $this->getContext("top");
      $left = $this->getContext("left");
      $this->setContext("x", $left);
      $this->setContext("y", $top);
      $this->setContext("acceptPageBreak", false);
    }
    parent::parse($node);
  }

}

class FO_ListBlock extends FO_LayoutObject {
  
}

?>
