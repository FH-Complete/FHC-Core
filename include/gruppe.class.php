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

class gruppe extends basis_db
{
	public $new;      				// boolean
	public $result = array(); 		// gruppen Objekt

	//Tabellenspalten
	public $gruppe_kurzbz;			// varchar(16)
	public $studiengang_kz;			// integer
	public $bezeichnung;			// varchar(32)
	public $semester;				// smallint
	public $sort;					// smallint
	public $lehre=true;				//boolean
	public $mailgrp;				// boolean
	public $beschreibung;			// varchar(128)
	public $generiert;				// boolean
	public $sichtbar;				// boolean
	public $aktiv;					// boolean
	public $updateamum;				// timestamp
	public $updatevon;				// varchar(16)
	public $insertamum;				// timestamp
	public $insertvon;				// varchar(16)
	public $orgform_kurzbz;

	/**
	 * Konstruktor - Laedt optional eine Gruppe
	 * @param $gruppe_kurzbz
	 */
	public function __construct($gruppe_kurzbz=null)
	{
		parent::__construct();
		
		if(!is_null($gruppe_kurzbz))
			$this->load($gruppe_kurzbz);
	}

	/**
	 * Loescht eine Gruppe
	 * @param gruppe_kurzbz
	 * @return boolean
	 */
	public function delete($gruppe_kurzbz)
	{
		$qry ="DELETE FROM public.tbl_gruppe WHERE gruppe_kurzbz='".addslashes($gruppe_kurzbz)."'";
		
		if($this->db_query($qry))
			return true;
		else
		{
			$this->errormsg = 'Fehler beim Loeschen der Gruppe';
			return false;
		}
	}

