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
 * Klasse schluesseltyp (FAS-Online)
 * @create 03-01-2006
 */
require_once(dirname(__FILE__).'/basis_db.class.php');

class schluesseltyp extends basis_db 
{
	public $new;      				// boolean

	//Tabellenspalten
	public $schluesseltyp;			//string
	public $beschreibung;   		//string
	public $anzahl; 				//smallint
	public $kaution;				//numeric(5,2)

	/**
	 * Konstruktor
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Speichert die Daten in die Datenbank
	 * @return true wenn erfolgreich, false im Fehlerfall
	 */
	public function save()
	{
		$qry1='SELECT * FROM public.tbl_schluesseltyp WHERE beschreibung='.$this->db_add_param($this->beschreibung).';';
		if($this->db_query($qry1))
		{
			if($this->db_num_rows()>0) //eintrag gefunden
			{
				if($row1 = $this->db_fetch_object())
				{
					if($row1->anzahl=null)
					{
						$dbanzahl=0;
					}
					else
					{
						$dbanzahl=$row1->anzahl;
					}
					$qry='UPDATE public.tbl_schluesseltyp SET '.
					'anzahl ='.($this->db_add_param($dbanzahl+$this->anzahl)).' '.
					'WHERE beschreibung='.$this->db_add_param($this->beschreibung).';';
				}
			}
			else
			{
				$qry='INSERT INTO public.tbl_schluesseltyp (schluesseltyp, beschreibung, anzahl, kaution) VALUES('.
					$this->db_add_param($this->schluesseltyp).', '.
					$this->db_add_param($this->beschreibung).', '.
					$this->db_add_param($this->anzahl).', '.
					$this->db_add_param($this->kaution).');';
			}
			if($this->db_query($qry))
			{
				return true;
			}
			else
			{
				$this->errormsg = 'Fehler beim Speichern des Schluesseltypen-Datensatzes: '.$this->schluesseltyp.' '.$qry;
				return false;
			}
		}
		else
		{
			$this->errormsg = 'Fehler beim Zugriff auf den Schluesseltypen-Datensatz: '.$this->schluesseltyp.' '.$qry1;
			return false;
		}
	}
}
?>
