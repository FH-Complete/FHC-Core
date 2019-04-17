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

/**
 * Klasse Nationengruppe (FAS-Online)
 * @create 06-04-2006
 */
require_once(dirname(__FILE__).'/basis_db.class.php');

class nationengruppe extends basis_db
{
	public $new;      // boolean
	public $nationengruppe = array(); // nation Objekt

	//Tabellenspalten
	public $nationengruppe_kurzbz;      // varchar(16)
	public $nationengruppe_bezeichnung; // varchar (128)
	public $aktiv;                      // Boolean

	/**
	 * Konstruktor
	 * @param string $nationengruppe_kurzbz Zu ladende Nationengruppe
	 */
	public function __construct($nationengruppe_kurzbz = null)
	{
		parent::__construct();

		if($nationengruppe_kurzbz != null)
			$this->load($nationengruppe_kurzbz);
	}


	/**
	 * Laedt die Nationengruppe
	 * @param string $nationengruppe_kurzbz Kurzbz der Nationengruppe
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function load($nationengruppe_kurzbz)
	{
		//Lesen der Daten aus der Datenbank
		$qry = "SELECT * FROM bis.tbl_nationengruppe WHERE nationengruppe_kurzbz=".$this->db_add_param($nationengruppe_kurzbz).';';

		if(!$this->db_query($qry))
		{
			$this->errormsg = 'Fehler bei einer Datenbankabfrage';
			return false;
		}

		if($row = $this->db_fetch_object())
		{
			$this->nationengruppe_kurzbz = $row->nationengruppe_kurzbz;
			$this->nationengruppe_bezeichnung = $row->nationengruppe_bezeichnung;
			$this->aktiv = $this->db_parse_bool($row->aktiv);
		}
		else
		{
			$this->errormsg = 'Kein Datensatz vorhanden!';
			return false;
		}
		return true;
	}

	/**
	 * Laedt alle Nationengruppen
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function getAll()
	{
		$qry = "SELECT * FROM bis.tbl_nationengruppe;";

		if (!$this->db_query($qry))
		{
			$this->errormsg = 'Fehler bei einer Datenbankabfrage';
			return false;
		}

		while ($row = $this->db_fetch_object())
		{
			$nationengruppe = new nationengruppe();

			$nationengruppe->nationengruppe_kurzbz = $row->nationengruppe_kurzbz;
			$nationengruppe->nationengruppe_bezeichnung = $row->nationengruppe_bezeichnung;
			$nationengruppe->aktiv = $this->db_parse_bool($row->aktiv);

			$this->nationengruppe[] = $nationengruppe;
		}

		return true;
	}

	/**
	 * Speichert die Nationengruppe
	 * @return true wenn erfolgreich, false im Fehlerfall
	 */
	public function save()
	{
		$qry='INSERT INTO bis.tbl_nationengruppe (nationengruppe_kurzbz, nationengruppe_bezeichnung, aktiv) VALUES('.
			$this->db_add_param($this->nationengruppe_kurzbz).', '.
			$this->db_add_param($this->nationengruppe_bezeichnung).', '.
			$this->db_add_param($this->aktiv, FHC_BOOLEAN).');';

		if($this->db_query($qry))
		{
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern des Nationengruppe-Datensatzes:'.$this->nationengruppe_kurzbz.' '.$qry;
			return false;
		}
	}
}
