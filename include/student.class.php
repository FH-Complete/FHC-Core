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
	public function load($uid, $studiensemester_kurzbz=null)
	{
		if(!benutzer::load($uid))
			return false;
		if(is_null($studiensemester_kurzbz))
			$qry = "SELECT * FROM public.tbl_student WHERE student_uid='".addslashes($uid)."'";
		else
			$qry = "SELECT *, tbl_studentlehrverband.studiengang_kz as studiengang_kz, tbl_studentlehrverband.semester as semester,
					tbl_studentlehrverband.verband as verband, tbl_studentlehrverband.gruppe as gruppe  
					FROM public.tbl_student JOIN public.tbl_studentlehrverband USING(student_uid) 
					WHERE studiensemester_kurzbz='".addslashes($studiensemester_kurzbz)."' AND student_uid='".addslashes($uid)."'";
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
			$this->errormsg = 'Fehler beim Auslesen des Studenten '.$qry;
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
		if(mb_strlen($this->uid)>16)
		{
			$this->errormsg = 'UID darf nicht laenger als 16 Zeichen sein';
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
			                    studiengang_kz, semester, ext_id, verband, gruppe, insertamum, insertvon)
			        VALUES('".addslashes($this->uid)."',".
			 	 	$this->addslashes($this->matrikelnr).",".
			 	 	$this->addslashes($this->updateamum).','.
			 	 	$this->addslashes($this->updatevon).','.
			 	 	$this->addslashes($this->prestudent_id).','.
					$this->studiengang_kz.','.
					$this->semester.','.
					($this->ext_id_student!=''?$this->ext_id_student:'null').','.
					($this->verband!=''?"'".addslashes($this->verband)."'":"' '").','.
					($this->gruppe!=''?"'".addslashes($this->gruppe)."'":"' '").','.
					$this->addslashes($this->insertamum).','.
					$this->addslashes($this->insertvon).');';
		}
		else
		{
			//Bestehenden Datensatz updaten
			$qry = 'UPDATE public.tbl_student SET'.
			       ' matrikelnr='.$this->addslashes($this->matrikelnr).','.
			       ' updateamum='.$this->addslashes($this->updateamum).','.
			       ' updatevon='.$this->addslashes($this->updatevon).','.
			       //' prestudent_id='.$this->addslashes($this->prestudent_id).','.
			       ' studiengang_kz='.$this->studiengang_kz.','.
			       ' semester='.$this->semester.','.
			       ' ext_id='.($this->ext_id_student!=''?$this->ext_id_student:'null').','.
			       ' verband='.($this->verband!=''?"'".addslashes($this->verband)."'":"' '").','.
			       ' gruppe='.($this->gruppe!=''?"'".addslashes($this->gruppe)."'":"' '").
			       " WHERE student_uid='".addslashes($this->uid)."';";
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
			$this->errormsg = 'Fehler beim Speichern des Studenten-Datensatzes'.$qry;
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
			$where=" gruppe_kurzbz='".addslashes($gruppe)."' AND tbl_benutzer.uid=tbl_benutzergruppe.uid";
			if($stsem!=null)
				$where.=" AND tbl_benutzergruppe.studiensemester_kurzbz='".addslashes($stsem)."'";
		}
		else
		{
			if ($stg_kz>=0)
			{
				$where.=" tbl_studentlehrverband.studiengang_kz='".addslashes($stg_kz)."'";
				if ($sem!=null)
					$where.=" AND tbl_studentlehrverband.semester='".addslashes($sem)."'";
				if ($ver!=null)
					$where.=" AND tbl_studentlehrverband.verband='".addslashes($ver)."'";
				if ($grp!=null)
					$where.=" AND tbl_studentlehrverband.gruppe='".addslashes($grp)."'";
			}
		}			

		if($stsem!=null)
				$where.=" AND tbl_studentlehrverband.studiensemester_kurzbz='".addslashes($stsem)."'";
		
		//$sql_query="SELECT * FROM campus.vw_student WHERE $where ORDER by nachname,vorname";
		$sql_query = "SELECT *, tbl_student.semester as std_semester, tbl_student.verband as std_verband, tbl_student.gruppe as std_gruppe, tbl_student.studiengang_kz as std_studiengang_kz,
					  tbl_studentlehrverband.studiengang_kz as lvb_studiengang_kz, tbl_studentlehrverband.semester as lvb_semester, tbl_studentlehrverband.verband as lvb_verband, tbl_studentlehrverband.gruppe as lvb_gruppe 
					  FROM public.tbl_person, public.tbl_student, public.tbl_benutzer, public.tbl_studentlehrverband, public.tbl_prestudent";
		if($gruppe!=null)
			$sql_query.= ",public.tbl_benutzergruppe";
		$sql_query.= " WHERE tbl_prestudent.prestudent_id=tbl_student.prestudent_id AND tbl_person.person_id=tbl_benutzer.person_id AND tbl_benutzer.uid = tbl_student.student_uid AND tbl_studentlehrverband.student_uid=tbl_student.student_uid AND $where ORDER BY nachname, vorname";
	    //echo $sql_query;
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
			$l->aktiv=$row->aktiv=='t'?true:false;
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
	 * Prueft ob die StudentLehrverband Zuteilung
	 * bereits existiert
	 * @param student_uid
	 * @param studiensemester_kurzbz
	 * @return true wenn vorhanden, false wenn nicht
	 */
	public function studentlehrverband_exists($student_uid, $studiensemester_kurzbz)
	{
		$qry = "SELECT count(*) as anzahl FROM public.tbl_studentlehrverband 
				WHERE student_uid='".addslashes($student_uid)."' AND studiensemester_kurzbz='".addslashes($studiensemester_kurzbz)."'";
		
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
				WHERE student_uid='".addslashes($student_uid)."' 
				AND studiensemester_kurzbz='".addslashes($studiensemester_kurzbz)."'";
		
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
					VALUES(".$this->addslashes($this->uid).','.
					$this->addslashes($this->studiensemester_kurzbz).','.
					$this->addslashes($this->studiengang_kz).','.
					$this->addslashes($this->semester).','.
					$this->addslashes(($this->verband==''?' ':$this->verband)).','.
					$this->addslashes(($this->gruppe==''?' ':$this->gruppe)).','.
					$this->addslashes($this->updateamum).','.
					$this->addslashes($this->updatevon).','.
					$this->addslashes($this->insertamum).','.
					$this->addslashes($this->insertvon).');';
		}
		else 
		{
			$qry = "UPDATE public.tbl_studentlehrverband SET".
					" studiengang_kz=".$this->addslashes($this->studiengang_kz).",".
					" semester=".$this->addslashes($this->semester).",".
					" verband=".$this->addslashes(($this->verband==''?' ':$this->verband)).",".
					" gruppe=".$this->addslashes(($this->gruppe==''?' ':$this->gruppe)).",".
					" updateamum=".$this->addslashes($this->updateamum).",".
					" updatevon=".$this->addslashes($this->updatevon).
					" WHERE student_uid='".addslashes($this->uid)."' AND studiensemester_kurzbz='".addslashes($this->studiensemester_kurzbz)."'";
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
		
		$qry = "SELECT student_uid FROM public.tbl_student WHERE prestudent_id='$prestudent_id'";
		
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
	 * Laedt die UID anhand der Matrikelnummer
	 * @param matrikelnummer
	 * @return uid wenn ok, false wenn Fehler
	 */
	public function getUidFromMatrikelnummer($matrikelnummer)
	{
		$qry = "SELECT student_uid FROM public.tbl_student WHERE matrikelnr='".addslashes($matrikelnummer)."'";
		
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
}
?>