<?php
/* Copyright (C) 2009 fhcomplete.org
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
 * Authors: Stefan Puraner <stefan.puraner@technikum-wien.at>,
 */
require_once('basis_db.class.php');

class studienjahr extends basis_db
{
	public $new;      // boolean
	public $result = array(); // studienjahr Objekt

	//Tabellenspalten
	public $studienjahr_kurzbz;// varchar(16)
	public $bezeichnung;			// varchar(64)

	/**
	 * Konstruktor - Laedt optional ein StSem
	 *
	 * @param $studienjahr_kurzbz StSem das geladen werden soll (default=null)
	 */
	public function __construct($studienjahr_kurzbz=null)
	{
		parent::__construct();

		if($studienjahr_kurzbz != null)
			$this->load($studienjahr_kurzbz);
	}

	/**
	 * Laedt das Studienjahr mit der uebergebenen Kurzbz
	 *
	 * @param $studienjahr_kurzbz Stsem das geladen werden soll
	 */
	public function load($studienjahr_kurzbz)
	{
		$qry = "SELECT * FROM public.tbl_studienjahr WHERE studienjahr_kurzbz=".$this->db_add_param($studienjahr_kurzbz);

		if(!$this->db_query($qry))
		{
			$this->errormsg = 'Fehler beim Lesen des Studienjahrs';
			return false;
		}

		if($row = $this->db_fetch_object())
		{
			$this->studienjahr_kurzbz = $row->studienjahr_kurzbz;
			$this->bezeichnung = $row->bezeichnung;
		}
		else
		{
			$this->errormsg = "Es ist kein Studienjahr mit dieser Kurzbezeichung vorhanden";
			return false;
		}

		return true;
	}

	/**
	 * Prueft die Variablen vor dem Speichern
	 * auf Gueltigkeit.
	 *
	 * @return true wenn ok, false im Fehlerfall
	 */
	private function validate()
	{
		if(mb_strlen($this->studienjahr_kurzbz)>16)
		{
			$this->errormsg = 'Studienjahr Kurzbezeichnung darf nicht laenger als 16 Zeichen sein';
			return false;
		}
		if(mb_strlen($this->bezeichnung)>64)
		{
			$this->errormsg = 'Studienjahr Bezeichnung darf nicht laenger als 64 Zeichen sein';
			return false;
		}
		if($this->studienjahr_kurzbz=='')
		{
			$this->errormsg = 'Es muss eine Kurzbezeichnung eingegeben werden';
			return false;
		}
		return true;
	}

	/**
	 * Speichert das Studienjahr in die Datenbank
	 * Wenn $new auf true gesetzt ist wird ein neuer Datensatz
	 * angelegt, ansonsten der Datensatz upgedated
	 *
	 * @return true wenn erfolgreich, false im Fehlerfall
	 */
	public function save()
	{
		//Variablen auf Gueltigkeit pruefen
		if(!$this->validate())
			return false;

		if($this->new)
		{
			$qry = "INSERT INTO public.tbl_studienjahr (studienjahr_kurzbz, bezeichnung)
			        VALUES(".$this->db_add_param($this->studienjahr_kurzbz).",".
					$this->db_add_param($this->bezeichnung).');';
		}
		else
		{
			$qry = 'UPDATE public.tbl_studienjahr SET'.
			       ' start='.$this->db_add_param($this->start).','.
			       ' ende='.$this->db_add_param($this->ende).
			       " WHERE studienjahr_kurzbz=".$this->db_add_param($this->studienjahr_kurzbz);
		}

		if($this->db_query($qry))
		{
			//Log schreiben
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern des Studienjahrs';
			return false;
		}
	}

	/**
	 * Liefert alle Studienjahr
	 *
	 * @return true wenn ok, sonst false
	 */
	public function getAll()
	{
		$qry = "SELECT * FROM public.tbl_studienjahr ORDER BY studienjahr_kurzbz;";

		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$stsem_obj = new studienjahr();

				$stsem_obj->studienjahr_kurzbz = $row->studienjahr_kurzbz;
				$stsem_obj->bezeichnung = $row->bezeichnung;

				$this->result[] = $stsem_obj;
			}
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Studienjahr';
			return false;
		}
	}

	/**
	 * Springt von Studienjahr $studienjahr_kurzbz um $wert Studienjahre vor/zurueck
	 *
	 * @param string $studienjahr_kurzbz.
	 * @param int $wert.
	 * @return string studienjahr_kurzbz
	 */
	public function jump($studienjahr_kurzbz, $wert)
	{
		if($wert>0)
		{
			$op='>=';
			$sort='ASC';
		}
		elseif($wert<0)
		{
			$op='<=';
			$sort='DESC';
		}
		else
		{
			$op='=';
			$sort='';
		}

		$qry = "select distinct(studienjahr_kurzbz)
				FROM tbl_studiensemester
				Where start $op
					(
					SELECT start
					FROM tbl_studiensemester
					WHERE studienjahr_kurzbz = ".$this->db_add_param($studienjahr_kurzbz)."
					ORDER BY start LIMIT 1
					)
				ORDER BY studienjahr_kurzbz $sort
				offset ".abs($wert)."  LIMIT 1";

		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				return $row->studienjahr_kurzbz;
			}
			else
				return $studienjahr_kurzbz;
		}
		else
		{
			$this->errormsg='Fehler bei einer Abfrage';
			return false;
		}
	}
}
?>
