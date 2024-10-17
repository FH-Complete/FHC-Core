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

class wawi_bestelldetail extends basis_db
{	
	public $bestelldetail_id;	// serial 
	public $bestellung_id; 		// integer
	public $position; 			// integer
	public $menge; 				// integer
	public $verpackungseinheit; // char
	public $beschreibung; 		// text
	public $artikelnummer; 		// char
	public $preisprove; 		// numeric
	public $mwst; 				// numeric
	public $erhalten=false;	// bool
	public $sort; 				// integer
	public $text=false;		// bool
	public $insertamum; 		// timestamp
	public $insertvon; 			// char
	public $updateamum; 		// timestamp
	public $updatevon; 			// char
	public $user; 
	public $new; 
	
	public $result = array(); 

	
	/**
	 * 
	 * Konstruktor
	 * @param $bestelldetail_id
	 */
	public function __construct($bestelldetail_id = null)
	{
		parent::__construct(); 
		
		if(!is_null($bestelldetail_id))
			$this->load($bestelldetail_id);
	}
	
	/**
	 * 
	 * Gibt das Beselldetail der übergebenen ID zurück
	 * @param $bestelldetail_id
	 */
	public function load($bestelldetail_id)
	{
		if(!is_numeric($bestelldetail_id))
		{
			$this->errormsg ='Keine gültige Bestell ID.';
			return false; 
		}
		
		$qry = 'SELECT * FROM wawi.tbl_bestelldetail WHERE bestelldetail_id = '.$this->db_add_param($bestelldetail_id, FHC_INTEGER).';'; 
		
		if(!$this->db_query($qry))
		{
			$this->errormsg = 'Fehler bei Datenbankabfrage.';
			return false; 
		}
		
		if($row = $this->db_fetch_object())
		{
			$this->bestelldetail_id = $row->bestelldetail_id;
			$this->bestellung_id = $row->bestellung_id; 
			$this->position = $row->position; 
			$this->menge = $row->menge; 
			$this->verpackungseinheit = $row->verpackungseinheit; 
			$this->beschreibung = $row->beschreibung; 
			$this->artikelnummer = $row->artikelnummer; 
			$this->preisprove = $row->preisprove; 
			$this->mwst = $row->mwst; 
			$this->erhalten = $this->db_parse_bool($row->erhalten); 
			$this->sort = $row->sort; 
			$this->text = $this->db_parse_bool($row->text); 
			$this->insertamum = $row->insertamum; 
			$this->insertvon = $row->insertvon; 
			$this->updateamum = $row->updateamum; 
			$this->updatevon = $row->updatevon; 
		}
		return true; 
	}
	
	/**
	 * 
	 * Gibt alle Bestelldetails zurück
	 */
	public function getAll()
	{
		$qry ='SELECT * FROM wawi.tbl_bestelldetail;';
		
		if(!$this->db_query($qry))
		{
			$this->errormsg = 'Fehler bei Datenbankabfrage.';
			return false; 
		}
		while($row = $this->db_fetch_object())
		{
			$detail = new wawi_bestelldetail();
			
			$detail->bestelldetail_id = $row->bestelldetail_id; 
			$detail->bestellung_id = $row->bestellung_id; 
			$detail->positoin = $row->position; 
			$detail->menge = $row->menge; 
			$detail->verpackungseinheit = $row->verpackungseinheit; 
			$detail->beschreibung = $row->beschreibung; 
			$detail->artikelnummer = $row->artikelnummer; 
			$detail->preisprove = $row->preisprove; 
			$detail->mwst = $row->mwst; 
			$detail->erhalten = $this->db_parse_bool($row->erhalten); 
			$detail->sort = $row->sort; 
			$detail->text = $this->db_parse_bool($row->text); 
			$detail->insertamum = $row->insertamum; 
			$detail->insertvon = $row->insertvon; 
			$detail->updateamum = $row->updateamum; 
			$detail->updatevon = $row->updatevon; 
			
			$detail->result = $detail; 
		}
		return true; 
	}
	
	/**
	 * 
	 * Löscht das Bestelldetail mit der übergebenen ID
	 * @param $bestelldetail_id
	 */
	public function delete($bestelldetail_id)
	{
		if(!is_numeric($bestelldetail_id))
		{
			$this->errormsg = 'Keine gültige Bestell ID.';
			return false;
		}
		
		$qry = 'DELETE FROM wawi.tbl_bestelldetail WHERE bestelldetail_id ='.$this->db_add_param($bestelldetail_id).';';
		
		if(!$this->db_query($qry))
		{
			$this->errormsg = 'Fehler beim Löschen des Betstelldetails.';
			return false; 
		}
		return true; 
	}
	
	/**
	 * 
	 * Überprüft die Richtigkeit der Daten
	 */
	public function validate()
	{
		
		if(!is_numeric($this->bestellung_id))
		{
			$this->errormsg = "Bestellung_id fehlerhaft.";
			return false;
		}	
		if(mb_strlen($this->verpackungseinheit)>16)
		{
			$this->errormsg ="Verpackungseinheit fehlerhaft.";
			return false; 
		}
		if(mb_strlen($this->artikelnummer)>32)
		{
			$this->errormsg ="Artikelnummer fehlerhaft.";
			return false; 
		}		
		if(!is_numeric($this->preisprove) && $this->preisprove != '')
		{
			$this->errormsg="Ungültiger Preis eingegeben.";
			return false; 
		}
		if(!is_numeric($this->mwst))
		{
			$this->errormsg="Ungültige MWSt. eingegeben.";
			return false; 
		}
		if($this->menge!='' && !fmod($this->menge,1)==0)
		{
			$this->errormsg = 'Menge muss eine ganze Zahl sein';
			return false;
		}
		return true; 		
	}
	
