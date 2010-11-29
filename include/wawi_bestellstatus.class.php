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
	public $new; 				// bool
	public $result = array(); 	// Aufteilungsobjekt array
	
	public $bestellung_bestellstatus_id; 		// integer
	public $bestellung_id; 
	public $bestellstatus_kurzbz; 
	public $uid; 
	public $oe_kurzbz; 
	public $datum; 
	public $insertvon; 
	public $insertamum; 
	public $updatevon; 
	public $updateamum; 
	
	
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
		
		$qry= "SELECT bestellstatus.* from wawi.tbl_bestellung_bestellstatus as bestellstatus where bestellstatus = ".$this->addslashes($bestellung_bestellstatus_id).";";
		
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
		$qry = "SELECT bestellstatus.* from wawi.tbl_bestellung_bestellstatus as bestellstatus;";
		
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
	public function isStatiVorhanden($bestellung_id, $status_kurzbz)
	{
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
		
		$qry = "select bestellstatus.* from wawi.tbl_bestellung_bestellstatus as bestellstatus
		where 
		bestellung_id = ".$this->addslashes($bestellung_id)." and bestellstatus_kurzbz = ".$this->addslashes($status_kurzbz).";";
		
		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				$this->datum = $row->datum; 
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
	 * Setzt den Status einer Bestellung auf Bestellt
	 */
	public function setBestellung()
	{	
		$qry = "INSERT INTO wawi.tbl_bestellung_bestellstatus (bestellung_id, bestellstatus_kurzbz, uid, oe_kurzbz, datum, insertvon, insertamum, updatevon, updateamum)
		VALUES
		(".($this->bestellung_id).", 'Bestellung',".$this->addslashes($this->uid).", ".$this->addslashes($this->oe_kurzbz).", '".($this->datum)."',
		 ".$this->addslashes($this->insertvon).", ".$this->addslashes($this->insertamum).", ".$this->addslashes($this->updatevon).", ".$this->addslashes($this->updateamum).");";
		
		if(!$this->db_query($qry))
		{
			$this->errormsg ="Fehler bei der Abfrage aufgetreten."; 
			return false; 
		}

		return true; 
	}
	
	/**
	 * 
	 * Enter description here ...
	 */
	public function setStorno()
	{
		$qry = "INSERT INTO wawi.tbl_bestellung_bestellstatus (bestellung_id, bestellstatus_kurzbz, uid, oe_kurzbz, datum, insertvon, insertamum, updatevon, updateamum)
		VALUES
		(".($this->bestellung_id).", 'Storno',".$this->addslashes($this->uid).", ".$this->addslashes($this->oe_kurzbz).", '".($this->datum)."',
		 ".$this->addslashes($this->insertvon).", ".$this->addslashes($this->insertamum).", ".$this->addslashes($this->updatevon).", ".$this->addslashes($this->updateamum).");";
		
		if(!$this->db_query($qry))
		{
			$this->errormsg ="Fehler bei der Abfrage aufgetreten."; 
			return false; 
		}

		return true; 
	}
}