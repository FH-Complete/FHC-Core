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
 * Klasse ferien (FAS-Online)
 * @create 07-12-2006
 */
require_once(dirname(__FILE__).'/basis_db.class.php');

class ferien extends basis_db 
{
	public $new;     			// boolean
	public $ferien = array(); 	// ferien Objekt

	//Tabellenspalten
	public $bezeichnung;		// varchar(64)
	public $studiengang_kz;		// integer
	public $vondatum;			// date
	public $bisdatum;			// date
	public $vontimestamp;
	public $bistimestamp;

	/**
	 * Konstruktor
	 * @param $bezeichnung und studiengang_kz ID der zu ladenden Ferien
	 */
	public function __construct($bezeichnung=null, $studiengang_kz=null)
	{
		parent::__construct();
		
		if($bezeichnung!=null && $studiengang_kz!=null && is_numeric($studiengang_kz))
			$this->load($bezeichnung, studiengang_kz);
	}

	/**
	 * Laedt alle verfuegbaren Feriendaten
	 * @param $stg_kz default = 0
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function getAll($stg_kz=0)
	{
		if(!is_numeric($stg_kz))
		{
			$this->errormsg = 'Studiengang_kz ist ungültig';
			return false;
		}
		
		$sql_query="SELECT * FROM lehre.tbl_ferien WHERE studiengang_kz=0 OR studiengang_kz=".$this->db_add_param($stg_kz, FHC_INTEGER)." ORDER BY vondatum;";
		
		if (!$this->db_query($sql_query))
		{
			$this->errormsg = $this->db_last_error();
			return false;
		}
		
		while ($row = $this->db_fetch_object())
		{
			// Record holen
			// Instanz erzeugen
			$f = new ferien();
			$f->bezeichnung=$row->bezeichnung;
			$f->studiengang_kz = $row->studiengang_kz;
			$f->vondatum=$row->vondatum;
			$f->bisdatum=$row->bisdatum;
			$f->vontimestamp=mktime(0,0,0,mb_substr($row->vondatum,5,2),mb_substr($row->vondatum,8),mb_substr($row->vondatum,0,4));
			$f->bistimestamp=mktime(23,59,59,mb_substr($row->bisdatum,5,2),mb_substr($row->bisdatum,8),mb_substr($row->bisdatum,0,4));
			// in array speichern
			$this->ferien[]=$f;
		}
		return true;
	}

	/**
	 * Laedt einen Feriendatensatz
	 * @param $bezeichnung, studiengang_kz ID der zu ladenden Ferien
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function load($bezeichnung, $studiengang_kz)
	{
		if($studiengang_kz == '' || !is_numeric($studiengang_kz) || $bezeichnung=='')
		{
			$this->errormsg = 'ID ungültig';
			return false;
		}

		$qry = "SELECT * FROM lehre.tbl_ferien WHERE bezeichnung = ".$this->db_add_param($this->bezeichnung)." 
				AND studiengang_kz = ".$this->db_add_param($this->studiengang_kz, FHC_INTEGER).";";

		if(!$this->db_query($qry))
		{
			$this->errormsg = 'Fehler beim Laden des Datensatzes';
			return false;
		}

		if($row = $this->db_fetch_object())
		{
			$this->bezeichnung		= $row->bezeichnung;
			$this->studiengang_kz	= $row->studiengang_kz;
			$this->vondatum		= $row->vondatum;
			$this->bisdatum		= $row->bisdatum;
		}
		else
		{
			$this->errormsg = 'Es ist kein Datensatz mit dieser ID vorhanden';
			return false;
		}

		return true;
	}
	
	/**
	 * Prueft die Gueltigkeit der Variablen
	 * @return true wenn ok, false im Fehlerfall
	 */
	protected function validate()
	{
		//Laenge Pruefen
		if(mb_strlen($this->bezeichnung)>64)
		{
			$this->errormsg = 'Bezeichnung darf nicht laenger als 64 Zeichen sein';
			return false;
		}
		if($this->studiengang_kz!='')
		{
			$this->errormsg = 'Studiengang_kz muss eingetragen werden';
			return false;
		}
		if($this->bezeichnung=='')
		{
			$this->errormsg = 'Bezeichnung muss eingetragen werden';
			return false;
		}
		
		$this->errormsg = '';
		return true;
	}
	
	/**
	 * Speichert den aktuellen Datensatz
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function save()
	{
		//Gueltigkeit der Variablen pruefen
		if(!$this->validate())
			return false;

		if($this->new)
		{
			//Neuen Datensatz anlegen
			$qry = 'INSERT INTO lehre.tbl_ferien (bezeichnung, studiengang_kz, vondatum, bisdatum) VALUES ('.
				$this->db_add_param($this->bezeichnung).', '.
				$this->db_add_param($this->studiengang_kz, FHC_INTEGER).', '.
				$this->db_add_param($this->vondatum).', '.
				$this->db_add_param($this->bisdatum).'); ';
		}
		else
		{
			//bestehenden Datensatz akualisieren
			$qry = 'UPDATE lehre.tbl_ferien SET '.
				'vondatum='.$this->db_add_param($this->vondatum).', '.
				'bisdatum='.$this->db_add_param($this->bisdatum).
				"WHERE studiengang_kz=".$this->db_add_param($this->studiengang_kz, FHC_INTEGER)." AND bezeichnung=".$this->db_add_param($this->bezeichnung).";";
		}

		if($this->db_query($qry))
		{
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern des Datensatzes';
			return false;
		}
	}

	/**
	 * Prueft ob der uebergebene Timestamp in der Ferienzeit liegt
	 *
	 * @param $timestamp
	 * @return boolean
	 */
	public function isferien($timestamp)
	{
		foreach ($this->ferien AS $f)
			if ($timestamp>=$f->vontimestamp && $timestamp<=$f->bistimestamp)
				return true;
		return false;
	}
	
	/**
	 * Liefert ein Array mit den Ferien zum angegebenen Datum
	 *
	 * @param $timestamp
	 * @return array
	 */
	public function getFerien($timestamp)
	{
		$ret = array();
		
		foreach ($this->ferien AS $f)
			if ($timestamp>=$f->vontimestamp && $timestamp<=$f->bistimestamp)
				$ret[]=$f->bezeichnung;
				
		return $ret;
	}
}
?>