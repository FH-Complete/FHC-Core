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

class uebung
{
	var $conn;     // resource DB-Handle
	var $errormsg; // string
	var $new;      // boolean
	var $uebungen = array(); // lehreinheit Objekt
	
	//Tabellenspalten
	var $uebung_id;		// serial
	var $gewicht;		// smalint
	var $punkte;		// Real
	var $angabedatei;	// oid
	var $freigabevon;	// timestamp
	var $freigabebis;	// timestamp
	var $abgabe;		// boolean
	var $beispiele;		// boolean
	var $bezeichnung;	// varchar(32)
	var $positiv;		// boolean
	var $defaultbemerkung;	// text
	var $lehreinheit_id;	// integer
	var $updateamum;		// timestamp
	var $updatevon;			// varchar(16)
	var $insertamum;		// timestamp
	var $insertvon;			// varchar(16)
	
	// *************************************************************************
	// * Konstruktor - Uebergibt die Connection und laedt optional eine Uebung
	// * @param $conn        	Datenbank-Connection
	// * 		$uebung_id
	// *        $unicode     	Gibt an ob die Daten mit UNICODE Codierung 
	// *                     	oder LATIN9 Codierung verarbeitet werden sollen
	// *************************************************************************
	function uebung($conn, $uebung_id=null, $unicode=false)
	{
		$this->conn = $conn;
		
		if($unicode)
			$qry = "SET CLIENT_ENCODING TO 'UNICODE';";
		else 
			$qry = "SET CLIENT_ENCODING TO 'LATIN9';";
			
		if(!pg_query($this->conn,$qry))
		{
			$this->errormsg	 = 'Encoding konnte nicht gesetzt werden';
			return false;
		}
		
		if($uebung_id!=null)
			$this->load($uebung_id);
	}
	
	// *********************************************************
	// * Laedt die Uebung
	// * @param uebung_id
	// *********************************************************
	function load($uebung_id)
	{
		if(!is_numeric($uebung_id))
		{
			$this->errormsg='Uebung_id muss eine gueltige Zahl sein';
			return false;
		}
		$qry = "SELECT * FROM campus.tbl_uebung WHERE uebung_id='$uebung_id'";
		
		if($result=pg_query($this->conn, $qry))
		{
			if($row = pg_fetch_object($result))
			{
				$this->uebung_id = $row->uebung_id;
				$this->gewicht = $row->gewicht;
				$this->punkte = $row->punkte;
				$this->angabedatei = $row->angabedatei;
				$this->freigabevon = $row->freigabevon;
				$this->freigabebis = $row->freigabebis;
				$this->abgabe = ($row->abgabe=='t'?true:false);
				$this->beispiele = ($row->beispiele=='t'?true:false);
				$this->bezeichnung = $row->bezeichnung;
				$this->positiv = ($row->positiv=='t'?true:false);
				$this->defaultbemerkung = $row->defaultbemerkung;
				$this->lehreinheit_id = $row->lehreinheit_id;
				$this->updateamum = $row->updateamum;
				$this->updatevon = $row->updatevon;
				$this->insertamum = $row->insertamum;
				$this->insertvon = $row->insertvon;
				return true;
			}
			else 
			{
				$this->errormsg = "Es ist keine Uebung mit der ID $uebung_id vorhanden";
				return false;
			}
		}
		else 
		{
			$this->errormsg = 'Fehler beim laden der Uebung';
			return false;
		}
	}
	