	/**
	 * 
	 * Speichert den aktuellen Datensatz in die Datenbank
	 * Updatet einen bereits vorhandenen
	 */
	public function save()
	{
		if(!$this->validate())
			return false; 
			
		$mwst = ($this->mwst == '' ? '0':$this->mwst); 
		$this->mwst = $mwst; 
		if($this->new)
		{
			// insert
			$qry ='BEGIN; INSERT INTO wawi.tbl_bestelldetail (bestellung_id, position, menge, verpackungseinheit, beschreibung, artikelnummer, 
			preisprove, mwst, erhalten, sort, text, insertamum, insertvon, updateamum, updatevon) VALUES ('.
			$this->db_add_param($this->bestellung_id).', '.
			$this->db_add_param($this->position).', '.
			$this->db_add_param($this->menge).', '.
			$this->db_add_param($this->verpackungseinheit).', '.
			$this->db_add_param($this->beschreibung).', '.
			$this->db_add_param($this->artikelnummer).', '.
			$this->db_add_param($this->preisprove).', '.
			$this->db_add_param($mwst).', 
			false, '.
			$this->db_add_param($this->sort).',  
			false , '.
			$this->db_add_param($this->insertamum).', '.
			$this->db_add_param($this->insertvon).', '.
			$this->db_add_param($this->updateamum).', '.
			$this->db_add_param($this->updatevon).'); ';
			
		}
		else
		{
			// Update
			$qry = 'UPDATE wawi.tbl_bestelldetail SET 
			bestellung_id = '.$this->db_add_param($this->bestellung_id, FHC_INTEGER).',
			position = '.$this->db_add_param($this->position).',
			menge = '.$this->db_add_param($this->menge).',
			verpackungseinheit = '.$this->db_add_param($this->verpackungseinheit).',
			beschreibung = '.$this->db_add_param($this->beschreibung).',
			artikelnummer = '.$this->db_add_param($this->artikelnummer).',
			preisprove = '.$this->db_add_param($this->preisprove).',
			mwst = '.$this->db_add_param($mwst).',
			erhalten = '.$this->db_add_param($this->erhalten, FHC_BOOLEAN).',
			sort = '.$this->db_add_param($this->sort).',
			text = '.$this->db_add_param($this->text, FHC_BOOLEAN).',
			updateamum = '.$this->db_add_param($this->updateamum).',
			updatevon = '.$this->db_add_param($this->updatevon).' WHERE bestelldetail_id = '.$this->db_add_param($this->bestelldetail_id, FHC_INTEGER).';'; 
		}
		
		if($this->db_query($qry))
		{
			if($this->new)
			{
				//aktuelle Sequence holen
				$qry="SELECT currval('wawi.seq_bestelldetail_bestelldetail_id') as id;";
				if($this->db_query($qry))
				{
					if($row = $this->db_fetch_object())
					{
						$this->bestelldetail_id = $row->id;						
						$this->db_query('COMMIT');
					}
					else
					{
						$this->db_query('ROLLBACK');
						$this->errormsg = "Fehler beim Auslesen der Sequence";
						return false;
					}
				}
				else
				{
					$this->db_query('ROLLBACK');
					$this->errormsg = 'Fehler beim Auslesen der Sequence';
					return false;
				}
			}
		}
		else
		{
			return false;
		}
		return $this->bestelldetail_id;
	}
	
	/**
	 * 
	 * Gibt alle Details einer Bestellung zurück
	 * @param $bestell_id
	 */
	public function getAllDetailsFromBestellung($bestell_id, $filter=null)
	{
		if(!is_numeric($bestell_id))
		{
			$this->errormsg ='Keine gültige Bestell ID.';
			return false; 
		}
		$qry = "SELECT 
					* 
				FROM 
					wawi.tbl_bestelldetail
				WHERE
					bestellung_id = ".$this->db_add_param($bestell_id);
		
		if(!is_null($filter))
		{
			$qry.=" AND (lower(beschreibung) like lower('%".$this->db_escape($filter)."%') 
						 OR bestelldetail_id::text like '%".$this->db_escape($filter)."%'
						 OR artikelnummer like '%".$this->db_escape($filter)."%'
						 )";
		}
		$qry.=" ORDER BY sort, position;";
		//echo $qry;
		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$detail = new wawi_bestelldetail(); 
				
				$detail->bestelldetail_id = $row->bestelldetail_id;
				$detail->bestellung_id = $row->bestellung_id; 
				$detail->position = $row->position; 
				$detail->menge = $row->menge; 
				$detail->verpackungseinheit = $row->verpackungseinheit; 
				$detail->beschreibung = $row->beschreibung; 
				$detail->artikelnummer = $row->artikelnummer; 
				$detail->preisprove = $row->preisprove; 
				$detail->mwst = $row->mwst; 
				$detail->erhalten = $this->db_parse_bool($row->erhalten); 
				$detail->sort = $row->sort; 
				$detail->text = $this->db_parse_bool($row->text); 
				$detail->insertamum = $row->insertamum; 
				$detail->insertvon = $row->insertvon; 
				$detail->updateamum = $row->updateamum; 
				$detail->updatevon = $row->updatevon; 	
				
				$this->result[] = $detail; 
			}
			return true; 
		}
		else 
		{
			$this->errormsg = "Fehler bei der Abfrage.";
			return false;
		}
	}
}
