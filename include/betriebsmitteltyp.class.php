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
	public $new;
	public $result = array();
	
	//Tabellenspalten
	public $betriebsmitteltyp;	//string
	public $beschreibung;   	//string
	public $anzahl; 			//smallint
	public $kaution;			//numeric(5,2)
	
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
		$this->errormsg	= 'Noch nicht implementiert';
		return false;
	}
	
	/**
	 * Laedt alle BetriebsmittelTypen
	 * @return true wenn ok, false wenn Fehler
	 */
	public function getAll()
	{
		$qry = "SELECT * FROM public.tbl_betriebsmitteltyp ORDER BY betriebsmitteltyp";
		
		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$bmt = new betriebsmitteltyp();
				
				$bmt->betriebsmitteltyp = $row->betriebsmitteltyp;
				$bmt->beschreibung = $row->beschreibung;
				$bmt->anzahl = $row->anzahl;
				$bmt->kaution = $row->kaution;
				
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
		$dbanzahl=0;
		$qry='';
		$qry1='SELECT * FROM public.tbl_betriebsmitteltyp WHERE beschreibung='.$this->addslashes($this->beschreibung).';';
		if($this->db_query($qry1))
		{
			if($this->db_num_rows()>0) //eintrag gefunden
			{
				if($row1 = $this->db_fetch_object())
				{
					if($row1->anzahl==null)
						$dbanzahl=0;
					else 
						$dbanzahl=$row1->anzahl;

					$qry='UPDATE public.tbl_betriebsmitteltyp SET '.
					'anzahl ='.addslashes($dbanzahl)."+".addslashes($this->anzahl).' '.
					'WHERE beschreibung='.$this->addslashes($this->beschreibung).';';
				}
			}
			else 
			{
				$qry='INSERT INTO public.tbl_betriebsmitteltyp (betriebsmitteltyp, beschreibung, anzahl, kaution) VALUES('.
					$this->addslashes($this->betriebsmitteltyp).', '.
					$this->addslashes($this->beschreibung).', '.
					$this->addslashes($this->anzahl).', '.
					$this->addslashes($this->kaution).');';
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
		else
		{			
			$this->errormsg = 'Fehler beim Zugriff auf den Betriebsmitteltypen-Datensatz';
			return false;
		}
	}
}
?>