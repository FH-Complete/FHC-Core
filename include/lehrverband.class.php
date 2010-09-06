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
		            studiengang_kz='".addslashes($studiengang_kz)."' AND
		            semester='".addslashes($semester)."' AND
		            trim(verband)='".trim(addslashes($verband))."' AND
		            trim(gruppe)='".trim(addslashes($gruppe))."'";

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
				$this->errormsg = 'Fehler bei Abfrage: '.$qry;
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
				WHERE studiengang_kz='".addslashes($studiengang_kz)."'
				AND semester='".addslashes($semester)."'
				AND verband='".addslashes($verband)."'
				AND gruppe='".addslashes($gruppe)."'";
		
		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				$this->studiengang_kz = $row->studiengang_kz;
				$this->semester = $row->semester;
				$this->verband = $row->verband;
				$this->gruppe = $row->gruppe;
				$this->aktiv = ($row->aktiv=='t'?true:false);
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
			$qry .=' AND studiengang_kz='.$this->addslashes($studiengang_kz);
		if(!is_null($semester))
			$qry .=' AND semester='.$this->addslashes($semester);
		if(!is_null($verband))
			$qry .=' AND verband='.$this->addslashes($verband);

		$qry .= ' ORDER BY studiengang_kz, semester, verband, gruppe';
		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$lv_obj = new lehrverband();

				$lv_obj->studiengang_kz = $row->studiengang_kz;
				$lv_obj->semester = $row->semester;
				$lv_obj->verband = $row->verband;
				$lv_obj->gruppe = $row->gruppe;
				$lv_obj->aktiv = $row->aktiv;
				$lv_obj->bezeichnung = $row->bezeichnung;
				$lv_obj->orgform_kurzbz = $row->orgform_kurzbz;

				$this->result[] = $lv_obj;
			}
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Lesen der Lehrverbaende '.$qry;
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
			        VALUES('.$this->addslashes($this->studiengang_kz).','.
					$this->addslashes($this->semester).','.
					$this->addslashes($this->verband).','.
					$this->addslashes($this->gruppe).','.
					($this->aktiv?'true':'false').','.
					$this->addslashes($this->bezeichnung).','.
					$this->addslashes($this->orgform_kurzbz).');';
		}
		else 
		{
			$qry = "UPDATE public.tbl_lehrverband SET ".
				   " aktiv=".($this->aktiv?'true':'false').", ".
				   " bezeichnung='".addslashes($this->bezeichnung)."',".
				   " orgform_kurzbz=".$this->addslashes($this->orgform_kurzbz).
				   " WHERE studiengang_kz='".addslashes($this->studiengang_kz)."'".
				   " AND semester='".addslashes($this->semester)."'".
				   " AND verband='".addslashes($this->verband)."'".
				   " AND gruppe='".addslashes($this->gruppe)."';";
		}

		if($this->db_query($qry))
		{
			//Log schreiben
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern des Lehrverbands:'.$qry;
			return false;
		}
	}
}
?>