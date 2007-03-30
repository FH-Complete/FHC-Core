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
class FO_Container {
  private $_refs = array();

  public function addReference($category, $name, $ref) {
    $_refs[$category][$name] = $ref;
  }

  public function resolveReference($category, $name) {
  	return (isset($_refs)?$_refs[$category][$name]:'');
  }
}

class FO_Context {
  public $_context;

  function __contruct() {
    $this->_context = array();
  }

  public function get($key) {
  	if(isset($this->_context[$key]))
    	return $this->_context[$key];
    else 
    	return false;
  }

  public function set($key, $val) {
    $this->_context[$key] = $val;
  }
}

abstract class FO_Object {		
  private $_children;
  private $_container;	
  private $_context;
  private $_localContext;
  private $_pdf;
  private $_parent;

  const NODE_TYPE_ELEMENT = 1;
  const NODE_TYPE_TEXT = 3;

  function __construct(FO_Container $container, FPDF $pdf, 
		       FO_Context $context) {
    //echo "New:$this:$context<br>";
    $this->_container = $container;
    $this->_pdf = $pdf;
    $this->_context = $context;    
    $this->_localContext = new FO_Context();    
  }

  /**
   * Check unit of value and scale to internal value, if needed
   * Internal values are stored in mm
   **/
  protected function calcInternalValue($value, $to = "mm", $from="mm") {
    sscanf($value, "%f%s", $value, $unit);
    if (!$unit) {
      $unit = $from;
    }
    //calculate to default value in mm
    switch ($to) {
	    case "mm":
		    switch ($unit) {
			    case "mm":
			      return $value;
			    case "cm":
			      return $value*10;
			    case "in":
			      return $value/25.4;
			    case "pt":
			      return $value*25.4/72;
			    default:
			      $this->NotYetSupported("Unit:$unit");
	      }
	      break;
	  case "cm": 
		    switch ($unit) {
			    case "mm":
			      return $value/10;
			    case "cm":
			      return $value;
			    case "in":
			      return $value/2.54;
			    case "pt":
			      return $value*2.54/72;
			    default:
			      $this->NotYetSupported("Unit:$unit");
	      }
	      break;
	  case "in": 
		    switch ($unit) {
			    case "mm":
			      return $value*25.4;
			    case "cm":
			      return $value*2.54;
			    case "in":
			      return $value;
			    case "pt":
			      return $value*72;
			    default:
			      $this->NotYetSupported("Unit:$unit");
	      }
	      break;
	  case "pt": 
		    switch ($unit) {
			    case "mm":
			      return $value*72/25.4;
			    case "cm":
			      return $value*72/2.54;
			    case "in":
			      return $value/72;
			    case "pt":
			      return $value;
			    default:
			      $this->NotYetSupported("Unit:$unit");
	      }
	      break;
	   default:
		     $this->NotYetSupported("Default Unit:$default");
    }
  }

  public function addReference($category, $name) {
    if ($category && $name) {
      $this->_container->addReference($category, $name, $this);
    }
  }

  public function resolveReference($category, $name) {
    return $this->_container->resolveReference($category, $name);
  }

  protected function setParent($parent) {
    $this->_parent = $parent;
  }

  protected function handleEvent($event) {   
    if ($event == "sync-position") {
      $pdf = $this->getPdf();
      $this->setContext("y", $pdf->GetY());
      $this->setContext("x", $pdf->GetX());
    }
    if ($this->_parent) {
      $this->_parent->handleEvent($event);
    }
  }

  /**
   * Get from current context informations
   */
  protected function getContext($key) {
    $val = $this->_localContext->get($key);
    if (!$val) {
      $val = $this->_context->get($key);
    }
    return $val;
  }

  /**
   * Set a context information for child nodes and current node as well
   */
  protected function setContext($key, $value) {
    $this->_context->set($key, $value);
  }

  protected function setLocalContext($key, $value) {
    $this->_localContext->set($key, $value);
  }
	
  protected function getAttribute(DOMNode $node, $key) {
  	if($node->attributes->getNamedItem($key)!=null)
    	return $node->attributes->getNamedItem($key)->nodeValue;
    else 
    	return false;
  }

  protected function getSizeAttribute(DOMNode $node, $key, $to="mm", $from="mm") {
  	if($node->attributes->getNamedItem($key)!=null)
  		$val = $node->attributes->getNamedItem($key)->nodeValue;
  	else 
  		$val=false;
   	return $this->calcInternalValue($val, $to, $from);
  }

