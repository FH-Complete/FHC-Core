<?php
/* Copyright (C) 2007 Technikum-Wien
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

class erhalter extends basis_db
{
	public $new;				// boolean
	public $result = array();	// erhalter Objekt
	
	public $erhalter_kz;		// integer
	public $kurzbz;			// varchar(5)
	public $bezeichnung;		// varchar(255)
	public $dvr;				// varchar(8)
	public $logo;				// text
	public $zvr;				// char(16)

	
	/**
	 * Konstruktor
	 * @param conn Connection zur Datenbank
	 *        
	 */
	public function __construct($erhalter_kz=null)
	{
		parent::__construct();
		
		if(!is_null($erhalter_kz))
			$this->load($erhalter_kz);
	}
		
	/**
	 * Laedt einen Erhalter
	 * @param stg_id ID des Studienganges der zu laden ist
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function load($erhalter_kz)
	{
		if(!is_numeric($erhalter_kz))
		{
			$this->errormsg = 'Erhalter_kz muss eine gueltige Zahl sein';
			return false;
		}
		
		$qry = "SELECT * FROM public.tbl_erhalter WHERE erhalter_kz=".$this->db_add_param($erhalter_kz);
		
		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{			
				$this->erhalter_kz=$row->erhalter_kz;
				$this->kurzbz=$row->kurzbz;
				$this->bezeichnung=$row->bezeichnung;
				$this->dvr=$row->dvr;
				$this->logo=$row->logo;
				$this->zvr=$row->zvr;
			}
		}
		else 
		{
			$this->errormsg = 'Datensatz konnte nicht geladen werden';
			return false;
		}
		
		return true;		
	}
		
	/**
	 * Liefert alle Erhalter
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function getAll($order=null)
	{
		$qry = "SELECT * FROM public.tbl_erhalter";
		
		if($order!=null)
		 	$qry .=" ORDER BY $order";
		
		if(!$this->db_query($qry))
		{
			$this->errormsg = 'Datensatz konnte nicht geladen werden';
			return false;
		}
		
		while($row = $this->db_fetch_object())
		{
			$stg_obj = new erhalter();
			
			$stg_obj->erhalter_kz=$row->erhalter_kz;
			$stg_obj->kurzbz=$row->kurzbz;
			$stg_obj->bezeichnung=$row->bezeichnung;
			$stg_obj->dvr=$row->dvr;
			$stg_obj->logo=$row->logo;
			$stg_obj->zvr=$row->zvr;
			
			$this->result[] = $stg_obj;
		}
		
		return true;
	}
	
	/**
	 * Prueft die Gueltigkeit der Variablen
	 * @return true wenn ok, false im Fehlerfall
	 */
	protected function validate()
	{	
		//Laenge Pruefen
		if(mb_strlen($this->bezeichnung)>255)           
		{
			$this->errormsg = 'Bezeichnung darf nicht laenger als 255 Zeichen sein';
			return false;
		}
		if(mb_strlen($this->kurzbz)>5)
		{
			$this->errormsg = 'Kurzbez darf nicht laenger als 5 Zeichen sein';
			return false;
		}
		if(!is_numeric($this->erhalter_kz))
		{
			$this->errormsg = 'erhalter_kz ungueltig!';
			return false;
		}
		$this->errormsg = '';
		return true;		
	}
	
	/**
	 * Speichert den aktuellen Datensatz
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function save()
	{
		//Gueltigkeit der Variablen pruefen
		if(!$this->validate()) 
			return false;
			
		if($this->new)
		{
			//Neuen Datensatz anlegen		
			$qry = 'INSERT INTO public.tbl_erhalter (erhalter_kz, kurzbz, bezeichnung, dvr, logo, zvr) VALUES ('.
				$this->db_add_param($this->erhalter_kz).', '.
				$this->db_add_param($this->kurzbz).', '.
				$this->db_add_param($this->bezeichnung).', '.
				$this->db_add_param($this->dvr).', '.
				$this->db_add_param($this->logo).', '.
				$this->db_add_param($this->zvr).');';
		}
		else 
		{
			//bestehenden Datensatz akualisieren
			
			$qry = 'UPDATE public.tbl_studiengang SET '. 
				'erhalter_kz='.$this->db_add_param($this->erhalter_kz).', '.
				'kurzbz='.$this->db_add_param($this->kurzbz).', '.
				'bezeichnung='.$this->db_add_param($this->bezeichnung).', '.
				'dvr='.$this->db_add_param($this->dvr).', '.
				'logo='.$this->db_add_param($this->logo).', '.
				'zvr='.$this->db_add_param($this->zvr).' '.
				'WHERE erhalter_kz='.$this->db_add_param($this->erhalter_kz).';';
		}

		if($this->db_query($qry))
		{
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern des Datensatzes';
			return false;
		}
	}
}
?>