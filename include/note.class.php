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
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at> and
 *			Gerald Raab <gerald.raab@technikum-wien.at>
 */
/**
 * Klasse Note
 * @create 2007-06-06
 */
require_once(dirname(__FILE__).'/basis_db.class.php');

class note extends basis_db
{
	public $new;       		// boolean
	public $result=array();

	//Tabellenspalten
	public $note;				// smallint
	public $bezeichnung;		// varchar(32)
	public $anmerkung;			// varchar(256)
	public $farbe;

	/**
	 * Konstruktor
	 * @param $lehrveranstaltung_id
	 *        $student_uid
	 *        $studiensemester_kurzbz
	 */
	public function __construct($note = null)
	{
		parent::__construct();

		if($note != null)
			$this->load($note);
	}

	/**
	 * Laedt eine Note
	 * @param  $lehrveranstaltung_id
	 *         $student_uid
	 *         $studiensemester_kurzbz
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function load($note)
	{
		if(!is_numeric($note))
		{
			$this->errormsg = 'Note ist ungueltig';
			return false;
		}

		$qry = "SELECT * FROM lehre.tbl_note WHERE note='".$note."'";

		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				$this->note = $row->note;
				$this->bezeichnung = $row->bezeichnung;
				$this->anmerkung = $row->anmerkung;
				$this->farbe = $row->farbe;
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
	public function validate()
	{
		if(!is_numeric($this->note))
		{
			$this->errormsg = 'Note ist ungueltig';
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
			$qry='INSERT INTO lehre.tbl_note (note, bezeichnung, anmerkung) VALUES('.
			     $this->addslashes($this->note).', '.
			     $this->addslashes($this->bezeichnung).', '.
			     $this->addslashes($this->anmerkung).');';
		}
		else
		{
			$qry='UPDATE lehre.tbl_note SET '.
				'note='.$this->addslashes($this->note).', '.
				'bezeichnung='.$this->addslashes($this->bezeichnung).', '.
				'anmerkung='.$this->addslashes($this->anmerkung).', '.
				'WHERE note='.$this->addslashes($this->note).';';
		}

		if($this->db_query($qry))
		{
			return true;
		}
		else
		{
			$this->errormsg = "Fehler beim Speichern des Datensatzes";
			return false;
		}
	}
	
	/**
	 * Laedt alle Noten
	 * @return true wenn ok, false wenn Fehler
	 */	
	public function getAll()
	{
		$qry = "SELECT * FROM lehre.tbl_note ORDER BY note";
		
		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$n = new note();

				$n->note = $row->note;
				$n->bezeichnung = $row->bezeichnung;
				$n->anmerkung = $row->anmerkung;
				$n->farbe = $row->farbe;

				$this->result[] = $n;
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