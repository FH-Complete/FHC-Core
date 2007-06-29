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

class bisfunktion
{
	var $conn;    // @var resource DB-Handle
	var $new;      // @var boolean
	var $errormsg; // @var string
	var $result = array(); // @var email Objekt
	
	//Tabellenspalten
	var $bisverwendung_id;
	var $studiengang_kz;
	var $sws;
	var $updateamum;
	var $updatevon;
	var $insertamum;
	var $insertvon;
	var $ext_id;
	
	// ***********************************************
	// * Konstruktor
	// * @param conn    Connection zur Datenbank
	// *        bisverwendung_id ID des zu ladenden Datensatzes
	// ***********************************************
	function bisfunktion($conn, $bisverwendung_id=null, $studiengang_kz=null, $unicode=false)
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
				$this->errormsg	 = "Encoding konnte nicht gesetzt werden";
				return false;
			}
		}
		
		if($bisverwendung_id != null && $studiengang_kz != null)
			$this->load($bisverwendung_id, $studiengang_kz);
	}
	
	// ***********************************************
	// * Laedt einen Datensatz
	// * @param bisverwendung_id ID des zu ladenden Datensatzes
	// *        studiengang_kz
	// ***********************************************
	function load($bisverwendung_id, $studiengang_kz)
	{
		//bisverwendung_id auf gueltigkeit pruefen
		if(!is_numeric($bisverwendung_id) || $bisverwendung_id == '')
		{
			$this->errormsg = 'bisverwendung_id muss eine gueltige Zahl sein';
			return false;
		}
		if(!is_numeric($studiengang_kz) || $studiengang_kz == '')
		{
			$this->errormsg = 'studiengang_kz muss eine gueltige Zahl sein';
			return false;
		}
		
		//laden des Datensatzes
		$qry = "SELECT * FROM bis.tbl_bisfunktion WHERE bisverwendung_id='$bisverwendung_id' AND studiengang_kz='$studiengang_kz'";
		
		if($result = pg_query($this->conn,$qry))
		{
			if($row=pg_fetch_object($result))
			{
				$this->bisverwendung_id = $row->bisverwendung_id;
				$this->studiengang_kz = $row->studiengang_kz;
				$this->sws = $row->sws;
				$this->updateamum = $row->updateamum;
				$this->updatevon = $row->updatevon;
				$this->insertamum = $row->insertamum;
				$this->insertvon = $row->insertvon;
				$this->ext_id = $row->ext_id;
				
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
	function delete($bisverwendung_id, $studiengang_kz)
	{
		//akte_id auf gueltigkeit pruefen
		if(!is_numeric($bisverwendung_id) || $bisverwendung_id == '')
		{
			$this->errormsg = 'bisverwendung_id muss eine gueltige Zahl sein';
			return false;
		}
		if(!is_numeric($studiengang_kz) || $studiengang_kz == '')
		{
			$this->errormsg = 'studiengang_kz muss eine gueltige Zahl sein';
			return false;
		}
		
		$qry = "DELETE FROM bis.tbl_bisfunktion WHERE bisverwendung_id = '$bisverwendung_id' AND studiengang_kz='$studiengang_kz';";
		
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
			$qry = "BEGIN;INSERT INTO bis.tbl_bisfunktion (bisverwendung_id, studiengang_kz, sws
					updateamum, updatevon, insertamum, insertvon, ext_id) VALUES (".
			       $this->addslashes($this->bisverwendung_id).', '.
			       $this->addslashes($this->studiengang_kz).', '.
			       $this->addslashes($this->sws).', '.
			       $this->addslashes($this->updateamum).', '.
			       $this->addslashes($this->updatevon).', '.
			       $this->addslashes($this->insertamum).', '.
			       $this->addslashes($this->insertvon).', '.
			       $this->addslashes($this->ext_id).');';
			       
		}
		else 
		{
			//Bestehenden Datensatz aktualisieren
			$qry= "UPDATE bis.tbl_bisfunktion SET".
				  " sws=".$this->addslashes($this->sws).",".
				  " updateamum=".$this->addslashes($this->updateamum).",".
				  " updatevon=".$this->addslashes($this->updatevon).",".
				  " ext_id=".$this->addslashes($this->ext_id).
				  " WHERE bisverwendung_id='".addslashes($this->bisverwendung_id)."' AND studiengang_kz='$studiengang_kz'";
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
	// * Laedt alle Verwendungen eines Mitarbeiters
	// * @param $uid UID des Mitarbeiters
	// * @return true wenn ok, false wenn Fehler
	// ********************************************
	function getBisFunktion($bisverwendung_id, $studiengang_kz=null)
	{
		//laden des Datensatzes
		$qry = "SELECT * FROM bis.tbl_bisfunktion WHERE bisverwendung_id='".addslashes($bisverwendung_id)."'";
		
		if($studiengang_kz!=null)
			$qry.=" AND studiengang_kz='".addslashes($studiengang_kz)."'";
		
		if($result = pg_query($this->conn,$qry))
		{
			while($row=pg_fetch_object($result))
			{
				$obj = new bisfunktion($this->conn, null, null, null);
				
				$obj->bisverwendung_id = $row->bisverwendung_id;
				$obj->studiengang_kz = $row->studiengang_kz;
				$obj->sws = $row->sws;
				$obj->updateamum = $row->updateamum;
				$obj->updatevon = $row->updatevon;
				$obj->insertamum = $row->insertamum;
				$obj->insertvon = $row->insertvon;
				$obj->ext_id = $row->ext_id;
				
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
	
}
?>