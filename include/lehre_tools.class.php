<?php
/* Copyright (C) 2012 FH Technikum-Wien
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
 * Authors: Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>
 */
/**
 * Klasse Lehre Tools
 * Verwaltet externe Tools die im CIS zusätzlich pro Organisationseinheit angezeigt werden
 */
require_once(dirname(__FILE__).'/basis_db.class.php');
require_once(dirname(__FILE__).'/sprache.class.php');

class lehre_tools extends basis_db
{
	public $new;				//  boolean
	public $result = array();	//  adresse Objekt

	//Tabellenspalten
	public $lehre_tools_id;		//  integer
	public $bezeichnung;		//  varchar(256) Array
	public $kurzbz; 			//  varchar(32)
	public $basis_url;			//  varchar(256)
	public $logo_dms_id;		//  integer
	
	public $oe_kurzbz;         	//  varchar(32)
	public $aktiv;				//  boolean

	/**
	 * Konstruktor
	 * @param $lehre_tools_id ID die geladen werden soll (Default=null)
	 */
	public function __construct($lehre_tools_id=null)
	{
		parent::__construct();
		
		if(!is_null($lehre_tools_id))
			$this->load($lehre_tools_id);
	}

	/**
	 * Laedt Datensatz mit der ID $lehre_tools_id
	 * @param  $lehre_tools_id ID des zu ladenden Datensatzes
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function load($lehre_tools_id)
	{
		//Pruefen ob lehre_tools_id eine gueltige Zahl ist
		if(!is_numeric($lehre_tools_id) || $lehre_tools_id == '')
		{
			$this->errormsg = 'id muss eine Zahl sein';
			return false;
		}

		$sprache = new sprache();
		$bezeichnung = $sprache->getSprachQuery('bezeichnung');
		
		//Daten aus der Datenbank lesen
		$qry = "SELECT *, $bezeichnung FROM campus.tbl_lehre_tools WHERE lehre_tools_id=".$this->db_add_param($lehre_tools_id, FHC_INTEGER, false);

		if(!$this->db_query($qry))
		{
			$this->errormsg = 'Fehler bei einer Datenbankabfrage';
			return false;
		}

		if($row = $this->db_fetch_object())
		{
			$this->lehre_tools_id = $row->lehre_tools_id;
			$this->bezeichnung = $sprache->parseSprachResult('bezeichnung', $row);
			$this->kurzbz = $row->bezeichnung;
			$this->basis_url = $row->basis_url;
			$this->logo_dms_id = $row->logo_dms_id;
		}
		else
		{
			$this->errormsg = 'Es ist kein Datensatz mit dieser ID vorhanden';
			return false;
		}

		return true;
	}

	/**
	 * 
	 * Laedt die Tools zu einer Lehrveranstaltung
	 * @param $lehrveranstaltung_id
	 * @param $studiensemester_kurzbz
	 */
	public function getTools($lehrveranstaltung_id, $studiensemester_kurzbz)
	{
		$sprache = new sprache();
		$bezeichnung = $sprache->getSprachQuery('bezeichnung');
		$qry = "SELECT 
					*, $bezeichnung
				FROM 
					campus.tbl_lehre_tools
					JOIN campus.tbl_lehre_tools_organisationseinheit USING(lehre_tools_id)
				WHERE
					campus.tbl_lehre_tools_organisationseinheit.aktiv AND
					(
						oe_kurzbz IN(		
							SELECT 
								tbl_studiengang.oe_kurzbz
							FROM
								lehre.tbl_lehrveranstaltung
								JOIN public.tbl_studiengang USING(studiengang_kz)
							WHERE
								tbl_lehrveranstaltung.lehrveranstaltung_id=".$this->db_add_param($lehrveranstaltung_id)."
							)
						OR
						oe_kurzbz IN( 
							SELECT 
								lehrfach.oe_kurzbz
							FROM
								lehre.tbl_lehreinheit
								JOIN lehre.tbl_lehrveranstaltung as lehrfach ON(lehrfach_id=lehrfach.lehrveranstaltung_id)
							WHERE
								tbl_lehreinheit.studiensemester_kurzbz=".$this->db_add_param($studiensemester_kurzbz)."
								AND tbl_lehreinheit.lehrveranstaltung_id=".$this->db_add_param($lehrveranstaltung_id)."
							)
					)
					ORDER BY lehre_tools_id";
		
		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				$obj = new lehre_tools();
				
				$obj->lehre_tools_id = $row->lehre_tools_id;
				$obj->bezeichnung = $sprache->parseSprachResult('bezeichnung', $row);
				$obj->kurzbz = $row->kurzbz;
				$obj->basis_url = $row->basis_url;
				$obj->logo_dms_id = $row->logo_dms_id;
				
				$this->result[] = $obj;
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
?>
