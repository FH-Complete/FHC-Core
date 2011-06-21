<?php
/* Copyright (C) 2011
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307, USA.
 *
 * Authors: Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>
 */
//namespace XSDFormPrinter;

class XSDFormPrinter
{
	public $xml = '<?xml version="1.0" encoding="UTF-8"?>';
	public $xml_inhalt;
	public $getparams='';
	
	public function __construct()
	{
		$this->loadDefaultConfiguration();
		require_once(dirname(__FILE__).'/types.php');
	}

	/**
	 * 
	 * Erzeugt das Formular
	 * @param $xsd XSD File (nicht der Filename)
	 * @param $xml XML mit den Daten die vorausgefuellt werden sollen
	 */
	public function output($xsd, $xml)
	{
		$dom = new DOMDocument();
		$dom->loadXML($xsd);

		
		$this->xml_inhalt = new DOMDocument();
		if($xml!='')
		{
			$this->xml_inhalt->loadXML($xml);
		}
		if($dom===false)
		{
			echo 'Failed to load XSD into DOM';
			return false;
		}

		echo '<script type="text/javascript">
			function '.$this->config['PREFIX'].'createXML()
			{
				$("textarea").each(function(){
					if(this.id.match(/^'.$this->config['PREFIX'].'FIELD/))
					{
						xml = xml.replace("$$"+this.id+"$$", tinyMCE.get(this.id).getContent());
					}
				});
				$(":checkbox").each(function(){
					if(this.id.match(/^'.$this->config['PREFIX'].'FIELD/))
					{
						var chk = this.checked;
						var chki = $(this).checked;
						xml = xml.replace("$$"+this.id+"$$", this.checked);
					}
				});
				$(":input").each(function(){
					if(this.id.match(/^'.$this->config['PREFIX'].'FIELD/))
						xml = xml.replace("$$"+this.id+"$$", $(this).val());
				});
								
				$("#'.$this->config['PREFIX'].'XML").val(xml);
				return true;
			}
			</script>';
		echo '<form action="'.$this->getparams.'" method="POST" onsubmit="'.$this->config['PREFIX'].'createXML()">';
		echo '<input type="hidden" name="'.$this->config['PREFIX'].'XML" id="'.$this->config['PREFIX'].'XML">';
		echo "<table>\n<tbody>";
		$output = $this->process($dom);
		$this->xml.=$output['xml'];
		echo "\n</tbody>";
		echo '
			<tfoot>
				<td></td>
				<td><input type="submit" value="Save"></td>
			</tfoot>';
		echo '</table>';
		echo "<script type=\"text/javascript\">var xml= unescape('".rawurlencode($this->xml)."');</script>";
		echo '</form>';
	}

	private function loadDefaultConfiguration()
	{
		require_once(dirname(__FILE__).'/config.inc.php');
	}

