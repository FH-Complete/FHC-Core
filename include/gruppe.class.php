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

class gruppe
{
	var $conn;     // resource DB-Handle
	var $errormsg; // string
	var $new;      // boolean
	var $result = array(); // gruppen Objekt
	
	//Tabellenspalten
	var $gruppe_kurzbz;			// varchar(16)
	var $studiengang_kz;		// integer
	var $bezeichnung;			// varchar(32)
	var $semester;				// smallint
	var $sort;					// smallint
	var $mailgrp;				// boolean
	var $beschreibung;			// varchar(128)
	var $sichtbar;				// boolean
	var $aktiv;					// boolean
	var $updateamum;			// timestamp
	var $updatevon;				// varchar(16)
	var $insertamum;			// timestamp
	var $insertvon;				// varchar(16)
	
	// *************************************************************************
	// * Konstruktor - Uebergibt die Connection und laedt optional eine Gruppe
	// * @param $conn        	Datenbank-Connection
	// *        $gruppe_kurzbz
	// *        $unicode     	Gibt an ob die Daten mit UNICODE Codierung 
	// *                     	oder LATIN9 Codierung verarbeitet werden sollen
	// *************************************************************************
	function gruppe($conn, $gruppe_kurzbz=null, $unicode=false)
	{
		$this->conn = $conn;
		
		if($unicode)
			$qry = "SET CLIENT_ENCODING TO 'UNICODE';";
		else 
			$qry = "SET CLIENT_ENCODING TO 'LATIN9';";
			
		if(!pg_query($conn,$qry))
		{
			$this->errormsg	 = 'Encoding konnte nicht gesetzt werden';
			return false;
		}
		
		if($gruppe_kurzbz!=null)
			$this->load($gruppe_kurzbz);
	}
	
	// ****************************************
	// * Prueft ob bereits eine Gruppe mit der
	// * uebergebenen Kurzbezeichnung existiert
	// * @param gruppe_kurzbz
	// ****************************************
	function exists($gruppe_kurzbz)
	{
		$qry = "SELECT count(*) as anzahl FROM public.tbl_gruppe WHERE gruppe_kurzbz='".addslashes($gruppe_kurzbz)."'";
		
		if($row = pg_fetch_object(pg_query($this->conn,$qry)))
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
	
	// *********************************************************
	// * Laedt die Gruppe
	// * @param gruppe_kurzbz
	// *********************************************************
	function load($gruppe_kurzbz)
	{
		return false;
	}
	
	function getgruppe($studiengang_kz=null, $semester=null, $mailgrp=null, $sichtbar=null)
	{
		$qry = 'SELECT * FROM public.tbl_gruppe WHERE 1=1';
		if(!is_null($studiengang_kz))
			$qry .= " AND studiengang_kz='$studiengang_kz'";
		if(!is_null($semester))
			$qry .= " AND semester='$semester'";
		if(!is_null($mailgrp))
			$qry .= " AND mailgrp=".($mailgrp?'true':'false');
		if(!is_null($sichtbar))
			$qry .= " AND sichtbar=".($sichtbar?'true':'false');
		
		if($result=pg_query($this->conn, $qry))
		{
			while($row = pg_fetch_object($result))
			{
				$grp_obj = new gruppe($this->conn);
				
				$grp_obj->gruppe_kurzbz = $row->gruppe_kurzbz;
				$grp_obj->studiengang_kz = $row->studiengang_kz;
				$grp_obj->bezeichnung = $row->bezeichnung;
				$grp_obj->semester = $row->semester;
				$grp_obj->sort = $row->sort;
				$grp_obj->mailgrp = ($row->mailgrp=='t'?true:false);
				$grp_obj->beschreibung = $row->beschreibung;
				$grp_obj->sichtbar = ($row->sichtbar=='t'?true:false);
				$grp_obj->aktiv = ($row->aktiv=='t'?true:false);
				$grp_obj->updateamum = $row->updateamum;
				$grp_obj->updatevon = $row->updatevon;
				$grp_obj->insertamum = $row->insertamum;
				$grp_obj->insertvon = $row->insertvon;
				
				$this->result[] = $grp_obj;
			}
			return true;
		}
		else 
		{
			$this->errormsg = 'Fehler beim laden der Gruppen'.$qry;
			return false;
		}
	}
	
	// *******************************************
	// * Prueft die Variablen vor dem Speichern 
	// * auf Gueltigkeit.
	// * @return true wenn ok, false im Fehlerfall
	// *******************************************
	function validate()
	{
		if(strlen($this->gruppe_kurzbz)>16)
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
		if(strlen($this->bezeichnung)>32)
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
		if(strlen($this->beschreibung)>128)
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
		if(strlen($this->updatevon)>16)
		{
			$this->errormsg = 'Updatevon darf nicht laenger als 16 Zeichen sein';
			return false;
		}
		if(strlen($this->insertvon)>16)
		{
			$this->errormsg = 'Insertvon darf nicht laenger als 16 Zeichen sein';
			return false;
		}
		
		return true;
	}

	// ************************************************
	// * wenn $var '' ist wird NULL zurueckgegeben
	// * wenn $var !='' ist werden Datenbankkritische 
	// * Zeichen mit Backslash versehen und das Ergbnis
	// * unter Hochkomma gesetzt.
	// ************************************************
	function addslashes($var)
	{
		return ($var!=''?"'".addslashes($var)."'":'null');
	}

	// ************************************************************
	// * Speichert Gruppe in die Datenbank
	// * Wenn $new auf true gesetzt ist wird ein neuer Datensatz
	// * angelegt, ansonsten der Datensatz upgedated
	// * @return true wenn erfolgreich, false im Fehlerfall
	// ************************************************************
	function save($new=null)
	{
		if(is_null($new))
			$new = $this->new;
					
		//Variablen auf Gueltigkeit pruefen
		if(!$this->validate())
			return false;

		if($new)
		{		
			$qry = 'INSERT INTO public.tbl_gruppe (gruppe_kurzbz, studiengang_kz, bezeichnung, semester, sort, 
			                                mailgrp, beschreibung, sichtbar, aktiv, 
			                                updateamum, updatevon, insertamum, insertvon)
			        VALUES('.$this->addslashes($this->gruppe_kurzbz).','.
					$this->addslashes($this->studiengang_kz).','.
					$this->addslashes($this->bezeichnung).','.
					$this->addslashes($this->semester).','.
					$this->addslashes($this->sort).','.
					($this->mailgrp?'true':'false').','.
					$this->addslashes($this->beschreibung).','.
					($this->sichtbar?'true':'false').','.
					($this->aktiv?'true':'false').','.
					$this->addslashes($this->updateamum).','.
					$this->addslashes($this->updatevon).','.
					$this->addslashes($this->insertamum).','.
					$this->addslashes($this->insertvon).');';
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
			       ' aktiv='.($this->aktiv?'true':'false').','.
			       ' updateamum='.$this->addslashes($this->updateamum).','.
			       ' updatevon='.$this->addslashes($this->updatevon).
			       " WHERE gruppe_kurzbz=".$this->addslashes($this->gruppe_kurzbz).";";
		}

		if(pg_query($this->conn,$qry))
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