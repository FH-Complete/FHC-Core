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
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>,
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>
 *          Manuela Thamer <manuela.thamer@technikum-wien.at>
 */
require_once(dirname(__FILE__).'/basis_db.class.php');

class entwicklungsteam extends basis_db
{
	public $new;
	public $result = array();

	//Tabellenspalten
	public $entwicklungsteam_id;
	public $mitarbeiter_uid;
	public $nachname;
	public $vorname;
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
	 * @param entwicklungsteam_id ID des zu ladenden Datensatzes
	 *        studiengang_kz
	 */
	public function __construct($entwicklungsteam_id = null)
	{
		parent::__construct();

		if(!is_null($entwicklungsteam_id))
			$this->load($entwicklungsteam_id);
	}

	/**
	 * Laedt einen Datensatz
	 * @param entwicklungsteam_id ID des zu ladenden Datensatzes
	 */
	public function load($entwicklungsteam_id)
	{
		if(!is_numeric($entwicklungsteam_id))
		{
			$this->errormsg = 'entwicklungsteam_id muss eine gueltige Zahl sein';
			return false;
		}

		//laden des Datensatzes
		$qry = "SELECT * FROM bis.tbl_entwicklungsteam JOIN bis.tbl_besqual USING(besqualcode)
				WHERE entwicklungsteam_id=".$this->db_add_param($entwicklungsteam_id);

				$qry.=";";

		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				$this->entwicklungsteam_id = $row->entwicklungsteam_id;
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
	 * @param entwicklungsteam_id ID des zu loeschenden Datensatzes
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function delete($entwicklungsteam_id)
	{
		if(!is_numeric($entwicklungsteam_id))
		{
			$this->errormsg = 'entwicklungsteam_id muss eine gueltige Zahl sein';
			return false;
		}

		$qry = "DELETE FROM bis.tbl_entwicklungsteam
				WHERE entwicklungsteam_id = ".$this->db_add_param($entwicklungsteam_id).";";

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
			$this->errormsg = 'Besondere Qualifikation muss eingetragen werden';
			return false;
		}
		if($this->ende != '' && $this->beginn > $this->ende)
		{
			$this->errormsg = 'Endedatum darf nicht vor Anfangsdatum liegen';
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
	public function save($new = null)
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
				  " WHERE entwicklungsteam_id=".$this->db_add_param($this->entwicklungsteam_id).";";
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
	public function getEntwicklungsteam($mitarbeiter_uid, $studiengang_kz = null)
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

				$obj->entwicklungsteam_id = $row->entwicklungsteam_id;
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
	 * Prueft ob der Eintrag schon existiert
	 *
	 * @param entwicklungsteam_id
	 * @return true wenn vorhanden, false wenn nicht
	 */
	public function exists($entwicklungsteam_id)
	{
		$qry = "SELECT count(*) as anzahl FROM bis.tbl_entwicklungsteam
				WHERE entwicklungsteam_id=".$this->db_add_param($entwicklungsteam_id).";";

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

	/**
	 * Liefert alle Entwicklungsteameinträge
	 * @param int $studiengang_kz Studiengangkennzeichen.
	 * @param char $sort Parameter, nach dem sortiert werden soll.
	 * @return alle Entwicklungsteameinträge
	 */
	public function getAll($studiengang_kz = null, $sort = null)
	{
		$qry = "SELECT e.*, p.nachname, p.vorname FROM bis.tbl_entwicklungsteam e
		        JOIN public.tbl_benutzer b ON e.mitarbeiter_uid = b.uid
						JOIN public.tbl_person p ON b.person_id = p.person_id
		       ";
		if ($studiengang_kz != null)
			$qry .= " WHERE e.studiengang_kz = ".$this->db_add_param($studiengang_kz);

		if ($sort != null)
		{
				$qry .= " ORDER BY ".$sort;
		}

		$qry .= ";";

		if ($this->db_query($qry))
		{
			while ($row = $this->db_fetch_object())
			{
				$obj = new entwicklungsteam();

				$obj->entwicklungsteam_id = $row->entwicklungsteam_id;
				$obj->mitarbeiter_uid = $row->mitarbeiter_uid;
				$obj->nachname = $row->nachname;
				$obj->vorname = $row->vorname;
				$obj->studiengang_kz = $row->studiengang_kz;
				$obj->besqualcode = $row->besqualcode;
				$obj->beginn = $row->beginn;
				$obj->ende = $row->ende;
				$obj->insertamum = $row->insertamum;
				$obj->insertvon = $row->insertvon;
				$obj->updateamum = $row->updateamum;
				$obj->updatevon = $row->updatevon;


				$this->result[] = $obj;
			}
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Entwicklungsteameinträge.';
			return false;
		}
	}

	/*
	 * Laedt alle Entwicklungsteameintraege eines Mitarbeiters für eine bestimmte Bisperiode
	 * @param $uid UID des Mitarbeiters
	 * @param $stichtag Stichtag im Format 'Y-m-d'
	 * @return true wenn ok, false wenn Fehler
	 */
	public function getEntwicklungsteamBis($mitarbeiter_uid, $stichtag, $studiengang_kz = null)
	{
		$datetime = new DateTime($stichtag);
		$bismeldung_jahr = $datetime->format('Y');

		//laden des Datensatzes
			$qry = "SELECT *
					FROM bis.tbl_entwicklungsteam
					JOIN bis.tbl_besqual USING(besqualcode)
					WHERE mitarbeiter_uid=".$this->db_add_param($mitarbeiter_uid)."
					AND (beginn is NULL OR beginn <= make_date(". $this->db_add_param($bismeldung_jahr). "::INTEGER, 12, 31))
					AND (ende is NULL OR ende >= make_date(". $this->db_add_param($bismeldung_jahr). "::INTEGER, 1, 1))";

		if($studiengang_kz!=null)
			$qry.=" AND studiengang_kz=".$this->db_add_param($studiengang_kz);

        $qry.=";";

		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$obj = new entwicklungsteam();

				$obj->entwicklungsteam_id = $row->entwicklungsteam_id;
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
}
?>
