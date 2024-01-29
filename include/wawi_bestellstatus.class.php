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
 * Klasse WaWi Bestelldetail
 */
 
require_once(dirname(__FILE__).'/basis_db.class.php');

class wawi_bestellstatus extends basis_db
{
	public $new;
	public $result = array(); 
	
	public $bestellung_bestellstatus_id; 	// integer
	public $bestellung_id; 					// integer
	public $bestellstatus_kurzbz; 			// varchar(32)
	public $uid; 							// varchar(32)
	public $oe_kurzbz; 						// varchar(32)
	public $datum; 							// date
	public $insertvon; 						// varchar(32)
	public $insertamum; 					// timestamp
	public $updatevon; 						// varchar(32)
	public $updateamum; 					// timestamp
		
	/**
	 * 
	 * Konstruktor
	 * @param unknown_type $aufteilung_id
	 */
	public function __construct($bestellung_bestellstatus_id=null)
	{
		parent::__construct();
		
		if(!is_null($bestellung_bestellstatus_id))
			$this->load($bestellung_bestellstatus_id);
	}

	/**
	 * 
	 * lädt den Bestellstatus der übergebenen ID 
	 * @param $bestellung_bestellstatus_id
	 */
	public function load($bestellung_bestellstatus_id)
	{
		if(!is_numeric($bestellung_bestellstatus_id))
		{
			$this->errormsg ="Ungültige Bestellstatus ID.";
			return false; 
		}
		
		$qry= "SELECT bestellstatus.* FROM wawi.tbl_bestellung_bestellstatus as bestellstatus 
				WHERE bestellung_bestellstatus_id = ".$this->db_add_param($bestellung_bestellstatus_id).";";
		
		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				$this->bestellung_bestellstatus_id = $row->bestellung_bestellstatus_id; 
				$this->bestellung_id = $row->bestellung_id; 
				$this->bestellstatus_kurzbz = $row->bestellstatus_kurzbz; 
				$this->uid = $row->uid; 
				$this->oe_kurzbz = $row->oe_kurzbz; 
				$this->datum = $row->datum; 
				$this->insertvon = $row->insertvon; 
				$this->insertamum = $row->insertamum; 
				$this->updatevon = $row->updatevon; 
				$this->updateamum = $row->updateamum; 
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
	 * Lädt alle Bestellstati zurück
	 */
	public function getAll()
	{
		$qry = "SELECT bestellstatus.* FROM wawi.tbl_bestellung_bestellstatus as bestellstatus;";
		
		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$status = new wawi_bestellstatus();
				
				$status->bestellung_bestellstatus_id = $row->bestellung_bestellstatus_id; 
				$status->bestellung_id = $row->bestellung_id; 
				$status->bestellstatus_kurzbz = $row->bestellstatus_kurzbz; 
				$status->uid = $row->uid; 
				$status->oe_kurzbz = $row->oe_kurzbz; 
				$status->datum = $row->datum; 
				$status->insertvon = $row->insertvon; 
				$status->insertamum = $row->insertamum; 
				$status->updatevon = $row->updatevon; 
				$status->updateamum = $row->updateamum; 
				
				$this->result[] = $status; 
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
	 * Return true wenn es den übergebenen Statuseintrag für die Übergebene Bestell ID gibt
	 * @param $bestellung_id
	 * @param $status_kurzbz
	 */
	public function isStatiVorhanden($bestellung_id, $status_kurzbz='', $oe_kurzbz ='')
	{
		$status='';
		if(!is_numeric($bestellung_id) || $bestellung_id == '')
		{
			$this->errormsg = "Bestellung ID fehlerhaft."; 
			return false; 
		}
		if($status_kurzbz == '')
		{
			$this->errormsg = "Status Kurzbezeichnung ist fehlerhaft."; 
			return false; 
		}
 
		if($oe_kurzbz!='')
		{
			$status .= " AND oe_kurzbz = ".$this->db_add_param($oe_kurzbz); 
		}
		if($status_kurzbz!='')
		{
			$status.=" AND bestellstatus_kurzbz = ".$this->db_add_param($status_kurzbz);
		}
		
		$qry = "SELECT bestellstatus.* 
				FROM wawi.tbl_bestellung_bestellstatus as bestellstatus
				WHERE 
					bestellung_id = ".$this->db_add_param($bestellung_id).$status."
				ORDER BY insertamum LIMIT 1;";
		
		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				$this->datum = $row->datum; 
				$this->insertvon = $row->insertvon; 
				return true;
			}
			else 
				return false;
		} 
		else
		{
			$this->errormsg = "Fehler bei der Abfrage aufgetreten.";
			return false; 
		}
	}
	
