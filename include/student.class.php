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
	var $matrikelnr;
	var $prestudent_id;
	var $studiengang_kz;
	var $semester;
	var $verband;
	var $gruppe;
	var $ext_id_student;

	// *************************************************************************
	// * Konstruktor - Uebergibt die Connection und laedt optional einen Studenten
	// * @param $conn        	Datenbank-Connection
	// *        $uid			Student der geladen werden soll (default=null)
	// *        $unicode     	Gibt an ob die Daten mit UNICODE Codierung
	// *                     	oder LATIN9 Codierung verarbeitet werden sollen
	// *************************************************************************
	function student($conn, $uid=null, $unicode=false)
	{
		$this->conn = $conn;

		if($unicode)
			$qry = "SET CLIENT_ENCODING TO 'UNICODE';";
		else
			$qry = "SET CLIENT_ENCODING TO 'LATIN9';";

		if(!pg_query($conn,$qry))
		{
			$this->errormsg	 = 'Encoding konnte nicht gesetzt werden';
			return false;
		}

		//Student laden
		if($uid!=null)
			$this->load($uid);
	}

	function load($uid)
	{
		if(!benutzer::load($uid))
			return false;

		$qry = "SELECT * FROM public.tbl_student WHERE student_uid='".addslashes($uid)."'";

		if($result = pg_query($this->conn, $qry))
		{
			if($row = pg_fetch_object($result))
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

	// *******************************************
	// * Prueft die Variablen vor dem Speichern
	// * auf Gueltigkeit.
	// * @return true wenn ok, false im Fehlerfall
	// *******************************************
	function validate()
	{
		if(strlen($this->uid)>16)
		{
			$this->errormsg = 'UID darf nicht laenger als 16 Zeichen sein';
			return false;
		}
		if($this->uid=='')
		{
			$this->errormsg = 'UID muss eingegeben werden';
			return false;
		}
		if(strlen($this->matrikelnr)>15)
		{
			$this->errormsg = 'Matrikelnummer darf nicht laenger als 15 Zeichen sein';
			return false;
		}
		if(strlen($this->updatevon)>32)
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
		if(strlen($this->verband)>1)
		{
			$this->errormsg = 'Verband darf nicht laenger als 1 Zeichen sein';
			return false;
		}
		if(strlen($this->gruppe)>1)
		{
			$this->errormsg = 'Gruppe darf nicht laenger als 1 Zeichen sein';
			return false;
		}

		return true;
	}


	// ************************************************************
	// * Speichert die Studentendaten in die Datenbank
	// * Wenn $new auf true gesetzt ist wird ein neuer Datensatz
	// * angelegt, ansonsten der Datensatz mit $person_id upgedated
	// * @return true wenn erfolgreich, false im Fehlerfall
	// ************************************************************
	function save()
	{
		//Variablen checken
		if(!$this->validate())
			return false;

		pg_query($this->conn,'BEGIN;');
		//Basisdaten speichern
		if(!benutzer::save())
		{
			pg_query($this->conn,'ROLLBACK;');
			return false;
		}

		if($this->new)
		{
			//Neuen Datensatz anlegen
			$qry = "INSERT INTO public.tbl_student(student_uid, matrikelnr, updateamum, updatevon, prestudent_id,
			                    studiengang_kz, semester, ext_id, verband, gruppe)
			        VALUES('".addslashes($this->uid)."',".
			 	 	$this->addslashes($this->matrikelnr).",".
			 	 	$this->addslashes($this->updateamum).','.
			 	 	$this->addslashes($this->updatevon).','.
			 	 	$this->addslashes($this->prestudent_id).','.
					$this->studiengang_kz.','.
					$this->semester.','.
					($this->ext_id_student!=''?$this->ext_id_student:'null').','.
					($this->verband!=''?"'".addslashes($this->verband)."'":' ').','.
					($this->gruppe!=''?"'".addslashes($this->gruppe)."'":' ').');';
		}
		else
		{
			//Bestehenden Datensatz updaten
			$qry = 'UPDATE public.tbl_student SET'.
			       ' matrikelnr='.$this->addslashes($this->matrikelnr).','.
			       ' updateamum='.$this->addslashes($this->updateamum).','.
			       ' updatevon='.$this->addslashes($this->updatevon).','.
			       ' prestudent_id='.$this->addslashes($this->prestudent_id).','.
			       ' studiengang_kz='.$this->studiengang_kz.','.
			       ' semester='.$this->semester.','.
			       ' ext_id='.($this->ext_id_student!=''?$this->ext_id_student:'null').','.
			       ' verband='.$this->addslashes($this->verband).','.
			       ' gruppe='.$this->addslashes($this->gruppe).
			       " WHERE student_uid='".addslashes($this->uid)."';";
		}
		
		if(pg_query($this->conn,$qry))
		{
			pg_query($this->conn,'COMMIT;');
			//Log schreiben
			return true;
		}
		else
		{
			pg_query($this->conn,'ROLLBACK;');
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

	function getStudents($stg_kz,$sem=null,$ver=null,$grp=null,$gruppe=null)
	{
		$where = '';
		if ($gruppe!=null)
			$where=" gruppe_kurzbz='".$gruppe."'";
		else
		{
			if ($stg_kz>=0)
			{
				$where.=" studiengang_kz=$stg_kz";
				if ($sem!=null)
					$where.=" AND semester=$sem";
				if ($ver!=null)
					$where.=" AND verband='".$ver."'";
				if ($grp!=null)
					$where.=" AND gruppe='".$grp."'";
			}
		}


		$sql_query="SELECT * FROM campus.vw_student WHERE $where ORDER by nachname,vorname";
	    //echo $sql_query;
		if(!($erg=pg_query($this->conn, $sql_query)))
		{
			$this->errormsg=pg_errormessage($this->conn);
			return false;
		}
		$num_rows=pg_num_rows($erg);
		$result=array();
		for($i=0;$i<$num_rows;$i++)
		{
   			$row=pg_fetch_object($erg,$i);
			$l=new student($this->conn);
			// Personendaten
			$l->uid=$row->uid;
			$l->titelpre=$row->titelpre;
			$l->titelpost=$row->titelpost;
			$l->vornamen=$row->vornamen;
			$l->vorname=$row->vorname;
			$l->nachname=$row->nachname;
			$l->gebdatum=$row->gebdatum;
			$l->gebort=$row->gebort;
			$l->gebzeit=$row->gebzeit;
			$l->foto=$row->foto;
			$l->anmerkungen=$row->anmerkungen;
			$l->aktiv=$row->aktiv=='t'?true:false;
			$l->alias=$row->alias;
			$l->homepage=$row->homepage;
			$l->updateamum=(isset($row->updateamum)?$row->updateamum:'');
			$l->updatevon=(isset($row->updatevon)?$row->updatevon:'');
			// Studentendaten
			$l->matrikelnr=$row->matrikelnr;
			$l->gruppe=$row->gruppe;
			$l->verband=$row->verband;
			$l->semester=$row->semester;
			$l->studiengang_kz=$row->studiengang_kz;
			//$l->stg_bezeichnung=$row->bezeichnung;
			// student in Array speichern
			$result[]=$l;
		}
		return $result;
	}

}
?>