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
 * Klasse Tags
 */

require_once(dirname(__FILE__).'/basis_db.class.php');
require_once(dirname(__FILE__).'/sprache.class.php');

class tags extends basis_db
{
	public $result = array();		//  Konto Objekt

	//Tabellenspalten
	public $tag;					//  integer
	public $bestellung_id;			//  string
	public $insertamum;        		//  timestamp
	public $insertvon;				//  string
	
	public $bestelldetail_id; 		// integer 

	
	/**
	 * Konstruktor
	 *
	 */
	public function __construct($tag=null)
	{
		parent::__construct();
		
		if(!is_null($tag))
			$this->load($tag);
	}
	
	/**
	 * 
	 * Gibt alle Tags zurück
	 */
	public function getAll($tag_search = null)
	{
		$matchcode=mb_strtoupper(str_replace(array('<','>',' ',';','*','_','-',',',"'",'"'),"%",$tag_search));
		$qry = "SELECT * FROM public.tbl_tag ";

		if (!empty($tag_search))
		{
			$qry.="WHERE UPPER(trim(public.tbl_tag.tag)) like '%".$this->db_escape($matchcode)."%' ";
		}
		$qry.="ORDER BY UPPER(trim(public.tbl_tag.tag)) ";

		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$tag = new tags();
				
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
	 * @param $bestellung_id
	 */
	public function GetTagsByBestellung($bestellung_id)
	{
		if(!is_numeric($bestellung_id))
		{
			$this->errormsg = "Ungültige Bestellung ID"; 
			return false; 
		}
		
		$qry = "SELECT * FROM wawi.tbl_bestellungtag WHERE bestellung_id = ".$this->db_add_param($bestellung_id, FHC_INTEGER).';';
		
		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$bestelltag = new tags();
				
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
	 * gibt die Tags per Strichpunkt getrennt zurück
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


	/**
	 * 
	 * Gibt alle Tags eines Bestelldetails zurück
	 * @param $bestelldetail_id
	 */
	public function GetTagsByBestelldetail($bestelldetail_id)
	{
		if($bestelldetail_id == '')
		{
			$this->errormsg = "Ungültige Bestelldetail ID"; 
			return false; 
		}
		
		$qry = "SELECT * FROM wawi.tbl_bestelldetailtag WHERE bestelldetail_id = ".$this->db_add_param($bestelldetail_id, FHC_INTEGER).";";

		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$bestelltag = new tags();
				
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
	
	/**
	 * 
	 * return true wenn Tag schon vorhanden ist, false wenn Tag noch nicht vorhanden ist
	 */
	public function TagExists()
	{
		$qry = "SELECT * FROM public.tbl_tag WHERE tag = ".$this->db_add_param($this->tag).";"; 
		
		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
				return true; 
			else
				return false; 
		}
		else
			return false; 
	}
	
	/**
	 * 
	 * return true wenn Tag schon in der Zwischentabelle vorhanden ist, 
	 * false wenn Tag noch nicht vorhanden ist
	 */
	public function BestellungTagExists()
	{
		$qry = "SELECT 
					* 
				FROM 
					wawi.tbl_bestellungtag 
				WHERE 
					tag = ".$this->db_add_param($this->tag)." 
					AND bestellung_id = ".$this->db_add_param($this->bestellung_id, FHC_INTEGER).";";
		
		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
				return true; 
			else
				return false;
		}
		else
			return false; 
	}
	
	/**
	 * 
	 * Speichert den Tag in der Datenbank
	 */
	public function saveTag()
	{
		if($this->tag != '')
		{
			$qry = "INSERT INTO public.tbl_tag (tag) VALUES (".$this->db_add_param($this->tag).");";
			
			if($this->db_query($qry))
			{
				return true; 
			}
			else
			{
				return false; 
			}
		}
	}
	
	/**
	 * 
	 * Speichert den Tag in tbl_bestelltag und weist dem Eintrag die Bestellung zu
	 * @param $bestellung_id
	 */
	public function saveBestellungTag()
	{ 
		if($this->tag != '')
		{
			$qry = "INSERT INTO wawi.tbl_bestellungtag (tag, bestellung_id, insertamum, insertvon) 
			VALUES("
			.$this->db_add_param($this->tag).","
			.$this->db_add_param($this->bestellung_id, FHC_INTEGER).","
			.$this->db_add_param($this->insertamum).","
			.$this->db_add_param($this->insertvon).");";
			
			if(!$this->db_query($qry))
			{
				$this->errormsg ="Fehler bei der Abfrage aufgetreten."; 
				return false; 
			}
			
			return true; 
		}
	}

	/**
	 * 
	 * Loescht die alle Tags zu einer Bestellung die nicht uebergeben werden
	 * BestellungID muss in der Klasse gesetzt sein 
	 * @param $tagArray Array mit Tags die nicht entfernt werden sollen
	 */
	public function deleteBestellungTag($tagArray)
	{
		$sqlTag = $this->implode4SQL($tagArray);
		$qry = "DELETE FROM wawi.tbl_bestellungtag 
				WHERE 
					bestellung_id = ".$this->db_add_param($this->bestellung_id, FHC_INTEGER)." 
					AND  tag not in(".$sqlTag.");"; 
 
		if(!$this->db_query($qry))
		{
			$this->errormsg ="Fehler bei der Abfrage aufgetreten."; 
			return false; 
		}
		
		return true; 
	}
	
	/**
	 * 
	 * Speichert den Tag in tbl_bestelldetailtag und weist dem Eintrag das Bestelldetail zu
	 * @param $bestellung_id
	 */
	public function saveBestelldetailTag()
	{ 
		if($this->tag != '')
		{
			$qry = "INSERT INTO wawi.tbl_bestelldetailtag (tag, bestelldetail_id, insertamum, insertvon) 
			VALUES("
			.$this->db_add_param($this->tag).","
			.$this->db_add_param($this->bestelldetail_id, FHC_INTEGER).","
			.$this->db_add_param($this->insertamum).","
			.$this->db_add_param($this->insertvon).");";
			
			if(!$this->db_query($qry))
			{
				$this->errormsg ="Fehler bei der Abfrage aufgetreten."; 
				return false; 
			}
			
			return true; 
		}
	}
	
	/**
	 * 
	 * Loescht die alle Tags zu einem Bestelldetail die nicht uebergeben werden
	 * BestelldetailID muss in der Klasse gesetzt sein 
	 * @param $tagArray Array mit Tags die nicht geloescht werden sollen
	 */
	public function deleteBestelldetailTag($tagArray)
	{
		$sqlTag = $this->implode4SQL($tagArray);
		$qry = "DELETE FROM wawi.tbl_bestelldetailtag 
				WHERE 
					bestelldetail_id = ".$this->db_add_param($this->bestelldetail_id, FHC_INTEGER)." 
					AND tag not in(".$sqlTag.");"; 
 
		if(!$this->db_query($qry))
		{
			$this->errormsg ="Fehler bei der Abfrage aufgetreten."; 
			return false; 
		}
		
		return true; 
	}
	
	/**
	 * Prueft ob ein Tag zu einem Bestelldetail zugeordnet ist
	 * 
	 * @return true wenn Tag schon in der Zwischentabelle vorhanden ist, 
	 * false wenn Tag noch nicht vorhanden ist
	 */
	public function BestelldetailTagExists()
	{
		$qry = "SELECT * FROM wawi.tbl_bestelldetailtag 
				WHERE 
					tag = ".$this->db_add_param($this->tag)." 
					AND bestelldetail_id = ".$this->db_add_param($this->bestelldetail_id, FHC_INTEGER).";";
		
		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
				return true; 
			else
				return false;
		}
		else
			return false; 
	}

}
?>