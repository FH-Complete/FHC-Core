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
require_once(dirname(__FILE__).'/person.class.php');

class benutzer extends person
{
	//Tabellenspalten
	public $uid;			// varchar(32)
	public $bnaktiv=true;	// boolean
	public $alias;			// varchar(256)
	public $bn_ext_id;
	public $aktivierungscode;
	public $result = array();
	public $updateaktivam;
	public $updateaktivvon;

	/**
	 * Konstruktor - Uebergibt die Connection und laedt optional einen Benutzer
	 * @param $uid            Benutzer der geladen werden soll (default=null)
	 */
	public function __construct($uid=null)
	{
		parent::__construct();

		if($uid != null)
			$this->load($uid);
	}

	/**
	 * Laedt Benutzer mit der uebergebenen ID
	 * @param $uid ID der Person die geladen werden soll
	 */
	public function load($uid = null)
	{
		if (empty($uid))
		{
			$this->errormsg = "UID not set!";
			return false;
		}

		$qry = "SELECT * FROM public.tbl_benutzer WHERE uid=".$this->db_add_param($uid);

		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				$this->uid = $row->uid;
				$this->bnaktiv = $this->db_parse_bool($row->aktiv);
				$this->alias = $row->alias;
				$this->aktivierungscode = $row->aktivierungscode;
				$this->updateaktivam = $row->updateaktivam;
				$this->updateaktivvon = $row->updateaktivvon;

				if(!person::load($row->person_id))
					return false;
				else
					return true;
			}
			else
			{
				$this->errormsg = "Benutzer nicht gefunden";
				return false;
			}
		}
		else
		{
			$this->errormsg = "Fehler beim Laden der Benutzerdaten";
			return false;
		}
	}

	/**
	 * Prueft die Variablen vor dem Speichern
	 * auf Gueltigkeit.
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function validate()
	{
		if(mb_strlen($this->uid)>32)
		{
			$this->errormsg = 'UID darf nicht laenger als 32 Zeichen sein';
			return false;
		}
		if($this->uid == '')
		{
			$this->errormsg = 'UID muss eingegeben werden';
			return false;
		}
		if(mb_strlen($this->alias)>256)
		{
			$this->errormsg = 'Alias darf nicht laenger als 256 Zeichen sein';
			return false;
		}
		if(!is_numeric($this->person_id))
		{
			$this->errormsg = 'person_id muss eine gueltige Zahl sein';
			return false;
		}
		if(!is_bool($this->bnaktiv))
		{
			$this->errormsg = 'aktiv muss ein boolscher wert sein';
			return false;
		}

		if($this->alias!='')
		{
			$qry = "SELECT * FROM public.tbl_benutzer WHERE alias=".$this->db_add_param($this->alias)." AND uid!=".$this->db_add_param($this->uid);
			if($this->db_query($qry))
			{
				if($this->db_num_rows()>0)
				{
					$this->errormsg = 'Dieser Alias ist bereits vergeben';
					return false;
				}
			}
			else
			{
				$this->errormsg = 'Fehler beim Pruefen des Alias';
				return false;
			}
		}
		return true;
	}

	/**
	 * Speichert die Benutzerdaten in die Datenbank
	 * Wenn $new auf true gesetzt ist wird ein neuer Datensatz angelegt
	 * ansonsten der Datensatz mit $uid upgedated
	 * @return true wenn erfolgreich, false im Fehlerfall
	 */
	public function save($new=null, $saveperson=true)
	{
		if($saveperson)
		{
			//Personen Datensatz speichern
			if(!person::save())
				return false;
		}

		if($new==null)
			$new = $this->new;

		//Variablen auf Gueltigkeit pruefen
		if(!benutzer::validate())
			return false;

		if($new) //Wenn new true ist dann ein INSERT absetzen ansonsten ein UPDATE
		{
			$qry = 'INSERT INTO public.tbl_benutzer (uid, aktiv, alias, person_id, insertamum, insertvon, updateamum, updatevon, aktivierungscode) VALUES('.
			       $this->db_add_param($this->uid).",".
			       $this->db_add_param($this->bnaktiv,FHC_BOOLEAN).",".
			       $this->db_add_param($this->alias).",".
			       $this->db_add_param($this->person_id, FHC_INTEGER).",".
			       $this->db_add_param($this->insertamum).",".
			       $this->db_add_param($this->insertvon).",".
			       $this->db_add_param($this->updateamum).",".
			       $this->db_add_param($this->updatevon).",".
				   $this->db_add_param($this->aktivierungscode).");";
		}
		else
		{
			//Wenn der Aktiv Status geaendert wurde, dann auch updateaktivamum und updateaktivvon setzen
			$upd='';
			$qry = "SELECT aktiv FROM public.tbl_benutzer WHERE uid=".$this->db_add_param($this->uid);
			if($this->db_query($qry))
			{
				if($row = $this->db_fetch_object())
				{
					$aktiv = $this->db_parse_bool($row->aktiv);

					if($aktiv!=$this->bnaktiv)
						$upd =" updateaktivam=".$this->db_add_param($this->updateamum).", updateaktivvon=".$this->db_add_param($this->updatevon).",";
				}
			}

			$qry = 'UPDATE public.tbl_benutzer SET'.
			       ' aktiv='.$this->db_add_param($this->bnaktiv, FHC_BOOLEAN).','.
			       ' alias='.$this->db_add_param($this->alias).','.
			       ' person_id='.$this->db_add_param($this->person_id).','.
			       ' updateamum='.$this->db_add_param($this->updateamum).','.$upd.
			       ' updatevon='.$this->db_add_param($this->updatevon).
			       ' WHERE uid='.$this->db_add_param($this->uid).';';
		}

		if($this->db_query($qry))
		{
			//Log schreiben
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern des Benutzer-Datensatzes';
			return false;
		}
	}


	/**
	 * Löscht den Benutzer mit der übergebenen uid. Da beim Speichern auch
	 * eine Person angelegt wird, muss eventuell auch diese gelöscht werden.
	 * Das kann durch Aufruf der geerbten Methode {@link person::delete()}
	 * erledigt werden. Damit die Klasse Abwärtskombatibel bleibt, wurde die
	 * Methode delete() absichtlich nicht überschrieben.
	 * @param $uid
	 */
	public function deleteBenutzer($uid)
	{
		$qry = "DELETE from public.tbl_benutzer where uid = ".$this->db_add_param($uid).";";

		if($this->db_query($qry))
		{
			return true;
		}
		else
		{
			$this->errormsg = "Es ist ein Fehler beim Löschen des Benutzers aufgetreten";
			return false;
		}
	}



	/**
	 * Prueft ob die UID bereits existiert
	 * @param uid
	 */
	public function uid_exists($uid)
	{
		$qry = "SELECT * FROM public.tbl_benutzer WHERE uid=".$this->db_add_param($uid);

		if($this->db_query($qry))
		{
			if($this->db_num_rows()>0)
			{
				$this->errormsg = '';
				return true;
			}
			else
			{
				$this->errormsg = '';
				return false;
			}

		}
		else
		{
			$this->errormsg = 'Fehler bei DatenbankAbfrage';
			return false;
		}

	}

	/**
	 * Prueft ob der alias bereits existiert
	 * @param $alias
	 */
	public function alias_exists($alias)
	{
		$qry = "SELECT * FROM public.tbl_benutzer WHERE alias=".$this->db_add_param($alias);

		if($this->db_query($qry))
		{
			if($this->db_num_rows()>0)
			{
				$this->errormsg = '';
				return true;
			}
			else
			{
				$this->errormsg = '';
				return false;
			}

		}
		else
		{
			$this->errormsg = 'Fehler bei DatenbankAbfrage';
			return false;
		}
	}

	/**
	 * Sucht nach Benutzern. Limit optional. Aktiv optional.
	 *
	 * @param $searchItems
	 * @param $limit (optional)
	 * @param bool $aktiv (optional). Default true. Wenn false werden nur inaktive benutzer geladen, wenn null dann alle
	 * @param bool $positivePersonalnr (optional). Default false. Wenn true, nur Mitarbeiter mit positiver Personalnr laden.
	 * @return bool
	 */
	public function search($searchItems, $limit=null, $aktiv=true, $positivePersonalnr=false)
	{
		// SearchItems imploden und trimmen, um preg_split(Zeichenweise trennung) durchfuehren zu koennen
		$searchItems_string_orig = implode(' ', $searchItems);
		$searchItems_string = generateSpecialCharacterString($searchItems_string_orig);

		$qry = "SELECT * FROM (
					SELECT
						distinct on (uid) vorname, nachname, wahlname, uid, mitarbeiter_uid, personalnummer, titelpre, titelpost, lektor, fixangestellt, alias, tbl_benutzer.aktiv, anrede,
							(SELECT UPPER
								(tbl_studiengang.typ || tbl_studiengang.kurzbz)
					 		FROM public.tbl_student
					 		JOIN public.tbl_studiengang USING(studiengang_kz)
							WHERE student_uid=tbl_benutzer.uid) as studiengang,

					 		(SELECT studiengang_kz FROM public.tbl_student
							WHERE student_uid=tbl_benutzer.uid) as studiengang_kz,

							(SELECT tbl_kontakt.kontakt || ' - ' ||telefonklappe
							FROM public.tbl_mitarbeiter
							LEFT JOIN public.tbl_kontakt USING(standort_id)
							WHERE
								mitarbeiter_uid=tbl_benutzer.uid
								AND (tbl_kontakt.kontakttyp='telefon' OR tbl_kontakt.kontakttyp is null)
								limit 1) as klappe,

							(SELECT planbezeichnung FROM public.tbl_mitarbeiter
							LEFT JOIN public.tbl_ort USING (ort_kurzbz)
							WHERE mitarbeiter_uid=tbl_benutzer.uid) as raum,

							(SELECT 1
							FROM PUBLIC.tbl_mitarbeiter
							WHERE mitarbeiter_uid = tbl_benutzer.uid
							) AS is_mitarbeiter
				FROM
					public.tbl_person
					JOIN public.tbl_benutzer USING(person_id)
					LEFT JOIN public.tbl_mitarbeiter ON(uid=mitarbeiter_uid)
				WHERE";
				if($aktiv===true)
					$qry.=" tbl_benutzer.aktiv=true AND";
				elseif($aktiv===false)
					$qry.=" tbl_benutzer.aktiv=false AND";

				if($positivePersonalnr === true)
					$qry.=" (personalnummer >= 0 OR personalnummer IS NULL) AND";

		$qry.=" (lower(vorname || ' ' || nachname) ~* lower(".$this->db_add_param($searchItems_string).")";
		$qry.=" OR lower(nachname || ' ' || vorname) ~* lower(".$this->db_add_param($searchItems_string).")";
		$qry.=" OR lower(nachname || ' ' || wahlname) ~* lower(".$this->db_add_param($searchItems_string).")";
		$qry.=" OR lower(wahlname || ' ' || nachname) ~* lower(".$this->db_add_param($searchItems_string).")";
		$qry.=" OR lower(uid) like lower('%".$this->db_escape(implode(' ',$searchItems))."%')";
		$qry.=" OR lower(telefonklappe) like lower('%".$this->db_escape(implode(' ',$searchItems))."%')";

		foreach($searchItems as $value)
		{
			$qry.=" OR lower(uid) = lower(".$this->db_add_param($value).")";
		}
		$qry.=")) a ORDER BY is_mitarbeiter, nachname, vorname";

		if(!is_null($limit) && is_numeric($limit))
			$qry.=" LIMIT ".$limit;

		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				$obj = new benutzer();

				$obj->titelpre = $row->titelpre;
				$obj->vorname  = $row->vorname;
				$obj->wahlname  = $row->wahlname;
				$obj->nachname = $row->nachname;
				$obj->titelpost = $row->titelpost;
				$obj->uid = $row->uid;
				$obj->mitarbeiter_uid = $row->mitarbeiter_uid;
				$obj->studiengang = $row->studiengang;
				$obj->studiengang_kz = $row->studiengang_kz;
				$obj->telefonklappe = $row->klappe;
				$obj->raum = $row->raum;
				$obj->alias = $row->alias;
				$obj->lektor = $row->lektor;
				$obj->fixangestellt = $row->fixangestellt;
				$obj->aktiv = $this->db_parse_bool($row->aktiv);
				$obj->anrede = $row->anrede;

				$this->result[] = $obj;
			}
			$this->errormsg = $qry;
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Daten';
			return false;
		}
	}

	/**
	 * Laedt alle Benutzer einer Person
	 * @param $person_id
	 * @param $aktiv optional wenn true werden nur aktive benutzer geladen, sonst alle
	 */
	function getBenutzerFromPerson($person_id, $aktiv=true)
	{
		$qry = "SELECT
					person_id, titelpre, vorname, nachname, titelpost, uid
				FROM
					public.tbl_benutzer
					JOIN public.tbl_person USING(person_id)
				WHERE
					person_id=".$this->db_add_param($person_id, FHC_INTEGER);
		if($aktiv)
			$qry.=" AND tbl_benutzer.aktiv=true ";

		$qry .= " ORDER BY tbl_person.insertamum";

		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				$obj = new benutzer();

				$obj->person_id = $row->person_id;
				$obj->titelpre = $row->titelpre;
				$obj->vorname  = $row->vorname;
				$obj->nachname = $row->nachname;
				$obj->titelpost = $row->titelpost;
				$obj->uid = $row->uid;

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
	 * Entfernt den Aktivierungscode eines Users
	 * @param $username
	 */
	public function DeleteAktivierungscode($username)
	{
		$qry = "UPDATE public.tbl_benutzer SET aktivierungscode=null WHERE uid=".$this->db_add_param($username);
		if($this->db_query($qry))
			return true;
		else
		{
			$this->errormsg = 'Fehler beim Loeschen des Aktivierungscodes';
			return false;
		}
	}

	/**
	 * Baut die Datenstruktur für senden als JSON Objekt auf
	 */
	public function cleanResult()
	{
		$values = array();
		if (count($this->result) > 0)
		{
			foreach ($this->result as $ben)
			{
				$obj = new stdClass();
				$obj->uid = $ben->uid;
				$obj->vorname = $ben->vorname;
				$obj->nachname = $ben->nachname;
				$values[] = $obj;

			}
		}
		else
		{
			$obj = new stdClass();
			$obj->uid = $this->uid;
			$obj->vorname = $this->vorname;
			$obj->nachname = $this->nachname;
			$values[] = $obj;
		}
		return $values;
	}

	/**
	 * Laedt Benutzer anhand des Alias
	 * @param $alias Alias der Person die geladen werden soll
	 */
	public function loadAlias($alias)
	{
		$qry = "SELECT * FROM public.tbl_benutzer WHERE alias=".$this->db_add_param($alias);

		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				$this->uid = $row->uid;
				$this->bnaktiv = $this->db_parse_bool($row->aktiv);
				$this->alias = $row->alias;
				$this->aktivierungscode = $row->aktivierungscode;

				if(!person::load($row->person_id))
					return false;
				else
					return true;
			}
			else
			{
				$this->errormsg = "Benutzer nicht gefunden";
				return false;
			}
		}
		else
		{
			$this->errormsg = "Fehler beim Laden der Benutzerdaten";
			return false;
		}
	}
}
?>
