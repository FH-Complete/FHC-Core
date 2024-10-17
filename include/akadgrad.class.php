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
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at> and
 *          Andreas Moik <moik@technikum-wien.at>.
 */
require_once(dirname(__FILE__).'/basis_db.class.php');

class akadgrad extends basis_db
{
	public $new;
	public $result = array();
	
	//Tabellenspalten
	public $akadgrad_id;
	public $akadgrad_kurzbz;
	public $studiengang_kz;
	public $titel;
	public $geschlecht;
	
	/**
	 * Konstruktor
	 * @param akadgrad_id ID des zu ladenden Datensatzes
	 */
	public function __construct($akadgrad_id=null)
	{
		parent::__construct();

		if(!is_null($akadgrad_id))
			$this->load($akadgrad_id);
	}
	
	/**
	 * Laedt einen Datensatz
	 * @param akadgrad_id ID des zu ladenden Datensatzes
	 */
	public function load($akadgrad_id)
	{
		//akadgrad_id auf gueltigkeit pruefen
		if(!is_numeric($akadgrad_id) || $akadgrad_id == '')
		{
			$this->errormsg = 'akadgrad_id muss eine gültige Zahl sein';
			return false;
		}
		
		//laden des Datensatzes
		$qry = "SELECT * FROM lehre.tbl_akadgrad WHERE akadgrad_id=".$this->db_add_param($akadgrad_id, FHC_INTEGER);
		
		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				$this->akadgrad_id = $row->akadgrad_id;
				$this->akadgrad_kurzbz = $row->akadgrad_kurzbz;
				$this->studiengang_kz = $row->studiengang_kz;
				$this->titel = $row->titel;
				$this->geschlecht = $row->geschlecht;
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
	 * Laedt alle Eintraege
	 */
	public function getAll()
	{
		//laden des Datensatzes
		$qry = "SELECT * FROM lehre.tbl_akadgrad";
		
		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$obj = new akadgrad();

				$obj->akadgrad_id = $row->akadgrad_id;
				$obj->akadgrad_kurzbz = $row->akadgrad_kurzbz;
				$obj->studiengang_kz = $row->studiengang_kz;
				$obj->titel = $row->titel;
				$obj->geschlecht = $row->geschlecht;
				
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
	 * Liefert den Akademischen Grad eines Studenten aus der Tabelle Akadgrad
	 * @param student_uid
	 */
	public function getAkadgradStudent($student_uid)
	{
		//laden des Datensatzes
		$qry = "SELECT * FROM lehre.tbl_akadgrad WHERE 
				studiengang_kz = (	SELECT studiengang_kz FROM public.tbl_student WHERE student_uid=".$this->db_add_param($student_uid).") AND
				(	geschlecht = (	SELECT geschlecht FROM public.tbl_student 
								JOIN public.tbl_benutzer ON (student_uid=uid) 
								JOIN public.tbl_person USING (person_id) 
								WHERE student_uid=".$this->db_add_param($student_uid).") 
					OR geschlecht IS NULL)
				ORDER BY geschlecht NULLS LAST LIMIT 1";
		
		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				$this->akadgrad_id = $row->akadgrad_id;
				$this->akadgrad_kurzbz = $row->akadgrad_kurzbz;
				$this->studiengang_kz = $row->studiengang_kz;
				$this->titel = $row->titel;
				$this->geschlecht = $row->geschlecht;
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
	
	public function __toString()
	{
		return $this->akadgrad_kurzbz;
	}
}
?>
