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
require_once (dirname(__FILE__).'/wawi_bestelldetail.class.php');
require_once (dirname(__FILE__).'/wawi_aufteilung.class.php');
require_once (dirname(__FILE__).'/organisationseinheit.class.php');
require_once (dirname(__FILE__).'/wawi_kostenstelle.class.php');
require_once (dirname(__FILE__).'/geschaeftsjahr.class.php');

class wawi_bestellung extends basis_db
{
	public $bestellung_id; 		// serial
	public $besteller_uid;		// char
	public $kostenstelle_id;	// int
	public $konto_id;			// int
	public $firma_id;			// int
	public $lieferadresse; 		// int
	public $rechnungsadresse;	// int
	public $freigegeben=false;	// bool
	public $bestell_nr;			// char
	public $titel;				// char
	public $bemerkung; 			// char
	public $liefertermin; 		// date
	public $updateamum; 		// timestamp
	public $updatevon; 			// char
	public $insertamum; 		// timestamp
	public $insertvon; 			// char
	public $ext_id;				// int
	public $zahlungstyp_kurzbz; // varchar

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

		$qry = "SELECT * FROM wawi.tbl_bestellung WHERE bestellung_id = ".$this->db_add_param($bestellung_id, FHC_INTEGER);

		if(!$this->db_query($qry))
		{
			$this->errormsg ="Fehler bei der Datenbankabfrage.";
			return false;
		}

