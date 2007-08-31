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
 /*
<fo:table-and-caption>

<fo:table>

<fo:table-column column-width="25mm"/>
<fo:table-column column-width="25mm"/>

<fo:table-header>
  <fo:table-cell>
    <fo:block font-weight="bold">Car</fo:block>
  </fo:table-cell>
  <fo:table-cell>
    <fo:block font-weight="bold">Price</fo:block>
  </fo:table-cell>
</fo:table-header>

<fo:table-body>
  <fo:table-row>
    <fo:table-cell>
      <fo:block>Volvo</fo:block>
    </fo:table-cell>
    <fo:table-cell>
      <fo:block>$50000</fo:block>
    </fo:table-cell>
  </fo:table-row>
  <fo:table-row>
    <fo:table-cell>
      <fo:block>SAAB</fo:block>
    </fo:table-cell>
    <fo:table-cell>
      <fo:block>$48000</fo:block>
    </fo:table-cell>
  </fo:table-row>
</fo:table-body>

</fo:table>

</fo:table-and-caption>
 */
?>
<?PHP

class FO_TableAndCaption extends FO_Object {

  static $CHILDNODES = array(
				     'FO_Table',
				     'FO_TableCaption'
				     );

  function parse(DOMNode $node) {
    $this->processChildNodes($node, self::$CHILDNODES);
  }
}

class FO_Table extends FO_LayoutObject {
  var $colCount = 0;

  static $CHILDNODES = array(
				     'FO_TableColumn',
				     'FO_TableHeader',
				     'FO_TableFooter',
				     'FO_TableBody'
				     );

  function getChildNodes() {
    return self::$CHILDNODES;
  }

  function initFOObject(FO_Object $col) {
    if (!$col instanceof FO_TableColumn) {
      return;
    }
    $col->setContext("column", $this->colCount++);
  }

  function postParse(FO_Object $obj) {
  	global $max_line_height_for_that_row;
    if ($obj instanceof FO_TableHeader) {
      $this->setLocalContext("width", $obj->getContext("width"));
      $this->setLocalContext("height", $this->getContext("height")+
			$obj->getContext("height"));
      $this->setContext("y", $this->getContext("y") + 
			     $obj->getContext("height"));
    }
    else if($obj instanceof FO_TableBody) {
      $this->setLocalContext("width", $obj->getContext("width"));      
      $this->setLocalContext("height", $this->getContext("height")+
			$obj->getContext("height"));
      $this->setContext("y", $this->getContext("y") + 
			     $obj->getContext("height"));
	  $max_line_height_for_that_row=1;
    }
    else if($obj instanceof FO_TableColumn) {
      $col = $obj->getContext("column");
      $this->setContext("column-$col-width", $obj->getContext("width"));
    }
  }
}

class FO_TableCaption extends FO_Object {

  static $CHILDNODES = array(
				     'FO_Block',
				     'FO_BlockContainer',
				     'FO_ListBlock'
				     );

  function parse(DOMNode $node) {
    $this->processChildNodes($node, self::$CHILDNODES);
  }
}

class FO_TableColumn extends FO_Object {

  static $CHILDNODES = array();

  function parse(DOMNode $node) {
    $width = $this->getSizeAttribute($node, "column-width");
    //calc internal width    
    $this->setContext("width", $width);    
    $this->processChildNodes($node, self::$CHILDNODES);    
  }
}

class FO_TableRow extends FO_LayoutObject {

  var $colIndex = 0;

  static $CHILDNODES = array(
				     'FO_TableCell'
				     );

  function getChildNodes() {
    return self::$CHILDNODES;
  }

  function initFOObject(FO_Object $col) {
    if (!$col instanceof FO_TableCell) {
      return;
    }
    $col->setContext("column", $this->colIndex++);
  }

  function postParse(FO_Object $obj) {  
  	  
    if ($obj instanceof FO_TableCell) {
      $this->setContext("x", $this->getContext("x")+
			$obj->getContext("width"));
      $this->setLocalContext("width", $this->getContext("width")+
			$obj->getContext("width"));
      if ($this->getContext("height") < $obj->getContext("height")) {
	$this->setLocalContext("height", $obj->getContext("height"));
      }
    }
    else if ($obj instanceof FO_TableRow) {
    	
	 $this->setLocalContext("width", $obj->getContext("width"));
	 $this->setLocalContext("height", $this->getContext("height")+
			   $obj->getContext("height"));
	 $this->setContext("y", $this->getContext("y") + 
			     $obj->getContext("height"));	 	 
    }
  }
}

class FO_TableHeader extends FO_TableRow {
  //oesi - set to _1 to work with php4
  static $CHILDNODES_1 = array(
				     'FO_TableCell',
				     'FO_TableRow'
				     );

  function getChildNodes() {
    return self::$CHILDNODES_1;
  }
}

class FO_TableFooter extends FO_LayoutObject {

  static $CHILDNODES = array(
				     'FO_TableCell',
				     'FO_TableRow'
				     );

  function getChildNodes() {
    return self::$CHILDNODES;
  }
}

class FO_TableBody extends FO_LayoutObject {

  static $CHILDNODES = array(
				     'FO_TableCell',
				     'FO_TableRow'
				     );

  function getChildNodes() {
    return self::$CHILDNODES;
  }
    
  function postParse(FO_Object $obj) {
    if ($obj instanceof FO_TableRow) {
	 $this->setLocalContext("width", $obj->getContext("width"));
	 $this->setLocalContext("height", $this->getContext("height")+
			   $obj->getContext("height"));
	 $this->setContext("y", $this->getContext("y") + 
			     $obj->getContext("height"));	 	 
    }
  }
  
  
}

class FO_TableCell extends FO_LayoutObject {

  static $CHILDNODES = array(
				     'FO_Block',
				     'FO_BlockContainer',
				     'FO_ListBlock',
				     'FO_Table',
				     'FO_TableAndCaption'
				     );
  

  function getChildNodes() {
    return self::$CHILDNODES;
  }

  function parse(DOMNode $node) {
    $col = $this->getContext("column");
    $width = $this->getContext("column-$col-width");
    $this->setLocalContext("width", $width);

    parent::parse($node);
  }

  /**
   * Overlap borders that the total width isn't larger than the 
   * specified
   */
  function drawBordersAndBackground($pos) {
    list($x1, $y1, $width1, $height1) = $pos[0];
    list($x2, $y2, $width2, $height2) = $pos[1];
    $xd = ($x1-$x2)/2;
    $yd = ($y1-$y2)/2;
    $wd = ($width2-$width1)/2;
    $hd = ($height2-$height1)/2;
    $this->drawBackground($x2, $y2, $width2+$wd, $height2+$hd);
    $this->drawBorders($x2, $y2, $width2+$wd, $height2+$hd);    
  }

  function postParse(FO_Object $obj) {
    $this->setLocalContext("height", $this->getContext("height")+
		      $obj->getContext("height")+0.5); // oesi - add +0.5 for tablespace
  }
}
?>
