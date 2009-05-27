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

class zeugnisnote
{
	var $conn;     			// resource DB-Handle
	var $new;       		// boolean
	var $errormsg;  		// string
	var $result=array();

	//Tabellenspalten
	var $lehrveranstaltung_id;		// integer
	var $student_uid;				// varchar(16)
	var $studiensemester_kurzbz;	// varchar(16)
	var $note;						// smalint
	var $uebernahmedatum;			// date
	var $benotungsdatum;			// date
	var $updateamum;				// timestamp
	var $updatevon;					// varchar(16)
	var $insertamum;				// timestamp
	var $insertvon;					// varchar(16)
	var $ext_id;					// bigint
	var $bemerkung;					// text

	var $lehrveranstaltung_bezeichung;
	var $note_bezeichnung;
	var $zeugnis;
	var $lv_lehrform_kurzbz;

	// *********************************************************************
	// * Konstruktor
	// * @param $conn      Connection
	// *        $lehrveranstaltung_id
	// *        $student_uid
	// *        $studiensemester_kurzbz
	// *********************************************************************
	function zeugnisnote($conn, $lehrveranstaltung_id=null, $student_uid=null, $studiensemester_kurzbz=null , $unicode=false)
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
	// * Laedt eine Zeugnisnote
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

		$qry = "SELECT * FROM lehre.tbl_zeugnisnote WHERE
				lehrveranstaltung_id='$lehrveranstaltung_id' AND
				student_uid='".addslashes($student_uid)."' AND
				studiensemester_kurzbz='".addslashes($studiensemester_kurzbz)."'";

