<?php
/* Copyright (C) 2006 Technikum-Wien
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
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>
 */

class rdf 
{
	// Header Variablen
	public $content_type='Content-type: application/xhtml+xml';	// string
	public $xml_header='<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';	// string
	public $xml_ns;				// string
	protected $rdf_url;      			// string
	protected static $rdf_text;
	protected static $nl="\n";
	protected static $tb="\t";

	// Objekt Variablen
	protected $counter=0;			// int
	public $obj_id;				// string
	public $obj = array();
	public $attr = array();			// string

	/**
	 * Konstruktor - Uebergibt die Connection und laedt optional eine Reservierung
	 * @param $reservierung_id
	 */
	public function __construct($xml_ns=null,$rdf_url=null)
	{
		$this->xml_ns = $xml_ns;
		$this->rdf_url = $rdf_url;
	}
	
	/**
	 * Prueft die Variablen vor dem Speichern
	 * auf Gueltigkeit.
	 * @return true wenn ok, false im Fehlerfall
	 */

	public function newObjekt($id)
	{
		$this->obj[$this->counter] = new rdf();
		$this->obj[$this->counter++]->setObjID($id);
		return $this->counter-1;
	}

	public function setObjID($id)
	{
		$this->obj_id=$id;
		return true;
	}
	
	public function sendHeader($cache=false)
	{

		if ($cache)
		{
		}
		else
		{
			header("Cache-Control: no-cache");
			header("Cache-Control: post-check=0, pre-check=0",false);
			header("Expires Mon, 26 Jul 1997 05:00:00 GMT");
			header("Pragma: no-cache");
		}
		header($this->content_type);
		echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
		return true;
	}

	public function setAttribut($name,$value,$cdata=true)
	{
		$this->attr[$this->counter]->name=$name;
		$this->attr[$this->counter]->value=$value;
		$this->attr[$this->counter]->cdata=$cdata;
		$this->counter++;
		//var_dump($this->attr);
		return true;
	}

	public function createRdfHeader()
	{
		$this->rdf_text="\n".'<RDF:RDF'."\n\t"
			.'xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"'."\n\t".'xmlns:'.$this->xml_ns.'="'.$this->rdf_url.'/rdf#"'."\n".'>'."\n\t"
			.'<RDF:Seq about="'.$this->rdf_url.'">'."\n";
	}
	
	public function createRdfData()
	{
		foreach ($this->obj as $obj)
		{		
			$this->rdf_text.="\n\t".'<RDF:li>'
				."\n\t\t".'<RDF:Description id="'.$obj->obj_id.'"  about="'.$this->rdf_url.'/'.$obj->obj_id.'" >';
			foreach ($obj->attr as $attr)
				$this->rdf_text.="\n\t\t\t<".$this->xml_ns.':'.$attr->name.'><![CDATA['.$attr->value.']]></'.$this->xml_ns.':'.$attr->name.'>';
			$this->rdf_text.="\n\t\t".'</RDF:Description>'."\n\t".'</RDF:li>';
		}
	}
	
	public function createRdfFooter()
	{
		$this->rdf_text.='</RDF:Seq>'."\n".'</RDF:RDF>'."\n\t";
	}

	public function sendRdfText()
	{
		//echo $this->rdf_text;
		if (!isset($this->rdf_text))
		{
			$this->createRdfHeader();
			$this->createRdfData();
			//$this->createRdfSequence();
			$this->createRdfFooter();
		}
		echo $this->rdf_text;
		return true;
	}
}
?>
