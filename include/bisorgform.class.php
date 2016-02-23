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
 * Authors: Werner Masik <werner@gefi.at>
 */
require_once(dirname(__FILE__).'/basis_db.class.php');

class bisorgform extends basis_db
{
	public $new;
	public $result = array();

	//Tabellenspalten
	public $bisorgform_kurzbz;
	public $code;


	/**
	 * Konstruktor
	 * @param code des zu ladenden Datensatzes
	 */
	public function __construct($code=null)
	{
		parent::__construct();

		if(!is_null($code))
			$this->load($code);
	}

	/**
	 * Laedt einen Datensatz
	 * @param code des zu ladenden Datensatzes
	 */
	public function load($code)
	{
		//code auf gueltigkeit pruefen
		if(!is_numeric($code) || $code == '')
		{
			$this->errormsg = 'code muss eine gueltige Zahl sein';
			return false;
		}

		//laden des Datensatzes
		$qry = "SELECT
					distinct code,bisorgform_kurzbz
				FROM
					bis.tbl_orgform
				WHERE
					code=".$this->db_add_param($code, FHC_INTEGER);

		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				$this->bisorgform_kurzbz = $row->bisorgform_kurzbz;
				$this->code = $row->code;

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
	 * Laedt alle BIS Orgformen
	 * @return List mit Orgformen wenn ok, false wenn Fehler
	 */
	public function getList()
	{
		//laden des Datensatzes
		$qry = "SELECT
					distinct code,bisorgform_kurzbz
				FROM
					bis.tbl_bisorgform
				ORDER BY code";

		if($this->db_query($qry))
		{
			$this->result = array();
			while($row = $this->db_fetch_object())
			{
				$obj = new bisorgform();
				$obj->bisorgform_kurzbz = $row->bisorgform_kurzbz;
				$obj->code = $row->code;
				$this->result[] = $obj;
			}
			return $this->result;
		}
		else
		{
			$this->errormsg = 'Fehler bei der Datenbankabfrage';
			return false;
		}
	}
}
?>
