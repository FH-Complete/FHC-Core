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

class vorlage extends basis_db
{
	// ErgebnisArray
	public $result=array();
	public $num_rows=0;
	public $errormsg;
	public $new;
	
	//Tabellenspalten
	public $vorlage_kurzbz;
	public $studiengang_kz;
	public $version;
	public $text;

	/**
	 * Konstruktor
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Liefert die Aktuelle Vorlage
	 *
	 * @param $studiengang_kz
	 * @param $vorlage_kurzbz
	 * @return boolean
	 */
	public function getAktuelleVorlage($studiengang_kz, $vorlage_kurzbz)
	{
		$qry = "SELECT * FROM public.tbl_vorlagestudiengang WHERE 
				(studiengang_kz=0 OR studiengang_kz='".addslashes($studiengang_kz)."') AND 
				vorlage_kurzbz='".addslashes($vorlage_kurzbz)."' ORDER BY studiengang_kz DESC, version DESC LIMIT 1";

		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				$this->vorlage_kurzbz = $row->vorlage_kurzbz;
				$this->studiengang_kz = $row->studiengang_kz;
				$this->version = $row->version;
				$this->text = $row->text;
				return true;
			}
			else 
			{
				$this->errormsg = 'Keine Vorlage gefunden';
				return false;
			}
		}
		else 
		{
			$this->errormsg = 'Fehler beim Laden der Vorlage';
			return false;
		}
	}
	
}
?>
