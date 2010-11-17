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
 * Klasse WaWi Bestellung
 */
require_once(dirname(__FILE__).'/basis_db.class.php');

class wawi_bestellung extends basis_db
{
	public $bestellung_id; 		// serial
	public $besteller_uid;		// char
	public $kostenstelle_id;	// int	
	public $konto_id;			// int
	public $firma_id;			// int
	public $lieferadresse; 		// int
	public $rechnungsadresse;	// int
	public $freigegeben; 		// bool
	public $bestell_nr;			// char
	public $titel;				// char
	public $bemerkung; 			// char
	public $liefertermin; 		// date
	public $updateamum; 		// timestamp
	public $updatevon; 			// char
	public $insertamum; 		// timestamp
	public $insertvon; 			// char
	public $ext_id;				// int 
	
	public $result = array(); 
	public $user; 
	public $new; 				// bool
	
	/**
	 * 
	 * Konstruktor 
	 * @param bestellung_id der Bestellung die geladen werden soll (Default=null)
	 */
	public function __construct($bestellung_id = null) 
	{
		parent::__construct(); 
		
		if(!is_null($bestellung_id))
			$this->load($bestellung_id);
			
	}
	
	/**
	 * 
	 * Lädt die Bestellung mit der Übergebenen ID 
	 * @param $bestellung_id der zu ladenden Bestellung
	 */
	public function load($bestellung_id)
	{
		if(!is_numeric($bestellung_id))
		{
			$this->errormsg ="Keine gültige Bestell ID.";
			return false; 
		}
		
		$qry = "SELECT * FROM wawi.tbl_bestellung WHERE bestellung_id = ".addslashes($bestellung_id);
		
		if(!$this->db_query($qry))
		{
			$this->errormsg ="Fehler bei der Datenbankabfrage.";
			return false; 
		}
		
		if($row = $this->db_fetch_object())
		{
			$this->bestellung_id = $row->bestellung_id; 
			$this->besteller_uid = $row->besteller_id; 
			$this->kostenstelle_id = $row->kostenstelle_id; 
			$this->konto_id = $row->konto_id; 
			$this->firma_id = $row->firma_id; 
			$this->lieferadresse = $row->lieferadresse; 
			$this->rechnungsadresse = $row->rechnungsadresse; 
			$this->freigegeben = $row->freigegeben; 
			$this->bestell_nr = $row->bestell_nr; 
			$this->titel = $row->titel; 
			$this->bemerkung = $row->bemerkung;
			$this->liefertermin = $row->liefertermin;
			$this->updateamum = $row->updateamum;
			$this->updatevon = $row->updatevon;
			$this->insertamum = $row->insertamum;
			$this->insertvon = $row->insertvon; 
			$this->ext_id = $row->ext_id; 
		}
		else
		{
			$this->errormsg ="Fehler bei der Datenbankabfrage.";
			return false; 
		}
		return true; 
	}
	
	/**
	 * 
	 * Gibt alle Bestellungen zurück
	 */
	public function getAll()
	{
		$qry = "SELECT * FROM wawi.tbl_bestellung";
		
		if(!$this->db_query($qry))
		{
			$this->errormsg = "Fehler bei der Datenbankabfrage.";
			return false; 
		}
		
		while($row = $this->db_fetch_object())
		{
			$bestellung = new wawi_bestellung(); 
			
			$bestellung->bestellung_id = $row->bestellung_id;
			$bestellung->besteller_uid = $row->besteller_id; 
			$bestellung->kostenstelle_id = $row->kostenstelle_id;
			$bestellung->konto_id = $row->konto_id; 
			$bestellung->firma_id = $row->firma_id; 
			$bestellung->lieferadresse = $row->lieferadresse; 
			$bestellung->rechnungsadresse = $row->rechnungsadresse; 
			$bestellung->freigegeben = $row->freigegeben; 
			$bestellung->bestell_nr = $row->bestell_nr;
			$bestellung->titel = $row->titel;
			$bestellung->bemerkung = $row->bemerkung; 
			$bestellung->liefertermin = $row->liefertermin; 
			$bestellung->updateamum = $row->updateamum; 
			$bestellung->updatevon = $row->updatevon;
			$bestellung->insertamum = $row->insertamum; 
			$bestellung->insertvon = $row->insertvon; 
			$bestellung->ext_id = $row->ext_id; 
			
			$this->result[] = $bestellung; 
		}
		return true; 
	}
	
