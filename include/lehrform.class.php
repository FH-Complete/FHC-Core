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

class lehrform
{
	var $conn;     // resource DB-Handle
	var $errormsg; // string
	var $new;      // boolean
	var $lehrform = array(); // lehrform Objekt
	
	//Tabellenspalten
	var $lehrform_kurbz;	// varchar(8)
	var $bezeichnung;		// varchar (256)
	var $verplanen; 		// boolean
	
	// *************************************************************************
	// * Konstruktor - Uebergibt die Connection und laedt optional eine Lehrform
	// * @param $conn        	Datenbank-Connection
	// *        $lehrform_kurbz Lehrform die geladen werden soll (default=null)
	// *        $unicode     	Gibt an ob die Daten mit UNICODE Codierung 
	// *                     	oder LATIN9 Codierung verarbeitet werden sollen
	// *************************************************************************
	function lehrform($conn, $lehrform_kurzbz=null, $unicode=false)
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
		
		if($lehrform_kurzbz != null)
			$this->load($lehrform_kurzbz);
	}
	
	// *********************************************************
	// * Laedt Lehrform mit der uebergebenen ID
	// * @param $lehrform_kurzbz Lehrform die geladen werden soll
	// *********************************************************
	function load($lehrform_kurzbz)
	{		
		$qry = "SELECT * FROM lehre.tbl_lehrform WHERE lehrform_kurzbz='".addslashes($lehrfach_nr)."'";
		if(!$result=pg_query($this->conn,$qry))
		{
			$this->errormsg = 'Fehler beim lesen der Lehrform';
			return false;
		}
		
		if($row = pg_fetch_object($result))
		{
			$this->lehrform_kurbz = $row->lehrform_kurzbz;
			$this->bezeichnung = $row->bezeichung;
			$this->verplanen = ($row->verplanen?true:false);
		}
		else
		{
			$this->errormsg = 'Es ist keine Lehrform mit der Kurzbz '.$lehrform_kurzbz.' vorhanden';
			return false;
		}
		
		return true;
		
	}
	
	// *******************************************
	// * Prueft die Variablen vor dem Speichern 
	// * auf Gueltigkeit.
	// * @return true wenn ok, false im Fehlerfall
	// *******************************************
	function validate()
	{
		if(strlen($this->lehrform_kurbz)>8)
		{
			$this->errormsg = 'Lehrform Kurzbezeichnung darf nicht laenger als 8 Zeichen sein.';
			return false;
		}
		if(strlen($this->bezeichnung)>256)
		{
			$this->errormsg = 'Bezeichnung darf nicht laenger als 256 Zeichen sein';
			return false;
		}
		if(!is_bool($this->verplanen))
		{
			$this->errormsg = 'Verplanen muss ein boolscher Wert sein';
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
	// * Speichert die Lehrform in die Datenbank
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
			$qry = "INSERT INTO lehre.tbl_lehrform (lehrform_kurzbz, bezeichnung, verplanen)
			        VALUES('".addslashes($this->lehrform_kurzbz)."',".
					$this->addslashes($this->bezeichnung).','.
					($this->verplanen?'true':'false').');';
		}
		else
		{
			$qry = 'UPDATE lehre.tbl_lehrform SET'.
			       ' bezeichnung='.$this->addslashes($this->bezeichnung).','.
			       ' verplanen='.($this->verplanen?'true':'false').
			       " WHERE lehrform_kurzbz='$this->lehrform_kurzbz'";
		}

		if(pg_query($this->conn,$qry))
		{
			//Log schreiben
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern der Lehrform:'.$qry;
			return false;
		}
	}
}
?>