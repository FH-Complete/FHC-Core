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
 * Klasse fachbereich (FAS-Online)
 * @create 04-12-2006
 */
require_once(dirname(__FILE__).'/basis_db.class.php');

class fachbereich extends basis_db
{
	public $new;     			// boolean
	public $result = array(); 	// fachbereich Objekt

	//Tabellenspalten
	public $fachbereich_kurzbz;	// string
	public $bezeichnung;		// string
	public $farbe;				// string
	public $studiengang_kz;		// integer
	public $aktiv;				// boolean
	public $ext_id;				// bigint
	public $oe_kurzbz;

	public $bezeichnung_arr = array();

	/**
	 * Konstruktor
	 * @param $fachb_id ID des zu ladenden Fachbereiches
	 */
	public function __construct($fachbereich_kurzbz=null)
	{
		parent::__construct();

		if(!is_null($fachbereich_kurzbz))
			$this->load($fachbereich_kurzbz);
	}

	/**
	 * Laedt alle verfuegbaren Fachbereiche
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function getAll()
	{
		$qry = 'SELECT * FROM public.tbl_fachbereich ORDER BY fachbereich_kurzbz;';

		if(!$this->db_query($qry))
		{
			$this->errormsg = 'Fehler beim Laden der Datensaetze';
			return false;
		}

		$this->result = array();

		while($row = $this->db_fetch_object())
		{
			$fachb_obj = new fachbereich();

			$fachb_obj->fachbereich_kurzbz 	= $row->fachbereich_kurzbz;
			$fachb_obj->bezeichnung = $row->bezeichnung;
			$fachb_obj->farbe = $row->farbe;
			$fachb_obj->studiengang_kz = $row->studiengang_kz;
			$fachb_obj->ext_id = $row->ext_id;
			$fachb_obj->aktiv = $this->db_parse_bool($row->aktiv);
			$fachb_obj->oe_kurzbz = $row->oe_kurzbz;

			$this->result[] = $fachb_obj;
			$this->bezeichnung_arr[$row->fachbereich_kurzbz] = $row->bezeichnung;
		}
		return true;
	}

	/**
	 * Laedt die Institute die als Array uebergeben werden
	 * @param $kurzbzs Array mit den Instituts-Kurzbezeichnungen
	 * @param $order Sortierreihenfolge
	 * @param $aktiv wenn true dann nur aktive sonst alle
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function loadArray($kurzbzs, $order=null, $aktiv=true)
	{
		if(count($kurzbzs)==0)
			return true;

		$qry = 'SELECT * FROM public.tbl_fachbereich WHERE fachbereich_kurzbz in('.$this->implode4SQL($kurzbzs).')';
		if ($aktiv)
			$qry.=' AND aktiv=true';

		if($order!=null)
		 	$qry .=" ORDER BY $order";

		if(!$this->db_query($qry))
		{
			$this->errormsg = 'Fehler beim Laden der Datensaetze';
			return false;
		}

		while($row = $this->db_fetch_object())
		{
			$fachb_obj = new fachbereich();

			$fachb_obj->fachbereich_kurzbz 	= $row->fachbereich_kurzbz;
			$fachb_obj->bezeichnung = $row->bezeichnung;
			$fachb_obj->farbe = $row->farbe;
			$fachb_obj->studiengang_kz = $row->studiengang_kz;
			$fachb_obj->ext_id = $row->ext_id;
			$fachb_obj->aktiv = $this->db_parse_bool($row->aktiv);
			$fachb_obj->oe_kurzbz = $row->oe_kurzbz;

			$this->result[] = $fachb_obj;
			$this->bezeichnung_arr[$row->fachbereich_kurzbz] = $row->bezeichnung;
		}
		return true;
	}

	/**
	 * Laedt einen Fachbereich
	 * @param $fachb_id ID des zu ladenden Fachbereiches
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function load($fachbereich_kurzbz)
	{
		if($fachbereich_kurzbz == '')
		{
			$this->errormsg = 'fachbereich_kurzbz ungueltig!';
			return false;
		}

		$qry = "SELECT * FROM public.tbl_fachbereich WHERE fachbereich_kurzbz = ".$this->db_add_param($fachbereich_kurzbz).";";

		if(!$this->db_query($qry))
		{
			$this->errormsg = 'Fehler beim Laden des Datensatzes';
			return false;
		}

		if($row = $this->db_fetch_object())
		{
			$this->fachbereich_kurzbz 	= $row->fachbereich_kurzbz;
			$this->bezeichnung = $row->bezeichnung;
			$this->farbe = $row->farbe;
			$this->studiengang_kz = $row->studiengang_kz;
			$this->ext_id = $row->ext_id;
			$this->aktiv = $this->db_parse_bool($row->aktiv);
			$this->oe_kurzbz = $row->oe_kurzbz;
		}
		else
		{
			$this->errormsg = 'Es ist kein Datensatz mit dieser ID vorhanden';
			return false;
		}

		return true;
	}

	/**
	 * Laedt einen Fachbereich ueber eine OE
	 * @param $oe_kurzbz OE des zu ladenden Fachbereiches
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function loadOE($oe_kurzbz)
	{
		if($oe_kurzbz == '')
		{
			$this->errormsg = 'oe_kurzbz ungueltig!';
			return false;
		}

		$qry = "SELECT * FROM public.tbl_fachbereich WHERE oe_kurzbz = ".$this->db_add_param($oe_kurzbz).";";

		if(!$this->db_query($qry))
		{
			$this->errormsg = 'Fehler beim Laden des Datensatzes';
			return false;
		}

		if($row = $this->db_fetch_object())
		{
			$this->fachbereich_kurzbz 	= $row->fachbereich_kurzbz;
			$this->bezeichnung = $row->bezeichnung;
			$this->farbe = $row->farbe;
			$this->studiengang_kz = $row->studiengang_kz;
			$this->ext_id = $row->ext_id;
			$this->aktiv = $this->db_parse_bool($row->aktiv);
			$this->oe_kurzbz = $row->oe_kurzbz;
		}
		else
		{
			$this->errormsg = 'Datensatz nicht gefunden';
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
		if(mb_strlen($this->bezeichnung)>128)
		{
			$this->errormsg = 'Bezeichnung darf nicht laenger als 128 Zeichen sein';
			return false;
		}
		if(mb_strlen($this->fachbereich_kurzbz)>16)
		{
			$this->errormsg = 'Kurzbez darf nicht laenger als 16 Zeichen sein';
			return false;
		}
		if($this->fachbereich_kurzbz == '')
		{
			$this->errormsg = 'fachbereich_kurzbz ungueltig!';
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
			$qry = 'INSERT INTO public.tbl_fachbereich (fachbereich_kurzbz, bezeichnung, farbe, aktiv,
					studiengang_kz) VALUES ('.
				$this->db_add_param($this->fachbereich_kurzbz).', '.
				$this->db_add_param($this->bezeichnung).', '.
				$this->db_add_param($this->farbe).', '.
				$this->db_add_param($this->aktiv, FHC_BOOLEAN).', '.
				$this->db_add_param($this->studiengang_kz).');';
		}
		else
		{
			//bestehenden Datensatz akualisieren

			$qry = 'UPDATE public.tbl_fachbereich SET '.
				'fachbereich_kurzbz='.$this->db_add_param($this->fachbereich_kurzbz).', '.
				'bezeichnung='.$this->db_add_param($this->bezeichnung).', '.
				'farbe='.$this->db_add_param($this->farbe).', '.
				'aktiv='.$this->db_add_param($this->aktiv, FHC_BOOLEAN).', '.
				'studiengang_kz='.$this->db_add_param($this->studiengang_kz).' '.
				'WHERE fachbereich_kurzbz = '.$this->db_add_param($this->fachbereich_kurzbz).';';
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
