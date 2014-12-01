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
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>,
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>,
 *			Stefan Puraner <puraner@technikum-wien.at>
 */
require_once(dirname(__FILE__).'/basis_db.class.php');

class lehrverband extends basis_db 
{
	public $new;      // boolean
	public $result = array(); // lehrverband Objekt

	//Tabellenspalten
	public $studiengang_kz;	// integer
	public $semester;			// integer
	public $verband;			// integer
	public $gruppe;			// integer
	public $aktiv;				// boolean
	public $bezeichnung;		// varchar(16)
	public $orgform_kurzbz;

	/**
	 * Konstruktor
	 * 
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Prueft ob ein Lehrverband existiert
	 *
	 * @param $studiengang_kz
	 * @param $semester
	 * @param $verband
	 * @param $gruppe
	 * @return true wenn vorhanden, sonst false
	 */
	public function exists($studiengang_kz, $semester, $verband, $gruppe)
	{
		$qry = "SELECT count(*) as anzahl FROM public.tbl_lehrverband WHERE
		            studiengang_kz=".$this->db_add_param($studiengang_kz, FHC_INTEGER)." AND
		            semester=".$this->db_add_param($semester, FHC_INTEGER, false)." AND
		            trim(verband)=".$this->db_add_param(trim($verband), FHC_STRING, false)." AND
		            trim(gruppe)=".$this->db_add_param(trim($gruppe), FHC_STRING, false).";";

		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				if($row->anzahl>0)
					return true;
				else
					return false;
			}
			else
			{
				$this->errormsg = 'Fehler bei Abfrage';
				return false;
			}
		}
		else 
		{
			$this->errormsg = 'Fehler bei einer Abfrage';
			return false;
		}
	}

	/**
	 * Laedt einen Lehrverband
	 *
	 * @param $studiengang_kz
	 * @param $semester
	 * @param $verband
	 * @param $gruppe
	 * @return boolean
	 */
	public function load($studiengang_kz, $semester, $verband, $gruppe)
	{
		$qry = "SELECT * FROM public.tbl_lehrverband
				WHERE studiengang_kz=".$this->db_add_param($studiengang_kz, FHC_INTEGER)."
				AND semester=".$this->db_add_param($semester, FHC_INTEGER)."
				AND verband=".$this->db_add_param($verband)."
				AND gruppe=".$this->db_add_param($gruppe).";";
		
		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				$this->studiengang_kz = $row->studiengang_kz;
				$this->semester = $row->semester;
				$this->verband = $row->verband;
				$this->gruppe = $row->gruppe;
				$this->aktiv = $this->db_parse_bool($row->aktiv);
				$this->bezeichnung = $row->bezeichnung;
				$this->orgform_kurzbz = $row->orgform_kurzbz;
				return true;
			}
			else 
			{
				$this->errormsg = 'Eintrag nicht gefunden';
				return false;
			}
		}
		else 
		{
			$this->errormsg = 'Fehler beim Lesen der Daten';
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
		if(!is_numeric($this->semester))
		{
			$this->errormsg = 'Semester muss eine gueltige Zahl sein';
			return false;
		}
		if(!is_numeric($this->studiengang_kz))
		{
			$this->errormsg = 'Studiengang muss eine gueltige Zahl sein';
			return false;
		}
		if($this->verband=='')
		{
			$this->verband=' ';
		}
		if($this->gruppe=='')
		{
			$this->gruppe=' ';
		}
		return true;
	}

	/**
	 * Liefert alle Lehrverbaende unterhalb des uebergebenen
	 *
	 * @param $studiengang_kz
	 * @param $semester
	 * @param $verband
	 * @return boolean
	 */
	public function getlehrverband($studiengang_kz=null, $semester=null, $verband=null)
	{
		$qry = 'SELECT * FROM public.tbl_lehrverband WHERE aktiv=true';
		if(!is_null($studiengang_kz))
			$qry .=' AND studiengang_kz='.$this->db_add_param($studiengang_kz, FHC_INTEGER);
		if(!is_null($semester))
			$qry .=' AND semester='.$this->db_add_param($semester, FHC_INTEGER);
		if(!is_null($verband))
			$qry .=' AND verband='.$this->db_add_param($verband);

		$qry .= ' ORDER BY studiengang_kz, semester, verband, gruppe;';
		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$lv_obj = new lehrverband();

				$lv_obj->studiengang_kz = $row->studiengang_kz;
				$lv_obj->semester = $row->semester;
				$lv_obj->verband = $row->verband;
				$lv_obj->gruppe = $row->gruppe;
				$lv_obj->aktiv = $this->db_parse_bool($row->aktiv);
				$lv_obj->bezeichnung = $row->bezeichnung;
				$lv_obj->orgform_kurzbz = $row->orgform_kurzbz;

				$this->result[] = $lv_obj;
			}
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Lesen der Lehrverbaende ';
			return false;
		}
	}
	
	public function getSemesterFromStudiengang($studiengang_kz, $aktiv=false)
	{
		$qry = 'SELECT semester, aktiv FROM public.tbl_lehrverband WHERE studiengang_kz='.$this->db_add_param($studiengang_kz, FHC_INTEGER)." AND verband=' ' ";

		if($aktiv)
			$qry.= ' AND aktiv=true';

		$qry .= ' GROUP BY semester, aktiv ORDER BY semester;';
		
		if($this->db_query($qry))
		{
			$lv_obj = array();
			while($row = $this->db_fetch_object())
			{
				$sem = array();
				$sem["aktiv"] = $row->aktiv;
				$sem["semester"] = $row->semester;
				array_push($lv_obj, $sem);
			}
			return $lv_obj;
		}
		else
		{
			$this->errormsg = 'Fehler beim Lesen der Semester ';
			return false;
		}
	}
	
	public function getVerbandFromSemester($studiengang_kz, $semester, $aktiv=false)
	{
		$qry = 'SELECT verband, aktiv, bezeichnung FROM public.tbl_lehrverband WHERE studiengang_kz='.$this->db_add_param($studiengang_kz, FHC_INTEGER).' AND semester='.$this->db_add_param($semester, FHC_INTEGER)." AND gruppe=' ' ";
		if($aktiv)
			$qry.=' AND aktiv=true';
		$qry .= ' GROUP BY verband, aktiv, bezeichnung ORDER BY verband;';
		
		if($this->db_query($qry))
		{
			$lv_obj = array();
			while($row = $this->db_fetch_object())
			{
				$verb = array();
				$verb["verband"] = $row->verband;
				$verb["aktiv"] = $row->aktiv;
				$verb["bezeichnung"] = $row->bezeichnung;
				array_push($lv_obj, $verb);
			}
			return $lv_obj;
		}
		else
		{
			$this->errormsg = 'Fehler beim Lesen der Lehrverbaende ';
			return false;
		}
	}
	
	public function getGruppeFromVerband($studiengang_kz, $semester, $verband, $aktiv=false)
	{
		$qry = 'SELECT gruppe, bezeichnung, aktiv FROM public.tbl_lehrverband WHERE studiengang_kz='.$this->db_add_param($studiengang_kz, FHC_INTEGER).' AND semester='.$this->db_add_param($semester, FHC_INTEGER).' AND verband='.$this->db_add_param($verband, FHC_STRING);
		if($aktiv)
		{
			$qry.=' AND aktiv=true';
		}
		$qry .= ' GROUP BY gruppe, bezeichnung, aktiv ORDER BY gruppe;';
		if($this->db_query($qry))
		{
			
			$lv_obj = array();
			
			while($row = $this->db_fetch_object())
			{
				$grp = array();
				$grp["bezeichnung"] = $row->bezeichnung;
				$grp["gruppe"] = $row->gruppe;
				$grp["aktiv"] = $row->aktiv;
				array_push($lv_obj, $grp);
			}
			return $lv_obj;
		}
		else
		{
			$this->errormsg = 'Fehler beim Lesen der Lehrverbaende ';
			return false;
		}
	}

	/**
	 * Speichert Lehrverband in die Datenbank
	 * Wenn $new auf true gesetzt ist wird ein neuer Datensatz
	 * angelegt, ansonsten der Datensatz upgedated
	 * @return true wenn erfolgreich, false im Fehlerfall
	 */
	public function save($new=null)
	{
		if($new==null)
			$new = $this->new;
			
		//Variablen auf Gueltigkeit pruefen
		if(!$this->validate())
			return false;

		if($new)
		{
			$qry = 'INSERT INTO public.tbl_lehrverband (studiengang_kz, semester, verband, gruppe, aktiv, bezeichnung, orgform_kurzbz)
			        VALUES('.$this->db_add_param($this->studiengang_kz, FHC_INTEGER).','.
					$this->db_add_param($this->semester, FHC_INTEGER).','.
					$this->db_add_param($this->verband).','.
					$this->db_add_param($this->gruppe).','.
					$this->db_add_param($this->aktiv, FHC_BOOLEAN).','.
					$this->db_add_param($this->bezeichnung).','.
					$this->db_add_param($this->orgform_kurzbz).');';
		}
		else 
		{
			$qry = "UPDATE public.tbl_lehrverband SET ".
				   " aktiv=".$this->db_add_param($this->aktiv, FHC_BOOLEAN).", ".
				   " bezeichnung=".$this->db_add_param($this->bezeichnung).",".
				   " orgform_kurzbz=".$this->db_add_param($this->orgform_kurzbz).
				   " WHERE studiengang_kz=".$this->db_add_param($this->studiengang_kz, FHC_INTEGER).
				   " AND semester=".$this->db_add_param($this->semester, FHC_INTEGER).
				   " AND verband=".$this->db_add_param($this->verband).
				   " AND gruppe=".$this->db_add_param($this->gruppe).";";
		}

		if($this->db_query($qry))
		{
			//Log schreiben
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern des Lehrverbands:';
			return false;
		}
	}
}
?>
