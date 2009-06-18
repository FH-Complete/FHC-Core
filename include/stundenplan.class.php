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
		$qry = "SELECT * FROM lehre.tbl_$this->stpl_table WHERE ".$this->stpl_table."_id='$stundenplan_id'";
		
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
				$this->fix = ($row->fix=='t'?true:false);
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
			$this->errormsg = 'Fehler beim Laden des Datensatzes'.$qry;
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
			        VALUES('.$this->addslashes($this->unr).','.
					$this->addslashes($this->mitarbeiter_uid).','.
					$this->addslashes($this->datum).','.
					$this->addslashes($this->stunde).','.
					$this->addslashes($this->ort_kurzbz).','.
					$this->addslashes($this->gruppe_kurzbz).','.
					$this->addslashes($this->titel).','.
					$this->addslashes($this->anmerkung).','.
					$this->addslashes($this->lehreinheit_id).','.
					$this->addslashes($this->studiengang_kz).','.
					$this->addslashes($this->semester).','.
					$this->addslashes(($this->verband!=''?$this->verband:' ')).','.
					$this->addslashes(($this->gruppe!=''?$this->gruppe:' ')).','.
					($this->fix?'true':'false').','.
					$this->addslashes($this->updateamum).','.
					$this->addslashes($this->updatevon).','.
					$this->addslashes($this->insertamum).','.
					$this->addslashes($this->insertvon).');';
		}
		else
		{
			$qry = 'UPDATE lehre.tbl_'.$this->stpl_table.' SET'.
			       ' unr='.$this->addslashes($this->unr).','.
			       ' mitarbeiter_uid='.$this->addslashes($this->mitarbeiter_uid).','.
			       ' datum='.$this->addslashes($this->datum).','.
			       ' stunde='.$this->addslashes($this->stunde).','.
			       ' ort_kurzbz='.$this->addslashes($this->ort_kurzbz).','.
			       ' gruppe_kurzbz='.$this->addslashes($this->gruppe_kurzbz).','.
			       ' titel='.$this->addslashes($this->titel).','.
			       ' anmerkung='.$this->addslashes($this->anmerkung).','.
			       ' lehreinheit_id='.$this->addslashes($this->lehreinheit_id).','.
			       ' studiengang_kz='.$this->addslashes($this->studiengang_kz).','.
			       ' semester='.$this->addslashes($this->semester).','.
			       ' verband='.$this->addslashes(($this->verband!=''?$this->verband:' ')).','.
			       ' gruppe='.$this->addslashes(($this->gruppe!=''?$this->gruppe:' ')).','.			       
			       ' fix='.($this->fix?'true':'false').','.
			       ' updateamum='.$this->addslashes($this->updateamum).','.
			       ' updatevon='.$this->addslashes($this->updatevon).
			       " WHERE ".$this->stpl_table."_id=".$this->addslashes($this->stundenplan_id).";";
		}

		if($this->db_query($qry))
		{
			//Log schreiben
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern des Stundenplanes:'.$this->db_last_error();
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
		
		$qry = "DELETE FROM lehre.tbl_$this->stpl_table WHERE ".$this->stpl_table."_id='$id'";
		
		if($this->db_query($qry))
		{
			return true;
		}
		else 
		{
			$this->errormsg = 'Fehler beim Loeschen des Eintrages: '.$this->db_last_error();
			return false;
		}
	}
}
?>