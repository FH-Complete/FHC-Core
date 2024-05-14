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
 * Klasse funktion (FAS-Online)
 * @create 14-03-2006
 */
require_once(dirname(__FILE__).'/basis_db.class.php');

class funktion extends basis_db
{
	public $new;     			//  boolean
	public $result = array(); 	//  fachbereich Objekt

	//Tabellenspalten
	public $funktion_kurzbz;	//  integer
	public $beschreibung;		//  string
	public $aktiv;				//  boolean
	public $ext_id;				//  bigint
	public $fachbereich;		//  boolean
	public $semester;			//  boolean

	/**
	 * Konstruktor
	 * @param $funktion_kurzbz Kurzbz der zu ladenden Funktion
	 */
	public function __construct($funktion_kurzbz=null)
	{
		parent::__construct();
		
		if(!is_null($funktion_kurzbz))
			$this->load($funktion_kurzbz);
	}

	/**
	 * Laedt alle verfuegbaren Funktionen
	 * @param uid Wenn die UID uebergeben wird, werden nur die Funktionen dieser Person geladen
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function getAll($uid=null)
	{
		if (is_null($uid))
			$qry='SELECT * FROM public.tbl_funktion order by funktion_kurzbz;';
		else
			$qry="SELECT * FROM public.tbl_funktion JOIN public.tbl_benutzerfunktion USING (funktion_kurzbz) 
					WHERE uid=".$this->db_add_param($uid).";";

		if(!$this->db_query($qry))
		{
			$this->errormsg = 'Fehler beim Laden der Datensaetze';
			return false;
		}

		while($row = $this->db_fetch_object())
		{
			$funktion_obj = new funktion();

			$funktion_obj->funktion_kurzbz = $row->funktion_kurzbz;
			$funktion_obj->beschreibung = $row->beschreibung;
			$funktion_obj->aktiv = $this->db_parse_bool($row->aktiv);
			$funktion_obj->fachbereich = $this->db_parse_bool($row->fachbereich);
			$funktion_obj->semester = $this->db_parse_bool($row->semester);

			$this->result[] = $funktion_obj;
		}
		return true;
	}

	/**
	 * Prueft ob die funktion vorhanden ist
	 *
	 * @param $funktion
	 * @return true wenn vorhanden sonst false
	 */
	public function checkFunktion($funktion)
	{
		foreach ($this->result AS $fkt)
			if ($fkt->funktion_kurzbz==$funktion)
				return true;
		return false;
	}

	/**
	 * Laedt eine Funktion
	 * @param $funktion_kurzbz ID der zu ladenden Funktion
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function load($funktion_kurzbz)
	{
		if ($funktion_kurzbz == '')
		{
			$this->errormsg = 'funktion_bz darf nicht leer sein';
			return false;
		}

		$qry = "SELECT *
				  FROM public.tbl_funktion
				 WHERE funktion_kurzbz = " . $this->db_add_param($funktion_kurzbz) . ";";

		if (!$this->db_query($qry))
		{
			$this->errormsg = 'Fehler beim Laden des Datensatzes';
			return false;
		}

		if ($row = $this->db_fetch_object())
		{
			$this->funktion_kurzbz = $row->funktion_kurzbz;
			$this->beschreibung = $row->beschreibung;
			$this->aktiv = $this->db_parse_bool($row->aktiv);
			$this->fachbereich = $this->db_parse_bool($row->fachbereich);
			$this->semester = $this->db_parse_bool($row->semester);
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
	protected function validate()
	{

		//Laenge Pruefen
		if(mb_strlen($this->beschreibung)>64)
		{
			$this->errormsg = 'Bezeichnung darf nicht laenger als 128 Zeichen sein';
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
			//Pruefen ob funktion_kurzbz befüllt ist
			if($this->funktion_kurzbz == '')
			{
				$this->errormsg = 'funktion_kurzbz darf nicht leer sein';
				return false;
			}
			//Neuen Datensatz anlegen
			$qry = 'INSERT INTO public.tbl_funktion (funktion_kurzbz, beschreibung, fachbereich, semester, aktiv) VALUES ('.
				$this->db_add_param($this->funktion_kurzbz).', '.
				$this->db_add_param($this->beschreibung).', '.
				$this->db_add_param($this->fachbereich, FHC_BOOLEAN).','.
				$this->db_add_param($this->semester, FHC_BOOLEAN).','.
				$this->db_add_param($this->aktiv, FHC_BOOLEAN).'); ';
		}
		else
		{
			//bestehenden Datensatz akualisieren

			//Pruefen ob fachbereich_id eine gueltige Zahl ist
			if( $this->funktion_kurzbz == '')
			{
				$this->errormsg = 'funktion_kurzbz darf nicht leer sein';
				return false;
			}

			$qry = 'UPDATE public.tbl_funktion SET '.
				'beschreibung='.$this->db_add_param($this->beschreibung).', '.
				'fachbereich='.$this->db_add_param($this->fachbereich, FHC_BOOLEAN).','.
				'semester='.$this->db_add_param($this->semester, FHC_BOOLEAN).','.
				'aktiv='.$this->db_add_param($this->aktiv, FHC_BOOLEAN).' '.
				'WHERE funktion_kurzbz = '.$this->db_add_param($this->funktion_kurzbz).';';
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