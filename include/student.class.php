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
require_once(dirname(__FILE__).'/benutzer.class.php');

class student extends benutzer
{

	//Tabellenspalten
	public $matrikelnr;
	public $prestudent_id;
	public $studiengang_kz;
	public $semester;
	public $verband;
	public $gruppe;
	public $ext_id_student;
	public $result;

	public $studiensemester_kurzbz;

	/**
	 * Konstruktor - Laedt optional einen Studenten
	 * @param $uid	Student der geladen werden soll (default=null)
	 */
	public function __construct($uid=null)
	{
		parent::__construct();

		//Student laden
		if($uid!=null)
			$this->load($uid);
	}

	/**
	 * Laedt die Daten eines Studenten
	 * Wenn Studiensemester_kurzbz angegeben wird, dann werden
	 * Studiengang, Semester, Verband und Gruppe aus der Tabelle
	 * Studentlehrverband geholt.
	 * @param uid
	 * 		studiensemester_kurzbz
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function load($uid = null, $studiensemester_kurzbz=null)
	{
		if(!benutzer::load($uid))
			return false;
		if(is_null($studiensemester_kurzbz))
			$qry = "SELECT * FROM public.tbl_student WHERE student_uid=".$this->db_add_param($uid);
		else
			$qry = "SELECT *, tbl_studentlehrverband.studiengang_kz as studiengang_kz, tbl_studentlehrverband.semester as semester,
					tbl_studentlehrverband.verband as verband, tbl_studentlehrverband.gruppe as gruppe
					FROM public.tbl_student JOIN public.tbl_studentlehrverband USING(student_uid)
					WHERE studiensemester_kurzbz=".$this->db_add_param($studiensemester_kurzbz)." AND student_uid=".$this->db_add_param($uid);
		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				$this->uid = $row->student_uid;
				$this->matrikelnr = $row->matrikelnr;
				$this->prestudent_id = $row->prestudent_id;
				$this->studiengang_kz = $row->studiengang_kz;
				$this->semester = $row->semester;
				$this->verband = $row->verband;
				$this->gruppe = $row->gruppe;
				$this->updateamum = $row->updateamum;
				$this->updatevon = $row->updatevon;
				$this->insertamum = $row->insertamum;
				$this->insertvon = $row->insertvon;
				$this->ext_id = $row->ext_id;

				return true;
			}
			else
			{
				$this->errormsg = 'Kein Benutzer mit dieser UID vorhanden';
				return false;
			}
		}
		else
		{
			$this->errormsg = 'Fehler beim Auslesen des Studenten';
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
		if($this->uid=='')
		{
			$this->errormsg = 'UID muss eingegeben werden';
			return false;
		}
		if(mb_strlen($this->matrikelnr)>15)
		{
			$this->errormsg = 'Matrikelnummer darf nicht laenger als 15 Zeichen sein';
			return false;
		}
		if(mb_strlen($this->updatevon)>32)
		{
			$this->errormsg = 'Updatevon darf nicht laenger als 32 Zeichen sein';
			return false;
		}
		if($this->prestudent_id!='' && !is_numeric($this->prestudent_id))
		{
			$this->errormsg = 'Prestudent_id muss eine gueltige Zahl sein';
			return false;
		}
		if(!is_numeric($this->studiengang_kz))
		{
			$this->errormsg  = 'Studiengang_id muss eine gueltige Zahl sein';
			return false;
		}
		if(!is_numeric($this->semester))
		{
			$this->errormsg = 'Semester muss ein gueltige Zahl sein';
			return false;
		}
		if(mb_strlen($this->verband)>1)
		{
			$this->errormsg = 'Verband darf nicht laenger als 1 Zeichen sein';
			return false;
		}
		if(mb_strlen($this->gruppe)>1)
		{
			$this->errormsg = 'Gruppe darf nicht laenger als 1 Zeichen sein';
			return false;
		}

		return true;
	}


	/**
	 * Speichert die Studentendaten in die Datenbank
	 * Wenn $new auf true gesetzt ist wird ein neuer Datensatz
	 * angelegt, ansonsten der Datensatz mit $person_id upgedated
	 * @return true wenn erfolgreich, false im Fehlerfall
	 */
	public function save($new=null, $savebenutzer=true)
	{
		//Variablen checken
		if(!$this->validate())
			return false;

		$this->db_query('BEGIN;');

		if($new==null)
			$new = $this->new;

		if($savebenutzer)
		{
			//Basisdaten speichern
			if(!benutzer::save())
			{
				$this->db_query('ROLLBACK;');
				return false;
			}
		}

		if($new)
		{
			//Neuen Datensatz anlegen
			$qry = "INSERT INTO public.tbl_student(student_uid, matrikelnr, updateamum, updatevon, prestudent_id,
								studiengang_kz, semester, verband, gruppe, insertamum, insertvon)
					VALUES(".$this->db_add_param($this->uid).",".
			 	 	$this->db_add_param($this->matrikelnr).",".
			 	 	$this->db_add_param($this->updateamum).','.
			 	 	$this->db_add_param($this->updatevon).','.
			 	 	$this->db_add_param($this->prestudent_id, FHC_INTEGER).','.
					$this->db_add_param($this->studiengang_kz).','.
					$this->db_add_param($this->semester).','.
					$this->db_add_param(($this->verband==''?' ':$this->verband)).','.
					$this->db_add_param(($this->gruppe==''?' ':$this->gruppe)).','.
					$this->db_add_param($this->insertamum).','.
					$this->db_add_param($this->insertvon).');';
		}
		else
		{
			//Bestehenden Datensatz updaten
			$qry = 'UPDATE public.tbl_student SET'.
				   ' matrikelnr='.$this->db_add_param($this->matrikelnr).','.
				   ' updateamum='.$this->db_add_param($this->updateamum).','.
				   ' updatevon='.$this->db_add_param($this->updatevon).','.
				   ' studiengang_kz='.$this->db_add_param($this->studiengang_kz).','.
				   ' semester='.$this->db_add_param($this->semester).','.
				   ' verband='.$this->db_add_param(($this->verband==''?' ':$this->verband)).','.
				   ' gruppe='.$this->db_add_param(($this->gruppe==''?' ':$this->gruppe)).
				   " WHERE student_uid=".$this->db_add_param($this->uid).";";
		}

		if($this->db_query($qry))
		{
			$this->db_query('COMMIT;');
			//Log schreiben
			return true;
		}
		else
		{
			$this->db_query('ROLLBACK;');
			$this->errormsg = 'Fehler beim Speichern des Studenten-Datensatzes';
			return false;
		}
	}

