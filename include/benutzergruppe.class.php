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

class benutzergruppe
{
	var $conn;     // resource DB-Handle
	var $errormsg; // string
	var $new;      // boolean
	var $benutzergruppen = array(); // benutzergruppe Objekt
	
	//Tabellenspalten
	var $uid;			// varchar(16)
	var $gruppe_kurzbz;	// varchar(16)
	var $updateamum;	// timestamp
	var $updatevon;		// varchar(16)
	var $insertamum;	// timestamp
	var $insertvon;		// varchar(16)
	var $studiensemester_kurzbz; // varchar(16)
	
	// *************************************************************************
	// * Konstruktor - Uebergibt die Connection und laedt optional eine BenutzerGruppe
	// * @param $conn        	Datenbank-Connection
	// *		$uid
	// *        $gruppe_kurzbz
	// *        $unicode     	Gibt an ob die Daten mit UNICODE Codierung 
	// *                     	oder LATIN9 Codierung verarbeitet werden sollen
	// *************************************************************************
	function benutzergruppe($conn, $uid=null, $gruppe_kurzbz=null, $unicode=false)
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
		
		if($gruppe_kurzbz!=null && $uid!=null)
			$this->load($uid, $gruppe_kurzbz);
	}
	
	// *********************************************************
	// * Laedt die BenutzerGruppe
	// * @param uid, gruppe_kurzbz, studiensemester_kurzbz
	// * @return true wenn ok, false im Fehlerfall
	// *********************************************************
	function load($uid, $gruppe_kurzbz, $studiensemester_kurzbz=null)
	{
		$qry = "SELECT * FROM public.tbl_benutzergruppe WHERE uid='".addslashes($uid)."' AND gruppe_kurzbz='".addslashes($gruppe_kurzbz)."'";
		if($studiensemester_kurzbz!=null)
			$qry.=" AND studiensemester_kurzbz='".addslashes($studiensemester_kurzbz)."'";
		
		if($result = pg_query($this->conn, $qry))
		{
			if($row = pg_fetch_object($result))
			{
				$this->uid = $row->uid;
				$this->gruppe_kurzbz = $row->gruppe_kurzbz;
				$this->updateamum = $row->updateamum;
				$this->updatevon = $row->updatevon;
				$this->insertamum = $row->insertamum;
				$this->insertvon = $row->insertvon;
				$this->studiensemester_kurzbz = $row->studiensemester_kurzbz;
				return true;
			}
			else 
			{
				$this->errormsg = 'Es wurde keine Datensatz gefunden';
				return false;				
			}
		}
		else 
		{
			$this->errormsg = 'Fehler beim laden des Datensatzes';
			return false;
		}
	}
	
	// *********************************************************
	// * Laedt die User in einer Benutzergruppe
	// * @param gruppe_kurzbz, stsem
	// * @return true wenn ok, false im Fehlerfall
	// *********************************************************
	function load_uids($gruppe_kurzbz, $stsem)
	{
		$qry = "SELECT * FROM public.tbl_benutzergruppe WHERE gruppe_kurzbz='".addslashes($gruppe_kurzbz)."' and studiensemester_kurzbz = '".$stsem."'";
		
		if($result = pg_query($this->conn, $qry))
		{
			if (pg_num_rows($result) == 0)
				return false;
			else
			{			
				while($row = pg_fetch_object($result))
				{
					$bg_obj = new benutzergruppe($this->conn);
					$bg_obj->uid = $row->uid;
					$this->uids[] = $bg_obj;
				}
				return true;
			}
		}
		else 
		{
			$this->errormsg = 'Fehler beim laden des Datensatzes';
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
		if(strlen($this->uid)>16)
		{
			$this->errormsg = 'UID darf nich laenger als 16 Zeichen sein';
			return false;
		}
		if(strlen($this->gruppe_kurzbz)>16)
		{
			$this->errormsg = 'Gruppe_kurzbz darf nicht laenger als 16 Zeichen sein';
			return false;
		}
		if(strlen($this->updatevon)>16)
		{
			//ToDo: Just 4 Sync dannach wieder errormsg setzen
			$this->updatevon = substr($this->updatevon,0,15);
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
	// * Speichert BenutzerGruppe in die Datenbank
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
			$qry = 'INSERT INTO public.tbl_benutzergruppe (uid, gruppe_kurzbz, updateamum, updatevon, insertamum, insertvon, studiensemester_kurzbz)
			        VALUES('.$this->addslashes($this->uid).','.
					$this->addslashes($this->gruppe_kurzbz).','.
					$this->addslashes($this->updateamum).','.
					$this->addslashes($this->updatevon).','.
					$this->addslashes($this->insertamum).','.
					$this->addslashes($this->insertvon).','.
					$this->addslashes($this->studiensemester_kurzbz).');';
		}
		else
		{
			//ToDo
			$qry = 'Select 1;';
		}

		if(pg_query($this->conn,$qry))
		{
			//Log schreiben
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern der BenutzerGruppe:'.$qry;
			return false;
		}
	}
	
	function delete($uid, $gruppe_kurzbz)
	{
		$qry = "DELETE FROM public.tbl_benutzergruppe WHERE uid='".addslashes($uid)."' AND gruppe_kurzbz='".addslashes($gruppe_kurzbz)."'";
		
		if(pg_query($this->conn, $qry))
			return true;
		else 
		{
			$this->errormsg = 'Fehler beim Loeschen der Zuteilung';
			return false;
		}
	}
}
?>