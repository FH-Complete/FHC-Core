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

class lehreinheitgruppe
{
	var $conn;     // resource DB-Handle
	var $errormsg; // string
	var $new;      // boolean
	var $lehreinheitgruppe = array(); // lehreinheitgruppe Objekt
	
	//Tabellenspalten
	var $lehreinheitgruppe_id;	//integer
	var $lehreinheit_id;		// integer
	var $studiengang_kz;		// integer
	var $semester;				// smalint
	var $verband;				// char(1)
	var $gruppe;				// char(1)
	var $gruppe_kurzbz;			// varchar(16)
	var $ext_id;				// bigint
	
	// *************************************************************************
	// * Konstruktor - Uebergibt die Connection und laedt optional eine LE
	// * @param $conn        	Datenbank-Connection
	// *        $gruppelehreinheit_id
	// *        $unicode     	Gibt an ob die Daten mit UNICODE Codierung 
	// *                     	oder LATIN9 Codierung verarbeitet werden sollen
	// *************************************************************************
	function lehreinheitgruppe($conn, $lehreinheitgruppe_id=null, $unicode=false)
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
		
		if($lehreinheitgruppe_id!=null)
			$this->load($lehreinheitgruppe_id);
	}
	
	// *********************************************************
	// * Laedt die LEGruppe
	// * @param lehreinheit_id
	// *********************************************************
	function load($lehreinheitgruppe_id)
	{
		return false;
	}
	
	// *******************************************
	// * Prueft die Variablen vor dem Speichern 
	// * auf Gueltigkeit.
	// * @return true wenn ok, false im Fehlerfall
	// *******************************************
	function validate()
	{
		if(!is_numeric($this->lehreinheit_id))
		{
			$this->errormsg = 'Lehreinheit_id muss eine gueltige Zahl sein';
			return false;
		}
		if(!is_numeric($this->studiengang_kz))
		{
			$this->errormsg = 'Studiengang_kz muss eine gueltige Zahl sein';
			return false;
		}
		if($this->semester!='' && !is_numeric($this->semester))
		{
			$this->errormsg = 'Semester muss eine gueltige Zahl sein';
			return false;
		}
		if(strlen($this->verband)>1)
		{
			$this->verband = 'Verband darf nicht laenger als 1 Zeichen sein';
			return false;
		}
		if(strlen($this->gruppe)>1)
		{
			$this->gruppe = 'Gruppe darf nicht laenger als 1 Zeichen sein';
			return false;
		}
		if(strlen($this->gruppe_kurzbz)>16)
		{
			$this->errormsg = 'Gruppe_kurzbz darf nicht laenger als 16 Zeichen sein';
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
	// * Speichert GruppeLE in die Datenbank
	// * Wenn $new auf true gesetzt ist wird ein neuer Datensatz
	// * angelegt, ansonsten der Datensatz upgedated
	// * @return true wenn erfolgreich, false im Fehlerfall
	// ************************************************************
	function save($new=null)
	{
		if(is_null($new))
			$new = $this->new;
					
		//Variablen auf Gueltigkeit pruefen
		if(!$this->validate())
			return false;

		if($new)
		{
			//ToDo ID entfernen
			$qry = 'INSERT INTO lehre.tbl_lehreinheitgruppe (lehreinheit_id, studiengang_kz, semester, verband, gruppe, gruppe_kurzbz, ext_id)
			        VALUES('.$this->addslashes($this->lehreinheit_id).','.
					$this->addslashes($this->studiengang_kz).','.
					$this->addslashes($this->semester).','.
					$this->addslashes($this->verband).','.
					$this->addslashes($this->gruppe).','.
					$this->addslashes($this->gruppe_kurzbz).','.
					$this->addslashes($this->ext_id).');';
		}
		else
		{
			$qry = 'UPDATE lehre.tbl_lehreinheitgruppe SET'.
			       ' lehreinheit_id='.$this->addslashes($this->lehreinheit_id).','.
			       ' studiengang_kz='.$this->addslashes($this->studiengang_kz).','.
			       ' semester='.$this->addslashes($this->semester).','.
			       ' verband='.$this->addslashes($this->verband).','.
			       ' gruppe='.$this->addslashes($this->gruppe).','.
			       ' gruppe_kurzbz='.$this->addslashes($this->gruppe_kurzbz).','.
			       ' ext_id='.$this->addslashes($this->ext_id).','.
			       " WHERE lehreinheitgruppe_id=".$this->addslashes($this->lehreinheitgruppe_id).";";
		}

		if(pg_query($this->conn,$qry))
		{
			//Log schreiben
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern der GruppeLE:'.$qry;
			return false;
		}
	}
	
	function exists($lehreinheit_id, $studiengang_kz, $semester, $verband, $gruppe, $gruppe_kurzbz)
	{
		$qry = "SELECT * FROM lehre.tbl_lehreinheitgruppe WHERE lehreinheit_id='$lehreinheit_id'";
		
		if($gruppe_kurzbz!='')
		{
			$qry .= " AND gruppe_kurzbz='".addslashes($gruppe_kurzbz)."'";
		}
		else 
		{
			$qry .= " AND semester='$semester'";
			if($verband!='')
				$qry .= " AND verband='$verband'";
			if($gruppe!='')
				$qry .= " AND gruppe='$gruppe'";
		}
		
		if($result = pg_query($this->conn, $qry))
		{
			if(pg_num_rows($result)>0)
				return true;
			else 
				return false;
		}
		else 
		{
			$this->errormsg = 'Fehler beim lesen der Lehreinheitgruppen';
			return false;
		}
	}
}
?>