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

class akte
{
	var $conn;    // @var resource DB-Handle
	var $new;      // @var boolean
	var $errormsg; // @var string
	var $result = array(); // @var email Objekt
	
	//Tabellenspalten
	var $akte_id;
	var $person_id;
	var $dokument_kurzbz;
	var $inhalt;
	var $mimetype;
	var $erstelltam;
	var $gedruckt;
	var $titel;
	var $bezeichnung;
	var $updateamum;
	var $updatevon;
	var $insertamum;
	var $insertvon;
	var $uid;	
	
	// ***********************************************
	// * Konstruktor
	// * @param conn    Connection zur Datenbank
	// *        akte_id ID des zu ladenden Datensatzes
	// ***********************************************
	function akte($conn, $akte_id=null, $unicode=false)
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
		
		if($akte_id != null)
			$this->load($akte_id);
	}
	
	// ***********************************************
	// * Laedt einen Datensatz
	// * @param akte_id ID des zu ladenden Datensatzes
	// ***********************************************
	function load($akte_id)
	{
		//akte_id auf gueltigkeit pruefen
		if(!is_numeric($akte_id) || $akte_id == '')
		{
			$this->errormsg = 'akte_id muss eine gueltige Zahl sein';
			return false;
		}
		
		//laden des Datensatzes
		$qry = "SELECT * FROM public.tbl_akte WHERE akte_id='$akte_id';";
		
		if($result = pg_query($this->conn,$qry))
		{
			if($row=pg_fetch_object($result))
			{
				$this->akte_id = $row->akte_id;
				$this->person_id = $row->person_id;
				$this->dokument_kurzbz = $row->dokument_kurzbz;
				$this->inhalt = $row->inhalt;
				$this->mimetype = $row->mimetype;
				$this->erstelltam = $row->erstelltam;
				$this->gedruckt = ($row->gedruckt=='t'?true:false);
				$this->titel = $row->titel;
				$this->bezeichnung = $row->bezeichnung;
				$this->updateamum = $row->updateamum;
				$this->updatevon = $row->updatevon;
				$this->insertamum = $row->insertamum;
				$this->insertvon = $row->insertvon;
				$this->uid = $row->uid;
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
	// * @param akte_id ID des zu loeschenden Datensatzes
	// * @return true wenn ok, false im Fehlerfall
	// **************************************************
	function delete($akte_id)
	{
		//akte_id auf gueltigkeit pruefen
		if(!is_numeric($akte_id) || $akte_id == '')
		{
			$this->errormsg = 'akte_id muss eine gueltige Zahl sein';
			return false;
		}
		
		$qry = "DELETE FROM public.tbl_akte WHERE akte_id = '$akte_id';";
		
		if(pg_query($this->conn,$qry))
		{
			//Log schreiben
			return true;
		}
		else 
		{
			$this->errormsg = 'Fehler beim loeschen';
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
			$qry = "BEGIN;INSERT INTO public.tbl_akte (person_id, dokument_kurzbz, inhalt, mimetype, erstelltam, gedruckt, titel, 
					bezeichnung, updateamum, updatevon, insertamum, insertvon, ext_id, uid) VALUES (".
			       $this->addslashes($this->person_id).', '.
			       $this->addslashes($this->dokument_kurzbz).', '.
			       $this->addslashes($this->inhalt).', '.
			       $this->addslashes($this->mimetype).', '.
			       $this->addslashes($this->erstelltam).', '.
			       ($this->gedruckt?'true':'false').', '.
			       $this->addslashes($this->titel).', '.
			       $this->addslashes($this->bezeichnung).', '.
			       $this->addslashes($this->updateamum).', '.
			       $this->addslashes($this->updatevon).', '.
			       $this->addslashes($this->insertamum).', '.
			       $this->addslashes($this->insertvon).', '.
			       $this->addslashes($this->ext_id).', '.
			       $this->addslashes($this->uid).');';
			       
		}
		else 
		{
			//Bestehenden Datensatz aktualisieren
			$qry= "UPDATE public.tbl_akte SET ";
				  " person_id=".$this->addslashes($this->person_id).",".
				  " dokument_kurzbz=".$this->addslashes($this->dokument_kurzbz).",".
				  " inhalt=".$this->addslashes($this->inhalt).",".
				  " mimetype=".$this->addslashes($this->mimetype).",".
				  " erstelltam=".$this->addslashes($this->erstelltam).",".
				  " gedruckt=".($this->gedruckt?'true':'false').",".
				  " titel=".$this->addslashes($this->titel).",".
				  " bezeichnung=".$this->addslashes($this->bezeichnung).",".
				  " updateamum=".$this->addslashes($this->updateamum).",".
				  " updatevon=".$this->addslashes($this->updatevon).",".
				  " ext_id=".$this->addslashes($this->ext_id).",".
				  " uid=".$this->addslashes($this->uid).
				  " WHERE akte_id='".addslashes($akte_id)."'";
		}
		
		if(pg_query($this->conn, $qry))
		{
			if($new)
			{
				$qry = "SELECT currval('public.tbl_akte_akte_id_seq') as id";
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
	
	function getAkten($person_id, $dokument_kurzbz=null)
	{
		$qry = "SELECT akte_id, person_id, dokument_kurzbz, mimetype, erstelltam, gedruckt, titel, bezeichnung, updateamum, insertamum, updatevon, insertvon, uid FROM public.tbl_akte WHERE person_id='".addslashes($person_id)."'";
		if($dokument_kurzbz!=null)
			$qry.=" AND dokument_kurzbz='".addslashes($dokument_kurzbz)."'";
		$qry.=" ORDER BY erstelltam";
		
		if($result = pg_query($this->conn, $qry))
		{
			while($row = pg_fetch_object($result))
			{
				$akten = new akte($this->conn, null, null);
				
				$akten->akte_id = $row->akte_id;
				$akten->person_id = $row->person_id;
				$akten->dokument_kurzbz = $row->dokument_kurzbz;
				//$akte->inhalt = $row->inhalt;
				$akten->mimetype = $row->mimetype;
				$akten->erstelltam = $row->erstelltam;
				$akten->gedruckt = ($row->gedruckt=='t'?true:false);
				$akten->titel = $row->titel;
				$akten->bezeichnung = $row->bezeichnung;
				$akten->updateamum = $row->updateamum;
				$akten->updatevon = $row->updatevon;
				$akten->insertamum = $row->insertamum;
				$akten->insertvon = $row->insertvon;
				$akten->uid = $row->uid;
				
				$this->result[] = $akten;
			}
			return true;
		}
		else 
		{
			$this->errormsg = 'Fehler beim laden der Daten';
			return false;
		}			
	}
	
}
?>