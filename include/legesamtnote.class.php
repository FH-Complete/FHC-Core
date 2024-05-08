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
require_once(dirname(__FILE__).'/basis_db.class.php');

class legesamtnote extends basis_db
{
	public $new;
	public $legesamtnoten = array();

	//Tabellenspalten
	public $student_uid;		// varchar(16)
	public $lehreinheit_id;	// int
	public $note;				// smallint
	public $benotungsdatum;	//date
	public $updateamum;		// timestamp
	public $updatevon;			// varchar(16)
	public $insertamum;		// timestamp
	public $insertvon;			// varchar(16)

	/**
	 * Konstruktor - Laedt optional eine LEGesamtNote
	 * @param $uebung_id
	 */
	public function __construct($student_uid=null, $lehreinheit_id=null)
	{
		parent::__construct();
		
		if(!is_null($student_uid))
			$this->load($student_uid, $lehreinheit_id);
	}

	/**
	 * Laedt die legesamtnote
	 * @param student_uid, lehreinheit_id
	 */
	public function load($student_uid, $lehreinheit_id)
	{
		if(!is_numeric($lehreinheit_id))
		{
			$this->errormsg='lehreinheit_id muss eine gueltige Zahl sein';
			return false;
		}
		$qry = "SELECT * FROM campus.tbl_legesamtnote 
				WHERE student_uid = ".$this->db_add_param($student_uid)." AND lehreinheit_id = ".$this->db_add_param($lehreinheit_id, FHC_INTEGER).';';

		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				$this->lehreinheit_id = $row->lehreinheit_id;
				$this->student_uid = $row->student_uid;
				$this->note = $row->note;
				$this->benotungsdatum = $row->benotungsdatum;
				$this->updateamum = $row->updateamum;
				$this->updatevon = $row->updatevon;
				$this->insertamum = $row->insertamum;
				$this->insertvon = $row->insertvon;
				return true;
			}
			else
			{
				$this->errormsg = "Es ist keine legesamtnote mit der fuer diesen studenten vorhanden";
				return false;
			}
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der LEGesamtNote';
			return false;
		}
	}
	
	/**
	 * Ledt die LEGesamtnoten einer Lehreinheit
	 *
	 * @param $lehreinheit_id
	 * @return boolean
	 */
	public function load_legesamtnote($lehreinheit_id)
	{
		if(!is_numeric($lehreinheit_id))
		{
			$this->errormsg = 'Lehreinheit_id muss eine gueltige Zahl sein';
			return false;
		}

		$qry = "SELECT * FROM campus.tbl_legesamtnote WHERE lehreinheit_id=".$this->db_add_param($lehreinheit_id, FHC_INTEGER)." ORDER BY student_uid;";


		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$legesamtnote_obj = new legesamtnote();

				$legesamtnote_obj->student_uid = $row->student_uid;
				$legesamtnote_obj->note = $row->note;
				$legesamtnote_obj->lehreinheit_id = $row->lehreinheit_id;
				$legesamtnote_obj->benotungsdatum = $row->benotungsdatum;
				$legesamtnote_obj->updateamum = $row->updateamum;
				$legesamtnote_obj->updatevon = $row->updatevon;
				$legesamtnote_obj->insertamum = $row->insertamum;
				$legesamtnote_obj->insertvon = $row->insertvon;

				$this->legesamtnoten[] = $legesamtnote_obj;
			}
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der legesamtnoten';
			return false;
		}
	}

	/**
	 * Prueft die Variablen vor dem Speichern
	 * auf Gueltigkeit.
	 * @return true wenn ok, false im Fehlerfall
	 */
	protected function validate()
	{
		if(!is_numeric($this->lehreinheit_id))
		{
			$this->errormsg = 'Lehreinheit_id muss eine gueltige Zahl sein';
			return false;
		}
		if(!is_numeric($this->note))
		{
			$this->errormsg = 'Note muss eine gueltige Zahl sein';
			return false;
		}
		return true;
	}

	/**
	 * Speichert Uebung in die Datenbank
	 * Wenn $new auf true gesetzt ist wird ein neuer Datensatz
	 * angelegt, ansonsten der Datensatz upgedated
	 * @return true wenn erfolgreich, false im Fehlerfall
	 */
	public function save()
	{
		//Variablen auf Gueltigkeit pruefen
		if(!$this->validate())
			return false;

		if($this->new)
		{
			$qry = 'INSERT INTO campus.tbl_legesamtnote(student_uid, lehreinheit_id, note, benotungsdatum, updateamum, updatevon, insertamum, insertvon) VALUES('.
			        $this->db_add_param($this->student_uid).','.
			        $this->db_add_param($this->lehreinheit_id, FHC_INTEGER).','.
			        $this->db_add_param($this->note, FHC_INTEGER).','.
			        $this->db_add_param($this->benotungsdatum).','.
			        'null,'.
			        'null,'.
			        $this->db_add_param($this->insertamum).','.
			        $this->db_add_param($this->insertvon).');';
		}
		else
		{
			$qry = 'UPDATE campus.tbl_legesamtnote SET'.
			       ' student_uid='.$this->db_add_param($this->student_uid).','.
			       ' lehreinheit_id ='.$this->db_add_param($this->lehreinheit_id, FHC_INTEGER).','.
			       ' note='.$this->db_add_param($this->note, FHC_INTEGER).','.
			       ' benotungsdatum='.$this->db_add_param($this->benotungsdatum).','.
			       ' updateamum='.$this->db_add_param($this->updateamum).','.
			       ' updatevon='.$this->db_add_param($this->updatevon).
			       " WHERE lehreinheit_id=".$this->db_add_param($this->lehreinheit_id, FHC_INTEGER)." AND student_uid = ".$this->db_add_param($this->student_uid).";";
		}

		if($this->db_query($qry))
		{
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern der legesamtnote:'.$qry;
			return false;
		}
	}

}
?>