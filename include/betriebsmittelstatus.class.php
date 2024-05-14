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
 * Klasse betriebsmittelstatus (FAS-Online)
 * @create 13-01-2007
 */
require_once(dirname(__FILE__).'/basis_db.class.php');

class betriebsmittelstatus extends basis_db
{
	public $new;
	public $result = array();
	
	//Tabellenspalten
	public $betriebsmittelstatus_kurzbz;	//string
	public $beschreibung;					//string
	
	/**
	 * Konstruktor
	 * @param $betriebsmittelstatus
	 */
	public function __construct($betriebsmittelstatus_kurzbz=null)
	{
		parent::__construct();
		
		if($betriebsmittelstatus_kurzbz!=null)
			$this->load($betriebsmittelstatus_kurzbz);
	}
		
	/**
	 * Laedt die Funktion mit der ID $betriebsmittelstatus
	 * @param  $betriebsmittelstatus
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function load($betriebsmittelstatus_kurzbz)
	{	
		$this->result=array();
		$this->errormsg = '';			

		$qry=" SELECT * FROM wawi.tbl_betriebsmittelstatus
				WHERE trim(UPPER(betriebsmittelstatus_kurzbz))=".$this->db_add_param(mb_strtoupper(trim($betriebsmittelstatus_kurzbz)));

		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$bmt = new betriebsmittelstatus();
				$bmt->betriebsmittelstatus_kurzbz = $row->betriebsmittelstatus_kurzbz;
				$bmt->beschreibung = $row->beschreibung;
				$this->result[] = $bmt;
			}
			return $this->result;
		}
		else 
		{
			$this->errormsg = 'Fehler beim Laden der Daten';
			return false;
		}
	}
	/**
	 * Laedt alle betriebsmittelstatus
	 * @return true wenn ok, false wenn Fehler
	 */
	public function getAll()
	{
		$this->result=array();
		$this->errormsg = '';			

		$qry='SELECT * FROM wawi.tbl_betriebsmittelstatus ORDER BY betriebsmittelstatus_kurzbz ';
		
		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$bmt = new betriebsmittelstatus();
				$bmt->betriebsmittelstatus_kurzbz = $row->betriebsmittelstatus_kurzbz;
				$bmt->beschreibung = $row->beschreibung;
				$this->result[] = $bmt;
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
	 * Speichert die Daten in die Datenbank
	 * @return true wenn erfolgreich, false im Fehlerfall
	 */
	public function save()
	{		
		$this->errormsg = '';	
		$qry='';
		if($this->new)
		{
			$qry='INSERT INTO wawi.tbl_betriebsmittelstatus 
			(betriebsmittelstatus_kurzbz, beschreibung ) 
						VALUES('.$this->db_add_param($this->betriebsmittelstatus_kurzbz)
						.','.$this->db_add_param($this->beschreibung).'); ';
		}
		else 
		{
			$qry='UPDATE wawi.tbl_betriebsmittelstatus SET '.
					"beschreibung =".$this->db_add_param($this->beschreibung) .
					" WHERE betriebsmittelstatus_kurzbz=".$this->db_add_param($this->betriebsmittelstatus_kurzbz);
		}
			
		if($this->db_query($qry))
		{
			return true;
		}
		else
		{			
			$this->errormsg = 'Fehler beim speichern des Betriebsmittelstatus-Datensatzes';
			return false;
		}	
	}

	/**
	 * Entfernt die Daten in die Datenbank
	 * @return true wenn erfolgreich, false im Fehlerfall
	 */
	public function delete()
	{		
		$this->errormsg = '';	
		$qry='DELETE FROM wawi.tbl_betriebsmittelstatus '.
			" WHERE betriebsmittelstatus_kurzbz=".$this->db_add_param($this->betriebsmittelstatus_kurzbz);
		
		if($this->db_query($qry))
		{
			return true;
		}
		else
		{			
			$this->errormsg = 'Fehler beim entfernen des Betriebsmittelstatus-Datensatzes';
			return false;
		}	
	}	
}
?>
