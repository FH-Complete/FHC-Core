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
require_once(dirname(__FILE__).'/datum.class.php');

// CI
require_once(dirname(__FILE__).'/../ci_hack.php');
require_once(dirname(__FILE__).'/../application/models/codex/Nation_model.php');

class nation extends Nation_model
{
	use db_extra; //CI Hack
	
	public $new;      // boolean
	public $nation = array(); // nation Objekt
	public $errormsg;			// string

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
	public function load($code = null)
	{
		//Lesen der Daten aus der Datenbank
		$result = parent::loadWhere(array('nation_code' => $code));
		
		if (!is_object($result) || (is_object($result) && $result->error != EXIT_SUCCESS))
		{
			$this->errormsg = 'Fehler bei einer Datenbankabfrage';
			return false;
		}

		if(is_array($result->retval) && count($result->retval) == 1)
		{
			$this->code = $code;
			
			$row = $result->retval[0];

			$this->sperre = $this->db_parse_bool($row->sperre);
			$this->kontinent = $row->kontinent;
			$this->entwicklungsstand = $row->entwicklungsstand;
			$this->eu = $this->db_parse_bool($row->eu);
			$this->ewr = $this->db_parse_bool($row->ewr);
			$this->kurztext = $row->kurztext;
			$this->langtext = $row->langtext;
			$this->engltext = $row->engltext;
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
        if (!$orderEnglish)
		{
			$result = parent::addOrder('kurztext');
		}
		else
		{
			$result = parent::addOrder('engltext');
		}
		
		if ($result->error == EXIT_SUCCESS)
		{
			if ($ohnesperre)
			{
				$result = parent::loadWhere('sperre IS NULL');
			}
			else
			{
				$result = parent::load();
			}
		}
			
		if (!is_object($result) || (is_object($result) && ($result->error != EXIT_SUCCESS || !is_array($result->retval))))
		{
			$this->errormsg = 'Fehler bei einer Datenbankabfrage';
			return false;
		}

		for ($i = 0; $i < count($result->retval); $i++)
		{
			$row = $result->retval[$i];
			
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


		$qry='INSERT INTO bis.tbl_nation (nation_code, entwicklungsstand, eu, ewr, kontinent, kurztext, langtext, engltext, sperre) VALUES('.
			$this->db_add_param($this->code).', '.
			$this->db_add_param($this->entwicklungsstand).', '.
			$this->db_add_param($this->eu, FHC_BOOLEAN).', '.
			$this->db_add_param($this->ewr, FHC_BOOLEAN).', '.
			$this->db_add_param($this->kontinent).', '.
			$this->db_add_param($this->kurztext).', '.
			$this->db_add_param($this->langtext).', '.
			$this->db_add_param($this->engltext).', '.
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