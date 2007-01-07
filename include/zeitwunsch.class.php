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

class zeitwunsch
{
	var $conn;     // resource DB-Handle
	var $errormsg; // string
	var $new;      // boolean
	var $zeitwuensche = array(); // zeitwunsch Objekt

	//Tabellenspalten
	var $stunde;			// smalint
	var $mitarbeiter_uid;	// varchar(16)
	var $tag;				// smalint
	var $gewicht;			// smalint

	// *************************************************************************
	// * Konstruktor - Uebergibt die Connection und laedt optional eine Lehrform
	// * @param $conn        	Datenbank-Connection
	// *        $uid			Uid des Mitarbeiters
	// *        $tag            Tag des Zeitwunsches
	// *        $stunde         Stunde des Zeitwunsches
	// *        $unicode     	Gibt an ob die Daten mit UNICODE Codierung
	// *                     	oder LATIN9 Codierung verarbeitet werden sollen
	// *************************************************************************
	function zeitwunsch($conn, $mitarbeiter_uid=null, $tag=null, $stunde=null, $unicode=false)
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

		if($mitarbeiter_uid != null && $tag!=null && $stunde!=null)
			$this->load($mitarbeiter_uid, $tag, $stunde);
	}

	// *********************************************************
	// * Laedt einen Zeitwunsch
	// * @param
	// *********************************************************
	function load($mitarbeiter_uid, $tag, $stunde)
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
		if(strlen($this->mitarbeiter_uid)>16)
		{
			$this->errormsg = 'UID darf nicht laenger als 16 Zeichen sein.';
			return false;
		}
		if($this->mitarbeiter_uid == '')
		{
			$this->errormsg = 'UID muss angegeben werden';
			return false;
		}
		if(!is_numeric($this->stunde))
		{
			$this->errormsg = 'Stunde muss eine gueltige Zahl sein';
			return false;
		}
		if(!is_numeric($this->gewicht))
		{
			$this->errormsg = 'Gewicht muss eine gueltige Zahl sein';
			return false;
		}
		if(!is_numeric($this->tag))
		{
			$this->errormsg = 'Tag muss eine gueltige Zahl sein';
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
	// * Speichert einen Zeitwunsch in die Datenbank
	// * Wenn $new auf true gesetzt ist wird ein neuer Datensatz
	// * angelegt, ansonsten der Datensatz mit $lehrfach_nr upgedated
	// * @return true wenn erfolgreich, false im Fehlerfall
	// ************************************************************
	function save()
	{
		//Variablen auf Gueltigkeit pruefen
		if(!$this->validate())
			return false;

		if($this->new)
		{
			$qry = "INSERT INTO campus.tbl_zeitwunsch (mitarbeiter_uid, tag, stunde, gewicht)
			        VALUES('".addslashes($this->mitarbeiter_uid)."',".
					$this->tag.','.$this->stunde.','.$this->gewicht.');';
		}
		else
		{
			$qry = 'UPDATE campus.tbl_zeitwunsch SET'.
			       ' gewicht='.$this->gewicht.
			       " WHERE mitarbeiter_uid='".addslashes($this->mitarbeiter_uid)."' AND
			         tag=".$this->tag.' AND stunde='.$this->stunde;
		}

		if(pg_query($this->conn,$qry))
		{
			//Log schreiben
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern des Zeitwunsches:'.$qry;
			return false;
		}
	}

	/**
	 * Zeitwunsch einer Person laden
	 * @return boolean Ergebnis steht in Array $zeitwunsch wenn true
	 */
	function loadPerson($uid)
	{
		// Zeitwuensche abfragen
		if(!$result=@pg_query($this->conn, "SELECT * FROM lehre.tbl_zeitwunsch WHERE uid='$uid'"))
		{
			$this->errormsg=pg_last_error($this->conn);
			return false;
		}
		else
		{
			while ($row=@pg_fetch_object($result))
				$this->zeitwunsch[$row->tag][$row->stunde]=$row->gewicht;
			return true;
		}
	}


	/**
	 * Zeitwunsch der Personen in Lehrveranstaltungen laden
	 * @return array mit Fachbereichen oder false=fehler
	 */
	function loadLVA($lva_id)
	{
		// SUB-Select fuer LVAs
		$sql_query_lva='SELECT DISTINCT lektor FROM tbl_lehrveranstaltung WHERE ';
		for ($i=0;$i<count($lva_id);$i++)
			$sql_query_lvaid.=' OR lehrveranstaltung_id='.$lva_id[$i];
		$sql_query_lvaid=substr($sql_query_lvaid,3);
		$sql_query_lva.=$sql_query_lvaid;

		// Schlechteste Zeitwuensche holen
		$sql_query='SELECT tag,stunde,min(gewicht) AS gewicht
				FROM tbl_zeitwunsch WHERE uid IN ('.$sql_query_lva.') GROUP BY tag,stunde';

		// Zeitwuensche abfragen
		if(!$result=@pg_query($this->conn, $sql_query))
		{
			$this->errormsg=pg_last_error($this->conn);
			return false;
		}
		else
		{
			while ($row=@pg_fetch_object($result))
				$this->zeitwunsch[$row->tag][$row->stunde]=$row->gewicht;
			return true;
		}
	}

}
?>