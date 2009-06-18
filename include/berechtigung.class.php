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

class berechtigung extends basis_db
{
	public $berechtigungen=array();
	public $new;

	public $rolle_kurzbz;
	public $beschreibung;
	public $berechtigung_kurzbz;
	
	/**
	 * Konstruktor
	 * @param 
	 */
	public function __construct()
	{
		parent::__construct();
	}
	
	/**
	 * Holt alle BerechtigungsRollen
	 * @return true wenn erfolgreich, false im Fehlerfall
	 */
	public function getRollen()
	{
		$qry = 'SELECT * FROM system.tbl_rolle';

		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$obj = new berechtigung();
	
				$obj->rolle_kurzbz=$row->rolle_kurzbz;
				$obj->beschreibung=$row->beschreibung;
				
				$this->result[] = $obj;
			}
			return true;	
		}
		else
		{
			$this->errormsg = 'Datensatz konnte nicht geladen werden';
			return false;
		}		
	}
	
	/**
	 * Laedt alle Berechtigungen zu einer rolle
	 *
	 * @param $rolle_kurzbz
	 */
	public function getRolleBerechtigung($rolle_kurzbz)
	{
		$qry = "SELECT * FROM system.tbl_rolleberechtigung JOIN system.tbl_berechtigung USING(berechtigung_kurzbz)
				WHERE rolle_kurzbz='".addslashes($rolle_kurzbz)."' ORDER BY berechtigung_kurzbz, beschreibung";
		
		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$obj = new berechtigung();
				
				$obj->berechtigung_kurzbz = $row->berechtigung_kurzbz;
				$obj->rolle_kurzbz = $row->rolle_kurzbz;
				$obj->beschreibung = $row->beschreibung;
				
				$this->result[] = $obj;
			}
			return true;
		}
		else 
		{
			$this->errormsg = 'Fehler beim Laden der Berechtigungen';
			return false;
		}
	}
}
?>