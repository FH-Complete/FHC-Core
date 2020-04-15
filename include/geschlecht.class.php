<?php
/* Copyright (C) 2019 fhcomplete.org
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
 * Authors: Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>,
 */
require_once(dirname(__FILE__).'/basis_db.class.php');
require_once(dirname(__FILE__).'/sprache.class.php');

class geschlecht extends basis_db
{
	public $new;				// boolean
	public $result = array();

	//Tabellenspalten
	public $geschlecht; //  character(1)
	public $bezeichnung_mehrsprachig; //  varchar(255)[]
	public $bezeichnung_mehrsprachig_arr = array();

	/**
	 * Konstruktor
	 * @param $geschlecht ID des Geschlechts das geladen werden soll (Default=null)
	 */
	public function __construct($geschlecht=null)
	{
		parent::__construct();

		if(!is_null($geschlecht))
			$this->load($geschlecht);
	}

	/**
	 * Laedt ein geschlecht
	 * @param  $geschlecht das zu ladende Geschlecht
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function load($geschlecht)
	{
		$sprache = new sprache();

		$qry = "
			SELECT
				*,".$sprache->getSprachQuery('bezeichnung_mehrsprachig')."
			FROM
				public.tbl_geschlecht
			WHERE
				geschlecht=".$this->db_add_param($geschlecht);

		if(!$this->db_query($qry))
		{
			$this->errormsg = 'Fehler bei einer Datenbankabfrage';
			return false;
		}

		if($row = $this->db_fetch_object())
		{
			$this->geschlecht = $row->geschlecht;
			$this->bezeichnung_mehrsprachig_arr = $sprache->parseSprachResult('bezeichnung_mehrsprachig', $row);
		}
		else
		{
			$this->errormsg = 'Es ist kein Datensatz mit dieser ID vorhanden';
			return false;
		}

		return true;
	}

	/**
	 * Laedt alle Geschlechter
	 * @return boolean true wenn erfolgreich, false im Fehlerfall
	 */
	public function getAll()
	{
		$sprache = new sprache();

		$qry = "
			SELECT
				*,".$sprache->getSprachQuery('bezeichnung_mehrsprachig')."
			FROM
				public.tbl_geschlecht
			ORDER BY sort, geschlecht";

		if (!$this->db_query($qry))
		{
			$this->errormsg = 'Fehler bei einer Datenbankabfrage';
			return false;
		}

		while ($row = $this->db_fetch_object())
		{
			$obj = new stdClass();

			$obj->geschlecht = $row->geschlecht;
			$obj->bezeichnung_mehrsprachig_arr = $sprache->parseSprachResult('bezeichnung_mehrsprachig', $row);

			$this->result[] = $obj;
		}

		return true;
	}
}
?>
