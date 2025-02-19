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

class raumtyp extends basis_db
{
	public $new;     			//  boolean
	public $result = array(); 	//  raumtyp Objekt

	//Tabellenspalten
	public $beschreibung;		//  string
	public $raumtyp_kurzbz;		//  string
	public $aktiv;	//boolean


	/**
	 * Konstruktor
	 * @param $raumtyp_kurzbz des zu ladenden Raumtyps
	 */
	public function __construct($raumtyp_kurzbz=null)
	{
		parent::__construct();

		if($raumtyp_kurzbz != null)
			$this->load($raumtyp_kurzbz);
	}

	/**
	 * Laedt alle verfuegbaren Raumtypen
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function getAll($aktiv = null)
	{
		$qry = 'SELECT * FROM public.tbl_raumtyp';

		if (!is_null($aktiv))
			$qry .= ' WHERE aktiv = '.$this->db_add_param($aktiv, FHC_BOOLEAN);

		$qry .= ' ORDER BY raumtyp_kurzbz';
		if(!$this->db_query($qry))
		{
			$this->errormsg = 'Fehler beim Laden der Datensaetze';
			return false;
		}

		while($row = $this->db_fetch_object())
		{
			$raumtyp_obj = new raumtyp();

			$raumtyp_obj->beschreibung = $row->beschreibung;
			$raumtyp_obj->raumtyp_kurzbz = $row->raumtyp_kurzbz;
			$raumtyp_obj->aktiv = $this->db_parse_bool($row->aktiv);

			$this->result[] = $raumtyp_obj;
		}
		return true;
	}

	/**
	 * Laedt einen Raumtyp
	 * @param $raumtyp ID des zu ladenden Raumtyps
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function load($raumtyp_kurzbz)
	{
		if($raumtyp_kurzbz == '')
		{
			$this->errormsg = 'Kein gültiger Schlüssel vorhanden';
			return false;
		}

		$qry = "SELECT * FROM public.tbl_raumtyp WHERE raumtyp_kurzbz=".$this->db_add_param($raumtyp_kurzbz).";";

		if(!$this->db_query($qry))
		{
			$this->errormsg = 'Fehler beim Laden des Datensatzes';
			return false;
		}

		if($row = $this->db_fetch_object())
		{
			$this->beschreibung 	= $row->beschreibung;
			$this->raumtyp_kurzbz 	= $row->kurzbz;
			$this->aktiv 	= $this->db_parse_bool($row->aktiv);
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
		if(mb_strlen($this->beschreibung)>256)
		{
			$this->errormsg = 'Beschreibung darf nicht laenger als 256 Zeichen sein';
			return false;
		}
		if(mb_strlen($this->raumtyp_kurzbz)>16)
		{
			$this->errormsg = 'Raumtyp_kurzbz darf nicht laenger als 16 Zeichen sein';
			return false;
		}
		if($this->raumtyp_kurzbz == '')
		{
			$this->errormsg = 'Keine gültige Kurzbz';
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
			if($this->raumtyp_kurzbz == '')
			{
				$this->errormsg = 'Keine gültige ID';
				return false;
			}

			//Neuen Datensatz anlegen
			$qry = 'INSERT INTO public.tbl_raumtyp (beschreibung, raumtyp_kurzbz) VALUES ('.
				$this->db_add_param($this->beschreibung).', '.
				$this->db_add_param($this->raumtyp_kurzbz).');';

		}
		else
		{
			//bestehenden Datensatz akualisieren
			$qry = 'UPDATE public.tbl_raumtyp SET '.
				'beschreibung='.$this->db_add_param($this->beschreibung).' '.
				'WHERE raumtyp_kurzbz = '.$this->db_add_param($this->ort_kurzbz).';';
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
