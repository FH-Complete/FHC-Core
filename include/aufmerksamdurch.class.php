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
/**
 * Klasse aufmerksamdurch
 * @create 02-01-2007
 */
require_once(dirname(__FILE__).'/basis_db.class.php');
require_once(dirname(__FILE__).'/sprache.class.php');

class aufmerksamdurch extends basis_db
{
	public $new;
	public $result = array();

	//Tabellenspalten
	public $aufmerksamdurch_kurzbz;
	public $beschreibung;
	public $bezeichnung;
	public $ext_id;
	public $aktiv;


	/**
	 * Konstruktor
	 * @param $aufmerksamdurch_kurzbz = ID (Default=null)
	 */
	public function __construct($aufmerksamdurch_kurzbz=null)
	{
		parent::__construct();

		if(!is_null($aufmerksamdurch_kurzbz))
			$this->load($aufmerksamdurch_kurzbz);
	}

	/**
	 * Laedt einen Datensatz
	 * @param  $aufmerksam_kurzbz ID
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function load($aufmerksam_kurzbz)
	{
		//noch nicht implementiert
		return false;
	}

	/**
	 * Laedt alle Datansaetze
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function getAll($orderby='aufmerksamdurch_kurzbz')
	{
        $sprache = new sprache();
		$qry = 'SELECT *,'.$sprache->getSprachQuery('bezeichnung').' FROM public.tbl_aufmerksamdurch';
		if($orderby!='')
			$qry .= " ORDER BY ".($orderby);

		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$obj = new aufmerksamdurch();

				$obj->aufmerksamdurch_kurzbz = $row->aufmerksamdurch_kurzbz;
				$obj->beschreibung = $row->beschreibung;
                $obj->bezeichnung=$sprache->parseSprachResult('bezeichnung',$row);
				$obj->aktiv = $this->db_parse_bool($row->aktiv);

				$this->result[] = $obj;
			}
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden';
			return false;
		}
	}

	/**
	 * Speichert den aktuellen Datensatz in die Datenbank
	 * Wenn $neu auf true gesetzt ist wird ein neuer Datensatz angelegt
	 * andernfalls wird der Datensatz mit der ID in $schluessel_id aktualisiert
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function save()
	{

		if($this->new)
		{
			//Neuen Datensatz einfuegen

			$qry='INSERT INTO public.tbl_aufmerksamdurch (aufmerksamdurch_kurzbz, beschreibung) VALUES('.
			     $this->db_add_param($this->aufmerksamdurch_kurzbz).', '.
			     $this->db_add_param($this->beschreibung).');';
		}
		else
		{
			$qry='UPDATE public.tbl_aufmerksamdurch SET '.
				'beschreibung='.$this->db_add_param($this->beschreibung).' '.
				'WHERE aufmerksamdurch_kurzbz='.$this->db_add_param($this->aufmerksamdurch_kurzbz).';';
		}

		if($this->db_query($qry))
		{
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern der Daten';
			return false;
		}
	}
}
?>
