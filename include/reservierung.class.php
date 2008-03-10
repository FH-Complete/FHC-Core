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

class reservierung
{
	var $conn;     // resource DB-Handle
	var $errormsg; // string
	var $new;      // boolean
	var $reservierungen = array(); // reservierung Objekt

	//Tabellenspalten
	var $reservierung_id;	// int
	var $ort_kurzbz;		// varchar(8)
	var $studiengang_kz;	// int
	var $uid;				// varchar(16)
	var $stunde;			// smalint
	var $datum;				// date
	var $titel;				// varchar(10)
	var $beschreibung;		// varchar(32)
	var $semester;			// smalint
	var $verband;			// char(1)
	var $gruppe;			// char(1)
	var $gruppe_kurzbz;		// varchar(10)

	// *************************************************************************
	// * Konstruktor - Uebergibt die Connection und laedt optional eine Reservierung
	// * @param $conn        	Datenbank-Connection
	// *        $reservierung_id
	// *        $unicode     	Gibt an ob die Daten mit UNICODE Codierung
	// *                     	oder LATIN9 Codierung verarbeitet werden sollen
	// *************************************************************************
	function reservierung($conn, $reservierung_id=null, $unicode=false)
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

		if($reservierung_id!=null)
			$this->load($reservierung_id);
	}

	// *********************************************************
	// * Laedt eine Reservierung
	// * @param reservierung_id
	// *********************************************************
	function load($reservierung_id)
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
		if(strlen($this->ort_kurzbz)>8)
		{
			$this->errormsg = 'Ort_Kurzbz darf nicht laenger als 8 Zeichen sein';
			return false;
		}
		if(!is_numeric($this->studiengang_kz))
		{
			$this->errormsg = 'Studiengang_kz muss eine gueltige Zahl sein';
			return false;
		}
		if(strlen($this->uid)>16)
		{
			$this->errormsg = 'UID darf nicht laenger als 16 Zeichen sein';
			return false;
		}
		if(!is_numeric($this->stunde))
		{
			$this->errormsg = 'Stunde ist ungueltig';
			return false;
		}
		if(strlen($this->titel)>10)
		{
			$this->errormsg = 'Titel darf nicht laenger als 10 Zeichen sein';
			return false;
		}
		if(strlen($this->beschreibung)>32)
		{
			$this->beschreibung = 'Beschreibung darf nicht laenger als 32 Zeichen sein';
			return false;
		}
		if($this->semester!='' && !is_numeric($this->semester))
		{
			$this->errormsg = 'Semester ist ungueltig';
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
		if(strlen($this->gruppe_kurzbz)>10)
		{
			$this->gruppe_kurzbz = 'Gruppe_kurzbz darf nicht laenger als 10 Zeichen sein';
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
	// * Speichert Reservierung in die Datenbank
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
			$qry = 'INSERT INTO campus.tbl_reservierung (reservierung_id, ort_kurzbz, studiengang_kz, uid, stunde, datum, titel,
			                                      beschreibung, semester, verband, gruppe, gruppe_kurzbz)
			        VALUES('.$this->addslashes($this->reservierung_id).','.
					$this->addslashes($this->ort_kurzbz).','.
					$this->addslashes($this->studiengang_kz).','.
					$this->addslashes($this->uid).','.
					$this->addslashes($this->stunde).','.
					$this->addslashes($this->datum).','.
					$this->addslashes($this->titel).','.
					$this->addslashes($this->beschreibung).','.
					$this->addslashes($this->semester).','.
					$this->addslashes($this->verband).','.
					$this->addslashes($this->gruppe).','.
					$this->addslashes($this->gruppe_kurzbz).');';
		}
		else
		{
			$qry = 'UPDATE campus.tbl_reservierung SET'.
			       ' ort_kurzbz='.$this->addslashes($this->ort_kurzbz).','.
			       ' studiengang_kz='.$this->addslashes($this->studiengang_kz).','.
			       ' uid='.$this->addslashes($this->uid).','.
			       ' stunde='.$this->addslashes($this->stunde).','.
			       ' datum='.$this->addslashes($this->datum).','.
			       ' titel='.$this->addslashes($this->titel).','.
			       ' beschreibung='.$this->addslashes($this->beschreibung).','.
			       ' semester='.$this->addslashes($this->semester).','.
			       ' verband='.$this->addslashes($this->verband).','.
			       ' gruppe='.$this->addslashes($this->gruppe).','.
			       ' gruppe_kurzbz='.$this->addslashes($this->gruppe_kurzbz).
			       " WHERE reservierung_id='".addslashes($this->reservierung_id)."'";
		}

		if(pg_query($this->conn,$qry))
		{
			//Log schreiben
			$this->new = false;
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern der Reservierung:'.$qry;
			return false;
		}
	}
	
	// ************************************************************
	// * Loescht eine Reservierung
	// * @param reservierung_id ID der zu leoschenden Reservierung
	// * @return true wenn ok, false im Fehlerfall
	// ************************************************************
	function delete($reservierung_id)
	{
		if(!is_numeric($reservierung_id))
		{
			$this->errormsg = 'Reservierung_id muss eine gueltige Zahl sein';
			return false;
		}
		
		$qry = "DELETE FROM campus.tbl_reservierung WHERE reservierung_id='$reservierung_id'";
		
		if(pg_query($this->conn, $qry))
			return true;
		else
		{
			$this->errormsg = 'Fehler beim Loeschen der Reservierung';
			return false;
		}
	}
}
?>