	/**
	 * 
	 * Parst das DOM Document
	 * @param $dom DOMDocument des XSD Files
	 */
	private function process($dom)
	{
		$addoutput=array('html'=>'','xml'=>'');
		if(!$dom->childNodes)
			return false;

		foreach($dom->childNodes as $child) 
		{
			if($child != NULL)
			{
				if(get_class($child)=='DOMText')
					continue;
					
				$name=$child->getAttribute('name');
				$maxoccurs = $child->getAttribute('maxOccurs');
				$minoccurs = $child->getAttribute('minOccurs');
				
				if($maxoccurs=='') $maxoccurs=1;
				if($minoccurs=='') $minoccurs=1;
				
				if($maxoccurs>1 || $maxoccurs=='unbounded')
				{
					$this->xml.=$addoutput['xml'];
					$addoutput=array('html'=>'','xml'=>'');
					$addfunc = uniqid($this->config['PREFIX'].'ADD_');
					$addid = uniqid($this->config['PREFIX'].'ADDID_');
					echo "<tr><td>$name<br><a href=\"#AddMore\" onclick=\"return $addfunc()\">Add More</a> ($maxoccurs)
					</td><td id=\"$addid\"><fieldset style='background-color: lightgray'><table>";
				}
				
				if($child->nodeName == "xs:element")
				{
					$type=$child->getAttribute('type');
					$addoutput['xml'].="\n<".$name.'>';
					if($type!='')
					{
						$output=$this->input($type, $name, $minoccurs);
						$addoutput['html'].=$output['html'];
						$addoutput['xml'].=$output['xml'];
						echo $output['html'];
					}
					else
					{
						$this->xml.=$addoutput['xml'];
						$addoutput['xml']='';
					}
					$output=$this->process($child);
					$addoutput['html'].=$output['html'];
					$addoutput['xml'].=$output['xml'];
					
					if($type=='')
						$addoutput['xml'].="\n";
					
					$addoutput['xml'].='</'.$name.">";
				}
				else
				{
					
					$output=$this->process($child);
					$addoutput['html'].=$output['html'];
					$addoutput['xml'].=$output['xml'];
				}
					
				if($maxoccurs>1 || $maxoccurs=='unbounded')
				{
					echo "</table></fieldset>
					<script type=\"text/javascript\">
					var ".$addid."_counter=0;
					
					function $addfunc()
					{
						".$addid."_counter=".$addid."_counter+1;
						var data = unescape('".rawurlencode("<fieldset class=\"XSDFormPrinterStyle_fieldset\"><table>".$addoutput['html']."</table></fieldset>")."');
						$(\"#$addid\").append(data);
						
						var xmldata=unescape('".rawurlencode($addoutput['xml'])."');
						
						var position = xml.indexOf('<$name>');
						
						var before = xml.substring(0, position);
						var after = xml.substring(position);
						xml = before+xmldata+after;
						
						return false;
					}
					</script></td></tr>";
				}
			}
		}
		return $addoutput;
	}

	/**
	 * 	Erzeugt ein Eingabefeld
	 * @param type type des Elements
	 * @param name Name des Elements
	 */
	private function input($type, $name, $minoccurs)
	{
		$output=array('html'=>'','xml'=>'');
		// ElementType => (MyType, (RegexPattern=>Errormsg))
		static $factory = array('xs:string'=>array('string',array()),
								'xs:decimal'=>array('string',array('/^\d[.,]\d*$/'=>'Value must be a valid Number')),
								'xs:integer'=>array('string',array('/^\d/$'=>'Value must be a valid Number')),
								'xs:positiveInteger'=>array('string',array('/^\d$/'=>'Value must be a positive Number')),
								'xs:date'=>array('date',array()),
								'wysiwyg'=>array('wysiwyg',array()),
								'file'=>array('file',array()),
								'boolean'=>array('boolean',array())
								);

		if(!isset($factory[$type]))
		{
			$this->debugmsg(0,"Input Type $type not supported -> using string instead");
			$type='xs:string';
		}

		$output['html'].= "\n<tr>";
		$ftype = $factory[$type][0];
		$output['html'].= '<td>'.$name.($minoccurs>0?'<span class="XSDFormPrinterStyle_required"> *</span>':'').'</td><td>';
		//ToDo: Create a Unique reproduceable fieldid
		$fieldid = $this->config['PREFIX'].'FIELD_'.$name;
		$validatefunction = $this->createValidation($fieldid, $factory[$type][1]);
		if($this->xml_inhalt->getElementsByTagName($name)->item(0))
			$value = $this->xml_inhalt->getElementsByTagName($name)->item(0)->nodeValue;
		else
			$value='';
		$output['html'].=sprintf($this->types[$ftype],$name, $fieldid,$validatefunction, $value);
		$output['html'].= '</td>';
		$output['html'].= '</tr>';
		$output['xml'].='<![CDATA[$$'.$fieldid.'$$]]>';
		return $output;
	}
	
	/**
	 * Erzeugt eine Javascript Funktion zum Validieren der Eingabe
	 * @param $fieldid ID des Feldes das Validiert werden soll
	 * @param patterns Array mit RegEx Patterns als Key und der Fehlermeldung als Value
	 */
	private function createValidation($fieldid, $patterns)
	{
		if(count($patterns)==0)
			return false;
		$functionname = uniqid($this->config['PREFIX'].'FUNC_');
		$func = 'function '.$functionname.'() {';
		$func .= 'var fieldvalue = document.getElementById("'.$fieldid.'").value;';
		foreach($patterns as $pattern=>$errormsg)
		{
			$func.='if(!'.$pattern.'.test(fieldvalue)){alert("'.$errormsg.'"); return false;}';
		}
		$func .= '};';
		echo '<script type="text/javascript">'.$func.'</script>';
		return $functionname.'()';
	}

	/**
	 * Prints a Debug Message
	 *
	 * @param dbglevel Debug Level
	 * @param msg Message to Output
	 */
	private function debugmsg($dbglevel, $msg)
	{
		if($dbglevel<=$this->config['debuglevel'])
			echo $msg;
	}
}