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
require_once(dirname(__FILE__).'/basis_db.class.php');

class zeugnisnote extends basis_db
{
	public $new;       		// boolean
	public $result=array();

	//Tabellenspalten
	public $lehrveranstaltung_id;		/// serial
	public $student_uid;				// varchar(16)
	public $studiensemester_kurzbz;		// varchar(16)
	public $note;						// smalint
	public $uebernahmedatum;			// date
	public $benotungsdatum;				// date
	public $updateamum;					// timestamp
	public $updatevon;					// varchar(16)
	public $insertamum;					// timestamp
	public $insertvon;					// varchar(16)
	public $ext_id;						// bigint
	public $bemerkung;					// text
	public $punkte;						// numeric(8,4)

	public $lehrveranstaltung_bezeichung;
	public $note_bezeichnung;
	public $zeugnis;
	public $lv_lehrform_kurzbz;

	/**
	 * Konstruktor
	 * Laedt optional eine Zeugnisnote
	 *
	 * @param $lehrveranstaltung_id
	 * @param $student_uid
	 * @param $studiensemester_kurzbz
	 */
	public function __construct($lehrveranstaltung_id=null, $student_uid=null, $studiensemester_kurzbz=null)
	{
		parent::__construct();

		if($lehrveranstaltung_id!=null && $student_uid!=null && $studiensemester_kurzbz!=null)
			$this->load($lehrveranstaltung_id, $student_uid, $studiensemester_kurzbz);
	}

