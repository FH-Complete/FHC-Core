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
require_once(dirname(__FILE__).'/basis_db.class.php');

class fotostatus extends basis_db
{
	public $new;				//  boolean
	public $result = array();	//  Array für FotoStatus Objekte

	//Tabellenspalten
	public $fotostatus_kurzbz;	//  varchar(32)
	public $beschreibung; 		//  varchar(256)
	
	public $person_fotostatus_id; // integer
	public $person_id;			//  integer
	public $datum;				//  date
	public $updateamum;			//  timestamp
	public $updatevon;			//  string
	public $insertamum;      	//  timestamp
	public $insertvon;      	//  string

	/**
	 * Konstruktor
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Prueft ob das Profilfoto der Person bereits akzeptiert wurde
	 * @param $person_id
	 */
	public function akzeptiert($person_id)
	{
		$qry = "SELECT 
					fotostatus_kurzbz
				FROM 
					public.tbl_person_fotostatus 
				WHERE 
					person_id=".$this->db_add_param($person_id)."
				ORDER BY datum desc, person_fotostatus_id desc limit 1";
		if($result = $this->db_query($qry))
		{
			if($row = $this->db_fetch_object($result))
			{
				if($row->fotostatus_kurzbz=='akzeptiert')
					return true;
				else
					return false;
			}
			else
			{
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
     * Überprüft ob der übergebene Status bei der Übergebenen Person vorhanden ist
     * @param $person_id
     * @param $fotostatus_kurzbz
     * @return boolean 
     */
    public function checkStatus($person_id, $fotostatus_kurzbz)
    {
        $qry = "SELECT 
                    *
                FROM
                    public.tbl_person_fotostatus
                WHERE
                    person_id = ".$this->db_add_param($person_id, FHC_INTEGER)."
                    AND fotostatus_kurzbz=".$this->db_add_param($fotostatus_kurzbz,FHC_STRING).";";
        
        if($result = $this->db_query($qry))
        {
            if($this->db_num_rows($result)>0)
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
    
    /**
     * Liefert alle Möglichen Stati eines Bildes zurück
     * @return boolean 
     */
    public function getAllStatusKurzbz()
    {
        $qry = "SELECT 
                    * 
                FROM 
                    tbl_fotostatus";
        
        if($result = $this->db_query($qry))
        {
            while($row  = $this->db_fetch_object($result))
            {
                $status = new fotostatus(); 
                $status->fotostatus_kurzbz = $row->fotostatus_kurzbz; 
                
                $this->result[]=$status; 
            }
            return true; 
        }
        else
        {
            $this->errormsg = "Fehler bei der Abfrage aufgetreten."; 
            return false; 
        }
    }
    
    
	public function save($new=null)
	{
		if(is_null($new))
			$new = $this->new;
			
		if($new)
		{
			$qry = 'BEGIN;INSERT INTO public.tbl_person_fotostatus(person_id, fotostatus_kurzbz, datum, 
					insertamum, insertvon, updateamum, updatevon) VALUES('.
					$this->db_add_param($this->person_id).','.
					$this->db_add_param($this->fotostatus_kurzbz).','.
					$this->db_add_param($this->datum).','.
					$this->db_add_param($this->insertamum).','.
					$this->db_add_param($this->insertvon).','.
					$this->db_add_param($this->updateamum).','.
					$this->db_add_param($this->updatevon).');';
		}
		else
		{
			$qry = 'UPDATE public.tbl_person_fotostatus SET '.
				'person_id='.$this->db_add_param($this->person_id).', '.
				'fotostatus_kurzbz='.$this->db_add_param($this->fotostatus_kurzbz).', '.
				'datum='.$this->db_add_param($this->datum).', '.
				'updateamum='.$this->db_add_param($this->updateamum).', '.
				'updatevon='.$this->db_add_param($this->updatevon).' '.
				'WHERE person_fotostatus_id='.$this->db_add_param($this->person_fotostatus_id);
		}
		
		if($result = $this->db_query($qry))
		{
			if($new)
			{
				// ID aus der Sequence holen
				$qry="SELECT currval('public.seq_person_fotostatus_person_fotostatus_id') as id;";
				if($this->db_query($qry))
				{
					if($row = $this->db_fetch_object())
					{
						$this->person_fotostatus_id = $row->id;
						$this->db_query('COMMIT');
						return true;
					}
					else
					{
						$this->db_query('ROLLBACK');
						$this->errormsg = "Fehler beim Auslesen der Sequence";
						return false;
					}
				}
				else
				{
					$this->db_query('ROLLBACK');
					$this->errormsg = 'Fehler beim Auslesen der Sequence';
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
			$this->errormsg = 'Fehler beim Speichern des Fotostatus';
			return false;
		}
	}
	
	/**
	 * Laedt den letzten Fotostatus einer Person
	 * @param $person_id
	 */
	public function getLastFotoStatus($person_id)
	{
		$qry = 'SELECT 
					* 
				FROM 
					public.tbl_person_fotostatus 
				WHERE 
					person_id='.$this->db_add_param($person_id).'
				ORDER BY datum desc, person_fotostatus_id DESC
				LIMIT 1';
		
		if($result = $this->db_query($qry))
		{
			if($row = $this->db_fetch_object($result))
			{
				$this->person_fotostatus_id = $row->person_fotostatus_id;
				$this->person_id = $row->person_id;
				$this->fotostatus_kurzbz = $row->fotostatus_kurzbz;
				$this->datum = $row->datum;
				$this->insertamum = $row->insertamum;
				$this->insertvon = $row->insertvon;
				$this->updateaum = $row->updateamum;
				$this->updatevon = $row->updatevon;
				
				return true;
			}
			else
			{
				$this->errormsg = 'Eintrag wurde nicht gefunden';
				return false;
			}
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Daten';
			return false;
		}
	}
}
?>
