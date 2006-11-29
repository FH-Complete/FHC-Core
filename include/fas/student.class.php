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

	// ***********************************************************************
	// * Konstruktor - Uebergibt die Connection und laedt optional eine Person
	// * @param $conn      Datenbank-Connection
	// *        $person_id Person die geladen werden soll (default=null)
	// ***********************************************************************
	function student($conn, $student_id=null, $unicode=false)
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
		if($student_id!=null)
			$this->load($student_id);
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
			$qry = "INSERT INTO tbl_student(uid, matrikelnr, updateamum, updatevon, prestudent_id, 
			                    studiengang_kz, semester, verband, gruppe)
			        VALUES('".addslashes($this->uid)."',".
			 	 	$this->addslashes($this->matrikelnr).",".
			 	 	$this->addslashes($this->updateamum).','.
			 	 	$this->addslashes($this->updatevon).','.
			 	 	$this->addslashes($this->prestudent_id).','.
					$this->studiengang_kz.','.
					$this->semester.','.
					($this->verband!=''?"'".addslashes($this->verband)."'":' ').','.
					($this->gruppe!=''?"'".addslashes($this->gruppe)."'":' ').');';
		}
		else 
		{
			//Bestehenden Datensatz updaten
			$qry = 'UPDATE tbl_student SET'.
			       ' matrikelnr='.$this->addslashes($this->matrikelnr).','.
			       ' updateamum='.$this->addslashes($this->updateamum).','.
			       ' updatevon='.$this->addslashes($this->updatevon).','.
			       ' prestudent_id='.$this->addslashes($this->prestudent_id).','.
			       ' studiengang_kz='.$this->studiengang_kz.','.
			       ' semester='.$this->semester.','.
			       ' verband='.$this->addslashes($this->verband).','.
			       ' gruppe='.$this->addslashes($this->gruppe).
			       " WHERE uid='".addslashes($this->uid)."';";
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
}
?>