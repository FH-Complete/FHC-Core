<?php
/* Copyright (C) 20011 FH Technikum-Wien
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
 * Klasse Ampel
 *  
 */
require_once(dirname(__FILE__).'/basis_db.class.php');

class ampel extends basis_db
{
	public $new;
	public $result = array();

	//Tabellenspalten
	public $ampel_id;		// bigint
	public $kurzbz;			// varchar(64)
	public $beschreibung;	// text
	public $benutzer_select;// text
	public $deadline;		// date
	public $vorlaufzeit;	// smallint
	public $verfallszeit;	// smallint
	public $insertamum;		// timestamp
	public $insertvon;		// varchar(32)
	public $updateamum;		// timestamp
	public $updatevon;		// varchar(32)
	
	public $ampel_benutzer_id;	// bigint
	public $uid;				// varchar(32)

	/**
	 * Konstruktor - Laedt optional eine Ampel
	 * @param $amepl_id
	 */
	public function __construct($ampel_id=null)
	{
		parent::__construct();
		
		if(!is_null($ampel_id))
			$this->load($ampel_id);
	}

	/**
	 * Laedt eine Ampel mit der uebergebenen ID
	 * 
	 * @param $ampel_id
	 * @return boolean
	 */
	public function load($ampel_id)
	{
		if(!is_numeric($ampel_id))
		{
			$this->errormsg = 'Ampel ID ist ungueltig';
			return false;
		}
		
		$qry = "SELECT * FROM public.tbl_ampel WHERE ampel_id='".addslashes($ampel_id)."'";

		if($result = $this->db_query($qry))
		{
			if($row = $this->db_fetch_object($result))
			{
				$this->ampel_id = $row->ampel_id;
				$this->kurzbz = $row->kurzbz;
				$this->beschreibung = $row->beschreibung;
				$this->benutzer_select = $row->benutzer_select;
				$this->deadline = $row->deadline;
				$this->vorlaufzeit = $row->vorlaufzeit;
				$this->verfallszeit = $row->verfallszeit;
				$this->insertamum = $row->insertamum;
				$this->insertvon = $row->insertvon;
				$this->updateamum = $row->updateamum;
				$this->updatevon = $row->updatevon;

				return true;
			}
			else
			{
				$this->errormsg = 'Ampel mit dieser ID exisitert nicht';
				return false;
			}
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Ampel';
			return false;
		}
	}
	