	/**
	 * 
	 * Übernimmt die Parameter (Formular --> Bestellung suchen) und gibt die gefundenen Bestellungen zurück
	 * @param $bestellnr
	 * @param $titel
	 * @param $evon
	 * @param $ebis
	 * @param $bvon
	 * @param $bbis
	 * @param $firma_id
	 * @param $oe_kurzbz
	 * @param $konto_id
	 * @param $mitarbeiter_uid
	 * @param $rechnung
	 */
	public function getAllSearch($bestellnr, $titel, $evon, $ebis, $bvon, $bbis, $firma_id, $oe_kurzbz, $konto_id, $mitarbeiter_uid, $rechnung )
	{
		$first = true; 
		$qry = "SELECT distinct on (bestellung.bestellung_id) *, status.updateamum as update, bestellung.updatevon as update_von, bestellung.insertamum as insert, bestellung.insertvon as insert_von 
		FROM 
		wawi.tbl_bestellung bestellung
		
		LEFT JOIN wawi.tbl_bestellung_bestellstatus status USING (bestellung_id) 
		LEFT JOIN wawi.tbl_kostenstelle kostenstelle USING (kostenstelle_id) 
		LEFT JOIN public.tbl_organisationseinheit orgaeinheit ON (orgaeinheit.oe_kurzbz = kostenstelle.oe_kurzbz)  "; 
		
		if ($bestellnr != '')
		{
			if($first == true)
			{
				$qry.= 'where ';
				$first = false; 
			}
			else 
				$qry.= ' and ';
			$qry.= "  UPPER(bestellung.bestell_nr) LIKE UPPER('%$bestellnr%')"; 
		}
		if ($titel != '')	
		{
			if($first == true)
			{
				$qry.= 'where ';
				$first = false; 
			}
			else 
				$qry.= ' and ';
			$qry.= " UPPER(bestellung.titel)  LIKE UPPER('%$titel%')"; 
		}	
		if ($evon != '')
		{
			if($first == true)
			{
				$qry.= 'where ';
				$first = false; 
			}
			else 
				$qry.= ' and ';	
			$qry.= ' bestellung.insertamum > date('.$this->addslashes($evon).')';
		}		
		if ($ebis != '')
		{
			if($first == true)
			{
				$qry.= 'where ';
				$first = false; 
			}
			else 
				$qry.= ' and ';
			$qry.= ' bestellung.insertamum < '.$this->addslashes($ebis);
		}
		if ($bvon != '')
		{
			if($first == true)
			{
				$qry.= 'where ';
				$first = false; 
			}
			else 
				$qry.= ' and ';
			$qry.= " status.bestellstatus_kurzbz = 'Bestellung' and status.insertamum > ".$this->addslashes($bvon);
		}
		if ($bbis != '')
		{
			if($first == true)
			{
				$qry.= 'where ';
				$first = false; 
			}
			else 
				$qry.= ' and ';
			$qry.= " status.bestellstatus_kurzbz = 'Bestellung' and status.insertamum < ".$this->addslashes($bbis);
		}
		if ($firma_id != '')
		{
			if($first == true)
			{
				$qry.= 'where ';
				$first = false; 
			}
			else 
				$qry.= ' and ';
			$qry.= ' bestellung.firma_id = '.$this->addslashes($firma_id);
		}
		if ($oe_kurzbz != '')
		{
		if($first == true)
			{
				$qry.= 'where ';
				$first = false; 
			}
			else 
				$qry.= ' and ';
			$qry.= ' orgaeinheit.oe_kurzbz = '.$this->addslashes($oe_kurzbz);
		}
		if ($konto_id != '')	
		{
			if($first == true)
			{
				$qry.= 'where ';
				$first = false; 
			}
			else 
				$qry.= ' and ';
			$qry.= ' bestellung.konto_id = '.$this->addslashes($konto_id);
		}
		if ($mitarbeiter_uid != '')	
		{
			if($first == true)
			{
				$qry.= 'where ';
				$first = false; 
			}
			else 
				$qry.= ' and ';
			$qry.= ' bestellung.updatevon = '.$this->addslashes($mitarbeiter_uid);
		}
		if($rechnung)
		{
			if($first == true)
			{
				$qry.= 'where ';
				$first = false; 
			}
			else 
				$qry.= ' and ';
			$qry.= ' not exists  (Select bestellung.bestellung_id from wawi.tbl_rechnung rechnung where rechnung.bestellung_id=bestellung.bestellung_id)';
		}
		echo $qry; 
		if(!$this->db_query($qry))
		{
			$this->errormsg = "Fehler bei der Datenbankabfrage.";
			return false; 
		}
		
		while($row = $this->db_fetch_object())
		{
			$bestellung = new wawi_bestellung(); 
			
			$bestellung->bestellung_id = $row->bestellung_id;
			$bestellung->kostenstelle_id = $row->kostenstelle_id;
			$bestellung->konto_id = $row->konto_id; 
			$bestellung->firma_id = $row->firma_id; 
			$bestellung->lieferadresse = $row->lieferadresse; 
			$bestellung->rechnungsadresse = $row->rechnungsadresse; 
			$bestellung->freigegeben = $row->freigegeben; 
			$bestellung->bestell_nr = $row->bestell_nr;
			$bestellung->titel = $row->titel;
			$bestellung->bemerkung = $row->bemerkung; 
			$bestellung->liefertermin = $row->liefertermin; 
			$bestellung->updateamum = $row->update; 
			$bestellung->updatevon = $row->update_von;
			$bestellung->insertamum = $row->insert; 
			$bestellung->insertvon = $row->insert_von; 
			$bestellung->ext_id = $row->ext_id; 
			
			$this->result[] = $bestellung; 
		}
		return true; 
	}
	
