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

class entwicklungsteam
{
	var $conn;    // @var resource DB-Handle
	var $new;      // @var boolean
	var $errormsg; // @var string
	var $result = array(); // @var email Objekt
	
	//Tabellenspalten
	var $mitarbeiter_uid;
	var $studiengang_kz;
	var $besqualcode;
	var $beginn;
	var $ende;
	var $updateamum;
	var $updatevon;
	var $insertamum;
	var $insertvon;
	var $ext_id;
	
	var $besqual;
	var $studiengang_kz_old;
	
	// ***********************************************
	// * Konstruktor
	// * @param conn    Connection zur Datenbank
	// *        mitarbeiter_uid ID des zu ladenden Datensatzes
	// *        studiengang_kz
	// ***********************************************
	function entwicklungsteam($conn, $mitarbeiter_uid=null, $studiengang_kz=null, $unicode=false)
	{
		$this->conn = $conn;
		/*
		if($unicode!=null)
		{
			if($unicode)
				$qry = "SET CLIENT_ENCODING TO 'UNICODE';";
			else 
				$qry = "SET CLIENT_ENCODING TO 'LATIN9';";
				
			if(!pg_query($conn,$qry))
			{
				$this->errormsg	 = "Encoding konnte nicht gesetzt werden";
				return false;
			}
		}
		*/
		if($mitarbeiter_uid != null && $studiengang_kz != null)
			$this->load($mitarbeiter_uid, $studiengang_kz);
	}
	
	// ***********************************************
	// * Laedt einen Datensatz
	// * @param mitarbeiter_uid ID des zu ladenden Datensatzes
	// *        studiengang_kz
	// ***********************************************
	function load($mitarbeiter_uid, $studiengang_kz)
	{
		if(!is_numeric($studiengang_kz) || $studiengang_kz == '')
		{
			$this->errormsg = 'studiengang_kz muss eine gueltige Zahl sein';
			return false;
		}
		
		//laden des Datensatzes
		$qry = "SELECT * FROM bis.tbl_entwicklungsteam JOIN bis.tbl_besqual USING(besqualcode) WHERE mitarbeiter_uid='".addslashes($mitarbeiter_uid)."' AND studiengang_kz='$studiengang_kz'";
		
		if($result = pg_query($this->conn,$qry))
		{
			if($row=pg_fetch_object($result))
			{
				$this->mitarbeiter_uid = $row->mitarbeiter_uid;
				$this->studiengang_kz = $row->studiengang_kz;
				$this->besqualcode = $row->besqualcode;
				$this->beginn = $row->beginn;
				$this->ende = $row->ende;
				$this->updateamum = $row->updateamum;
				$this->updatevon = $row->updatevon;
				$this->insertamum = $row->insertamum;
				$this->insertvon = $row->insertvon;
				$this->ext_id = $row->ext_id;
				$this->besqual = $row->besqualbez;				
				return true;		
			}
			else 
			{
				$this->errormsg = 'Fehler bei der Datenbankabfrage';
				return false;
			}
		}
		else
		{
			$this->errormsg = 'Fehler bei der Datenbankabfrage';
			return false;
		}
	}
			
	// **************************************************
	// * Loescht einen Datensatz
	// * @param bisverwendung_id ID des zu loeschenden Datensatzes
	// * @return true wenn ok, false im Fehlerfall
	// **************************************************
	function delete($mitarbeiter_uid, $studiengang_kz)
	{
		if(!is_numeric($studiengang_kz) || $studiengang_kz == '')
		{
			$this->errormsg = 'studiengang_kz muss eine gueltige Zahl sein';
			return false;
		}
		
		$qry = "DELETE FROM bis.tbl_entwicklungsteam WHERE mitarbeiter_uid = '".addslashes($mitarbeiter_uid)."' AND studiengang_kz='$studiengang_kz';";
		
		if(pg_query($this->conn,$qry))
		{
			//Log schreiben
			return true;
		}
		else 
		{
			$this->errormsg = 'Fehler beim Loeschen';
			return false;
		}
	}
	
	function validate()
	{
		return true;
	}
	
	// ************************************************
	// * wenn $var '' ist wird "null" zurueckgegeben
	// * wenn $var !='' ist werden datenbankkritische 
	// * Zeichen mit backslash versehen und das Ergebnis
	// * unter Hochkomma gesetzt.
	// ************************************************
	function addslashes($var)
	{
		return ($var!=''?"'".addslashes($var)."'":'null');
	}
	
