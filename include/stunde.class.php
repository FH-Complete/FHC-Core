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

class stunde extends basis_db 
{
	public $new;      // boolean
	public $stunden = array(); // stunde Objekt

	//Tabellenspalten
	public $stunde;	// smalint
	public $beginn;	// time without timezone
	public $ende;	// time without timezone

	/**
	 * Konstruktor
	 */
	public function __construct()
	{
		parent::__construct();
	}


	/**
	 * Prueft die Variablen vor dem Speichern
	 * auf Gueltigkeit.
	 * @return true wenn ok, false im Fehlerfall
	 */
	protected function validate()
	{
		if(!is_numeric($this->stunde))
		{
			$this->errormsg = 'Stunde muss eine gueltige Zahl sein';
			return false;
		}
		return true;
	}

	/**
	 * Speichert eine Stunde in die Datenbank
	 * Wenn $new auf true gesetzt ist wird ein neuer Datensatz
	 * angelegt, ansonsten der Datensatz mit $lehrfach_nr upgedated
	 * @return true wenn erfolgreich, false im Fehlerfall
	 */
	public function save()
	{
		//Variablen auf Gueltigkeit pruefen
		if(!$this->validate())
			return false;

		if($this->new)
		{
			$qry = "INSERT INTO lehre.tbl_stunde (stunde, beginn, ende)
			        VALUES('".$this->stunde."',".
					$this->addslashes($this->beginn).','.
					$this->addslashes($this->ende).');';
		}
		else
		{
			$qry = 'UPDATE lehre.tbl_stunde SET'.
			       ' beginn='.$this->addslashes($this->beginn).','.
			       ' ende='.$this->addslashes($this->ende).
			       " WHERE stunde=".$this->stunde;
		}

		if($this->db_query($qry))
		{
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern der Stunde:'.$qry;
			return false;
		}
	}
}
?>