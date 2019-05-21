<?php
/* Copyright (C) 2006 Technikum-Wien
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
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>.
 */
/**
 * Klasse kontakt
 * @create 20-12-2006
 */
require_once(dirname(__FILE__).'/basis_db.class.php');
require_once(dirname(__FILE__).'/sprache.class.php');

class kontakt extends basis_db
{
	public $new;       // boolean
	public $result = array(); // adresse Objekt

	//Tabellenspalten
	public $kontakt_id;	// integer
	public $person_id;	// integer
	public $firma_id;		// integer
	public $standort_id;	// integer

	public $kontakttyp;	// string
	public $anmerkung;	// string
	public $kontakt;		// string
	public $zustellung;	// boolean
	public $ext_id;		// integer
	public $insertamum;	// timestamp
	public $insertvon;	// bigint
	public $updateamum;	// timestamp
	public $updatevon;	// bigint

	public $beschreibung;
	public $firma_name;


	public $anrede;
	public $titelpost;
	public $titelpre;
	public $nachname;
	public $vorname;
	public $vornamen;

	/**
	 * Konstruktor
	 * @param $kontakt_id ID der Adresse die geladen werden soll (Default=null)
	 */
	public function __construct($kontakt_id=null)
	{
		parent::__construct();

		if(!is_null($kontakt_id))
			$this->load($kontakt_id);
	}

