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

class lehrfach
{
	var $conn;     // resource DB-Handle
	var $errormsg; // string
	var $new;      // boolean
	var $lehrfaecher = array(); // lehrfach Objekt

	//Tabellenspalten
	var $lehrfach_id;		// integer
	var $studiengang_kz;	// integer
	var $fachbereich_kurzbz;// integer
	var $kurzbz;			// varchar(12)
	var $bezeichnung;		// varchar(255)
	var $farbe;				// char(6)
	var $aktiv;				// boolean
	var $semester;			// smallint
	var $sprache;			// varchar(16)
	var $ext_id;

	// ***********************************************************************
	// * Konstruktor - Uebergibt die Connection und laedt optional ein LF
	// * @param $conn        Datenbank-Connection
	// *        $lehrfach_nr Lehrfach das geladen werden soll (default=null)
	// *        $unicode     Gibt an ob die Daten mit UNICODE Codierung
	// *                     oder LATIN9 Codierung verarbeitet werden sollen
	// ***********************************************************************
	function lehrfach($conn, $lehrfach_id=null, $unicode=false)
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

		if($lehrfach_id != null)
			$this->load($lehrfach_id);
	}

	// *********************************************************
	// * Laedt Lehrfach mit der uebergebenen ID
	// * @param $lehrfach_nr Nr des LF das geladen werden soll
	// *********************************************************
	function load($lehrfach_id)
	{
		//lehrfach_nr auf Gueltigkeit pruefen
		if(is_numeric($lehrfach_id) && $lehrfach_id!='')
		{
			$qry = "SELECT * FROM lehre.tbl_lehrfach WHERE lehrfach_id='$lehrfach_id'";

			if(!$result=pg_query($this->conn,$qry))
			{
				$this->errormsg = 'Fehler beim lesen des Lehrfaches';
				return false;
			}

			if($row = pg_fetch_object($result))
			{
				$this->lehrfach_id = $row->lehrfach_id;
				$this->studiengang_kz = $row->studiengang_kz;
				$this->fachbereich_kurzbz = $row->fachbereich_kurzbz;
				$this->kurzbz = $row->kurzbz;
				$this->bezeichnung = $row->bezeichnung;
				$this->farbe = $row->farbe;
				$this->aktiv = ($row->aktiv=='t'?true:false);
				$this->semester = $row->semester;
				$this->sprache = $row->sprache;
				$this->ext_id = $row->ext_id;
			}
			else
			{
				$this->errormsg = 'Es ist kein Lehrfach mit der ID '.$lehrfach_id.' vorhanden';
				return false;
			}

			return true;
		}
		else
		{
			$this->errormsg = 'Die lehrfach_nr muss eine gueltige Zahl sein';
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
		if(!is_numeric($this->studiengang_kz))
		{
			$this->errormsg = 'Studiengang_kz muss eine gueltige Zahl sein';
			return false;
		}
		if(strlen($this->fachbereich_kurzbz)>16)
		{
			$this->errormsg = 'Fachbereich_kurzbz darf nicht laenger als 16 Zeichen sein';
			return false;
		}
		if(strlen($this->kurzbz)>12)
		{
			$this->errormsg = 'Kurzbezeichnung darf nicht laenger als 12 Zeichen sein';
			return false;
		}
		if(strlen($this->bezeichnung)>255)
		{
			$this->errormsg = 'Bezeichnung darf nicht laenger als 255 Zeichen sein';
			return false;
		}
		if(strlen($this->farbe)>6)
		{
			$this->errormsg = 'Farbe darf nicht laenger als 6 Zeichen sein';
			return false;
		}
		if(!is_bool($this->aktiv))
		{
			$this->errormsg = 'Aktiv muss ein boolscher Wert sein';
			return false;
		}
		if($this->semester!='' && !is_numeric($this->semester))
		{
			$this->errormsg = 'Semester muss eine Zahl sein';
			return false;
		}
		if(strlen($this->sprache)>16)
		{
			$this->errormsg = 'Sprache darf nicht laenger als 16 Zeichen sein';
			return false;
		}

		return true;
	}

	// ************************************************
	// * wenn $var '' ist wird "null" zurueckgegeben
	// * wenn $var !='' ist werden Datenbankkritische
	// * zeichen mit backslash versehen und das ergbnis
	// * unter hochkomma gesetzt.
	// ************************************************
	function addslashes($var)
	{
		return ($var!=''?"'".addslashes($var)."'":'null');
	}

	// ************************************************************
	// * Speichert das Lehrfach in die Datenbank
	// * Wenn $new auf true gesetzt ist wird ein neuer Datensatz
	// * angelegt, ansonsten der Datensatz mit $lehrfach_nr upgedated
	// * @return true wenn erfolgreich, false im Fehlerfall
	// ************************************************************
	function save()
	{
		//Variablen auf Gueltigkeit pruefen
		if(!$this->validate())
			return false;

		if($this->new)
		{
			$qry = 'INSERT INTO lehre.tbl_lehrfach (lehrfach_id, studiengang_kz, fachbereich_kurzbz, kurzbz,
			                                  bezeichnung, farbe, aktiv, semester, sprache, ext_id)
			        VALUES('.
					($this->lehrfach_id!=''?$this->addslashes($this->lehrfach_id):"nextval('lehre.tbl_lehrfach_lehrfach_id_seq')").','. // HuschPfusch 4 Syncro
					$this->addslashes($this->studiengang_kz).','.
					$this->addslashes($this->fachbereich_kurzbz).','.
					$this->addslashes($this->kurzbz).','.
					$this->addslashes($this->bezeichnung).','.
					$this->addslashes($this->farbe).','.
					($this->aktiv?'true':'false').','.
					$this->addslashes($this->semester).','.
					$this->addslashes($this->sprache).','.
					$this->addslashes($this->ext_id).');';
		}
		else
		{
			//lehrfach_nr auf Gueltigkeit pruefen
			if(!is_numeric($this->lehrfach_id))
			{
				$this->errormsg = 'Lehrfach_id muss eine gueltige Zahl sein';
				return false;
			}

			$qry = 'UPDATE lehre.tbl_lehrfach SET'.
			       ' studiengang_kz='.$this->addslashes($this->studiengang_kz).','.
			       ' fachbereich_kurzbz='.$this->addslashes($this->fachbereich_kurzbz).','.
			       ' kurzbz='.$this->addslashes($this->kurzbz).','.
			       ' bezeichnung='.$this->addslashes($this->bezeichnung).','.
			       ' farbe='.$this->addslashes($this->farbe).','.
			       ' aktiv='.($this->aktiv?'true':'false').','.
			       ' semester='.$this->semester.','.
			       ' ext_id='.$this->ext_id.','.
			       ' sprache='.$this->addslashes($this->sprache).
			       " WHERE lehrfach_id='$this->lehrfach_id'";
		}

		if(pg_query($this->conn,$qry))
		{
			//Log schreiben
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern des Lehrfaches:'.$qry;
			return false;
		}
	}

	/**
	 * Liefert die Tabellenelemente die den Kriterien der Parameter entsprechen
	 * @param 	$stg Studiengangs_kz
	 *			$sem Semester
	 *			$order Sortierkriterium
	 *			$fachb fachbereich_kurzbz
	 * @return array mit Lehrfaechern oder false=fehler
	 */
	function getTab($stg=null,$sem=null, $order='lehrfach_id', $fachb=null)
	{
		if($stg!=null && !is_numeric($stg))
		{
			$this->errormsg = 'Studiengang_kz muss eine gueltige Zahl sein';
			return false;
		}
		if($sem!=null && !is_numeric($sem))
		{
			$this->errormsg = 'Semester muss eine gueltige Zahl sein';
			return false;
		}
		$sql_query = "SELECT * FROM lehre.tbl_lehrfach";

		if($stg!=null || $sem!=null || $fachb!=null)
		   $sql_query .= " WHERE true";

		if($stg!=null)
		   $sql_query .= " AND studiengang_kz='$stg'";

		if($sem!=null)
			$sql_query .= " AND semester='$sem'";

		if($fachb!=null)
			$sql_query .= " AND fachbereich_kurzbz='".addslashes($fachb)."'";

		$sql_query .= " ORDER BY $order";

		if($result=pg_query($this->conn,$sql_query))
		{
			while($row=pg_fetch_object($result))
			{
				$l = new lehrfach($this->conn);
				$l->lehrfach_id = $row->lehrfach_id;
				$l->fachbereich_kurzbz = $row->fachbereich_kurzbz;
				$l->kurzbz = $row->kurzbz;
				$l->bezeichnung = $row->bezeichnung;
				$l->farbe = $row->farbe;
				$l->aktiv = $row->aktiv;
				$l->studiengang_kz = $row->studiengang_kz;
				$l->semester = $row->semester;
				$l->sprache = $row->sprache;
				$l->updateamum = $row->updateamum;
				$l->updatevon = $row->updatevon;
				$l->insertamum = $row->insertamum;
				$l->insertvon = $row->insertvon;
				$l->ext_id = $row->ext_id;
				$this->lehrfaecher[]=$l;
			}
		}
		else
		{
			$this->errormsg = pg_errormessage($this->conn);
			return false;
		}
		return true;
	}
}
?>