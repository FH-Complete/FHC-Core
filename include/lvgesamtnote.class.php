<?php
/* Copyright (C) 2007 Technikum-Wien
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
/**
 * Klasse Zeugnisnote
 * @create 2007-06-06
 */

class lvgesamtnote
{
	var $conn;     			// resource DB-Handle
	var $new;       		// boolean
	var $errormsg;  		// string
	var $result=array();					
		
	//Tabellenspalten
	var $lehrveranstaltung_id;		// integer
	var $student_uid;				// varchar(16)
	var $mitarbeiter_uid;			// varchar(16)
	var $studiensemester_kurzbz;	// varchar(16)
	var $note;						// smalint
	var $freigabedatum;				// date
	var $benotungsdatum;			// date
	var $updateamum;				// timestamp
	var $updatevon;					// varchar(16)
	var $insertamum;				// timestamp
	var $insertvon;					// varchar(16)
	var $bemerkung;					// text
	var $freigabevon_uid;			//varchar(16)
	
	var $lehrveranstaltung_bezeichung;
	var $note_bezeichnung;
	
	// *********************************************************************
	// * Konstruktor
	// * @param $conn      Connection
	// *        $lehrveranstaltung_id
	// *        $student_uid
	// *        $studiensemester_kurzbz
	// *********************************************************************
	function lvgesamtnote($conn, $lehrveranstaltung_id=null, $student_uid=null, $studiensemester_kurzbz=null , $unicode=false)
	{
		$this->conn = $conn;
		
		if($unicode!=null)
		{
			if ($unicode)
				$qry = "SET CLIENT_ENCODING TO 'UNICODE';";
			else 
				$qry="SET CLIENT_ENCODING TO 'LATIN9';";

			if(!pg_query($conn,$qry))
			{
				$this->errormsg= "Encoding konnte nicht gesetzt werden";
				return false;
			}
		}
		
		if($lehrveranstaltung_id!=null && $student_uid!=null && $studiensemester_kurzbz!=null)
			$this->load($lehrveranstaltung_id, $student_uid, $studiensemester_kurzbz);
	}
	
	// **************************************************************
	// * Laedt eine LVGesamtNote
	// * @param  $lehrveranstaltung_id
	// *         $student_uid
	// *         $studiensemester_kurzbz
	// * @return true wenn ok, false im Fehlerfall
	// ***************************************************************
	function load($lehrveranstaltung_id, $student_uid, $studiensemester_kurzbz)
	{
		if(!is_numeric($lehrveranstaltung_id))
		{
			$this->errormsg = 'Lehrveranstaltung_id ist ungueltig';
			return false;
		}
		
		$qry = "SELECT * FROM campus.tbl_lvgesamtnote WHERE 
				lehrveranstaltung_id='$lehrveranstaltung_id' AND 
				student_uid='".addslashes($student_uid)."' AND
				studiensemester_kurzbz='".addslashes($studiensemester_kurzbz)."'";
		
		if($result = pg_query($this->conn, $qry))
		{
			if($row = pg_fetch_object($result))
			{
				$this->lehrveranstaltung_id = $row->lehrveranstaltung_id;
				$this->student_uid = $row->student_uid;
				$this->mitarbeiter_uid = $row->mitarbeiter_uid;
				$this->studiensemester_kurzbz = $row->studiensemester_kurzbz;
				$this->note = $row->note;
				$this->freigabedatum = $row->freigabedatum;
				$this->benotungsdatum = $row->benotungsdatum;
				$this->updateamum = $row->updateamum;
				$this->updatevon = $row->updatevon;
				$this->insertamum = $row->insertamum;
				$this->inservon = $row->insertvon;
				$this->bemerkung = $row->bemerkung;
				$this->freigabevon_uid = $row->freigabevon_uid;
				return true;				
			}
			else 
			{
				$this->errormsg = 'Datensatz wurde nicht gefunden';
				return false;
			}
		}
		else 
		{
			$this->errormsg = 'Fehler beim Laden der Daten';
			return false;
		}
	}
	
