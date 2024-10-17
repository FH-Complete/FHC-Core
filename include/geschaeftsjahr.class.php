<?php
/* Copyright (C) 2010 FH Technikum Wien
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
 *          Karl Burkhart <karl.burkhart@technikum-wien.at>.
 */
require_once('basis_db.class.php');
require_once('datum.class.php'); 

class geschaeftsjahr extends basis_db
{
	public $new;      // boolean
	public $result = array(); // studiensemester Objekt

	//Tabellenspalten
	public $geschaeftsjahr_kurzbz; // varchar(32)
	public $start; // date
	public $ende;  // date
	public $bezeichnung;

	/**
	 * Konstruktor - Laedt optional ein Geschaeftsjahr
	 * 
	 * @param $geschaeftsjahr_kurzbz Geschaeftsjahr das geladen werden soll (default=null)
	 */
	public function __construct($geschaeftsjahr_kurzbz=null)
	{
		parent::__construct();
		
		if($geschaeftsjahr_kurzbz != null)
			$this->load($geschaeftsjahr_kurzbz);
	}

	/**
	 * Laedt das Geschaeftsjahr mit der uebergebenen Kurzbz
	 * 
	 * @param $geschaeftsjahr_kurzbz Geschaeftsjahr das geladen werden soll
	 */
	public function load($geschaeftsjahr_kurzbz)
	{
		$qry = "SELECT * FROM public.tbl_geschaeftsjahr WHERE geschaeftsjahr_kurzbz=".$this->db_add_param($geschaeftsjahr_kurzbz).';';

		if(!$this->db_query($qry))
		{
			$this->errormsg = 'Fehler beim Lesen des Studiensemesters';
			return false;
		}

		if($row = $this->db_fetch_object())
		{
			$this->geschaeftsjahr_kurzbz = $row->geschaeftsjahr_kurzbz;
			$this->start = $row->start;
			$this->ende = $row->ende;
			$this->bezeichnung = $row->bezeichnung;
		}
		else
		{
			$this->errormsg = "Geschaeftsjahr nicht gefunden";
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
		if(mb_strlen($this->geschaeftsjahr_kurzbz)>32)
		{
			$this->errormsg = 'Geschaeftsjahr Kurzbezeichnung darf nicht laenger als 32 Zeichen sein';
			return false;
		}
		if($this->geschaeftsjahr_kurzbz=='')
		{
			$this->errormsg = 'Es muss eine Kurzbezeichnung eingegeben werden';
			return false;
		}
		return true;
	}

	/**
	 * Speichert das Geschaeftsjahr in die Datenbank
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
			$qry = "INSERT INTO public.tbl_geschaeftsjahr (geschaeftsjahr_kurzbz, start, ende, bezeichnung)
			        VALUES(".$this->db_add_param($this->geschaeftsjahr_kurzbz).",".
					$this->db_add_param($this->start).','.
					$this->db_add_param($this->ende).'.'.
					$this->db_add_param($this->bezeichnung).');';
		}
		else
		{
			$qry = 'UPDATE public.tbl_geschaeftsjahr SET'.
			       ' start='.$this->db_add_param($this->start).','.
			       ' ende='.$this->db_add_param($this->ende).','.
				   ' bezeichnung='.$this->db_add_param($this->bezeichnung).
			       " WHERE geschaeftsjahr_kurzbz=".$this->db_add_param($this->geschaeftsjahr_kurzbz).';';
		}

		if($this->db_query($qry))
		{
			//Log schreiben
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern des Geschaeftsjahres';
			return false;
		}
	}

	/**
	 * Liefert das aktuelle Geschaeftsjahr
	 * 
	 * @return aktuelles Geschaeftsjahr oder false wenn es keines gibt
	 */
	public function getakt()
	{
		$qry = "SELECT geschaeftsjahr_kurzbz FROM public.tbl_geschaeftsjahr WHERE start <= CURRENT_DATE
		 AND ende >= CURRENT_DATE;";

		if(!$this->db_query($qry))
		{
			$this->errormsg = $this->db_last_error();
			return false;
		}

		if($this->db_num_rows()>0)
		{
		   $erg = $this->db_fetch_object();
		   return $erg->geschaeftsjahr_kurzbz;
		}
		else
		{
			$this->errormsg = "Kein aktuelles Geschaeftsjahr vorhanden";
			return false;
		}
	}
	
	/**
	 * Liefert alle Geschaeftsjahre
	 *
	 * @return true wenn ok, sonst false
	 */
	public function getAll()
	{
		$qry = "SELECT * FROM public.tbl_geschaeftsjahr ORDER BY ende;";

		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$obj = new geschaeftsjahr();

				$obj->geschaeftsjahr_kurzbz = $row->geschaeftsjahr_kurzbz;
				$obj->start = $row->start;
				$obj->ende = $row->ende;
				$obj->bezeichnung = $row->bezeichnung;

				$this->result[] = $obj;
			}
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Geschaeftsjahre';
			return false;
		}
	}

	/**
	 * Liefert das vorige Geschaeftsjahr
	 * 
	 * @return geschaeftsjahr_kurzbz oder false wenn keines vorhanden
	 */
	public function getPrevious()
	{
		$qry = "SELECT geschaeftsjahr_kurzbz FROM public.tbl_geschaeftsjahr WHERE ende<now() ORDER BY ende DESC LIMIT 1;";

		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				return $row->geschaeftsjahr_kurzbz;
			}
			else
			{
				$this->errormsg = 'Es wurde kein vorangegangenes Geschaeftsjahr gefunden';
				return false;
			}
		}
		else
		{
			$this->errormsg = 'Fehler beim Ermitteln des vorangegangenen Geschaeftsjahr';
			return false;
		}
	}
	
	/**
	 * 
	 * Liefert das Geschäftsjahr eines Datums zurück
	 * @param $datum
	 */
	public function getSpecific($datum)
	{
		$date = new datum; 
		$newDatum = $date->formatDatum($datum,'Y-m-d');
		
		$qry = "SELECT * FROM public.tbl_geschaeftsjahr 
		WHERE ".$this->db_add_param($newDatum)." >= public.tbl_geschaeftsjahr.start AND 
		".$this->db_add_param($newDatum)." <= public.tbl_geschaeftsjahr.ende;";
		
		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				return $row->geschaeftsjahr_kurzbz; 
			}
		}
		return false; 
	}
}
?>