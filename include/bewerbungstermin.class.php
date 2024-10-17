<?php
/* Copyright (C) 2015 fhcomplete.org
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
 * Authors: Stefan Puraner <stefan.puraner@technikum-wien.at>
 */
/**
 * Klasse Bewerbungstermin
 * @create 10-01-2007
 */

require_once(dirname(__FILE__).'/basis_db.class.php');

class bewerbungstermin extends basis_db
{
	public $new;			//  boolean
	public $result = array();

	//Tabellenspalten
	public $bewerbungstermin_id;//  integer
	public $studiengang_kz;	//integer
	public $studiensemester_kurzbz;	//  string
	public $beginn;		//  timestamp
	public $ende;		//  timestamp
	public $nachfrist;		//  boolean
	public $nachfrist_ende;		//  timestamp
	public $anmerkung;		//  string
	public $insertamum;		//  timestamp
	public $insertvon;		//  bigint
	public $updateamum;		//  timestamp
	public $updatevon;		//  bigint
	public $studienplan_id;	// integer
	public $nationengruppe_kurzbz;	// varchar (16)

	/**
	 * Konstruktor
	 * @param $reihungstest_id ID der Adresse die geladen werden soll (Default=null)
	 */
	public function __construct($bewerbungstermin_id=null)
	{
		parent::__construct();

		if(!is_null($bewerbungstermin_id))
			$this->load($bewerbungstermin_id);
	}

	/**
	 * Laedt den Fördervertrag mit der ID $foerdervertrag_id
	 * @param  $foerdervertrag_id ID des zu ladenden Fördervertrags
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function load($bewerbungstermin_id)
	{
		if(!is_numeric($bewerbungstermin_id))
		{
			$this->errormsg = 'bewerbungstermin_id ist ungueltig';
			return false;
		}

		$qry = "SELECT * FROM public.tbl_bewerbungstermine WHERE bewerbungstermin_id=".$this->db_add_param($bewerbungstermin_id, FHC_INTEGER, false);

		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				$this->bewerbungstermin_id = $row->bewerbungstermin_id;
				$this->studiengang_kz = $row->studiengang_kz;
				$this->studiensemester_kurzbz = $row->studiensemester_kurzbz;
				$this->beginn = $row->beginn;
				$this->ende = $row->ende;
				$this->nachfrist = $row->nachfrist;
				$this->nachfrist_ende = $row->nachfrist_ende;
				$this->anmerkung = $row->anmerkung;
				$this->insertamum = $row->insertamum;
				$this->insertvon = $row->insertvon;
				$this->updateamum = $row->updateamum;
				$this->updatevon = $row->updatevon;
				$this->studienplan_id = $row->studienplan_id;
				$this->nationengruppe_kurzbz = $row->nationengruppe_kurzbz;
				return true;
			}
			else
			{
				$this->errormsg = 'Bewerbungstermin existiert nicht';
				return false;
			}
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden des Bewerbungstermins';
			return false;
		}
	}

	/**
	 * Liefert die Bewerbungstermine eines Studiengangs
	 * @param integer $studiengang_kz. Kennzahl des Studiengangs, dessen Bewerbungstermine geladen werden sollen
	 * @param string $studiensemester_kurzbz. Optional. Default NULL. Studiensemester, dessen Bewerbungstermine geladen werden sollen
	 * @param string $sort. Optional. Default NULL. Sortierung der Ergebnisse
	 * @param integer $studienplan_id. Optional. Default NULL. Studienplan ID, dessen Bewerbungstermine geladen werden sollen
	 * @param string $nationengruppe_kurzbz. Optional. Default NULL. Wenn gesetzt, werden die Bewerbungstermine dieser Nationengruppe geladen, wenn 0 werden alle NULL-Werte geladen, wenn NULL, wird der Parameter gar nicht geladen
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function getBewerbungstermine($studiengang_kz, $studiensemester_kurzbz = null, $sort = null, $studienplan_id = null, $nationengruppe_kurzbz = null)
	{
		$qry = "SELECT
					tbl_bewerbungstermine.*,
					tbl_studienplan.bezeichnung as stpl_bezeichnung
				FROM
					public.tbl_bewerbungstermine
					LEFT JOIN lehre.tbl_studienplan USING(studienplan_id)
				WHERE
					studiengang_kz=".$this->db_add_param($studiengang_kz, FHC_INTEGER);

		if($studiensemester_kurzbz != '')
			$qry.=" AND studiensemester_kurzbz=".$this->db_add_param($studiensemester_kurzbz);

		if($studienplan_id != '')
			$qry.=" AND studienplan_id=".$this->db_add_param($studienplan_id);

		if($nationengruppe_kurzbz !== '')
		{
			if($nationengruppe_kurzbz === 0)
			{
				$qry.=" AND nationengruppe_kurzbz IS NULL";
			}
			elseif($nationengruppe_kurzbz != '')
			{
				$qry.=" AND nationengruppe_kurzbz=".$this->db_add_param($nationengruppe_kurzbz);
			}
		}

		if($sort != null)
		{
		    $qry.=" ORDER BY ".$sort;
		}
		$qry.=";";
//echo $qry;
		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$obj = new bewerbungstermin();

				$obj->bewerbungstermin_id = $row->bewerbungstermin_id;
				$obj->studiengang_kz = $row->studiengang_kz;
				$obj->studiensemester_kurzbz = $row->studiensemester_kurzbz;
				$obj->beginn = $row->beginn;
				$obj->ende = $row->ende;
				$obj->nachfrist = $this->db_parse_bool($row->nachfrist);
				$obj->nachfrist_ende = $row->nachfrist_ende;
				$obj->anmerkung = $row->anmerkung;
				$obj->insertamum = $row->insertamum;
				$obj->insertvon = $row->insertvon;
				$obj->updateamum = $row->updateamum;
				$obj->updatevon = $row->updatevon;
				$obj->studienplan_id = $row->studienplan_id;
				$obj->stpl_bezeichnung = $row->stpl_bezeichnung;
				$obj->nationengruppe_kurzbz = $row->nationengruppe_kurzbz;

				$this->result[] = $obj;
			}
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Bewerbungstermine.';
			return false;
		}
	}

	/**
	 * Prueft die Variablen auf Gueltigkeit
	 * @return true wenn ok, false im Fehlerfall
	 */
	private function validate()
	{
	    return true;
	}

