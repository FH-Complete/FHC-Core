<?php
/* Copyright (C) 2014 fhcomplete.org
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
 * Authors: Nikolaus Krondraf <nikolaus.krondraf@technikum-wien.at>,
 *          Andreas Österreicher <andreas.oesterreicher@technikum-wien.at>
 */
require_once(dirname(__FILE__).'/basis_db.class.php');
require_once(dirname(__FILE__).'/functions.inc.php');

class vertrag extends basis_db
{
	public $new=true;
	public $result = array();

	public $vertragstyp_bezeichnung;

	public $vertrag_id;			// bigint
	public $bezeichnung;		// varchar(256)
	public $person_id;			// bigint
	public $vertragstyp_kurzbz; // varchar(32)
	public $betrag; 			// numeric(8,2)
	public $insertamum;			// timestamp
	public $insertvon;			// varchar(32)
	public $updateamum;			// timestamp
	public $updatevon;			// varchar(32)
	public $ext_id; 			// bigint
	public $anmerkung; 			// text
	public $vertragsdatum;		// date
	public $lehrveranstaltung_id; // integer

    /**
	 * Konstruktor
	 */
	public function __construct()
	{
		parent::__construct();
	}

	public function load($vertrag_id)
	{
		$qry = "SELECT * FROM lehre.tbl_vertrag WHERE vertrag_id=".$this->db_add_param($vertrag_id, FHC_INTEGER);

		if($result = $this->db_query($qry))
		{
			if($row = $this->db_fetch_object($result))
			{
				$this->vertrag_id = $row->vertrag_id;
				$this->vertragstyp_kurzbz = $row->vertragstyp_kurzbz;
				$this->bezeichnung = $row->bezeichnung;
				$this->betrag = $row->betrag;
				$this->person_id = $row->person_id;
				$this->anmerkung = $row->anmerkung;
				$this->vertragsdatum = $row->vertragsdatum;
				$this->lehrveranstaltung_id = $row->lehrveranstaltung_id;
				$this->vertragsstunden = $row->vertragsstunden;
				$this->vertragsstunden_studiensemester_kurzbz = $row->vertragsstunden_studiensemester_kurzbz;

				$this->new=false;

				return true;
			}
			else
			{
				$this->errormsg = 'Eintrag wurde nicht gefunden';
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
	 * Laedt die Verträge einer Person
	 * @param $person_id
	 * @return boolean true wenn ok ,false im Fehlerfall
	 */
	public function loadVertrag($person_id, $abgerechnet=null, $datum=null)
	{
		$qry = "SELECT
					*,
					tbl_vertrag.bezeichnung as bezeichnung,
					tbl_vertragstyp.bezeichnung as vertragstyp_bezeichnung,
					(SELECT bezeichnung FROM lehre.tbl_vertragsstatus JOIN lehre.tbl_vertrag_vertragsstatus USING(vertragsstatus_kurzbz)
						WHERE vertrag_id=tbl_vertrag.vertrag_id ORDER BY datum desc limit 1) as status
				FROM
					lehre.tbl_vertrag
					LEFT JOIN lehre.tbl_vertragstyp USING(vertragstyp_kurzbz)
				WHERE person_id=".$this->db_add_param($person_id);

		if($abgerechnet===true)
			$qry.=" AND EXISTS (SELECT 1 FROM lehre.tbl_vertrag_vertragsstatus WHERE vertrag_id=tbl_vertrag.vertrag_id AND vertragsstatus_kurzbz='abgerechnet')";
		if($abgerechnet===false)
			$qry.=" AND NOT EXISTS (SELECT 1 FROM lehre.tbl_vertrag_vertragsstatus WHERE vertrag_id=tbl_vertrag.vertrag_id AND vertragsstatus_kurzbz='abgerechnet')";

		if(!is_null($datum))
		{
			$qry.=" AND NOT
						(
							vertragstyp_kurzbz='Lehrauftrag'
							AND EXISTS(SELECT
											1
										FROM
											lehre.tbl_lehreinheitmitarbeiter
											JOIN lehre.tbl_lehreinheit USING(lehreinheit_id)
										WHERE
											vertrag_id=tbl_vertrag.vertrag_id
											AND studiensemester_kurzbz in (SELECT studiensemester_kurzbz FROM public.tbl_studiensemester WHERE start>=".$this->db_add_param($datum).")
								)
						)";
		}

		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				$obj = new vertrag();

				$obj->vertrag_id = $row->vertrag_id;
				$obj->bezeichnung = $row->bezeichnung;
				$obj->person_id = $row->person_id;
				$obj->vertragstyp_kurzbz = $row->vertragstyp_kurzbz;
				$obj->betrag = $row->betrag;
				$obj->insertamum = $row->insertamum;
				$obj->insertvon = $row->insertvon;
				$obj->updateamum = $row->updateamum;
				$obj->updatevon = $row->updatevon;
				$obj->status = $row->status;
				$obj->anmerkung = $row->anmerkung;
				$obj->vertragsdatum = $row->vertragsdatum;
				$obj->lehrveranstaltung_id = $row->lehrveranstaltung_id;
				$obj->vertragsstunden = $row->vertragsstunden;
				$obj->vertragsstunden_studiensemester_kurzbz = $row->vertragsstunden_studiensemester_kurzbz;

				$obj->vertragstyp_bezeichnung = $row->vertragstyp_bezeichnung;

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
	 * Speichert den Vertragstyp in der Datenbank
	 * Wenn $new auf true gesetzt ist wird ein neuer Datensatz
	 * angelegt, ansonsten der Datensatz upgedated
	 * @return true wenn erfolgreich, false im Fehlerfall
	 */
	public function saveVertragstyp($new=null)
	{
		if(is_null($new))
			$new = $this->new;

		if($this->vertragstyp_kurzbz=='')
		{
			$this->errormsg = 'Vertragstyp_kurzbz muss angegeben werden';
			return false;
		}

		if($new)
		{
			//Prüfung, ob Eintrag bereits vorhanden
			$qry='SELECT vertragstyp_kurzbz FROM lehre.tbl_vertragstyp
				WHERE vertragstyp_kurzbz='.$this->db_add_param($this->vertragstyp_kurzbz);
			if($this->db_query($qry))
			{
				if($this->db_fetch_object())
				{
					$this->errormsg = 'Eintrag bereits vorhanden';
					return false;
				}
			}
			else
			{
				$this->errormsg = 'Fehler beim Durchführen der Datenbankabfrage';
				return false;
			}
		}

		if($new)
		{
			$qry = 'INSERT INTO lehre.tbl_vertragstyp(vertragstyp_kurzbz, bezeichnung) VALUES('.
			        $this->db_add_param($this->vertragstyp_kurzbz).','.
			        $this->db_add_param($this->vertragstyp_bezeichnung).');';
		}
		else
		{
			$qry = 'UPDATE lehre.tbl_vertragstyp SET '.
					'bezeichnung = '.$this->db_add_param($this->vertragstyp_bezeichnung).
					'WHERE vertragstyp_kurzbz = '.$this->db_add_param($this->vertragstyp_kurzbz);
		}

		if($this->db_query($qry))
		{
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern des Vertragstyps';
			return false;
		}
	}

	/**
	 * Loescht einen Vertragstyp wenn er noch nicht verwendet wird
	 * @param vertragstyp_kurzbz
	 */
	public function deleteVertragstyp($vertragstyp_kurzbz)
	{
		// prüfen ob Vertrag bereits verwendet wird
		$qry = "SELECT vertragstyp_kurzbz FROM lehre.tbl_vertrag
			WHERE vertragstyp_kurzbz = " . $this->db_add_param($vertragstyp_kurzbz);

		if($this->db_query($qry))
		{
			if($this->db_fetch_object())
			{
				$this->errormsg = "Der Vertragstyp kann nicht gelöscht werden da er bereits verwendet wird";
				return false;
			}
		}
		else
		{
			$this->errormsg = "Fehler beim Durchführen der Datenbankabfrage";
			return false;
		}

                if(is_null($vertragstyp_kurzbz))
		{
			$this->errormsg = 'Vertragstyp_kurzbz darf nicht leer sein';
			return false;
		}

		$qry = "DELETE FROM lehre.tbl_vertragstyp
				WHERE vertragstyp_kurzbz=".$this->db_add_param($vertragstyp_kurzbz).";";

		if($this->db_query($qry))
			return true;
		else
		{
			$this->errormsg = 'Fehler beim Loeschen des Vertragstyps';
			return false;
		}
	}

	/**
	 * Liefert alle Vertragsytpen
	 * @return true wenn ok false im Fehlerfall
	 */
	public function getAllVertragstypen()
	{
		$qry = "SELECT * FROM lehre.tbl_vertragstyp ORDER BY bezeichnung;";

		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$vertrag = new vertrag();

				$vertrag->vertragstyp_kurzbz = $row->vertragstyp_kurzbz;
				$vertrag->vertragstyp_bezeichnung = $row->bezeichnung;

				$this->result[] = $vertrag;
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
	 * Laedt einen Vertragstyp
	 *
	 * @param $vertragstyp_kurzbz
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function loadVertragstyp($vertragstyp_kurzbz)
	{
		$qry="SELECT * FROM lehre.tbl_vertragstyp
				WHERE
					vertragstyp_kurzbz=".$this->db_add_param($vertragstyp_kurzbz);

		if($result = $this->db_query($qry))
		{
			if($row = $this->db_fetch_object($result))
			{
				$this->vertragstyp_kurzbz = $row->vertragstyp_kurzbz;
				$this->vertragstyp_bezeichnung = $row->bezeichnung;
				return true;
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
	 * Laedt die Vertragselemente die noch keinem Vertrag zugeordnet sind
	 * @param $person_id
	 * @return boolean true wenn ok ,false im Fehlerfall
	 */
	public function loadNichtZugeordnet($person_id)
	{
		/*
			Lehrauftraege
			UNION
			Betreuungen
			UNION
			Pruefungen
		*/
		$qry = "
		SELECT
			'Lehrauftrag' as type,
			lehreinheit_id,
			mitarbeiter_uid,
			null as pruefung_id,
			null as projektarbeit_id,
			(tbl_lehreinheitmitarbeiter.semesterstunden*tbl_lehreinheitmitarbeiter.stundensatz) as betrag,
			tbl_lehreinheit.studiensemester_kurzbz,
			null as betreuerart_kurzbz,
			(	SELECT
					upper(tbl_studiengang.typ || tbl_studiengang.kurzbz) || tbl_lehrveranstaltung.semester  || '-' || tbl_lehrveranstaltung.kurzbz || '-' || tbl_lehreinheit.lehrform_kurzbz
				FROM
					lehre.tbl_lehrveranstaltung
					JOIN public.tbl_studiengang USING(studiengang_kz)
				WHERE
					lehrveranstaltung_id=tbl_lehreinheit.lehrveranstaltung_id)
			as bezeichnung
		FROM
			lehre.tbl_lehreinheitmitarbeiter
			JOIN lehre.tbl_lehreinheit USING(lehreinheit_id)
		WHERE
			mitarbeiter_uid IN (SELECT uid FROM public.tbl_benutzer WHERE person_id=".$this->db_add_param($person_id, FHC_INTEGER).")
			AND vertrag_id IS NULL
		UNION
		SELECT
			'Betreuung' as type,
			tbl_projektarbeit.lehreinheit_id as lehreinheit_id,
			null as mitarbeiter_uid,
			null::integer as pruefung_id,
			projektarbeit_id,
			(tbl_projektbetreuer.stunden*tbl_projektbetreuer.stundensatz) as betrag,
			tbl_lehreinheit.studiensemester_kurzbz,
			tbl_projektbetreuer.betreuerart_kurzbz,
			(SELECT nachname || ' ' || vorname FROM public.tbl_person JOIN public.tbl_benutzer USING(person_id) WHERE uid=tbl_projektarbeit.student_uid)
			as bezeichnung
		FROM
			lehre.tbl_projektbetreuer
			JOIN lehre.tbl_projektarbeit USING(projektarbeit_id)
			JOIN lehre.tbl_lehreinheit USING(lehreinheit_id)
		WHERE
			tbl_projektbetreuer.person_id=".$this->db_add_param($person_id, FHC_INTEGER)."
			AND vertrag_id IS NULL";
			/*
		UNION
		SELECT
			'Pruefung' as type,
			lehreinheit_id,
			mitarbeiter_uid,
			pruefung_id,
			null as projektarbeit_id,
			'".PRUEFUNGSHONORAR."' as betrag,
			tbl_lehreinheit.studiensemester_kurzbz AS studiensemester_kurzbz,
			null as betreuerart_kurzbz,
			(	SELECT
					nachname || ' ' || vorname || ' ' || (SELECT kurzbz FROM lehre.tbl_lehrveranstaltung WHERE lehrveranstaltung_id=tbl_lehreinheit.lehrveranstaltung_id)
				FROM
					public.tbl_person
					JOIN public.tbl_benutzer USING(person_id)
				WHERE uid=tbl_pruefung.student_uid)
			as bezeichnung
		FROM
			lehre.tbl_pruefung
			JOIN lehre.tbl_lehreinheit USING(lehreinheit_id)
		WHERE
			mitarbeiter_uid IN (SELECT uid FROM public.tbl_benutzer WHERE person_id=".$this->db_add_param($person_id, FHC_INTEGER).")
			AND vertrag_id IS NULL
		";*/
		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				$obj = new stdClass();

				$obj->type = $row->type;
				$obj->lehreinheit_id = $row->lehreinheit_id;
				$obj->mitarbeiter_uid = $row->mitarbeiter_uid;
				$obj->projektarbeit_id = $row->projektarbeit_id;
				$obj->pruefung_id = $row->pruefung_id;
				$obj->betrag = $row->betrag;
				$obj->studiensemester_kurzbz = $row->studiensemester_kurzbz;
				$obj->betreuerart_kurzbz = $row->betreuerart_kurzbz;
				$obj->bezeichnung = $row->bezeichnung;
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
	 * Laedt alle Personen die noch Lehraufträge haben die keinem
	 * Vertrag zugeordnet sind
	 * @param $studiensemester_kurzbz
	 * @return boolean true wenn ok, false im Fehlerfall
	 */
	public function loadPersonenNichtZugeordnet($studiensemester_kurzbz)
	{
		$db = new basis_db();
		// Alle Personen holen die Lehraufträge haben die noch keinen Verträge zugeordnet sind
		$qry = "SELECT
					distinct tbl_person.person_id, tbl_benutzer.uid, tbl_person.vorname, tbl_person.nachname,
					tbl_person.svnr, tbl_person.titelpre, tbl_person.titelpost
				FROM
					public.tbl_mitarbeiter
					JOIN public.tbl_benutzer ON(uid=mitarbeiter_uid)
					JOIN public.tbl_person USING(person_id)
				WHERE
					tbl_mitarbeiter.fixangestellt = false
					AND EXISTS(SELECT 1 FROM lehre.tbl_lehreinheit JOIN lehre.tbl_lehreinheitmitarbeiter USING(lehreinheit_id)
						WHERE
							tbl_lehreinheitmitarbeiter.mitarbeiter_uid=tbl_mitarbeiter.mitarbeiter_uid
							AND tbl_lehreinheitmitarbeiter.vertrag_id IS NULL
							AND tbl_lehreinheit.studiensemester_kurzbz=".$db->db_add_param($studiensemester_kurzbz)."
							)
					OR
					EXISTS (SELECT 1 FROM lehre.tbl_projektbetreuer
								JOIN lehre.tbl_projektarbeit USING(projektarbeit_id)
								JOIN lehre.tbl_lehreinheit USING(lehreinheit_id)
							WHERE
								tbl_lehreinheit.studiensemester_kurzbz=".$db->db_add_param($studiensemester_kurzbz)."
								AND tbl_projektbetreuer.person_id=tbl_person.person_id
								AND tbl_projektbetreuer.vertrag_id IS NULL
							)
					";

		if($result = $db->db_query($qry))
		{
			while($row = $db->db_fetch_object($result))
			{
				$obj = new stdClass();
				$obj->person_id = $row->person_id;
				$obj->vorname = $row->vorname;
				$obj->nachname = $row->nachname;
				$obj->uid = $row->uid;
				$obj->titelpre = $row->titelpre;
				$obj->titelpost = $row->titelpost;
				$obj->svnr = $row->svnr;
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
	 * Laedt die Vertragselemente die einem Vertrag zugeordnet sind
	 * @param $vertrag_id
	 * @return boolean true wenn ok ,false im Fehlerfall
	 */
	public function loadZugeordnet($vertrag_id)
	{
		/*
			Lehrauftraege
			UNION
			Betreuungen
			UNION
			Pruefungen
		*/
		$qry = "
		SELECT
			'Lehrauftrag' as type,
			lehreinheit_id,
			mitarbeiter_uid,
			null as pruefung_id,
			null as projektarbeit_id,
			(tbl_lehreinheitmitarbeiter.semesterstunden*tbl_lehreinheitmitarbeiter.stundensatz) as betrag,
			tbl_lehreinheit.studiensemester_kurzbz,
			null as betreuerart_kurzbz,
			(	SELECT
					upper(tbl_studiengang.typ || tbl_studiengang.kurzbz) || tbl_lehrveranstaltung.semester  || '-' || tbl_lehrveranstaltung.kurzbz || '-' || tbl_lehreinheit.lehrform_kurzbz
				FROM
					lehre.tbl_lehrveranstaltung
					JOIN public.tbl_studiengang USING(studiengang_kz)
				WHERE
					lehrveranstaltung_id=tbl_lehreinheit.lehrveranstaltung_id)
			as bezeichnung,
			vertragsstunden,
    		vertragsstunden_studiensemester_kurzbz
		FROM
			lehre.tbl_lehreinheitmitarbeiter
			JOIN lehre.tbl_lehreinheit USING(lehreinheit_id)
			JOIN lehre.tbl_vertrag USING (vertrag_id)
		WHERE
			vertrag_id=".$this->db_add_param($vertrag_id, FHC_INTEGER)."
		UNION
		SELECT
			'Betreuung' as type,
			tbl_projektarbeit.lehreinheit_id as lehreinheit_id,
			null as mitarbeiter_uid,
			null::integer as pruefung_id,
			projektarbeit_id,
			(tbl_projektbetreuer.stunden*tbl_projektbetreuer.stundensatz) as betrag,
			tbl_lehreinheit.studiensemester_kurzbz,
			tbl_projektbetreuer.betreuerart_kurzbz,
			(SELECT nachname || ' ' || vorname FROM public.tbl_person JOIN public.tbl_benutzer USING(person_id) WHERE uid=tbl_projektarbeit.student_uid)
			as bezeichnung,
			vertragsstunden,
    		vertragsstunden_studiensemester_kurzbz
		FROM
			lehre.tbl_projektbetreuer
			JOIN lehre.tbl_projektarbeit USING(projektarbeit_id)
			JOIN lehre.tbl_lehreinheit USING(lehreinheit_id)
			JOIN lehre.tbl_vertrag USING (vertrag_id)
		WHERE
			vertrag_id=".$this->db_add_param($vertrag_id, FHC_INTEGER).";";
/*		UNION
		SELECT
			'Pruefung' as type,
			lehreinheit_id,
			mitarbeiter_uid,
			pruefung_id,
			null as projektarbeit_id,
			'".PRUEFUNGSHONORAR."' as betrag,
			tbl_lehreinheit.studiensemester_kurzbz AS studiensemester_kurzbz,
			null as betreuerart_kurzbz,
			(	SELECT
					nachname || ' ' || vorname || ' ' || (SELECT kurzbz FROM lehre.tbl_lehrveranstaltung WHERE lehrveranstaltung_id=tbl_lehreinheit.lehrveranstaltung_id)
				FROM
					public.tbl_person
					JOIN public.tbl_benutzer USING(person_id)
				WHERE uid=tbl_pruefung.student_uid)
			as bezeichnung
		FROM
			lehre.tbl_pruefung
			JOIN lehre.tbl_lehreinheit USING(lehreinheit_id)
		WHERE
			vertrag_id=".$this->db_add_param($vertrag_id, FHC_INTEGER);
*/
		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				$obj = new stdClass();

				$obj->type = $row->type;
				$obj->lehreinheit_id = $row->lehreinheit_id;
				$obj->mitarbeiter_uid = $row->mitarbeiter_uid;
				$obj->projektarbeit_id = $row->projektarbeit_id;
				$obj->pruefung_id = $row->pruefung_id;
				$obj->betrag = $row->betrag;
				$obj->studiensemester_kurzbz = $row->studiensemester_kurzbz;
				$obj->betreuerart_kurzbz = $row->betreuerart_kurzbz;
				$obj->bezeichnung = $row->bezeichnung;
				$obj->vertragsstunden = $row->vertragsstunden;
				$obj->vertragsstunden_studiensemester_kurzbz = $row->vertragsstunden_studiensemester_kurzbz;
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
	 * Speichert einen Vertrag
	 */
	public function save($new=null)
	{
		if(is_null($new))
			$new = $this->new;


		if($new)
		{
			$qry = "BEGIN;INSERT INTO lehre.tbl_vertrag(bezeichnung, person_id, vertragstyp_kurzbz, betrag, insertamum, insertvon,
					updateamum, updatevon, anmerkung, vertragsdatum,lehrveranstaltung_id) VALUES(".
					$this->db_add_param($this->bezeichnung).','.
					$this->db_add_param($this->person_id,FHC_INTEGER).','.
					$this->db_add_param($this->vertragstyp_kurzbz).','.
					$this->db_add_param($this->betrag).','.
					$this->db_add_param($this->insertamum).','.
					$this->db_add_param($this->insertvon).','.
					$this->db_add_param($this->updateamum).','.
					$this->db_add_param($this->updatevon).','.
					$this->db_add_param($this->anmerkung).','.
					$this->db_add_param($this->vertragsdatum).','.
					$this->db_add_param($this->lehrveranstaltung_id).');';

		}
		else
		{
			$qry = "UPDATE lehre.tbl_vertrag SET ".
			" bezeichnung=".$this->db_add_param($this->bezeichnung).','.
			" person_id=".$this->db_add_param($this->person_id, FHC_INTEGER).','.
			" vertragstyp_kurzbz=".$this->db_add_param($this->vertragstyp_kurzbz).','.
			" betrag=".$this->db_add_param($this->betrag).','.
			" updateamum=".$this->db_add_param($this->updateamum).','.
			" updatevon=".$this->db_add_param($this->updatevon).','.
			" anmerkung=".$this->db_add_param($this->anmerkung).','.
			" vertragsdatum=".$this->db_add_param($this->vertragsdatum).','.
			" lehrveranstaltung_id=".$this->db_add_param($this->lehrveranstaltung_id).
			" WHERE vertrag_id=".$this->db_add_param($this->vertrag_id, FHC_INTEGER,false);
		}

		if($this->db_query($qry))
		{
			if($new)
			{
				//Sequence auslesen
				$qry = "SELECT currval('lehre.seq_vertrag_vertrag_id') as id;";
				if($this->db_query($qry))
				{
					if($row = $this->db_fetch_object())
					{
						$this->vertrag_id = $row->id;
						$this->new=false;
						$this->db_query('COMMIT');
						return true;
					}
					else
					{
						$this->errormsg = 'Fehler beim Auslesen der Sequence';
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
			else
				return true;
		}
		else
		{
			$this->errormsg='Fehler beim Speichern der Daten';
			return false;
		}
	}

	/**
	 * Laedt alle Stati eines Vertrags
	 *
	 * @param $vertrag_id
	 * @return boolean
	 */
	public function getAllStatus($vertrag_id)
	{
		$qry="SELECT
					*
			FROM
				lehre.tbl_vertrag_vertragsstatus
				JOIN lehre.tbl_vertragsstatus USING(vertragsstatus_kurzbz)
			WHERE
				tbl_vertrag_vertragsstatus.vertrag_id=".$this->db_add_param($vertrag_id)."
			ORDER BY datum DESC";

		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				$obj = new stdClass();
				$obj->vertrag_id = $row->vertrag_id;
				$obj->vertragsstatus_kurzbz = $row->vertragsstatus_kurzbz;
				$obj->vertragsstatus_bezeichnung = $row->bezeichnung;
				$obj->datum = $row->datum;
				$obj->uid = $row->uid;
				$obj->insertvon = $row->insertvon;
				$obj->insertamum = $row->insertamum;
				$obj->updatevon = $row->updatevon;
				$obj->updateamum = $row->updateamum;

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
	 * Laedt den letzten Status eines Vertrags
	 *
	 * @param $vertrag_id
	 * @param $status optional
	 * @return boolean
	 */
	public function getStatus($vertrag_id, $status=NULL)
	{
		$qry="SELECT
					*
			FROM
				lehre.tbl_vertrag_vertragsstatus
				JOIN lehre.tbl_vertragsstatus USING(vertragsstatus_kurzbz)
			WHERE
				tbl_vertrag_vertragsstatus.vertrag_id=".$this->db_add_param($vertrag_id);

		if(!is_null($status))
		{
			$qry .= " AND tbl_vertrag_vertragsstatus.vertragsstatus_kurzbz=".$this->db_add_param($status);
		}

		$qry .= " ORDER BY datum DESC;";

		if($result = $this->db_query($qry))
		{
			if($row = $this->db_fetch_object($result))
			{
				$this->vertrag_id = $row->vertrag_id;
				$this->vertragsstatus_kurzbz = $row->vertragsstatus_kurzbz;
				$this->vertragsstatus_bezeichnung = $row->bezeichnung;
				$this->datum = $row->datum;
				$this->uid = $row->uid;
				$this->insertvon = $row->insertvon;
				$this->insertamum = $row->insertamum;
				$this->updatevon = $row->updatevon;
				$this->updateamum = $row->updateamum;
				$this->new=false;
				return true;
			}
			else
			{
				$this->errormsg = 'Eintrag wurde nicht gefunden';
				return false;
			}
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Daten';
			return false;
		}
	}

	public function saveVertragsstatus($new=null)
	{
		if(is_null($new))
			$new = $this->new;

		if($new)
		{
			$qry = "INSERT INTO lehre.tbl_vertrag_vertragsstatus(vertragsstatus_kurzbz, vertrag_id, uid, datum, insertvon, updatevon, updateamum) VALUES(".
					$this->db_add_param($this->vertragsstatus_kurzbz).','.
					$this->db_add_param($this->vertrag_id).','.
					$this->db_add_param($this->uid).','.
					$this->db_add_param($this->datum).','.
					$this->db_add_param($this->insertvon).','.
					$this->db_add_param($this->updatevon).','.
					$this->db_add_param($this->updateamum).');';
		}
		else
		{
		    $qry = "UPDATE lehre.tbl_vertrag_vertragsstatus"
			    . " SET updatevon=".$this->db_add_param($this->updatevon).','
			    . " updateamum=".$this->db_add_param($this->updateamum).','
			    . " datum=".$this->db_add_param($this->datum)
			    . " WHERE vertrag_id=".$this->db_add_param($this->vertrag_id)
			    . " AND vertragsstatus_kurzbz=".$this->db_add_param($this->vertragsstatus_kurzbz).";";
		}



		if($this->db_query($qry))
		{
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern der Daten';
			return false;
		}
	}

	/**
	 * Laedt alle Vorhandenen Vertragsstati
	 * @return boolean true wenn ok, false im Fehlerfall
	 */
	public function loadVertragsstatus()
	{
		$qry = "SELECT * FROM lehre.tbl_vertragsstatus ORDER BY bezeichnung";

		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				$obj = new stdClass();
				$obj->vertragsstatus_kurzbz = $row->vertragsstatus_kurzbz;
				$obj->bezeichnung = $row->bezeichnung;
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
	 * Laedt die uebergebenen Vertraege
	 * @param $vertrag_id_arr array mit VertragsIDs
	 * @return boolean
	 */
	public function getVertraege($vertrag_id_arr)
	{
		if(count($vertrag_id_arr)==0)
		{
			$this->result = array();
			return false;
		}
		$qry = "SELECT * FROM lehre.tbl_vertrag WHERE vertrag_id in (".$this->db_implode4SQL($vertrag_id_arr).')';

		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				$obj = new stdClass();

				$obj->vertrag_id = $row->vertrag_id;
				$obj->vertragstyp_kurzbz = $row->vertragstyp_kurzbz;
				$obj->bezeichnung = $row->bezeichnung;
				$obj->betrag = $row->betrag;
				$obj->person_id = $row->person_id;
				$obj->anmerkung = $row->anmerkung;
				$obj->vertragsdatum = $row->vertragsdatum;

				$this->result[]=$obj;
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
	 * Loescht einen Vertragsstatus
	 * @param vertrag_id
	 * @param vertragsstatus_kurzbz
	 * @return boolean
	 */
	public function deleteVertragsstatus($vertrag_id, $vertragsstatus_kurzbz)
	{
		// prüfen ob Vertrag bereits verwendet wird
		$qry = "DELETE FROM lehre.tbl_vertrag_vertragsstatus
			WHERE
				vertragsstatus_kurzbz=".$this->db_add_param($vertragsstatus_kurzbz)."
				AND vertrag_id=".$this->db_add_param($vertrag_id);

		if($this->db_query($qry))
		{
			return true;
		}
		else
		{
			$this->errormsg = "Fehler beim Loeschen des Eintrages";
			return false;
		}
	}

	/**
	 * Liefert die Vertraege zu einem Datum
	 *
	 */
	public function getVertragFromDatum($mitarbeiter_uid, $datum)
	{
		// Studiensemester zu Datum ermitteln
		$stsem_obj = new studiensemester();
		$stsem = $stsem_obj->getSemesterFromDatum($datum);

		// Vorheriges Studiensemester
		$prev = $stsem_obj->getPreviousFrom($stsem);
		$stsem_obj->load($prev);
		$prevstsemende = $stsem_obj->ende;

		// Alle Vertraege holen die in das Studiensemester fallen
		// (Lehreinheiten und Betreuungen)
		// plus Sonderhonorare die in diesem Zeitraum angelegt wurden
		$qry = "SELECT
					tbl_vertrag.*
				FROM
					lehre.tbl_vertrag
					JOIN public.tbl_benutzer USING(person_id)
				WHERE
					(
					EXISTS (SELECT
								1
							FROM
								lehre.tbl_lehreinheitmitarbeiter
								JOIN lehre.tbl_lehreinheit USING(lehreinheit_id)
							WHERE
								vertrag_id=tbl_vertrag.vertrag_id
								AND studiensemester_kurzbz=".$this->db_add_param($stsem).")
					OR
					EXISTS (SELECT
								1
							FROM
								lehre.tbl_projektbetreuer
								JOIN lehre.tbl_projektarbeit USING(projektarbeit_id)
								JOIN lehre.tbl_lehreinheit USING(lehreinheit_id)
							WHERE
								vertrag_id=tbl_vertrag.vertrag_id
								AND studiensemester_kurzbz=".$this->db_add_param($stsem).")
				 	OR
					(NOT EXISTS (SELECT
								1
							FROM
								lehre.tbl_lehreinheitmitarbeiter
							WHERE
								vertrag_id=tbl_vertrag.vertrag_id)
					AND NOT EXISTS (SELECT
											1
										FROM
											lehre.tbl_projektbetreuer
										WHERE
											vertrag_id=tbl_vertrag.vertrag_id)
					)
					)
					AND vertragsdatum<=".$this->db_add_param($datum)."
					AND
						(
							vertragsdatum>=".$this->db_add_param($prevstsemende)."
							OR
							EXISTS (SELECT
										1
									FROM
										lehre.tbl_lehreinheitmitarbeiter
										JOIN lehre.tbl_lehreinheit USING(lehreinheit_id)
									WHERE
										vertrag_id=tbl_vertrag.vertrag_id
										AND studiensemester_kurzbz=".$this->db_add_param($stsem).")
						)
					AND tbl_benutzer.uid=".$this->db_add_param($mitarbeiter_uid);

		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				$obj = new stdClass();

				$obj->vertrag_id = $row->vertrag_id;
				$obj->vertragstyp_kurzbz = $row->vertragstyp_kurzbz;
				$obj->bezeichnung = $row->bezeichnung;
				$obj->betrag = $row->betrag;
				$obj->person_id = $row->person_id;
				$obj->anmerkung = $row->anmerkung;
				$obj->vertragsdatum = $row->vertragsdatum;

				$this->result[]=$obj;
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
	 * Loescht einen Vertrag und seine Verbindungen
	 * @param $vertrag_id ID des Vertrags
	 */
	public function delete($vertrag_id)
	{
		$qry = "UPDATE lehre.tbl_lehreinheitmitarbeiter SET vertrag_id=null WHERE vertrag_id=".$this->db_add_param($vertrag_id, FHC_INTEGER).";
		UPDATE lehre.tbl_projektbetreuer SET vertrag_id=null WHERE vertrag_id=".$this->db_add_param($vertrag_id, FHC_INTEGER).";
		DELETE FROM lehre.tbl_vertrag_vertragsstatus WHERE vertrag_id=".$this->db_add_param($vertrag_id, FHC_INTEGER).";
		DELETE FROM lehre.tbl_vertrag WHERE vertrag_id=".$this->db_add_param($vertrag_id, FHC_INTEGER).";";

		if($this->db_query($qry))
			return true;
		else
		{
			$this->errormsg = 'Fehler beim Löschen der Daten';
			return false;
		}
	}

	/**
	 * Storniert einen Vertrag und seine Verbindungen
	 * @param $vertrag_id ID des Vertrags
	 * @param $mitarbeiter_uid
	 */
	public function cancel($vertrag_id, $mitarbeiter_uid)
	{
		$insertvon = get_uid();

		$qry = "
			UPDATE lehre.tbl_lehreinheitmitarbeiter SET vertrag_id=null WHERE vertrag_id=".$this->db_add_param($vertrag_id, FHC_INTEGER).";
			UPDATE lehre.tbl_projektbetreuer SET vertrag_id=null WHERE vertrag_id=".$this->db_add_param($vertrag_id, FHC_INTEGER).";
			INSERT INTO lehre.tbl_vertrag_vertragsstatus(vertragsstatus_kurzbz, vertrag_id, uid, datum, insertamum, insertvon)
				VALUES(".
					$this->db_qoute('storno'). ", ".
					$this->db_add_param($vertrag_id, FHC_INTEGER). ", ".
					$this->db_add_param($mitarbeiter_uid). ", ".
					$this->db_qoute('NOW()'). ", ".
					$this->db_qoute('NOW()'). ", ".
					$this->db_qoute($insertvon). "
				);
			";

		if($this->db_query($qry))
			return true;
		else
		{
			$this->errormsg = 'Fehler beim Stornieren des Vertrags';
			return false;
		}
	}

	/**
	 * Liefert alle Vertraege bei denen die Lehraufträge nicht zur Person passen.
	 * (zB Aufgrund Lektorenaenderung)
	 * @param $studiensemester_kurzbz
	 * @return boolean true wenn ok, false im Fehlerfall
	 */
	public function getFalscheVertraege($studiensemester_kurzbz)
	{
		$qry = "SELECT
					tbl_vertrag.*
				FROM
					lehre.tbl_vertrag
					JOIN lehre.tbl_lehreinheitmitarbeiter USING(vertrag_id)
					JOIN lehre.tbl_lehreinheit USING(lehreinheit_id)
				WHERE
					studiensemester_kurzbz=".$this->db_add_param($studiensemester_kurzbz)."
					AND tbl_lehreinheitmitarbeiter.mitarbeiter_uid NOT IN(SELECT uid FROM public.tbl_benutzer WHERE person_id=tbl_vertrag.person_id)
				UNION
				SELECT
					tbl_vertrag.*
				FROM
					lehre.tbl_vertrag
					JOIN lehre.tbl_projektbetreuer USING(vertrag_id)
					JOIN lehre.tbl_projektarbeit USING(projektarbeit_id)
					JOIN lehre.tbl_lehreinheit USING(lehreinheit_id)
				WHERE
					studiensemester_kurzbz=".$this->db_add_param($studiensemester_kurzbz)."
					AND tbl_projektbetreuer.person_id!=tbl_vertrag.person_id";

		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				$obj = new stdClass();

				$obj->vertrag_id = $row->vertrag_id;
				$obj->vertragstyp_kurzbz = $row->vertragstyp_kurzbz;
				$obj->bezeichnung = $row->bezeichnung;
				$obj->betrag = $row->betrag;
				$obj->person_id = $row->person_id;
				$obj->anmerkung = $row->anmerkung;
				$obj->vertragsdatum = $row->vertragsdatum;

				$this->result[]=$obj;
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
	 * Liefert alle Vertraege bei denen die Lehraufträgs-Beträge nicht zur Vertragsbetrag passen
	 * (zB Aufgrund von Honoraraenderung)
	 * @param $studiensemester_kurzbz
	 * @return boolean true wenn ok, false im Fehlerfall
	 */
	public function getFalscheBetraege($studiensemester_kurzbz)
	{
		$qry = "SELECT * FROM
				(
					SELECT
						tbl_vertrag.*, tbl_lehreinheitmitarbeiter.mitarbeiter_uid, tbl_lehreinheitmitarbeiter.lehreinheit_id,
						COALESCE(tbl_lehreinheitmitarbeiter.stundensatz, 0) as stundensatz,
						COALESCE(tbl_lehreinheitmitarbeiter.semesterstunden, 0) as semesterstunden
					FROM
						lehre.tbl_vertrag
						JOIN lehre.tbl_lehreinheitmitarbeiter USING(vertrag_id)
						JOIN lehre.tbl_lehreinheit USING(lehreinheit_id)
					WHERE
						studiensemester_kurzbz=".$this->db_add_param($studiensemester_kurzbz)."
				)x
				WHERE
					x.semesterstunden * x.stundensatz != x.betrag";

		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				$obj = new stdClass();

				$obj->vertrag_id = $row->vertrag_id;
				$obj->vertragstyp_kurzbz = $row->vertragstyp_kurzbz;
				$obj->bezeichnung = $row->bezeichnung;
				$obj->betrag = $row->betrag;
				$obj->person_id = $row->person_id;
				$obj->anmerkung = $row->anmerkung;
				$obj->vertragsdatum = $row->vertragsdatum;
				$obj->semesterstunden = $row->semesterstunden;
				$obj->stundensatz = $row->stundensatz;
				$obj->mitarbeiter_uid = $row->mitarbeiter_uid;
				$obj->lehreinheit_id = $row->lehreinheit_id;

				$this->result[]=$obj;
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
	 * Prueft ob ein Mitarbeiter einen erteilten Vertrag zu einer Lehrveranstaltung besitzt.
	 * @param $lehrveranstaltung_id ID der Lehrveranstaltung
	 * @param $studiensemester_kurzbz Studiensemester das geprueft wird
	 * @param $mitarbeiter_uid UID des Mitarbeiters
	 */
	public function isVertragErteiltLV($lehrveranstaltung_id, $studiensemester_kurzbz, $mitarbeiter_uid)
	{
		if (defined('CIS_LV_LEKTORINNENZUTEILUNG_VERTRAGSPRUEFUNG_VON')
		 && CIS_LV_LEKTORINNENZUTEILUNG_VERTRAGSPRUEFUNG_VON != '')
		{
			// Liegt das Studiensemester vor dem Pruefdatum, wird die LV immer als Erteilt angezeigt
			$qry = "
				SELECT
					tbl_studiensemester.start
				FROM
					public.tbl_studiensemester
				WHERE
					studiensemester_kurzbz=".$this->db_add_param($studiensemester_kurzbz)."
					AND tbl_studiensemester.start < (SELECT start
						FROM public.tbl_studiensemester stsem WHERE
						stsem.studiensemester_kurzbz=".$this->db_add_param(CIS_LV_LEKTORINNENZUTEILUNG_VERTRAGSPRUEFUNG_VON)."
						)";

			if ($result = $this->db_query($qry))
			{
				if ($this->db_num_rows($result)>0)
				{
					// Wenn das Studiensemester vor dem Pruefdatum liegt, gilt der Vertrag immer als erteilt.
					return true;
				}
			}
			else
			{
				$this->errormsg = 'Fehler beim Laden des Studiensemesters';
				return false;
			}
		}

		$qry = "
			SELECT
				1
			FROM
				lehre.tbl_lehreinheitmitarbeiter
				JOIN lehre.tbl_lehreinheit USING(lehreinheit_id)
				JOIN lehre.tbl_vertrag USING(vertrag_id)
				JOIN lehre.tbl_vertrag_vertragsstatus USING(vertrag_id)
			WHERE
				tbl_lehreinheitmitarbeiter.mitarbeiter_uid=".$this->db_add_param($mitarbeiter_uid)."
				AND tbl_lehreinheit.studiensemester_kurzbz=".$this->db_add_param($studiensemester_kurzbz)."
				AND tbl_lehreinheit.lehrveranstaltung_id=".$this->db_add_param($lehrveranstaltung_id)."
				AND tbl_vertrag_vertragsstatus.vertragsstatus_kurzbz='erteilt'
				AND NOT EXISTS(
					SELECT 1 FROM lehre.tbl_vertrag_vertragsstatus vstatus
					WHERE vstatus.vertrag_id = tbl_vertrag.vertrag_id
					AND vstatus.vertragsstatus_kurzbz='storno'
				)
		";

		if ($result = $this->db_query($qry))
		{
			if ($this->db_num_rows($result) > 0)
			{
				return true;
			}
			else
			{
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
	 * Prueft ob ein Mitarbeiter einen erteilten Vertrag zu einer Lehreinheit besitzt.
	 * @param $lehreinheit ID der Lehreinheit
	 * @param $studiensemester_kurzbz Studiensemester das geprueft wird
	 * @param $mitarbeiter_uid UID des Mitarbeiters
	 */
	public function isVertragErteiltLE($lehreinheit_id, $studiensemester_kurzbz, $mitarbeiter_uid)
	{
		if (defined('CIS_LV_LEKTORINNENZUTEILUNG_VERTRAGSPRUEFUNG_VON')
		 && CIS_LV_LEKTORINNENZUTEILUNG_VERTRAGSPRUEFUNG_VON != '')
		{
			// Liegt das Studiensemester vor dem Pruefdatum, wird die LV immer als Erteilt angezeigt
			$qry = "
				SELECT
					tbl_studiensemester.start
				FROM
					public.tbl_studiensemester
				WHERE
					studiensemester_kurzbz=".$this->db_add_param($studiensemester_kurzbz)."
					AND tbl_studiensemester.start < (SELECT start
						FROM public.tbl_studiensemester stsem WHERE
						stsem.studiensemester_kurzbz=".$this->db_add_param(CIS_LV_LEKTORINNENZUTEILUNG_VERTRAGSPRUEFUNG_VON)."
						)";

			if ($result = $this->db_query($qry))
			{
				if ($this->db_num_rows($result)>0)
				{
					// Wenn das Studiensemester vor dem Pruefdatum liegt, gilt der Vertrag immer als erteilt.
					return true;
				}
			}
			else
			{
				$this->errormsg = 'Fehler beim Laden des Studiensemesters';
				return false;
			}
		}

		$qry = "
			SELECT
				1
			FROM
				lehre.tbl_lehreinheitmitarbeiter
				JOIN lehre.tbl_lehreinheit USING(lehreinheit_id)
				JOIN lehre.tbl_vertrag USING(vertrag_id)
				JOIN lehre.tbl_vertrag_vertragsstatus USING(vertrag_id)
			WHERE
				tbl_lehreinheitmitarbeiter.mitarbeiter_uid=".$this->db_add_param($mitarbeiter_uid)."
				AND tbl_lehreinheit.studiensemester_kurzbz=".$this->db_add_param($studiensemester_kurzbz)."
				AND tbl_lehreinheit.lehreinheit_id=".$this->db_add_param($lehreinheit_id)."
				AND tbl_vertrag_vertragsstatus.vertragsstatus_kurzbz='erteilt'
				AND NOT EXISTS(
					SELECT 1 FROM lehre.tbl_vertrag_vertragsstatus vstatus
					WHERE vstatus.vertrag_id = tbl_vertrag.vertrag_id
					AND vstatus.vertragsstatus_kurzbz='storno'
				)
		";

		if ($result = $this->db_query($qry))
		{
			if ($this->db_num_rows($result) > 0)
			{
				return true;
			}
			else
			{
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
?>
