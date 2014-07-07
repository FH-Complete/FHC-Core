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

require_once("fpdf/myfpdf.php");
require_once("fo_core.php");
require_once("fo_layout.php");
require_once("fo_block.php");
require_once("fo_table.php");
require_once("fo_instream.php");
require_once("svg_circle.php");
require_once("svg_rect.php");
require_once("svg_ellipse.php");
require_once("svg_line.php");
require_once("svg_polygon.php");
require_once("svg_g.php");
require_once("svg_text.php");
require_once("svg_path.php");

class XslFo2PDF {
  function generatePdf($xml, $name="out.pdf", $dest='') {
	$doc = new DOMDocument();
    $doc->loadXML($xml);

    if ($doc === false) {		  
      echo "failed loading dom<br>";
      return false;
    }
    //get the only child			
    foreach($doc->childNodes as $child) {
      if ($child != NULL && 
	  $child->nodeName == "fo:root") {
	$rootNode = $child;
      }
    }
    if ($rootNode == null) {
      echo "Didn't find root node<br>";
      return false;
    }
    
    // oesi - Format und orientation auslesen
	$masterpage = $rootNode->getElementsByTagName('simple-page-master');
	$format = 'A4';
	$orient = 'P';
	foreach ($masterpage as $x=>$mp) 
	{
		if($mp->getAttribute('format')!='')
			$format = $mp->getAttribute('format');
		if($mp->getAttribute('orientation')!='')
			$orient = $mp->getAttribute('orientation');
	}
	
    $pdf = new MyPDF($orient, 'mm', $format);
    $root = new FO_Root($pdf);
    $this->initDefaults($pdf, $root);		
    if ($root->parse($rootNode) === false) {
      echo "Parsing failed<br>";
      return false;
    }		

    if (strpos($name, ".pdf")===false) {
      $name = $name.".pdf";
    }
    $pdf->Output($name, $dest);
    return true;
  }

  function initDefaults(FPDF $pdf, FO_Root $root) {
    $pdf->SetFont('Arial','',14);	  
    $pdf->SetAutoPageBreak(true);
    $root->setContext("page-width", "21");
  }
}

class FO_Factory {
  static $factory = array("fo:layout-master-set" => 'FO_LayoutMasterSet',
				  "fo:block" => 'FO_Block',
				  "fo:page-sequence" => 'FO_PageSequence',
				  "fo:flow" => 'FO_Flow',
				  "fo:block-container" => 'FO_BlockContainer',
				  "fo:list-block" => 'FO_ListBlock',
				  "fo:table-and-caption" => 'FO_TableAndCaption',
				  "fo:table" => 'FO_Table',
				  "fo:table-caption" => 'FO_TableCaption',
				  "fo:table-header" => 'FO_TableHeader',
				  "fo:table-footer" => 'FO_TableFooter',
				  "fo:table-body" => 'FO_TableBody',
				  "fo:table-row" => 'FO_TableRow',
				  "fo:table-column" => 'FO_TableColumn',
				  "fo:table-cell" => 'FO_TableCell',
				  "fo:inline" => 'FO_Inline',
				  "fo:instream-foreign-object" => 
				  'FO_InstreamForeignObject',
				  "fo:basic-link" => 'FO_BasicLink',
				  "fo:external-graphic" => 'FO_ExternalGraphic',
				  "svg:svg" => 'FO_SVG',
				  "svg:circle" => 'SVG_Circle',
				  "svg:rect" => 'SVG_Rect',
				  "svg:ellipse" => 'SVG_Ellipse',
				  "svg:line" => 'SVG_Line',
				  "svg:polygon" => 'SVG_Polygon',
				  "svg:polyline" => 'SVG_Polygon',
				  "svg:g" => 'SVG_G',
				  "svg:text" => 'SVG_Text',
				  "svg:path" => 'SVG_Path'
				  );

  static $names = NULL;

  static function createFOObject(DOMNode $node, FO_Container $container,
					FPDF $pdf, FO_Context &$context, 
					$filter) 
  {
	if(isset(self::$factory[$node->nodeName]))
    	$obj = self::$factory[$node->nodeName];
    if (!isset($obj) || !$obj) 
    {
      return NULL;
    }
    if (!$filter || !in_array($obj, $filter)) 
    {
      echo "ignore due to filter:$obj<br>";
      return NULL;
    }
    //echo "Create:$obj<br>";
    return new $obj($container, $pdf, $context);    
  }
}


?>