		if($row = $this->db_fetch_object())
		{
			$this->bestellung_id = $row->bestellung_id;
			$this->besteller_uid = $row->besteller_uid;
			$this->kostenstelle_id = $row->kostenstelle_id;
			$this->konto_id = $row->konto_id;
			$this->firma_id = $row->firma_id;
			$this->lieferadresse = $row->lieferadresse;
			$this->rechnungsadresse = $row->rechnungsadresse;
			$this->freigegeben = $this->db_parse_bool($row->freigegeben);
			$this->bestell_nr = $row->bestell_nr;
			$this->titel = $row->titel;
			$this->bemerkung = $row->bemerkung;
			$this->liefertermin = $row->liefertermin;
			$this->updateamum = $row->updateamum;
			$this->updatevon = $row->updatevon;
			$this->insertamum = $row->insertamum;
			$this->insertvon = $row->insertvon;
			$this->ext_id = $row->ext_id;
			$this->zahlungstyp_kurzbz = $row->zahlungstyp_kurzbz;
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
			$bestellung->freigegeben = $this->db_parse_bool($row->freigegeben);
			$bestellung->bestell_nr = $row->bestell_nr;
			$bestellung->titel = $row->titel;
			$bestellung->bemerkung = $row->bemerkung;
			$bestellung->liefertermin = $row->liefertermin;
			$bestellung->updateamum = $row->updateamum;
			$bestellung->updatevon = $row->updatevon;
			$bestellung->insertamum = $row->insertamum;
			$bestellung->insertvon = $row->insertvon;
			$bestellung->ext_id = $row->ext_id;
			$bestellung->zahlungstyp_kurzbz = $row->zahlungstyp_kurzbz;

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
	 * @param $filter_firma
	 * @param $kostenstelle_id
	 * @param $tag
	 * @param $zahlungstyp
	 * @param $tagNotExists
	 * @param $bestellposition
	 */
	public function getAllSearch($bestellnr, $titel, $evon, $ebis, $bvon, $bbis, $firma_id, $oe_kurzbz, $konto_id, $mitarbeiter_uid, $rechnung, $filter_firma, $kostenstelle_id=null, $tag=null, $zahlungstyp=null, $tagNotExists=false, $bestellposition=null,$ohneFreigabe=false)
	{
		$first = true;
		$qry = "SELECT distinct on (bestellung.bestellung_id) *, bestellung.updateamum as update, bestellung.updatevon as update_von, bestellung.insertamum as insert, bestellung.insertvon as insert_von, betriebsmittel.inventarnummer
		FROM
		wawi.tbl_bestellung bestellung
		LEFT JOIN wawi.tbl_bestellung_bestellstatus status USING (bestellung_id)
		LEFT JOIN wawi.tbl_kostenstelle kostenstelle USING (kostenstelle_id)
		LEFT JOIN wawi.tbl_bestellungtag bestelltag USING (bestellung_id)
		LEFT JOIN wawi.tbl_bestelldetail detail USING (bestellung_id)
		LEFT JOIN wawi.tbl_betriebsmittel betriebsmittel USING(bestellung_id)
		LEFT JOIN public.tbl_organisationseinheit orgaeinheit ON (orgaeinheit.oe_kurzbz = kostenstelle.oe_kurzbz)
		WHERE 1=1 ";

		// Bestellnummer und Inventarnummer werden durchsucht
		if ($bestellnr != '')
			$qry.= " AND (UPPER(bestellung.bestell_nr) LIKE UPPER('%".$this->db_escape($bestellnr)."%') OR UPPER(betriebsmittel.inventarnummer) LIKE UPPER('%".$this->db_escape($bestellnr)."%'))";
		else
		{
			if ($titel != '')
				$qry.= " AND UPPER(bestellung.titel) LIKE UPPER('%".$this->db_escape($titel)."%')";

			if ($evon != '')
				$qry.= ' AND bestellung.insertamum::date >= date('.$this->db_add_param($evon).')';

			if ($ebis != '')
				$qry.= ' AND bestellung.insertamum::date <= '.$this->db_add_param($ebis);

			if ($bvon != '')
				$qry.= " AND status.bestellstatus_kurzbz = 'Bestellung' and status.datum > ".$this->db_add_param($bvon);

			if ($bbis != '')
				$qry.= " AND status.bestellstatus_kurzbz = 'Bestellung' and status.datum < ".$this->db_add_param($bbis);

			if ($firma_id != '')
				$qry.= ' AND bestellung.firma_id = '.$this->db_add_param($firma_id, FHC_INTEGER);

			if ($filter_firma != '')
				$qry.= ' AND bestellung.firma_id = '.$this->db_add_param($filter_firma);

			if ($oe_kurzbz != '')
				$qry.= ' AND orgaeinheit.oe_kurzbz = '.$this->db_add_param($oe_kurzbz);

			if ($konto_id != '')
				$qry.= ' AND bestellung.konto_id = '.$this->db_add_param($konto_id, FHC_INTEGER);

			if ($mitarbeiter_uid != '')
				$qry.= ' AND ( bestellung.updatevon = '.$this->db_add_param($mitarbeiter_uid).' OR bestellung.insertvon = '.$this->db_add_param($mitarbeiter_uid)
				.' OR bestellung.besteller_uid = '.$this->db_add_param($mitarbeiter_uid).' )';

			if($rechnung)
				$qry.= ' AND not exists  (Select bestellung.bestellung_id from wawi.tbl_rechnung rechnung where rechnung.bestellung_id=bestellung.bestellung_id)';

			if($kostenstelle_id!='')
				$qry.= ' AND kostenstelle_id='.$this->db_add_param($kostenstelle_id, FHC_INTEGER);

			if($zahlungstyp!='')
				$qry.= ' AND bestellung.zahlungstyp_kurzbz = '.$this->db_add_param($zahlungstyp);

			if($tag!='')
				$qry.= ' AND (EXISTS (SELECT 1 FROM wawi.tbl_bestellungtag WHERE tag='.$this->db_add_param($tag).' AND bestellung_id=bestellung.bestellung_id)
							OR EXISTS (SELECT 1 FROM wawi.tbl_bestelldetailtag JOIN wawi.tbl_bestelldetail USING(bestelldetail_id) WHERE tag='.$this->db_add_param($tag).' AND bestellung_id=bestellung.bestellung_id)
							)';
			if($tagNotExists)
				$qry.=' AND (NOT EXISTS (SELECT 1 FROM wawi.tbl_bestellungtag WHERE tag is not null AND bestellung_id=bestellung.bestellung_id)
						 AND NOT EXISTS (SELECT 1 FROM wawi.tbl_bestelldetailtag JOIN wawi.tbl_bestelldetail USING(bestelldetail_id) WHERE tag is not null AND bestellung_id=bestellung.bestellung_id) )';

			if($bestellposition!='')
				$qry.=" AND EXISTS (SELECT 1 FROM wawi.tbl_bestelldetail where UPPER(beschreibung) LIKE UPPER('%".$this->db_escape($bestellposition)."%') AND bestellung_id=bestellung.bestellung_id)";

			if($ohneFreigabe)
				$qry.=" AND bestellung.freigegeben = 'false'";
		}

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
			$bestellung->freigegeben = $this->db_parse_bool($row->freigegeben);
			$bestellung->bestell_nr = $row->bestell_nr;
			$bestellung->titel = $row->titel;
			$bestellung->bemerkung = $row->bemerkung;
			$bestellung->liefertermin = $row->liefertermin;
			$bestellung->updateamum = $row->update;
			$bestellung->updatevon = $row->update_von;
			$bestellung->insertamum = $row->insert;
			$bestellung->insertvon = $row->insert_von;
			$bestellung->ext_id = $row->ext_id;
			$bestellung->zahlungstyp_kurzbz = $row->zahlungstyp_kurzbz;

