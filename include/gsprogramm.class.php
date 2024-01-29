<?php
/* Copyright (C) 2016 fhcomplete.org
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
 * Authors: Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>
 */

/**
 * Klasse GSProgramm
 */

require_once(dirname(__FILE__).'/basis_db.class.php');

class gsprogramm extends basis_db
{
	public $new=true;			//  boolean
	public $result = array();

	//Tabellenspalten
	public $gsprogramm_id;
	public $programm_code;
	public $bezeichnung;
	public $gsprogrammtyp_kurzbz;
	public $studienkennung_uni;

	public function getAll()
	{
		$qry ="SELECT
					tbl_gsprogramm.*,
					tbl_gsprogrammtyp.bezeichnung as gsprogrammtyp_bezeichnung
				FROM
					bis.tbl_gsprogramm
					LEFT JOIN bis.tbl_gsprogrammtyp USING(gsprogrammtyp_kurzbz)
				ORDER BY tbl_gsprogramm.bezeichnung";

		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$obj = new gsprogramm();

				$obj->gsprogramm_id = $row->gsprogramm_id;
				$obj->programm_code = $row->programm_code;
				$obj->bezeichnung = $row->bezeichnung;
				$obj->gsprogrammtyp_kurzbz = $row->gsprogrammtyp_kurzbz;

				$obj->gsprogrammtyp_bezeichnung = $row->gsprogrammtyp_bezeichnung;
				$obj->studienkennung_uni = $row->studienkennung_uni;

				$this->result[]=$obj;
			}
			return true;
		}
		else
		{
			$this->errormsg = "Fehler bei der Abfrage aufgetreten";
			return false;
		}
	}


	/**
	 * Lädt ein GSProgramm
	 * @param int $gsprogramm_id
	 * @return boolean
	 */
	public function load($gsprogramm_id)
	{
		$qry ="SELECT * FROM bis.tbl_gsprogramm where gsprogramm_id =".$this->db_add_param($gsprogramm_id, FHC_INTEGER).';';
		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				$this->gsprogramm_id = $row->gsprogramm_id;
				$this->programm_code = $row->programm_code;
				$this->bezeichnung = $row->bezeichnung;
				$this->gsprogrammtyp_kurzbz = $row->gsprogrammtyp_kurzbz;
				$this->studienkennung_uni = $row->studienkennung_uni;
				$this->new = false;
			}
			return true;
		}
		else
		{
			$this->errormsg = "Fehler bei der Abfrage aufgetreten";
			return false;
		}
	}

	/**
	 * Laedt die GSProgrammTypen
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function getTypen()
	{
		$qry = "SELECT * FROM bis.tbl_gsprogrammtyp ORDER BY bezeichnung";

		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				$obj = new stdClass();

				$obj->gsprogrammtyp_kurzbz = $row->gsprogrammtyp_kurzbz;
				$obj->bezeichnung = $row->bezeichnung;
				$obj->programmtyp_code = $row->programmtyp_code;

				$this->result[] = $obj;
			}
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Programmtypen';
			return false;
		}
	}

	/**
	 * Prueft die Daten vor dem Speichern
	 *
	 * @return true wenn ok, false wenn Fehler
	 */
	public function validate()
	{
		if(!is_numeric($this->programm_code))
		{
			$this->errormsg = 'Programm Code muss eine Zahl sein';
			return false;
		}
		if($this->gsprogrammtyp_kurzbz=='')
		{
			$this->errormsg = 'Gsprogrammtyp Kurzbezeichnung muss eingegeben werden';
			return false;
		}
		if($this->bezeichnung=='')
		{
			$this->errormsg = 'Bezeichnung muss eingetragen werden';
			return false;
		}
		if($this->studienkennung_uni != '' && preg_match("/^[AFHLU][UPF][A-Z]([0-9]{3}){1,3}(0[1-6]|[UP][A-W]){0,1}$/", $this->studienkennung_uni) !== 1)
		{
			$this->errormsg = 'Ungültige Studienkennung Uni';
			return false;
		}
		return true;
	}

	public function save()
	{
		if(!$this->validate())
			return false;
		if($this->new)
		{
			$qry = 'BEGIN;INSERT INTO bis.tbl_gsprogramm(programm_code,
				bezeichnung, gsprogrammtyp_kurzbz, studienkennung_uni) VALUES('.
				$this->db_add_param($this->programm_code).','.
				$this->db_add_param($this->bezeichnung).','.
				$this->db_add_param($this->gsprogrammtyp_kurzbz).','.
				$this->db_add_param($this->studienkennung_uni).');';
		}
		else
		{
			$qry = 'UPDATE bis.tbl_gsprogramm SET
				bezeichnung='.$this->db_add_param($this->bezeichnung).',
				gsprogrammtyp_kurzbz='.$this->db_add_param($this->gsprogrammtyp_kurzbz).',
				programm_code='.$this->db_add_param($this->programm_code, FHC_INTEGER).',
				studienkennung_uni='.$this->db_add_param($this->studienkennung_uni).'
				WHERE gsprogramm_id='.$this->db_add_param($this->gsprogramm_id, FHC_INTEGER, false);

		}

		if($this->db_query($qry))
		{
			if($this->new)
			{
				//Sequence lesen
				$qry="SELECT currval('bis.tbl_gsprogramm_gsprogramm_id_seq') as id;";
				if($this->db_query($qry))
				{
					if($row = $this->db_fetch_object())
					{
						$this->gsprogramm_id = $row->id;
						$this->db_query('COMMIT;');
					}
					else
					{
						$this->errormsg = 'Fehler beim Auslesen der Sequence';
						$this->db_query('ROLLBACK;');
						return false;
					}
					return true;
				}
				else
				{
					$this->errormsg = 'Fehler beim Auslesen der Sequence';
					$this->db_query('ROLLBACK;');
					return false;
				}
			}
			else
			{
				return true;
			}
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern der Daten';
			return false;
		}
	}

	/**
	 * Loescht ein GSProgramm
	 * @param $gsprogramm_id ID des Datensatzes
	 * @return boolean true wenn ok, false im Fehlerfall
	 */
	public function delete($gsprogramm_id)
	{
		$qry = "DELETE FROM bis.tbl_gsprogramm
				WHERE gsprogramm_id=".$this->db_add_param($gsprogramm_id, FHC_INTEGER);

		if($this->db_query($qry))
		{
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Löschen des Datensatzes';
			return false;
		}
	}
}
