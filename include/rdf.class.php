<?php
/* Copyright (C) 2011 FH Technikum-Wien
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
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>,
 * 			Andreas Österreicher <andreas.oesterreicher@technikum-wien.at>
 */
/**
 * RDF Klasse
 *
 * Hilfsfunktionen für die Generierung von RDF-Dateien
 *
 */
class rdf
{
	// Header Variablen
	public $content_type='Content-type: application/xhtml+xml';	// string
	public $xml_header='<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';	// string
	public $xml_ns;				// string
	protected $rdf_url;  		// string
	protected $rdf_text;
	protected static $nl="\n";
	protected static $tb="\t";

	// Objekt Variablen
	protected $counter=0;
	public $obj_id;
	public $obj = array();
	public $attr = array();
	protected $childs = array();
	protected $sequence = array();

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
	 * Erstellt ein neues RDF Description Objekt
	 *
	 * @return index des neuen Objekts
	 */
	public function newObjekt($id)
	{
		$this->obj[$this->counter] = new rdf();
		$this->obj[$this->counter++]->setObjID($id);
		return $this->counter-1;
	}

	/**
	 * Setzt die ID eines Objektes
	 *
	 * @param $id
	 */
	public function setObjID($id)
	{
		$this->obj_id=$id;
		return true;
	}

	/**
	 * Sendet die HTTP-Header der RDF Datei
	 * @param $cache
	 */
	public function sendHeader($cache=false)
	{

		if ($cache)
		{
		}
		else
		{
			header("Cache-Control: no-cache");
			header("Cache-Control: post-check=0, pre-check=0",false);
			header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
			header("Pragma: no-cache");
		}
		header($this->content_type);
		echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
		return true;
	}

	/**
	 * Setzt die Werte
	 * @param $name
	 * @param $value
	 * @param $cdata
	 */
	public function setAttribut($name,$value,$cdata=true, $parsetype=null)
	{
		if(!isset($this->attr[$this->counter]))
			$this->attr[$this->counter] = new stdClass();
		$this->attr[$this->counter]->name=$name;
		$this->attr[$this->counter]->value=$value;
		$this->attr[$this->counter]->cdata=$cdata;
		if(!is_null($parsetype))
			$this->attr[$this->counter]->parseType=$parsetype;
		$this->counter++;
		//var_dump($this->attr);
		return true;
	}

	/**
	 * Erzeugt den RDF Header aus den bestehenden Daten
	 */
	public function createRdfHeader()
	{
		$this->rdf_text="\n".'<RDF:RDF'."\n\t"
			.'xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"'."\n\t".'xmlns:nc="http://home.netscape.com/NC-rdf#"'."\n\t".'xmlns:'.$this->xml_ns.'="'.$this->rdf_url.'/rdf#"'."\n".'>'."\n\t";
	}

	/**
	 * Erzeugt die Descriptions aus den bestehenden Daten
	 */
	public function createRdfData()
	{
		foreach ($this->obj as $obj)
		{
			$this->rdf_text.="\n\t\t".'<RDF:Description id="'.$obj->obj_id.'"  about="'.$this->rdf_url.'/'.$obj->obj_id.'" >';
			foreach ($obj->attr as $attr)
			{
				if(isset($attr->parseType))
					$parsetype = 'nc:parseType="'.$attr->parseType.'"';
				else
					$parsetype='';
				$this->rdf_text.="\n\t\t\t<".$this->xml_ns.':'.$attr->name.' '.$parsetype.'><![CDATA['.$attr->value.']]></'.$this->xml_ns.':'.$attr->name.'>';
			}
			$this->rdf_text.="\n\t\t".'</RDF:Description>';
		}
	}

	/**
	 * Fuegt ein Objekt zur Sequence hinzu
	 * Wenn eine Parent_id uebergeben wird, wird das Objekt unterhalb dieses Eintrags
	 * angehängt
	 *
	 * @param $id
	 * @param $parent_id
	 */
	public function addSequence($id, $parent_id=null)
	{
		if(!is_null($parent_id))
		{
			$this->childs[$parent_id][]=$id;
		}
		else
		{
			$this->sequence[]=$id;
		}
	}

	/**
	 * Erzeugt die Sequenz
	 * Wenn eine ID uebergeben wird, wird nur die Sequenz unterhalb dieser ID erzeugt
	 *
	 * @param $id
	 */
	function createRDFSequence($id=null)
	{
		if(is_null($id))
		{
			$this->rdf_text.="\n\t\t".'<RDF:Seq about="'.$this->rdf_url.'">'."\n";
			foreach ($this->sequence as $id)
			{
				$this->createRDFSequence($id);
			}
			$this->rdf_text.="\n\t\t".'</RDF:Seq>'."\n";
		}
		else
		{
			$this->rdf_text.="\n\t".'<RDF:li RDF:resource="'.$this->rdf_url.'/'.$id.'" >';
			if(isset($this->childs[$id]))
			{
				$this->rdf_text.='<RDF:Seq about="'.$this->rdf_url.'/'.$id.'">'."\n";
				foreach($this->childs[$id] as $childid)
				{
					$this->createRDFSequence($childid);
				}
				$this->rdf_text.='</RDF:Seq>'."\n";
			}
			$this->rdf_text.='</RDF:li>';
		}
	}

	/**
	 * Generiert den RDF Footer
	 */
	public function createRdfFooter()
	{
		$this->rdf_text.='</RDF:RDF>'."\n\t";
	}

	/**
	 * Generiert das RDF
	 */
	public function sendRdfText()
	{
		//echo $this->rdf_text;
		if (!isset($this->rdf_text))
		{
			$this->createRdfHeader();
			$this->createRdfData();
			$this->createRdfSequence();
			$this->createRdfFooter();
		}
		echo $this->rdf_text;
		return true;
	}
}
?>
