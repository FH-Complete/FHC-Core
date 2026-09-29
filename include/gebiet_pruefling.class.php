<?php
/* Copyright (C) 2024 Technikum-Wien
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
 */
require_once(dirname(__FILE__).'/basis_db.class.php');

class gebiet_pruefling extends basis_db
{
	//Tabellenspalten
	public $prueflinggebiet_id;
	public $pruefling_id;
	public $gebiet_id;
	public $insertamum;


	public function __construct()
	{
		parent::__construct();
	}

	public function loadByPruefling($pruefling_id)
	{
		$qry = "SELECT *
     			FROM testtool.tbl_pruefling_gebiet
     			WHERE pruefling_id = ".$this->db_add_param($pruefling_id, FHC_INTEGER);

		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				$this->prueflinggebiet_id = $row->prueflinggebiet_id;
				$this->pruefling_id = $row->pruefling_id;
				$this->gebiet_id = $row->gebiet_id;
				return true;
			}
			else
			{
				$this->errormsg = "Pruefling nicht gefunden";
				return false;
			}
		}
		else
		{
			$this->errormsg = "Fehler beim Laden der Gebiete für den Pruefling";
			return false;
		}
	}

	public function checkIfExists($pruefling_id, $gebiet_id)
	{
		$qry = "SELECT 1 
				FROM testtool.tbl_pruefling_gebiet 
				WHERE pruefling_id =".$this->db_add_param($pruefling_id, FHC_INTEGER) ." AND gebiet_id =".$this->db_add_param($gebiet_id, FHC_INTEGER);

		if ($result = $this->db_query($qry))
		{
			if ($this->db_num_rows($result) > 0)
				return true;
			else
				return false;
		}
	}

	public function saveGebietForPruefling()
	{
		$qry = "INSERT INTO testtool.tbl_pruefling_gebiet 
    					(pruefling_id, gebiet_id)
				VALUES(".
			$this->db_add_param($this->pruefling_id, FHC_INTEGER).','.
			$this->db_add_param($this->gebiet_id, FHC_INTEGER).');';

		if ($this->db_query($qry))
		{
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern der Daten';
			return true;
		}
	}

}
?>
