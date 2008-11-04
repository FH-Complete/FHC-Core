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

class variable
{
	var $conn;     // resource DB-Handle
	var $errormsg; // string
	var $new;      // boolean
	var $variables = array(); // variable Objekt

	//Tabellenspalten
	var $uid;	// varchar(32)
	var $name;	// varchar(64)
	var $wert;	// varchar(64)

	// *************************************************************************
	// * Konstruktor - Uebergibt die Connection und laedt optional eine Variable
	// * @param $conn        	Datenbank-Connection
	// *        $uid
	// *		$name
	// *        $unicode     	Gibt an ob die Daten mit UNICODE Codierung
	// *                     	oder LATIN9 Codierung verarbeitet werden sollen
	// *************************************************************************
	function variable($conn, $uid=null, $name=null, $unicode=false)
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

		if($uid!=null && $name!=null)
			$this->load($uid, $name);
	}

	// *********************************************************
	// * Laedt die Variablen
	// * @param
	// *********************************************************
	function load($uid, $name)
	{
		$qry = "SELECT wert FROM public.tbl_variable WHERE uid='".addslashes($uid)."' AND name='".addslashes($name)."'";
		if($result = pg_query($this->conn, $qry))
		{
			if($row = pg_fetch_object($result))
			{
				$this->uid = $uid;
				$this->name = $name;
				$this->wert = $row->wert;
				
				return true;
			}
			else 
				return false;
		}
		else
			return false;
	}

	// *******************************************
	// * Prueft die Variablen vor dem Speichern
	// * auf Gueltigkeit.
	// * @return true wenn ok, false im Fehlerfall
	// *******************************************
	function validate()
	{
		if(strlen($this->uid)>32)
		{
			$this->errormsg = 'UID darf nicht laenger als 32 Zeichen sein';
			return true;
		}
		if(strlen($this->name)>64)
		{
			$this->errormsg = 'Name darf nicht laenger als 64 Zeichen sein';
			return false;
		}
		if(strlen($this->wert)>64)
		{
			$this->errormsg = 'Wert darf nicht laenger als 64 Zeichen sein';
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
	// * Speichert Variable in die Datenbank
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
			$qry = 'INSERT INTO public.tbl_variable (uid, name, wert)
			        VALUES('.$this->addslashes($this->uid).','.
					$this->addslashes($this->name).','.
					$this->addslashes($this->wert).');';
		}
		else
		{
			$qry = 'UPDATE public.tbl_variable SET'.
			       ' wert='.$this->addslashes($this->wert).
			       " WHERE uid='".addslashes($this->uid)."' AND name='".addslashes($this->name)."';";
		}

		if(pg_query($this->conn,$qry))
		{
			//Log schreiben
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern der Variable:'.$qry;
			return false;
		}
	}
	
	// ****
	// * Loescht einen Variableneintrag
	// ****
	function delete($name, $uid)
	{
		if($name=='' || $uid == '')
		{
			$this->errormsg = 'Name und UID muessen angegeben werden';
			return false;
		}
		
		$qry = "DELETE FROM public.tbl_variable WHERE name='".addslashes($name)."' AND uid='".addslashes($uid)."'";
		
		if(pg_query($this->conn, $qry))
			return true;
		else 
		{
			$this->errormsg = 'Fehler beim Loeschen';
			return false;
		}
	}
	
	// ******
	// * Liefert alle Variablen eines Benutzers
	// ******
	function getVars($uid)
	{
		$qry = "SELECT * FROM public.tbl_variable WHERE uid='".addslashes($uid)."' ORDER BY name";
		
		if($result = pg_query($this->conn, $qry))
		{
			while($row = pg_fetch_object($result))
			{
				$v = new variable($this->conn, null, null, null);
				
				$v->uid = $row->uid;
				$v->name = $row->name;
				$v->wert = $row->wert;
				
				$this->variables[] = $v;
			}
			return true;
		}
		else 
		{
			$this->errormsg = 'Fehler beim Laden der Daten';
			return false;
		}
	}
		
}
?>