	/**
	 * Laedt einen Kontakt mit der ID $kontakt_id
	 * @param  $kontakt_id ID des zu ladenden Kontaktes
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function load($kontakt_id)
	{
		if (!is_numeric($kontakt_id))
		{
			$this->errormsg = 'Kontakt_id ist ungueltig';
			return false;
		}

		$qry = "SELECT tbl_kontakt.*, tbl_firma.name as firma_name, tbl_firma.firma_id
				FROM
					public.tbl_kontakt
					LEFT JOIN public.tbl_standort USING(standort_id)
					LEFT JOIN public.tbl_firma USING(firma_id)
				WHERE kontakt_id = " . $this->db_add_param($kontakt_id, FHC_INTEGER) . ";";

		if ($this->db_query($qry))
		{
			if ($row = $this->db_fetch_object())
			{
				$this->kontakt_id = $row->kontakt_id;
				$this->person_id = $row->person_id;
				$this->standort_id = $row->standort_id;
				$this->firma_id = $row->firma_id;
				$this->firma_name = $row->firma_name;
				$this->kontakttyp = $row->kontakttyp;
				$this->anmerkung = $row->anmerkung;
				$this->kontakt = $row->kontakt;
				$this->zustellung = $this->db_parse_bool($row->zustellung);
				$this->updateamum = $row->updateamum;
				$this->updatevon = $row->updatevon;
				$this->insertamum = $row->insertamum;
				$this->insertvon = $row->insertvon;
				$this->ext_id = $row->ext_id;
				return true;
			}
			else
			{
				$this->errormsg = 'Datensatz wurde nicht gefunden';
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
	 * Prueft die Variablen auf Gueltigkeit
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function validate()
	{

		//Gesamtlaenge pruefen
		//$this->errormsg='Eine der Gesamtlaengen wurde ueberschritten';
		if(mb_strlen($this->kontakttyp)>32)
		{
			$this->errormsg = 'kontakttyp darf nicht länger als 32 Zeichen sein';
			return false;
		}
		if(mb_strlen($this->anmerkung)>64)
		{
			$this->errormsg = 'anmerkung darf nicht länger als 64 Zeichen sein';
			return false;
		}
		if(mb_strlen($this->kontakt)>128)
		{
			$this->errormsg = 'kontakt darf nicht länger als 128 Zeichen sein';
			return false;
		}
		if($this->kontakttyp=='email' && !strstr($this->kontakt, '@'))
		{
			$this->errormsg = 'Wenn der Typ E-Mail ausgewählt wurde, muss der Kontakt ein @ enthalten!';
			return false;
		}
		$this->errormsg = '';
		return true;
	}

	/**
	 * Speichert den aktuellen Datensatz in die Datenbank
	 * Wenn $neu auf true gesetzt ist wird ein neuer Datensatz angelegt
	 * andernfalls wird der Datensatz mit der ID in $kontakt_id aktualisiert
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function save()
	{
		//Variablen pruefen
		if(!$this->validate())
			return false;

		if($this->new)
		{
			//Neuen Datensatz einfuegen
			$qry='BEGIN;INSERT INTO public.tbl_kontakt (person_id, standort_id, kontakttyp, anmerkung, kontakt, zustellung, insertamum, insertvon, updateamum, updatevon) VALUES('.
			     $this->db_add_param($this->person_id, FHC_INTEGER).', '.
			     $this->db_add_param($this->standort_id, FHC_INTEGER).', '.
			     $this->db_add_param($this->kontakttyp).', '.
			     $this->db_add_param($this->anmerkung).', '.
			     $this->db_add_param($this->kontakt).', '.
			     $this->db_add_param($this->zustellung, FHC_BOOLEAN).', now(), '.
			     $this->db_add_param($this->insertvon).', now(), '.
			     $this->db_Add_param($this->updatevon).');';
		}
		else
		{
			//Updaten des bestehenden Datensatzes

			//Pruefen ob kontakt_id eine gueltige Zahl ist
			if(!is_numeric($this->kontakt_id))
			{
				$this->errormsg = 'kontakt_id muss eine gueltige Zahl sein';
				return false;
			}
			$qry='UPDATE public.tbl_kontakt SET '.
				'person_id='.$this->db_add_param($this->person_id,FHC_INTEGER).', '.
				'standort_id='.$this->db_add_param($this->standort_id, FHC_INTEGER).', '.
				'kontakttyp='.$this->db_add_param($this->kontakttyp).', '.
				'anmerkung='.$this->db_add_param($this->anmerkung).', '.
				'kontakt='.$this->db_add_param($this->kontakt).', '.
				'zustellung='.$this->db_add_param($this->zustellung, FHC_BOOLEAN).', '.
			    'updateamum= now(), '.
			    'updatevon='.$this->db_add_param($this->updatevon).' '.
				'WHERE kontakt_id='.$this->db_add_param($this->kontakt_id, FHC_INTEGER).';';
		}

		if($this->db_query($qry))
		{
			//Sequence auslesen um die eingefuegte ID zu ermitteln
			if($this->new)
			{
				$qry = "SELECT currval('public.tbl_kontakt_kontakt_id_seq') as id;";

				if($this->db_query($qry))
				{
					if($row = $this->db_fetch_object())
					{
						$this->kontakt_id = $row->id;
						$this->db_query('COMMIT;');
						return true;
					}
					else
					{
						$this->errormsg = 'Fehler beim Auslesen er Sequence';
						$this->db_query('ROLLBACK;');
						return false;
					}
				}
				else
				{
					$this->errormsg = 'Fehler beim Auslesen der Sequence';
					$this->db_query('ROLLBACK;');
					return false;
				}
			}
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern der Daten';
			return false;
		}
	}

	/**
	 * Loescht den Datenensatz mit der ID die uebergeben wird
	 * @param $kontakt_id ID die geloescht werden soll
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function delete($kontakt_id)
	{
		if(!is_numeric($kontakt_id))
		{
			$this->errormsg = 'Kontakt_id ist ungueltig';
			return false;
		}

		$qry = "DELETE FROM public.tbl_kontakt WHERE kontakt_id=".$this->db_add_param($kontakt_id, FHC_INTEGER).";";

		if($this->db_query($qry))
			return true;
		else
		{
			$this->errormsg = 'Fehler beim Loeschen der Daten';
			return false;
		}
	}

    /**
	 * Laedt Kontaktdaten eines bestimmten typs der Person
	 * @param person_id
     * @param kontakttyp
	 * @return boolean
	 */
	public function load_persKontakttyp($person_id, $kontakttyp, $order = null)
	{
		if(!is_numeric($person_id))
		{
			$this->errormsg = 'Person_id ist ungueltig';
			return false;
		}

		$qry = "SELECT tbl_kontakt.*,
					tbl_firma.NAME AS firma_name,
					tbl_firma.firma_id
				FROM PUBLIC.tbl_kontakt
				LEFT JOIN PUBLIC.tbl_standort USING (standort_id)
				LEFT JOIN PUBLIC.tbl_firma USING (firma_id)
				WHERE person_id = ".$this->db_add_param($person_id, FHC_INTEGER)."
					AND kontakttyp = ".$this->db_add_param($kontakttyp, FHC_STRING);

		if ($order != null)
			$qry .= " ORDER BY ".$order;

		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$obj = new kontakt();

				$obj->kontakt_id = $row->kontakt_id;
				$obj->person_id = $row->person_id;
				$obj->standort_id = $row->standort_id;
				$obj->firma_id = $row->firma_id;
				$obj->firma_name = $row->firma_name;
				$obj->kontakttyp = $row->kontakttyp;
				$obj->anmerkung = $row->anmerkung;
				$obj->kontakt = $row->kontakt;
				$obj->zustellung = $this->db_parse_bool($row->zustellung);
				$obj->updateamum = $row->updateamum;
				$obj->updatevon = $row->updatevon;
				$obj->insertamum = $row->insertamum;
				$obj->insertvon = $row->insertvon;
				$obj->ext_id = $row->ext_id;

				$this->result[] = $obj;
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
	 * Laedt alle Kontaktdaten einer Person
	 * @param person_id
	 * @return boolean
	 */
	public function load_pers($person_id)
	{
		if(!is_numeric($person_id))
		{
			$this->errormsg = 'Person_id ist ungueltig';
			return false;
		}
		$sprache = new sprache();
		$bezeichnung_mehrsprachig = $sprache->getSprachQuery('bezeichnung_mehrsprachig');
		$qry = "SELECT tbl_kontakt.*, tbl_firma.name as firma_name, tbl_firma.firma_id, tbl_kontakttyp.beschreibung as kontakttyp_name, $bezeichnung_mehrsprachig
				FROM public.tbl_kontakt JOIN tbl_kontakttyp USING (kontakttyp) LEFT JOIN public.tbl_standort USING(standort_id) LEFT JOIN public.tbl_firma USING(firma_id) WHERE person_id=".$this->db_add_param($person_id, FHC_INTEGER).';';

		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$obj = new kontakt();

				$obj->kontakt_id = $row->kontakt_id;
				$obj->person_id = $row->person_id;
				$obj->standort_id = $row->standort_id;
				$obj->firma_id = $row->firma_id;
				$obj->firma_name = $row->firma_name;
				$obj->kontakttyp = $row->kontakttyp;
				$obj->anmerkung = $row->anmerkung;
				$obj->kontakt = $row->kontakt;
				$obj->zustellung = $this->db_parse_bool($row->zustellung);
				$obj->updateamum = $row->updateamum;
				$obj->updatevon = $row->updatevon;
				$obj->insertamum = $row->insertamum;
				$obj->insertvon = $row->insertvon;
				$obj->ext_id = $row->ext_id;
				$obj->kontakttyp_name = $row->kontakttyp_name;
				$obj->bezeichnung_mehrsprachig = $sprache->parseSprachResult('bezeichnung_mehrsprachig',$row);

				$this->result[] = $obj;
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
	 * Laedt alle Kontaktdaten zu einem Standort
	 * @param standort_id
	 * @return boolean
	 */
	public function load_standort($standort_id='',$firma_id='',$kontakt_id='',$person_id='')
	{
		$this->result=array();
		$this->errormsg = '';

		if($firma_id!='' && !is_numeric($firma_id))
		{
			$this->errormsg = 'Firma ist ungueltig';
			return false;
		}
		if($standort_id!='' && !is_numeric($standort_id))
		{
			$this->errormsg = 'Standort ist ungueltig';
			return false;
		}
		if($kontakt_id!='' && !is_numeric($kontakt_id))
		{
			$this->errormsg = 'Kontakt ist ungueltig';
			return false;
		}
		if($person_id!='' && !is_numeric($person_id))
		{
			$this->errormsg = 'Person ist ungueltig';
			return false;
		}
		$qry = "SELECT tbl_kontakt.*
				,tbl_standort.firma_id, tbl_standort.kurzbz as standort_kurzbz, tbl_standort.bezeichnung as standort_bezeichnung
				FROM public.tbl_kontakt,public.tbl_standort
				WHERE tbl_standort.standort_id=tbl_kontakt.standort_id
			";

			if(is_numeric($firma_id))
				$qry.=" and tbl_standort.firma_id=".$this->db_add_param($firma_id, FHC_INTEGER);
			if(is_numeric($standort_id))
				$qry.=" and tbl_kontakt.standort_id=".$this->db_add_param($standort_id, FHC_INTEGER);
			if(is_numeric($kontakt_id))
				$qry.=" and tbl_kontakt.kontakt_id=".$this->db_add_param($kontakt_id, FHC_INTEGER);
			if(is_numeric($person_id))
				$qry.=" and tbl_kontakt.person_id=".$this->db_add_param($person_id, FHC_INTEGER);

            $qry.=';';
##echo $qry;
		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$obj = new kontakt();

				$obj->kontakt_id = $row->kontakt_id;
				$obj->person_id = $row->person_id;
				$obj->standort_id = $row->standort_id;
				$obj->firma_id = $row->firma_id;
				$obj->standort_kurzbz  = $row->standort_kurzbz;
				$obj->standort_bezeichnung  = $row->standort_bezeichnung;
				$obj->kontakttyp = $row->kontakttyp;
				$obj->anmerkung = $row->anmerkung;
				$obj->kontakt = $row->kontakt;
				$obj->zustellung = $this->db_parse_bool($row->zustellung);
				$obj->updateamum = $row->updateamum;
				$obj->updatevon = $row->updatevon;
				$obj->insertamum = $row->insertamum;
				$obj->insertvon = $row->insertvon;
				$obj->ext_id = $row->ext_id;

				$this->result[] = $obj;
			}
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Daten';
			return false;
		}
		return $this->result;
	}