	function load_uebung($lehreinheit_id)
	{
		if(!is_numeric($lehreinheit_id))
		{
			$this->errormsg = 'Lehreinheit_id muss eine gueltige Zahl sein';
			return false;
		}
		
		$qry = "SELECT * FROM campus.tbl_uebung WHERE lehreinheit_id='$lehreinheit_id'";
				
		if($result=pg_query($this->conn, $qry))
		{
			while($row = pg_fetch_object($result))
			{
				$uebung_obj = new uebung($this->conn);
				
				$uebung_obj->uebung_id = $row->uebung_id;
				$uebung_obj->gewicht = $row->gewicht;
				$uebung_obj->punkte = $row->punkte;
				$uebung_obj->angabedatei = $row->angabedatei;
				$uebung_obj->freigabevon = $row->freigabevon;
				$uebung_obj->freigabebis = $row->freigabebis;
				$uebung_obj->abgabe = ($row->abgabe=='t'?true:false);
				$uebung_obj->beispiele = ($row->beispiele=='t'?true:false);
				$uebung_obj->bezeichnung = $row->bezeichnung;
				$uebung_obj->positiv = ($row->positiv=='t'?true:false);
				$uebung_obj->defaultbemerkung = $row->defaultbemerkung;
				$uebung_obj->lehreinheit_id = $row->lehreinheit_id;
				$uebung_obj->updateamum = $row->updateamum;
				$uebung_obj->updatevon = $row->updatevon;
				$uebung_obj->insertamum = $row->insertamum;
				$uebung_obj->insertvon = $row->insertvon;
				
				$this->uebungen[] = $uebung_obj;
			}
			return true;
		}
		else 
		{
			$this->errormsg = 'Fehler beim laden der Uebung';
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
		if(!is_numeric($this->lehreinheit_id))
		{
			$this->errormsg = 'Lehreinheit_id muss eine gueltige Zahl sein';
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
	// * Speichert Uebung in die Datenbank
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
			$qry = 'BEGIN; INSERT INTO campus.tbl_uebung(gewicht, punkte, angabedatei, freigabevon, freigabebis, 
			        abgabe, beispiele, bezeichnung, positiv, defaultbemerkung, lehreinheit_id, updateamum, 
			        updatevon, insertamum, insertvon) VALUES('.
			        $this->addslashes($this->gewicht).','.
			        $this->addslashes($this->punkte).','.
			        $this->addslashes($this->angabedatei).','.
			        $this->addslashes($this->freigabevon).','.
			        $this->addslashes($this->freigabebis).','.
			        ($this->abgabe?'true':'false').','.
			        ($this->beispiele?'true':'false').','.
			        $this->addslashes($this->bezeichnung).','.
			        ($this->positiv?'true':'false').','.
			        $this->addslashes($this->defaultbemerkung).','.
			        $this->addslashes($this->lehreinheit_id).','.
			        $this->addslashes($this->updateamum).','.
			        $this->addslashes($this->updatevon).','.
			        $this->addslashes($this->insertamum).','.
			        $this->addslashes($this->insertvon).');';
		}
		else
		{
			$qry = 'UPDATE campus.tbl_uebung SET'.
			       ' gewicht='.$this->addslashes($this->gewicht).','.
			       ' punkte='.$this->addslashes($this->punkte).','.
			       ' angabedatei='.$this->addslashes($this->angabedatei).','.
			       ' freigabevon='.$this->addslashes($this->freigabevon).','.
			       ' freigabebis='.$this->addslashes($this->freigabebis).','.
			       ' abgabe='.($this->abgabe?'true':'false').','.
			       ' beispiele='.($this->beispiele?'true':'false').','.
			       ' bezeichnung='.$this->addslashes($this->bezeichnung).','.
			       ' positiv='.($this->positiv?'true':'false').','.
			       ' defaultbemerkung='.$this->addslashes($this->defaultbemerkung).','.
			       ' lehreinheit_id='.$this->addslashes($this->lehreinheit_id).','.
			       ' updateamum='.$this->addslashes($this->updateamum).','.
			       ' updatevon='.$this->addslashes($this->updatevon).
			       " WHERE uebung_id=".$this->addslashes($this->uebung_id).";";
		}

		if(pg_query($this->conn,$qry))
		{
			if($new)
			{
				$qry = "SELECT currval('campus.tbl_uebung_uebung_id_seq') as id;";
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
			$this->errormsg = 'Fehler beim Speichern der Uebung:'.$qry;
			return false;
		}
	}
}
?>