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
require_once(dirname(__FILE__).'/sprache.class.php');

class lehrform extends basis_db
{
	public $new;				// boolean
	public $lehrform = array(); // lehrform Objekt

	//Tabellenspalten
	public $lehrform_kurbz;		// varchar(8)
	public $bezeichnung;		// varchar (256)
	public $verplanen; 			// boolean
	public $bezeichnung_kurz;
	public $bezeichnung_lang;

	/**
	 * Konstruktor - Laedt optional eine Lehrform
	 * @param $lehrform_kurbz Lehrform die geladen werden soll (default=null)
	 */
	public function __construct($lehrform_kurzbz=null)
	{
		parent::__construct();

		if(!is_null($lehrform_kurzbz))
			$this->load($lehrform_kurzbz);
	}

	/**
	 * Laedt Lehrform mit der uebergebenen ID
	 * @param $lehrform_kurzbz Lehrform die geladen werden soll
	 */
	public function load($lehrform_kurzbz)
	{
		$sprache = new sprache();
		$qry = "SELECT *,".$sprache->getSprachQuery('bezeichnung_kurz').",".$sprache->getSprachQuery('bezeichnung_lang')." FROM lehre.tbl_lehrform WHERE lehrform_kurzbz=".$this->db_add_param($lehrform_kurzbz).";";
		
		if(!$this->db_query($qry))
		{
			$this->errormsg = 'Fehler beim Lesen der Lehrform';
			return false;
		}

		if($row = $this->db_fetch_object())
		{
			$this->lehrform_kurzbz = $row->lehrform_kurzbz;
			$this->bezeichnung = $row->bezeichnung;
			$this->verplanen = $this->db_parse_bool($row->verplanen);
			$this->bezeichnung_kurz=$sprache->parseSprachResult('bezeichnung_kurz',$row);
			$this->bezeichnung_lang=$sprache->parseSprachResult('bezeichnung_lang',$row);
		}
		else
		{
			$this->errormsg = 'Es ist keine Lehrform mit der Kurzbz '.$lehrform_kurzbz.' vorhanden';
			return false;
		}

		return true;

	}

	/**
	 * Liefert alle Lehrformen
	 * @return boolean
	 */
	public function getAll()
	{
		$sprache = new sprache();

		$qry = "SELECT *,".$sprache->getSprachQuery('bezeichnung_kurz').",".$sprache->getSprachQuery('bezeichnung_lang')." FROM lehre.tbl_lehrform ORDER BY lehrform_kurzbz;";
		
		if(!$this->db_query($qry))
		{
			$this->errormsg = 'Fehler beim Lesen der Lehrform';
			return false;
		}

		while($row = $this->db_fetch_object())
		{
			$lf = new lehrform();

			$lf->lehrform_kurzbz = $row->lehrform_kurzbz;
			$lf->bezeichnung = $row->bezeichnung;
			$lf->verplanen = $this->db_parse_bool($row->verplanen);
			$lf->bezeichnung_kurz=$sprache->parseSprachResult('bezeichnung_kurz',$row);
			$lf->bezeichnung_lang=$sprache->parseSprachResult('bezeichnung_lang',$row);

			$this->lehrform[] = $lf;
		}

		return true;
	}

	/**
	 * Prueft die Variablen vor dem Speichern
	 * auf Gueltigkeit.
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function validate()
	{
		if(strlen($this->lehrform_kurbz)>8)
		{
			$this->errormsg = 'Lehrform Kurzbezeichnung darf nicht laenger als 8 Zeichen sein.';
			return false;
		}
		if(strlen($this->bezeichnung)>256)
		{
			$this->errormsg = 'Bezeichnung darf nicht laenger als 256 Zeichen sein';
			return false;
		}
		if(!is_bool($this->verplanen))
		{
			$this->errormsg = 'Verplanen muss ein boolscher Wert sein';
			return false;
		}

		return true;
	}

	/**
	 * Speichert die Lehrform in die Datenbank
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
			$qry = "INSERT INTO lehre.tbl_lehrform (lehrform_kurzbz, bezeichnung, verplanen)
			        VALUES(".$this->db_add_param($this->lehrform_kurzbz).",".
					$this->db_add_param($this->bezeichnung).','.
					$this->db_add_param($this->verplanen, FHC_BOOLEAN).');';
		}
		else
		{
			$qry = 'UPDATE lehre.tbl_lehrform SET'.
			       ' bezeichnung='.$this->db_add_param($this->bezeichnung).','.
			       ' verplanen='.$this->db_add_param($this->verplanen, FHC_BOOLEAN).
			       " WHERE lehrform_kurzbz=".$this->db_add_param($this->lehrform_kurzbz).';';
		}

		if($this->db_query($qry))
		{
			//Log schreiben
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern der Lehrform:'.$qry;
			return false;
		}
	}
}
?>
