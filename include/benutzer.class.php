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


class benutzer extends person
{
	//Tabellenspalten
	var $uid;		// varchar(16)
	var $bnaktiv=true;	// boolean
	var $alias;		// varchar(256)
		
	// *************************************************************************
	// * Konstruktor - Uebergibt die Connection und laedt optional einen Benutzer
	// * @param $conn        	Datenbank-Connection
	// *        $uid            Benutzer der geladen werden soll (default=null)
	// *        $unicode     	Gibt an ob die Daten mit UNICODE Codierung 
	// *                     	oder LATIN9 Codierung verarbeitet werden sollen
	// *************************************************************************
	function benutzer($conn, $uid=null, $unicode=false)
	{
		$this->conn = $conn;
		
		if($unicode!=null)
		{
			if($unicode)
				$qry = "SET CLIENT_ENCODING TO 'UNICODE';";
			else 
				$qry = "SET CLIENT_ENCODING TO 'LATIN9';";
				
			if(!pg_query($conn,$qry))
			{
				$this->errormsg	 = 'Encoding konnte nicht gesetzt werden';
				return false;
			}
		}
		
		if($uid != null)
			$this->load($uid);
	}
	
	// ***********************************************************
	// * Laedt Benutzer mit der uebergebenen ID
	// * @param $uid ID der Person die geladen werden soll
	// ***********************************************************
	function load($uid)
	{
		$qry = "SELECT * FROM public.tbl_benutzer WHERE uid='$uid'";
		if($result = pg_query($this->conn, $qry))
		{
			if($row = pg_fetch_object($result))
			{
				$this->uid = $row->uid;
				$this->bnaktiv = ($row->aktiv=='t'?true:false);
				$this->alias = $row->alias;
				if(!person::load($row->person_id))
					return false;
				else 
					return true;
			}
			else 
			{
				$this->errormsg = "Kein Eintrag gefunden fuer $uid";
				return false;
			}				
		}
		else 
		{
			$this->errormsg = "Fehler beim Laden der Benutzerdaten";
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
			$this->errormsg = 'UID darf nicht laenger als 16 Zeichen sein';
			return false;
		}
		if($this->uid == '')
		{
			$this->errormsg = 'UID muss eingegeben werden '.$this->uid;
			return false;
		}
		if(strlen($this->alias)>256)
		{
			$this->errormsg = 'Alias darf nicht laenger als 256 Zeichen sein';
			return false;
		}
		if(!is_numeric($this->person_id))
		{
			$this->errormsg = 'person_id muss eine gueltige Zahl sein';
			return false;
		}
		if(!is_bool($this->aktiv))
		{
			$this->errormsg = 'aktiv muss ein boolscher wert sein';
			return false;
		}
		
		if($this->alias!='')
		{
			$qry = "SELECT * FROM tbl_benutzer WHERE alias='".addslashes($this->alias)."' AND uid!='".$this->uid."'";
			if($result = pg_query($this->conn, $qry))
			{
				if(pg_num_rows($result)>0)
				{
					$this->errormsg = 'Dieser Alias ist bereits vergeben';
					return false;
				}
			}
			else 
			{
				$this->errormsg = 'Fehler beim Pruefen des Alias';
				return false;
			}
		}
		return true;
	}
	
	// ******************************************************************
	// * Speichert die Benutzerdaten in die Datenbank
	// * Wenn $new auf true gesetzt ist wird ein neuer Datensatz angelegt
	// * ansonsten der Datensatz mit $uid upgedated
	// * @return true wenn erfolgreich, false im Fehlerfall
	// ******************************************************************
	function save($new=null, $saveperson=true)
	{
		if($saveperson)
		{
			//Personen Datensatz speichern
			if(!person::save())
				return false;
		}
		
		if($new==null)
			$new = $this->new;
			
		//Variablen auf Gueltigkeit pruefen
		if(!benutzer::validate())
			return false;
		
		if($new) //Wenn new true ist dann ein INSERT absetzen ansonsten ein UPDATE
		{
			$qry = 'INSERT INTO public.tbl_benutzer (uid, aktiv, alias, person_id, insertamum, insertvon, updateamum, updatevon) VALUES('.
			       "'".addslashes($this->uid)."',".
			       ($this->bnaktiv?'true':'false').','.
			       $this->addslashes($this->alias).",'".
			       $this->person_id."',".
			       $this->addslashes($this->insertamum).",".
			       $this->addslashes($this->insertvon).",".
			       $this->addslashes($this->updateamum).",".
			       $this->addslashes($this->updatevon).");";
		}
		else
		{			
			$qry = 'UPDATE public.tbl_benutzer SET'.
			       ' aktiv='.($this->bnaktiv?'true':'false').','.
			       ' alias='.$this->addslashes($this->alias).','.
			       " person_id='".$this->person_id."',".
			       ' updateamum='.$this->addslashes($this->updateamum).','.
			       ' updatevon='.$this->addslashes($this->updatevon).
			       " WHERE uid='".addslashes($this->uid)."';";
		}
		
		if(pg_query($this->conn,$qry))
		{
			//Log schreiben
			return true;
		}
		else 
		{	
			$this->errormsg = 'Fehler beim Speichern des Benutzer-Datensatzes:'.$qry;
			return false;
		}
	}
	
	// ****
	// * Prueft ob die UID bereits existiert
	// ****
	function uid_exists($uid)
	{
		$qry = "SELECT * FROM public.tbl_benutzer WHERE uid='".addslashes($uid)."'";
		
		if($result = pg_query($this->conn, $qry))
		{
			if(pg_num_rows($result)>0)
			{
				$this->errormsg = '';
				return true;
			}
			else 
			{
				$this->errormsg = '';
				return false;
			}
				
		}
		else 
		{
			$this->errormsg = 'Fehler bei DatenbankAbfrage';
			return false;
		}
		
	}
	
	// ****
	// * Prueft ob der alias bereits existiert
	// ****
	function alias_exists($alias)
	{
		$qry = "SELECT * FROM public.tbl_benutzer WHERE alias='".addslashes($alias)."'";
		
		if($result = pg_query($this->conn, $qry))
		{
			if(pg_num_rows($result)>0)
			{
				$this->errormsg = '';
				return true;
			}
			else 
			{
				$this->errormsg = '';
				return false;
			}
				
		}
		else 
		{
			$this->errormsg = 'Fehler bei DatenbankAbfrage';
			return false;
		}
		
	}
}
?>