	/**
	 * 
	 * Löscht die Bestellung mit der Übergebenen ID
	 * @param $bestellung_id Bestellung die gelöscht werden soll
	 */
	public function delete($bestellung_id)
	{
		if(!is_numeric($bestellung_id))
		{
			$this->errormsg = "Keine gültige Bestell ID";
			return false; 
		}		
		
		$qry ="DELETE FROM wawi.tbl_bestellung WHERE bestellung_id = ".addslashes($bestellung_id);
		
		if(!$this->db_query($qry))
		{
			$this->errormsg ="Fehler beim Löschen der Bestell ID $bestellung_id aufgetreten.";
			return false; 
		}
		return true; 
	}
	
	/**
	 * 
	 * Prüft ob Richtige Daten eingegeben/vorhanden sind
	 */
	public function validate()
	{
		
		if(!is_numeric($this->bestellung_id))
		{
			$this->errormsg = "Bestellung_id fehlerhaft.";
			return false;
		}
		if(mb_strlen($this->besteller_uid)>32)
		{
			$this->errormsg ="Besteller_uid fehlerhaft.";
			return false; 
		}
		if(!is_numeric($this->kostenstelle_id))
		{
			$this->errormsg ="Kostenstelle_id fehlerhaft.";
			return false; 
		}
		if(!is_numeric($this->konto_id))
		{
			$this->errormsg ="Konto_id fehlerhaft.";
			return false; 
		}
		if(!is_numeric($this->firma_id))
		{
			$this->errormsg ="Firma_id fehlerhaft.";
			return false; 
		}				
		if(!is_numeric($this->lieferadresse))
		{
			$this->errormsg="Lieferadresse fehlerhaft";
			return false; 
		}
		if(!is_numeric($this->rechnungsadresse))
		{
			$this->errormsg ="Rechnungsadresse fehlerhaft";
			return false;
		}
		if(mb_strlen($this->bestell_nr)>32)
		{
			$this->errormsg ="Bestell_nr zu lang.";
			return false; 
		}	
		if(mb_strlen($this->titel)>256)
		{
			$this->errormsg ="Titel zu lang.";
			return false; 
		}	
		if(mb_strlen($this->bemerkung)>256)
		{
			$this->errormsg ="Bemerkung zu lang.";
			return false; 
		}		
		return true; 		
	}
	
