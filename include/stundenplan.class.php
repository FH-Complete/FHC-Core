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

class stundenplan
{
	var $conn;     // resource DB-Handle
	var $errormsg; // string
	var $new;      // boolean
	var $stundenplan = array(); // stundenplan Objekt
	var $stpl_table;
	
	//Tabellenspalten
	var $stundenplan_id;
	var $unr;
	var $mitarbeiter_uid;
	var $datum;
	var $stunde;
	var $ort_kurzbz;
	var $gruppe_kurzbz;
	var $titel;
	var $anmerkung;
	var $lehreinheit_id;
	var $studiengang_kz;
	var $semester;
	var $verband;
	var $gruppe;
	var $fix;
	var $updateamum;
	var $updatevon;
	var $insertamum;
	var $insertvon;

	// *************************************************************************
	// * Konstruktor - Uebergibt die Connection und laedt optional einen Stundenplaneintrag
	// * @param $conn        	Datenbank-Connection
	// *        $stundenplantabelle
	// *		$stundenplan_id
	// *        $unicode     	Gibt an ob die Daten mit UNICODE Codierung
	// *                     	oder LATIN9 Codierung verarbeitet werden sollen
	// *************************************************************************
	function stundenplan($conn, $stundenplantabelle, $stundenplan_id=null, $unicode=false)
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

		$this->stpl_table = $stundenplantabelle;
		
		if($stundenplan_id!=null)
			$this->load($stundenplan_id);
	}

	// *********************************************************
	// * Laedt einen Stundenplaneintrag
	// * @param stundenplan_id
	// *********************************************************
	function load($stundenplan_id)
	{
		$qry = "SELECT * FROM lehre.tbl_$this->stpl_table WHERE ".$this->stpl_table."_id='$stundenplan_id'";
		
		if($result = pg_query($this->conn, $qry))
		{
			if($row = pg_fetch_object($result))
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

	// *******************************************
	// * Prueft die Variablen vor dem Speichern
	// * auf Gueltigkeit.
	// * @return true wenn ok, false im Fehlerfall
	// *******************************************
	function validate()
	{
		
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
	// * Speichert Stundenplaneintrag in die Datenbank
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

		if(pg_query($this->conn,$qry))
		{
			//Log schreiben
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern des Stundenplanes:'.pg_last_error($this->conn);
			return false;
		}
	}
	
	// ****
	// * Loescht einen Eintrag aus der Stundenplantabelle
	// ****
	function delete($id)
	{
		if(!is_numeric($id))
		{
			$this->errormsg = 'ID muss eine gueltige Zahl sein';
			return false;
		}
		
		$qry = "DELETE FROM lehre.tbl_$this->stpl_table WHERE ".$this->stpl_table."_id='$id'";
		
		if(pg_query($this->conn, $qry))
		{
			return true;
		}
		else 
		{
			$this->errormsg = 'Fehler beim Loeschen des Eintrages: '.pg_last_error($this->conn);
			return false;
		}
	}
}
?>