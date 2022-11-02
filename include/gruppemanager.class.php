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
 * Authors: Alexei Karpenko <karpenko@technikum-wien.at>.
 */
require_once(dirname(__FILE__).'/basis_db.class.php');

class gruppemanager extends basis_db
{
	public $new;

	//Tabellenspalten
	public $uid;			// varchar(32)
	public $gruppe_kurzbz;	// varchar(32)
	public $insertamum;		// timestamp
	public $insertvon;		// varchar(32)

	public $uids = array();	// array

	/**
	 * Konstruktor - Laedt optional einen Gruppenmanager
	 * @param $uid
	 * @param $gruppe_kurzbz
	 */
	public function __construct($uid=null, $gruppe_kurzbz=null)
	{
		parent::__construct();

		if(!is_null($gruppe_kurzbz) && !is_null($uid))
			$this->load($uid, $gruppe_kurzbz);
	}

	/**
	 * Laedt die BenutzerGruppe
	 * @param uid, gruppe_kurzbz
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function load($uid, $gruppe_kurzbz)
	{
		$qry = "SELECT * FROM public.tbl_gruppe_manager WHERE uid=".$this->db_add_param($uid)." AND gruppe_kurzbz=".$this->db_add_param($gruppe_kurzbz);

		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				$this->uid = $row->uid;
				$this->gruppe_kurzbz = $row->gruppe_kurzbz;
				$this->insertamum = $row->insertamum;
				$this->insertvon = $row->insertvon;
				return true;
			}
			else
			{
				$this->errormsg = 'Es wurde kein Datensatz gefunden';
				return false;
			}
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden des Datensatzes';
			return false;
		}
	}

	/**
	 * Laedt die Manager einer Benutzergruppe
	 * @param gruppe_kurzbz
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function load_uids($gruppe_kurzbz)
	{
		$qry = "SELECT * FROM public.tbl_gruppe_manager
				WHERE gruppe_kurzbz=".$this->db_add_param($gruppe_kurzbz);

		if ($this->db_query($qry))
		{
			if ($this->db_num_rows() == 0)
				return false;
			else
			{
				while ($row = $this->db_fetch_object())
				{
					$gm_obj = new gruppemanager();
					$gm_obj->uid = $row->uid;
					$this->uids[] = $gm_obj;
				}
				return true;
			}
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden des Datensatzes';
			return false;
		}
	}

	/**
	 * Prueft die Variablen vor dem Speichern
	 * auf Gueltigkeit.
	 * @return true wenn ok, false im Fehlerfall
	 */
	protected function validate()
	{
		if(mb_strlen($this->uid)>32)
		{
			$this->errormsg = 'UID darf nich laenger als 32 Zeichen sein';
			return false;
		}
		if(mb_strlen($this->gruppe_kurzbz)>32)
		{
			$this->errormsg = 'Gruppe_kurzbz darf nicht laenger als 32 Zeichen sein';
			return false;
		}
		if(mb_strlen($this->insertvon)>32)
		{
			$this->errormsg = 'Insertvon darf nicht laenger als 32 Zeichen sein';
			return false;
		}

		return true;
	}

	/**
	 * Speichert GruppeManager in die Datenbank
	 * Wenn $new auf true gesetzt ist wird ein neuer Datensatz
	 * angelegt, ansonsten der Datensatz upgedated
	 * @return true wenn erfolgreich, false im Fehlerfall
	 */
	public function save()
	{
		//Variablen auf Gueltigkeit pruefen
		if(!$this->validate())
			return false;

		$qry = 'INSERT INTO public.tbl_gruppe_manager (uid, gruppe_kurzbz, insertamum, insertvon)
				VALUES('.$this->db_add_param($this->uid).','.
				$this->db_add_param($this->gruppe_kurzbz).','.
				$this->db_add_param($this->insertamum).','.
				$this->db_add_param($this->insertvon).');';

		if($this->db_query($qry))
		{
			//Log schreiben
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern des GruppeManagers';
			return false;
		}
	}

	/**
	 * Loescht eine Gruppenmanagerzuordnung
	 *
	 * @param $uid
	 * @param $gruppe_kurzbz
	 * @return boolean
	 */
	public function delete($uid, $gruppe_kurzbz)
	{
		$qry = "DELETE FROM public.tbl_gruppe_manager WHERE uid=".$this->db_add_param($uid)." AND gruppe_kurzbz=".$this->db_add_param($gruppe_kurzbz);

		if($this->db_query($qry))
			return true;
		else
		{
			$this->errormsg = 'Fehler beim Loeschen der Zuteilung';
			return false;
		}
	}
}
?>
