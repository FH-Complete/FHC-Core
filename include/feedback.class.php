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

class feedback
{
	var $conn;     // resource DB-Handle
	var $errormsg; // string
	var $new;      // boolean
	var $result = array(); // feedback Objekt
	
	//Tabellenspalten
	var $feedback_id;	// integer
	var $betreff;		// varchar(128)
	var $text;			// text
	var $datum;			// date
	var $uid;			// varchar(16)
	var $lehrveranstaltung_id; // integer
	
	// *************************************************************************
	// * Konstruktor - Uebergibt die Connection und laedt optional eine Lehrform
	// * @param $conn        	Datenbank-Connection
	// *        $feedback_id    
	// *        $unicode     	Gibt an ob die Daten mit UNICODE Codierung 
	// *                     	oder LATIN9 Codierung verarbeitet werden sollen
	// *************************************************************************
	function feedback($conn, $feedback_id=null, $unicode=false)
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
		
		if($feedback_id!=null)
			$this->load($feedback_id);
	}
	
	// *********************************************************
	// * Laedt ein Feedback
	// * @param 
	// *********************************************************
	function load($feedback_id)
	{
		if(!is_numeric($feedback_id))
		{
			$this->errormsg = 'feedback_id muss eine gueltige Zahl sein';
			return false;
		}
		
		$qry = "SELECT * FROM campus.tbl_feedback WHERE feedback_id='$feedback_id'";
		
		if($result = pg_query($this->conn, $qry))
		{
			$this->feedback_id=$row->feedback_id;
			$this->betreff=$row->betreff;
			$this->text=$row->text;
			$this->datum=$row->datum;
			$this->uid=$row->uid;
			$this->lehrveranstaltung_id=$row->lehrveranstaltung_id;
		}
		else 
		{
			$this->errormsg = 'Fehler beim laden der Lehrveranstaltungen';
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
		if(strlen($this->betreff)>128)
		{
			$this->errormsg = 'Betreff darf nicht laenger als 128 Zeichen sein';
			return false;
		}
		if(strlen($this->uid)>16)
		{
			$this->errormsg = 'UID darf nicht laenger als 16 Zeichen sein';
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

	function load_feedback($lehrveranstaltung_id)
	{
		if(!is_numeric($lehrveranstaltung_id))
		{
			$this->errormsg = 'Lehrveranstaltung_id muss eine gueltige Zahl sein';
			return false;
		}
		
		$qry = "SELECT * FROM campus.tbl_feedback WHERE lehrveranstaltung_id='$lehrveranstaltung_id'";
		
		if($result = pg_query($this->conn, $qry))
		{
			while($row=pg_fetch_object($result))
			{				
				$fb_obj = new feedback($this->conn);
				
				$fb_obj->feedback_id=$row->feedback_id;
				$fb_obj->betreff=$row->betreff;
				$fb_obj->text=$row->text;
				$fb_obj->datum=$row->datum;
				$fb_obj->uid=$row->uid;
				$fb_obj->lehrveranstaltung_id=$row->lehrveranstaltung_id;
				
				$this->result[] = $fb_obj;
			}
			return true;
		}
		else 
		{
			$this->errormsg = 'Fehler beim laden der Lehrveranstaltungen';
			return false;
		}	
	}
	
	// ************************************************************
	// * Speichert Feedback in die Datenbank
	// * Wenn $new auf true gesetzt ist wird ein neuer Datensatz
	// * angelegt, ansonsten der Datensatz upgedated
	// * @return true wenn erfolgreich, false im Fehlerfall
	// ************************************************************
	function save()
	{
		//Variablen auf Gueltigkeit pruefen
		if(!$this->validate())
			return false;

		if($this->new)
		{
			//ToDo: Feedback_ID wieder entfernen und per Seq fuellen
			$qry = 'INSERT INTO campus.tbl_feedback (betreff, text, datum, uid, lehrveranstaltung_id)
			        VALUES('.$this->addslashes($this->betreff).','.
					$this->addslashes($this->text).','.
					$this->addslashes($this->datum).','.
					$this->addslashes($this->uid).','.
					$this->addslashes($this->lehrveranstaltung_id).');';
		}
		else
		{
			$qry = 'UPDATE campus.tbl_feedback SET'.
			       ' betreff='.$this->addslashes($this->betreff).','.
			       ' text='.$this->addslashes($this->text).','.
			       ' datum='.$this->addslashes($this->datum).','.
			       ' uid='.$this->addslashes($this->uid).
			       " WHERE feedback_id='".addslashes($this->feedback_id)."'";
		}

		if(pg_query($this->conn,$qry))
		{
			//Log schreiben
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern des Feedbacks:'.$qry;
			return false;
		}
	}
}
?>