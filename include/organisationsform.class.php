<?php
/* Copyright (C) 2010 Technikum-Wien
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
 *          Karl Burkhart <burkhart@technikum-wien.at>.
 */

/**
 * Klasse Organisationsform
 */

class organisationsform extends basis_db
{
	public $orgform_kurzbz;
	public $code;
	public $bezeichnung;
	public $rolle;
	public $bisorgform_kurzbz;
	public $bezeichnung_mehrsprachig;

	public $result = array();

	/**
	 *
	 * Konstruktor
	 */
	public function __construct($orgform_kurzbz = null)
	{
		parent::__construct();

		if($orgform_kurzbz != null)
			$this->load($orgform_kurzbz);
	}

	/**
	 * Laedt eine Organisationsform
	 * @param $orgform_kurzbz
	 */
	public function load($orgform_kurzbz)
	{
		$sprache = new sprache();
		$bezeichnung_mehrsprachig = $sprache->getSprachQuery('bezeichnung_mehrsprachig');
		$qry = "SELECT 	orgform_kurzbz,
						code,
						bezeichnung,
						rolle,
						bisorgform_kurzbz,
						$bezeichnung_mehrsprachig
 				FROM bis.tbl_orgform 
 				WHERE orgform_kurzbz=".$this->db_add_param($orgform_kurzbz).';';

		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				$this->orgform_kurzbz = $row->orgform_kurzbz;
				$this->code = $row->code;
				$this->bezeichnung = $row->bezeichnung;
				$this->rolle = $this->db_parse_bool($row->rolle);
				$this->bisorgform_kurzbz = $row->bisorgform_kurzbz;
				$this->bezeichnung_mehrsprachig = $sprache->parseSprachResult('bezeichnung_mehrsprachig',$row);
			}
		}
		else
		{
			$this->errormsg ="Fehler bei der Abfrage aufgetreten";
			return false;
		}

        return true;
    }

	/**
	 *
	 * Liefert alle Organisationsformen zurück
	 */
	public function getAll()
	{
		$sprache = new sprache();
		$bezeichnung_mehrsprachig = $sprache->getSprachQuery('bezeichnung_mehrsprachig');
		$qry = "SELECT 	orgform_kurzbz,
						code,
						bezeichnung,
						rolle,
						bisorgform_kurzbz,
						$bezeichnung_mehrsprachig 
				FROM bis.tbl_orgform";

		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$orgform = new organisationsform();

				$orgform->orgform_kurzbz = $row->orgform_kurzbz;
				$orgform->code = $row->code;
				$orgform->bezeichnung = $row->bezeichnung;
				$orgform->rolle = $this->db_parse_bool($row->rolle);
				$orgform->bisorgform_kurzbz = $row->bisorgform_kurzbz;
				$orgform->bezeichnung_mehrsprachig = $sprache->parseSprachResult('bezeichnung_mehrsprachig',$row);

				$this->result[] = $orgform;
			}
			return true;
		}
		else
		{
			$this->errormsg ="Fehler bei der Abfrage aufgetreten";
			return false;
		}
	}

	/**
	*
	* Orgform Kurzbezeichnung wird übergeben und alle passenden Kurzbezeichnungen werden zurückgegeben
	* @param $orgform_kurzbz
	*/
	public function checkOrgForm($orgform_kurzbz)
	{

		if(is_null($orgform_kurzbz))
		{
			$this->errormsg ="Kein gültiger Wert für Orgform Kurzbz.";
			return false;
		}

		switch ($orgform_kurzbz)
		{
			case "VZ":
				$vzArray= array('VZ', '');
				return $vzArray;
			case "BB":
				$bbArray=array('BB','DL','DDP','');
				return $bbArray;
			case "VBB":
				$vbbArray = array('VZ','BB','DDP','DL');
				return $vbbArray;
			default:
				return false;
		}
	}

	/**
	 * Laedt alle Organisationsformen die fuer Lehrveranstaltungen verwendent werden duerfen
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function getOrgformLV()
	{
		$sprache = new sprache();
		$bezeichnung_mehrsprachig = $sprache->getSprachQuery('bezeichnung_mehrsprachig');
		$qry = "SELECT 	orgform_kurzbz,
						code,
						bezeichnung,
						rolle,
						bisorgform_kurzbz,
						$bezeichnung_mehrsprachig 
				FROM bis.tbl_orgform
				WHERE orgform_kurzbz NOT IN ('VBB', 'ZGS')
			  ORDER BY orgform_kurzbz";

		if ($result = $this->db_query($qry))
		{
			while ($row = $this->db_fetch_object($result))
			{
				$orgform = new organisationsform();

				$orgform->orgform_kurzbz = $row->orgform_kurzbz;
				$orgform->code = $row->code;
				$orgform->bezeichnung = $row->bezeichnung;
				$orgform->rolle = $row->rolle;
				$orgform->bisorgform_kurzbz = $row->bisorgform_kurzbz;
				$orgform->bezeichnung_mehrsprachig = $sprache->parseSprachResult('bezeichnung_mehrsprachig',$row);

				$this->result[] = $orgform;
			}
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Daten';
			return false;
		}
	}
}
