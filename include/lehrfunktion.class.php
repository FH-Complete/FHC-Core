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

class lehrfunktion extends basis_db 
{
	public $new;      // @var boolean
	public $lehrfunktionen = array(); // @var lehrfunktion Objekt

	public $lehrfunktion_kurzbz; // @var varchar(16)
	public $beschreibung;        // @var varchar(256)
	public $standardfaktor;      // @var numeric(3,2)

	/**
	 * Konstruktor
	 * @param conn Connection zur DB
	 *        lehrfunktion_kurzbz kurzbezeichnung der zu ladenden Funktion
	 */
	public function __construct($lehrfunktion_kurzbz=null)
	{
		parent::__construct();

		if(!is_null($lehrfunktion_kurzbz))
			$this->load($lehrfunktion_kurzbz);
	}

	/**
	 * Laedt eine Lehrfunktion
	 * @param lehrfunktion_kurzbz ID des Datensatzes der zu laden ist
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function load($lehrfunktion_kurzbz)
	{
		$qry = "SELECT * FROM lehre.tbl_lehrfunktion WHERE lehrfunktion_kurzbz = ".$this->db_add_param($lehrfunktion_kurzbz).";";

		if(!$this->db_query($qry))
		{
			$this->errormsg = 'Fehler beim Laden des Datensatzes';
			return false;
		}

		if($row = $this->db_fetch_object())
		{
			$this->lehrfunktion_kurzbz = $row->lehrfunktion_kurzbz;
			$this->beschreibung = $row->beschreibung;
			$this->standardfaktor = $row->standardfaktor;
		}
		else
		{
			$this->errormsg = 'Es ist kein Datensatz mit dieser ID vorhanden';
			return false;
		}
		return true;
	}

	/**
	 * Laedt alle Lehrfunktionen
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function getAll()
	{
		$qry = "SELECT * FROM lehre.tbl_lehrfunktion ORDER BY lehrfunktion_kurzbz;";

		if(!$this->db_query($qry))
		{
			$this->errormsg = 'Fehler beim Laden der Datensaetze';
			return false;
		}

		while($row = $this->db_fetch_object())
		{
			$lehrfkt_obj = new lehrfunktion();

			$lehrfkt_obj->lehrfunktion_kurzbz = $row->lehrfunktion_kurzbz;
			$lehrfkt_obj->beschreibung = $row->beschreibung;
			$lehrfkt_obj->standardfaktor = $row->standardfaktor;

			$this->lehrfunktionen[] = $lehrfkt_obj;
		}
		return true;
	}
}
?>