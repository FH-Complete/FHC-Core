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
 * Klasse ortraumtyp (FAS-Online)
 * @create 04-12-2006
 */
require_once(dirname(__FILE__).'/basis_db.class.php');

class ortraumtyp extends basis_db
{
	public $new;     		// boolean
	public $result = array();

	//Tabellenspalten
	public $ort_kurzbz;		// string
	public $hierarchie;		// smallint
	public $raumtyp_kurzbz;	// string

	/**
	 * Konstruktor
	 * @param $ort_kurzbz 
	 * @param $hierarchie
	 */
	public function __construct($ort_kurzbz=null, $hierarchie=0)
	{
		parent::__construct();
		
		if($ort_kurzbz != null && $hierarchie!=null && is_numeric($hierarchie))
			$this->load($ort_kurzbz, $hierarchie);
	}

	/**
	 * Laedt alle verfuegbaren OrtRaumtypen
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function getAll()
	{
		$qry = 'SELECT * FROM public.tbl_ortraumtyp ORDER BY ort_kurzbz, hierarchie;';

		if(!$this->db_query($qry))
		{
			$this->errormsg = 'Fehler beim Laden der Datensaetze';
			return false;
		}

		while($row = $this->db_fetch_object())
		{
			$ortraumtyp_obj = new ort();

			$ortraumtyp_obj->ort_kurzbz 	= $row->ort_kurzbz;
			$ortraumtyp_obj->hierarchie 	= $row->hierarchie;
			$ortraumtyp_obj->raumtyp_kurzbz = $row->raumtyp_kurzbz;

			$this->result[] = $ortraumtyp_obj;
		}
		return true;
	}

	/**
	 * Laedt einen OrtRaumtyp
	 * @param $ortraumtyp
	 * @param $hierarchie
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function load($ort_kurzbz, $hierarchie)
	{
		if($ort_kurzbz == '' || !is_numeric($hierarchie) || $hierarchie=='')
		{
			$this->errormsg = 'Kein gültiger Schlüssel vorhanden';
			return false;
		}

		$qry = "SELECT 
					* 
				FROM 
					public.tbl_ortraumtyp 
				WHERE 
					ort_kurzbz = ".$this->db_add_param($ort_kurzbz)." 
					AND hierarchie = ".$this->db_add_param($hierarchie).";";

		if(!$this->db_query($qry))
		{
			$this->errormsg = 'Fehler beim Laden des Datensatzes';
			return false;
		}

		if($row = $this->db_fetch_object())
		{
			$this->ort_kurzbz 		= $row->ort_kurzbz;
			$this->hierarchie 		= $row->hierarchie;
			$this->raumtyp_kurzbz 	= $row->kurzbz;
		}
		else
		{
			$this->errormsg = 'Es ist kein Datensatz mit dieser ID vorhanden';
			return false;
		}

		return true;
	}
	
	/**
	 * Prueft die Gueltigkeit der Variablen
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function validate()
	{
		//Laenge Pruefen
		if(mb_strlen($this->ort_kurzbz)>16)
		{
			$this->errormsg = 'Ort_kurzbz darf nicht laenger als 16 Zeichen sein';
			return false;
		}
		if(mb_strlen($this->raumtyp_kurzbz)>16)
		{
			$this->errormsg = 'Raumtyp_kurzbz darf nicht laenger als 16 Zeichen sein';
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
			//Pruefen ob id gültig ist
			if($this->ort_kurzbz == '' || !is_numeric($this->hierarchie) || $this->hierarchie=='')
			{
				$this->errormsg = 'Keine gültige ID';
				return false;
			}
			//Neuen Datensatz anlegen
			$qry = 'INSERT INTO public.tbl_ortraumtyp (ort_kurzbz, hierarchie, raumtyp_kurzbz) VALUES ('.
				$this->db_add_param($this->ort_kurzbz).', '.
				$this->db_add_param($this->hierarchie).', '.
				$this->db_add_param($this->raumtyp_kurzbz).');';

		}
		else
		{
			//bestehenden Datensatz akualisieren

			//Pruefen ob id gueltig ist
			if($this->ort_kurzbz == '' || !is_numeric($this->hierarchie) || $this->hierarchie=='')
			{
				$this->errormsg = 'Keine gültige ID';
				return false;
			}

			$qry = 'UPDATE public.tbl_ortraumtyp SET '.
				'raumtyp_kurzbz='.$this->db_add_param($this->raumtyp_kurzbz).' '.
				'WHERE ort_kurzbz = '.$this->db_add_param($this->ort_kurzbz).' AND hierarchie = '.$this->db_add_param($this->hierarchie).';';
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
	
	/**
	 * Laedt die Raumtypen eines Ortes
	 * 
	 * @param $ort_kurzbz
	 * @return boolean
	 */
	public function getRaumtypen($ort_kurzbz)
	{
		if($ort_kurzbz=='')
		{
			$this->errormsg = 'Kein gültiger Schlüssel vorhanden';
			return false;
		}

		$qry = "SELECT 
					* 
				FROM 
					public.tbl_ortraumtyp 
					JOIN public.tbl_raumtyp USING(raumtyp_kurzbz) 
				WHERE 
					ort_kurzbz=".$this->db_add_param($ort_kurzbz);

		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$obj = new ortraumtyp();
								
				$obj->ort_kurzbz = $row->ort_kurzbz;
				$obj->hierarchie = $row->hierarchie;
				$obj->raumtyp_kurzbz = $row->raumtyp_kurzbz;
				$obj->beschreibung = $row->beschreibung;
				
				$this->result[] = $obj;				
			}
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Daten';
			return false;
		}

		return true;
	}

	/**
	 * Loescht eine Zuordnung
	 * @param $ort_kurzbz
	 * @param $raumtyp_kurzbz
	 */
	public function delete($ort_kurzbz, $raumtyp_kurzbz)
	{
		$qry = "DELETE FROM public.tbl_ortraumtyp 
			WHERE ort_kurzbz=".$this->db_add_param($ort_kurzbz)."
			AND raumtyp_kurzbz=".$this->db_add_param($raumtyp_kurzbz).";";
		if($this->db_query($qry))
		{
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Löschen';
			return false;
		}
	}
}
?>
