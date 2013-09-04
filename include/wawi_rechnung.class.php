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
require_once(dirname(__FILE__).'/geschaeftsjahr.class.php');

class wawi_rechnung extends basis_db
{
	public $rechnung_id; 		// serial
	public $bestellung_id;		// int
	public $rechnungstyp_kurzbz='Rechnung';// varchar
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
		
		$qry = "SELECT * FROM wawi.tbl_rechnung WHERE rechnung_id = ".$this->db_add_param($rechnung_id, FHC_INTEGER).';';
		
		if(!$this->db_query($qry))
		{
			$this->errormsg ='Fehler bei der Datenbankabfrage';
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
			$this->freigegeben = $this->db_parse_bool($row->freigegeben);
			$this->freigegebenamum = $row->freigegebenamum;
			$this->freigegebenvon = $row->freigegebenvon;
			$this->updateamum = $row->updateamum;
			$this->updatevon = $row->updatevon;
			$this->insertamum = $row->insertamum;
			$this->insertvon = $row->insertvon;
		}
		else
		{
			$this->errormsg ='Fehler bei der Datenbankabfrage';
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
	 * @param $betrag
	 * @param $zahlungstyp
	 */
	public function getAllSearch($rechnungsnr, $rechnungsdatum_von, $rechnungsdatum_bis, $buchungsdatum_von, $buchungsdatum_bis, $erstelldatum_von, $erstelldatum_bis, $bestelldatum_von, $bestelldatum_bis, $bestellnummer, $firma_id, $oe_kurzbz, $konto_id, $kostenstelle_id, $betrag, $zahlungstyp='', $ohneTransferdatum=false)
	{
		$first = true; 
		$qry = "
		SELECT 
			distinct on (tbl_rechnung.rechnung_id) tbl_rechnung.*, tbl_bestellung.bestell_nr
		FROM 
			wawi.tbl_rechnung
			LEFT JOIN wawi.tbl_bestellung USING (bestellung_id) 
			LEFT JOIN wawi.tbl_kostenstelle USING (kostenstelle_id)
			LEFT JOIN wawi.tbl_bestellung_bestellstatus status USING(bestellung_id) 
		WHERE 1=1 
		"; 
		
		if ($rechnungsnr!='')
			$qry.= " AND UPPER(tbl_rechnung.rechnungsnr) LIKE UPPER('%".$this->db_escape($rechnungsnr)."%')"; 
			
		if ($rechnungsdatum_von != '')
			$qry.= ' AND tbl_rechnung.rechnungsdatum >= '.$this->db_add_param($rechnungsdatum_von);
		
		if ($rechnungsdatum_bis != '')
			$qry.= ' AND tbl_rechnung.rechnungsdatum <= '.$this->db_add_param($rechnungsdatum_bis);

		if ($buchungsdatum_von != '')
			$qry.= ' AND tbl_rechnung.buchungsdatum >= '.$this->db_add_param($buchungsdatum_von);
			
		if ($buchungsdatum_bis != '')
			$qry.= ' AND tbl_rechnung.buchungsdatum <= '.$this->db_add_param($buchungsdatum_bis);
			
		if ($erstelldatum_von != '')
			$qry.= ' AND tbl_bestellung.insertamum::date >= '.$this->db_add_param($erstelldatum_von);
			
		if ($erstelldatum_bis != '')
			$qry.= ' AND tbl_bestellung.insertamum::date <= '.$this->db_add_param($erstelldatum_bis);
		
		if ($bestelldatum_von != '')
			$qry.= " AND status.bestellstatus_kurzbz = 'Bestellung' AND status.datum >= ".$this->db_add_param($bestelldatum_von);
		if ($bestelldatum_bis != '')
			$qry.= " AND status.bestellstatus_kurzbz = 'Bestellung' AND status.datum <= ".$this->db_add_param($bestelldatum_bis);

		if ($firma_id != '')
			$qry.= ' AND tbl_bestellung.firma_id = '.$this->db_add_param($firma_id);

		if ($oe_kurzbz != '')
			$qry.= ' AND tbl_kostenstelle.oe_kurzbz = '.$this->db_add_param($oe_kurzbz);

		if ($konto_id != '')	
			$qry.= ' AND tbl_bestellung.konto_id = '.$this->db_add_param($konto_id);
		
		if ($kostenstelle_id != '')	
			$qry.= ' AND tbl_bestellung.kostenstelle_id = '.$this->db_add_param($kostenstelle_id);

		if ($bestellnummer != '')	
//			$qry.= ' AND UPPER(tbl_bestellung.bestell_nr) = UPPER('.$this->db_add_param($bestellnummer).')';
//			$qry.= " AND (UPPER(bestellung.bestell_nr) LIKE UPPER('%".$this->db_escape($bestellnr)."%') OR UPPER(betriebsmittel.inventarnummer) LIKE UPPER('%".$this->db_escape($bestellnr)."%'))"; 
			$qry.= " AND UPPER(tbl_bestellung.bestell_nr) LIKE UPPER('%".$this->db_escape($bestellnummer)."%')";
		
		if ($betrag != '')
			$qry.= ' AND ('.$this->db_add_param($betrag).' = (SELECT sum(betrag) FROM wawi.tbl_rechnungsbetrag WHERE rechnung_id=tbl_rechnung.rechnung_id)
					   OR '.$this->db_add_param($betrag).' = (SELECT sum((betrag*(mwst+100)/100)) FROM wawi.tbl_rechnungsbetrag WHERE rechnung_id=tbl_rechnung.rechnung_id))';
			
		if($zahlungstyp!='')
			$qry.= ' AND tbl_bestellung.zahlungstyp_kurzbz = '.$this->db_add_param($zahlungstyp);
			
		if($ohneTransferdatum)
			$qry.= ' AND tbl_rechnung.transfer_datum IS NULL';
			
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
			$obj->freigegeben = $this->db_parse_bool($row->freigegeben);
			$obj->freigegebenamum = $row->freigegebenamum;
			$obj->freigegebenvon = $row->freigegebenvon;
			$obj->updateamum = $row->updateamum;
			$obj->updatevon = $row->updatevon;
			$obj->insertamum = $row->insertamum;
			$obj->insertvon = $row->insertvon;
			
			$obj->bestell_nr = $row->bestell_nr;
			
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
		
		$qry ="DELETE FROM wawi.tbl_rechnung WHERE rechnung_id = ".$this->db_add_param($rechnung_id, FHC_INTEGER).';';
		
		if(!$this->db_query($qry))
		{
			$this->errormsg ="Fehler beim Löschen der Rechnung";
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
			rechnungsnr, rechnungsdatum, transfer_datum, buchungstext, freigegeben, freigegebenvon, freigegebenamum,
			updateamum, updatevon, insertamum, insertvon) VALUES ('.
			$this->db_add_param($this->bestellung_id, FHC_INTEGER).', '.
			$this->db_add_param($this->rechnungstyp_kurzbz).', '.
			$this->db_add_param($this->buchungsdatum).', '.
			$this->db_add_param($this->rechnungsnr).', '.
			$this->db_add_param($this->rechnungsdatum).', '.
			$this->db_add_param($this->transfer_datum).', '.
			$this->db_add_param($this->buchungstext).', '.
			$this->db_add_param($this->freigegeben, FHC_BOOLEAN).','.
			$this->db_add_param($this->freigegebenvon).', '.
			$this->db_add_param($this->freigegebenamum).', '.
			$this->db_add_param($this->updateamum).', '.
			$this->db_add_param($this->updatevon).', '.
			$this->db_add_param($this->insertamum).', '.
			$this->db_add_param($this->insertvon).'); ';
		}
		else
		{
			//UPDATE
			$qry = 'UPDATE wawi.tbl_rechnung SET 
			bestellung_id = '.$this->db_add_param($this->bestellung_id).', 
			rechnungstyp_kurzbz = '.$this->db_add_param($this->rechnungstyp_kurzbz).', 
			buchungsdatum = '.$this->db_add_param($this->buchungsdatum).',
			rechnungsnr = '.$this->db_add_param($this->rechnungsnr).',
			rechnungsdatum = '.$this->db_add_param($this->rechnungsdatum).',
			transfer_datum = '.$this->db_add_param($this->transfer_datum).',
			buchungstext = '.$this->db_add_param($this->buchungstext).',
			freigegeben = '.$this->db_add_param($this->freigegeben, FHC_BOOLEAN).',
			freigegebenvon = '.$this->db_add_param($this->freigegebenvon).',
			freigegebenamum = '.$this->db_add_param($this->freigegebenamum).',
			updateamum = '.$this->db_add_param($this->updateamum).',
			updatevon = '.$this->db_add_param($this->updatevon).
			' WHERE rechnung_id = '.$this->db_add_param($this->rechnung_id, FHC_INTEGER).';'; 
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
						if($this->rechnungsnr=='')
							$this->rechnungsnr = $row->id;						
						$this->db_query('COMMIT;');
					}
					else
					{
						$this->db_query('ROLLBACK;');
						$this->errormsg = "Fehler beim Auslesen der Sequence";
						return false;
					}
				}
				else
				{
					$this->db_query('ROLLBACK;');
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
	
	/**
	 * Laedt die Betraege zu einer Rechnung
	 * 
	 * @param $rechnung_id ID der Rechnung
	 */
	public function loadBetraege($rechnung_id)
	{
		if(!is_numeric($rechnung_id))
		{
			$this->errormsg='Ungueltige ID';
			return false;
		}
		
		$qry = "SELECT * FROM wawi.tbl_rechnungsbetrag WHERE rechnung_id=".$this->db_add_param($rechnung_id, FHC_INTEGER)." ORDER BY rechnungsbetrag_id;";
		
		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				$obj = new wawi_rechnung();
				
				$obj->rechnungsbetrag_id=$row->rechnungsbetrag_id;
				$obj->rechnung_id = $row->rechnung_id;
				$obj->betrag = $row->betrag;
				$obj->mwst = $row->mwst;
				$obj->bezeichnung = $row->bezeichnung;
				
				$this->result[] = $obj;
			}
			return true;
		}
		else
		{
			$this->errorlog='Fehler beim Laden der Daten';
			return false;
		}
	}
	
	/**
	 * 
	 * Speichert einen Rechnungsbetrag
	 */
	public function save_betrag()
	{
		if(!is_numeric($this->betrag))
		{
			$this->errormsg = "Ungültiger Betrag.";
			return false; 
		}
		
		if(!is_numeric($this->mwst))
		{
			$this->errormsg = "Ungültiger Betrag."; 
			return false; 
		}
		
		if($this->new)
		{
			$qry = 'BEGIN;INSERT INTO wawi.tbl_rechnungsbetrag(rechnung_id, mwst, betrag, bezeichnung) VALUES('.
					$this->db_add_param($this->rechnung_id, FHC_INTEGER).','.
					$this->db_add_param($this->mwst).','.
					$this->db_add_param($this->betrag).','.
					$this->db_add_param($this->bezeichnung).');';
		}
		else
		{
			$qry = 'UPDATE wawi.tbl_rechnungsbetrag SET'.
					' rechnung_id='.$this->db_add_param($this->rechnung_id, FHC_INTEGER).','.
				 	' mwst='.$this->db_add_param($this->mwst).','.
					' betrag='.$this->db_add_param($this->betrag).','.
					' bezeichnung='.$this->db_add_param($this->bezeichnung).
					" WHERE rechnungsbetrag_id=".$this->db_add_param($this->rechnungsbetrag_id, FHC_INTEGER).';';
		}
		
		if($this->db_query($qry))
		{
			if($this->new)
			{
				$qry = "SELECT currval('wawi.seq_rechnungsbetrag_rechnungsbetrag_id') as id;";
				
				if($result = $this->db_query($qry))
				{
					if($row = $this->db_fetch_object($result))
					{
						$this->rechnugnsbetrag_id=$row->id;
						$this->db_query('COMMIT;');
						return true; 
					}
					else
					{
						$this->db_query('ROLLBACK;');
						$this->errormsg = 'Fehler beim Auslesen der Sequence';
						return false;
					}
				}
				else
				{
					 $this->errormsg = 'Fehler beim Auslesen der Sequence';
					 return false;
				}
			}
		}
		else 
		{
			$this->errormsg = 'Fehler beim Speichern der Daten';
			return false;
		}
	}
	
	/**
	 * Loescht einen Eintrag aus der Tabelle rechnungsbetrag
	 *
	 * @param $rechnungsbetrag_id
	 */
	public function delete_betrag($rechnungsbetrag_id)
	{
		if(!is_numeric($rechnungsbetrag_id) || $rechnungsbetrag_id=='')
		{
			$this->errormsg = 'ungueltige ID';
			return false;
		}
		
		$qry = "DELETE FROM wawi.tbl_rechnungsbetrag WHERE rechnungsbetrag_id=".$this->db_add_param($rechnungsbetrag_id, FHC_INTEGER).';';
		if($this->db_query($qry))
		{
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Löschen der Daten';
			return false;
		}
	}
	
	/**
	 * Laedt alle Rechnungstypen
	 */
	public function getRechnungstyp()
	{
		$qry = 'SELECT * FROM wawi.tbl_rechnungstyp ORDER BY beschreibung;';
		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				$obj = new wawi_rechnung();
				$obj->rechnungstyp_kurzbz = $row->rechnungstyp_kurzbz;
				$obj->beschreibung = $row->beschreibung;

				$this->result[] = $obj;
			}
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Daten';
			return false;
		}	
	}
	
	/**
	 * Liefert die Anzahl der Rechnungen zu einer Bestellung
	 *
	 * @param $bestellung_id
	 * @return Anzahl der Rechnungen oder false im Fehlerfall
	 */
	public function count($bestellung_id)
	{
		$qry = "SELECT count(*) as anzahl FROM wawi.tbl_rechnung 
		WHERE bestellung_id=".$this->db_add_param($bestellung_id, FHC_INTEGER).';';
		
		if($result = $this->db_query($qry))
		{
			if($row = $this->db_fetch_object($result))
			{
				return $row->anzahl;
			}
			else
			{
				$this->errormsg='Fehler beim Laden der Daten';
				return false;
			}
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Daten';
			return false;
		}
	}
	
	/**
	 * Liefert den gesamten Bruttobetrag von einer Rechnung
	 * 
	 * @param $rechnung_id
	 */
	public function getBrutto($rechnung_id)
	{
		$this->loadBetraege($rechnung_id);
		$brutto=0;
		foreach($this->result as $row)
		{
			$brutto += ($row->betrag*($row->mwst+100)/100);
		}
		return $brutto;
	}
	
	/**
	 * Liefert die Summe der Brutto Rechnungsbeträge einer Kostenstelle in einem Geschäftsjahr
	 *
	 * @param $geschaeftsjahr_kurzbz
	 * @param $kostenstelle_id
	 */
	public function getAusgaben($geschaeftsjahr_kurzbz, $kostenstelle_id)
	{
		if(!is_numeric($kostenstelle_id))
		{
			$this->errormsg = 'KostenstelleID ist ungueltig';
			return false;
		}
		
		$gj = new geschaeftsjahr();
		if(!$gj->load($geschaeftsjahr_kurzbz))
		{
			$this->errormsg = 'Fehler beim Laden des Geschaeftsjahres';
			return false;
		}
		
		$qry = "SELECT sum(brutto) as gesamt
				FROM 
				(
				SELECT 
					(tbl_rechnungsbetrag.betrag*(tbl_rechnungsbetrag.mwst+100)/100) as brutto
				FROM 
					wawi.tbl_rechnung 
					JOIN wawi.tbl_bestellung USING(bestellung_id)
					JOIN wawi.tbl_rechnungsbetrag USING(rechnung_id)
				WHERE
					kostenstelle_id=".$this->db_add_param($kostenstelle_id, FHC_INTEGER)."
					AND tbl_bestellung.insertamum>=".$this->db_add_param($gj->start)."
					AND tbl_bestellung.insertamum<=".$this->db_add_param($gj->ende)."
				) as a;";
		
		if($result = $this->db_query($qry))
		{
			if($row = $this->db_fetch_object($result))
			{
				return $row->gesamt;
			}
			else
			{
				$this->errormsg = 'Fehler beim Berechnen der Daten';
				return false;
			}
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Daten';
			return false;
		}
	}
}