	/**
	 * Rueckgabewert ist die Anzahl der Ergebnisse. Bei Fehler negativ und die
	 * Fehlermeldung liegt in errormsg.
	 * Wenn der Parameter stg_kz NULL ist tritt gruppe in Kraft.
	 * @param string $einheit_kurzbz    Einheit
	 * @param string grp    Gruppe
	 * @param string ver    Verband
	 * @param integer sem    Semester
	 * @param integer stg_kz    Kennzahl des Studiengangs
	 * @return integer Anzahl der gefundenen Einträge; <b>negativ</b> bei Fehler
	 */
	public function getStudents($stg_kz,$sem=null,$ver=null,$grp=null,$gruppe=null, $stsem=null)
	{
		$where = '';
		if ($gruppe!=null)
		{
			$where=" gruppe_kurzbz=".$this->db_add_param($gruppe)." AND tbl_benutzer.uid=tbl_benutzergruppe.uid";
			if($stsem!=null)
				$where.=" AND tbl_benutzergruppe.studiensemester_kurzbz=".$this->db_add_param($stsem);
		}
		else
		{
			$where.=" tbl_studentlehrverband.studiengang_kz=".$this->db_add_param($stg_kz);
			if ($sem!=null)
				$where.=" AND tbl_studentlehrverband.semester=".$this->db_add_param($sem);
			if ($ver!=null)
				$where.=" AND tbl_studentlehrverband.verband=".$this->db_add_param($ver);
			if ($grp!=null)
				$where.=" AND tbl_studentlehrverband.gruppe=".$this->db_add_param($grp);
		}

		if($stsem!=null)
				$where.=" AND tbl_studentlehrverband.studiensemester_kurzbz=".$this->db_add_param($stsem);

		$sql_query = "SELECT *, tbl_student.semester as std_semester, tbl_student.verband as std_verband, tbl_student.gruppe as std_gruppe, tbl_student.studiengang_kz as std_studiengang_kz,
					  tbl_studentlehrverband.studiengang_kz as lvb_studiengang_kz, tbl_studentlehrverband.semester as lvb_semester, tbl_studentlehrverband.verband as lvb_verband, tbl_studentlehrverband.gruppe as lvb_gruppe
					  FROM public.tbl_person, public.tbl_student, public.tbl_benutzer, public.tbl_studentlehrverband, public.tbl_prestudent";
		if($gruppe!=null)
			$sql_query.= ",public.tbl_benutzergruppe";
		$sql_query.= " WHERE tbl_prestudent.prestudent_id=tbl_student.prestudent_id AND tbl_person.person_id=tbl_benutzer.person_id AND tbl_benutzer.uid = tbl_student.student_uid AND tbl_studentlehrverband.student_uid=tbl_student.student_uid AND $where ORDER BY nachname, vorname";

		if(!$this->db_query($sql_query))
		{
			$this->errormsg=$this->db_last_error();
			return false;
		}
		$result=array();

		while($row = $this->db_fetch_object())
		{
			$l=new student();
			// Personendaten
			$l->uid=$row->uid;
			$l->person_id=$row->person_id;
			$l->prestudent_id=$row->prestudent_id;
			$l->titelpre=$row->titelpre;
			$l->titelpost=$row->titelpost;
			$l->vornamen=$row->vornamen;
			$l->vorname=$row->vorname;
			$l->nachname=$row->nachname;
			$l->gebdatum=$row->gebdatum;
			$l->gebort=$row->gebort;
			$l->gebzeit=$row->gebzeit;
			$l->familienstand = $row->familienstand;
			$l->svnr=$row->svnr;
			$l->foto=$row->foto;
			$l->anmerkungen=$row->anmerkung;
			$l->aktiv=$this->db_parse_bool($row->aktiv);
			$l->alias=$row->alias;
			$l->homepage=$row->homepage;
			$l->updateamum=(isset($row->updateamum)?$row->updateamum:'');
			$l->updatevon=(isset($row->updatevon)?$row->updatevon:'');
			// Studentendaten
			$l->matrikelnr=$row->matrikelnr;
			$l->gruppe=$row->lvb_gruppe;
			$l->verband=$row->lvb_verband;
			$l->semester=$row->lvb_semester;
			$l->studiengang_kz=$row->lvb_studiengang_kz;
			$l->staatsbuergerschaft = $row->staatsbuergerschaft;

			$l->zgv_code = $row->zgv_code;
			$l->zgvort = $row->zgvort;
			$l->zgvdatum = $row->zgvdatum;
			$l->zgvmas_code = $row->zgvmas_code;
			$l->zgvmaort = $row->zgvmaort;
			$l->zgvmadatum = $row->zgvmadatum;
			//$l->stg_bezeichnung=$row->bezeichnung;
			// student in Array speichern
			$result[]=$l;

		}
		return $result;
	}


