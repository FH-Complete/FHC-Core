<?php
/* Copyright (C) 2010 Technikum-Wien
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
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 *          Karl Burkhart <burkhart@technikum-wien.at>.
 */
/**
 * Klasse WaWi Tags
 */

require_once(dirname(__FILE__).'/basis_db.class.php');
require_once(dirname(__FILE__).'/sprache.class.php');

class wawi_tags extends basis_db
{
	public $result = array();		//  Konto Objekt

	//Tabellenspalten
	public $tag;					//  integer
	public $bestellung_id;			//  string
	public $insertamum;        		//  timestamp
	public $insertvon;				//  string
	
	public $bestelldetail_id; 

	 
	
	
	/**
	 * Konstruktor
	 * @param $konto_id ID des Kontos das geladen werden soll (Default=null)
	 */
	public function __construct($tag=null)
	{
		parent::__construct();
		
		if(!is_null($tag))
			$this->load($tag);
	}

	
	public function getAll()
	{
		$qry = "Select * from public.tbl_tag; ";

		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$tag = new wawi_tags();
				
				$tag->tag = $row->tag; 

				$this->result[] = $tag; 
			}	
			return true; 
		}
		else
		{
			$this->errormsg = "Fehler bei der Abfrage aufgetreten."; 
			return false; 
		}
		
	}
	
	/**
	 * 
	 * Gibt die Tags einer Bestellung zurück
	 * @param unknown_type $bestellung_id
	 */
	public function GetTagsByBestellung($bestellung_id)
	{
		if(!is_numeric($bestellung_id))
		{
			$this->errormsg = "Ungültige Bestellung ID"; 
			return false; 
		}
		
		$qry = "Select * from wawi.tbl_bestellungtag where bestellung_id = ".$bestellung_id.";";
		
		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$bestelltag = new wawi_tags();
				
				$bestelltag->tag = $row->tag; 
				$bestelltag->bestellung_id = $row->bestellung_id; 
				$bestelltag->insertamum = $row->insertamum; 
				$bestelltag->insertvon = $row->insertvon; 
				
				$this->result[] = $bestelltag; 
			}	
		}
		else
		{
			$this->errormsg = "Fehler bei Abfrage aufgetreten."; 
			return false; 
		}
		return true; 
	}

	/**
	 * 
	 * gibt die Tags per strichpunkt getrennt zurück
	 */
	public function GetStringTags()
	{
			$string = '';
			foreach($this->result as $row)
			{
				if($string!='')
					$string.='; ';
				$string.=($row->tag);
			}
			return $string;
	}


	public function GetTagsByBesteldetail($bestelldetail_id)
	{
		/*if(!is_numeric($bestelldetail_id))
		{
			$this->errormsg = "Ungültige Bestelldetail ID"; 
			return false; 
		}*/
		
		$qry = "Select * from wawi.tbl_bestelldetailtag where bestelldetail_id = ".$bestelldetail_id.";";
		
		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$bestelltag = new wawi_tags();
				
				$bestelltag->tag = $row->tag; 
				$bestelltag->bestelldetail_id = $row->bestelldetail_id; 
				$bestelltag->insertamum = $row->insertamum; 
				$bestelltag->insertvon = $row->insertvon; 
				
				$this->result[] = $bestelltag; 
			}	
		}
		else
		{
			$this->errormsg = "Fehler bei Abfrage aufgetreten."; 
			return false; 
		}
		return true; 
	}


}

