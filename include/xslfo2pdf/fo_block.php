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
class FO_Block extends FO_LayoutObject{
  private static $CHILDNODES = array(
	  //FO_BidiOverride,
	  //FO_Character,
	  FO_ExternalGraphic, /*oesi - uncomment for ExternalGraphic Support*/
	  FO_InstreamForeignObject,
	  FO_Inline,
	  //FO_InlineContainer,
	  //FO_Leader,
	  //FO_PageNumber,
	  //FO_PageNumberCitation,
	  FO_BasicLink,
	  //FO_MultiToggle,
	  FO_Block,
	  FO_BlockContainer,
	  FO_TableAndCaption,
	  FO_Table,
	  FO_ListBlock
	  );
		
  protected function getChildNodes() {
    return self::$CHILDNODES;
  }

  protected function initAttributes($node) {
    $this->initAttribute($node, "text-align");
    $this->initAttribute($node, "content-width");
  }

  protected function processContent($text) {
    $talign = $this->getContext("text-align");
    //oesi - add attribute content-width
    $colwidth = $this->getContext("content-width");
    switch ($talign) {
    case "center":
      $align = "C";
      break;
    case "right":
      $align = "R";
      break;
    case "left":
      $align = "L";
    default:
    }
	
    $text = $this->escape($text);
    
    $x = $this->getContext("x");
    $x2 = $this->getContext("startx");
    if (!$x2) {
      $x2 = $x;
    }
    $y = $this->getContext("y");
    //    echo "Draw at:$x:$x2:$y<br>";
    $pdf = $this->getPdf();
    $lineHeight = $this->getContext("line-height");
    
    list($width, $height, $nb, $sx, $sy, $lx, $ly) = 
	    $pdf->Text2($x2, $y, $text, $align, $lineHeight, $x, $colwidth);
//echo "Wrote block:$x:$y:$height:$lineHeight:".$pdf->FontSize.":".$pdf->FontSizePt."<br>";
    $this->setLocalContext("content_height", $height);
    $this->setLocalContext("content_width", $width);
    $this->setLocalContext("lx", $lx);
    $this->setLocalContext("ly", $ly);
    $this->setLocalContext("sx", $sx);
    $this->setLocalContext("sy", $sy);
  }

  protected function postParseContent($content) {
        $this->setContext("startx", $this->getContext("lx"));
	$this->setContext("y", $this->getContext("ly"));	
	$pdf = $this->getPdf();
	$this->setLocalContext("width", $pdf->GetPageWidth());
	$h1 = $this->getContext("height");
	$h2 = $this->getContext("content_height");
	if ($h2 > $h1) {
		$this->setLocalContext("height", $h2);
	}
  }

  protected function postParse(FO_Object $obj) {
	if (!$obj instanceof FO_Inline) {
		return parent::postParse($obj);
	}
        $this->setContext("startx", $obj->getContext("lx"));
	$this->setContext("y", $obj->getContext("ly"));
	$this->setContext("x", $obj->getContext("x"));	
	$h1 = $this->getContext("height");
	$h2 = $obj->getContext("height");
	if ($h2 > $h1) {
		$this->setLocalContext("height", $h2);
	}
  }

  private function escape($text) {
    return str_replace('\t', '', preg_replace('/\s+/', ' ', $text));
  }
}

class FO_Inline extends FO_Block {

  protected function processContent($text) {
    //	echo "show inline content:$text<br>";
    return parent::processContent($text);
  }
    
}

class FO_BasicLink extends FO_Block {
  protected function initAttributes($node) {
    $this->initLocalAttribute($node, "internal-destination");
    $this->initLocalAttribute($node, "external-destination");
  }

  protected function processContent($text) {
    parent::processContent($text);
    $width = $this->getContext("content_width");
    $height = $this->getContext("content_height");
    $lx = $this->getContext("lx");
    $ly = $this->getContext("ly");
    $sx = $this->getContext("sx");
    $sy = $this->getContext("sy");    
    //echo "Link at:$x:$y:$width:$height<br>";
    $pdf = $this->getPdf();
    $internal = $this->getContext("internal-destination");
    $external = $this->getContext("external-destination");
    if ($internal) {
      $lnk = $pdf->AddLink();
      $ref = $this->getReference($internal);
      if ($ref) {	
	//TODO: add all references from id's as well as the page-number
	$pdf->SetLink($lnk, $ref->getContext("y"), $ref->getContext("page-number"));
	$pdf->Link($x, $y, $width, $height, $lnk);
      }
    }
    else if ($external) {
      $pdf->Link($x, $y, $width, $height, $external);
    }
  }
}

//oesi - add ExternalGraphics
class FO_ExternalGraphic extends FO_Block
{
  protected function initAttributes($node) 
  {
    $this->initLocalAttribute($node, "src");
    $this->initLocalAttribute($node, "width");
    $this->initLocalAttribute($node, "height");
    $this->initLocalAttribute($node, "posx");
    $this->initLocalAttribute($node, "posy");
  }

  protected function processContent($text) 
  {
    parent::processContent($text);
                
    $pdf = $this->getPdf();
    
    $src = $this->getContext("src");    
    $width = $this->getContext("width");
    $height = $this->getContext("height");
    $x = $this->getContext("posx");
    $y = $this->getContext("posy");
        
     $pdf->Image($src, $x, $y, $width, $height, "jpg","");
 }
}


?>