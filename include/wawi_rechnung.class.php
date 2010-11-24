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
 * Klasse WaWi Rechnung
 */
require_once(dirname(__FILE__).'/basis_db.class.php');

class wawi_rechnung extends basis_db
{
	public $rechnung_id; 		// serial
	public $bestellung_id;		// int
	public $rechnungstyp_kurzbz;// varchar
	public $buchungsdatum;		// date
	public $rechnungsnr;		// varchar
	public $rechnungsdatum;		// date
	public $transfer_datum;		// date
	public $buchungstext;		// text
	public $freigegeben; 		// boolean
	public $freigegebenamum;	// timestamp
	public $freigegebenvon;		// varchar
	public $updateamum; 		// timestamp
	public $updatevon; 			// varchar
	public $insertamum; 		// timestamp
	public $insertvon; 			// varchar
		
	public $result = array(); 
	public $new; 				// bool
	
	/**
	 * 
	 * Konstruktor 
	 * @param rechnung_id der Rechnung die geladen werden soll (Default=null)
	 */
	public function __construct($rechnung_id = null) 
	{
		parent::__construct(); 
		
		if(!is_null($rechnung_id))
			$this->load($rechnung_id);			
	}
	
	/**
	 * 
	 * Lädt die Rechnung mit der Übergebenen ID 
	 * @param $rechnung_id der zu ladenden Rechnung
	 */
	public function load($rechnung_id)
	{
		if(!is_numeric($rechnung_id))
		{
			$this->errormsg='ID ist ungueltig';
			return false; 
		}
		
		$qry = "SELECT * FROM wawi.tbl_rechnung WHERE rechnung_id = '".addslashes($rechnung_id)."'";
		
		if(!$this->db_query($qry))
		{
			$this->errormsg ="Fehler bei der Datenbankabfrage.";
			return false; 
		}
		
		if($row = $this->db_fetch_object())
		{
			$this->rechnung_id = $row->rechnung_id; 
			$this->bestellung_id = $row->bestellung_id;
			$this->rechnungstyp_kurzbz = $row->rechnungstyp_kurzbz;
			$this->buchungsdatum = $row->buchungsdatum;
			$this->rechnungsnr = $row->rechnungsnr;
			$this->rechnungsdatum = $row->rechnungsdatum;
			$this->transfer_datum = $row->transfer_datum;
			$this->buchungstext = $row->buchungstext;
			$this->freigegeben = ($row->freigegeben=='t'?true:false);
			$this->freigegebenamum = $row->freigegebenamum;
			$this->freigegebenvon = $row->freigegebenvon;
			$this->updateamum = $row->updateamum;
			$this->updatevon = $row->updatevon;
			$this->insertamum = $row->insertamum;
			$this->insertvon = $row->insertvon;
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
	 * Laedt alle Rechnung anhand von verschiedenen Parametern
	 * 
	 * @param $rechnungsnr
	 * @param $rechnungsdatum_von
	 * @param $rechnungsdatum_bis
	 * @param $buchungsdatum_von
	 * @param $buchungsdatum_bis
	 * @param $erstelldatum_von
	 * @param $erstelldatum_bis
	 * @param $bestelldatum_von
	 * @param $bestelldatum_bis
	 * @param $buchungsnummer
	 * @param $firma_id
	 * @param $oe_kurzbz
	 * @param $konto_id
	 * @param $kostenstelle_id
	 */
	public function getAllSearch($rechnungsnr, $rechnungsdatum_von, $rechnungsdatum_bis, $buchungsdatum_von, $buchungsdatum_bis, $erstelldatum_von, $erstelldatum_bis, $bestelldatum_von, $bestelldatum_bis, $bestellnummer, $firma_id, $oe_kurzbz, $konto_id, $kostenstelle_id)
	{
		$first = true; 
		$qry = "
		SELECT 
			distinct on (tbl_rechnung.rechnung_id) tbl_rechnung.*  
		FROM 
			wawi.tbl_rechnung
			LEFT JOIN wawi.tbl_bestellung USING (bestellung_id) 
			LEFT JOIN wawi.tbl_kostenstelle USING (kostenstelle_id)
			LEFT JOIN wawi.tbl_bestellung_bestellstatus status USING(bestellung_id) 
		WHERE 1=1 
		"; 
		
		if ($rechnungsnr!='')
			$qry.= " AND UPPER(tbl_rechnung.rechnungsnr) LIKE UPPER('%$rechnungsnr%')"; 
			
		if ($rechnungsdatum_von != '')
			$qry.= ' AND tbl_rechnung.rechnungsdatum >= '.$this->addslashes($rechnungsdatum_von);
		
		if ($rechnungsdatum_bis != '')
			$qry.= ' AND tbl_rechnung.rechnungsdatum <= '.$this->addslashes($rechnungsdatum_bis);

		if ($buchungsdatum_von != '')
			$qry.= ' AND tbl_rechnung.buchungsdatum >= '.$this->addslashes($buchungsdatum_von);
			
		if ($buchungsdatum_bis != '')
			$qry.= ' AND tbl_rechnung.buchungsdatum <= '.$this->addslashes($buchungsdatum_bis);
			
		if ($erstelldatum_von != '')
			$qry.= ' AND tbl_bestellung.insertamum >= '.$this->addslashes($erstelldatum_von);
			
		if ($erstelldatum_bis != '')
			$qry.= ' AND tbl_bestellung.insertamum <= '.$this->addslashes($erstelldatum_bis);
		
		if ($bestelldatum_von != '')
			$qry.= " AND status.bestellstatus_kurzbz = 'Bestellung' AND status.datum >= ".$this->addslashes($bestelldatum_von);
		if ($bestelldatum_bis != '')
			$qry.= " AND status.bestellstatus_kurzbz = 'Bestellung' AND status.datum <= ".$this->addslashes($bestelldatum_bis);

		if ($firma_id != '')
			$qry.= ' AND tbl_bestellung.firma_id = '.$this->addslashes($firma_id);

		if ($oe_kurzbz != '')
			$qry.= ' AND tbl_kostenstelle.oe_kurzbz = '.$this->addslashes($oe_kurzbz);

		if ($konto_id != '')	
			$qry.= ' AND tbl_bestellung.konto_id = '.$this->addslashes($konto_id);
		
		if ($kostenstelle_id != '')	
			$qry.= ' AND tbl_bestellung.kostenstelle_id = '.$this->addslashes($kostenstelle_id);

		if ($bestellnummer != '')	
			$qry.= ' AND tbl_bestellung.bestell_nr = '.$this->addslashes($bestellnummer);
		
		$qry.=" LIMIT 1000";
		if(!$this->db_query($qry))
		{
			$this->errormsg = "Fehler bei der Datenbankabfrage.";
			return false; 
		}
		
		while($row = $this->db_fetch_object())
		{
			$obj = new wawi_rechnung(); 
			
			$obj->rechnung_id = $row->rechnung_id; 
			$obj->bestellung_id = $row->bestellung_id;
			$obj->rechnungstyp_kurzbz = $row->rechnungstyp_kurzbz;
			$obj->buchungsdatum = $row->buchungsdatum;
			$obj->rechnungsnr = $row->rechnungsnr;
			$obj->rechnungsdatum = $row->rechnungsdatum;
			$obj->transfer_datum = $row->transfer_datum;
			$obj->buchungstext = $row->buchungstext;
			$obj->freigegeben = ($row->freigegeben=='t'?true:false);
			$obj->freigegebenamum = $row->freigegebenamum;
			$obj->freigegebenvon = $row->freigegebenvon;
			$obj->updateamum = $row->updateamum;
			$obj->updatevon = $row->updatevon;
			$obj->insertamum = $row->insertamum;
			$obj->insertvon = $row->insertvon;
			
			$this->result[] = $obj; 
		}
		return true; 
	}
	
	/**
	 * 
	 * Löscht die Rechnung mit der Übergebenen ID
	 * @param $rechnung_id Rechnung die gelöscht werden soll
	 */
	public function delete($rechnung_id)
	{
		if(!is_numeric($rechnung_id))
		{
			$this->errormsg = "Keine gültige ID";
			return false; 
		}		
		
		$qry ="DELETE FROM wawi.tbl_rechnung WHERE rechnung_id = '".addslashes($rechnung_id)."'";
		
		if(!$this->db_query($qry))
		{
			$this->errormsg ="Fehler beim Löschen der Rechnung ID $rechnung_id aufgetreten.";
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
		if(mb_strlen($this->rechnungstyp_kurzbz)>32)
		{
			$this->errormsg ="Rechnungstyp fehlerhaft.";
			return false; 
		}
		if(mb_strlen($this->rechnungsnr)>32)
		{
			$this->errormsg ="Rechnungsnr fehlerhaft.";
			return false; 
		}
		if(!is_bool($this->freigegeben))
		{
			$this->errormsg = 'freigegeben ist ungueltig';
			return false;
		}
		if(mb_strlen($this->freigegebenvon)>32)
		{
			$this->errormsg ="freigegebenvon zu lang.";
			return false; 
		}	
		if(mb_strlen($this->insertvon)>32)
		{
			$this->errormsg ="insertvon zu lang.";
			return false; 
		}	
		if(mb_strlen($this->updatevon)>32)
		{
			$this->errormsg ="updatevon zu lang.";
			return false; 
		}			
		return true; 		
	}
	
	/**
	 * 
	 * Speichert eine neue Rechnung in die Datenbank oder Updated eine bestehende
	 */
	public function save()
	{
		if(!$this->validate())
			return false;
		
		if($this->new)
		{
			$qry = 'BEGIN; INSERT INTO wawi.tbl_rechnung (bestellung_id,rechnungstyp_kurzbz, buchungsdatum, 
			rechnungsnr, rechnugsdatum, transfer_datum, buchungstext, freigegeben, freigegebenvon, freigegebenamum,
			updateamum, updatevon, insertamum, insertvon) VALUES ('.
			$this->addslashes($this->bestellung_id).', '.
			$this->addslashes($this->rechnungstyp_kurzbz).', '.
			$this->addslashes($this->buchungsdatum).', '.
			"currval('wawi.seq_rechnung_rechnung_id'), ".
			$this->addslashes($this->rechnungsdatum).', '.
			$this->addslashes($this->transfer_datum).", ".
			$this->addslashes($this->buchungstext).', '.
			($this->freigegeben?'true':'false').','.
			$this->addslashes($this->freigegebenvon).', '.
			$this->addslashes($this->freigegebenamum).', '.
			$this->addslashes($this->updateamum).', '.
			$this->addslashes($this->updatevon).', '.
			$this->addslashes($this->insertamum).', '.
			$this->addslashes($this->insertvon).'); ';
		}
		else
		{
			//UPDATE
			$qry = 'UPDATE wawi.tbl_rechnung SET 
			bestellung_id = '.$this->addslashes($this->bestellung_id).', 
			rechnungstyp_kurzbz = '.$this->addslashes($this->rechnungstyp_kurzbz).', 
			buchungsdatum = '.$this->addslashes($this->buchungsdatum).',
			rechnungsnr = '.$this->addslashes($this->rechnungsnr).',
			rechnungsdatum = '.$this->addslashes($this->rechnungsdatum).',
			transfer_datum = '.$this->addslashes($this->transfer_datum).',
			buchungstext = '.$this->addslashes($this->buchungstext).',
			freigegeben = '.($this->freigegeben?'true':'false').',
			freigegebenvon = '.$this->addslashes($this->freigegebenvon).',
			freigegebenamum = '.$this->addslashes($this->freigegebenamum).',
			updateamum = '.$this->addslashes($this->updateamum).',
			updatevon = '.$this->addslashes($this->updatevon).
			' WHERE rechnung_id = '.$this->rechnung_id.';'; 
		}
		
		if($this->db_query($qry))
		{
			if($this->new)
			{
				//aktuelle Sequence holen
				$qry="SELECT currval('wawi.seq_rechnung_rechnung_id') as id;";
				if($this->db_query($qry))
				{
					if($row = $this->db_fetch_object())
					{
						$this->rechnung_id = $row->id;
						$this->rechnungsnr = $row->rechnungsnr;						
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
		return $this->rechnung_id;
	}
}