	// *************************************
	// * Prueft die Daten vor dem Speichern 
	// * auf Gueltigkeit
	// *************************************
	function validate()
	{
		if(!is_numeric($this->lehrveranstaltung_id))
		{
			$this->errormsg = 'Lehrveranstaltung_id ist ungueltig';
			return false;
		}
		if($this->student_uid=='')
		{
			$this->errormsg = 'UID muss angegeben werden';
			return false;
		}
		if($this->studiensemester_kurzbz=='')
		{
			$this->errormsg = 'Studiensemester muss angegeben werden';
			return false;
		}
		if($this->note!='' && !is_numeric($this->note))
		{
			$this->errormsg = 'Note ist ungueltig';
			return false;
		}
		if($this->freigabedatum!='' && !ereg("([0-9]{4})-([0-9]{2})-([0-9]{2})",$this->freigabedatum))
		{
			$this->errormsg = 'Uebernahmedatum ist ungueltig';
			return false;
		}
		if($this->benotungsdatum!='' && !ereg("([0-9]{4})-([0-9]{2})-([0-9]{2})",$this->benotungsdatum))
		{
			$this->errormsg = 'Benotungsdatum ist ungueltig';
			return false;
		}
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
	
	// *******************************************************************************
	// * Speichert den aktuellen Datensatz in die Datenbank	 
	// * Wenn $neu auf true gesetzt ist wird ein neuer Datensatz angelegt
	// * andernfalls wird der Datensatz mit der ID in $betriebsmittel_id aktualisiert
	// * @return true wenn ok, false im Fehlerfall
	// *******************************************************************************
	function save($new=null)
	{
		if($new==null)
			$new=$this->new;
		
		if(!$this->validate())
			return false;
		
		if($new)
		{
			//Neuen Datensatz einfuegen					
			$qry='INSERT INTO campus.tbl_lvgesamtnote (lehrveranstaltung_id, student_uid, studiensemester_kurzbz, mitarbeiter_uid, note, freigabedatum, freigabevon_uid, benotungsdatum, bemerkung, updateamum, updatevon, insertamum, insertvon) VALUES('.
			     $this->addslashes($this->lehrveranstaltung_id).', '.
			     $this->addslashes($this->student_uid).', '.
			     $this->addslashes($this->studiensemester_kurzbz).', '.
			     $this->addslashes($this->mitarbeiter_uid).', '.
			     $this->addslashes($this->note).', '.
			     $this->addslashes($this->freigabedatum).', '.
			     $this->addslashes($this->freigabevon_uid).', '.
			     $this->addslashes($this->benotungsdatum).', '.
			     $this->addslashes($this->bemerkung).', '.
			     $this->addslashes($this->updateamum).', '.
			     $this->addslashes($this->updatevon).', '.
			     $this->addslashes($this->insertamum).', '.
			     $this->addslashes($this->insertvon).');';
		}
		else
		{			
			$qry='UPDATE campus.tbl_lvgesamtnote SET '.
				'note='.$this->addslashes($this->note).', '. 
				'freigabedatum='.$this->addslashes($this->freigabedatum).', '.
				'freigabevon_uid='.$this->addslashes($this->freigabevon_uid).', '.
				'benotungsdatum='.$this->addslashes($this->benotungsdatum).', '.
				'bemerkung='.$this->addslashes($this->bemerkung).', '.
				'mitarbeiter_uid='.$this->addslashes($this->mitarbeiter_uid).', '.
		     	'updateamum= '.$this->addslashes($this->updateamum).', '.
		     	'updatevon='.$this->addslashes($this->updatevon).' '.
				'WHERE lehrveranstaltung_id='.$this->addslashes($this->lehrveranstaltung_id).' '.
				'AND student_uid='.$this->addslashes($this->student_uid).' '.
				'AND studiensemester_kurzbz='.$this->addslashes($this->studiensemester_kurzbz).';';
		}
		
		if(pg_query($this->conn, $qry))
		{
			return true;
		}
		else 
		{
			$this->errormsg = "Fehler beim Speichern des Datensatzes: ".pg_last_error($this->conn);
			return false;
		}
	}
	
	// ********************************************************
	// * Loescht den Datenensatz mit der ID die uebergeben wird
	// * @param $lehrveranstaltung_id
	// *        $student_uid
	// *        $studiensemester_kurzbz
	// * @return true wenn ok, false im Fehlerfall
	// ********************************************************
	function delete($lehrveranstaltung_id, $student_uid, $studiensemester_kurzbz)
	{
		$qry = "DELETE FROM campus.tbl_lvgesamtnote WHERE 
				lehrveranstaltung_id='".addslashes($lehrveranstaltung_id)."' AND
				student_uid='".addslashes($student_uid)."' AND
				studiensemester_kurzbz='".addslashes($studiensemester_kurzbz)."'";
		
		if(pg_query($this->conn, $qry))
			return true;
		else 
		{
			$this->errormsg = 'Fehler beim loeschen der Daten';
			return false;
		}
	}
	
	// *********************************************
	// * Laed die Noten
	// * @param $lehrveranstaltung_id
	// *        $student_uid
	// *        $studiensemester_kurzbz
	// * @return true wenn ok, false wenn Fehler
	// *********************************************
	function getLvGesamtNoten($lehrveranstaltung_id, $student_uid, $studiensemester_kurzbz)
	{
		$qry = "SELECT 
					tbl_lvgesamtnote.*,
					tbl_note.bezeichnung as note_bezeichnung,
					tbl_lehrveranstaltung.bezeichnung as lehrveranstaltung_bezeichnung
				FROM 
					campus.tbl_lvgesamtnote,
					lehre.tbl_note,
					lehre.tbl_lehrveranstaltung			
				WHERE
					tbl_lvgesamtnote.note=tbl_note.note AND
					tbl_lvgesamtnote.lehrveranstaltung_id=tbl_lehrveranstaltung.lehrveranstaltung_id AND
					tbl_lvgesamtnote.studiensemester_kurzbz='".addslashes($studiensemester_kurzbz)."' AND
					tbl_lvgesamtnote.freigabedatum<now()";
		
		if($lehrveranstaltung_id!=null)
			$qry.=" AND tbl_lvgesamtnote.lehrveranstaltung_id='".addslashes($lehrveranstaltung_id)."'";
		if($student_uid!=null)
			$qry.=" AND tbl_lvgesamtnote.student_uid='".addslashes($student_uid)."'";
		
		if($result = pg_query($this->conn, $qry))
		{
			while($row = pg_fetch_object($result))
			{
				$obj = new lvgesamtnote($this->conn, null, null, null, null);
				
				$obj->lehrveranstaltung_id = $row->lehrveranstaltung_id;
				$obj->student_uid = $row->student_uid;
				$obj->mitarbeiter_uid = $row->mitarbeiter_uid;
				$obj->studiensemester_kurzbz = $row->studiensemester_kurzbz;
				$obj->note = $row->note;
				$obj->freigabedatum = $row->freigabedatum;
				$obj->freigabevon_uid = $row->freigabevon_uid;
				$obj->benotungsdatum = $row->benotungsdatum;
				$obj->updateamum = $row->updateamum;
				$obj->udpatevon = $row->updatevon;
				$obj->insertamum = $row->insertamum;
				$obj->insertvon = $row->insertvon;
				$obj->note_bezeichnung = $row->note_bezeichnung;
				$obj->lehrveranstaltung_bezeichnung = $row->lehrveranstaltung_bezeichnung;
				$obj->bemerkung = $row->bemerkung;
				$this->result[] = $obj;
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