	// *********************************************************************
	// * Speichert den aktuellen Datensatz
	// * Wenn $neu auf true gesetzt ist wird ein neuer Datensatz angelegt
	// * andernfalls wird der Datensatz mit der ID in $akte_id aktualisiert
	// * @return true wenn ok, false im Fehlerfall
	// *********************************************************************
	function save($new=null)
	{
		if(!$this->validate())
			return false;
		if($new==null)
			$new = $this->new;
			
		if($new)
		{
			//Neuen Datensatz anlegen	
			$qry = "INSERT INTO bis.tbl_entwicklungsteam (mitarbeiter_uid, studiengang_kz, besqualcode, beginn, ende,
					updateamum, updatevon, insertamum, insertvon, ext_id) VALUES (".
			       $this->addslashes($this->mitarbeiter_uid).', '.
			       $this->addslashes($this->studiengang_kz).', '.
			       $this->addslashes($this->besqualcode).', '.
			       $this->addslashes($this->beginn).', '.
			       $this->addslashes($this->ende).', '.
			       $this->addslashes($this->updateamum).', '.
			       $this->addslashes($this->updatevon).', '.
			       $this->addslashes($this->insertamum).', '.
			       $this->addslashes($this->insertvon).', '.
			       $this->addslashes($this->ext_id).');';
			       
		}
		else 
		{
			if($this->studiengang_kz_old=='')
				$this->studiengang_kz_old = $this->studiengang_kz;
				
			//Bestehenden Datensatz aktualisieren
			$qry= "UPDATE bis.tbl_entwicklungsteam SET".
				  " besqualcode=".$this->addslashes($this->besqualcode).",".
				  " beginn=".$this->addslashes($this->beginn).",".
				  " studiengang_kz=".$this->addslashes($this->studiengang_kz).",".
				  " ende=".$this->addslashes($this->ende).",".
				  " updateamum=".$this->addslashes($this->updateamum).",".
				  " updatevon=".$this->addslashes($this->updatevon).",".
				  " ext_id=".$this->addslashes($this->ext_id).
				  " WHERE mitarbeiter_uid='".addslashes($this->mitarbeiter_uid)."' AND studiengang_kz='$this->studiengang_kz_old'";
		}
		
		if(pg_query($this->conn, $qry))
		{
			return true;
		}
		else 
		{
			$this->errormsg = 'Fehler beim Speichern des Datensatzes';
			return false;
		}
	}
	
	// ********************************************
	// * Laedt alle Entwicklungsteameintraege eines Mitarbeiters
	// * @param $uid UID des Mitarbeiters
	// * @return true wenn ok, false wenn Fehler
	// ********************************************
	function getEntwicklungsteam($mitarbeiter_uid, $studiengang_kz=null)
	{
		//laden des Datensatzes
		$qry = "SELECT * FROM bis.tbl_entwicklungsteam JOIN bis.tbl_besqual USING(besqualcode) WHERE mitarbeiter_uid='".addslashes($mitarbeiter_uid)."'";
		
		if($studiengang_kz!=null)
			$qry.=" AND studiengang_kz='".addslashes($studiengang_kz)."'";
		
		if($result = pg_query($this->conn,$qry))
		{
			while($row=pg_fetch_object($result))
			{
				$obj = new entwicklungsteam($this->conn, null, null, null);
				
				$obj->mitarbeiter_uid = $row->mitarbeiter_uid;
				$obj->studiengang_kz = $row->studiengang_kz;
				$obj->besqualcode = $row->besqualcode;
				$obj->beginn = $row->beginn;
				$obj->ende = $row->ende;
				$obj->updateamum = $row->updateamum;
				$obj->updatevon = $row->updatevon;
				$obj->insertamum = $row->insertamum;
				$obj->insertvon = $row->insertvon;
				$obj->ext_id = $row->ext_id;
				$obj->besqual = $row->besqualbez;				
				
				$this->result[] = $obj;
			}
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler bei der Datenbankabfrage';
			return false;
		}
	}
	
	// *****
	// * Preuft ob der Eintrag schon existiert
	// *****
	function exists($mitarbeiter_uid,$studiengang_kz)
	{
		$qry = "SELECT count(*) as anzahl FROM bis.tbl_entwicklungsteam WHERE mitarbeiter_uid='".addslashes($mitarbeiter_uid)."' AND studiengang_kz='".addslashes($studiengang_kz)."'";
		
		if($result = pg_query($this->conn, $qry))
		{
			if($row = pg_fetch_object($result))
			{
				if($row->anzahl>0)
					return true;
				else 
					return false;
			}
			else 
			{
				$this->errormsg = 'Fehler beim Laden der Daten';
				return false;
			}
		}
		else 
		{
			$this->errormsg = 'Fehler beim Laden der Daten';
			return false;
		}
	}
}
?>