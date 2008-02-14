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

class abschlusspruefung
{
	var $conn;    // @var resource DB-Handle
	var $new;      // @var boolean
	var $errormsg; // @var string
	var $result = array(); // @var email Objekt
	
	//Tabellenspalten
	var $abschlusspruefung_id;
	var $student_uid;
	var $vorsitz;
	var $pruefer1;
	var $pruefer2;
	var $pruefer3;
	var $abschlussbeurteilung_kurzbz;
	var $note;
	var $akadgrad_id;
	var $datum;
	var $sponsion;
	var $pruefungstyp_kurzbz;
	var $anmerkung;
	var $updateamum;
	var $updatevon;
	var $insertamum;
	var $insertvon;
	var $ext_id;
		
	// ***********************************************
	// * Konstruktor
	// * @param conn    Connection zur Datenbank
	// *        abschlusspruefung_id ID des zu ladenden Datensatzes
	// ***********************************************
	function abschlusspruefung($conn, $abschlusspruefung_id=null, $unicode=false)
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
		
		if($abschlusspruefung_id!= null)
			$this->load($abschlusspruefung_id);
	}
	
	// ***********************************************
	// * Laedt einen Datensatz
	// * @param abschlusspruefung_id ID des zu ladenden Datensatzes
	// ***********************************************
	function load($abschlusspruefung_id)
	{
		//id auf Gueltigkeit pruefen
		if(!is_numeric($abschlusspruefung_id))
		{
			$this->errormsg = 'abschlusspruefung_id muss eine gueltige Zahl sein';
			return false;
		}
		
		//laden des Datensatzes
		$qry = "SELECT * FROM lehre.tbl_abschlusspruefung WHERE abschlusspruefung_id='$abschlusspruefung_id';";
		
		if($result = pg_query($this->conn,$qry))
		{
			if($row=pg_fetch_object($result))
			{
				$this->abschlusspruefung_id = $row->abschlusspruefung_id;
				$this->student_uid = $row->student_uid;
				$this->vorsitz = $row->vorsitz;
				$this->pruefer1 = $row->pruefer1;
				$this->pruefer2 = $row->pruefer2;
				$this->pruefer3 = $row->pruefer3;
				$this->abschlussbeurteilung_kurzbz = $row->abschlussbeurteilung_kurzbz;
				$this->note = $row->note;
				$this->akadgrad_id = $row->akadgrad_id;
				$this->datum = $row->datum;
				$this->sponsion = $row->sponsion;
				$this->pruefungstyp_kurzbz = $row->pruefungstyp_kurzbz;
				$this->anmerkung = $row->anmerkung;
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
	// * @param abschlusspruefung_id ID des zu loeschenden Datensatzes
	// * @return true wenn ok, false im Fehlerfall
	// **************************************************
	function delete($abschlusspruefung_id)
	{
		//abschlusspruefung_id auf Gueltigkeit pruefen
		if(!is_numeric($abschlusspruefung_id))
		{
			$this->errormsg = 'abschlusspruefung_id muss eine gueltige Zahl sein';
			return false;
		}
		
		$qry = "DELETE FROM lehre.tbl_abschlusspruefung WHERE abschlusspruefung_id = '$abschlusspruefung_id';";
		
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
			$qry = "BEGIN;INSERT INTO lehre.tbl_abschlusspruefung (student_uid, vorsitz, pruefer1, pruefer2, pruefer3, abschlussbeurteilung_kurzbz, akadgrad_id,
					datum, sponsion, pruefungstyp_kurzbz, anmerkung, updateamum, updatevon, insertamum, insertvon, ext_id, note) VALUES (".
			       $this->addslashes($this->student_uid).', '.
			       $this->addslashes($this->vorsitz).', '.
			       $this->addslashes($this->pruefer1).', '.
			       $this->addslashes($this->pruefer2).', '.
			       $this->addslashes($this->pruefer3).', '.
			       $this->addslashes($this->abschlussbeurteilung_kurzbz).', '.
			       $this->addslashes($this->akadgrad_id).', '.
			       $this->addslashes($this->datum).', '.
			       $this->addslashes($this->sponsion).', '.
			       $this->addslashes($this->pruefungstyp_kurzbz).', '.
			       $this->addslashes($this->anmerkung).', '.
			       $this->addslashes($this->updateamum).', '.
			       $this->addslashes($this->updatevon).', '.
			       $this->addslashes($this->insertamum).', '.
			       $this->addslashes($this->insertvon).', '.
			       $this->addslashes($this->ext_id).','.
			       $this->addslashes($this->note).');';
			       
		}
		else 
		{
			//Bestehenden Datensatz aktualisieren
			$qry= "UPDATE lehre.tbl_abschlusspruefung SET".
				  " student_uid=".$this->addslashes($this->student_uid).",".
				  " vorsitz=".$this->addslashes($this->vorsitz).",".
				  " pruefer1=".$this->addslashes($this->pruefer1).",".
				  " pruefer2=".$this->addslashes($this->pruefer2).",".
				  " pruefer3=".$this->addslashes($this->pruefer3).",".
				  " abschlussbeurteilung_kurzbz=".$this->addslashes($this->abschlussbeurteilung_kurzbz).",".
				  " note=".$this->addslashes($this->note).",".
				  " akadgrad_id=".$this->addslashes($this->akadgrad_id).",".
				  " datum=".$this->addslashes($this->datum).",".
				  " sponsion=".$this->addslashes($this->sponsion).",".
				  " pruefungstyp_kurzbz=".$this->addslashes($this->pruefungstyp_kurzbz).",".
				  " anmerkung=".$this->addslashes($this->anmerkung).",".
				  " updateamum=".$this->addslashes($this->updateamum).",".
				  " updatevon=".$this->addslashes($this->updatevon).",".
				  " ext_id=".$this->addslashes($this->ext_id).
				  " WHERE abschlusspruefung_id='".addslashes($this->abschlusspruefung_id)."'";
		}
		
		if(pg_query($this->conn, $qry))
		{
			if($new)
			{
				$qry = "SELECT currval('lehre.tbl_abschlusspruefung_abschlusspruefung_id') as id";
				if($result = pg_query($this->conn, $qry))
				{
					if($row = pg_fetch_object($result))
					{
						$this->abschlusspruefung_id = $row->id;
						pg_query($this->conn, 'COMMIT;');
						return true;
					}
					else 
					{
						$this->errormsg = 'Fehler beim Auslesen der Sequence';
						pg_query($this->conn, 'ROLLBACK');
						return false;
					}
				}
				else 
				{
					$this->errormsg = 'Fehler beim Auslesen der Sequence';
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
	
	// ************************************************
	// * Laedt alle Abschlusspruefungen eines Studenten
	// * @param student_uid UID des Studenten
	// * @return true wenn ok, false wenn Fehler
	// ************************************************
	function getAbschlusspruefungen($student_uid)
	{
		$qry = "SELECT * FROM lehre.tbl_abschlusspruefung WHERE student_uid='".addslashes($student_uid)."' ORDER BY datum";
		
		if($result = pg_query($this->conn, $qry))
		{
			while($row = pg_fetch_object($result))
			{
				$obj = new abschlusspruefung($this->conn, null, null);
				
				$obj->abschlusspruefung_id = $row->abschlusspruefung_id;
				$obj->student_uid = $row->student_uid;
				$obj->vorsitz = $row->vorsitz;
				$obj->pruefer1 = $row->pruefer1;
				$obj->pruefer2 = $row->pruefer2;
				$obj->pruefer3 = $row->pruefer3;
				$obj->abschlussbeurteilung_kurzbz = $row->abschlussbeurteilung_kurzbz;
				$obj->note = $row->note;
				$obj->akadgrad_id = $row->akadgrad_id;
				$obj->datum = $row->datum;
				$obj->sponsion = $row->sponsion;
				$obj->pruefungstyp_kurzbz = $row->pruefungstyp_kurzbz;
				$obj->anmerkung = $row->anmerkung;
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
			$this->errormsg = 'Fehler beim Laden der Daten';
			return false;
		}			
	}
	
}
?>