  protected function initSizeAttribute(DOMNode $node, $key, $to="mm", $from="mm") {
    $val = $this->getSizeAttribute($node, $key, $to, $from);
    if ($val) {
      $this->setContext($key, $val);
    }
  }

  protected function initAttribute(DOMNode $node, $key) {
    $val = $this->getAttribute($node, $key);
    if ($val) {
      $this->setContext($key, $val);
    }
  }

  protected function initLocalSizeAttribute(DOMNode $node, $key, $to="mm", $from="mm"){
    $val = $this->getSizeAttribute($node, $key, $to, $from);
    if ($val) {
      $this->_localContext->set($key, $val);
    }
  }

  protected function initLocalAttribute(DOMNode $node, $key) {
    $val = $this->getAttribute($node, $key);    
    if ($val) {
      $this->_localContext->set($key, $val);
    }
  }

  protected function processChildNodes(DOMNode $node, $filter) {
    foreach($node->childNodes as $child) {
      $this->processChildNode($child, $filter);
    }
  }

  protected function processChildNode(DOMNode $node, $filter) {   
    $subcontext = clone $this->_context;
    $next = 
      FO_Factory::createFOObject($node, $this->_container, 
				 $this->_pdf, $subcontext, $filter);
    if ($next != null) {
      $next->setParent($this);
      $this->initFOObject($next);
      $this->_children[$node->nodeName] = $next;
      $this->preParse($next);
      $next->parse($node);
      $this->postParse($next);
    }				
  }

  protected function initFOObject(FO_Object $obj) {
  }

  protected function preParse(FO_Object $obj) {
  }
  
  protected function postParse(FO_Object $obj) {    
  }

  protected function processContents(DOMNode $node) {
    foreach($node->childNodes as $child) {
      if ($child->nodeType == self::NODE_TYPE_TEXT) {
	$this->processContent($child->textContent);
      }
    }
  }

  protected function processContent($content) {
    //do nothing per default
  }

  protected function getPdf() {
    return $this->_pdf;
  }

  protected function NotYetSupported($msg=0) {
    echo "Not Yet Supported[".get_class($this)."]:$msg<br>";
  }

  protected function children() {
    return $this->_children;
  }

  /**
   * Parse the color from either xml attribute value or FPDF
   * internal representation
   **/
  protected function parseColor($color) {
     if (sscanf($color, "#%2x%2x%2x", $r, $g, $b) == 3) {
       //parse RGB color
       $r = sprintf("%d", $r);
       $g = sprintf("%d", $g);
       $b = sprintf("%d", $b);
     }
     else if(sscanf($color, "rgb(%d,%d,%d)", $r, $g, $b) == 3) {      
     }
     else if (sscanf($color, "%f %f %f RG", $r, $g, $b) == 3) {
     }
     else if (sscanf($color, "%f G", $g) == 1) {
     }
     else {
       //get color from word
       switch ($color) {
       case "white":
	 $r=255; $g=255; $b=255;
	 break;
       case "red":
	 $r=255; $g=0; $b=0;
	 break;
       case "green":
	 $r=0; $g=255; $b=0;
	 break;
       case "blue":
	 $r=0; $g=0; $b=255;
	 break;
       case "yellow":
	 $r=255; $g=255; $b=0;
	 break;       
       case "magenta":
	 $r=0; $g=255; $b=255;
	 break;
       case "cyan":
	 $r=255; $g=0; $b=255;
	 break;              
       case "black":
       default:
	 $r=0; $g=0; $b=0;
	 break;       
       }
     }
     return array($r, $g, $b);
   }   

  public abstract function parse(DOMNode $node);	
}

/**
 * <fo:root xmlns:fo="http://www.w3.org/1999/XSL/Format">
 *  <!-- The full XSL-FO document goes here -->
 * </fo:root>
 */
class FO_Root extends FO_Object{

  function __construct(FPDF $pdf) {
    $container = new FO_Context();    
    parent::__construct(new FO_Container(), $pdf, $container);
    $this->setContext("acceptPageBreak", true);
  }
	
  private static $CHILDNODES = array (
				      'FO_LayoutMasterSet',
				      'FO_PageSequence'
				      );
	
  public function parse(DOMNode $node) {
    //no attrbutes which concerns us
    $_children[$node->nodeName] = 
      $this->processChildNodes($node, self::$CHILDNODES);
  }

  public function setContext($key, $value) {
    parent::setContext($key, $value);
  }
}

?>