	/**
	 * Prueft ob bereits eine Gruppe mit der
	 * uebergebenen Kurzbezeichnung existiert
	 * @param gruppe_kurzbz
	 */
	public function exists($gruppe_kurzbz)
	{
		$qry = "SELECT count(*) as anzahl FROM public.tbl_gruppe WHERE gruppe_kurzbz='".addslashes(mb_strtoupper($gruppe_kurzbz))."'";

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
				$this->errormsg = 'Fehler bei einer Abfrage: '.$qry;
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
	 * Laedt die Gruppe
	 * @param gruppe_kurzbz
	 */
	public function load($gruppe_kurzbz)
	{
		$qry = "SELECT * FROM public.tbl_gruppe WHERE gruppe_kurzbz='".addslashes($gruppe_kurzbz)."'";
		
		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				$this->gruppe_kurzbz = $row->gruppe_kurzbz;
				$this->studiengang_kz = $row->studiengang_kz;
				$this->bezeichnung = $row->bezeichnung;
				$this->semester = $row->semester;
				$this->sort = $row->sort;
				$this->mailgrp = ($row->mailgrp=='t'?true:false);
				$this->lehre = ($row->lehre=='t'?true:false);
				$this->beschreibung = $row->beschreibung;
				$this->sichtbar = ($row->sichtbar=='t'?true:false);
				$this->aktiv = ($row->aktiv=='t'?true:false);
				$this->generiert = ($row->generiert=='t'?true:false);
				$this->updateamum = $row->updateamum;
				$this->updatevon = $row->updatevon;
				$this->insertamum = $row->insertamum;
				$this->insertvon = $row->insertvon;
				$this->orgform_kurzbz = $row->orgform_kurzbz;
				return true;
			}
			else
			{
				$this->errormsg = 'Fehler beim Laden der Daten';
				return false;
			}
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Daten';
			return false;
		}
	}

	/**
	 * Laedt alle Gruppen
	 *
	 * @return boolean
	 */
	public function getAll()
	{
		$qry = "SELECT * FROM public.tbl_gruppe ORDER BY gruppe_kurzbz";

		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$grp_obj = new gruppe();
				
				$grp_obj->gruppe_kurzbz = $row->gruppe_kurzbz;
				$grp_obj->studiengang_kz = $row->studiengang_kz;
				$grp_obj->bezeichnung = $row->bezeichnung;
				$grp_obj->semester = $row->semester;
				$grp_obj->sort = $row->sort;
				$grp_obj->lehre = ($row->lehre=='t'?true:false);
				$grp_obj->mailgrp = ($row->mailgrp=='t'?true:false);
				$grp_obj->beschreibung = $row->beschreibung;
				$grp_obj->sichtbar = ($row->sichtbar=='t'?true:false);
				$grp_obj->aktiv = ($row->aktiv=='t'?true:false);
				$grp_obj->generiert = ($row->generiert=='t'?true:false);
				$grp_obj->updateamum = $row->updateamum;
				$grp_obj->updatevon = $row->updatevon;
				$grp_obj->insertamum = $row->insertamum;
				$grp_obj->insertvon = $row->insertvon;
				$grp_obj->orgform_kurzbz = $row->orgform_kurzbz;

				$this->result[] = $grp_obj;
			}
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Gruppen';
			return false;
		}
	}

	/**
	 * Liefert die Anzahl der Personen in dieser Gruppe
	 *
	 * @param $gruppe_kurzbz
	 * @return anzahl der Personen
	 */
	public function countStudenten($gruppe_kurzbz)
	{
		$qry = "SELECT count(*) as anzahl FROM public.tbl_benutzergruppe 
				WHERE gruppe_kurzbz='".addslashes($gruppe_kurzbz)."'";
		
		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
				return $row->anzahl;
			else
			{
				$this->errormsg = 'Fehler beim Lesen der benutzergruppe';
				return false;
			}
		}
		else
		{
			$this->errormsg = 'Fehler beim Lesen der benutzergruppe';
			return false;
		}
	}

	/**
	 * Laedt die Gruppen die den Parametern ensprechen
	 *
	 * @param $studiengang_kz
	 * @param $semester
	 * @param $mailgrp
	 * @param $sichtbar
	 * @return boolean
	 */
	public function getgruppe($studiengang_kz=null, $semester=null, $mailgrp=null, $sichtbar=null)
	{
		$qry = 'SELECT * FROM public.tbl_gruppe WHERE 1=1';
		if(!is_null($studiengang_kz) && $studiengang_kz!='')
			$qry .= " AND studiengang_kz='".addslashes($studiengang_kz)."'";
		if(!is_null($semester) && $semester!='')
			$qry .= " AND semester='".addslashes($semester)."'";
		if(!is_null($mailgrp) && $mailgrp!='')
			$qry .= " AND mailgrp=".($mailgrp?'true':'false');
		if(!is_null($sichtbar))
			$qry .= " AND sichtbar=".($sichtbar?'true':'false');
		$qry.=" ORDER BY beschreibung";
		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$grp_obj = new gruppe();
				
				$grp_obj->gruppe_kurzbz = $row->gruppe_kurzbz;
				$grp_obj->studiengang_kz = $row->studiengang_kz;
				$grp_obj->bezeichnung = $row->bezeichnung;
				$grp_obj->semester = $row->semester;
				$grp_obj->sort = $row->sort;
				$grp_obj->mailgrp = ($row->mailgrp=='t'?true:false);
				$grp_obj->lehre = ($row->lehre=='t'?true:false);
				$grp_obj->beschreibung = $row->beschreibung;
				$grp_obj->sichtbar = ($row->sichtbar=='t'?true:false);
				$grp_obj->aktiv = ($row->aktiv=='t'?true:false);
				$grp_obj->generiert = ($row->generiert=='t'?true:false);
				$grp_obj->updateamum = $row->updateamum;
				$grp_obj->updatevon = $row->updatevon;
				$grp_obj->insertamum = $row->insertamum;
				$grp_obj->insertvon = $row->insertvon;
				$grp_obj->orgform_kurzbz = $row->orgform_kurzbz;

				$this->result[] = $grp_obj;
			}
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Gruppen'.$qry;
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
		if(mb_strlen($this->gruppe_kurzbz)>16)
		{
			$this->errormsg = 'Gruppe_kurzbz darf nicht laenger als 16 Zeichen sein';
			return false;
		}
		if($this->gruppe_kurzbz=='')
		{
			$this->errormsg = 'Gruppe muss angegeben werden';
			return false;
		}
		if(!is_numeric($this->studiengang_kz))
		{
			$this->errormsg = 'Studiengang_kz muss eine gueltige Zahl sein';
			return false;
		}
		if(mb_strlen($this->bezeichnung)>32)
		{
			$this->errormsg = 'Bezeichnung darf nicht laenger als 32 Zeichen sein';
			return false;
		}
		if($this->semester!='' && !is_numeric($this->semester))
		{
			$this->errormsg = 'Semester muss eine gueltige Zahl sein';
			return false;
		}
		if($this->sort!='' && !is_numeric($this->sort))
		{
			$this->errormsg = 'Typ muss eine gueltige Zahl sein';
			return false;
		}
		if(!is_bool($this->mailgrp))
		{
			$this->errormsg = 'Mailgrp muss ein boolscher wert sein';
			return false;
		}
		if(mb_strlen($this->beschreibung)>128)
		{
			$this->errormsg = 'Beschreibung darf nicht laenger als 128 Zeichen sein';
			return false;
		}
		if(!is_bool($this->sichtbar))
		{
			$this->errormsg = 'Sichtbar muss ein boolscher Wert sein';
			return false;
		}
		if(!is_bool($this->aktiv))
		{
			$this->errormsg = 'Aktiv muss ein boolscher Wert sein';
			return false;
		}
		if(mb_strlen($this->updatevon)>16)
		{
			$this->errormsg = 'Updatevon darf nicht laenger als 16 Zeichen sein';
			return false;
		}
		if(mb_strlen($this->insertvon)>16)
		{
			$this->errormsg = 'Insertvon darf nicht laenger als 16 Zeichen sein';
			return false;
		}

		return true;
	}
	
	/**
	 * Speichert Gruppe in die Datenbank
	 * Wenn $new auf true gesetzt ist wird ein neuer Datensatz
	 * angelegt, ansonsten der Datensatz upgedated
	 * @return true wenn erfolgreich, false im Fehlerfall
	 */
	public function save($new=null, $upper=true)
	{
		if(is_null($new))
			$new = $this->new;

		//Variablen auf Gueltigkeit pruefen
		if(!$this->validate())
			return false;

		if($new)
		{
			if ($upper)			
				$kurzbz = mb_strtoupper($this->gruppe_kurzbz);
			else
				$kurzbz = $this->gruppe_kurzbz;
			
			$qry = 'INSERT INTO public.tbl_gruppe (gruppe_kurzbz, studiengang_kz, bezeichnung, semester, sort,
			                                mailgrp, beschreibung, sichtbar, generiert, aktiv, lehre,
			                                updateamum, updatevon, insertamum, insertvon, orgform_kurzbz)
			        VALUES('.$this->addslashes($kurzbz).','.
					$this->addslashes($this->studiengang_kz).','.
					$this->addslashes($this->bezeichnung).','.
					$this->addslashes($this->semester).','.
					$this->addslashes($this->sort).','.
					($this->mailgrp?'true':'false').','.
					$this->addslashes($this->beschreibung).','.
					($this->sichtbar?'true':'false').','.
					($this->generiert?'true':'false').','.
					($this->aktiv?'true':'false').','.
					($this->lehre?'true':'false').','.
					$this->addslashes($this->updateamum).','.
					$this->addslashes($this->updatevon).','.
					$this->addslashes($this->insertamum).','.
					$this->addslashes($this->insertvon).','.
					$this->addslashes($this->orgform_kurzbz).');';
		}
		else
		{
			$qry = 'UPDATE public.tbl_gruppe SET'.
			       ' studiengang_kz='.$this->addslashes($this->studiengang_kz).','.
			       ' bezeichnung='.$this->addslashes($this->bezeichnung).','.
			       ' semester='.$this->addslashes($this->semester).','.
			       ' sort='.$this->addslashes($this->sort).','.
			       ' mailgrp='.($this->mailgrp?'true':'false').','.
			       ' beschreibung='.$this->addslashes($this->beschreibung).','.
			       ' sichtbar='.($this->sichtbar?'true':'false').','.
			       ' generiert='.($this->generiert?'true':'false').','.
			       ' aktiv='.($this->aktiv?'true':'false').','.
			       ' lehre='.($this->lehre?'true':'false').','.
			       ' updateamum='.$this->addslashes($this->updateamum).','.
			       ' updatevon='.$this->addslashes($this->updatevon).','.
			       ' orgform_kurzbz='.$this->addslashes($this->orgform_kurzbz).
			       " WHERE gruppe_kurzbz=".$this->addslashes(mb_strtoupper($this->gruppe_kurzbz)).";";
		}

		if($this->db_query($qry))
		{
			//Log schreiben
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern der Gruppe:'.$qry;
			return false;
		}
	}
}
?>