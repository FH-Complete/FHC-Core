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
 * Klasse LVGesamtnote
 * @create 2007-06-06
 */
require_once(dirname(__FILE__).'/basis_db.class.php');

class lvgesamtnote extends basis_db 
{
	public $new;       		// boolean
	public $result=array();					
		
	//Tabellenspalten
	public $lehrveranstaltung_id;		// integer
	public $student_uid;				// varchar(16)
	public $mitarbeiter_uid;			// varchar(16)
	public $studiensemester_kurzbz;		// varchar(16)
	public $note;						// smalint
	public $freigabedatum;				// date
	public $benotungsdatum;				// date
	public $updateamum;					// timestamp
	public $updatevon;					// varchar(16)
	public $insertamum;					// timestamp
	public $insertvon;					// varchar(16)
	public $bemerkung;					// text
	public $freigabevon_uid;			// varchar(16)
	public $punkte;						// numeric(8,4)
	
	public $lehrveranstaltung_bezeichung;
	public $note_bezeichnung;
	
	/**
	 * Konstruktor
	 * @param $lehrveranstaltung_id
	 *        $student_uid
	 *        $studiensemester_kurzbz
	 */
	public function __construct($lehrveranstaltung_id=null, $student_uid=null, $studiensemester_kurzbz=null)
	{
		parent::__construct();
		
		if($lehrveranstaltung_id!=null && $student_uid!=null && $studiensemester_kurzbz!=null)
			$this->load($lehrveranstaltung_id, $student_uid, $studiensemester_kurzbz);
	}
	
