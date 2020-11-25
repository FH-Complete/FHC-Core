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
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>,
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at> and
 *          Andreas Moik <moik@technikum-wien.at>.
 */
require_once(dirname(__FILE__).'/basis_db.class.php');

class akte extends basis_db
{
	public $new;
	public $result = array();

	//Tabellenspalten
	public $akte_id;
	public $person_id;
	public $dokument_kurzbz;
	public $inhalt;
	public $mimetype;
	public $erstelltam;
	public $gedruckt;
	public $titel;
	public $bezeichnung;
	public $updateamum;
	public $updatevon;
	public $insertamum;
	public $insertvon;
	public $uid;
	public $ext_id;
	public $dms_id;
	public $nachgereicht;
	public $anmerkung;
	public $titel_intern;
	public $anmerkung_intern;
	public $nachgereicht_am;
	public $ausstellungsnation;
	public $formal_geprueft_amum;
	public $archiv = false;
	public $signiert = false;
	public $stud_selfservice = false;
	public $akzeptiertamum;

	/**
	 * Konstruktor
	 * @param akte_id ID des zu ladenden Datensatzes
	 */
	public function __construct($akte_id=null)
	{
		parent::__construct();

		if(!is_null($akte_id))
			$this->load($akte_id);
	}

	/**
	 * Laedt einen Datensatz
	 * @param akte_id ID des zu ladenden Datensatzes
	 */
	public function load($akte_id)
	{
		//akte_id auf gueltigkeit pruefen
		if(!is_numeric($akte_id) || $akte_id == '')
		{
			$this->errormsg = 'akte_id muss eine gueltige Zahl sein';
			return false;
		}

		//laden des Datensatzes
		$qry = "SELECT * FROM public.tbl_akte WHERE akte_id=".$this->db_add_param($akte_id, FHC_INTEGER);

		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				$this->akte_id = $row->akte_id;
				$this->person_id = $row->person_id;
				$this->dokument_kurzbz = $row->dokument_kurzbz;
				$this->inhalt = $row->inhalt;
				$this->mimetype = $row->mimetype;
				$this->erstelltam = $row->erstelltam;
				$this->gedruckt = $this->db_parse_bool($row->gedruckt);
				$this->titel = $row->titel;
				$this->bezeichnung = $row->bezeichnung;
				$this->updateamum = $row->updateamum;
				$this->updatevon = $row->updatevon;
				$this->insertamum = $row->insertamum;
				$this->insertvon = $row->insertvon;
				$this->uid = $row->uid;
				$this->dms_id = $row->dms_id;
				$this->anmerkung = $row->anmerkung;
				$this->nachgereicht = $this->db_parse_bool($row->nachgereicht);
				$this->titel_intern = $row->titel_intern;
				$this->anmerkung_intern = $row->anmerkung_intern;
				$this->nachgereicht_am = $row->nachgereicht_am;
				$this->ausstellungsnation = $row->ausstellungsnation;
				$this->formal_geprueft_amum = $row->formal_geprueft_amum;
				$this->archiv = $this->db_parse_bool($row->archiv);
				$this->signiert = $this->db_parse_bool($row->signiert);
				$this->stud_selfservice = $this->db_parse_bool($row->stud_selfservice);
				$this->akzeptiertamum = $row->akzeptiertamum;

				return true;
			}
			else
			{
				$this->errormsg = 'Fehler bei der Datenbankabfrage';
				return false;
			}
		}
		else
		{
			$this->errormsg = 'Fehler bei der Datenbankabfrage';
			return false;
		}
	}

	/**
	 * Loescht einen Datensatz
	 * @param akte_id ID des zu loeschenden Datensatzes
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function delete($akte_id)
	{
		//akte_id auf gueltigkeit pruefen
		if(!is_numeric($akte_id) || $akte_id == '')
		{
			$this->errormsg = 'akte_id muss eine gueltige Zahl sein';
			return false;
		}

		$qry = "DELETE FROM public.tbl_akte WHERE akte_id=".$this->db_add_param($akte_id, FHC_INTEGER);

		if($this->db_query($qry))
		{
			//Log schreiben
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Löschen';
			return false;
		}
	}

	/**
	 * Prueft die Variablen vor dem Speichern
	 *
	 * @return true wenn ok, sonst false
	 */
	protected function validate()
	{
		if($this->person_id=='')
		{
			$this->errormsg = 'Person ID muss angegeben werden';
			return false;
		}
		if($this->dokument_kurzbz=='')
		{
			$this->errormsg = 'DokumentKurzbz muss angegeben werden';
			return false;
		}

		return true;
	}

	/**
	 * Speichert den aktuellen Datensatz
	 * Wenn $neu auf true gesetzt ist wird ein neuer Datensatz angelegt
	 * andernfalls wird der Datensatz mit der ID in $akte_id aktualisiert
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function save($new=null)
	{
		if(!$this->validate())
			return false;

		if($new==null)
			$new = $this->new;

		if($new)
		{
			//Neuen Datensatz anlegen
			$qry = "BEGIN;INSERT INTO public.tbl_akte (person_id, dokument_kurzbz, inhalt, mimetype, erstelltam, gedruckt, titel,
					bezeichnung, updateamum, updatevon, insertamum, insertvon, uid, dms_id, nachgereicht, anmerkung,
					titel_intern, anmerkung_intern, nachgereicht_am, ausstellungsnation, formal_geprueft_amum,
					archiv, signiert, stud_selfservice, akzeptiertamum) VALUES (".
						$this->db_add_param($this->person_id, FHC_INTEGER).', '.
						$this->db_add_param($this->dokument_kurzbz).', '.
						$this->db_add_param($this->inhalt).', '.
						$this->db_add_param($this->mimetype).', '.
						$this->db_add_param($this->erstelltam).', '.
						$this->db_add_param($this->gedruckt, FHC_BOOLEAN).', '.
						$this->db_add_param($this->titel).', '.
						$this->db_add_param($this->bezeichnung).', '.
						$this->db_add_param($this->updateamum).', '.
						$this->db_add_param($this->updatevon).', '.
						$this->db_add_param($this->insertamum).', '.
						$this->db_add_param($this->insertvon).', '.
						$this->db_add_param($this->uid).','.
						$this->db_add_param($this->dms_id, FHC_INTEGER).','.
						$this->db_add_param($this->nachgereicht, FHC_BOOLEAN).','.
						$this->db_add_param($this->anmerkung).','.
						$this->db_add_param($this->titel_intern).','.
						$this->db_add_param($this->anmerkung_intern).','.
						$this->db_add_param($this->nachgereicht_am).','.
						$this->db_add_param($this->ausstellungsnation).','.
						$this->db_add_param($this->formal_geprueft_amum).','.
						$this->db_add_param($this->archiv, FHC_BOOLEAN).','.
						$this->db_add_param($this->signiert, FHC_BOOLEAN).','.
						$this->db_add_param($this->stud_selfservice, FHC_BOOLEAN).','.
						$this->db_add_param($this->akzeptiertamum).');';
		}
		else
		{
			//Bestehenden Datensatz aktualisieren
			$qry= "UPDATE public.tbl_akte SET".
				" person_id=".$this->db_add_param($this->person_id, FHC_INTEGER).",".
				" dokument_kurzbz=".$this->db_add_param($this->dokument_kurzbz).",".
				" inhalt=".$this->db_add_param($this->inhalt).",".
				" mimetype=".$this->db_add_param($this->mimetype).",".
				" erstelltam=".$this->db_add_param($this->erstelltam).",".
				" gedruckt=".$this->db_add_param($this->gedruckt,FHC_BOOLEAN).",".
				" titel=".$this->db_add_param($this->titel).",".
				" bezeichnung=".$this->db_add_param($this->bezeichnung).",".
				" updateamum=".$this->db_add_param($this->updateamum).",".
				" updatevon=".$this->db_add_param($this->updatevon).",".
				" uid=".$this->db_add_param($this->uid).",".
				" dms_id=".$this->db_add_param($this->dms_id, FHC_INTEGER).",".
				" nachgereicht=".$this->db_add_param($this->nachgereicht, FHC_BOOLEAN).",".
				" anmerkung=".$this->db_add_param($this->anmerkung).",".
				" titel_intern=".$this->db_add_param($this->titel_intern).",".
				" anmerkung_intern=".$this->db_add_param($this->anmerkung_intern).",".
				" nachgereicht_am=".$this->db_add_param($this->nachgereicht_am).",".
				" ausstellungsnation=".$this->db_add_param($this->ausstellungsnation).",".
				" formal_geprueft_amum=".$this->db_add_param($this->formal_geprueft_amum).",".
				" archiv=".$this->db_add_param($this->archiv, FHC_BOOLEAN).",".
				" signiert=".$this->db_add_param($this->signiert, FHC_BOOLEAN).",".
				" stud_selfservice=".$this->db_add_param($this->stud_selfservice, FHC_BOOLEAN).",".
				" akzeptiertamum=".$this->db_add_param($this->akzeptiertamum).
				" WHERE akte_id=".$this->db_add_param($this->akte_id, FHC_INTEGER);
		}

		if($this->db_query($qry))
		{
			if($new)
			{
				$qry = "SELECT currval('public.tbl_akte_akte_id_seq') as id";
				if($this->db_query($qry))
				{
					if($row = $this->db_fetch_object())
					{
						$this->akte_id = $row->id;
						$this->db_query('COMMIT;');
						return true;
					}
					else
					{
						$this->errormsg = 'Fehler beim Auslesen der Sequence';
						$this->db_query('ROLLBACK');
						return false;
					}
				}
				else
				{
					$this->errormsg = 'Fehler beim Auslesen der Sequence';
					$this->db_query('ROLLBACK');
					return false;
				}
			}
			else
				return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern des Datensatzes';
			return false;
		}
	}

	/**
	 * Liefert die Akten einer Person
	 *
	 * @param $person_id
	 * @param $dokument_kurzbz
	 * @param $stg_kz -> wenn gesetzt werden nur Akten angezeigt die ZUSÄTZLICH zum Studiengang abgegeben worden sind ohne Zeugnis
	 * @param $prestudent_id -> gesetzt wenn auch stg_kz gesetzt ist um sicherzugehen, dass Akten, die er schon für seinen Studiengang abgegeben hat,
	 * nicht mehr angezeigt werden
	 * @param boolean $returnInhalt Wenn true, wird auch den Inhalt (base64-Code) geladen, sonst nur allgemeine Informationen
	 * @param string $order Sortierreihenfolge im SQL
	 * @return true wenn ok, sonst false
	 */
	public function getAkten($person_id, $dokument_kurzbz = null, $stg_kz = null, $prestudent_id = null, $returnInhalt = false, $order = 'erstelltam')
	{
		$qry = "SELECT
					akte_id, person_id, dokument_kurzbz, mimetype, erstelltam, gedruckt, titel_intern, anmerkung_intern,
					titel, bezeichnung, updateamum, insertamum, updatevon, insertvon, uid, dms_id, anmerkung, nachgereicht,
					CASE WHEN inhalt is not null THEN true ELSE false END as inhalt_vorhanden,
					nachgereicht_am, ausstellungsnation, formal_geprueft_amum, archiv, signiert, stud_selfservice, akzeptiertamum";
		if($returnInhalt === true)
		{
			$qry .= ",inhalt ";
		}
		
		$qry.=" FROM public.tbl_akte WHERE person_id=".$this->db_add_param($person_id, FHC_INTEGER);
		if($dokument_kurzbz != '')
		{
			$qry .= " AND dokument_kurzbz=".$this->db_add_param($dokument_kurzbz);
		}
		if($stg_kz != null && $prestudent_id != null)
		{
			$qry .= " AND dokument_kurzbz not in (SELECT dokument_kurzbz FROM public.tbl_dokument JOIN public.tbl_dokumentstudiengang USING(dokument_kurzbz)
				WHERE studiengang_kz= ".$this->db_add_param($stg_kz).") AND dokument_kurzbz NOT IN ('Zeugnis','DiplSupp','Bescheid') AND dokument_kurzbz NOT IN
				(SELECT dokument_kurzbz FROM public.tbl_dokumentprestudent JOIN public.tbl_dokument USING(dokument_kurzbz)
				WHERE prestudent_id=".$this->db_add_param($prestudent_id).")";
		}

		if ($order != '')
		{
			$qry .= " ORDER BY ".$order;
		}
		//echo $qry;
		$this->errormsg = $qry;

		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$akten = new akte();

				$akten->akte_id = $row->akte_id;
				$akten->person_id = $row->person_id;
				$akten->dokument_kurzbz = $row->dokument_kurzbz;
				if($returnInhalt === true)
					$akten->inhalt = $row->inhalt;
				
				$akten->inhalt_vorhanden = $this->db_parse_bool($row->inhalt_vorhanden);
				$akten->mimetype = $row->mimetype;
				$akten->erstelltam = $row->erstelltam;
				$akten->gedruckt = $this->db_parse_bool($row->gedruckt);
				$akten->titel = $row->titel;
				$akten->bezeichnung = $row->bezeichnung;
				$akten->updateamum = $row->updateamum;
				$akten->updatevon = $row->updatevon;
				$akten->insertamum = $row->insertamum;
				$akten->insertvon = $row->insertvon;
				$akten->uid = $row->uid;
				$akten->dms_id = $row->dms_id;
				$akten->nachgereicht = $this->db_parse_bool($row->nachgereicht);
				$akten->anmerkung = $row->anmerkung;
				$akten->titel_intern = $row->titel_intern;
				$akten->anmerkung_intern = $row->anmerkung_intern;
				$akten->nachgereicht_am = $row->nachgereicht_am;
				$akten->ausstellungsnation = $row->ausstellungsnation;
				$akten->formal_geprueft_amum = $row->formal_geprueft_amum;
				$akten->archiv = $this->db_parse_bool($row->archiv);
				$akten->signiert = $this->db_parse_bool($row->signiert);
				$akten->stud_selfservice = $this->db_parse_bool($row->stud_selfservice);
				$akten->akzeptiertamum = $row->akzeptiertamum;

				$this->result[] = $akten;
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
	 * Liefert die Akten die ein Outgoing sehen darf
	 *
	 * @param $person_id
	 * @return true wenn ok, sonst false
	 */
	public function getAktenOutgoing($person_id)
	{
		$qry = "SELECT
			akte_id, person_id, dokument_kurzbz, mimetype, erstelltam, gedruckt,
			titel, bezeichnung, updateamum, insertamum, updatevon, insertvon, uid,
			dms_id,nachgereicht,anmerkung,titel_intern,anmerkung_intern, nachgereicht_am,
			ausstellungsnation, formal_geprueft_amum, archiv, signiert, stud_selfservice
			FROM public.tbl_akte WHERE person_id=".$this->db_add_param($person_id, FHC_INTEGER);

		$qry.=" AND dokument_kurzbz IN ('Lebenslf','Motivat','LearnAgr')";
		$qry.=" ORDER BY erstelltam";

		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$akten = new akte();

				$akten->akte_id = $row->akte_id;
				$akten->person_id = $row->person_id;
				$akten->dokument_kurzbz = $row->dokument_kurzbz;
				//$akte->inhalt = $row->inhalt;
				$akten->mimetype = $row->mimetype;
				$akten->erstelltam = $row->erstelltam;
				$akten->gedruckt = $this->db_parse_bool($row->gedruckt);
				$akten->titel = $row->titel;
				$akten->bezeichnung = $row->bezeichnung;
				$akten->updateamum = $row->updateamum;
				$akten->updatevon = $row->updatevon;
				$akten->insertamum = $row->insertamum;
				$akten->insertvon = $row->insertvon;
				$akten->uid = $row->uid;
				$akten->dms_id = $row->dms_id;
				$akten->nachgereicht = $this->db_parse_bool($row->nachgereicht);
				$akten->anmerkung = $row->anmerkung;
				$akten->titel_intern = $row->titel_intern;
				$akten->anmerkung_intern = $row->anmerkung_intern;
				$akten->nachgereicht_am = $row->nachgereicht_am;
				$akten->ausstellungsnation = $row->ausstellungsnation;
				$akten->formal_geprueft_amum = $row->formal_geprueft_amum;
				$akten->archiv = $this->db_parse_bool($row->archiv);
				$akten->signiert = $this->db_parse_bool($row->signiert);
				$akten->stud_selfservice = $this->db_parse_bool($row->stud_selfservice);
				$akten->akzeptiertamum = $row->akzeptiertamum;

				$this->result[] = $akten;
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
	 * Liefert die Akten anhand der dms_id
	 *
	 * @param $person_id
	 * @return true wenn ok, sonst false
	 */
	public function getAktenDms($dms_id)
	{
		$qry = "SELECT
					akte_id, person_id, dokument_kurzbz, mimetype, erstelltam, gedruckt,
					titel, bezeichnung, updateamum, insertamum, updatevon, insertvon, uid,
					dms_id,nachgereicht,anmerkung,titel_intern,anmerkung_intern, nachgereicht_am,
					ausstellungsnation, formal_geprueft_amum, archiv, signiert, stud_selfservice, akzeptiertamum
				FROM public.tbl_akte WHERE dms_id=".$this->db_add_param($dms_id, FHC_INTEGER)."
				ORDER BY erstelltam";

		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$akten = new akte();

				$akten->akte_id = $row->akte_id;
				$akten->person_id = $row->person_id;
				$akten->dokument_kurzbz = $row->dokument_kurzbz;
				//$akte->inhalt = $row->inhalt;
				$akten->mimetype = $row->mimetype;
				$akten->erstelltam = $row->erstelltam;
				$akten->gedruckt = $this->db_parse_bool($row->gedruckt);
				$akten->titel = $row->titel;
				$akten->bezeichnung = $row->bezeichnung;
				$akten->updateamum = $row->updateamum;
				$akten->updatevon = $row->updatevon;
				$akten->insertamum = $row->insertamum;
				$akten->insertvon = $row->insertvon;
				$akten->uid = $row->uid;
				$akten->dms_id = $row->dms_id;
				$akten->nachgereicht = $this->db_parse_bool($row->nachgereicht);
				$akten->anmerkung = $row->anmerkung;
				$akten->titel_intern = $row->titel_intern;
				$akten->anmerkung_intern = $row->anmerkung_intern;
				$akten->nachgereicht_am = $row->nachgereicht_am;
				$akten->ausstellungsnation = $row->ausstellungsnation;
				$akten->formal_geprueft_amum = $row->formal_geprueft_amum;
				$akten->archiv = $this->db_parse_bool($row->archiv);
				$akten->signiert = $this->db_parse_bool($row->signiert);
				$akten->stud_selfservice = $this->db_parse_bool($row->stud_selfservice);
				$akten->akzeptiertamum = $row->akzeptiertamum;

				$this->result[] = $akten;
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
	 * Liefert die Archivdokumente einer Person
	 *
	 * @param $person_id ID der Person.
	 * @param $signiert Boolean Wenn true werden nur Dokumente geliefert die digital signiert wurden.
	 * @param $stud_selfservice Boolean Wenn true werden nur Dokumente geliefert die Studierende selbst herunterladen duerfen.
	 * @return true wenn ok, sonst false
	 */
	public function getArchiv($person_id, $signiert = null, $stud_selfservice = null)
	{
		$qry = "
			SELECT
				akte_id, person_id, dokument_kurzbz, mimetype, erstelltam, gedruckt, titel_intern, anmerkung_intern,
				titel, bezeichnung, updateamum, insertamum, updatevon, insertvon, uid, dms_id, anmerkung, nachgereicht,
				CASE WHEN inhalt is not null THEN true ELSE false END as inhalt_vorhanden,
				nachgereicht_am, ausstellungsnation, formal_geprueft_amum, archiv, signiert, stud_selfservice, akzeptiertamum
			FROM
				public.tbl_akte
			WHERE
				person_id=".$this->db_add_param($person_id, FHC_INTEGER)."
				AND archiv = true";

		if(!is_null($signiert))
			$qry.=" AND signiert=".($signiert?'true':'false');
		if(!is_null($stud_selfservice))
			$qry.=" AND stud_selfservice=".($stud_selfservice?'true':'false');

		$qry.=" ORDER BY erstelltam DESC";

		$this->errormsg = $qry;

		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$akten = new akte();

				$akten->akte_id = $row->akte_id;
				$akten->person_id = $row->person_id;
				$akten->dokument_kurzbz = $row->dokument_kurzbz;
				//$akte->inhalt = $row->inhalt;
				$akten->inhalt_vorhanden = $this->db_parse_bool($row->inhalt_vorhanden);
				$akten->mimetype = $row->mimetype;
				$akten->erstelltam = $row->erstelltam;
				$akten->gedruckt = $this->db_parse_bool($row->gedruckt);
				$akten->titel = $row->titel;
				$akten->bezeichnung = $row->bezeichnung;
				$akten->updateamum = $row->updateamum;
				$akten->updatevon = $row->updatevon;
				$akten->insertamum = $row->insertamum;
				$akten->insertvon = $row->insertvon;
				$akten->uid = $row->uid;
				$akten->dms_id = $row->dms_id;
				$akten->nachgereicht = $this->db_parse_bool($row->nachgereicht);
				$akten->anmerkung = $row->anmerkung;
				$akten->titel_intern = $row->titel_intern;
				$akten->anmerkung_intern = $row->anmerkung_intern;
				$akten->nachgereicht_am = $row->nachgereicht_am;
				$akten->ausstellungsnation = $row->ausstellungsnation;
				$akten->formal_geprueft_amum = $row->formal_geprueft_amum;
				$akten->archiv = $this->db_parse_bool($row->archiv);
				$akten->signiert = $this->db_parse_bool($row->signiert);
				$akten->stud_selfservice = $this->db_parse_bool($row->stud_selfservice);
				$akten->akzeptiertamum = $row->akzeptiertamum;

				$this->result[] = $akten;
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