	/**
	 * Laedt einen Kontakt eines Standortes
	 * Es wird nur der erste Eintrag zurueckgeliefert!
	 *
	 * @param $standort_id
	 * @param $kontakttyp
	 */
	public function loadFirmaKontakttyp($standort_id, $kontakttyp)
	{
		if(!is_numeric($standort_id))
		{
			$this->errormsg='StandortID ist ungueltig';
			return false;
		}

		$qry = "SELECT * FROM public.tbl_kontakt WHERE standort_id=".$this->db_add_param($standort_id, FHC_INTEGER)." AND kontakttyp=".$this->db_add_param($kontakttyp)." ORDER BY kontakt_id LIMIT 1;";

		if($result = $this->db_query($qry))
		{
			if($row = $this->db_fetch_object($result))
			{
				$this->kontakt = $row->kontakt;
				$this->kontakt_id = $row->kontakt_id;
				return true;
			}
			else
				return false;
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Daten';
			return false;
		}
	}

	/**
	 * Laedt alle Kontakttypen
	 * @return true wenn ok
	 * false im Fehlerfall
	 */
	public function getKontakttyp()
	{
		$qry = "SELECT * FROM public.tbl_kontakttyp ORDER BY beschreibung;";

		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$obj = new kontakt();

				$obj->kontakttyp = $row->kontakttyp;
				$obj->beschreibung = $row->beschreibung;

				$this->result[] = $obj;
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
	 * Sucht nach Kontaktdaten, die den Suchkriterien entsprechen
	 * @param string $searchstring 	String, nach dem gesucht werden soll.
	 * 								Wenn $typ = nummer ist, werden eventuelle nicht-numerische Zeichen mit preg_replace entfernt,
	 * 								und es wird nach den Typen "telefon","mobil","so.tel","firmenhandy" und "notfallkontakt" gesucht
	 * @param string $typ Optional. Kontakttyp. Möglich sind <b>"nummer"</b>,"telefon","mobil","so.tel","firmenhandy","firmenhandy","email","fax" und "homepage".
	 * 								Wenn $typ = nummer ist, werden eventuelle nicht-numerische Zeichen mit preg_replace entfernt,
	 * 								und es wird nach den Typen "telefon","mobil","so.tel","firmenhandy" und "notfallkontakt" gesucht
	 * @return boolean
	 */
	public function searchKontakt ($searchstring, $typ = '')
	{
		if ($typ == 'nummer')
		{
			$searchstring = preg_replace('/[^0-9]/','',$searchstring);
			if(!is_numeric($searchstring))
			{
				$this->errormsg='Der Suchbegriff ist keine gültige Telefonnummer';
				return false;
			}
		}
		$qry = "SELECT
					*
				FROM
					public.tbl_kontakt
				WHERE
					1=1";

		if ($typ == 'nummer')
			$qry .= " AND regexp_replace(kontakt , '[^0-9]', '', 'g') LIKE ('%".$this->db_escape($searchstring)."%')";
		else
			$qry .= " AND LOWER (kontakt) LIKE LOWER ('%".$this->db_escape($searchstring)."%')";

		if ($typ != '' && $typ != 'nummer')
			$qry .= " AND kontakttyp=".$this->db_add_param($typ);

		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$obj = new kontakt();

				$obj->kontakt_id = $row->kontakt_id;
				$obj->person_id = $row->person_id;
				$obj->standort_id = $row->standort_id;
				$obj->kontakttyp = $row->kontakttyp;
				$obj->anmerkung = $row->anmerkung;
				$obj->kontakt = $row->kontakt;
				$obj->zustellung = $this->db_parse_bool($row->zustellung);
				$obj->updateamum = $row->updateamum;
				$obj->updatevon = $row->updatevon;
				$obj->insertamum = $row->insertamum;
				$obj->insertvon = $row->insertvon;
				$obj->ext_id = $row->ext_id;

				$this->result[] = $obj;
			}

			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Daten';
			return false;
		}
	}
}
?>