		if($result = pg_query($this->conn, $qry))
		{
			if($row = pg_fetch_object($result))
			{
				$this->lehrveranstaltung_id = $row->lehrveranstaltung_id;
				$this->student_uid = $row->student_uid;
				$this->studiensemester_kurzbz = $row->studiensemester_kurzbz;
				$this->note = $row->note;
				$this->uebernahmedatum = $row->uebernahmedatum;
				$this->benotungsdatum = $row->benotungsdatum;
				$this->updateamum = $row->updateamum;
				$this->updatevon = $row->updatevon;
				$this->insertamum = $row->insertamum;
				$this->insertvon = $row->insertvon;
				$this->ext_id = $row->ext_id;
				$this->bemerkung = $row->bemerkung;
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
		if($this->uebernahmedatum!='' && !ereg("([0-9]{4})-([0-9]{2})-([0-9]{2})",$this->uebernahmedatum))
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
			$qry='INSERT INTO lehre.tbl_zeugnisnote (lehrveranstaltung_id, student_uid, studiensemester_kurzbz, note, uebernahmedatum, benotungsdatum, bemerkung,
				  updateamum, updatevon, insertamum, insertvon, ext_id) VALUES('.
			     $this->addslashes($this->lehrveranstaltung_id).', '.
			     $this->addslashes($this->student_uid).', '.
			     $this->addslashes($this->studiensemester_kurzbz).', '.
			     $this->addslashes($this->note).', '.
			     $this->addslashes($this->uebernahmedatum).', '.
			     $this->addslashes($this->benotungsdatum).', '.
			     $this->addslashes($this->bemerkung).', '.
			     $this->addslashes($this->updateamum).', '.
			     $this->addslashes($this->updatevon).', '.
			     $this->addslashes($this->insertamum).', '.
			     $this->addslashes($this->insertvon).', '.
			     $this->addslashes($this->ext_id).');';
		}
		else
		{
			$qry='UPDATE lehre.tbl_zeugnisnote SET '.
				'note='.$this->addslashes($this->note).', '.
				'uebernahmedatum='.$this->addslashes($this->uebernahmedatum).', '.
				'benotungsdatum='.$this->addslashes($this->benotungsdatum).', '.
				'bemerkung='.$this->addslashes($this->bemerkung).', '.
		     	'updateamum= '.$this->addslashes($this->updateamum).', '.
		     	'updatevon='.$this->addslashes($this->updatevon).' '.
				'WHERE lehrveranstaltung_id='.$this->addslashes($this->lehrveranstaltung_id).' '.
				'AND student_uid='.$this->addslashes($this->student_uid).' '.
				'AND studiensemester_kurzbz='.$this->addslashes($this->studiensemester_kurzbz).';';
		}

		//if(pg_send_query($this->conn, $qry))
		if(pg_query($this->conn, $qry))		
		{
			return true;
		}
		else
		{
			//echo $qry;
			$this->errormsg='Fehler beim Speichern der Zeugnisnote';
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
		$qry = "DELETE FROM lehre.tbl_zeugnisnote WHERE
				lehrveranstaltung_id='".addslashes($lehrveranstaltung_id)."' AND
				student_uid='".addslashes($student_uid)."' AND
				studiensemester_kurzbz='".addslashes($studiensemester_kurzbz)."'";

		if(pg_query($this->conn, $qry))
			return true;
		else
		{
			$this->errormsg = 'Fehler beim Loeschen der Daten';
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
	function getZeugnisnoten($lehrveranstaltung_id, $student_uid, $studiensemester_kurzbz)
	{
		$where='';
		if($lehrveranstaltung_id!=null)
			$where.=" AND vw_student_lehrveranstaltung.lehrveranstaltung_id='".addslashes($lehrveranstaltung_id)."'";
		if($student_uid!=null)
			$where.=" AND uid='".addslashes($student_uid)."'";
		if($studiensemester_kurzbz!=null)
			$where.=" AND vw_student_lehrveranstaltung.studiensemester_kurzbz='".addslashes($studiensemester_kurzbz)."'";
		$where2='';
		if($lehrveranstaltung_id!=null)
			$where2.=" AND lehrveranstaltung_id='".addslashes($lehrveranstaltung_id)."'";
		if($student_uid!=null)
			$where2.=" AND student_uid='".addslashes($student_uid)."'";
		if($studiensemester_kurzbz!=null)
			$where2.=" AND studiensemester_kurzbz='".addslashes($studiensemester_kurzbz)."'";

		$qry = "SELECT vw_student_lehrveranstaltung.lehrveranstaltung_id, uid,
					   vw_student_lehrveranstaltung.studiensemester_kurzbz, note, uebernahmedatum, benotungsdatum,
					   vw_student_lehrveranstaltung.ects, vw_student_lehrveranstaltung.semesterstunden,
					   tbl_zeugnisnote.updateamum, tbl_zeugnisnote.updatevon, tbl_zeugnisnote.insertamum,
					   tbl_zeugnisnote.insertvon, tbl_zeugnisnote.ext_id,
					   vw_student_lehrveranstaltung.bezeichnung as lehrveranstaltung_bezeichnung,
					   vw_student_lehrveranstaltung.bezeichnung_english as lehrveranstaltung_bezeichnung_english,
					   tbl_note.bezeichnung as note_bezeichnung,
					   tbl_zeugnisnote.bemerkung as bemerkung,
					   vw_student_lehrveranstaltung.sort,
					   vw_student_lehrveranstaltung.zeugnis,
					   vw_student_lehrveranstaltung.studiengang_kz,
					   vw_student_lehrveranstaltung.lv_lehrform_kurzbz
				FROM
				(
					campus.vw_student_lehrveranstaltung LEFT JOIN lehre.tbl_zeugnisnote
						ON(uid=student_uid
						   AND vw_student_lehrveranstaltung.studiensemester_kurzbz=tbl_zeugnisnote.studiensemester_kurzbz
						   AND vw_student_lehrveranstaltung.lehrveranstaltung_id=tbl_zeugnisnote.lehrveranstaltung_id
						  )
				) LEFT JOIN lehre.tbl_note USING(note)
				WHERE true $where
				UNION
				SELECT lehre.tbl_lehrveranstaltung.lehrveranstaltung_id,student_uid AS uid,studiensemester_kurzbz, note,
					uebernahmedatum, benotungsdatum,lehre.tbl_lehrveranstaltung.ects,lehre.tbl_lehrveranstaltung.semesterstunden, tbl_zeugnisnote.updateamum, tbl_zeugnisnote.updatevon, tbl_zeugnisnote.insertamum,
					tbl_zeugnisnote.insertvon, tbl_zeugnisnote.ext_id, lehre.tbl_lehrveranstaltung.bezeichnung as lehrveranstaltung_bezeichnung, lehre.tbl_lehrveranstaltung.bezeichnung_english as lehrveranstaltung_bezeichnung_english,
					tbl_note.bezeichnung as note_bezeichnung, tbl_zeugnisnote.bemerkung as bemerkung, tbl_lehrveranstaltung.sort, tbl_lehrveranstaltung.zeugnis, tbl_lehrveranstaltung.studiengang_kz,
					tbl_lehrveranstaltung.lehrform_kurzbz as lv_lehrform_kurzbz
				FROM
					lehre.tbl_zeugnisnote
					JOIN lehre.tbl_lehrveranstaltung USING (lehrveranstaltung_id)
					JOIN lehre.tbl_note USING(note)
				WHERE true $where2
				ORDER BY sort";
		//echo $qry;
		if($result = pg_query($this->conn, $qry))
		{
			while($row = pg_fetch_object($result))
			{
				$obj = new zeugnisnote($this->conn, null, null, null, null);

				$obj->lehrveranstaltung_id = $row->lehrveranstaltung_id;
				$obj->student_uid = $row->uid;
				$obj->studiensemester_kurzbz = $row->studiensemester_kurzbz;
				$obj->note = $row->note;
				$obj->uebernahmedatum = $row->uebernahmedatum;
				$obj->benotungsdatum = $row->benotungsdatum;
				$obj->updateamum = $row->updateamum;
				$obj->udpatevon = $row->updatevon;
				$obj->insertamum = $row->insertamum;
				$obj->insertvon = $row->insertvon;
				$obj->ext_id = $row->ext_id;
				$obj->note_bezeichnung = $row->note_bezeichnung;
				$obj->lehrveranstaltung_bezeichnung = $row->lehrveranstaltung_bezeichnung;
				$obj->lehrveranstaltung_bezeichnung_english = $row->lehrveranstaltung_bezeichnung_english;
				$obj->bemerkung = $row->bemerkung;
				$obj->semesterstunden = $row->semesterstunden;
				$obj->ects = $row->ects;
				$obj->sort = $row->sort;
				$obj->studiengang_kz = $row->studiengang_kz;
				$obj->zeugnis = ($row->zeugnis=='t'?true:false);
				$obj->lv_lehrform_kurzbz = $row->lv_lehrform_kurzbz;

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