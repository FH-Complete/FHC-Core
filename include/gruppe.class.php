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
require_once(dirname(__FILE__).'/basis_db.class.php');

class gruppe extends basis_db
{
	public $new;      				// boolean
	public $result = array(); 		// gruppen Objekt
	public $gruppe_kurbzNeu;		// Variable für geänderte Gruppen Kurzbez.

	//Tabellenspalten
	public $gruppe_kurzbz;			// varchar(16)
	public $studiengang_kz;			// integer
	public $bezeichnung;			// varchar(32)
	public $semester;				// smallint
	public $sort;					// smallint
	public $lehre=true;				// boolean
	public $mailgrp;				// boolean
	public $beschreibung;			// varchar(128)
	public $generiert;				// boolean
	public $sichtbar;				// boolean
	public $aktiv;					// boolean
	public $content_visible=false;	// boolean
	public $updateamum;				// timestamp
	public $updatevon;				// varchar(16)
	public $insertamum;				// timestamp
	public $insertvon;				// varchar(16)
	public $orgform_kurzbz;
	public $gesperrt=false;		// boolean
	public $zutrittssystem=false;	// boolean
	public $aufnahmegruppe=false; // boolean
	public $direktinskription=false;

	/**
	 * Konstruktor - Laedt optional eine Gruppe
	 * @param $gruppe_kurzbz
	 */
	public function __construct($gruppe_kurzbz=null)
	{
		parent::__construct();

		if(!is_null($gruppe_kurzbz))
			$this->load($gruppe_kurzbz);
	}

	/**
	 * Loescht eine Gruppe
	 * @param gruppe_kurzbz
	 * @return boolean
	 */
	public function delete($gruppe_kurzbz)
	{
		$qry ="DELETE FROM public.tbl_gruppe WHERE gruppe_kurzbz=".$this->db_add_param($gruppe_kurzbz);

		if($this->db_query($qry))
			return true;
		else
		{
			$this->errormsg = 'Fehler beim Loeschen der Gruppe';
			return false;
		}
	}

