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
	public $aktiv=true;
	public $bezeichnung;
	public $insertamum;
	public $insertvon;
	public $updateamum;
	public $updatevon;
		
	public $beschreibung;
	public $url_vorlage;
	
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
		$qry = "SELECT * FROM campus.tbl_freebusy WHERE freebusy_id=".$this->db_add_param($freebusy_id,FHC_INTEGER);

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
			$this->aktiv = $this->db_parse_bool($row->aktiv);
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
		$qry = "SELECT * FROM campus.tbl_freebusy WHERE uid=".$this->db_add_param($uid)." ORDER BY freebusy_id";
		
		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				$obj = new freebusy();
				
				$obj->freebusy_id = $row->freebusy_id;
				$obj->uid = $row->uid;
				$obj->freebusytyp_kurzbz = $row->freebusytyp_kurzbz;
				$obj->url = $row->url;
				$obj->aktiv = $this->db_parse_bool($row->aktiv);
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
	 * Laedt einen Freebusytyp
	 * 
	 * @param $freebusytyp_kurzbz
	 * @return boolean
	 */
	public function loadTyp($freebusytyp_kurzbz)
	{
		$qry = "SELECT * FROM campus.tbl_freebusytyp WHERE freebusytyp_kurzbz=".$this->db_add_param($freebusytyp_kurzbz);
		
		if($result = $this->db_query($qry))
		{
			if($row = $this->db_fetch_object($result))
			{
				$this->freebusytyp_kurzbz = $row->freebusytyp_kurzbz;
				$this->bezeichnung = $row->bezeichnung;
				$this->beschreibung = $row->beschreibung;
				$this->url_vorlage = $row->url_vorlage;
				return true;
			}
			else
			{
				$this->errormsg = 'Fehler beim Laden der Daten';
				return false;
			}
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
				
				$obj->freebusytyp_kurzbz = $row->freebusytyp_kurzbz;
				$obj->bezeichnung = $row->bezeichnung;
				$obj->beschreibung = $row->beschreibung;
				$obj->url_vorlage = $row->url_vorlage;
				
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
	 * Entfernt einen Eintrag aus der Datenbank
	 * 
	 * @param $freebusy_id
	 * @return boolean
	 */
	public function delete($freebusy_id)
	{
		$qry = "DELETE FROM campus.tbl_freebusy WHERE freebusy_id=".$this->db_add_param($freebusy_id, FHC_INTEGER, false);
		
		if($this->db_query($qry))
			return true;
		else
		{
			$this->errormsg = 'Fehler beim Löschen des Eintrages';
			return false;
		}
	}
	
	/**
	 * Speichert die Daten die in die Datenbank
	 * 
	 * @param $new boolean
	 * @return boolean
	 */
	public function save($new=null)
	{
		if(is_null($new))
			$new = $this->new;
			
		if($new)
		{
			$qry = 'BEGIN; INSERT INTO campus.tbl_freebusy(uid, freebusytyp_kurzbz, url, aktiv, bezeichnung, 
					insertamum, insertvon, updateamum, updatevon) VALUES('.
					$this->db_add_param($this->uid).','.
					$this->db_add_param($this->freebusytyp_kurzbz).','.
					$this->db_add_param($this->url).','.
					$this->db_add_param($this->aktiv,FHC_BOOLEAN).','.
					$this->db_add_param($this->bezeichnung).','.
					$this->db_add_param($this->insertamum).','.
					$this->db_add_param($this->insertvon).','.
					$this->db_add_param($this->updateamum).','.
					$this->db_add_param($this->updatevon).');';
		}
		else
		{
			$qry = 'UPDATE campus.tbl_freebusy SET '.
					' uid='.$this->db_add_param($this->uid).','.
					' freebusytyp_kurzbz='.$this->db_add_param($this->freebusytyp_kurzbz).','.
					' url='.$this->db_add_param($this->url).','.
					' aktiv='.$this->db_add_param($this->aktiv, FHC_BOOLEAN).','.
					' bezeichnung='.$this->db_add_param($this->bezeichnung).','.
					' updateamum='.$this->db_add_param($this->updateamum).','.
					' updatevon='.$this->db_add_param($this->updatevon).' '.
					' WHERE freebusy_id='.$this->db_add_param($this->freebusy_id, FHC_INTEGER, false);
		}
		
		if($this->db_query($qry))
		{
			if($new)
			{
				$qry = "SELECT currval('campus.seq_freebusy_freebusy_id') as id";
				if($result = $this->db_query($qry))
				{
					if($row = $this->db_fetch_object($result))
					{
						$this->freebusy_id = $row->id;
						$this->db_query('COMMIT;');
						return true;
					}
					else
					{
						$this->errormsg = 'Fehler beim Auslesen der Sequence';
						$this->db_query('ROLLBACK');
						return false;
					}
				}
				else
				{
					$this->errormsg = 'Fehler beim Auslesen der Sequence';
					$this->db_query('ROLLBACK');
					return false;
				}
			}
			else
			{
				return true;
			}
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern der Daten';
			return false;
		}
	}
	
}
?>