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
 * Authors: Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 */
/**
 * Klasse FreeBusy
 * @create 27-01-2012
 */
require_once(dirname(__FILE__).'/basis_db.class.php');

class freebusy extends basis_db
{
	public $new;
	public $result = array();

	public $freebusy_id;
	public $uid;
	public $freebusytyp_kurzbz;
	public $url;
	public $aktiv;
	public $bezeichnung;
	public $insertamum;
	public $insertvon;
	public $updateamum;
	public $updatevon;
	
	public $beschreibung;
	
	/**
	 * Konstruktor
	 * @param $freebusy_id ID der FreeBusy Eintrags der geladen werden soll (Default=null)
	 */
	public function __construct($freebusy_id=null)
	{
		parent::__construct();
		
		if(!is_null($freebusy_id))
			$this->load($freebusy_id);
	}

	/**
	 * Laedt einen FreeBusy Eintrag mit der ID $freebusy_id
	 * @param  freebusy_id
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function load($freebusy_id)
	{
		//Pruefen ob id eine gueltige Zahl ist
		if(!is_numeric($freebusy_id) || $freebusy_id == '')
		{
			$this->errormsg = 'id muss eine Zahl sein';
			return false;
		}

		//Daten aus der Datenbank lesen
		$qry = "SELECT * FROM campus.tbl_freebusy WHERE freebusy_id='".addslashes($freebusy_id)."'";

		if(!$this->db_query($qry))
		{
			$this->errormsg = 'Fehler bei einer Datenbankabfrage';
			return false;
		}

		if($row = $this->db_fetch_object())
		{
			$this->freebusy_id = $row->freebusy_id;
			$this->uid = $row->uid;
			$this->freebusytyp_kurzbz = $row->freebusytyp_kurzbz;
			$this->url = $row->url;
			$this->aktiv = ($row->aktiv=='t'?true:false);
			$this->bezeichnung = $row->bezeichnung;
			$this->insertamum = $row->insertamum;
			$this->insertvon = $row->insertvon;
			$this->updateamum = $row->updateamum;
			$this->updatevon = $row->updatevon;
		}
		else
		{
			$this->errormsg = 'Es ist kein Datensatz mit dieser ID vorhanden';
			return false;
		}

		return true;
	}

	/**
	 * Liefert die FreeBusy Eintraege eines Benutzers
	 * 
	 * @param $uid
	 */
	public function getFreeBusy($uid)
	{
		$qry = "SELECT * FROM campus.tbl_freebusy WHERE uid='".addslashes($uid)."' ORDER BY freebusy_id";
		
		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				$obj = new freebusy();
				
				$obj->freebusy_id = $row->freebusy_id;
				$obj->uid = $row->uid;
				$obj->freebusytyp_kurzbz = $row->freebusytyp_kurzbz;
				$obj->url = $row->url;
				$obj->aktiv = ($row->aktiv=='t'?true:false);
				$obj->bezeichnung = $row->bezeichnung;
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
	
	/**
	 * Laedt die FreeBusyTypen
	 *
	 */
	public function getTyp()
	{
		$qry = "SELECT * FROM campus.tbl_freebusytyp ORDER BY bezeichnung";
		
		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				$obj = new freebusy();
				
				$obj->freebusytyp_kurbz = $row->freebusytyp_kurzbz;
				$obj->bezeichnung = $row->bezeichnung;
				$obj->beschreibung = $row->beschreibung;
				
				$this->result[] = $obj;
			}
			return false;
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Daten';
			return false;
		}
	}
}
?>