	/**
	 * Prueft ob bereits eine Gruppe mit der
	 * uebergebenen Kurzbezeichnung existiert
	 * @param gruppe_kurzbz
	 */
	public function exists($gruppe_kurzbz)
	{
		$qry = "SELECT count(*) as anzahl FROM public.tbl_gruppe WHERE gruppe_kurzbz=".$this->db_add_param(mb_strtoupper($gruppe_kurzbz));

		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				if($row->anzahl>0)
					return true;
				else
					return false;
			}
			else
			{
				$this->errormsg = 'Fehler bei einer Abfrage: '.$qry;
				return false;
			}
		}
		else
		{
			$this->errormsg = 'Fehler bei einer Abfrage';
			return false;
		}
	}

	/**
	 * Laedt die Gruppe
	 * @param gruppe_kurzbz
	 */
	public function load($gruppe_kurzbz)
	{
		$qry = "SELECT * FROM public.tbl_gruppe WHERE gruppe_kurzbz=".$this->db_add_param($gruppe_kurzbz);

		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				$this->gruppe_kurzbz = $row->gruppe_kurzbz;
				$this->studiengang_kz = $row->studiengang_kz;
				$this->bezeichnung = $row->bezeichnung;
				$this->semester = $row->semester;
				$this->sort = $row->sort;
				$this->mailgrp = $this->db_parse_bool($row->mailgrp);
				$this->lehre = $this->db_parse_bool($row->lehre);
				$this->beschreibung = $row->beschreibung;
				$this->sichtbar = $this->db_parse_bool($row->sichtbar);
				$this->aktiv = $this->db_parse_bool($row->aktiv);
				$this->content_visible = $this->db_parse_bool($row->content_visible);
				$this->generiert = $this->db_parse_bool($row->generiert);
				$this->updateamum = $row->updateamum;
				$this->updatevon = $row->updatevon;
				$this->insertamum = $row->insertamum;
				$this->insertvon = $row->insertvon;
				$this->orgform_kurzbz = $row->orgform_kurzbz;
				$this->gesperrt = $this->db_parse_bool($row->gesperrt);
				$this->zutrittssystem = $this->db_parse_bool($row->zutrittssystem);
				$this->aufnahmegruppe = $this->db_parse_bool($row->aufnahmegruppe);
				$this->direktinskription = $this->db_parse_bool($row->direktinskription);
				return true;
			}
			else
			{
				$this->errormsg = 'Fehler beim Laden der Daten';
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
	 * Laedt alle Gruppen
	 *
	 * @return boolean
	 */
	public function getAll()
	{
		$qry = "SELECT * FROM public.tbl_gruppe ORDER BY gruppe_kurzbz";

		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$grp_obj = new gruppe();

				$grp_obj->gruppe_kurzbz = $row->gruppe_kurzbz;
				$grp_obj->studiengang_kz = $row->studiengang_kz;
				$grp_obj->bezeichnung = $row->bezeichnung;
				$grp_obj->semester = $row->semester;
				$grp_obj->sort = $row->sort;
				$grp_obj->lehre = $this->db_parse_bool($row->lehre);
				$grp_obj->mailgrp = $this->db_parse_bool($row->mailgrp);
				$grp_obj->beschreibung = $row->beschreibung;
				$grp_obj->sichtbar = $this->db_parse_bool($row->sichtbar);
				$grp_obj->aktiv = $this->db_parse_bool($row->aktiv);
				$grp_obj->content_visible = $this->db_parse_bool($row->content_visible);
				$grp_obj->generiert = $this->db_parse_bool($row->generiert);
				$grp_obj->updateamum = $row->updateamum;
				$grp_obj->updatevon = $row->updatevon;
				$grp_obj->insertamum = $row->insertamum;
				$grp_obj->insertvon = $row->insertvon;
				$grp_obj->orgform_kurzbz = $row->orgform_kurzbz;
				$grp_obj->gesperrt = $this->db_parse_bool($row->gesperrt);
				$grp_obj->zutrittssystem = $this->db_parse_bool($row->zutrittssystem);
				$grp_obj->aufnahmegruppe = $this->db_parse_bool($row->aufnahmegruppe);
				$grp_obj->direktinskription = $this->db_parse_bool($row->direktinskription);

				$this->result[] = $grp_obj;
			}
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Gruppen';
			return false;
		}
	}

	/**
	 * Liefert die Anzahl der Personen in dieser Gruppe
	 *
	 * @param $gruppe_kurzbz
	 * @return anzahl der Personen
	 */
	public function countStudenten($gruppe_kurzbz)
	{
		$qry = "SELECT count(*) as anzahl FROM public.tbl_benutzergruppe
				WHERE gruppe_kurzbz=".$this->db_add_param($gruppe_kurzbz);

		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
				return $row->anzahl;
			else
			{
				$this->errormsg = 'Fehler beim Lesen der benutzergruppe';
				return false;
			}
		}
		else
		{
			$this->errormsg = 'Fehler beim Lesen der benutzergruppe';
			return false;
		}
	}

	/**
	 * Laedt die Gruppen die den Parametern ensprechen
	 *
	 * @param $studiengang_kz
	 * @param $semester
	 * @param $mailgrp
	 * @param $sichtbar
	 * @param $content_visible
	 * @param $aktiv
	 * @param $order Spalte nach der sortiert werden soll. Default='beschreibung'
	 * @return boolean
	 */
	public function getgruppe($studiengang_kz=null, $semester=null, $mailgrp=null, $sichtbar=null, $content_visible=null, $aktiv=null, $order=null)
	{
		$qry = 'SELECT * FROM public.tbl_gruppe WHERE 1=1';
		if(!is_null($studiengang_kz) && $studiengang_kz!='')
			$qry .= " AND studiengang_kz=".$this->db_add_param($studiengang_kz);
		if(!is_null($semester) && $semester!='')
			$qry .= " AND semester=".$this->db_add_param($semester);
		if(!is_null($mailgrp) && $mailgrp!='')
			$qry .= " AND mailgrp=".$this->db_add_param($mailgrp, FHC_BOOLEAN);
		if(!is_null($sichtbar))
			$qry .= " AND sichtbar=".$this->db_add_param($sichtbar, FHC_BOOLEAN);
		if(!is_null($content_visible))
			$qry .= " AND content_visible=".$this->db_add_param($content_visible, FHC_BOOLEAN);
		if(!is_null($aktiv) && $aktiv!='')
			$qry .= " AND aktiv=".$this->db_add_param($aktiv, FHC_BOOLEAN);
		if(!is_null($order) && $order!='')
			$qry .= " ORDER BY ".$order;
		else
			$qry.=" ORDER BY beschreibung";
		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$grp_obj = new gruppe();

				$grp_obj->gruppe_kurzbz = $row->gruppe_kurzbz;
				$grp_obj->studiengang_kz = $row->studiengang_kz;
				$grp_obj->bezeichnung = $row->bezeichnung;
				$grp_obj->semester = $row->semester;
				$grp_obj->sort = $row->sort;
				$grp_obj->mailgrp = $this->db_parse_bool($row->mailgrp);
				$grp_obj->lehre = $this->db_parse_bool($row->lehre);
				$grp_obj->beschreibung = $row->beschreibung;
				$grp_obj->sichtbar = $this->db_parse_bool($row->sichtbar);
				$grp_obj->aktiv = $this->db_parse_bool($row->aktiv);
				$grp_obj->content_visible = $this->db_parse_bool($row->content_visible);
				$grp_obj->generiert = $this->db_parse_bool($row->generiert);
				$grp_obj->updateamum = $row->updateamum;
				$grp_obj->updatevon = $row->updatevon;
				$grp_obj->insertamum = $row->insertamum;
				$grp_obj->insertvon = $row->insertvon;
				$grp_obj->orgform_kurzbz = $row->orgform_kurzbz;
				$grp_obj->gesperrt = $this->db_parse_bool($row->gesperrt);
				$grp_obj->zutrittssystem = $this->db_parse_bool($row->zutrittssystem);
				$grp_obj->aufnahmegruppe = $this->db_parse_bool($row->aufnahmegruppe);
				$grp_obj->direktinskription = $this->db_parse_bool($row->direktinskription);

				$this->result[] = $grp_obj;
			}
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Gruppen';
			return false;
		}
	}

	/**
	 * Prueft die Variablen vor dem Speichern
	 * auf Gueltigkeit.
	 * @return true wenn ok, false im Fehlerfall
	 */
	protected function validate()
	{
		if(mb_strlen($this->gruppe_kurzbz)>32)
		{
			$this->errormsg = 'Gruppe_kurzbz darf nicht laenger als 32 Zeichen sein';
			return false;
		}
		if($this->gruppe_kurzbz=='')
		{
			$this->errormsg = 'Gruppe muss angegeben werden';
			return false;
		}
		if(!is_numeric($this->studiengang_kz))
		{
			$this->errormsg = 'Studiengang_kz muss eine gueltige Zahl sein';
			return false;
		}
		if(mb_strlen($this->bezeichnung)>32)
		{
			$this->errormsg = 'Bezeichnung darf nicht laenger als 32 Zeichen sein';
			return false;
		}
		if($this->semester!='' && !is_numeric($this->semester))
		{
			$this->errormsg = 'Semester muss eine gueltige Zahl sein';
			return false;
		}
		if($this->sort!='' && !is_numeric($this->sort))
		{
			$this->errormsg = 'Typ muss eine gueltige Zahl sein';
			return false;
		}
		if(!is_bool($this->mailgrp))
		{
			$this->errormsg = 'Mailgrp muss ein boolscher wert sein';
			return false;
		}
		if(mb_strlen($this->beschreibung)>128)
		{
			$this->errormsg = 'Beschreibung darf nicht laenger als 128 Zeichen sein';
			return false;
		}
		if(!is_bool($this->sichtbar))
		{
			$this->errormsg = 'Sichtbar muss ein boolscher Wert sein';
			return false;
		}
		if(!is_bool($this->aktiv))
		{
			$this->errormsg = 'Aktiv muss ein boolscher Wert sein';
			return false;
		}
		if(!is_bool($this->direktinskription))
		{
			$this->errormsg = 'direktinskription muss ein boolscher Wert sein';
			return false;
		}
		if(mb_strlen($this->updatevon)>32)
		{
			$this->errormsg = 'Updatevon darf nicht laenger als 32 Zeichen sein';
			return false;
		}
		if(mb_strlen($this->insertvon)>32)
		{
			$this->errormsg = 'Insertvon darf nicht laenger als 32 Zeichen sein';
			return false;
		}

		return true;
	}

	/**
	 * Speichert Gruppe in die Datenbank
	 * Wenn $new auf true gesetzt ist wird ein neuer Datensatz
	 * angelegt, ansonsten der Datensatz upgedated
	 * @return true wenn erfolgreich, false im Fehlerfall
	 */
	public function save($new=null, $upper=true)
	{
		if(is_null($new))
			$new = $this->new;

		//Variablen auf Gueltigkeit pruefen
		if(!$this->validate())
			return false;

		if($new)
		{
			if ($upper)
				$kurzbz = mb_strtoupper($this->gruppe_kurzbz);
			else
				$kurzbz = $this->gruppe_kurzbz;

			$qry = 'INSERT INTO public.tbl_gruppe (gruppe_kurzbz, studiengang_kz, bezeichnung, semester, sort,
											mailgrp, beschreibung, sichtbar, generiert, aktiv, lehre, content_visible,
											updateamum, updatevon, insertamum, insertvon, orgform_kurzbz, gesperrt,
											zutrittssystem, aufnahmegruppe, direktinskription)
					VALUES('.$this->db_add_param($kurzbz).','.
					$this->db_add_param($this->studiengang_kz).','.
					$this->db_add_param($this->bezeichnung).','.
					$this->db_add_param($this->semester).','.
					$this->db_add_param($this->sort).','.
					$this->db_add_param($this->mailgrp, FHC_BOOLEAN).','.
					$this->db_add_param($this->beschreibung).','.
					$this->db_add_param($this->sichtbar, FHC_BOOLEAN).','.
					$this->db_add_param($this->generiert, FHC_BOOLEAN).','.
					$this->db_add_param($this->aktiv, FHC_BOOLEAN).','.
					$this->db_add_param($this->lehre, FHC_BOOLEAN).','.
					$this->db_add_param($this->content_visible, FHC_BOOLEAN).','.
					$this->db_add_param($this->updateamum).','.
					$this->db_add_param($this->updatevon).','.
					$this->db_add_param($this->insertamum).','.
					$this->db_add_param($this->insertvon).','.
					$this->db_add_param($this->orgform_kurzbz).','.
					$this->db_add_param($this->gesperrt, FHC_BOOLEAN).','.
					$this->db_add_param($this->zutrittssystem, FHC_BOOLEAN).','.
					$this->db_add_param($this->aufnahmegruppe, FHC_BOOLEAN).','.
					$this->db_add_param($this->direktinskription, FHC_BOOLEAN).');';
		}
		else
		{
			$qry = 'UPDATE public.tbl_gruppe SET'.
				' studiengang_kz='.$this->db_add_param($this->studiengang_kz).','.
				' bezeichnung='.$this->db_add_param($this->bezeichnung).','.
				' semester='.$this->db_add_param($this->semester).','.
				' sort='.$this->db_add_param($this->sort).','.
				' mailgrp='.$this->db_add_param($this->mailgrp, FHC_BOOLEAN).','.
				' beschreibung='.$this->db_add_param($this->beschreibung).','.
				' sichtbar='.$this->db_add_param($this->sichtbar, FHC_BOOLEAN).','.
				' generiert='.$this->db_add_param($this->generiert, FHC_BOOLEAN).','.
				' aktiv='.$this->db_add_param($this->aktiv, FHC_BOOLEAN).','.
				' lehre='.$this->db_add_param($this->lehre, FHC_BOOLEAN).','.
				' content_visible='.$this->db_add_param($this->content_visible, FHC_BOOLEAN).','.
				' updateamum='.$this->db_add_param($this->updateamum).','.
				' updatevon='.$this->db_add_param($this->updatevon).','.
				' orgform_kurzbz='.$this->db_add_param($this->orgform_kurzbz).', '.
				' gesperrt='.$this->db_add_param($this->gesperrt, FHC_BOOLEAN).', '.
				' zutrittssystem='.$this->db_add_param($this->zutrittssystem, FHC_BOOLEAN).', '.
				' aufnahmegruppe='.$this->db_add_param($this->aufnahmegruppe, FHC_BOOLEAN).', '.
				' direktinskription='.$this->db_add_param($this->direktinskription, FHC_BOOLEAN).' ';

			if($this->gruppe_kurbzNeu != null)
			{
				$qry.=', gruppe_kurzbz='.$this->db_add_param($this->gruppe_kurbzNeu).' ';
			}
			$qry.=" WHERE gruppe_kurzbz=".$this->db_add_param($this->gruppe_kurzbz).";";
		}

		if($this->db_query($qry))
		{
			//Log schreiben
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern der Gruppe';
			return false;
		}
	}

	/**
	 * Laedt die User dieser Gruppe
	 *
	 * @param $gruppe_kurzbz
	 */
	public function loadUser($gruppe_kurzbz)
	{
		$qry = "SELECT
					tbl_benutzer.uid, tbl_person.vorname, tbl_person.nachname
				FROM
					public.tbl_benutzergruppe
					JOIN public.tbl_benutzer USING(uid)
					JOIN public.tbl_person USING(person_id)
				WHERE
					tbl_benutzergruppe.gruppe_kurzbz=".$this->db_add_param($gruppe_kurzbz)."
				ORDER BY nachname, vorname";

		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				$obj = new gruppe();

				$obj->uid = $row->uid;
				$obj->vorname = $row->vorname;
				$obj->nachname = $row->nachname;

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
	 * Laedt alle Zutrittsgruppen in denen sich der Benutzer befindet
	 *
	 * @param $user UID des Benutzers
	 */
	public function loadZutrittsgruppen($user)
	{
		$qry = "SELECT
					*
				FROM
					public.tbl_benutzergruppe
					JOIN public.tbl_gruppe USING(gruppe_kurzbz)
				WHERE
					tbl_gruppe.zutrittssystem=true
					AND tbl_benutzergruppe.uid=".$this->db_add_param($user);

		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				$grp_obj = new gruppe();

				$grp_obj->gruppe_kurzbz = $row->gruppe_kurzbz;
				$grp_obj->studiengang_kz = $row->studiengang_kz;
				$grp_obj->bezeichnung = $row->bezeichnung;
				$grp_obj->semester = $row->semester;
				$grp_obj->sort = $row->sort;
				$grp_obj->mailgrp = $this->db_parse_bool($row->mailgrp);
				$grp_obj->lehre = $this->db_parse_bool($row->lehre);
				$grp_obj->beschreibung = $row->beschreibung;
				$grp_obj->sichtbar = $this->db_parse_bool($row->sichtbar);
				$grp_obj->aktiv = $this->db_parse_bool($row->aktiv);
				$grp_obj->content_visible = $this->db_parse_bool($row->content_visible);
				$grp_obj->generiert = $this->db_parse_bool($row->generiert);
				$grp_obj->updateamum = $row->updateamum;
				$grp_obj->updatevon = $row->updatevon;
				$grp_obj->insertamum = $row->insertamum;
				$grp_obj->insertvon = $row->insertvon;
				$grp_obj->orgform_kurzbz = $row->orgform_kurzbz;
				$grp_obj->gesperrt = $this->db_parse_bool($row->gesperrt);
				$grp_obj->zutrittssystem = $this->db_parse_bool($row->zutrittssystem);
				$grp_obj->aufnahmegruppe = $this->db_parse_bool($row->aufnahmegruppe);
				$grp_obj->direktinskription = $this->db_parse_bool($row->direktinskription);

				$this->result[] = $grp_obj;
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
	 * Prüft ob die Gruppenbezeichnung bereits vorhanden ist
	 * und gibt bei Bedarf die Bezeichnung inkl. Nummerierung zurück
	 *
	 * @param $gruppe_kurzbz zu prüfende Gruppenbezeichnung
	 */
	public function getNummerierteGruppenbez($gruppe_kurzbz)
	{
		$gruppe_kurzbz_regex = $gruppe_kurzbz . '-[0-9]+$';

		$qry = 'SELECT COUNT(gruppe_kurzbz) AS anzahl
				FROM public.tbl_gruppe
				WHERE gruppe_kurzbz = ' . $this->db_add_param($gruppe_kurzbz) . '
				OR gruppe_kurzbz ~ ' . $this->db_add_param($gruppe_kurzbz_regex);

		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				if($row->anzahl > 0)
					$gruppe_kurzbz = $gruppe_kurzbz . "-" . $row->anzahl;
			}
		}

		return $gruppe_kurzbz;
	}

	/**
	 * Laedt alle Aufnahmegruppen
	 *
	 * @return boolean true wenn erfolgreich, false im Fehlerfall
	 */
	public function getAufnahmegruppen()
	{
		$qry = "SELECT * FROM public.tbl_gruppe WHERE aufnahmegruppe=true ORDER BY bezeichnung";

		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				$grp_obj = new gruppe();

				$grp_obj->gruppe_kurzbz = $row->gruppe_kurzbz;
				$grp_obj->studiengang_kz = $row->studiengang_kz;
				$grp_obj->bezeichnung = $row->bezeichnung;
				$grp_obj->semester = $row->semester;
				$grp_obj->sort = $row->sort;
				$grp_obj->mailgrp = $this->db_parse_bool($row->mailgrp);
				$grp_obj->lehre = $this->db_parse_bool($row->lehre);
				$grp_obj->beschreibung = $row->beschreibung;
				$grp_obj->sichtbar = $this->db_parse_bool($row->sichtbar);
				$grp_obj->aktiv = $this->db_parse_bool($row->aktiv);
				$grp_obj->content_visible = $this->db_parse_bool($row->content_visible);
				$grp_obj->generiert = $this->db_parse_bool($row->generiert);
				$grp_obj->updateamum = $row->updateamum;
				$grp_obj->updatevon = $row->updatevon;
				$grp_obj->insertamum = $row->insertamum;
				$grp_obj->insertvon = $row->insertvon;
				$grp_obj->orgform_kurzbz = $row->orgform_kurzbz;
				$grp_obj->gesperrt = $this->db_parse_bool($row->gesperrt);
				$grp_obj->zutrittssystem = $this->db_parse_bool($row->zutrittssystem);
				$grp_obj->aufnahmegruppe = $this->db_parse_bool($row->aufnahmegruppe);
				$grp_obj->direktinskription = $this->db_parse_bool($row->direktinskription);

				$this->result[] = $grp_obj;
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
	 * Sucht nach Gruppen in gruppe_kurzbz, bezeichnung und beschreibung.
	 *
	 * @param array $searchItems Array mit Suchbegriffen, nach denen gesucht werden soll
	 * @param boolean $aktiv (optional). Default true. Wenn false werden nur inaktive gruppen geladen, wenn null dann alle
	 * @param integer $limit (optional). Limit an Ergebnissen
	 *
	 * @return true, wenn erfolgreich, false im Fehlerfall
	 */
	public function searchGruppen($searchItems, $aktiv = true, $limit = null)
	{
		if (!is_array($searchItems))
		{
			$this->errormsg = '$searchItems muss ein Array sein';
			return false;
		}
		$qry = "SELECT
				*
				FROM
					public.tbl_gruppe
				WHERE";
		if(is_null($aktiv))
			$qry.=" (";
		elseif($aktiv==true)
			$qry.=" tbl_gruppe.aktiv=true AND (";
		elseif($aktiv==false)
			$qry.=" tbl_gruppe.aktiv=false AND (";

		$qry.=" lower(gruppe_kurzbz) like lower('%".$this->db_escape(implode(' ',$searchItems))."%')";
		$qry.=" OR lower(bezeichnung) like lower('%".$this->db_escape(implode(' ',$searchItems))."%')";
		$qry.=" OR lower(beschreibung) like lower('%".$this->db_escape(implode(' ',$searchItems))."%')";

		$qry.=") ORDER BY gruppe_kurzbz";

		if(!is_null($limit) && is_numeric($limit))
			$qry.=" LIMIT ".$limit;

		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				$grp_obj = new gruppe();

				$grp_obj->gruppe_kurzbz = $row->gruppe_kurzbz;
				$grp_obj->studiengang_kz = $row->studiengang_kz;
				$grp_obj->bezeichnung = $row->bezeichnung;
				$grp_obj->semester = $row->semester;
				$grp_obj->sort = $row->sort;
				$grp_obj->mailgrp = $this->db_parse_bool($row->mailgrp);
				$grp_obj->lehre = $this->db_parse_bool($row->lehre);
				$grp_obj->beschreibung = $row->beschreibung;
				$grp_obj->sichtbar = $this->db_parse_bool($row->sichtbar);
				$grp_obj->aktiv = $this->db_parse_bool($row->aktiv);
				$grp_obj->content_visible = $this->db_parse_bool($row->content_visible);
				$grp_obj->generiert = $this->db_parse_bool($row->generiert);
				$grp_obj->updateamum = $row->updateamum;
				$grp_obj->updatevon = $row->updatevon;
				$grp_obj->insertamum = $row->insertamum;
				$grp_obj->insertvon = $row->insertvon;
				$grp_obj->orgform_kurzbz = $row->orgform_kurzbz;
				$grp_obj->gesperrt = $this->db_parse_bool($row->gesperrt);
				$grp_obj->zutrittssystem = $this->db_parse_bool($row->zutrittssystem);
				$grp_obj->aufnahmegruppe = $this->db_parse_bool($row->aufnahmegruppe);
				$grp_obj->direktinskription = $this->db_parse_bool($row->direktinskription);

				$this->result[] = $grp_obj;
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
}
?>
