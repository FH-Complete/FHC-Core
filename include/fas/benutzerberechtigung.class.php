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

class benutzerberechtigung
{
	var $conn;     // resource DB-Handle
	var $errormsg; // string
	var $new;      // boolean
	var $benutzerberechtigungen = array(); // benutzerberechtigung Objekt
	
	//Tabellenspalten
	var $benutzerberechtigung_id;	// int
	var $art;						// varchar(16)
	var $fachbereich_id;			// int
	var $studiengang_kz;			// int
	var $berechtigung_kurzbz;		// varchar(16)
	var $uid;						// varchar(16)
	var $studiensemester_kurzbz;	// varchar(16)
	var $start;						// date
	var $ende;						// date
	
	// *************************************************************************
	// * Konstruktor - Uebergibt die Connection und laedt optional eine Lehrform
	// * @param $conn        	Datenbank-Connection
	// *        $benutzerberechtigung_id    
	// *        $unicode     	Gibt an ob die Daten mit UNICODE Codierung 
	// *                     	oder LATIN9 Codierung verarbeitet werden sollen
	// *************************************************************************
	function benutzerberechtigung($conn, $benutzerberechtigung_id=null, $unicode=false)
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
		
		if($benutzerberechtigung_id!=null)
			$this->load($benutzerberechtigung_id);
	}
	
	// *********************************************************
	// * Laedt eine Benutzerberechtigung
	// * @param benutzerberechtigung_id
	// *********************************************************
	function load($benutzerberechtigung_id)
	{
		return true;
	}
	
	// *******************************************
	// * Prueft die Variablen vor dem Speichern 
	// * auf Gueltigkeit.
	// * @return true wenn ok, false im Fehlerfall
	// *******************************************
	function validate()
	{
		if(strlen($this->art)>16)
		{
			$this->errormsg = 'Art darf nicht laenger als 16 Zeichen sein';
			return false;
		}
		
		if($this->fachbereich_id!='' && !is_numeric($this->fachbereich_id))
		{
			$this->errormsg = 'Fachbereich_id muss eine gueltige Zahl sein';
			return false;
		}
		if($this->studiengang_kz!='' && !is_numeric($this->studiengang_kz))
		{
			$this->errormsg = 'Studiengangskennzahl muss eine gueltige Zahl sein';
			return false;
		}
		if(strlen($this->berechtigung_kurzbz)>16)
		{
			$this->errormsg = 'Berechtigung_kurzbz darf nicht laenger als 16 Zeichen sein';
			return false;
		}
		if($this->berechtigung_kurzbz=='')
		{
			$this->errormsg = 'Berechtigung_kurzbz muss angegeben werden';
			return false;
		}
		if(strlen($this->uid)>16)
		{
			$this->errormsg = 'UID darf nicht laenger als 16 Zeichen sein';
			return false;
		}
		if($this->uid=='')
		{
			$this->errormsg = 'UID muss angegeben werden';
			return false;
		}
	
		return true;
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
	// * Speichert Benutzerberechtigung in die Datenbank
	// * Wenn $new auf true gesetzt ist wird ein neuer Datensatz
	// * angelegt, ansonsten der Datensatz upgedated
	// * @return true wenn erfolgreich, false im Fehlerfall
	// ************************************************************
	function save()
	{
		//Variablen auf Gueltigkeit pruefen
		if(!$this->validate())
			return false;

		if($this->new)
		{			
			$qry = 'INSERT INTO tbl_benutzerberechtigung (art, fachbereich_id, studiengang_kz, berechtigung_kurzbz, 
			                                              uid, studiensemester_kurzbz, start, ende)
			        VALUES('.$this->addslashes($this->art).','.
					$this->addslashes($this->fachbereich_id).','.
					$this->addslashes($this->studiengang_kz).','.
					$this->addslashes($this->berechtigung_kurzbz).','.
					$this->addslashes($this->uid).','.
					$this->addslashes($this->studiensemester_kurzbz).','.
					$this->addslashes($this->start).','.
					$this->addslashes($this->ende).');';
		}
		else
		{
			$qry = 'UPDATE tbl_benutzerberechtigung SET'.
			       ' art='.$this->addslashes($this->art).','.
			       ' fachbereich_id='.$this->addslashes($this->fachbereich_id).','.
			       ' studiengang_kz='.$this->addslashes($this->studiengang_kz).','.
			       ' berechtigung_kurzbz='.$this->addslashes($this->berechtigung_kurzbz).','.
			       ' uid='.$this->addslashes($this->uid).','.
			       ' studiensemester_kurzbz='.$this->addslashes($this->studiensemester_kurzbz).','.
			       ' start='.$this->addslashes($this->start).','.
			       ' ende='.$this->addslashes($this->ende).
			       " WHERE benutzerberechtigung_id='".addslashes($this->benutzerberechtigung_id)."'";
		}

		if(pg_query($this->conn,$qry))
		{
			//Log schreiben
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern des Feedbacks:'.$qry;
			return false;
		}
	}
}
?>