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
	public function __construct($load=false)
	{
		parent::__construct();
		if (is_numeric($load))
			$this->load($load);
		elseif($load)
			$this->loadAll();
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
			        VALUES(".$this->db_add_param($this->stunde).",".
					$this->db_add_param($this->beginn).','.
					$this->db_add_param($this->ende).');';
		}
		else
		{
			$qry = 'UPDATE lehre.tbl_stunde SET'.
			       ' beginn='.$this->db_add_param($this->beginn).','.
			       ' ende='.$this->db_add_param($this->ende).
			       " WHERE stunde=".$this->db_add_param($this->stunde);
		}

		if($this->db_query($qry))
		{
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern der Stunde';
			return false;
		}
	}
	
	/**
	 * 
	 * Liefert die Stunden innerhalb der uebergebenen Uhrzeiten
	 * @param $uhrzeit_start
	 * @param $uhrzeit_ende
	 * @return array $stunden
	 */
	public function getStunden($uhrzeit_start, $uhrzeit_ende)
	{
		$stunden=array();
		
		$qry = "SELECT 
					* 
				FROM 
					lehre.tbl_stunde 
				WHERE 
					(beginn BETWEEN ".$this->db_add_param($uhrzeit_start)." AND ".$this->db_add_param($uhrzeit_ende).")
					OR (ende BETWEEN ".$this->db_add_param($uhrzeit_start)." AND ".$this->db_add_param($uhrzeit_ende).");";
		
		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				$stunden[] = $row->stunde;
			}
		} 
		return $stunden;
	}
	
	/**
	 * Laedt eine Stunde
	 * @param $stunde
	 */
	public function load($stunde)
	{
		$qry = "SELECT * FROM lehre.tbl_stunde WHERE stunde=".$this->db_add_param($stunde, FHC_INTEGER).";";
		if($result = $this->db_query($qry))
		{
			if($row = $this->db_fetch_object($result))
			{
				$this->beginn = $row->beginn;
				$this->ende = $row->ende;
				$this->stunde = $row->stunde;
				return true;
			}
			else
			{
				$this->errormsg = 'Stunde wurde nicht gefunden';
				return false;
			}
		}
		else
		{
			$this->errormsg ='Fehler beim Laden der Daten';
			return false;
		}
	}

	/**
	 * Laedt alle Stunden
	 */
	public function loadAll()
	{
		$qry = 'SELECT * FROM lehre.tbl_stunde ORDER BY stunde;';
		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				$stunde = new stunde();
				$beginn = date_create('2001-01-01');
				$ende = date_create('2001-01-01');
				date_time_set($beginn,substr($row->beginn,0,2), substr($row->beginn,3,2));
				date_time_set($ende,substr($row->ende,0,2), substr($row->ende,3,2));
				$stunde->beginn = $beginn;
				$stunde->ende = $ende;
				$stunde->stunde = $row->stunde;
				$this->stunden[] = $stunde;
			}
		}
		else
		{
			$this->errormsg ='Fehler beim Laden der Daten';
			return false;
		}
	}
	
	/**
	 * 
	 * Liefert die Stunde einer uebergebenen Uhrzeiten
	 * @param $uhrzeit
	 * @return integer $stunde->stunde
	 */
	public function getStundeByTime($uhrzeit)
	{
		$time = date_create('2001-01-01');
		if (!date_time_set($time,substr($uhrzeit,0,2), substr($uhrzeit,3,2)))
			return false;
		foreach($this->stunden as $stunde)
		{
			if($time >= $stunde->beginn && $time < $stunde->ende)
			{
				return $stunde->stunde;
			}
		} 
		return false;
	}
}
?>