	/**
	 * 
	 * Speichert den Status in die Datenbank
	 */
	public function save()
	{
		if(!is_numeric($this->bestellung_id))
		{
			return false; 
		}
		
		$qry = "INSERT INTO wawi.tbl_bestellung_bestellstatus (bestellung_id, bestellstatus_kurzbz, uid, 
				oe_kurzbz, datum, insertvon, insertamum, updatevon, updateamum)	VALUES(".
				$this->db_add_param($this->bestellung_id, FHC_INTEGER).", ".
				$this->db_add_param($this->bestellstatus_kurzbz).", ".
				$this->db_add_param($this->uid).", ".
				$this->db_add_param($this->oe_kurzbz).", ".
				$this->db_add_param($this->datum).", ".
				$this->db_add_param($this->insertvon).", ".
				$this->db_add_param($this->insertamum).", ".
				$this->db_add_param($this->updatevon).", ".
				$this->db_add_param($this->updateamum).");";
		
		if(!$this->db_query($qry))
		{
			$this->errormsg ="Fehler bei der Abfrage aufgetreten."; 
			return false; 
		}

		return true; 
	}
	
	/**
	 * 
	 * Gibt das Bestelldetail einer Bestellung zur übergebenen BestellungID zurück
	 * @param $bestellung_id
	 */
	public function getStatiFromBestellung($status, $bestellung_id, $oe_kurzbz ='')
	{
		if(!is_numeric($bestellung_id) || $bestellung_id == '')
		{
			$this->errormsg = "Bestellung ID fehlerhaft."; 
			return false; 
		}
		
		$qry ="
			SELECT 
				* 
			FROM 
				wawi.tbl_bestellung_bestellstatus 
			WHERE 
				bestellstatus_kurzbz=".$this->db_add_param($status)." 
				AND bestellung_id=".$this->db_add_param($bestellung_id, FHC_INTEGER).";";
		 
		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{

				$this->bestellung_bestellstatus_id = $row->bestellung_bestellstatus_id; 
				$this->bestellung_id = $row->bestellung_id; 
				$this->bestellstatus_kurzbz = $row->bestellstatus_kurzbz; 
				$this->uid = $row->uid; 
				$this->oe_kurzbz= $row->oe_kurzbz; 
				$this->datum = $row->datum; 
				$this->insertvon = $row->insertvon; 
				$this->insertamum = $row->insertamum; 
				$this->updatevon = $row->updatevon; 
				$this->updateamum = $row->updateamum; 
				
				return true; 
			}
		}
		else
			return false; 
	}
	
	/**
	 * 
	 * liefert die Freigaben zu Kostenstellen oder Organisationseinheiten zurück, true wenn es einen Eintrag gibt, false wenn nicht
	 * wenn oe_kurzbz nicht mitangegeben wird, wird auf Kostenstelle Freigabe geprüft
	 * @param $bestellung_id
	 * @param $kostenstelle
	 */
	public function getFreigabeFromBestellung($bestellung_id, $oe_kurzbz='')
	{
		if($oe_kurzbz == '')
			$oe = ' AND oe_kurzbz is null';
		else
			$oe = ' AND oe_kurzbz= '.$this->db_add_param($oe_kurzbz); 
		
		$qry = "SELECT 
					* 
				FROM 
					wawi.tbl_bestellung_bestellstatus 
				WHERE 
					bestellung_id = ".$this->db_add_param($bestellung_id, FHC_INTEGER).
					' '.$oe.
					" AND bestellstatus_kurzbz = 'Freigabe';";
		
		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				$this->bestellung_bestellstatus_id = $row->bestellung_bestellstatus_id; 
				$this->bestellung_id = $row->bestellung_id; 
				$this->bestellstatus_kurzbz = $row->bestellstatus_kurzbz; 
				$this->uid = $row->uid; 
				$this->oe_kurzbz = $row->oe_kurzbz; 
				$this->datum = $row->datum; 
				$this->insertvon = $row->insertvon; 
				$this->insertamum = $row->insertamum; 
				$this->updatevon = $row->updatevon; 
				$this->updateamum = $row->updateamum; 
				
				return true;
			}
			else
				return false; 
		}
		else 
			return false; 
		
	}
	
}