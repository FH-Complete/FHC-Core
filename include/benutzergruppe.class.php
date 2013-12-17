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
require_once(dirname(__FILE__).'/basis_db.class.php');

class benutzergruppe extends basis_db
{
	public $new;
	public $benutzergruppen = array(); // benutzergruppe Objekt
	
	//Tabellenspalten
	public $uid;			// varchar(32)
	public $gruppe_kurzbz;	// varchar(32)
	public $updateamum;		// timestamp
	public $updatevon;		// varchar(32)
	public $insertamum;		// timestamp
	public $insertvon;		// varchar(32)
	public $studiensemester_kurzbz; // varchar(16)
	
	/**
	 * Konstruktor - Laedt optional eine BenutzerGruppe
	 * @param $uid
	 * @param $gruppe_kurzbz
	 */
	public function __construct($uid=null, $gruppe_kurzbz=null)
	{
		parent::__construct();
		
		if(!is_null($gruppe_kurzbz) && !is_null($uid))
			$this->load($uid, $gruppe_kurzbz);
	}
	
	/**
	 * Laedt die BenutzerGruppe
	 * @param uid, gruppe_kurzbz, studiensemester_kurzbz
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function load($uid, $gruppe_kurzbz, $studiensemester_kurzbz=null)
	{
		$qry = "SELECT * FROM public.tbl_benutzergruppe WHERE uid=".$this->db_add_param($uid)." AND gruppe_kurzbz=".$this->db_add_param($gruppe_kurzbz);
		if($studiensemester_kurzbz!=null)
			$qry.=" AND studiensemester_kurzbz=".$this->db_add_param($studiensemester_kurzbz);
		
		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				$this->uid = $row->uid;
				$this->gruppe_kurzbz = $row->gruppe_kurzbz;
				$this->updateamum = $row->updateamum;
				$this->updatevon = $row->updatevon;
				$this->insertamum = $row->insertamum;
				$this->insertvon = $row->insertvon;
				$this->studiensemester_kurzbz = $row->studiensemester_kurzbz;
				return true;
			}
			else 
			{
				$this->errormsg = 'Es wurde kein Datensatz gefunden';
				return false;				
			}
		}
		else 
		{
			$this->errormsg = 'Fehler beim Laden des Datensatzes';
			return false;
		}
	}
	
	/**
	 * Laedt die User in einer Benutzergruppe
	 * @param gruppe_kurzbz, stsem
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function load_uids($gruppe_kurzbz, $stsem)
	{
		$qry = "SELECT * FROM public.tbl_benutzergruppe WHERE gruppe_kurzbz=".$this->db_add_param($gruppe_kurzbz)." and studiensemester_kurzbz = ".$this->db_add_param($stsem);
		
		if($this->db_query($qry))
		{
			if ($this->db_num_rows() == 0)
				return false;
			else
			{			
				while($row = $this->db_fetch_object())
				{
					$bg_obj = new benutzergruppe();
					$bg_obj->uid = $row->uid;
					$this->uids[] = $bg_obj;
				}
				return true;
			}
		}
		else 
		{
			$this->errormsg = 'Fehler beim Laden des Datensatzes';
			return false;
		}
	}	
	
	/**
	 * Prueft die Variablen vor dem Speichern 
	 * auf Gueltigkeit.
	 * @return true wenn ok, false im Fehlerfall
	 */
	protected function validate()
	{
		if(mb_strlen($this->uid)>32)
		{
			$this->errormsg = 'UID darf nich laenger als 32 Zeichen sein';
			return false;
		}
		if(mb_strlen($this->gruppe_kurzbz)>32)
		{
			$this->errormsg = 'Gruppe_kurzbz darf nicht laenger als 32 Zeichen sein';
			return false;
		}
		if(mb_strlen($this->updatevon)>32)
		{
			$this->errormsg = 'Updatevon darf nicht laenger als 32 Zeichen sein';
		}
		if(mb_strlen($this->insertvon)>32)
		{
			$this->errormsg = 'Insertvon darf nicht laenger als 32 Zeichen sein';
			return false;
		}
		
		return true;
	}

	/**
	 * Speichert BenutzerGruppe in die Datenbank
	 * Wenn $new auf true gesetzt ist wird ein neuer Datensatz
	 * angelegt, ansonsten der Datensatz upgedated
	 * @return true wenn erfolgreich, false im Fehlerfall
	 */
	public function save($new=null)
	{
		if(is_null($new))
			$new = $this->new;
					
		//Variablen auf Gueltigkeit pruefen
		if(!$this->validate())
			return false;

		if($new)
		{		
			$qry = 'INSERT INTO public.tbl_benutzergruppe (uid, gruppe_kurzbz, updateamum, updatevon, insertamum, insertvon, studiensemester_kurzbz)
			        VALUES('.$this->db_add_param($this->uid).','.
					$this->db_add_param($this->gruppe_kurzbz).','.
					$this->db_add_param($this->updateamum).','.
					$this->db_add_param($this->updatevon).','.
					$this->db_add_param($this->insertamum).','.
					$this->db_add_param($this->insertvon).','.
					$this->db_add_param($this->studiensemester_kurzbz).');';
		}
		else
		{
			//ToDo
			//$qry = 'Select 1;';
			$this->errormsg = 'Update ist noch nicht implementiert';
			return false;
		}

		if($this->db_query($qry))
		{
			//Log schreiben
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern der BenutzerGruppe';
			return false;
		}
	}
	
	/**
	 * Loescht eine Gruppenzuordnung
	 *
	 * @param $uid
	 * @param $gruppe_kurzbz
	 * @return boolean
	 */
	public function delete($uid, $gruppe_kurzbz)
	{
		$qry = "DELETE FROM public.tbl_benutzergruppe WHERE uid=".$this->db_add_param($uid)." AND gruppe_kurzbz=".$this->db_add_param($gruppe_kurzbz);
		
		if($this->db_query($qry))
			return true;
		else 
		{
			$this->errormsg = 'Fehler beim Loeschen der Zuteilung';
			return false;
		}
	}
}
?>