	/**
	 * Laedt eine LVGesamtNote
	 * @param $lehrveranstaltung_id
	 * @param $student_uid
	 * @param $studiensemester_kurzbz
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function load($lehrveranstaltung_id, $student_uid, $studiensemester_kurzbz)
	{
		if(!is_numeric($lehrveranstaltung_id))
		{
			$this->errormsg = 'Lehrveranstaltung_id ist ungueltig';
			return false;
		}
		
		$qry = "SELECT * FROM campus.tbl_lvgesamtnote WHERE 
				lehrveranstaltung_id=".$this->db_add_param($lehrveranstaltung_id, FHC_INTEGER)." AND 
				student_uid=".$this->db_add_param($student_uid)." AND
				studiensemester_kurzbz=".$this->db_add_param($studiensemester_kurzbz).";";
		
		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
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
				$this->punkte = $row->punkte;
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
	
	/**
	 * Prueft die Daten vor dem Speichern 
	 * auf Gueltigkeit
	 */
	protected function validate()
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
			$this->errormsg = 'Note ist ungueltig: '.$this->note;
			return false;
		}
		if($this->freigabedatum!='' && !mb_ereg("([0-9]{4})-([0-9]{2})-([0-9]{2})",$this->freigabedatum))
		{
			$this->errormsg = 'Uebernahmedatum ist ungueltig';
			return false;
		}
		if($this->benotungsdatum!='' && !mb_ereg("([0-9]{4})-([0-9]{2})-([0-9]{2})",$this->benotungsdatum))
		{
			$this->errormsg = 'Benotungsdatum ist ungueltig';
			return false;
		}
		return true;
	}
		
	/**
	 * Speichert den aktuellen Datensatz in die Datenbank	 
	 * Wenn $neu auf true gesetzt ist wird ein neuer Datensatz angelegt
	 * andernfalls wird der Datensatz mit der ID in $betriebsmittel_id aktualisiert
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function save($new=null)
	{
		if($new==null)
			$new=$this->new;
		
		if(!$this->validate())
			return false;
		
		if($new)
		{
			//Neuen Datensatz einfuegen					
			$qry='INSERT INTO campus.tbl_lvgesamtnote (lehrveranstaltung_id, student_uid, studiensemester_kurzbz, 
				mitarbeiter_uid, note, freigabedatum, freigabevon_uid, benotungsdatum, bemerkung, updateamum, 
				updatevon, insertamum, insertvon, punkte) VALUES('.
			     $this->db_add_param($this->lehrveranstaltung_id, FHC_INTEGER).', '.
			     $this->db_add_param($this->student_uid).', '.
			     $this->db_add_param($this->studiensemester_kurzbz).', '.
			     $this->db_add_param($this->mitarbeiter_uid).', '.
			     $this->db_add_param($this->note, FHC_INTEGER).', '.
			     $this->db_add_param($this->freigabedatum).', '.
			     $this->db_add_param($this->freigabevon_uid).', '.
			     $this->db_add_param($this->benotungsdatum).', '.
			     $this->db_add_param($this->bemerkung).', '.
			     $this->db_add_param($this->updateamum).', '.
			     $this->db_add_param($this->updatevon).', '.
			     $this->db_add_param($this->insertamum).', '.
			     $this->db_add_param($this->insertvon).','.
			     $this->db_add_param($this->punkte).');';
		}
		else
		{			
			$qry='UPDATE campus.tbl_lvgesamtnote SET '.
				'note='.$this->db_add_param($this->note, FHC_INTEGER).', '.
				'punkte='.$this->db_add_param($this->punkte).','. 
				'freigabedatum='.$this->db_add_param($this->freigabedatum).', '.
				'freigabevon_uid='.$this->db_add_param($this->freigabevon_uid).', '.
				'benotungsdatum='.$this->db_add_param($this->benotungsdatum).', '.
				'bemerkung='.$this->db_add_param($this->bemerkung).', '.
				'mitarbeiter_uid='.$this->db_add_param($this->mitarbeiter_uid).', '.
		     	'updateamum= '.$this->db_add_param($this->updateamum).', '.
		     	'updatevon='.$this->db_add_param($this->updatevon).' '.
				'WHERE lehrveranstaltung_id='.$this->db_add_param($this->lehrveranstaltung_id, FHC_INTEGER).' '.
				'AND student_uid='.$this->db_add_param($this->student_uid).' '.
				'AND studiensemester_kurzbz='.$this->db_add_param($this->studiensemester_kurzbz).';';
		}
		
		if($this->db_query($qry))
		{
			return true;
		}
		else 
		{
			$this->errormsg = "Fehler beim Speichern des Datensatzes: ".$this->db_last_error();
			return false;
		}
	}
	
	/**
	 * Loescht den Datenensatz mit der ID die uebergeben wird
	 * @param $lehrveranstaltung_id
	 * @param $student_uid
	 * @param $studiensemester_kurzbz
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function delete($lehrveranstaltung_id, $student_uid, $studiensemester_kurzbz)
	{
		$qry = "DELETE FROM campus.tbl_lvgesamtnote WHERE 
				lehrveranstaltung_id=".$this->db_add_param($lehrveranstaltung_id, FHC_INTEGER)." AND
				student_uid=".$this->db_add_param($student_uid)." AND
				studiensemester_kurzbz=".$this->db_add_param($studiensemester_kurzbz).";";
		
		if($this->db_query($qry))
			return true;
		else 
		{
			$this->errormsg = 'Fehler beim Loeschen der Daten';
			return false;
		}
	}
	
	/**
	 * Laedt die Noten
	 * @param $lehrveranstaltung_id
	 *        $student_uid
	 *        $studiensemester_kurzbz
	 * @return true wenn ok, false wenn Fehler
	 */
	public function getLvGesamtNoten($lehrveranstaltung_id, $student_uid, $studiensemester_kurzbz)
	{
		$qry = "SELECT 
					tbl_lvgesamtnote.*,
					tbl_note.bezeichnung as note_bezeichnung,
					tbl_lehrveranstaltung.bezeichnung as lehrveranstaltung_bezeichnung,
					tbl_lehrveranstaltung.studiengang_kz
				FROM 
					campus.tbl_lvgesamtnote,
					lehre.tbl_note,
					lehre.tbl_lehrveranstaltung			
				WHERE
					tbl_lvgesamtnote.note=tbl_note.note AND
					tbl_lvgesamtnote.lehrveranstaltung_id=tbl_lehrveranstaltung.lehrveranstaltung_id AND
					tbl_lvgesamtnote.studiensemester_kurzbz=".$this->db_add_param($studiensemester_kurzbz)." AND
					tbl_lvgesamtnote.freigabedatum<now()";
		
		if($lehrveranstaltung_id!=null)
			$qry.=" AND tbl_lvgesamtnote.lehrveranstaltung_id=".$this->db_add_param($lehrveranstaltung_id, FHC_INTEGER);
		if($student_uid!=null)
			$qry.=" AND tbl_lvgesamtnote.student_uid=".$this->db_add_param($student_uid);
        
        $qry.=';';
		
		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$obj = new lvgesamtnote();
				
				$obj->lehrveranstaltung_id = $row->lehrveranstaltung_id;
				$obj->student_uid = $row->student_uid;
				$obj->mitarbeiter_uid = $row->mitarbeiter_uid;
				$obj->studiensemester_kurzbz = $row->studiensemester_kurzbz;
				$obj->note = $row->note;
				$obj->punkte = $row->punkte;
				$obj->freigabedatum = $row->freigabedatum;
				$obj->freigabevon_uid = $row->freigabevon_uid;
				$obj->benotungsdatum = $row->benotungsdatum;
				$obj->updateamum = $row->updateamum;
				$obj->updatevon = $row->updatevon;
				$obj->insertamum = $row->insertamum;
				$obj->insertvon = $row->insertvon;
				$obj->note_bezeichnung = $row->note_bezeichnung;
				$obj->lehrveranstaltung_bezeichnung = $row->lehrveranstaltung_bezeichnung;
				$obj->bemerkung = $row->bemerkung;
				$obj->studiengang_kz = $row->studiengang_kz;
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
