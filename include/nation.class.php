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
 * Klasse Nation (FAS-Online)
 * @create 06-04-2006
 */
require_once(dirname(__FILE__).'/basis_db.class.php');

class nation extends basis_db
{
	public $new;      // boolean
	public $nation = array(); // nation Objekt

	//Tabellenspalten
	public $code;
	public $sperre;
	public $kontinent;
	public $entwicklungsstand;
	public $eu;
	public $ewr;
	public $kurztext;
	public $langtext;
	public $engltext;
	public $nationengruppe_kurzbz;

	/**
	 * Konstruktor
	 * @param $code      Zu ladende Nation
	 */
	public function __construct($code=null)
	{
		parent::__construct();

		if($code != null)
			$this->load($code);
	}


	/**
	 * Laedt die Funktion mit der ID $adress_id
	 * @param  $code code der zu ladenden Nation
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function load($code)
	{
		//Lesen der Daten aus der Datenbank
		$qry = "SELECT * FROM bis.tbl_nation WHERE nation_code=".$this->db_add_param($code).';';

		if(!$this->db_query($qry))
		{
			$this->errormsg = 'Fehler bei einer Datenbankabfrage';
			return false;
		}

		if($row = $this->db_fetch_object())
		{
			$this->code = $code;

			$this->sperre = $this->db_parse_bool($row->sperre);
			$this->kontinent = $row->kontinent;
			$this->entwicklungsstand = $row->entwicklungsstand;
			$this->eu = $this->db_parse_bool($row->eu);
			$this->ewr = $this->db_parse_bool($row->ewr);
			$this->kurztext = $row->kurztext;
			$this->langtext = $row->langtext;
			$this->engltext = $row->engltext;
			$this->nationengruppe_kurzbz = $row->nationengruppe_kurzbz;
		}
		else
		{
			$this->errormsg = 'Kein Datensatz vorhanden!';
			return false;
		}
		return true;
	}

	/**
	 * Laedt alle Nationen
	 * @param ohnesperre wenn dieser Parameter auf true gesetzt ist werden
	 *        nur die nationen geliefert dessen Buerger bei uns studieren duerfen
	 */
	public function getAll($ohnesperre = false, $orderEnglish = false)
	{
		//Lesen der Daten aus der Datenbank
		$qry = "SELECT * FROM bis.tbl_nation";

		if ($ohnesperre)
			$qry .= " WHERE sperre is null OR sperre is false";

		if ($orderEnglish == false)
			$qry .= " ORDER BY kurztext";
		else
			$qry .= " ORDER BY engltext";

        $qry .= ';';

		if (!$this->db_query($qry))
		{
			$this->errormsg = 'Fehler bei einer Datenbankabfrage';
			return false;
		}

		while ($row = $this->db_fetch_object())
		{
			$nation = new nation();

			$nation->code = $row->nation_code;
			$nation->sperre = $this->db_parse_bool($row->sperre);
			$nation->kontinent = $row->kontinent;
			$nation->entwicklungsstand = $row->entwicklungsstand;
			$nation->eu = $this->db_parse_bool($row->eu);
			$nation->ewr = $this->db_parse_bool($row->ewr);
			$nation->kurztext = $row->kurztext;
			$nation->langtext = $row->langtext;
			$nation->engltext = $row->engltext;
			$nation->nationengruppe_kurzbz = $row->nationengruppe_kurzbz;

			$this->nation[] = $nation;
		}

		return true;
	}

	/**
	 * Speichert die Personendaten in die Datenbank
	 * @return true wenn erfolgreich, false im Fehlerfall
	 */
	public function save()
	{


		$qry='INSERT INTO bis.tbl_nation (nation_code, entwicklungsstand, eu, ewr, kontinent, kurztext, langtext, engltext, nationengruppe_kurzbz, sperre) VALUES('.
			$this->db_add_param($this->code).', '.
			$this->db_add_param($this->entwicklungsstand).', '.
			$this->db_add_param($this->eu, FHC_BOOLEAN).', '.
			$this->db_add_param($this->ewr, FHC_BOOLEAN).', '.
			$this->db_add_param($this->kontinent).', '.
			$this->db_add_param($this->kurztext).', '.
			$this->db_add_param($this->langtext).', '.
			$this->db_add_param($this->engltext).', '.
			$this->db_add_param($this->nationengruppe_kurzbz).', '.
			$this->db_add_param($this->sperre, FHC_BOOLEAN).');';


		if($this->db_query($qry))
		{
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern des Nationen-Datensatzes:'.$this->code.' '.$qry;
			return false;
		}
	}
}
?>
