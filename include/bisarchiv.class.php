<?php
/* Copyright (C) 2015 Technikum-Wien
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
 * Authors: Nikolaus Krondraf <nikolaus.krondraf@technikum-wien.at>
 */
require_once(dirname(__FILE__).'/basis_db.class.php');

class bisarchiv extends basis_db 
{
	public $errormsg;
	public $result;

	// Tabellenspalten
	public $archiv_id;
	public $studiensemester_kurzbz;
	public $meldung;
	public $html;
	public $studiengang_kz;
	public $insertamum;
	public $insertvon;
	public $typ;
	
	/**
	 * Konstruktor
	 */
	public function __construct()
	{
		parent::__construct();
	}
	
	/**
	 * Prueft die Variablen auf Gueltigkeit
	 * @return true wenn ok, false im Fehlerfall
	 */
	protected function validate()
	{
		if($this->studiensemster_kurzbz == null || mb_strlen($this->studiensemster_kurzbz) > 6)
		{
			$this->errormsg = "Studiensemester ist ungueltig";
			return false;
		}
		
		if($this->meldung == null)
		{
			$this->errormsg = "Meldung ist ungueltig";
			return false;
		}
		
		if(empty($this->typ))
		{
			$this->errormsg = "Typ ist ungueltig";
			return false;
		}
		
		if($this->typ == "studenten")
		{
			if(!is_numeric($this->studiengang_kz))
			{
				$this->errormsg = "Studiengangkennzahl ist ungueltig";
				return false;
			}
		}
		else
		{
			if(!empty($this->studiengang_kz))
			{
				$this->errormsg = "Studiengangkennzahl ist ungueltig";
				return false;
			}
		}
		
		if($this->insertvon == null)
		{
			$this->errormsg = "Mitarbeiter ist ungueltig";
			return false;
		}
		
		return true;
	}
	
	/**
	 * Speichert den aktuellen Datensatz in die Datenbank
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function save()
	{
		// abbrechen wenn bereits ein Fehler aufgetreten ist
		if($this->errormsg != '')
			return false;

		// Variablen pruefen
		if(!$this->validate())
			return false;
		
		$qry = "INSERT INTO bis.tbl_archiv (studiensemester_kurzbz, meldung, html, studiengang_kz, insertamum, insertvon, typ) VALUES ("
			. $this->db_add_param($this->studiensemster_kurzbz) . ","
			. $this->db_add_param($this->meldung) . ","
			. $this->db_add_param($this->html) . ","
			. $this->db_add_param($this->studiengang_kz) . ","
			. "CURRENT_TIMESTAMP,"
			. $this->db_add_param($this->insertvon) . ","
			. $this->db_add_param($this->typ) . ")";
		
		if($this->db_query($qry))
		{
			return true;
		}
		else
		{
			$this->errormsg = "Fehler beim Speichern der Daten";
			return false;
		}
	}
	
	/**
	 * Liest die Dateien der Meldung aus
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function readFile($file, $type)
	{
		if(strpos($file, '..') !== false || preg_match('/\.(xml|html)$/', $file) === 0)
		{
			$this->errormsg = "Datei ungueltig";
			return false;
		}
		
		$contents = null;
		
		if(is_readable($file))
		{
			$handle = fopen($file, "r");
			$contents = fread($handle, filesize($file));
			fclose($handle);
		}
		else
		{
			$this->errormsg = "Datei kann nicht gelesen werden";
			return false;
		}
		
		switch($type)
		{
			case 'xml':
				$this->meldung = $contents;
				return true;
			case 'html':
				$this->html = $contents;
				return true;
			default:
				$this->errormsg = "Typ ist ungueltig";
				return false;
		}
	}
	
	/**
	 * Gibt die BIS-Daten für das gewünschte Semester zurück
	 * @param $sem Studiensemester
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function getBisData($sem)
	{
		$qry = "SELECT archiv_id, meldung, html, studiengang_kz, insertamum, typ "
				. "FROM bis.tbl_archiv "
				. "WHERE studiensemester_kurzbz = " . $this->db_add_param($sem) . ""
				. "ORDER BY insertamum DESC";
		
		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$this->result[] = $row;
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
	 * Lädt die BIS-Meldung mit der angegebenen ID
	 * @param $id ID der Meldung
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function load($id) 
	{
		if(!is_numeric($id))
		{
			$this->errormsg = 'ID muss eine gueltige Zahl sein';
			return false;
		}
		
		$qry = "SELECT archiv_id, meldung, html, studiengang_kz, insertamum, typ "
				. "FROM bis.tbl_archiv "
				. "WHERE archiv_id = " . $this->db_add_param($id);
		
		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$this->archiv_id = $id;
				$this->meldung = $row->meldung;
				$this->html = $row->html;
				$this->studiengang_kz = $row->studiengang_kz;
				$this->insertamum = $row->insertamum;
				$this->typ = $row->typ;
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