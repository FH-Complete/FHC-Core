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

class lehreinheitmitarbeiter
{
	var $conn;     // resource DB-Handle
	var $errormsg; // string
	var $new;      // boolean
	var $lehreinheitmitarbeiter = array(); // lehreinheitmitarbeiter Objekt
	
	//Tabellenspalten
	var $lehreinheit_id;	// integer
	var $uid;				// varchar(16)
	var $semesterstunden;	// smalint
	var $planstunden;		// smalint
	var $stundensatz;		// numeric(6,2)
	var $faktor;			// numeric(2,1)
	var $anmerkung;			// varchar(256)	
	
	// *************************************************************************
	// * Konstruktor - Uebergibt die Connection und laedt optional eine LE
	// * @param $conn        	Datenbank-Connection
	// *        $lehreinheit_id
	// *		$uid
	// *        $unicode     	Gibt an ob die Daten mit UNICODE Codierung 
	// *                     	oder LATIN9 Codierung verarbeitet werden sollen
	// *************************************************************************
	function lehreinheitmitarbeiter($conn, $lehreinheit_id=null, $uid=null, $unicode=false)
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
		
		if($lehreinheit_id!=null && $uid!=null)
			$this->load($lehreinheit_id, $uid);
	}
	
	// *********************************************************
	// * Laedt die LEMitarbeiter
	// * @param lehreinheit_id
	// *********************************************************
	function load($lehreinheit_id, $uid)
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
	// * Speichert LEMitarbeiter in die Datenbank
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
			$qry = 'INSERT INTO lehre.tbl_lehreinheitmitarbeiter (lehreinheit_id, uid, semesterstunden, planstunden, 
			                                                stundensatz, faktor, anmerkung)
			        VALUES('.$this->addslashes($this->lehreinheit_id).','.
					$this->addslashes($this->uid).','.
					$this->addslashes($this->semesterstunden).','.
					$this->addslashes($this->planstunden).','.
					$this->addslashes($this->stundensatz).','.
					$this->addslashes($this->faktor).','.
					$this->addslashes($this->anmerkung).');';
		}
		else
		{
			$qry = 'UPDATE lehre.tbl_lehreinheitmitarbeiter SET'.
			       ' semesterstunden='.$this->addslashes($this->semesterstunden).','.
			       ' planstunden='.$this->addslashes($this->planstunden).','.
			       ' stundensatz='.$this->addslashes($this->stundensatz).','.
			       ' faktor='.$this->addslashes($this->faktor).','.
			       ' anmerkung='.$this->addslashes($this->anmerkung).','.
			       " WHERE lehreinheit_id=".$this->addslashes($this->lehreinheit_id)." AND
			               uid=".$this->lehreinheit_id.";";
		}

		if(pg_query($this->conn,$qry))
		{
			//Log schreiben
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern der LEMitarbeiter:'.$qry;
			return false;
		}
	}
}
?>