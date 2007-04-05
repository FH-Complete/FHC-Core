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
require_once("fo_svg.php");
class FO_InstreamForeignObject extends FO_Object {

  static $CHILDNODES = array(
				     'FO_SVG'
				     );

  function parse(DOMNode $node) {
   $this->processChildNodes($node, self::$CHILDNODES);
  }

  function postParse(FO_Object $obj) {
    $this->setLocalContext("width", $obj->getContext("width"));
    $this->setLocalContext("height", $this->getContext("height")+
			   $obj->getContext("height"));
    $this->setContext("y", $this->getContext("y") + 
		      $obj->getContext("height"));
    //echo "Post:".$this->getContext("y")."<br>";
  }
}
?>