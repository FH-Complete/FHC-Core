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

class gruppe
{
	var $conn;     // resource DB-Handle
	var $errormsg; // string
	var $new;      // boolean
	var $gruppen = array(); // gruppen Objekt
	
	//Tabellenspalten
	var $gruppe_kurzbz;			// varchar(10)
	var $studiengang_kz;		// integer
	var $bezeichnung;			// varchar(64)
	var $semester;				// smallint
	var $typ;					// smallint
	var $mailgrp_kurzbz;		// varchar(16)
	var $mailgrp_beschreibung;	// varchar(64)
	var $sichtbar;				// boolean
	var $aktiv;					// boolean
	var $updateamum;			// timestamp
	var $updatevon;				// varchar(16)
	var $insertamum;			// timestamp
	var $insertvon;				// varchar(16)
	
	// *************************************************************************
	// * Konstruktor - Uebergibt die Connection und laedt optional eine Gruppe
	// * @param $conn        	Datenbank-Connection
	// *        $gruppe_kurzbz
	// *        $unicode     	Gibt an ob die Daten mit UNICODE Codierung 
	// *                     	oder LATIN9 Codierung verarbeitet werden sollen
	// *************************************************************************
	function gruppe($conn, $gruppe_kurzbz=null, $unicode=false)
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
		
		if($gruppe_kurzbz!=null)
			$this->load($gruppe_kurzbz);
	}
	
	// *********************************************************
	// * Laedt die Gruppe
	// * @param gruppe_kurzbz
	// *********************************************************
	function load($gruppe_kurzbz)
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
		if(strlen($this->gruppe_kurzbz)>10)
		{
			$this->errormsg = 'Gruppe_kurzbz darf nicht laenger als 10 Zeichen sein';
			return false;
		}
		if($this->gruppe_kurzbz=='')
		{
			$this->errormsg = 'Gruppe muss angegeben werden';
			return false;
		}
		if(!is_numeric($this->studiengang_kz))
		{
			$this->errormsg = 'Studiengang_kz muss eine gueltige Zahl sein';
			return false;
		}
		if(strlen($this->bezeichnung)>64)
		{
			$this->errormsg = 'Bezeichnung darf nicht laenger als 64 Zeichen sein';
			return false;
		}
		if($this->semester!='' && !is_numeric($this->semester))
		{
			$this->errormsg = 'Semester muss eine gueltige Zahl sein';
			return false;
		}
		if($this->typ!='' && !is_numeric($this->typ))
		{
			$this->errormsg = 'Typ muss eine gueltige Zahl sein';
			return false;
		}
		if(strlen($this->mailgrp_kurzbz)>16)
		{
			$this->errormsg = 'Mailgrp_kurzbz darf nicht laenger als 16 Zeichen sein';
			return false;
		}
		if(strlen($this->mailgrp_beschreibung)>64)
		{
			$this->errormsg = 'Mailgrp_beschreibung darf nicht laenger als 64 Zeichen sein';
			return false;
		}
		if(!is_bool($this->sichtbar))
		{
			$this->errormsg = 'Sichtbar muss ein boolscher Wert sein';
			return false;
		}
		if(!is_bool($this->aktiv))
		{
			$this->errormsg = 'Aktiv muss ein boolscher Wert sein';
			return false;
		}
		if(strlen($this->updatevon)>16)
		{
			$this->errormsg = 'Updatevon darf nicht laenger als 16 Zeichen sein';
			return false;
		}
		if(strlen($this->insertvon)>16)
		{
			$this->errormsg = 'Insertvon darf nicht laenger als 16 Zeichen sein';
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
	// * Speichert Gruppe in die Datenbank
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
			$qry = 'INSERT INTO tbl_gruppe (gruppe_kurzbz, studiengang_kz, bezeichnung, semester, typ, 
			                                mailgrp_kurzbz, mailgrp_beschreibung, sichtbar, aktiv, 
			                                updateamum, updatevon, insertamum, insertvon)
			        VALUES('.$this->addslashes($this->gruppe_kurzbz).','.
					$this->addslashes($this->studiengang_kz).','.
					$this->addslashes($this->bezeichnung).','.
					$this->addslashes($this->semester).','.
					$this->addslashes($this->typ).','.
					$this->addslashes($this->mailgrp_kurzbz).','.
					$this->addslashes($this->mailgrp_beschreibung).','.
					($this->sichtbar?'true':'false').','.
					($this->aktiv?'true':'false').','.
					$this->addslashes($this->updateamum).','.
					$this->addslashes($this->updatevon).','.
					$this->addslashes($this->insertamum).','.
					$this->addslashes($this->insertvon).');';
		}
		else
		{
			$qry = 'UPDATE tbl_gruppe SET'.
			       ' studiengang_kz='.$this->addslashes($this->studiengang_kz).','.
			       ' bezeichnung='.$this->addslashes($this->bezeichnung).','.
			       ' semester='.$this->addslashes($this->semester).','.
			       ' typ='.$this->addslashes($this->typ).','.
			       ' mailgrp_kurzbz='.$this->addslashes($this->mailgrp_kurzbz).','.
			       ' mailgrp_beschreibung='.$this->addslashes($this->mailgrp_beschreibung).','.
			       ' sichtbar='.($this->sichtbar?'true':'false').','.
			       ' aktiv='.($this->aktiv?'true':'false').','.
			       ' updateamum='.$this->addslashes($this->updateamum).','.
			       ' updatevon='.$this->addslashes($this->updatevon).
			       " WHERE gruppe_kurzbz=".$this->addslashes($this->gruppe_kurzbz).";";
		}

		if(pg_query($this->conn,$qry))
		{
			//Log schreiben
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern der Gruppe:'.$qry;
			return false;
		}
	}
}
?>