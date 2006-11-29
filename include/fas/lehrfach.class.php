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

class lehrfach
{
	var $conn;     // resource DB-Handle
	var $errormsg; // string
	var $new;      // boolean
	var $lehrfaecher = array(); // lehrfach Objekt
	
	//Tabellenspalten
	var $lehrfach_nr;		// integer
	var $studiengang_kz;	// integer
	var $fachbereich_id;	// integer
	var $kurzbz;			// varchar(12)
	var $bezeichnung;		// varchar(255)
	var $farbe;				// char(6)
	var $aktiv;				// boolean
	var $semester;			// smallint
	var $sprache;			// varchar(16)
	
	// ***********************************************************************
	// * Konstruktor - Uebergibt die Connection und laedt optional ein LF
	// * @param $conn        Datenbank-Connection
	// *        $lehrfach_nr Lehrfach das geladen werden soll (default=null)
	// *        $unicode     Gibt an ob die Daten mit UNICODE Codierung 
	// *                     oder LATIN9 Codierung verarbeitet werden sollen
	// ***********************************************************************
	function lehrfach($conn, $lehrfach_nr=null, $unicode=false)
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
		
		if($lehrfach_nr != null)
			$this->load($lehrfach_nr);
	}
	
	// *********************************************************
	// * Laedt Lehrfach mit der uebergebenen ID
	// * @param $lehrfach_nr Nr des LF das geladen werden soll
	// *********************************************************
	function load($lehrfach_nr)
	{
		//lehrfach_nr auf Gueltigkeit pruefen
		if(is_numeric($lehrfach_nr) && $lehrfach_nr!='')
		{
			$qry = "SELECT * FROM tbl_lehrfach WHERE lehrfach_nr='$lehrfach_nr'";
			
			if(!$result=pg_query($this->conn,$qry))
			{
				$this->errormsg = 'Fehler beim lesen des Lehrfaches';
				return false;
			}
			
			if($row = pg_fetch_object($result))
			{
				$this->lehrfach_nr = $row->lehrfach_nr;
				$this->studiengang_kz = $row->studiengang_kz;
				$this->fachbereich_id = $row->fachbereich_id;
				$this->kurzbz = $row->kurzbz;
				$this->bezeichnung = $row->bezeichnung;
				$this->farbe = $row->farbe;
				$this->aktiv = ($row->aktiv=='t'?true:false);
				$this->semester = $row->semester;
				$this->sprache = $row->sprache;
			}
			else
			{
				$this->errormsg = 'Es ist kein Lehrfach mit der Nr '.$lehrfach_nr.' vorhanden';
				return false;
			}
			
			return true;
		}
		else
		{
			$this->errormsg = 'Die lehrfach_nr muss eine gueltige Zahl sein';
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
		if(!is_numeric($this->studiengang_kz))
		{
			$this->errormsg = 'Studiengang_kz muss eine gueltige Zahl sein';
			return false;
		}
		if(!is_numeric($this->fachbereich_id))
		{
			$this->errormsg = 'Fachbereich_id muss eine gueltige Zahl sein';
			return false;
		}
		if(strlen($this->kurzbz)>12)
		{
			$this->errormsg = 'Kurzbezeichnung darf nicht laenger als 12 Zeichen sein';
			return false;
		}
		if(strlen($this->bezeichnung)>255)
		{
			$this->errormsg = 'Bezeichnung darf nicht laenger als 255 Zeichen sein';
			return false;
		}
		if(strlen($this->farbe)>6)
		{
			$this->errormsg = 'Farbe darf nicht laenger als 6 Zeichen sein';
			return false;
		}
		if(!is_bool($this->aktiv))
		{
			$this->errormsg = 'Aktiv muss ein boolscher Wert sein';
			return false;
		}
		if($this->semester!='' && !is_numeric($this->semester))
		{
			$this->errormsg = 'Semester muss eine Zahl sein';
			return false;
		}
		if(strlen($this->sprache)>16)
		{
			$this->errormsg = 'Sprache darf nicht laenger als 16 Zeichen sein';
			return false;
		}

		return true;
	}

	// ************************************************
	// * wenn $var '' ist wird "null" zurueckgegeben
	// * wenn $var !='' ist werden Datenbankkritische 
	// * zeichen mit backslash versehen und das ergbnis
	// * unter hochkomma gesetzt.
	// ************************************************
	function addslashes($var)
	{
		return ($var!=''?"'".addslashes($var)."'":'null');
	}

	// ************************************************************
	// * Speichert das Lehrfach in die Datenbank
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
			$qry = 'INSERT INTO tbl_lehrfach (lehrfach_nr, studiengang_kz, fachbereich_id, kurzbz, 
			                                  bezeichnung, farbe, aktiv, semester, sprache)
			        VALUES('.$this->addslashes($this->lehrfach_nr).','.
					$this->addslashes($this->studiengang_kz).','.
					$this->addslashes($this->fachbereich_id).','.
					$this->addslashes($this->kurzbz).','.
					$this->addslashes($this->bezeichnung).','.
					$this->addslashes($this->farbe).','.
					($this->aktiv?'true':'false').','.
					$this->addslashes($this->semester).','.
					$this->addslashes($this->sprache).');';
		}
		else
		{
			//lehrfach_nr auf Gueltigkeit pruefen
			if(!is_numeric($this->lehrfach_nr))
			{
				$this->errormsg = 'Lehrfach_nr muss eine gueltige Zahl sein';
				return false;
			}

			$qry = 'UPDATE tbl_lehrfach SET'.
			       ' studiengang_kz='.$this->addslashes($this->studiengang_kz).','.
			       ' fachbereich_id='.$this->addslashes($this->fachbereich_id).','.
			       ' kurzbz='.$this->addslashes($this->kurzbz).','.
			       ' bezeichnung='.$this->addslashes($this->bezeichnung).','.
			       ' farbe='.$this->addslashes($this->farbe).','.
			       ' aktiv='.($this->aktiv?'true':'false').','.
			       ' semester='.$this->semester.','.
			       ' sprache='.$this->addslashes($this->sprache).
			       " WHERE lehrfach_nr='$this->lehrfach_nr'";
		}

		if(pg_query($this->conn,$qry))
		{
			//Log schreiben
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern des Lehrfaches:'.$qry;
			return false;
		}
	}
}
?>