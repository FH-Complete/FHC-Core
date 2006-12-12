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

class lehrverband
{
	var $conn;     // resource DB-Handle
	var $errormsg; // string
	var $new;      // boolean
	var $result = array(); // lehrverband Objekt
	
	//Tabellenspalten
	var $studiengang_kz;	// integer
	var $semester;			// integer
	var $verband;			// integer
	var $gruppe;			// integer	
	
	// *************************************************************************
	// * Konstruktor - Uebergibt die Connection und laedt optional einen Lehrverband
	// * @param $conn        	Datenbank-Connection
	// *        
	// *        $unicode     	Gibt an ob die Daten mit UNICODE Codierung 
	// *                     	oder LATIN9 Codierung verarbeitet werden sollen
	// *************************************************************************
	function lehrverband($conn, $unicode=false)
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
	}
		
	function exists($studiengang_kz, $semester, $verband, $gruppe)
	{
		$qry = "SELECT count(*) as anzahl FROM tbl_lehrverband WHERE 
		            studiengang_kz='".addslashes($studiengang_kz)."' AND
		            semester='".addslashes($semester)."' AND
		            verband='".addslashes($verband)."' AND
		            gruppe='".addslashes($gruppe)."'";
		
		if($row=pg_fetch_object(pg_query($this->conn, $qry)))
		{
			if($row->anzahl>0)
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
			$this->errormsg = 'Fehler bei Abfrage: '.$qry;
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
		if(!is_numeric($this->semester))
		{
			$this->errormsg = 'Semester muss eine gueltige Zahl sein';
			return false;
		}
		if(!is_numeric($this->studiengang_kz))
		{
			$this->errormsg = 'Studiengang muss eine gueltige Zahl sein';
			return false;
		}
		if($this->verband=='')
		{
			$this->verband=' ';
		}
		if($this->gruppe=='')
		{
			$this->gruppe=' ';
		}
		return true;
	}
	
	function getlehrverband($studiengang_kz=null, $semester=null, $verband=null)
	{
		$qry = 'SELECT * FROM tbl_lehrverband WHERE 1=1';
		if(!is_null($studiengang_kz))
			$qry .=' AND studiengang_kz='.$this->addslashes($studiengang_kz);
		if(!is_null($semester))
			$qry .=' AND semester='.$this->addslashes($semester);
		if(!is_null($verband))
			$qry .=' AND verband='.$this->addslashes($verband);
			
		$qry .= ' ORDER BY studiengang_kz, semester, verband, gruppe';
		if($result = pg_query($this->conn, $qry))
		{
			while($row=pg_fetch_object($result))
			{
				$lv_obj = new lehrverband($this->conn);
				
				$lv_obj->studiengang_kz = $row->studiengang_kz;
				$lv_obj->semester = $row->semester;
				$lv_obj->verband = $row->verband;
				$lv_obj->gruppe = $row->gruppe;
				
				$this->result[] = $lv_obj;
			}
			return true;
		}
		else 
		{
			$this->errormsg = 'Fehler beim lesen der Lehrverbaende '.$qry;
			return false;
		}
	}

	// ************************************************
	// * wenn $var '' ist wird NULL zurueckgegeben
	// * wenn $var !='' ist werden Datenbankkritische 
	// * Zeichen mit Backslash versehen und das Ergbnis
	// * unter Hochkomma gesetzt.
	// ************************************************
	function addslashes($var)
	{
		return ($var!=''?"'".addslashes($var)."'":'null');
	}

	// ************************************************************
	// * Speichert Lehrverband in die Datenbank
	// * Wenn $new auf true gesetzt ist wird ein neuer Datensatz
	// * angelegt, ansonsten der Datensatz upgedated
	// * @return true wenn erfolgreich, false im Fehlerfall
	// ************************************************************
	function save()
	{
					
		//Variablen auf Gueltigkeit pruefen
		if(!$this->validate())
			return false;
		
		$qry = 'INSERT INTO tbl_lehrverband (studiengang_kz, semester, verband, gruppe)
		        VALUES('.$this->addslashes($this->studiengang_kz).','.
				$this->addslashes($this->semester).','.
				$this->addslashes($this->verband).','.
				$this->addslashes($this->gruppe).');';
		if(pg_query($this->conn,$qry))
		{
			//Log schreiben
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern des Lehrverbands:'.$qry;
			return false;
		}
	}
}
?>