	/**
	 * 
	 * Speichert eine neue Besetellung in die Datenbank oder Updated eine bestehende
	 */
	public function save()
	{
		if(!$this->validate())
			return false; 
		
		if($this->new)
		{
			$qry = 'BEGIN; INSERT INTO wawi.tbl_bestellung (besteller_uid, kostenstelle_id, konto_id, firma_id, lieferadresse, rechnungsadresse, 
			freigegeben, bestell_nr, titel, bemerkung, liefertermin, updateamum, updatevon, insertamum, insertvon, ext_id) VALUES ('.
			$this->addslashes($this->besteller_uid).', '.
			$this->addslashes($this->kostenstelle_id).', '.
			$this->addslashes($this->konto_id).', '.
			$this->addslashes($this->firma_id).', '.
			$this->addslashes($this->lieferadresse).', '.
			$this->addslashes($this->rechnungsadresse).', '.
			$this->addslashes($this->freigegeben).', '.
			$this->addslashes($this->bestell_nr).', '.
			$this->addslashes($this->titel).', '.
			$this->addslashes($this->bemerkung).', '.
			$this->addslashes($this->liefertermin).', '.
			$this->addslashes($this->updateamum).', '.
			$this->addslashes($this->updatevon).', '.
			$this->addslashes($this->insertamum).', '.
			$this->addslashes($this->insertvon).', '.
			$this->addslashes($this->ext_id).')';

		}
		else
		{
			//UPDATE
			$qry = 'UPDATE wawi.tbl_bestellung SET 
			besteller_uid = '.$this->addslashes($this->besteller_uid).', 
			kostenstelle_id = '.$this->addslashes($this->kostenstelle_id).', 
			konto_id = '.$this->addslashes($this->konto_id).',
			firma_id = '.$this->addslashes($this->firma_id).',
			lieferadresse = '.$this->addslashes($this->lieferadresse).',
			rechnungsadresse = '.$this->addslashes($this->rechnungsadresse).',
			freigegeben = '.$this->addslashes($this->freigegeben).',
			bestell_nr = '.$this->addslashes($this->bestell_nr).',
			titel = '.$this->addslashes($this->titel).',
			bemerkung = '.$this->addslashes($this->bemerkung).',
			liefertermin = '.$this->addslashes($this->liefertermin).',
			updateamum = '.$this->addslashes($this->updateamum).',
			insertamum = '.$this->addslashes($this->insertamum).',
			insertvon = '.$this->addslashes($this->insertvon).',
			ext_id = '.$this->addslashes($this->ext_id).' WHERE bestellung_id = '.$this->bestellung_id.';'; 
		}
		if($this->db_query($qry))
		{
			if($this->new)
			{
				//aktuelle Sequence holen
				$qry="SELECT currval('seq_bestellung_bestellung_id') as id;";
				if($this->db_query($qry))
				{
					if($row = $this->db_fetch_object())
					{
						$this->bestellung_id = $row->id;						
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
		return $this->bestellung_id;
	}
	/**
	 * 
	 * Rechnet den Bruttopreis einer Rechnung aus
	 * @param $bestellung_id dessen Bruttopreis ausgerechnet werden soll
	 */
	public function getBrutto($bestellung_id)
	{
		$brutto = 0;
		$qry_brutto= "select sum(brutto) as brutto 
					from 
					(select detail.menge, detail.preisprove, detail.mwst, sum(detail.menge * detail.preisprove) * ((100+detail.mwst)/100) as brutto 
					from 
					wawi.tbl_bestellung as bestellung, wawi.tbl_bestelldetail as detail 
					where 
					bestellung.bestellung_id = detail.bestellung_id and bestellung.bestellung_id =".$bestellung_id." group by detail.menge, detail.preisprove, detail.mwst) as b;";
		
		if($this->db_query($qry_brutto))
		{
			if($row = $this->db_fetch_object())
			{
				$brutto = $row->brutto;						
			}
			else
			{
				return false; 
				$this->errormsg =" Fehler bei der Berechnung des Bruttobetrages.";
			}
			return $brutto; 			
		}
	}
	
}