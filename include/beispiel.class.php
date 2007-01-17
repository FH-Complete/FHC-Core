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

class beispiel
{
	var $conn;     // resource DB-Handle
	var $errormsg; // string
	var $new;      // boolean
	var $beispiele = array(); // lehreinheit Objekt
	
	//Tabellenspalten
	var $beispiel_id;	// Serial
	var $uebung_id;		// integer
	var $bezeichnung;	// varchar(32)
	var $punkte;		// real
	var $updateamum;	// timestamp
	var $updatevon;		// varchar(16)
	var $insertamum;	// timestamp
	var $insertvon;		// varchar(16)
	
	var $student_uid;
	var $vorbereitet;
	var $probleme;
	
	// *************************************************************************
	// * Konstruktor - Uebergibt die Connection und laedt optional ein beispiel
	// * @param $conn        	Datenbank-Connection
	// * 		$beispiel_id
	// *        $unicode     	Gibt an ob die Daten mit UNICODE Codierung 
	// *                     	oder LATIN9 Codierung verarbeitet werden sollen
	// *************************************************************************
	function beispiel($conn, $beispiel_id=null, $unicode=false)
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
		
		if($beispiel_id!=null)
			$this->load($beispiel_id);
	}
	
	// *********************************************************
	// * Laedt ein Beispiel
	// * @param uebung_id
	// *********************************************************
	function load($beispiel_id)
	{
		if(!is_numeric($beispiel_id))
		{
			$this->errormsg='Beispiel_id muss eine gueltige Zahl sein';
			return false;
		}
		$qry = "SELECT * FROM campus.tbl_beispiel WHERE beispiel_id='$beispiel_id'";
		
		if($result=pg_query($this->conn, $qry))
		{
			if($row = pg_fetch_object($result))
			{
				$this->beispiel_id = $row->beispiel_id;
				$this->uebung_id = $row->uebung_id;
				$this->punkte = $row->punkte;
				$this->bezeichnung = $row->bezeichnung;
				$this->updateamum = $row->updateamum;
				$this->updatevon = $row->updatevon;
				$this->insertamum = $row->insertamum;
				$this->insertvon = $row->insertvon;
				return true;
			}
			else 
			{
				$this->errormsg = "Es ist kein Beispiel mit der ID $beispiel_id vorhanden";
				return false;
			}
		}
		else 
		{
			$this->errormsg = 'Fehler beim laden des Beispiels';
			return false;
		}
	}
	
	function load_beispiel($uebung_id)
	{
		if(!is_numeric($uebung_id))
		{
			$this->errormsg = 'Uebung_id muss eine gueltige Zahl sein';
			return false;
		}
		
		$qry = "SELECT * FROM campus.tbl_beispiel WHERE uebung_id='$uebung_id' ORDER BY bezeichnung";
				
		if($result=pg_query($this->conn, $qry))
		{
			while($row = pg_fetch_object($result))
			{
				$beispiel_obj = new beispiel($this->conn);

				$beispiel_obj->beispiel_id = $row->beispiel_id;
				$beispiel_obj->uebung_id = $row->uebung_id;
				$beispiel_obj->punkte = $row->punkte;
				$beispiel_obj->bezeichnung = $row->bezeichnung;
				$beispiel_obj->updateamum = $row->updateamum;
				$beispiel_obj->updatevon = $row->updatevon;
				$beispiel_obj->insertamum = $row->insertamum;
				$beispiel_obj->insertvon = $row->insertvon;
				
				$this->beispiele[] = $beispiel_obj;
			}
			return true;
		}
		else 
		{
			$this->errormsg = 'Fehler beim laden der Beispiele';
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
		if(!is_numeric($this->uebung_id))
		{
			$this->errormsg = 'uebung_id muss eine gueltige Zahl sein';
			return false;
		}
		if(strlen($this->bezeichnung)>32)
		{
			$this->errormsg = 'Bezeichnung darf nicht laenger als 32 Zeichen sein';
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
	// * Speichert ein Beispiel in die Datenbank
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
			if($this->exists($this->uebung_id, $this->bezeichnung))
			{
				$this->errormsg = 'Fehler beim Speichern! Es existiert bereits ein Beispiel mit diesem Namen';
				return false;
			}
			$qry = 'BEGIN; INSERT INTO campus.tbl_beispiel(uebung_id, punkte, bezeichnung, updateamum, 
			        updatevon, insertamum, insertvon) VALUES('.
			        $this->addslashes($this->uebung_id).','.
			        $this->addslashes($this->punkte).','.
			        $this->addslashes($this->bezeichnung).','.
			        $this->addslashes($this->updateamum).','.
			        $this->addslashes($this->updatevon).','.
			        $this->addslashes($this->insertamum).','.
			        $this->addslashes($this->insertvon).');';
		}
		else
		{
			$qry = 'UPDATE campus.tbl_beispiel SET'.
			       ' uebung_id='.$this->addslashes($this->uebung_id).','.
			       ' punkte='.$this->addslashes($this->punkte).','.
			       ' bezeichnung='.$this->addslashes($this->bezeichnung).','.
			       ' updateamum='.$this->addslashes($this->updateamum).','.
			       ' updatevon='.$this->addslashes($this->updatevon).
			       " WHERE beispiel_id=".$this->addslashes($this->beispiel_id).";";
		}
		
		if(pg_query($this->conn,$qry))
		{
			if($new)
			{
				$qry = "SELECT currval('campus.tbl_beispiel_beispiel_id_seq') as id;";
				if($result = pg_query($this->conn, $qry))
				{
					if($row=pg_fetch_object($result))
					{
						$this->uebung_id = $row->id;
						pg_query($this->conn, 'COMMIT');
						return true;
					}
					else 
					{
						$this->errormsg = 'Fehler beim Auslesen der Sequence';
						pg_query($this->conn,'ROLLBACK');
						return false;
					}
				}
				else 
				{
					$this->errormsg = 'Fehler beim Auslesen der Sequence';
					pg_query($this->conn,'ROLLBACK');
					return false;
				}
			}
			else 
				return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern des Beispiels';
			return false;
		}
	}
	
	function exists($uebung_id, $bezeichnung)
	{
		if(!is_numeric($uebung_id))
		{
			$this->errormsg = 'Uebung_id muss eine gueltige Zahl sein';
			return false;
		}
		
		$qry = "SELECT beispiel_id FROM campus.tbl_beispiel WHERE uebung_id='$uebung_id' AND bezeichnung=".$this->addslashes($bezeichnung);
		
		if($result = pg_query($this->conn, $qry))
		{
			if(pg_num_rows($result)>0)
				return true;
			else 
				return false;
		}
		else 
		{
			$this->errormsg ='Fehler beim lesen der Beispiele';
			return false;
		}
	}
	
	function studentbeispiel_exists($uid,$beispiel_id)
	{
		if(!is_numeric($beispiel_id))
		{
			$this->errormsg = 'Beispiel_id muss eine gueltige Zahl sein';
			return false;
		}
		
		$qry = "SELECT vorbereitet FROM campus.tbl_studentbeispiel WHERE beispiel_id='$beispiel_id' AND student_uid='".addslashes($uid)."'";
		
		if($result = pg_query($this->conn, $qry))
		{
			if(pg_num_rows($result)>0)
				return true;
			else 
				return false;
		}
		else 
		{
			$this->errormsg = 'Fehler beim lesen der aus der DB';
			return false;
		}
	}
	
	function delete($beispiel_id)
	{
		if(!is_numeric($beispiel_id))
		{
			$this->errormsg = 'Beispiel_id muss eine gueltige Zahl sein';
			return false;
		}
		
		$qry = "DELETE FROM campus.tbl_studentbeispiel WHERE beispiel_id='$beispiel_id';
				DELETE FROM campus.tbl_beispiel WHERE beispiel_id='$beispiel_id';";
		
		if(pg_query($this->conn, $qry))
			return true;
		else 	
		{
			$this->errormsg = 'Fehler beim loeschen des Beispiels';
			return false;
		}
	}
	
	function load_studentbeispiel($uid, $beispiel_id)
	{
		if(!is_numeric($beispiel_id))
		{
			$this->errormsg = 'Beispiel_id muss eine gueltige Zahl sein';
			return false;
		}
		$qry = "SELECT * FROM campus.tbl_studentbeispiel WHERE student_uid='$uid' AND beispiel_id='$beispiel_id'";
		
		if($result = pg_query($this->conn, $qry))
		{
			if($row = pg_fetch_object($result))
			{
				$this->beispiel_id = $row->beispiel_id;
				$this->student_uid = $row->student_uid;
				$this->vorbereitet = ($row->vorbereitet=='t'?true:false);
				$this->probleme = ($row->probleme=='t'?true:false);
				$this->updateamum = $row->updateamum;
				$this->udpatevon = $row->updatevon;
				$this->insertamum = $row->insertamum;
				$this->insertvon = $row->insertvon;
				return true;
			}
			else 
			{
				$this->errormsg = 'Fehler beim laden des Student_Beispiels';
				return false;
			}
		}
		else 
		{
			$this->errormsg = 'Fehler beim laden des Student_Beispiels';
			return false;
		}		
	}
	
	// **
	// * Prueft die studentbeispiel Daten auf gueltigkeit
	// *
	function studentbeispiel_validate()
	{
		if(!is_numeric($this->beispiel_id))
		{
			$this->errormsg = 'Beispiel_id muss eine gueltige Zahl sein';
			return false;
		}
		return true;
	}
	
	// **
	// * Speichert einen Studentbeispiel Datensatz in die DB
	// *
	// *
	function studentbeispiel_save($new=null)
	{
		if(is_null($new))
			$new = $this->new;
					
		//Variablen auf Gueltigkeit pruefen
		if(!$this->studentbeispiel_validate())
			return false;

		if($new)
		{			
			$qry = 'INSERT INTO campus.tbl_studentbeispiel(student_uid, beispiel_id, vorbereitet, probleme, 
					updateamum, updatevon, insertamum, insertvon) VALUES('.
			        $this->addslashes($this->student_uid).','.
			        $this->addslashes($this->beispiel_id).','.
			        $this->addslashes($this->vorbereitet).','.
			        $this->addslashes($this->probleme).','.
			        $this->addslashes($this->updateamum).','.
			        $this->addslashes($this->updatevon).','.
			        $this->addslashes($this->insertamum).','.
			        $this->addslashes($this->insertvon).');';
		}
		else
		{
			$qry = 'UPDATE campus.tbl_studentbeispiel SET'.
			       ' vorbereitet='.$this->addslashes($this->vorbereitet).','.
			       ' probleme='.$this->addslashes($this->probleme).','.
			       ' updateamum='.$this->addslashes($this->updateamum).','.
			       ' updatevon='.$this->addslashes($this->updatevon).
			       " WHERE beispiel_id=".$this->beispiel_id." AND student_uid=".$this->addslashes($this->student_uid).';';
		}
		
		if(pg_query($this->conn,$qry))
		{			
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern des Beispiels';
			return false;
		}
	}
}
?>