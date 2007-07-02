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

class bisverwendung
{
	var $conn;    // @var resource DB-Handle
	var $new;      // @var boolean
	var $errormsg; // @var string
	var $result = array(); // @var email Objekt
	
	//Tabellenspalten
	var $bisverwendung_id;
	var $ba1code;
	var $ba2code;
	var $beschausmasscode;
	var $verwendung_code;
	var $mitarbeiter_uid;
	var $hauptberufcode;
	var $hauptberuflich;
	var $habilitation;
	var $beginn;
	var $ende;
	var $updateamum;
	var $updatevon;
	var $insertamum;
	var $insertvon;
	var $ext_id;	
	
	var $ba1bez;
	var $ba2bez;
	var $beschausmass;
	var $verwendung;
	var $hauptberuf;
	
	// ***********************************************
	// * Konstruktor
	// * @param conn    Connection zur Datenbank
	// *        bisverwendung_id ID des zu ladenden Datensatzes
	// ***********************************************
	function bisverwendung($conn, $bisverwendung_id=null, $unicode=false)
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
		
		if($bisverwendung_id != null)
			$this->load($bisverwendung_id);
	}
	
	// ***********************************************
	// * Laedt einen Datensatz
	// * @param bisverwendung_id ID des zu ladenden Datensatzes
	// ***********************************************
	function load($bisverwendung_id)
	{
		//bisverwendung_id auf gueltigkeit pruefen
		if(!is_numeric($bisverwendung_id) || $bisverwendung_id == '')
		{
			$this->errormsg = 'bisverwendung_id muss eine gueltige Zahl sein';
			return false;
		}
		
		//laden des Datensatzes
		$qry = "SELECT * FROM bis.tbl_bisverwendung, bis.tbl_beschaeftigungsart1, bis.tbl_beschaeftigungsart2, 
				bis.bescharftigungsausmass, bis.tbl_verwendung WHERE 
				tbl_bisverwendung.ba1code=beschaeftigungsart1.ba1code AND
				tbl_bisverwendung.ba2code=beschaeftigungsart2.ba2code AND
				tbl_bisverwendung.beschausmasscode=beschaeftigungsausmass.beschausmasscode AND
				tbl_bisverwendung.verwendung_code=tbl_verwendung.verwendung_code AND
				bisverwendung_id='$bisverwendung_id';";
		
		if($result = pg_query($this->conn,$qry))
		{
			if($row=pg_fetch_object($result))
			{
				$this->bisverwendung_id = $row->bisverwendung_id;
				$this->ba1code = $row->ba1code;
				$this->ba2code = $row->ba2code;
				$this->beschausmasscode = $row->beschausmasscode;
				$this->verwendung_code = $row->verwendung_code;
				$this->mitarbeiter_uid = $row->mitarbeiter_uid;
				$this->hauptberufcode = $row->hauptberufcode;
				$this->hauptberuflich = ($row->hauptberuflich=='t'?true:false);
				$this->habilitation = ($row->habilitation=='t'?true:false);
				$this->beginn = $row->beginn;
				$this->ende = $row->ende;
				$this->updatevon = $row->updatevon;
				$this->updateamum = $row->updateamum;
				$this->insertamum = $row->insertamum;
				$this->insertvon = $row->insertvon;
				$this->ext_id = $row->ext_id;
				$this->ba1bez = $row->ba1bez;
				$this->ba2bez = $row->ba2bez;
				$this->beschausmass = $row->beschausmassbez;
				$this->verwendung = $row->verwendungbez;
				$this->hauptberuf = $row->bezeichnung;
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
	function delete($bisverwendung_id)
	{
		//akte_id auf gueltigkeit pruefen
		if(!is_numeric($bisverwendung_id) || $bisverwendung_id == '')
		{
			$this->errormsg = 'bisverwendung_id muss eine gueltige Zahl sein';
			return false;
		}
		
		$qry = "DELETE FROM bis.tbl_bisverwendung WHERE bisverwendung_id = '$bisverwendung_id';";
		
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
			$qry = "BEGIN;INSERT INTO bis.tbl_bisverwendung (ba1code, ba2code, beschausmasscode, 
					verwendung_code, mitarbeiter_uid, hauptberufcode, hauptberuflich, habilitation, beginn, ende, 
					updateamum, updatevon, insertamum, insertvon, ext_id) VALUES (".
			       $this->addslashes($this->ba1code).', '.
			       $this->addslashes($this->ba2code).', '.
			       $this->addslashes($this->beschausmasscode).', '.
			       $this->addslashes($this->verwendung_code).', '.
			       $this->addslashes($this->mitarbeiter_uid).', '.
			       $this->addslashes($this->hauptberufcode).', '.
			       ($this->hauptberuflich?'true':'false').', '.
			       ($this->habilitation?'true':'false').', '.
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
			//Bestehenden Datensatz aktualisieren
			$qry= "UPDATE bis.tbl_bisverwendung SET".
				  " ba1code=".$this->addslashes($this->ba1code).",".
				  " ba2code=".$this->addslashes($this->ba2code).",".
				  " beschausmasscode=".$this->addslashes($this->beschausmasscode).",".
				  " verwendung_code=".$this->addslashes($this->verwendung_code).",".
				  " mitarbeiter_uid=".$this->addslashes($this->mitarbeiter_uid).",".
				  " hauptberufcode=".$this->addslashes($this->hauptberufcode).",".
				  " hauptberuflich=".($this->hauptberuflich?'true':'false').",".
				  " habilitation=".($this->habilitation?'true':'false').",".
				  " beginn=".$this->addslashes($this->beginn).",".
				  " ende=".$this->addslashes($this->ende).",".
				  " updateamum=".$this->addslashes($this->updateamum).",".
				  " updatevon=".$this->addslashes($this->updatevon).",".
				  " insertamum=".$this->addslashes($this->insertamum).",".
				  " insertvon=".$this->addslashes($this->insertvon).",".
				  " ext_id=".$this->addslashes($this->ext_id).
				  " WHERE bisverwendung_id='".addslashes($this->bisverwendung_id)."'";
		}
		
		if(pg_query($this->conn, $qry))
		{
			if($new)
			{
				$qry = "SELECT currval('bis.tbl_bisverwendung_bisverwendung_id_seq') as id";
				if($result = pg_query($this->conn, $qry))
				{
					if($row = pg_fetch_object($result))
					{
						$this->akte_id = $row->id;
						pg_query($this->conn, 'COMMIT;');
						return true;
					}
					else 
					{
						$this->errormsg = 'Fehler beim auslesen der Sequence';
						pg_query($this->conn, 'ROLLBACK');
						return false;
					}
				}
				else 
				{
					$this->errormsg = 'Fehler beim auslesen der Sequence';
					pg_query($this->conn, 'ROLLBACK');
					return false;
				}
			}
			else 
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
	function getVerwendung($uid)
	{
		//laden des Datensatzes
		$qry = "SELECT * FROM bis.tbl_bisverwendung, bis.tbl_beschaeftigungsart1, bis.tbl_beschaeftigungsart2, 
				bis.tbl_beschaeftigungsausmass, bis.tbl_verwendung, bis.tbl_hauptberuf WHERE 
				tbl_bisverwendung.ba1code=tbl_beschaeftigungsart1.ba1code AND
				tbl_bisverwendung.ba2code=tbl_beschaeftigungsart2.ba2code AND
				tbl_bisverwendung.beschausmasscode=tbl_beschaeftigungsausmass.beschausmasscode AND
				tbl_bisverwendung.verwendung_code=tbl_verwendung.verwendung_code AND
				tbl_bisverwendung.hauptberufcode=tbl_hauptberuf.hauptberufcode AND
				mitarbeiter_uid='".addslashes($uid)."';";
		
		if($result = pg_query($this->conn,$qry))
		{
			while($row=pg_fetch_object($result))
			{
				$obj = new bisverwendung($this->conn, null, null);
				
				$obj->bisverwendung_id = $row->bisverwendung_id;
				$obj->ba1code = $row->ba1code;
				$obj->ba2code = $row->ba2code;
				$obj->beschausmasscode = $row->beschausmasscode;
				$obj->verwendung_code = $row->verwendung_code;
				$obj->mitarbeiter_uid = $row->mitarbeiter_uid;
				$obj->hauptberufcode = $row->hauptberufcode;
				$obj->hauptberuflich = ($row->hauptberuflich=='t'?true:false);
				$obj->habilitation = ($row->habilitation=='t'?true:false);
				$obj->beginn = $row->beginn;
				$obj->ende = $row->ende;
				$obj->updatevon = $row->updatevon;
				$obj->updateamum = $row->updateamum;
				$obj->insertamum = $row->insertamum;
				$obj->insertvon = $row->insertvon;
				$obj->ext_id = $row->ext_id;
				$obj->ba1bez = $row->ba1bez;
				$obj->ba2bez = $row->ba2bez;
				$obj->beschausmass = $row->beschausmassbez;
				$obj->verwendung = $row->verwendungbez;
				$obj->hauptberuf = $row->bezeichnung;
				
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