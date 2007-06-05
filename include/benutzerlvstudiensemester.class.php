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

class benutzerlvstudiensemester
{
	var $conn;     // resource DB-Handle
	var $errormsg; // string
	var $new;      // boolean
	var $benutzerlvstudiensemester = array(); // benutzerlvstudiensemester Objekt

	//Tabellenspalten
	var $uid;						// varchar(16)
	var $studiensemester_kurzbz;	// varchar(16)
	var $lehrveranstaltung_id;		// integer

	// *************************************************************************
	// * Konstruktor - Uebergibt die Connection und laedt optional eine Zuteilung
	// * @param $conn        	Datenbank-Connection
	// *        $uid
	// * 		$studiensemester_kurzbz
	// *		$lehrveranstaltung_nr
	// *        $unicode     	Gibt an ob die Daten mit UNICODE Codierung
	// *                     	oder LATIN9 Codierung verarbeitet werden sollen
	// *************************************************************************
	function benutzerlvstudiensemester($conn, $uid=null, $studiensemester_kurzbz=null, $lehrveranstaltung_id=null, $unicode=false)
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
		else
			$this->new = true;

		if($uid!=null && $studiensemester_kurzbz!=null && $lehrveranstaltung_id!=null)
			$this->load($uid, $studiensemester_kurzbz, $lehrveranstaltung_id);
	}

	// *********************************************************
	// * Laedt eine Zuteilung
	// * @param $uid, $studiensemester_kurzbz, $lehrveranstaltung_nr
	// *********************************************************
	function load($uid, $studiensemester_kurzbz, $lehrveranstaltung_id)
	{
		$this->errormsg = 'Not implemented';
		return false;
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
		if(strlen($this->studiensemester_kurzbz)>16)
		{
			$this->errormsg = 'Studiensemester_kurzbz darf nicht laenger als 16 Zeichen sein';
			return false;
		}
		if(!is_numeric($this->lehrveranstaltung_id))
		{
			$this->errormsg = 'Lehrveranstaltungsnummer muss eine gueltige Zahl sein';
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
	// * Speichert Zuteilung in die Datenbank
	// * Wenn $new auf true gesetzt ist wird ein neuer Datensatz
	// * angelegt, ansonsten der Datensatz upgedated
	// * @return true wenn erfolgreich, false im Fehlerfall
	// ************************************************************
	function save($new=null)
	{
		if(!is_null($new))
			$this->new = $new;

		//Variablen auf Gueltigkeit pruefen
		if(!$this->validate())
			return false;

		if($this->new)
		{
			$qry = 'INSERT INTO campus.tbl_benutzerlvstudiensemester (uid, studiensemester_kurzbz, lehrveranstaltung_id)
			        VALUES('.$this->addslashes($this->uid).','.
					$this->addslashes($this->studiensemester_kurzbz).','.
					$this->addslashes($this->lehrveranstaltung_id).');';
		}
		else
		{
			// ToDo
			$qry = 'Select 1;';
		}

		if(pg_query($this->conn,$qry))
		{
			//Log schreiben
			$this->new = false;
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern der BenutzerLVStudiensemester:'.$qry;
			return false;
		}
	}
}
?>