	/**
	 * Laedt eine Zeugnisnote
	 *
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

		$qry = "SELECT
					*
				FROM
					lehre.tbl_zeugnisnote
				WHERE
					lehrveranstaltung_id=".$this->db_add_param($lehrveranstaltung_id, FHC_INTEGER)."
					AND	student_uid=".$this->db_add_param($student_uid)."
					AND	studiensemester_kurzbz=".$this->db_add_param($studiensemester_kurzbz);

		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
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
			$this->errormsg = 'Note ist ungueltig';
			return false;
		}
		if($this->punkte!='' && !is_numeric($this->punkte))
		{
			$this->errormsg = 'Punkte sind ungueltig';
			return false;
		}
		if($this->uebernahmedatum!='' && !mb_ereg("([0-9]{4})-([0-9]{2})-([0-9]{2})",$this->uebernahmedatum))
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
			$qry='INSERT INTO lehre.tbl_zeugnisnote (lehrveranstaltung_id, student_uid,
				studiensemester_kurzbz, note, uebernahmedatum, benotungsdatum, bemerkung,
				updateamum, updatevon, insertamum, insertvon, punkte) VALUES('.
			     $this->db_add_param($this->lehrveranstaltung_id, FHC_INTEGER).', '.
			     $this->db_add_param($this->student_uid).', '.
			     $this->db_add_param($this->studiensemester_kurzbz).', '.
			     $this->db_add_param($this->note).', '.
			     $this->db_add_param($this->uebernahmedatum).', '.
			     $this->db_add_param($this->benotungsdatum).', '.
			     $this->db_add_param($this->bemerkung).', '.
			     $this->db_add_param($this->updateamum).', '.
			     $this->db_add_param($this->updatevon).', '.
			     $this->db_add_param($this->insertamum).', '.
			     $this->db_add_param($this->insertvon).', '.
			     $this->db_add_param($this->punkte).');';
		}
		else
		{
			$qry='UPDATE lehre.tbl_zeugnisnote SET '.
				'note='.$this->db_add_param($this->note).', '.
				'punkte='.$this->db_add_param($this->punkte).','.
				'uebernahmedatum='.$this->db_add_param($this->uebernahmedatum).', '.
				'benotungsdatum='.$this->db_add_param($this->benotungsdatum).', '.
				'bemerkung='.$this->db_add_param($this->bemerkung).', '.
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
			$this->errormsg='Fehler beim Speichern der Zeugnisnote';
			return false;
		}
	}

	/**
	 * Loescht den Datenensatz mit der ID die uebergeben wird
	 * @param $lehrveranstaltung_id
	 *        $student_uid
	 *        $studiensemester_kurzbz
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function delete($lehrveranstaltung_id, $student_uid, $studiensemester_kurzbz)
	{
		$qry = "DELETE FROM lehre.tbl_zeugnisnote WHERE
				lehrveranstaltung_id=".$this->db_add_param($lehrveranstaltung_id, FHC_INTEGER, false)." AND
				student_uid=".$this->db_add_param($student_uid)." AND
				studiensemester_kurzbz=".$this->db_add_param($studiensemester_kurzbz);

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
	public function getZeugnisnoten($lehrveranstaltung_id, $student_uid, $studiensemester_kurzbz, $nichtAnzeigen = null)
	{
		$where='';
		if($lehrveranstaltung_id!=null)
			$where.=" AND vw_student_lehrveranstaltung.lehrveranstaltung_id=".$this->db_add_param($lehrveranstaltung_id);
		if($student_uid!=null)
			$where.=" AND uid=".$this->db_add_param($student_uid);
		if($studiensemester_kurzbz!=null)
			$where.=" AND vw_student_lehrveranstaltung.studiensemester_kurzbz=".$this->db_add_param($studiensemester_kurzbz);
		if ($nichtAnzeigen != null)
			$where.=" AND tbl_note.anmerkung NOT IN (".$this->db_implode4SQL($nichtAnzeigen).")";
		$where2='';
		if($lehrveranstaltung_id!=null)
			$where2.=" AND lehrveranstaltung_id=".$this->db_add_param($lehrveranstaltung_id, FHC_INTEGER);
		if($student_uid!=null)
			$where2.=" AND student_uid=".$this->db_add_param($student_uid);
		if($studiensemester_kurzbz!=null)
			$where2.=" AND studiensemester_kurzbz=".$this->db_add_param($studiensemester_kurzbz);
		if ($nichtAnzeigen != null)
			$where2.=" AND tbl_note.anmerkung NOT IN (".$this->db_implode4SQL($nichtAnzeigen).")";

		$qry = "SELECT vw_student_lehrveranstaltung.lehrveranstaltung_id, uid,
					   vw_student_lehrveranstaltung.studiensemester_kurzbz, note, punkte, uebernahmedatum, benotungsdatum,
					   vw_student_lehrveranstaltung.ects, vw_student_lehrveranstaltung.semesterstunden,
					   tbl_zeugnisnote.updateamum, tbl_zeugnisnote.updatevon, tbl_zeugnisnote.insertamum,
					   tbl_zeugnisnote.insertvon, tbl_zeugnisnote.ext_id,
					   vw_student_lehrveranstaltung.bezeichnung as lehrveranstaltung_bezeichnung,
					   vw_student_lehrveranstaltung.bezeichnung_english as lehrveranstaltung_bezeichnung_english,
					   tbl_note.bezeichnung as note_bezeichnung,
					   tbl_note.positiv as note_positiv,
					   tbl_zeugnisnote.bemerkung as bemerkung,
					   vw_student_lehrveranstaltung.sort,
					   vw_student_lehrveranstaltung.zeugnis,
					   vw_student_lehrveranstaltung.studiengang_kz,
					   vw_student_lehrveranstaltung.lv_lehrform_kurzbz,
					   tbl_lehrveranstaltung.sws
				FROM
				(
					campus.vw_student_lehrveranstaltung LEFT JOIN lehre.tbl_zeugnisnote
						ON(uid=student_uid
						   AND vw_student_lehrveranstaltung.studiensemester_kurzbz=tbl_zeugnisnote.studiensemester_kurzbz
						   AND vw_student_lehrveranstaltung.lehrveranstaltung_id=tbl_zeugnisnote.lehrveranstaltung_id
						  )
				) LEFT JOIN lehre.tbl_note USING(note)
				JOIN lehre.tbl_lehrveranstaltung ON(vw_student_lehrveranstaltung.lehrveranstaltung_id=tbl_lehrveranstaltung.lehrveranstaltung_id)
				WHERE true $where
				UNION
				SELECT lehre.tbl_lehrveranstaltung.lehrveranstaltung_id,student_uid AS uid,studiensemester_kurzbz, note, punkte,
					uebernahmedatum, benotungsdatum,lehre.tbl_lehrveranstaltung.ects,lehre.tbl_lehrveranstaltung.semesterstunden, tbl_zeugnisnote.updateamum, tbl_zeugnisnote.updatevon, tbl_zeugnisnote.insertamum,
					tbl_zeugnisnote.insertvon, tbl_zeugnisnote.ext_id, lehre.tbl_lehrveranstaltung.bezeichnung as lehrveranstaltung_bezeichnung, lehre.tbl_lehrveranstaltung.bezeichnung_english as lehrveranstaltung_bezeichnung_english,
					tbl_note.bezeichnung as note_bezeichnung, tbl_note.positiv as note_positiv, tbl_zeugnisnote.bemerkung as bemerkung, tbl_lehrveranstaltung.sort, tbl_lehrveranstaltung.zeugnis, tbl_lehrveranstaltung.studiengang_kz,
					tbl_lehrveranstaltung.lehrform_kurzbz as lv_lehrform_kurzbz, tbl_lehrveranstaltung.sws
				FROM
					lehre.tbl_zeugnisnote
					JOIN lehre.tbl_lehrveranstaltung USING (lehrveranstaltung_id)
					JOIN lehre.tbl_note USING(note)
				WHERE true $where2
				ORDER BY sort";

		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$obj = new zeugnisnote();

				$obj->lehrveranstaltung_id = $row->lehrveranstaltung_id;
				$obj->student_uid = $row->uid;
				$obj->studiensemester_kurzbz = $row->studiensemester_kurzbz;
				$obj->note = $row->note;
				$obj->punkte = $row->punkte;
				$obj->uebernahmedatum = $row->uebernahmedatum;
				$obj->benotungsdatum = $row->benotungsdatum;
				$obj->updateamum = $row->updateamum;
				$obj->updatevon = $row->updatevon;
				$obj->insertamum = $row->insertamum;
				$obj->insertvon = $row->insertvon;
				$obj->ext_id = $row->ext_id;
				$obj->note_bezeichnung = $row->note_bezeichnung;
				$obj->note_positiv = $this->db_parse_bool($row->note_positiv);
				$obj->lehrveranstaltung_bezeichnung = $row->lehrveranstaltung_bezeichnung;
				$obj->lehrveranstaltung_bezeichnung_english = $row->lehrveranstaltung_bezeichnung_english;
				$obj->bemerkung = $row->bemerkung;
				$obj->semesterstunden = $row->semesterstunden;
				$obj->ects = $row->ects;
				$obj->sort = $row->sort;
				$obj->studiengang_kz = $row->studiengang_kz;
				$obj->zeugnis = $this->db_parse_bool($row->zeugnis);
				$obj->lv_lehrform_kurzbz = $row->lv_lehrform_kurzbz;
				$obj->sws = $row->sws;

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

	/**
	 * Laedt die Noten Studienjahr
	 * @param $lehrveranstaltung_id
	 *        $student_uid
	 *        $studiensemester_kurzbz
	 * @return true wenn ok, false wenn Fehler
	 */
	public function getZeugnisnotenStudienplan($student_uid, $studiensemester_arr, $studienplan_id)
	{

		$stsem = $this->db_implode4SQL($studiensemester_arr);

		/*
		 * Alle Lehrveranstaltungen holen zu denen eine Note eingetragen ist und alle zu denen der Studierende zugeteilt ist.
		 * Danach wird im Studienplan gesucht und eventuell darbueberliegenden Module zusaetzlich geladen
		 */
		$qry = "
		WITH RECURSIVE data(lvid, studienplan_lehrveranstaltung_id, studienplan_lehrveranstaltung_id_parent) as
		(
			SELECT
			vw_student_lehrveranstaltung.lehrveranstaltung_id,
			tbl_studienplan_lehrveranstaltung.studienplan_lehrveranstaltung_id,
			tbl_studienplan_lehrveranstaltung.studienplan_lehrveranstaltung_id_parent
			FROM
			(
				campus.vw_student_lehrveranstaltung LEFT JOIN lehre.tbl_zeugnisnote
					ON(uid=student_uid
						AND vw_student_lehrveranstaltung.studiensemester_kurzbz=tbl_zeugnisnote.studiensemester_kurzbz
						AND vw_student_lehrveranstaltung.lehrveranstaltung_id=tbl_zeugnisnote.lehrveranstaltung_id
					)
			)
			LEFT JOIN lehre.tbl_note USING(note)
			LEFT JOIN lehre.tbl_studienplan_lehrveranstaltung ON(vw_student_lehrveranstaltung.lehrveranstaltung_id=tbl_studienplan_lehrveranstaltung.lehrveranstaltung_id)
			WHERE
				uid=".$this->db_add_param($student_uid)."
				AND vw_student_lehrveranstaltung.studiensemester_kurzbz IN(".$stsem.")
				AND tbl_studienplan_lehrveranstaltung.studienplan_id=".$this->db_add_param($studienplan_id, FHC_INTEGER)."
			UNION
			SELECT lehre.tbl_lehrveranstaltung.lehrveranstaltung_id,
				tbl_studienplan_lehrveranstaltung.studienplan_lehrveranstaltung_id,
				tbl_studienplan_lehrveranstaltung.studienplan_lehrveranstaltung_id_parent
			FROM
				lehre.tbl_zeugnisnote
				JOIN lehre.tbl_lehrveranstaltung USING (lehrveranstaltung_id)
				JOIN lehre.tbl_note USING(note)
				LEFT JOIN lehre.tbl_studienplan_lehrveranstaltung USING(lehrveranstaltung_id)
			WHERE
				student_uid=".$this->db_add_param($student_uid)."
				AND studiensemester_kurzbz IN(".$stsem.")
				AND tbl_studienplan_lehrveranstaltung.studienplan_id=".$this->db_add_param($studienplan_id, FHC_INTEGER)."

			UNION ALL
			SELECT stpllv.lehrveranstaltung_id, stpllv.studienplan_lehrveranstaltung_id, stpllv.studienplan_lehrveranstaltung_id_parent
			FROM lehre.tbl_studienplan_lehrveranstaltung stpllv, data
			WHERE stpllv.studienplan_lehrveranstaltung_id=data.studienplan_lehrveranstaltung_id_parent
		)
		SELECT
			tbl_studienplan_lehrveranstaltung.studienplan_lehrveranstaltung_id,
			tbl_studienplan_lehrveranstaltung.studienplan_lehrveranstaltung_id_parent, tbl_studienplan_lehrveranstaltung.semester,
			tbl_lehrveranstaltung.lehrveranstaltung_id,tbl_lehrveranstaltung.bezeichnung as lehrveranstaltung_bezeichnung, tbl_lehrveranstaltung.bezeichnung_english as lehrveranstaltung_bezeichnung_english,
			tbl_lehrveranstaltung.semesterstunden, tbl_lehrveranstaltung.ects, tbl_lehrveranstaltung.sort, tbl_lehrveranstaltung.studiengang_kz, tbl_lehrveranstaltung.zeugnis,
			tbl_lehrveranstaltung.lehrform_kurzbz as lv_lehrform_kurzbz,
			tbl_zeugnisnote.studiensemester_kurzbz, tbl_zeugnisnote.uebernahmedatum, tbl_zeugnisnote.benotungsdatum,
			tbl_zeugnisnote.note, tbl_zeugnisnote.updateamum, tbl_zeugnisnote.updatevon, tbl_zeugnisnote.insertamum, tbl_zeugnisnote.insertvon,
			tbl_note.bezeichnung as note_bezeichnung, tbl_zeugnisnote.bemerkung, tbl_lehrveranstaltung.lvnr, tbl_studienplan_lehrveranstaltung.sort as studienplan_lehrveranstaltung_sort
		FROM
			lehre.tbl_lehrveranstaltung
			LEFT JOIN lehre.tbl_zeugnisnote ON(tbl_lehrveranstaltung.lehrveranstaltung_id=tbl_zeugnisnote.lehrveranstaltung_id AND tbl_zeugnisnote.student_uid=".$this->db_add_param($student_uid)." AND tbl_zeugnisnote.studiensemester_kurzbz IN(".$stsem."))
			LEFT JOIN lehre.tbl_studienplan_lehrveranstaltung ON(tbl_lehrveranstaltung.lehrveranstaltung_id=tbl_studienplan_lehrveranstaltung.lehrveranstaltung_id AND tbl_studienplan_lehrveranstaltung.studienplan_id=".$this->db_add_param($studienplan_id).")
			LEFT JOIN lehre.tbl_note USING(note)
		WHERE
			(tbl_zeugnisnote.studiensemester_kurzbz IN(".$stsem.") OR tbl_zeugnisnote.studiensemester_kurzbz is null)
			AND tbl_lehrveranstaltung.lehrveranstaltung_id in(SELECT lvid FROM data)
		ORDER BY studienplan_lehrveranstaltung_id_parent desc, studienplan_lehrveranstaltung_id
		";

		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$obj = new zeugnisnote();

				$obj->lehrveranstaltung_id = $row->lehrveranstaltung_id;
				$obj->student_uid = $student_uid;
				$obj->studiensemester_kurzbz = $row->studiensemester_kurzbz;
				$obj->note = $row->note;
				$obj->uebernahmedatum = $row->uebernahmedatum;
				$obj->benotungsdatum = $row->benotungsdatum;
				$obj->updateamum = $row->updateamum;
				$obj->updatevon = $row->updatevon;
				$obj->insertamum = $row->insertamum;
				$obj->insertvon = $row->insertvon;
				$obj->note_bezeichnung = $row->note_bezeichnung;
				$obj->lehrveranstaltung_bezeichnung = $row->lehrveranstaltung_bezeichnung;
				$obj->lehrveranstaltung_bezeichnung_english = $row->lehrveranstaltung_bezeichnung_english;
				$obj->bemerkung = $row->bemerkung;
				$obj->semesterstunden = $row->semesterstunden;
				$obj->ects = $row->ects;
				$obj->sort = $row->sort;
				$obj->studiengang_kz = $row->studiengang_kz;
				$obj->zeugnis = $this->db_parse_bool($row->zeugnis);
				$obj->lv_lehrform_kurzbz = $row->lv_lehrform_kurzbz;
				$obj->lehrveranstaltung_lvnr = $row->lvnr;
				$obj->studienplan_lehrveranstaltung_id = $row->studienplan_lehrveranstaltung_id;
				$obj->studienplan_lehrveranstaltung_id_parent = $row->studienplan_lehrveranstaltung_id_parent;
				$obj->studienplan_lehrveranstaltung_semester = $row->semester;
				$obj->studienplan_lehrveranstaltung_sort = $row->studienplan_lehrveranstaltung_sort;

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

	/**
	 * Generiert den SQL-Befehl für eine UNDO-Aktion
	 * @param type $crud gewünschter Typ der UNDO-Aktion
	 */
	public function getUndo($crud)
	{
	    if(strtoupper($crud) === 'INSERT')
	    {
		return 'INSERT INTO lehre.tbl_zeugnisnote (lehrveranstaltung_id, student_uid,
			    studiensemester_kurzbz, note, uebernahmedatum, benotungsdatum, bemerkung,
			    updateamum, updatevon, insertamum, insertvon, punkte) VALUES('.
			    $this->db_add_param($this->lehrveranstaltung_id, FHC_INTEGER).', '.
			    $this->db_add_param($this->student_uid).', '.
			    $this->db_add_param($this->studiensemester_kurzbz).', '.
			    $this->db_add_param($this->note).', '.
			    $this->db_add_param($this->uebernahmedatum).', '.
			    $this->db_add_param($this->benotungsdatum).', '.
			    $this->db_add_param($this->bemerkung).', '.
			    $this->db_add_param($this->updateamum).', '.
			    $this->db_add_param($this->updatevon).', '.
			    $this->db_add_param($this->insertamum).', '.
			    $this->db_add_param($this->insertvon).', '.
			    $this->db_add_param($this->punkte).');';
	    }
	    else if(strtoupper($crud) === 'UPDATE')
	    {
		return 'UPDATE lehre.tbl_zeugnisnote SET '.
			    'note='.$this->db_add_param($this->note).', '.
			    'punkte='.$this->db_add_param($this->punkte).','.
			    'uebernahmedatum='.$this->db_add_param($this->uebernahmedatum).', '.
			    'benotungsdatum='.$this->db_add_param($this->benotungsdatum).', '.
			    'bemerkung='.$this->db_add_param($this->bemerkung).', '.
			    'updateamum= '.$this->db_add_param($this->updateamum).', '.
			    'updatevon='.$this->db_add_param($this->updatevon).' '.
			    'WHERE lehrveranstaltung_id='.$this->db_add_param($this->lehrveranstaltung_id, FHC_INTEGER).' '.
			    'AND student_uid='.$this->db_add_param($this->student_uid).' '.
			    'AND studiensemester_kurzbz='.$this->db_add_param($this->studiensemester_kurzbz).';';
	    }
	    else
	    {
		return NULL;
	    }
	}
}
?>