	/**
	 * Gibt Studenten zurück die im übergebenen Studiengang und semester sind
	 * @param $studiengang_kz
	 * @param $semester
	 * @return boolean
	 */
	public function getStudentsStudiengang($studiengang_kz, $semester = null)
	{
		$qry = "SELECT * FROM public.tbl_student
			JOIN public.tbl_benutzer ON (student_uid = uid)
			JOIN public.tbl_person USING (person_id)
			WHERE tbl_benutzer.aktiv = 'true'";
		if($studiengang_kz!='')
			$qry.=" AND studiengang_kz =".$this->db_add_param($studiengang_kz,FHC_INTEGER);

		if($semester != null)
			$qry .= " AND semester =".$this->db_add_param($semester, FHC_INTEGER);
		$qry.=" ORDER BY nachname, vorname";


		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				$stud = new student();
				$stud->uid = $row->student_uid;
				$stud->matrikelnr = $row->matrikelnr;
				$stud->prestudent_id = $row->prestudent_id;
				$stud->studiengang_kz = $row->studiengang_kz;
				$stud->semester = $row->semester;
				$stud->verband = $row->verband;
				$stud->gruppe = $row->gruppe;
				$stud->person_id = $row->person_id;
				$stud->vorname = $row->vorname;
				$stud->nachname = $row->nachname;
				$stud->gebdatum = $row->gebdatum;

				$this->result[] = $stud;
			}
			return true;
		}
		else
		{
			$this->errormsg = "Fehler bei der Abfrage aufgetreten";
			return false;
		}
	}

	/**
	 * Prueft ob die StudentLehrverband Zuteilung
	 * bereits existiert
	 * @param student_uid
	 * @param studiensemester_kurzbz
	 * @return true wenn vorhanden, false wenn nicht
	 */
	public function studentlehrverband_exists($student_uid, $studiensemester_kurzbz)
	{
		$qry = "SELECT count(*) as anzahl FROM public.tbl_studentlehrverband
				WHERE student_uid=".$this->db_add_param($student_uid)." AND studiensemester_kurzbz=".$this->db_add_param($studiensemester_kurzbz);

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
				$this->errormsg = 'Fehler beim Ermitteln des Lehrverbandes';
				return false;
			}
		}
		else
		{
			$this->errormsg ='Fehler beim Ermitteln des Lehrverbandes';
			return false;
		}
	}

	/**
	 * Prueft ob die StudentLehrverband Zuteilung
	 * bereits existiert
	 * @param student_uid
	 *        studiensemester_kurzbz
	 * @return true wenn vorhanden, false wenn nicht
	 */
	public function load_studentlehrverband($student_uid, $studiensemester_kurzbz)
	{
		$qry = "SELECT * FROM public.tbl_studentlehrverband
				WHERE student_uid=".$this->db_add_param($student_uid)."
				AND studiensemester_kurzbz=".$this->db_add_param($studiensemester_kurzbz);

		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				$this->uid = $row->student_uid;
				$this->studiensemester_kurzbz = $row->studiensemester_kurzbz;
				$this->studiengang_kz = $row->studiengang_kz;
				$this->semester = $row->semester;
				$this->verband = $row->verband;
				$this->gruppe = $row->gruppe;
				$this->updateamum = $row->updateamum;
				$this->updatevon = $row->updatevon;
				$this->insertamum = $row->insertamum;
				$this->insertvon = $row->insertvon;

				return true;
			}
			else
			{
				$this->errormsg = 'Fehler beim Ermitteln des Lehrverbandes';
				return false;
			}
		}
		else
		{
			$this->errormsg ='Fehler beim Ermitteln des Lehrverbandes';
			return false;
		}
	}

	/**
	 * Speichert die Zuteilung von Student zu Lehrverband
	 * @param $new
	 * @return boolean
	 */
	public function save_studentlehrverband($new=null)
	{
		if($new==null)
			$new = $this->new;

		if($new)
		{
			$qry = "INSERT INTO public.tbl_studentlehrverband (student_uid, studiensemester_kurzbz, studiengang_kz, semester, verband, gruppe, updateamum, updatevon, insertamum, insertvon)
					VALUES(".$this->db_add_param($this->uid).','.
					$this->db_add_param($this->studiensemester_kurzbz).','.
					$this->db_add_param($this->studiengang_kz).','.
					$this->db_add_param($this->semester).','.
					$this->db_add_param(($this->verband==''?' ':$this->verband)).','.
					$this->db_add_param(($this->gruppe==''?' ':$this->gruppe)).','.
					$this->db_add_param($this->updateamum).','.
					$this->db_add_param($this->updatevon).','.
					$this->db_add_param($this->insertamum).','.
					$this->db_add_param($this->insertvon).');';
		}
		else
		{
			$qry = "UPDATE public.tbl_studentlehrverband SET".
					" studiengang_kz=".$this->db_add_param($this->studiengang_kz).",".
					" semester=".$this->db_add_param($this->semester).",".
					" verband=".$this->db_add_param(($this->verband==''?' ':$this->verband)).",".
					" gruppe=".$this->db_add_param(($this->gruppe==''?' ':$this->gruppe)).",".
					" updateamum=".$this->db_add_param($this->updateamum).",".
					" updatevon=".$this->db_add_param($this->updatevon).
					" WHERE student_uid=".$this->db_add_param($this->uid)." AND studiensemester_kurzbz=".$this->db_add_param($this->studiensemester_kurzbz);
		}

		if($this->db_query($qry))
		{
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern der Studentlehrverbandzuordnung';
			return false;
		}
	}

	/**
	 * Laedt die UID anhand der Prestudent_id
	 * @param prestudent_id
	 * @return uid wenn ok, false wenn Fehler
	 */
	public function getUid($prestudent_id)
	{
		if(!is_numeric($prestudent_id))
		{
			$this->errormsg = 'PrestudentID ist ungueltig';
			return false;
		}

		$qry = "SELECT student_uid FROM public.tbl_student WHERE prestudent_id=".$this->db_add_param($prestudent_id);

		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				return $row->student_uid;
			}
			else
			{
				$this->errormsg = 'Student nicht gefunden';
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
	 * Check, ob inputparameter gültige studenten_id ist
	 * @param matrikelnummer oder student_uid
	 * @return ok, wenn gültige Id, sonst false
	 */
	public function checkIfValidStudentUID($uid)
	{
		$qry = "SELECT student_uid FROM public.tbl_student WHERE student_uid=".$this->db_add_param($uid);

		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
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
	 * Laedt die UID anhand der Matrikelnummer
	 * @param matrikelnummer
	 * @return uid wenn ok, false wenn Fehler
	 */
	public function getUidFromMatrikelnummer($matrikelnummer)
	{
		$qry = "SELECT student_uid FROM public.tbl_student WHERE matrikelnr=".$this->db_add_param($matrikelnummer);

		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				return $row->student_uid;
			}
			else
			{
				$this->errormsg = 'Student nicht gefunden';
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
	 * Laedt die Daten eines Studenten anhand der Person_id und des Studienganges
	 * Wenn mehrere Eintraege fuer diesen Studiengang vorhanden sind, dann wird der zuletzt eingetragene verwendet
	 *
	 * @param person_id
	 * @param studiengang_kz
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function load_person($person_id, $studiengang_kz = null)
	{
		$qry = "SELECT tbl_student.* FROM public.tbl_benutzer JOIN public.tbl_student ON(uid=student_uid)
				WHERE person_id=".$this->db_add_param($person_id, FHC_INTEGER);

		if($studiengang_kz != '')
		{
			$qry .= " AND studiengang_kz=".$this->db_add_param($studiengang_kz, FHC_INTEGER);
		}

		$qry .= " ORDER BY prestudent_id DESC LIMIT 1";

		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				$this->uid = $row->student_uid;
				$this->matrikelnr = $row->matrikelnr;
				$this->prestudent_id = $row->prestudent_id;
				$this->studiengang_kz = $row->studiengang_kz;
				$this->semester = $row->semester;
				$this->verband = $row->verband;
				$this->gruppe = $row->gruppe;
				$this->updateamum = $row->updateamum;
				$this->updatevon = $row->updatevon;
				$this->insertamum = $row->insertamum;
				$this->insertvon = $row->insertvon;
				$this->ext_id = $row->ext_id;

				return true;
			}
			else
			{
				$this->errormsg = 'Kein Benutzer mit dieser UID vorhanden';
				return false;
			}
		}
		else
		{
			$this->errormsg = 'Fehler beim Auslesen des Studenten';
			return false;
		}
	}

	/**
	 * Liefert die Tabellenelemente die den Kriterien der Parameter entsprechen
	 * Ueberschreibt die Methode aus der Klasse Person
	 * @param $filter String mit Vorname oder Nachname
	 * @param $order Sortierkriterium
	 * @return array mit Personen oder false wenn ein Fehler auftritt
	 */
	public function getTab($filter, $order='person_id')
	{
		$sql_query = "SELECT
						person_id, staatsbuergerschaft, geburtsnation, sprache, anrede, titelpost, titelpre,
						nachname, vorname, vornamen, gebdatum, gebort, gebzeit, anmerkung, homepage, svnr,
						ersatzkennzeichen, familienstand, geschlecht, anzahlkinder, tbl_person.aktiv, kurzbeschreibung,
						tbl_benutzer.aktiv as bnaktiv, tbl_student.studiengang_kz, tbl_student.semester, tbl_student.verband,
						tbl_student.gruppe, tbl_student.prestudent_id, tbl_benutzer.uid
					  FROM
					  	public.tbl_person
						JOIN public.tbl_benutzer USING(person_id)
						JOIN public.tbl_student ON(student_uid=uid)
					WHERE true ";

		if($filter!='')
		{
			$sql_query.=" AND 	nachname ~* ".$this->db_add_param($filter)." OR
								vorname ~* ".$this->db_add_param($filter)." OR
								(nachname || ' ' || vorname) ~* ".$this->db_add_param($filter)." OR
								(vorname || ' ' || nachname) ~* ".$this->db_add_param($filter);
		}

		$sql_query .= " ORDER BY $order";
		if($filter=='')
		   $sql_query .= " LIMIT 30";

		if($this->db_query($sql_query))
		{
			while($row = $this->db_fetch_object())
			{
				$l = new student();
				$l->person_id = $row->person_id;
				$l->staatsbuergerschaft = $row->staatsbuergerschaft;
				$l->geburtsnation = $row->geburtsnation;
				$l->sprache = $row->sprache;
				$l->anrede = $row->anrede;
				$l->titelpost = $row->titelpost;
				$l->titelpre = $row->titelpre;
				$l->nachname = $row->nachname;
				$l->vorname = $row->vorname;
				$l->vornamen = $row->vornamen;
				$l->gebdatum = $row->gebdatum;
				$l->gebort = $row->gebort;
				$l->gebzeit = $row->gebzeit;
				$l->anmerkungen = $row->anmerkung;
				$l->homepage = $row->homepage;
				$l->svnr = $row->svnr;
				$l->ersatzkennzeichen = $row->ersatzkennzeichen;
				$l->familienstand = $row->familienstand;
				$l->geschlecht = $row->geschlecht;
				$l->anzahlkinder = $row->anzahlkinder;
				$l->aktiv = $this->db_parse_bool($row->aktiv);
				$l->kurzbeschreibung = $row->kurzbeschreibung;
				$l->bnaktiv = $this->db_parse_bool($row->bnaktiv);
				$l->studiengang_kz = $row->studiengang_kz;
				$l->semester = $row->semester;
				$l->verband = $row->verband;
				$l->gruppe = $row->gruppe;
				$l->prestudent_id = $row->prestudent_id;
				$l->uid = $row->uid;
				$this->result[]=$l;
			}
		}
		else
		{
			$this->errormsg = $this->db_last_error();
			return false;
		}
		return true;
	}

	/**
	 * Laedt alle Incoming
	 * @return boolean
	 */
	public function getIncoming()
	{
		$qry = "
			SELECT
				distinct tbl_student.*, tbl_benutzer.*, tbl_person.*
			FROM
				public.tbl_student
				JOIN public.tbl_benutzer ON (student_uid = uid)
				JOIN public.tbl_person USING (person_id)
				JOIN public.tbl_prestudent USING (prestudent_id)
				JOIN public.tbl_prestudentstatus USING(prestudent_id)
			WHERE
				tbl_benutzer.aktiv AND
				tbl_prestudentstatus.status_kurzbz='Incoming'
			";

		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				$stud = new student();
				$stud->uid = $row->student_uid;
				$stud->matrikelnr = $row->matrikelnr;
				$stud->prestudent_id = $row->prestudent_id;
				$stud->studiengang_kz = $row->studiengang_kz;
				$stud->semester = $row->semester;
				$stud->verband = $row->verband;
				$stud->gruppe = $row->gruppe;
				$stud->person_id = $row->person_id;
				$stud->vorname = $row->vorname;
				$stud->nachname = $row->nachname;
				$stud->gebdatum = $row->gebdatum;

				$this->result[] = $stud;
			}
			return true;
		}
		else
		{
			$this->errormsg = "Fehler bei der Abfrage aufgetreten";
			return false;
		}
	}

	public function getStudentUidsForMeldung($studiensemester1, $studiensemester2, $studiensemester3, $zeitraumStart, $zeitraumEnde)
	{
		$qry = "SELECT DISTINCT ON(student_uid)* FROM public.tbl_student
				JOIN public.tbl_benutzer ON(student_uid = uid)
				JOIN public.tbl_person USING(person_id)
				JOIN public.tbl_prestudent USING(prestudent_id)
				JOIN public.tbl_prestudentstatus ps USING(prestudent_id)
			WHERE
				bismelden
				AND ps.studiensemester_kurzbz
				IN(".$this->db_add_param($studiensemester1).","
				.$this->db_add_param($studiensemester2).","
				.$this->db_add_param($studiensemester3).")
				AND ps.datum > ".$this->db_add_param($zeitraumStart)."
				AND ps.datum <= ".$this->db_add_param($zeitraumEnde)."
				AND ps.status_kurzbz IN('Student','Unterbrecher','Abbrecher','Absolvent');";

		if($result = $this->db_query($qry))
		{
			$uids = array();
			while($row = $this->db_fetch_object($result))
			{
			array_push($uids, $row->student_uid);
			}
			return $uids;
		}
		return false;
	}

	/**
	 * Löscht die Zuordnung eines Studenten zu einer Lehrverbandsgruppe
	 * @param type $uid
	 * @param type $studiengang_kz
	 * @param type $studiensemester
	 * @param type $semester
	 * @param type $verband
	 * @param type $gruppe
	 */
	public function delete_studentLehrverband($uid, $studiengang_kz, $studiensemester, $semester)
	{
		$qry = 'DELETE FROM public.tbl_studentlehrverband '
			. 'WHERE student_uid='.$this->db_add_param($uid)
			. ' AND studiensemester_kurzbz='.$this->db_add_param($studiensemester)
			. ' AND studiengang_kz='.$this->db_add_param($studiengang_kz)
			. ' AND semester='.$this->db_add_param($semester).';';

		if($this->db_query($qry))
		{
			return true;
		}
		else
		{
			$this->errormsg = 'StudentLehrverband konnte nicht gelöscht werden.';
			return false;
		}
	}

	/**
	 * Lädt alle LV eines Studenten für ein Semester (standardmäßig aktuelles Semester)
	 *
	 * @param string $uid
	 * @param string $studiensemester
	 * @return boolean
	 */
	public function get_lv($uid, $studiensemester = null)
	{

		if(!$studiensemester)
		{
			$sem = new studiensemester;
			$studiensemester = $sem->getNearest();
		}

		$qry = 'SELECT * '
				. 'FROM campus.vw_student_lehrveranstaltung '
				. 'WHERE uid = ' . $this->db_add_param($uid)
				. 'AND studiensemester_kurzbz = ' . $this->db_add_param($studiensemester)
				. ' ORDER BY bezeichnung';

		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				$this->result[] = $row;
			}
			return true;
		}
		else
		{
			$this->errormsg = "Fehler bei der Abfrage aufgetreten";
			return false;
		}
	}

	/**
	 * Checkt, ob ein Student schon BIS-Gemeldet wurde.
	 *
	 * @param string $uid
	 * @return boolean True, wenn er schon gemeldet wurde, False, wenn nicht und Null im Fehlerfall
	 */
	public function isStudentBisGemeldet($uid)
	{
		$qry = "SELECT datum
				FROM PUBLIC.tbl_student
				JOIN PUBLIC.tbl_prestudent USING (prestudent_id)
				JOIN PUBLIC.tbl_prestudentstatus USING (prestudent_id)
				WHERE student_uid = " . $this->db_add_param($uid) . "
					AND status_kurzbz = 'Student'
					AND bismelden = true
				ORDER BY ausbildungssemester ASC LIMIT 1";

		// Datum der letzten BIS-Meldung herausfinden
		$datumLetzteMeldung = '';
		$datumNovemberVorjahr = date('Y', strtotime("-1 year")).'-11-15';
		$datumApril = date('Y').'-04-15';
		$datumNovember = date('Y').'-11-15';

		$timestampNovemberVorjahr = strtotime(date('Y', strtotime("-1 year")).'-11-15');
		$timestampApril = strtotime(date('Y').'-04-15');
		$timestampNovember = strtotime(date('Y').'-11-15');

		$heute = time();

		if ($heute - $timestampNovemberVorjahr >= $heute - $timestampApril &&
			$heute - $timestampApril < 0)
			$datumLetzteMeldung = $datumNovemberVorjahr;
		elseif ($heute - $timestampApril >= $heute - $timestampNovember &&
			$heute - $timestampNovember < 0)
			$datumLetzteMeldung = $datumApril;
		else
			$datumLetzteMeldung = $datumNovember;

		if($result = $this->db_query($qry))
		{
			if($this->db_num_rows($result) > 0)
			{
				if($row = $this->db_fetch_object($result))
				{
					// Wenn der Studentenstatus kleiner oder gleich dem Datum der letzten Meldung ist, wurde der Student gemeldet
					if (strtotime($row->datum) <= strtotime($datumLetzteMeldung))
						return true;
					else
						return false;
				}
				else
				{
					$this->errormsg = 'Es wurde kein Datum oder Student gefunden';
					return null;
				}
			}
			else
				return null;
		}
		else
		{
			$this->errormsg = 'Fehler beim ausführen der Abfrage';
			return null;
		}
	}
}
