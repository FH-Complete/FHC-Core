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
 * Klasse betriebsmitteltyp (FAS-Online)
 * @create 13-01-2007
 */
require_once(dirname(__FILE__).'/basis_db.class.php');

class betriebsmitteltyp extends basis_db
{
	public $debug=false;   	// boolean
	public $new;   	// boolean
	public $result = array();
	
	//Tabellenspalten
	public $betriebsmitteltyp;	//string
	public $beschreibung;   	//string
	public $anzahl; 			//smallint
	public $kaution;			//numeric(5,2)
	public $typ_code;			//string(2)	
	
	/**
	 * Konstruktor
	 * @param $betriebsmitteltyp
	 */
	public function __construct($betriebsmitteltyp=null)
	{
		parent::__construct();
		
		if($betriebsmitteltyp!=null)
			$this->load($betriebsmitteltyp);
	}
		
	/**
	 * Laedt die Funktion mit der ID $betriebsmitteltyp
	 * @param  $betriebsmitteltyp
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function load($betriebsmitteltyp)
	{		
		$this->result=array();
		$this->errormsg = '';
		$search = mb_strtoupper(trim(str_replace(array('*',';',' ',"'",'"'),'%',trim($betriebsmitteltyp))));
		$qry=" 
			SELECT * FROM wawi.tbl_betriebsmitteltyp 
			WHERE trim(UPPER(betriebsmitteltyp)) like '%".$this->db_escape($search)."%'
			ORDER BY betriebsmitteltyp";
		
		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$bmt = new betriebsmitteltyp();
				$bmt->betriebsmitteltyp = $row->betriebsmitteltyp;
				$bmt->beschreibung = $row->beschreibung;
				$bmt->anzahl = $row->anzahl;
				$bmt->kaution = $row->kaution;
				$bmt->typ_code = $row->typ_code;
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
	 * Laedt alle BetriebsmittelTypen
	 * @return true wenn ok, false wenn Fehler
	 */
	public function getAll($order='beschreibung')
	{
		$this->result=array();
		$this->errormsg = '';
		$qry = "SELECT * FROM wawi.tbl_betriebsmitteltyp";
		if($order!='')
			$qry.=" ORDER BY ".$order;
		
		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$bmt = new betriebsmitteltyp();
				
				$bmt->betriebsmitteltyp = $row->betriebsmitteltyp;
				$bmt->beschreibung = $row->beschreibung;
				$bmt->anzahl = $row->anzahl;
				$bmt->kaution = $row->kaution;
				$bmt->typ_code = $row->typ_code;
				
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
	 *
	 * @param $new wenn true wird ein Insert durchgefuehrt sonst ein Update
	 * @return boolean
	 */
	public function save($new=null)
	{
		if(is_null($new))
			$new = $this->new;
			
		if($new)
		{
			$qry="INSERT INTO wawi.tbl_betriebsmitteltyp (betriebsmitteltyp, beschreibung, anzahl, kaution , typ_code)
					VALUES(".$this->db_add_param($this->betriebsmitteltyp).",".
						$this->db_add_param($this->beschreibung).",".
						$this->db_add_param($this->anzahl).",".
						$this->db_add_param($this->kaution).",".
						$this->db_add_param($this->typ_code).");";
		}
		else 
		{
			$qry='UPDATE wawi.tbl_betriebsmitteltyp SET '.
				'beschreibung ='.$this->db_add_param($this->beschreibung).', '.
				'anzahl ='.$this->db_add_param($this->anzahl).', '.
				'kaution ='.$this->db_add_param($this->kaution).', '.
				'typ_code ='.$this->db_add_param($this->typ_code).' '.
				'WHERE betriebsmitteltyp='.$this->db_add_param($this->betriebsmitteltyp).'; ' ;	
		}
		
		if($this->db_query($qry))
		{
			return true;
		}
		else
		{			
			$this->errormsg = 'Fehler beim Speichern des Betriebsmitteltypen-Datensatzes';
			return false;
		}	
	}
	
	/**
	 * Speichert die Daten in die Datenbank
	 * @return true wenn erfolgreich, false im Fehlerfall
	 */
	public function check_beschreibung()
	{		
		$this->errormsg = '';	
		$qry='UPDATE wawi.tbl_betriebsmitteltyp SET '.
					'beschreibung = trim(betriebsmitteltyp) '.
					' where beschreibung is null ';
		if($this->db_query($qry))
		{
			return true;
		}
		else
		{			
			$this->errormsg = 'Fehler beim Pruefen der Beschreibung des Betriebsmitteltypen-Datensatzes';
			return false;
		}	
	}
}
?>