			$this->result[] = $bestellung;
		}
		return true;
	}

	/**
	 *
	 * Liefert die Daten einer Bestellung
	 * @param $filter
	 */
	public function getBestellung($filter)
	{
		$qry = "SELECT
					*
				FROM
					wawi.tbl_bestellung
				WHERE
					bestellung_id::text LIKE '%".$this->db_escape($filter)."%'
					OR lower(bestell_nr) LIKE lower('%".$this->db_escape($filter)."%')";

		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				$bestellung = new wawi_bestellung();

				$bestellung->bestellung_id = $row->bestellung_id;
				$bestellung->kostenstelle_id = $row->kostenstelle_id;
				$bestellung->konto_id = $row->konto_id;
				$bestellung->firma_id = $row->firma_id;
				$bestellung->lieferadresse = $row->lieferadresse;
				$bestellung->rechnungsadresse = $row->rechnungsadresse;
				$bestellung->freigegeben = $this->db_parse_bool($row->freigegeben);
				$bestellung->bestell_nr = $row->bestell_nr;
				$bestellung->titel = $row->titel;
				$bestellung->bemerkung = $row->bemerkung;
				$bestellung->liefertermin = $row->liefertermin;
				$bestellung->updateamum = $row->updateamum;
				$bestellung->updatevon = $row->updatevon;
				$bestellung->insertamum = $row->insertamum;
				$bestellung->insertvon = $row->insertvon;
				$bestellung->ext_id = $row->ext_id;
				$bestellung->zahlungstyp_kurzbz = $row->zahlungstyp_kurzbz;

				$this->result[] = $bestellung;
			}
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Daten';
			return false;
		}
	}


	/**
	 *
	 * Liefert die Bestellungen zu einem Projekt
	 * @param $projekt_kurzbz
	 */
	public function getBestellungProjekt($projekt_kurzbz)
	{
		$qry = "SELECT
					*
				FROM
					wawi.tbl_bestellung
					JOIN wawi.tbl_projekt_bestellung USING (bestellung_id)
				WHERE projekt_kurzbz=".$this->db_add_param($projekt_kurzbz).";";

		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				$bestellung = new wawi_bestellung();

				$bestellung->bestellung_id = $row->bestellung_id;
				$bestellung->kostenstelle_id = $row->kostenstelle_id;
				$bestellung->konto_id = $row->konto_id;
				$bestellung->firma_id = $row->firma_id;
				$bestellung->lieferadresse = $row->lieferadresse;
				$bestellung->rechnungsadresse = $row->rechnungsadresse;
				$bestellung->freigegeben = $this->db_parse_bool($row->freigegeben);
				$bestellung->bestell_nr = $row->bestell_nr;
				$bestellung->titel = $row->titel;
				$bestellung->bemerkung = $row->bemerkung;
				$bestellung->liefertermin = $row->liefertermin;
				$bestellung->updateamum = $row->updateamum;
				$bestellung->updatevon = $row->updatevon;
				$bestellung->insertamum = $row->insertamum;
				$bestellung->insertvon = $row->insertvon;
				$bestellung->ext_id = $row->ext_id;
				$bestellung->zahlungstyp_kurzbz = $row->zahlungstyp_kurzbz;

				$this->result[] = $bestellung;
			}
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Daten';
			return false;
		}
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
			$this->errormsg = 'Keine gültige Bestell ID';
			return false;
		}

		$qry ="DELETE FROM wawi.tbl_bestellung WHERE bestellung_id = ".$this->db_add_param($bestellung_id, FHC_INTEGER);

		if(!$this->db_query($qry))
		{
			$this->errormsg ="Fehler beim Löschen der Bestell aufgetreten.";
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
			freigegeben, bestell_nr, titel, bemerkung, liefertermin, updateamum, updatevon, insertamum, insertvon, zahlungstyp_kurzbz) VALUES ('.
			$this->db_add_param($this->besteller_uid).', '.
			$this->db_add_param($this->kostenstelle_id, FHC_INTEGER).', '.
			$this->db_add_param($this->konto_id, FHC_INTEGER).', '.
			$this->db_add_param($this->firma_id, FHC_INTEGER).', '.
			$this->db_add_param($this->lieferadresse).', '.
			$this->db_add_param($this->rechnungsadresse).', '.
			$this->db_add_param($this->freigegeben, FHC_BOOLEAN).','.
			$this->db_add_param($this->bestell_nr).', '.
			$this->db_add_param($this->titel).', '.
			$this->db_add_param($this->bemerkung).', '.
			$this->db_add_param($this->liefertermin).', '.
			$this->db_add_param($this->updateamum).', '.
			$this->db_add_param($this->updatevon).', '.
			$this->db_add_param($this->insertamum).', '.
			$this->db_add_param($this->insertvon).', '.
			$this->db_add_param($this->zahlungstyp_kurzbz).')';

		}
		else
		{
			//UPDATE
			$qry = 'UPDATE wawi.tbl_bestellung SET
			besteller_uid = '.$this->db_add_param($this->besteller_uid).',
			kostenstelle_id = '.$this->db_add_param($this->kostenstelle_id, FHC_INTEGER).',
			konto_id = '.$this->db_add_param($this->konto_id, FHC_INTEGER).',
			firma_id = '.$this->db_add_param($this->firma_id, FHC_INTEGER).',
			lieferadresse = '.$this->db_add_param($this->lieferadresse).',
			rechnungsadresse = '.$this->db_add_param($this->rechnungsadresse).',
			freigegeben = '.$this->db_add_param($this->freigegeben, FHC_BOOLEAN).',
			bestell_nr = '.$this->db_add_param($this->bestell_nr).',
			titel = '.$this->db_add_param($this->titel).',
			bemerkung = '.$this->db_add_param($this->bemerkung).',
			liefertermin = '.$this->db_add_param($this->liefertermin).',
			updateamum = '.$this->db_add_param($this->updateamum).',
			updatevon ='.$this->db_add_param($this->updatevon).',
			zahlungstyp_kurzbz = '.$this->db_add_param($this->zahlungstyp_kurzbz).'
			WHERE bestellung_id = '.$this->db_add_param($this->bestellung_id, FHC_INTEGER, false).';';
		}
		if($this->db_query($qry))
		{
			if($this->new)
			{
				//aktuelle Sequence holen
				$qry="SELECT currval('wawi.seq_bestellung_bestellung_id') as id;";
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
	 * Rechnet den Bruttopreis einer Rechnung aus, false im Fehlerfall
	 * @param $bestellung_id dessen Bruttopreis ausgerechnet werden soll
	 */
	public function getBrutto($bestellung_id)
	{
		if(!is_numeric($bestellung_id))
		{
			$this->errormsg = "Keine gültige Bestell ID";
			return false;
		}

		$brutto = 0;
		$qry_brutto= "
			SELECT
				sum(brutto) as brutto
			FROM
				(SELECT
					detail.menge, detail.preisprove, detail.mwst,
					sum(detail.menge * detail.preisprove) * ((100+COALESCE(detail.mwst,0))/100) as brutto
				FROM
					wawi.tbl_bestellung as bestellung, wawi.tbl_bestelldetail as detail
				WHERE
					bestellung.bestellung_id = detail.bestellung_id
					AND bestellung.bestellung_id =".$this->db_add_param($bestellung_id, FHC_INTEGER)."
				GROUP BY detail.menge, detail.preisprove, detail.mwst
				) as b;";

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

	/**
	 *
	 * Liefert den ausgegebenen Bruttobetrag einer Kostenstelle im Geschäftsjahr zurück
	 * @param unknown_type $kostenstelle_id
	 * @param unknown_type $geschaeftsjahr_kurzbz
	 */
	public function getSpentBudget($kostenstelle_id, $geschaeftsjahr_kurzbz)
	{
		if($kostenstelle_id != '')
		{
			$geschaeftsjahr = new geschaeftsjahr();
			$geschaeftsjahr->load($geschaeftsjahr_kurzbz);
			$start = $geschaeftsjahr->start;
			$ende = $geschaeftsjahr->ende;
			$bestellung_id = array();
			$brutto = 0;

			$qry = "SELECT
						bestellung.bestellung_id, bestellung.kostenstelle_id
					FROM
						wawi.tbl_bestellung bestellung
					WHERE
						bestellung.kostenstelle_id = ".$this->db_add_param($kostenstelle_id, FHC_INTEGER)."
						AND bestellung.insertamum >= ".$this->db_add_param($start)."
						AND bestellung.insertamum <= ".$this->db_add_param($ende).';';

			if($this->db_query($qry))
			{
				while($row = $this->db_fetch_object())
				{
					$bestellung_id[] = $row->bestellung_id;
				}
			}
			else
				return false;

			foreach($bestellung_id as $bestellung)
			{
				$brutto += $this->getBrutto($bestellung);
			}
			return $brutto;
		}
	}

	/**
	 *
	 * Kopiert eine bestehende Bestellung
	 * @param $bestellung_id
	 */
	function copyBestellung($bestellung_id, $user)
	{
		// neue Bestellnummer erstellen
		$bestellung = new wawi_bestellung();
		$bestellung->load($bestellung_id);
		$newBestellNummer = $bestellung->createBestellNr($bestellung->kostenstelle_id);

		if(!is_numeric($bestellung_id))
		{
			$this->errormsg = "Keine gültige Bestell ID";
			return false;
		}

		$error = false;
		$this->db_query('BEGIN;');

		// Bestellung kopieren
		$qry_bestellung = "INSERT INTO wawi.tbl_bestellung (bestellung_id, besteller_uid, kostenstelle_id,
		konto_id, firma_id, lieferadresse, rechnungsadresse, zahlungstyp_kurzbz, freigegeben, bestell_nr,
		titel, bemerkung, liefertermin, updateamum, updatevon, insertamum, insertvon)
		SELECT nextval('wawi.seq_bestellung_bestellung_id'), ".$this->db_add_param($user).",
		kostenstelle_id, konto_id, firma_id, lieferadresse,	rechnungsadresse, zahlungstyp_kurzbz, 'false',
		".$this->db_add_param($newBestellNummer).", titel, bemerkung, liefertermin, now(),
		".$this->db_add_param($user).", now(), ".$this->db_add_param($user)."
		FROM wawi.tbl_bestellung WHERE bestellung_id = ".$this->db_add_param($bestellung_id, FHC_INTEGER).';';

		if(!$this->db_query($qry_bestellung))
			$error = true;

		// neue Bestellid abfragen
		$qry_currval = "SELECT currval('wawi.seq_bestellung_bestellung_id') as id;";
		if($this->db_query($qry_currval))
		{
			if($row = $this->db_fetch_object())
			{
				$newBestellung_id = $row->id;
			}
		}
		else
			$error = true;

		$bestelldetail = new wawi_bestelldetail();
		$bestelldetail->getAllDetailsFromBestellung($bestellung_id);

		// Bestelldetails kopieren
		foreach ($bestelldetail->result as $detail)
		{
			$qry_detail ="INSERT INTO wawi.tbl_bestelldetail (bestellung_id, position, menge,
			verpackungseinheit, beschreibung, artikelnummer, preisprove, mwst, erhalten, sort,
			text, insertamum, insertvon, updateamum, updatevon)
			SELECT ".$this->db_add_param($newBestellung_id, FHC_INTEGER).", position, menge,
			verpackungseinheit, beschreibung, artikelnummer, preisprove, mwst, erhalten, sort,
			text, now(), ".$this->db_add_param($user).", now(), ".$this->db_add_param($user)."
			FROM wawi.tbl_bestelldetail	WHERE bestelldetail_id = ".$this->db_add_param($detail->bestelldetail_id).';';

			if (!$this->db_query($qry_detail))
				$error = true;

			// neue Bestelldetail id abfragen
			$qry_currval = "SELECT currval('wawi.seq_bestelldetail_bestelldetail_id') as id;";
			if($this->db_query($qry_currval))
			{
				if($row = $this->db_fetch_object())
				{
					$newBestellDetail_id = $row->id;
				}
			}
			else
			{
				$error = true;
			}

			// zugehörigen TAG kopieren
			$qry_detailtag = "INSERT INTO wawi.tbl_bestelldetailtag (tag, bestelldetail_id, insertamum, insertvon)
			SELECT tag, ".$this->db_add_param($newBestellDetail_id, FHC_INTEGER).", now(), ".
			$this->db_add_param($user)." FROM wawi.tbl_bestelldetailtag
			WHERE bestelldetail_id = ".$this->db_add_param($detail->bestelldetail_id, FHC_INTEGER).';';

			if (!$this->db_query($qry_detailtag))
				$error = true;

		}

		// aufteilung kopieren
		$qry_aufteilung = "INSERT INTO wawi.tbl_aufteilung (bestellung_id, oe_kurzbz, anteil, updateamum,
		updatevon, insertamum, insertvon) SELECT ".$this->db_add_param($newBestellung_id, FHC_INTEGER)."
		, oe_kurzbz, anteil, updateamum, updatevon, now(), ".$this->db_add_param($user)."
		FROM wawi.tbl_aufteilung WHERE bestellung_id = ".$this->db_add_param($bestellung_id, FHC_INTEGER).";";
		if (!$this->db_query($qry_aufteilung))
			$error = true;

		// projekt bestellung kopieren
		$qry_project ="INSERT INTO wawi.tbl_projekt_bestellung (projekt_kurzbz, bestellung_id, anteil)
		SELECT projekt_kurzbz, ".$this->db_add_param($newBestellung_id, FHC_INTEGER).", anteil
		FROM wawi.tbl_projekt_bestellung WHERE bestellung_id = ".$this->db_add_param($bestellung_id, FHC_INTEGER).";";
		if (!$this->db_query($qry_project))
			$error = true;

		// bestelltag kopieren
		$qry_bestelltag ="INSERT INTO wawi.tbl_bestellungtag (tag, bestellung_id, insertamum, insertvon)
		SELECT tag, ".$this->db_add_param($newBestellung_id, FHC_INTEGER).", now(), ".$this->db_add_param($user)."
		FROM wawi.tbl_bestellungtag WHERE bestellung_id = ".$this->db_add_param($bestellung_id, FHC_INTEGER).";";
		if (!$this->db_query($qry_bestelltag))
			$error = true;

		if(!$error)
		{
			$this->db_query('COMMIT');
			return $newBestellung_id;
		}
		else
		{
			$this->db_query('ROLLBACK');
			return false;
		}
	}

	/**
	 *
	 * Liefert die oe_kurzbz einer Bestellung zurück. JOIN über Kostenstelle
	 * @return oe_kurzbz der Bestellung oder false im Fehlerfall
	 */
	public function getOe()
	{
		$qry = "SELECT
					kostenstelle.oe_kurzbz
				FROM
					wawi.tbl_kostenstelle as kostenstelle,
					wawi.tbl_bestellung as bestellung
				WHERE
					bestellung.kostenstelle_id = kostenstelle.kostenstelle_id
					AND bestellung.bestellung_id = ".$this->db_add_param($this->bestellung_id, FHC_INTEGER).";";

		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				return $row->oe_kurzbz;
			}
			else
			{
				return false;
			}
		}
		else
		return false;
	}

	/**
	 *
	 * Gibt alle OEs zurück die freigegeben werden müssen
	 * @return Array von oe_kurzbz oder false im Fehlerfall
	 */
	public function FreigabeOe($bestellung_id)
	{
		if(!is_numeric($bestellung_id))
		{
			$this->errormsg = "Keine gültige Bestell ID";
			return false;
		}

		$oe = new organisationseinheit();
		$bestellung = new wawi_bestellung();

		$bestellung->load($bestellung_id);
		$brutto = $bestellung->getBrutto($bestellung_id);
		$brutto = number_format($brutto,2,".","");
		$oe_bestellung = $bestellung->getOe();
		// oe der bestellung
		$oe->load($oe_bestellung);
		$oes = array();

		$qry = "WITH RECURSIVE oes(oe_kurzbz, oe_parent_kurzbz, freigabegrenze) as
			(
				SELECT oe_kurzbz, oe_parent_kurzbz, freigabegrenze FROM public.tbl_organisationseinheit
				WHERE oe_kurzbz=".$this->db_add_param($oe_bestellung)." and aktiv = true
				UNION ALL
				SELECT o.oe_kurzbz, o.oe_parent_kurzbz, o.freigabegrenze FROM public.tbl_organisationseinheit o, oes
				WHERE o.oe_kurzbz=oes.oe_parent_kurzbz and aktiv = true
			)
			SELECT oe_kurzbz
			FROM oes
			WHERE freigabegrenze<=".$this->db_add_param($brutto)."
			GROUP BY oe_kurzbz,freigabegrenze ORDER BY freigabegrenze";

		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$oes[] = $row->oe_kurzbz;
			}
			return $oes;
		}
		else
			return false;
	}

	/**
	 *
	 * Gibt true zurück wenn schon eine Rechnung zur Übergebenen Bestellung vorhanden ist, andernfalls false
	 * @param $bestellung_id
	 */
	public function RechnungVorhanden($bestellung_id)
	{
		if(!is_numeric($bestellung_id))
		{
			$this->errormsg = 'Keine gültige Bestell ID';
			return false;
		}

		$qry = "SELECT * FROM wawi.tbl_rechnung WHERE bestellung_id = ".$this->db_add_param($bestellung_id, FHC_INTEGER).';';

		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				return true;
			}
			return false;
		}
		else
			return false;
	}

	/**
	 *
	 * Aktualisiert eine Bestellung mit der übergebenen Bestellnummer
	 * @param $bestellung_id
	 * @param $bestellnr
	 */
	public function updateBestellNr($bestellung_id, $bestellnr)
	{
		if(!is_numeric($bestellung_id))
		{
			$this->errormsg = "Keine gültige Bestell ID";
			return false;
		}

		$qry = "UPDATE wawi.tbl_bestellung SET bestell_nr = ".$this->db_add_param($bestellnr)."
				WHERE bestellung_id = ".$this->db_add_param($bestellung_id, FHC_INTEGER).';';

		if($this->db_query($qry))
			return true;
		else
		{
			$this->errormsg ="Fehler beim Aendern der Bestellnummer";
			return false;
		}
	}



	/**
	 *
	 * Setzt die Freigabe einer Bestellung
	 * @param $bestellung_id
	 * @return true wenn ok, sonst false
	 */
	public function SetFreigegeben($bestellung_id)
	{
		if(!is_numeric($bestellung_id))
		{
			$this->errormsg = "Keine gültige Bestell ID";
			return false;
		}

		$qry = "UPDATE wawi.tbl_bestellung SET freigegeben = true
				WHERE bestellung_id = ".$this->db_add_param($bestellung_id, FHC_INTEGER).';';

		if($this->db_query($qry))
			return true;
		else
		{
			$this->errormsg ="Fehler beim Setzen von Freigegeben";
			return false;
		}
	}

	/**
	 *
	 * Gibt alle Bestellungen zurück die im Zeitraum zwischen max und min liegen und einen abgeschickt status und keinen Bestellstatus besitzen
	 * @param $min	in wochen
	 * @param $max in wochen
	 */
	public function loadBestellungForCheck($min, $max)
	{
		if(!is_numeric($min) || !is_numeric($max))
		{
			$this->errormsg = "Ungueltige Werte für min und max in Check-Skript.";
			return false;
		}

		$qry ="
		SELECT
			*
		FROM
			wawi.tbl_bestellung
		WHERE
			bestellung_id IN(
				SELECT
					b.bestellung_id
				FROM
					wawi.tbl_bestellung as b
					LEFT JOIN wawi.tbl_bestellung_bestellstatus as s using (bestellung_id)
				WHERE
				b.bestellung_id NOT IN (
					SELECT
						bestellung_id
					FROM
						wawi.tbl_bestellung_bestellstatus
					WHERE
						bestellung_id=b.bestellung_id
						AND bestellstatus_kurzbz ='Bestellung'
					)
				AND (bestellstatus_kurzbz = ('Abgeschickt') OR bestellstatus_kurzbz = ('Freigegeben'))
				AND b.bestellung_id = b.bestellung_id
				AND b.insertamum <= CURRENT_DATE - '".$this->db_escape($min)." week'::interval
				AND b.insertamum > CURRENT_DATE - '".$this->db_escape($max)." week'::interval
			)
		ORDER BY bestellung_id";

		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				$bestellung = new wawi_bestellung();

				$bestellung->bestellung_id = $row->bestellung_id;
				$bestellung->bestell_nr = $row->bestell_nr;
				$bestellung->titel = $row->titel;
				$bestellung->bemerkung = $row->bemerkung;
				$bestellung->liefertermin = $row->liefertermin;
				$bestellung->besteller_uid = $row->besteller_uid;
				$bestellung->lieferadresse = $row->lieferadresse;
				$bestellung->kostenstelle_id = $row->kostenstelle_id;
				$bestellung->konto_id = $row->konto_id;
				$bestellung->rechnungsadresse = $row->rechnungsadresse;
				$bestellung->firma_id = $row->firma_id;
				$bestellung->freigegeben = $this->db_parse_bool($row->freigegeben);
				$bestellung->updateamum = $row->updateamum;
				$bestellung->updatevon = $row->updatevon;
				$bestellung->insertamum = $row->insertamum;
				$bestellung->insertvon = $row->insertvon;
				$bestellung->ext_id = $row->ext_id;
				$bestellung->zahlungstyp_kurzbz = $row->zahlungstyp_kurzbz;

				$this->result[] = $bestellung;
			}
		}
		else
		{
			$this->errormsg ="Fehler bei der Abfrage aufgetreten.";
			return false;
		}
		return true;
	}

	/**
	 *
	 * Gibt alle Bestellungen die Bestellt aber nicht geliefert wurden
	 */
	public function loadBestellungNichtGeliefert()
	{
		$qry ="
			SELECT
				*
			FROM
				wawi.tbl_bestellung b
			WHERE
				EXISTS (SELECT bestellung_id FROM wawi.tbl_bestellung_bestellstatus
						WHERE bestellung_id=b.bestellung_id AND bestellstatus_kurzbz ='Bestellung')
				AND NOT EXISTS (SELECT bestellung_id FROM wawi.tbl_bestellung_bestellstatus
						WHERE bestellung_id=b.bestellung_id AND bestellstatus_kurzbz ='Lieferung')
				AND b.insertamum>CURRENT_DATE - '1 year'::interval
			ORDER BY bestellung_id";

		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				$bestellung = new wawi_bestellung();

				$bestellung->bestellung_id = $row->bestellung_id;
				$bestellung->bestell_nr = $row->bestell_nr;
				$bestellung->titel = $row->titel;
				$bestellung->bemerkung = $row->bemerkung;
				$bestellung->liefertermin = $row->liefertermin;
				$bestellung->besteller_uid = $row->besteller_uid;
				$bestellung->lieferadresse = $row->lieferadresse;
				$bestellung->kostenstelle_id = $row->kostenstelle_id;
				$bestellung->konto_id = $row->konto_id;
				$bestellung->rechnungsadresse = $row->rechnungsadresse;
				$bestellung->firma_id = $row->firma_id;
				$bestellung->freigegeben = $this->db_parse_bool($row->freigegeben);
				$bestellung->updateamum = $row->updateamum;
				$bestellung->updatevon = $row->updatevon;
				$bestellung->insertamum = $row->insertamum;
				$bestellung->insertvon = $row->insertvon;
				$bestellung->ext_id = $row->ext_id;
				$bestellung->zahlungstyp_kurzbz = $row->zahlungstyp_kurzbz;

				$this->result[] = $bestellung;
			}
		}
		else
		{
			$this->errormsg ="Fehler bei der Abfrage aufgetreten.";
			return false;
		}
		return true;
	}
	/**
	 *
	 * true wenn die Bestellung schon freigegeben wurde
	 * @param $bestellung_id
	 */
	public function isFreigegeben($bestellung_id)
	{
		if(!is_numeric($bestellung_id))
		{
			$this->errormsg = "Keine gültige Bestell ID";
			return false;
		}

		$qry = "SELECT
					*
				FROM
					wawi.tbl_bestellung
				WHERE
					freigegeben = true
					AND bestellung_id = ".$this->db_add_param($bestellung_id, FHC_INTEGER).";";

		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				return true;
			}
		}
		else
		{
			return false;
		}
		return false;
	}

	/**
	 *
	 * speichert / loescht ein Projekt zu einer Bestellung
	 * @param $bestellungID
	 * @param $projektKurzbz
	 */
	public function saveProjektToBestellung($bestellungID, $projektKurzbz)
	{
		// Wenn keine ProjektKurzbz übergeben wurde Lösche die Zuteilung
		if($projektKurzbz == '')
		{
			$qry = "DELETE FROM wawi.tbl_projekt_bestellung WHERE bestellung_id = ".$this->db_add_param($bestellungID, FHC_INTEGER).";";
			if($this->db_query($qry))
			{
				return true;
			}
			else
			{
				$this->errormsg = "Fehler beim Löschen der Zuordnung: Projekt zu Bestellung aufgetreten.";
				return false;
			}
		}
		else
		{
			$qry1 = "SELECT 1 FROM wawi.tbl_projekt_bestellung WHERE bestellung_id = ".$this->db_add_param($bestellungID, FHC_INTEGER).";";

			if($this->db_query($qry1))
			{
				if($row = $this->db_fetch_object())
				{
					// es gibt eine Zuordnung -> UPDATE
					$qry ="UPDATE wawi.tbl_projekt_bestellung SET projekt_kurzbz = ".$this->db_add_param($projektKurzbz)."
					WHERE bestellung_id = ".$this->db_add_param($bestellungID, FHC_INTEGER).";";

					if($this->db_query($qry))
					{
						return true;
					}
					else
					{
						$this->errormsg = "Fehler beim Zuordnen: Bestellung zu Projekt aufgetreten.";
						return false;
					}
				}
				else
				{
					// gibt noch keine Zuordnung -> INSERT
					$qry ="INSERT INTO wawi.tbl_projekt_bestellung (projekt_kurzbz, bestellung_id, anteil)
					VALUES (".$this->db_add_param($projektKurzbz).", ".$this->db_add_param($bestellungID, FHC_INTEGER).", '100');";

					if($this->db_query($qry))
					{
						return true;
					}
					else
					{
						$this->errormsg = "Fehler beim Zuordnen: Bestellung zu Projekt aufgetreten.";
						return false;
					}
				}
			}
		}
	}

	/**
	 *
	 * Liefert die nächste Bestellnummer der Kostenstelle zurück
	 * @param $kostenstelle_id
	 */
	public function createBestellNr($kostenstelle_id)
	{
		// kostenstelle holen
		$qry="SELECT
				tbl_kostenstelle.kurzbz, tbl_organisationseinheit.kurzzeichen
			FROM
				wawi.tbl_kostenstelle
				JOIN public.tbl_organisationseinheit USING(oe_kurzbz)
			WHERE
				kostenstelle_id=".$this->db_add_param($kostenstelle_id, FHC_INTEGER).";";

		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				$kostenstelle_kz=$row->kurzbz;
				$kurzzeichen = $row->kurzzeichen;
			}
		}
		// wenn kurzbz länger ist -> abschneiden
		if(mb_strlen($kurzzeichen)>3)
		{
			$kurzzeichen = mb_substr($kurzzeichen, 0,3);
		}
		$kurzzeichen = mb_strtoupper($kurzzeichen);
		$akt_timestamp=time();
		$akt_datum=getdate($akt_timestamp);
		$akt_mon=$akt_datum['mon'];
		$akt_year=$akt_datum['year'];
		if ($akt_mon<9)
			$akt_year--;
		$akt_year=substr($akt_year,2,2);
		$kostenstelle_kz = mb_strtoupper($kostenstelle_kz);
		$kuerzel = $kurzzeichen.$akt_year.$kostenstelle_kz.'___';
		$qry = "SELECT max(substr(bestell_nr,length(bestell_nr)-2)) FROM wawi.tbl_bestellung
		WHERE wawi.tbl_bestellung.bestell_nr LIKE ".$this->db_add_param($kuerzel).";";

		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{	$bnum = $row->max + 1;
				$bnum = sprintf("%03s",$bnum);
			}
		}
		else
		{
			$this->errormsg ="Fehler bei der Datenbankabfrage aufgetreten";
			return false;
		}

		$bnum=sprintf("%s%s%s%s",$kurzzeichen,$akt_year,$kostenstelle_kz,$bnum);
		return $bnum;
	}

}
