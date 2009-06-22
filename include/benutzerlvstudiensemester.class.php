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

class benutzerlvstudiensemester extends basis_db
{
	public $new; // boolean
	public $benutzerlvstudiensemester = array(); // benutzerlvstudiensemester Objekt

	//Tabellenspalten
	public $uid;						// varchar(16)
	public $studiensemester_kurzbz;	// varchar(16)
	public $lehrveranstaltung_id;		// integer

	/**
	 * Konstruktor - Laedt optional eine Zuteilung
	 * @param $uid
	 * @param $studiensemester_kurzbz
	 * @param $lehrveranstaltung_id
	 */
	public function __construct($uid=null, $studiensemester_kurzbz=null, $lehrveranstaltung_id=null)
	{
		parent::__construct();
		
		$this->new = true;

		if(!is_null($uid) && !is_null($studiensemester_kurzbz) && !is_null($lehrveranstaltung_id))
			$this->load($uid, $studiensemester_kurzbz, $lehrveranstaltung_id);
	}

	/**
	 * Laedt eine Zuteilung
	 * @param $uid, $studiensemester_kurzbz, $lehrveranstaltung_nr
	 */
	public function load($uid, $studiensemester_kurzbz, $lehrveranstaltung_id)
	{
		$this->errormsg = 'Not implemented';
		return false;
	}
	
	/**
	 * Laedt alle uids in zu einer lv/szudiensemester - kombination
	 * gibt auch vor- und Nachname zurueck
	 * @param studiensemester_kurzbz
	 * @param lehrveranstaltung_id
	 * @return boolean
	 */
	public function get_all_uids($studiensemester_kurzbz, $lehrveranstaltung_id)
	{
		$qry = "SELECT tbl_benutzerlvstudiensemester.uid, vw_benutzer.nachname, vw_benutzer.vorname 
				FROM campus.tbl_benutzerlvstudiensemester, campus.vw_benutzer 
				WHERE studiensemester_kurzbz='".addslashes($studiensemester_kurzbz)."' 
					AND lehrveranstaltung_id = '".addslashes($lehrveranstaltung_id)."' 
					AND vw_benutzer.uid = tbl_benutzerlvstudiensemester.uid ORDER BY nachname";
		
		if(!$this->db_query($qry))
		{
			$this->errormsg = 'Datensatz konnte nicht geladen werden';
			return false;
		}
		
		if ($this->db_num_rows() == 0)
			return false;
		else
		{
			while($row = $this->db_fetch_object())
			{
				$lv_obj = new benutzerlvstudiensemester();
				$lv_obj->uid = $row->uid;
				$lv_obj->nachname = $row->nachname;
				$lv_obj->vorname = $row->vorname;
				$this->uids[] = $lv_obj;
			}
			return true;
		}
	}
	
	/**
	 * Prueft die Variablen vor dem Speichern
	 * auf Gueltigkeit.
	 * @return true wenn ok, false im Fehlerfall
	 */
	protected function validate()
	{
		if(mb_strlen($this->uid)>16)
		{
			$this->errormsg = 'UID darf nicht laenger als 16 Zeichen sein';
			return false;
		}
		if(mb_strlen($this->studiensemester_kurzbz)>16)
		{
			$this->errormsg = 'Studiensemester_kurzbz darf nicht laenger als 16 Zeichen sein';
			return false;
		}
		if(!is_numeric($this->lehrveranstaltung_id))
		{
			$this->errormsg = 'Lehrveranstaltungsnummer muss eine gueltige Zahl sein';
			return false;
		}
		return true;
	}

	/**
	 * Speichert Zuteilung in die Datenbank
	 * Wenn $new auf true gesetzt ist wird ein neuer Datensatz
	 * angelegt, ansonsten der Datensatz upgedated
	 * @return true wenn erfolgreich, false im Fehlerfall
	 */
	public function save($new=null)
	{
		if(!is_null($new))
			$this->new = $new;

		//Variablen auf Gueltigkeit pruefen
		if(!$this->validate())
			return false;

		if($this->new)
		{
			$qry = 'INSERT INTO campus.tbl_benutzerlvstudiensemester (uid, studiensemester_kurzbz, lehrveranstaltung_id)
			        VALUES('.$this->addslashes($this->uid).','.
					$this->addslashes($this->studiensemester_kurzbz).','.
					$this->addslashes($this->lehrveranstaltung_id).');';
		}
		else
		{
			// ToDo
			//$qry = 'Select 1;';
			$this->errormsg = 'Update ist noch nicht implementiert';
			return false;
		}

		if($this->db_query($qry))
		{
			//Log schreiben
			$this->new = false;
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern der BenutzerLVStudiensemester';
			return false;
		}
	}
}
?>