	/**
	 * Speichert den aktuellen Datensatz in die Datenbank
	 * Wenn $neu auf true gesetzt ist wird ein neuer Datensatz angelegt
	 * andernfalls wird der Datensatz mit der ID in $reihungstest_id aktualisiert
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function save()
	{
		if(!$this->validate())
			return false;

		if($this->new)
		{
			//Neuen Datensatz einfuegen

			$qry='BEGIN; INSERT INTO public.tbl_bewerbungstermine(studiensemester_kurzbz, studiengang_kz, beginn, ende, nachfrist, nachfrist_ende, anmerkung, insertamum, insertvon, nationengruppe_kurzbz, studienplan_id) VALUES('.
			     $this->db_add_param($this->studiensemester_kurzbz).', '.
			     $this->db_add_param($this->studiengang_kz, FHC_INTEGER).', '.
			     $this->db_add_param($this->beginn).', '.
			     $this->db_add_param($this->ende).', '.
			     $this->db_add_param($this->nachfrist, FHC_BOOLEAN).', '.
			     $this->db_add_param($this->nachfrist_ende).', '.
			     $this->db_add_param($this->anmerkung).', now(),'.
			     $this->db_add_param($this->insertvon).','.
			     $this->db_add_param($this->nationengruppe_kurzbz).','.
			     $this->db_add_param($this->studienplan_id, FHC_INTEGER).');';
		}
		else
		{
			$qry='UPDATE public.tbl_bewerbungstermine SET '.
				'studiensemester_kurzbz='.$this->db_add_param($this->studiensemester_kurzbz).', '.
				'studiengang_kz='.$this->db_add_param($this->studiengang_kz,FHC_INTEGER).', '.
				'beginn='.$this->db_add_param($this->beginn).', '.
				'ende='.$this->db_add_param($this->ende).', '.
				'nachfrist='.$this->db_add_param($this->nachfrist, FHC_BOOLEAN).', '.
				'nachfrist_ende='.$this->db_add_param($this->nachfrist_ende).', '.
				'anmerkung='.$this->db_add_param($this->anmerkung).', '.
				'updateamum= now(), '.
				'updatevon='.$this->db_add_param($this->updatevon).', '.
				'nationengruppe_kurzbz='.$this->db_add_param($this->nationengruppe_kurzbz).', '.
				'studienplan_id='.$this->db_add_param($this->studienplan_id, FHC_INTEGER).' '.
				'WHERE bewerbungstermin_id='.$this->db_add_param($this->bewerbungstermin_id, FHC_INTEGER, false).';';
		}
		if($this->db_query($qry))
		{
			if($this->new)
			{
				$qry = "SELECT currval('public.tbl_bewerbungstermine_bewerbungstermin_id_seq') as id";
				if($this->db_query($qry))
				{
					if($row = $this->db_fetch_object())
					{
						$this->foerdervertrag_id = $row->id;
						$this->db_query('COMMIT');
						return true;
					}
					else
					{
						$this->errormsg = 'Fehler beim Auslesen der Sequence';
						$this->db_query('ROLLBACK');
						return false;
					}
				}
				else
				{
					$this->errormsg = 'Fehler beim Auslesen der Sequence';
					$this->db_query('ROLLBACK');
					return false;
				}
			}
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern der Daten';
			return false;
		}
	}

	public function delete($bewerbungstermin_id)
	{
	    $qry = "DELETE from public.tbl_bewerbungstermine WHERE bewerbungstermin_id=".$this->db_add_param($bewerbungstermin_id);

	    if(!$this->db_query($qry))
	    {
		$this->errormsg = 'Fehler beim Löschen der Daten';
		return false;
	    }

	    return true;
	}
}
