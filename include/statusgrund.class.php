<?php
/* Copyright (C) 2017 fhcomplete.org
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
 			Manuela Thamer <manuela.thamer@technikum-wien.at>
 */
require_once('basis_db.class.php');

class statusgrund extends basis_db
{
	public $new;      // boolean
	public $result = array(); // statusgrund Objekt

	//Tabellenspalten
	public $statusgrund_id; // integer
	public $status_kurzbz;	// varchar(20)
	public $aktiv = true;   // boolean
	public $bezeichnung_mehrsprachig; // varchar(255)[]
	public $bezeichnung;    // text[]
	public $statusgrund_kurzbz; //varchar(32)

	/**
	 * Konstruktor - Laedt optional einen Statusgrund
	 *
	 * @param $statusgrund_id Statusgrund der geladen werden soll (default=null)
	 */
	public function __construct($statusgrund_id=null)
	{
		parent::__construct();

		if($statusgrund_id != null)
			$this->load($statusgrund_id);
	}

	/**
	 * Laedt den Statusgrund
	 *
	 * @param $statusgrund_id Grund der geladen werden soll
	 */
	public function load($statusgrund_id)
	{
		$sprache = new sprache();
		$bezeichnung_mehrsprachig = $sprache->getSprachQuery('bezeichnung_mehrsprachig');
		$beschreibung = $sprache->getSprachQuery('beschreibung');
		$qry = "
			SELECT
				*,".$bezeichnung_mehrsprachig.",".$beschreibung."
			FROM
				public.tbl_status_grund
			WHERE
				statusgrund_id=".$this->db_add_param($statusgrund_id, FHC_INTEGER);

		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				$this->statusgrund_id = $row->statusgrund_id;
				$this->status_kurzbz = $row->status_kurzbz;
				$this->aktiv = $this->db_parse_bool($row->aktiv);
				$this->bezeichnung_mehrsprachig = $sprache->parseSprachResult('bezeichnung_mehrsprachig', $row);
				$this->beschreibung = $sprache->parseSprachResult('beschreibung', $row);
				$this->statusgrund_kurzbz = $row->statusgrund_kurzbz;
			}
			else
			{
				$this->errormsg = "Es ist kein Eintrag mit dieser ID vorhanden";
				return false;
			}
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Daten';
			return false;
		}

		return true;
	}

	/**
	 * Laedt die Gruende fuer einen Status
	 *
	 * @param $status_kurzbz Status zu dem die Gruende geladen werden sollen
	 * @param boolean $aktiv Gibt an ob nur aktive Eintraege geladen werden sollen
	 */
	public function getFromStatus($status_kurzbz, $aktiv=null)
	{
		$sprache = new sprache();
		$bezeichnung_mehrsprachig = $sprache->getSprachQuery('bezeichnung_mehrsprachig');
		$beschreibung = $sprache->getSprachQuery('beschreibung');
		$qry = "
			SELECT
				*,".$bezeichnung_mehrsprachig.",".$beschreibung."
			FROM
				public.tbl_status_grund
			WHERE
				status_kurzbz=".$this->db_add_param($status_kurzbz);
		if(!is_null($aktiv))
			$qry.=" AND aktiv=".($aktiv?'true':'false');
		$qry.=" ORDER BY bezeichnung_mehrsprachig[0]";

		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$obj = new statusgrund();

				$obj->statusgrund_id = $row->statusgrund_id;
				$obj->status_kurzbz = $row->status_kurzbz;
				$obj->aktiv = $this->db_parse_bool($row->aktiv);
				$obj->bezeichnung_mehrsprachig = $sprache->parseSprachResult('bezeichnung_mehrsprachig', $row);
				$obj->beschreibung = $sprache->parseSprachResult('beschreibung', $row);
				$obj->statusgrund_kurzbz = $row->statusgrund_kurzbz;

				$this->result[] = $obj;
			}
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Daten';
			return false;
		}

		return true;
	}

	/**
	 * Laedt die Statusgruende
	 *
	 * @param boolean $aktiv Wenn true werden nur aktive geladen.
	 */
	public function getAll($aktiv=null)
	{
		$sprache = new sprache();
		$bezeichnung_mehrsprachig = $sprache->getSprachQuery('bezeichnung_mehrsprachig');
		$beschreibung = $sprache->getSprachQuery('beschreibung');
		$qry = "
			SELECT
				*,".$bezeichnung_mehrsprachig.",".$beschreibung."
			FROM
				public.tbl_status_grund
			";

		if(!is_null($aktiv))
			$qry.="WHERE aktiv=".($aktiv?'true':'false');
		$qry.=" ORDER BY status_kurzbz, bezeichnung_mehrsprachig[0]";

		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$obj = new statusgrund();

				$obj->statusgrund_id = $row->statusgrund_id;
				$obj->status_kurzbz = $row->status_kurzbz;
				$obj->aktiv = $this->db_parse_bool($row->aktiv);
				$obj->bezeichnung_mehrsprachig = $sprache->parseSprachResult('bezeichnung_mehrsprachig', $row);
				$obj->beschreibung = $sprache->parseSprachResult('beschreibung', $row);
				$obj->statusgrund_kurzbz = $row->statusgrund_kurzbz;

				$this->result[] = $obj;
			}
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Daten';
			return false;
		}

		return true;
	}

	/**
	 * Laedt das Klassenobjekt anhand der kurzbz
	 *
	 * @param string $statusgrund_kurzbz Statusgrund zu dem das Objekt geladen werden soll.
	 * @return object classobject
	 */
	public function getByStatusgrundKurzbz($statusgrund_kurzbz)
	{
		$qry = "
			SELECT
				 *
			FROM
				 public.tbl_status_grund
			WHERE
				statusgrund_kurzbz ='". $statusgrund_kurzbz. "'
		";

		$this->db_query($qry);

		return
			$this->db_fetch_object();
	}
}
?>
