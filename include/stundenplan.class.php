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
require_once(dirname(__FILE__).'/basis_db.class.php');

class stundenplan extends basis_db
{
	public $new;      // boolean
	public $stundenplan = array(); // stundenplan Objekt
	public $stpl_table;
	
	//Tabellenspalten
	public $stundenplan_id;
	public $unr;
	public $mitarbeiter_uid;
	public $datum;
	public $stunde;
	public $ort_kurzbz;
	public $gruppe_kurzbz;
	public $titel;
	public $anmerkung;
	public $lehreinheit_id;
	public $studiengang_kz;
	public $semester;
	public $verband;
	public $gruppe;
	public $fix;
	public $updateamum;
	public $updatevon;
	public $insertamum;
	public $insertvon;

	/**
	 * Konstruktor - Laedt optional einen Stundenplaneintrag
	 * @param $stundenplantabelle
	 * @param $stundenplan_id
	 */
	public function __construct($stundenplantabelle, $stundenplan_id=null)
	{
		parent::__construct();
		
		$this->stpl_table = $stundenplantabelle;
		
		if($stundenplan_id!=null)
			$this->load($stundenplan_id);
	}

	/**
	 * Laedt einen Stundenplaneintrag
	 * @param stundenplan_id
	 */
	public function load($stundenplan_id)
	{
		$qry = "SELECT * FROM lehre.tbl_$this->stpl_table WHERE ".$this->stpl_table."_id=".$this->db_add_param($stundenplan_id);
		
		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				$id = $this->stpl_table.'_id';
				$this->stundenplan_id = $row->$id;
				$this->lehreinheit_id = $row->lehreinheit_id;
				$this->unr = $row->unr;
				$this->studiengang_kz = $row->studiengang_kz;
				$this->semester = $row->semester;
				$this->verband = $row->verband;
				$this->gruppe = $row->gruppe;
				$this->gruppe_kurzbz = $row->gruppe_kurzbz;
				$this->mitarbeiter_uid = $row->mitarbeiter_uid;
				$this->ort_kurzbz = $row->ort_kurzbz;
				$this->datum = $row->datum;
				$this->stunde = $row->stunde;
				$this->titel = $row->titel;
				$this->anmerkung = $row->anmerkung;
				$this->fix = $this->db_parse_bool($row->fix);
				$this->updateamum = $row->updateamum;
				$this->updatevon = $row->updatevon;
				$this->insertamum = $row->insertamum;
				$this->insetvon = $row->insertvon;
				
				return true;
			}
			else 
			{
				$this->errormsg = 'Der Datensatz wurde nicht gefunden';
				return false;
			}
		}
		else 
		{
			$this->errormsg = 'Fehler beim Laden des Datensatzes';
			return false;
		}
	}

	/**
	 * Prueft die Variablen vor dem Speichern
	 * auf Gueltigkeit.
	 * @return true wenn ok, false im Fehlerfall
	 */
	protected function validate()
	{
		return true;
	}

	/**
	 * Speichert Stundenplaneintrag in die Datenbank
	 * Wenn $new auf true gesetzt ist wird ein neuer Datensatz
	 * angelegt, ansonsten der Datensatz upgedated
	 * @return true wenn erfolgreich, false im Fehlerfall
	 */
	public function save($new=null)
	{
		if(is_null($new))
			$new = $this->new;

		//Variablen auf Gueltigkeit pruefen
		if(!$this->validate())
			return false;

		if($new)
		{
			$qry = 'INSERT INTO lehre.tbl_'.$this->stpl_table.' (unr, mitarbeiter_uid, datum, stunde, ort_kurzbz, gruppe_kurzbz, 
					titel, anmerkung, lehreinheit_id, studiengang_kz, semester, verband, gruppe, fix, updateamum, updatevon, 
					insertamum, insertvon)
			        VALUES('.
					$this->db_add_param($this->unr).','.
					$this->db_add_param($this->mitarbeiter_uid).','.
					$this->db_add_param($this->datum).','.
					$this->db_add_param($this->stunde).','.
					$this->db_add_param($this->ort_kurzbz).','.
					$this->db_add_param($this->gruppe_kurzbz).','.
					$this->db_add_param($this->titel).','.
					$this->db_add_param($this->anmerkung).','.
					$this->db_add_param($this->lehreinheit_id).','.
					$this->db_add_param($this->studiengang_kz).','.
					$this->db_add_param($this->semester).','.
					$this->db_add_param(($this->verband!=''?$this->verband:' ')).','.
					$this->db_add_param(($this->gruppe!=''?$this->gruppe:' ')).','.
					$this->db_add_param($this->fix, FHC_BOOLEAN).','.
					$this->db_add_param($this->updateamum).','.
					$this->db_add_param($this->updatevon).','.
					$this->db_add_param($this->insertamum).','.
					$this->db_add_param($this->insertvon).');';
		}
		else
		{
			$qry = 'UPDATE lehre.tbl_'.$this->stpl_table.' SET'.
			       ' unr='.$this->db_add_param($this->unr).','.
			       ' mitarbeiter_uid='.$this->db_add_param($this->mitarbeiter_uid).','.
			       ' datum='.$this->db_add_param($this->datum).','.
			       ' stunde='.$this->db_add_param($this->stunde).','.
			       ' ort_kurzbz='.$this->db_add_param($this->ort_kurzbz).','.
			       ' gruppe_kurzbz='.$this->db_add_param($this->gruppe_kurzbz).','.
			       ' titel='.$this->db_add_param($this->titel).','.
			       ' anmerkung='.$this->db_add_param($this->anmerkung).','.
			       ' lehreinheit_id='.$this->db_add_param($this->lehreinheit_id, FHC_INTEGER).','.
			       ' studiengang_kz='.$this->db_add_param($this->studiengang_kz, FHC_INTEGER).','.
			       ' semester='.$this->db_add_param($this->semester).','.
			       ' verband='.$this->db_add_param(($this->verband!=''?$this->verband:' ')).','.
			       ' gruppe='.$this->db_add_param(($this->gruppe!=''?$this->gruppe:' ')).','.			       
			       ' fix='.$this->db_add_param($this->fix, FHC_BOOLEAN).','.
			       ' updateamum='.$this->db_add_param($this->updateamum).','.
			       ' updatevon='.$this->db_add_param($this->updatevon).
			       " WHERE ".$this->stpl_table."_id=".$this->db_add_param($this->stundenplan_id, FHC_INTEGER, false).";";
		}

		if($this->db_query($qry))
		{
			//Log schreiben
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern des Stundenplanes';
			return false;
		}
	}
	
	/**
	 * Loescht einen Eintrag aus der Stundenplantabelle
	 * @param id stundenplan_id
	 */
	public function delete($id)
	{
		if(!is_numeric($id))
		{
			$this->errormsg = 'ID muss eine gueltige Zahl sein';
			return false;
		}
		
		$qry = "DELETE FROM lehre.tbl_$this->stpl_table WHERE ".$this->stpl_table."_id=".$this->db_add_param($id, FHC_INTEGER, false);
		
		if($this->db_query($qry))
		{
			return true;
		}
		else 
		{
			$this->errormsg = 'Fehler beim Loeschen des Eintrages';
			return false;
		}
	}
	
	/**
	 * 
	 * Prueft ob ein Stundenplaneintrag fuer den gewaehlten Ort/Datum/Stunde vorhanden ist
	 * @param $ort_kurzbz
	 * @param $datum
	 * @param $stunde
	 * @return true wenn belegt, false wenn frei, false+errormsg bei Fehler
	 */
	public function isBelegt($ort_kurzbz, $datum, $stunde)
	{
		$qry = "SELECT 
					1 
				FROM 
					lehre.tbl_$this->stpl_table 
				WHERE 
					ort_kurzbz=".$this->db_add_param($ort_kurzbz)." 
					AND datum=".$this->db_add_param($datum)."
					AND stunde=".$this->db_add_param($stunde).";";

		if($result = $this->db_query($qry))
		{
			if($this->db_num_rows($result)>0)
				return true;
			else
				return false;
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Daten';
			return false;
		}
	}

	/**
	 * Liefert Gesamtstunden einer Lehreinheit
	 *
	 * @param int $lehreinheit_id
	 * @param string $uid
	 */
	public function getStunden($lehreinheit_id)
	{

		$qry = 'SELECT count(*) as stunden FROM (SELECT lehreinheit_id, datum, stunde '
				. 'FROM lehre.tbl_stundenplan '
				. 'WHERE lehreinheit_id = ' . $this->db_add_param($lehreinheit_id).' GROUP by lehreinheit_id, datum, stunde) as a';

		$result = $this->db_query($qry);

		$row = $this->db_fetch_object($result);
		return $row->stunden;
	}
}
