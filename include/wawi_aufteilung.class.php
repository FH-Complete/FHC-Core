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
 * Klasse WaWi Aufteilung
 */
 
require_once(dirname(__FILE__).'/basis_db.class.php');

class wawi_aufteilung extends basis_db
{
	public $new; 				// bool
	public $result = array(); 	// Aufteilungsobjekt array
	
	public $aufteilung_id; 		// integer
	public $kostenstelle_id; 	// integer
	public $oe_kurzbz; 			// char
	public $anteil; 			// float(5,2)
	public $insertamum; 		// timestamp
	public $insertvon; 			// char
	public $updateamum; 		// timestamp
	public $updatevon; 			// char
	
	public $bestellung_id; 	
	
	/**
	 * 
	 * Konstruktor
	 * @param unknown_type $aufteilung_id
	 */
	public function __construct($aufteilung_id=null)
	{
		parent::__construct();
		
		if(!is_null($aufteilung_id))
			$this->load($aufteilung_id);
	}
	
	/**
	 * 
	 * Gibt die Aufteilung der übergebenen id zurück
	 * @param $aufteilung_id
	 */
	public function load($aufteilung_id)
	{
		if(!is_numeric($aufteilung_id))
		{
			$this->errormsg = "Ungültige aufteilung_id.";
			return false;
		}
		$qry = "SELECT * FROM wawi.tbl_aufteilung_default WHERE aufteilung_id =".$this->addslashes($aufteilung_id).';';
		 
		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				$this->aufteilung_id = $row->aufteilung_id; 
				$this->kostenstelle_id = $row->kostenstelle_id; 
				$this->oe_kurzbz = $row->oe_kurzbz; 
				$this->anteil = $row->anteil; 
				$this->insertamum = $row->insertamum; 
				$this->insertvon = $row->insertvon; 
				$this->updateamum = $row->updateamum; 
				$this->updatevon = $row->updatevon;
				
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
	 * Gibt alle Aufteilungen zurück
	 */
	public function getAll()
	{
		$qry = "SELECT *`FROM wawi.tbl_aufteilung_default;";
		
		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$aufteilung = new wawi_aufteilung(); 
				
				$aufteilung->aufteilung_id = $row->aufteilung_id; 
				$aufteilung->kostenstelle_id = $row->kostenstelle_id; 
				$aufteilung->oe_kurzbz = $row->oe_kurzbz; 
				$aufteilung->anteil = $row->anteil; 
				$aufteilung->insertamum = $row->insertamum; 
				$aufteilung->insertvon = $row->insertvon; 
				$aufteilung->updateamum = $row->upateamum; 
				$aufteilung->updatevon = $row->updatevon; 
				
				$this->result[] = $aufteilung; 
				
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
	 * Gibt alle Aufteilungen zurück die einer Kostenstelle zugeordnet sind
	 * @param $kostenstelle_id
	 */
	public function getAufteilungFromKostenstelle($kostenstelle_id)
	{
		if(!is_numeric($kostenstelle_id))
		{
			$this->errormsg = "Keine gültige Kostenstellen ID."; 
			return false; 
		}
		
		$qry = "SELECT aufteilung.* from wawi.tbl_aufteilung_default as aufteilung, wawi.tbl_kostenstelle as kostenstelle 
		where
		kostenstelle.kostenstelle_id = aufteilung.kostenstelle_id and kostenstelle.kostenstelle_id = ".$this->addslashes($kostenstelle_id).";";
		
		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$aufteilung = new wawi_aufteilung(); 
				
				$aufteilung->aufteilung_id = $row->aufteilung_id; 
				$aufteilung->kostenstelle_id = $row->kostenstelle_id; 
				$aufteilung->oe_kurzbz = $row->oe_kurzbz; 
				$aufteilung->anteil = $row->anteil; 
				$aufteilung->insertamum = $row->insertamum; 
				$aufteilung->insertvon = $row->insertvon; 
				$aufteilung->updateamum = $row->updateamum; 
				$aufteilung->updatevon = $row->updatevon; 
				
				$this->result[] = $aufteilung; 
			}
		}
		else
		{
			$this->errormsg = "Fehler bei der Abfrage."; 
			return false; 
		}
	}
	
	/**
	 * 
	 * Gibt alle Aufteilungen zurück die einer Bestellung zugeordnet sind
	 * @param $bestellung_id
	 */
	public function getAufteilungFromBestellung($bestellung_id)
	{
		
		$qry = "SELECT * from wawi.tbl_aufteilung where bestellung_id = ".$this->addslashes($bestellung_id)." Order by oe_kurzbz;";
		
		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$aufteilung = new wawi_aufteilung(); 
				
				$aufteilung->aufteilung_id = $row->aufteilung_id;  
				$aufteilung->bestellung_id = $row->bestellung_id; 
				$aufteilung->oe_kurzbz = $row->oe_kurzbz; 
				$aufteilung->anteil = $row->anteil; 
				$aufteilung->insertamum = $row->insertamum; 
				$aufteilung->insertvon = $row->insertvon; 
				$aufteilung->updateamum = $row->updateamum; 
				$aufteilung->updatevon = $row->updatevon; 
				
				$this->result[] = $aufteilung; 
			}
		}
		else
		{
			$this->errormsg = "Fehler bei der Abfrage."; 
			return false; 
		}
	}
	
	/**
	 * 
	 * return true wenn es den Aufteilungseintrag in tbl_aufteilung schon gibt, false wenn es ihn noch nicht gibt
	 */
	public function AufteilungExists()
	{
		$qry = "SELECT * from wawi.tbl_aufteilung where bestellung_id = ".$this->addslashes($this->bestellung_id)." and oe_kurzbz = ".$this->addslashes($this->oe_kurzbz).";";
		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				$this->aufteilung_id = $row->aufteilung_id; 
				return true;
			} 
			else
				return false;
		}
		else
			return false; 
	}
	
	/**
	 * 
	 * Speichert die Aufteilung in der Datenbank
	 */
	public function saveAufteilung()
	{
		$this->anteil = mb_str_replace(",",".",$this->anteil);
		$this->anteil = number_format($this->anteil,2,".",",");
		if($this->new == true)
		{
			// insert
			$qry= "Insert into wawi.tbl_aufteilung (bestellung_id, oe_kurzbz, anteil, updateamum, updatevon, insertamum, insertvon) values ("
			.$this->addslashes($this->bestellung_id).", "
			.$this->addslashes($this->oe_kurzbz).", "
			.$this->addslashes($this->anteil).", "
			.$this->addslashes($this->updateamum).", "
			.$this->addslashes($this->updatevon).", "
			.$this->addslashes($this->insertamum).", "
			.$this->addslashes($this->insertvon).");";
		}
		else
		{
			// update
			$qry = "Update wawi.tbl_aufteilung set 
			bestellung_id = ".$this->addslashes($this->bestellung_id).", 
			oe_kurzbz = ".$this->addslashes($this->oe_kurzbz).", 
			anteil = ".$this->addslashes($this->anteil).",
			updateamum = ".$this->addslashes($this->updateamum).",
			updatevon = ".$this->addslashes($this->updatevon)."
			where aufteilung_id = ".$this->addslashes($this->aufteilung_id).";"; 
		}

		if($this->db_query($qry))
			return true; 
		else
			return false; 
	}
		
}