	/**
	 * Laedt alle vorhandenen Ampeln
	 */
	public function getAll()
	{
		$qry = "SELECT * FROM public.tbl_ampel ORDER BY deadline";
		
		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				$obj = new ampel();
					
				$obj->ampel_id = $row->ampel_id;
				$obj->kurzbz = $row->kurzbz;
				$obj->beschreibung = $row->beschreibung;
				$obj->benutzer_select = $row->benutzer_select;
				$obj->deadline = $row->deadline;
				$obj->vorlaufzeit = $row->vorlaufzeit;
				$obj->verfallszeit = $row->verfallszeit;
				$obj->insertamum = $row->insertamum;
				$obj->insertvon = $row->insertvon;
					
				$this->result[] = $obj;
			}
			return true;
		}
		else
		{
			$this->errormsg='Fehler beim Laden der Daten';
			return false;
		}
	}

	/**
	 * Prueft ob ein User eine Ampel schon bestaetigt hat
	 * 
	 * @param $user
	 * @param $ampel_id
	 * @return boolean
	 */
	public function isBestaetigt($user, $ampel_id)
	{
		$qry = "SELECT 1 FROM public.tbl_ampel_benutzer_bestaetigt WHERE ampel_id='".addslashes($ampel_id)."' AND uid='".addslashes($user)."'";
		
		if($result = $this->db_query($qry))
		{
			if($this->db_num_rows($result)>0)
				return true;
			else
				return false;
		}
		else
		{
			$this->errormsg='Fehler beim Laden der Daten';
			return false;
		}
	}
	
	/**
	 * Prueft ob ein User zu einer Ampel zugeteilt ist
	 * @param $user
	 * @param $benutzer_select
	 */
	public function isZugeteilt($user, $benutzer_select)
	{
		$qry = "SELECT CASE WHEN '".addslashes($user)."' IN (".$row->benutzer_select.") THEN true ELSE false END as zugeteilt";
		if($result_zugeteilt = $this->db_query($qry))
		{
			if($row_zugeteilt = $this->db_fetch_object($result_zugeteilt))
			{
				if($row_zugeteilt->zugeteilt=='t')
					return true;
				else
					return false;
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
	 * Laedt alle aktuellen Ampeln eines Users
	 * @param $user
	 */
	public function loadUserAmpel($user)
	{
		$qry = "SELECT * FROM public.tbl_ampel WHERE deadline+verfallszeit>now() AND deadline-vorlaufzeit<now()";
		
		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				if($this->isZugeteilt($user, $row->benutzer_select))
				{
					$obj = new ampel();
					
					$obj->ampel_id = $row->ampel_id;
					$obj->kurzbz = $row->kurzbz;
					$obj->beschreibung = $row->beschreibung;
					$obj->benutzer_select = $row->benutzer_select;
					$obj->deadline = $row->deadline;
					$obj->vorlaufzeit = $row->vorlaufzeit;
					$obj->verfallszeit = $row->verfallszeit;
					$obj->insertamum = $row->insertamum;
					$obj->insertvon = $row->insertvon;
					
					$this->result[] = $obj;
				}
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
	 * Speichert eine Ampel
	 * @param $new
	 */
	public function save($new=null)
	{
		if(is_null($new))
			$new = $this->new;
			
		if($this->new)
		{
			$qry = "BEGIN;INSERT INTO public.tbl_ampel (kurzbz, beschreibung, benutzer_select, deadline, 
					vorlaufzeit, verfallszeit, insertamum, insertvon , updateamum, updatevon) VALUES(".
					$this->addslashes($this->kurzbz).','.
					$this->addslashes($this->beschreibung).','.
					$this->addslashes($this->benutzer_select).','.
					$this->addslashes($this->deadline).','.
					$this->addslashes($this->vorlaufzeit).','.
					$this->addslashes($this->verfallszeit).','.
					$this->addslashes($this->insertamum).','.
					$this->addslashes($this->insertvon).','.
					$this->addslashes($this->updateamum).','.
					$this->addslashes($this->updatevon).');';
		}
		else
		{
			$qry = 'UPDATE public.tbl_ampel SET'.
					' kurzbz = '.$this->addslashes($this->kurzbz).','.
					' beschreibung = '.$this->addslashes($this->beschreibung).','.
					' benutzer_select = '.$this->addslashes($this->benutzer_select).','.
					' deadline = '.$this->addslashes($this->deadline).','.
					' vorlaufzeit = '.$this->addslashes($this->vorlaufzeit).','.
					' verfallszeit = '.$this->addslashes($this->verfallszeit).','.
					' updateamum ='.$this->addslashes($this->updateamum).','.
					' updatevon ='.$this->addslashes($this->updatevon).
					' WHERE ampel_id='.$this->addslashes($this->ampel_id).';';					
		}
		
		if($this->db_query($qry))
		{
			if($new)
			{
				$qry = "SELECT currval('public.seq_ampel_ampel_id') as id";
				if($result = $this->db_query($qry))
				{
					if($row = $this->db_fetch_object($result))
					{
						$this->ampel_id = $row->id;
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
				return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern der Daten';
			return false;
		}
	}
	
	/**
	 * Loescht eine Ampel
	 
	 * @param $ampel_id
	 */
	public function delete($ampel_id)
	{
		if(!is_numeric($ampel_id))
		{
			$this->errormsg='ID ist ungueltig';
			return false;
		}
		$qry = "DELETE FROM public.tbl_ampel WHERE ampel_id='".addslashes($ampel_id)."'";
		
		if($this->db_query($qry))
			return true;
		else
		{
			$this->errormsg = 'Fehler beim Loeschen der Ampel';
			return false;
		}
	}	
		
}
?>