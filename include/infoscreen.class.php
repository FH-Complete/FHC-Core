<?php
/* Copyright (C) 2011 FH Technikum-Wien
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
 * Authors: Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 */
require_once(dirname(__FILE__).'/basis_db.class.php');

class infoscreen extends basis_db
{
	public $new;
	public $result = array();
	
	//Tabellenspalten
	public $infoscreen_id;
	public $bezeichnung;
	public $beschreibung;
	public $ipadresse;
	
	public $infoscreen_content_id;
	public $content_id;
	public $gueltigvon;
	public $gueltigbis;
	public $insertamum;
	public $insertvon;
	public $updateamum;
	public $updatevon;
	
	/**
	 * Konstruktor
	 * @param infoscreen_id ID des zu ladenden Datensatzes
	 */
	public function __construct($infoscreen_id=null)
	{
		parent::__construct();

		if(!is_null($infoscreen_id))
			$this->load($infoscreen_id);
	}
	
	/**
	 * Laedt einen Datensatz
	 * @param infoscreen_id ID des zu ladenden Datensatzes
	 */
	public function load($infoscreen_id)
	{
		//infoscreen_id auf gueltigkeit pruefen
		if(!is_numeric($infoscreen_id) || $infoscreen_id == '')
		{
			$this->errormsg = 'infoscreen_id muss eine gültige Zahl sein';
			return false;
		}
		
		//laden des Datensatzes
		$qry = "SELECT * FROM campus.tbl_infoscreen WHERE infoscreen_id='".addslashes($infoscreen_id)."';";
		
		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				$this->infoscreen_id = $row->infoscreen_id;
				$this->bezeichnung = $row->bezeichnung;
				$this->beschreibung = $row->beschreibung;
				$this->ipadresse = $row->ipadresse;
				return true;
			}
			else 
			{
				$this->errormsg = 'Fehler bei der Datenbankabfrage';
				return false;
			}
		}
		else
		{
			$this->errormsg = 'Fehler bei der Datenbankabfrage';
			return false;
		}
	}
	
	/**
	 * 
	 * Liefert den Infoscreen anhand der IP-Adresse
	 * @param $ipadresse
	 */
	public function getInfoscreen($ipadresse)
	{
		$qry = "SELECT * FROM campus.tbl_infoscreen WHERE ipadresse='".addslashes($ipadresse)."'";
		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{			
				$this->infoscreen_id = $row->infoscreen_id;
				$this->bezeichnung = $row->bezeichnung;
				$this->beschreibung = $row->beschreibung;
				$this->ipadresse = $row->ipadresse;
				return true;
			}
			else 
			{
				$this->errormsg = 'Fehler bei der Datenbankabfrage';
				return false;
			}
		}
		else
		{
			$this->errormsg = 'Fehler bei der Datenbankabfrage';
			return false;
		}
	}
	
	/**
	 * 
	 * Liefert den Content der am betreffenden Infoscreen angezeigt werden soll
	 * @param $infoscreen_id id des Infoscreens
	 */
	public function getScreenContent($infoscreen_id)
	{
		$qry = "SELECT 
					* 
				FROM 
					campus.tbl_infoscreen_content
				WHERE 
					(infoscreen_id='".addslashes($infoscreen_id)."' OR infoscreen_id is null)
					AND (gueltigvon<=now() OR gueltigvon is null)
					AND (gueltigbis>=now() OR gueltigbis is null)";
		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				$obj = new infoscreen();
				
				$obj->infoscreen_content_id = $row->infoscreen_content_id;
				$obj->infoscreen_id = $row->infoscreen_id;
				$obj->content_id = $row->content_id;
				$obj->gueltigvon = $row->gueltigvon;
				$obj->gueltigbis = $row->gueltigbis;
				$obj->insertamum = $row->insertamum;
				$obj->insertvon = $row->insertvon;
				$obj->updateamum = $row->updateamum;
				$obj->updatevon = $row->updatevon;
				
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