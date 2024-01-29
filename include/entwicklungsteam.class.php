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

class entwicklungsteam extends basis_db
{
	public $new;
	public $result = array();

	//Tabellenspalten
	public $mitarbeiter_uid;
	public $studiengang_kz;
	public $besqualcode;
	public $beginn;
	public $ende;
	public $updateamum;
	public $updatevon;
	public $insertamum;
	public $insertvon;
	public $ext_id;

	public $besqual;
	public $studiengang_kz_old;

	/**
	 * Konstruktor
	 * @param mitarbeiter_uid ID des zu ladenden Datensatzes
	 *        studiengang_kz
	 */
	public function __construct($mitarbeiter_uid=null, $studiengang_kz=null)
	{
		parent::__construct();

		if(!is_null($mitarbeiter_uid) && !is_null($studiengang_kz))
			$this->load($mitarbeiter_uid, $studiengang_kz);
	}

	/**
	 * Laedt einen Datensatz
	 * @param mitarbeiter_uid ID des zu ladenden Datensatzes
	 *        studiengang_kz
	 */
	public function load($mitarbeiter_uid, $studiengang_kz)
	{
		if(!is_numeric($studiengang_kz) || $studiengang_kz == '')
		{
			$this->errormsg = 'studiengang_kz muss eine gueltige Zahl sein';
			return false;
		}

		//laden des Datensatzes
		$qry = "SELECT * FROM bis.tbl_entwicklungsteam JOIN bis.tbl_besqual USING(besqualcode)
				WHERE mitarbeiter_uid=".$this->db_add_param($mitarbeiter_uid)." AND studiengang_kz=".$this->db_add_param($studiengang_kz, FHC_INTEGER).";";

		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				$this->mitarbeiter_uid = $row->mitarbeiter_uid;
				$this->studiengang_kz = $row->studiengang_kz;
				$this->besqualcode = $row->besqualcode;
				$this->beginn = $row->beginn;
				$this->ende = $row->ende;
				$this->updateamum = $row->updateamum;
				$this->updatevon = $row->updatevon;
				$this->insertamum = $row->insertamum;
				$this->insertvon = $row->insertvon;
				$this->ext_id = $row->ext_id;
				$this->besqual = $row->besqualbez;
				return true;
			}
			else
			{
				$this->errormsg = 'Fehler bei der Datenbankabfrage';
				return false;
			}
		}
		else
		{
			$this->errormsg = 'Fehler bei der Datenbankabfrage';
			return false;
		}
	}

	/**
	 * Loescht einen Datensatz
	 * @param bisverwendung_id ID des zu loeschenden Datensatzes
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function delete($mitarbeiter_uid, $studiengang_kz)
	{
		if(!is_numeric($studiengang_kz) || $studiengang_kz == '')
		{
			$this->errormsg = 'studiengang_kz muss eine gueltige Zahl sein';
			return false;
		}

		$qry = "DELETE FROM bis.tbl_entwicklungsteam
				WHERE mitarbeiter_uid = ".$this->db_add_param($mitarbeiter_uid)." AND studiengang_kz=".$this->db_add_param($studiengang_kz, FHC_INTEGER).";";

		if($this->db_query($qry))
		{
			//Log schreiben
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Loeschen';
			return false;
		}
	}

	/**
	 * Prueft die Variablen vor dem Speichern
	 *
	 * @return true wenn ok, sonst false
	 */
	protected function validate()
	{
		if($this->mitarbeiter_uid=='')
		{
			$this->errormsg = 'Es muss ein Mitarbeiter angegeben werden';
			return false;
		}
		if($this->studiengang_kz=='')
		{
			$this->errormsg = 'Es muss ein Studiengang angegeben werden';
			return false;
		}
		if($this->besqualcode=='')
		{
			$this->errormsg = 'BesondereQualifikation muss eingetragen werden';
			return false;
		}
		return true;
	}

	/**
	 * Speichert den aktuellen Datensatz
	 * Wenn $neu auf true gesetzt ist wird ein neuer Datensatz angelegt
	 * andernfalls wird der Datensatz mit der ID in $akte_id aktualisiert
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function save($new=null)
	{
		if(!$this->validate())
			return false;
		if($new==null)
			$new = $this->new;

		if($new)
		{
			//Neuen Datensatz anlegen
			$qry = "INSERT INTO bis.tbl_entwicklungsteam (mitarbeiter_uid, studiengang_kz, besqualcode, beginn, ende,
					updateamum, updatevon, insertamum, insertvon) VALUES (".
			       $this->db_add_param($this->mitarbeiter_uid).', '.
			       $this->db_add_param($this->studiengang_kz, FHC_INTEGER).', '.
			       $this->db_add_param($this->besqualcode, FHC_INTEGER).', '.
			       $this->db_add_param($this->beginn).', '.
			       $this->db_add_param($this->ende).', '.
			       $this->db_add_param($this->updateamum).', '.
			       $this->db_add_param($this->updatevon).', '.
			       $this->db_add_param($this->insertamum).', '.
			       $this->db_add_param($this->insertvon).');';

		}
		else
		{
			if($this->studiengang_kz_old=='')
				$this->studiengang_kz_old = $this->studiengang_kz;

			//Bestehenden Datensatz aktualisieren
			$qry= "UPDATE bis.tbl_entwicklungsteam SET".
				  " besqualcode=".$this->db_add_param($this->besqualcode, FHC_INTEGER).",".
				  " beginn=".$this->db_add_param($this->beginn).",".
				  " studiengang_kz=".$this->db_add_param($this->studiengang_kz, FHC_INTEGER).",".
				  " ende=".$this->db_add_param($this->ende).",".
				  " updateamum=".$this->db_add_param($this->updateamum).",".
				  " updatevon=".$this->db_add_param($this->updatevon).
				  " WHERE mitarbeiter_uid=".$this->db_add_param($this->mitarbeiter_uid)." AND studiengang_kz=".$this->db_add_param($this->studiengang_kz_old, FHC_INTEGER).";";
		}

		if($this->db_query($qry))
		{
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern des Datensatzes';
			return false;
		}
	}

	/*
	 * Laedt alle Entwicklungsteameintraege eines Mitarbeiters
	 * @param $uid UID des Mitarbeiters
	 * @return true wenn ok, false wenn Fehler
	 */
	public function getEntwicklungsteam($mitarbeiter_uid, $studiengang_kz=null)
	{
		//laden des Datensatzes
		$qry = "SELECT * FROM bis.tbl_entwicklungsteam JOIN bis.tbl_besqual USING(besqualcode)
				WHERE mitarbeiter_uid=".$this->db_add_param($mitarbeiter_uid);

		if($studiengang_kz!=null)
			$qry.=" AND studiengang_kz=".$this->db_add_param($studiengang_kz);

        $qry.=";";

		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$obj = new entwicklungsteam();

				$obj->mitarbeiter_uid = $row->mitarbeiter_uid;
				$obj->studiengang_kz = $row->studiengang_kz;
				$obj->besqualcode = $row->besqualcode;
				$obj->beginn = $row->beginn;
				$obj->ende = $row->ende;
				$obj->updateamum = $row->updateamum;
				$obj->updatevon = $row->updatevon;
				$obj->insertamum = $row->insertamum;
				$obj->insertvon = $row->insertvon;
				$obj->ext_id = $row->ext_id;
				$obj->besqual = $row->besqualbez;

				$this->result[] = $obj;
			}
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler bei der Datenbankabfrage';
			return false;
		}
	}

	/**
	 * Preuft ob der Eintrag schon existiert
	 *
	 * @param $mitarbeiter_uid
	 * @param $studiengang_kz
	 * @return true wenn vorhanden, false wenn nicht
	 */
	public function exists($mitarbeiter_uid,$studiengang_kz)
	{
		$qry = "SELECT count(*) as anzahl FROM bis.tbl_entwicklungsteam
				WHERE mitarbeiter_uid=".$this->db_add_param($mitarbeiter_uid)." AND studiengang_kz=".$this->db_add_param($studiengang_kz, FHC_INTEGER).";";

		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				if($row->anzahl>0)
					return true;
				else
					return false;
			}
			else
			{
				$this->errormsg = 'Fehler beim Laden der Daten';
				return false;
			}
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Daten';
			return false;
		}
	}
}
?>
