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

class lehreinheit
{
	var $conn;     // resource DB-Handle
	var $errormsg; // string
	var $new;      // boolean
	var $lehreinheiten = array(); // lehreinheit Objekt
	
	//Tabellenspalten
	var $lehreinheit_id;			// integer
	var $lehrveranstaltung_id;		// integer
	var $studiensemester_kurzbz; 	// varchar(16)
	var $lehrfach_id;				// integer
	var $lehrform_kurzbz;			// varchar(8)
	var $stundenblockung;			// smalint
	var $wochenrythmus;				// smalint
	var $start_kw;					// smalint
	var $raumtyp;					// varchar(8)
	var $raumtypalternativ;			// varchar(8)
	var $lehre;						// boolean
	var $anmerkung;					// varchar(255)
	var $unr;						// integer
	var $lvnr;						// bigint
	var $insertamum;				// timestamp
	var $insertvon;					// varchar(16)
	var $updateamum;				// timestamp
	var $updatevon;					// varchar(16)
	var $ext_id;					// bigint
	
	// *************************************************************************
	// * Konstruktor - Uebergibt die Connection und laedt optional eine LE
	// * @param $conn        	Datenbank-Connection
	// *        $gruppe_kurzbz
	// *        $unicode     	Gibt an ob die Daten mit UNICODE Codierung 
	// *                     	oder LATIN9 Codierung verarbeitet werden sollen
	// *************************************************************************
	function lehreinheit($conn, $lehreinheit_id=null, $unicode=false)
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
		
		if($lehreinheit_id!=null)
			$this->load($lehreinheit_id);
	}
	
	// *********************************************************
	// * Laedt die LE
	// * @param lehreinheit_id
	// *********************************************************
	function load($lehreinheit_id)
	{
		if(!is_numeric($lehreinheit_id))
		{
			$this->errormsg = 'Lehreinheit_id muss eine gueltige Zahl sein';
		}
		
		$qry = "SELECT * FROM lehre.tbl_lehreinheit WHERE lehreinheit_id='$lehreinheit_id'";
		
		if($result = pg_query($this->conn, $qry))
		{
			if($row = pg_fetch_object($result))
			{				
				$this->lehreinheit_id = $row->lehreinheit_id;
				$this->lehrveranstaltung_id = $row->lehrveranstaltung_id;
				$this->studiensemester_kurzbz = $row->studiensemester_kurzbz;
				$this->lehrfach_id = $row->lehrfach_id;
				$this->lehrform_kurzbz = $row->lehrform_kurzbz;
				$this->stundenblockung = $row->stundenblockung;
				$this->wochenrythmus = $row->wochenrythmus;
				$this->start_kw = $row->start_kw;
				$this->raumtyp = $row->raumtyp;
				$this->raumtypalternativ = $row->raumtypalternativ;
				$this->lehre = ($row->lehre=='t'?true:false);
				$this->anmerkung = $row->anmerkung;
				$this->unr = $row->unr;
				$this->lvnr = $row->lvnr;
				$this->insertamum = $row->insertamum;
				$this->insertvon = $row->insertvon;
				$this->updateamum = $row->updateamum;
				$this->updatevon = $row->updatevon;
				$this->ext_id = $row->ext_id;				
			}
		}
		else 
		{
			$this->errormsg = 'Fehler beim Laden der Lehreinheit';
			return false;
		}
	}
	
	function load_lehreinheiten($lehrveranstaltung_id, $studiensemester_kurzbz)
	{
		$qry = "SELECT * FROM lehre.tbl_lehreinheit WHERE lehrveranstaltung_id='$lehrveranstaltung_id' AND studiensemester_kurzbz='$studiensemester_kurzbz'";
		
		if($result = pg_query($this->conn, $qry))
		{
			while($row = pg_fetch_object($result))
			{
				$le_obj = new lehreinheit($this->conn);
				
				$le_obj->lehreinheit_id = $row->lehreinheit_id;
				$le_obj->lehrveranstaltung_id = $row->lehrveranstaltung_id;
				$le_obj->studiensemester_kurzbz = $row->studiensemester_kurzbz;
				$le_obj->lehrfach_id = $row->lehrfach_id;
				$le_obj->lehrform_kurzbz = $row->lehrform_kurzbz;
				$le_obj->stundenblockung = $row->stundenblockung;
				$le_obj->wochenrythmus = $row->wochenrythmus;
				$le_obj->start_kw = $row->start_kw;
				$le_obj->raumtyp = $row->raumtyp;
				$le_obj->raumtypalternativ = $row->raumtypalternativ;
				$le_obj->lehre = ($row->lehre=='t'?true:false);
				$le_obj->anmerkung = $row->anmerkung;
				$le_obj->unr = $row->unr;
				$le_obj->lvnr = $row->lvnr;
				$le_obj->insertamum = $row->insertamum;
				$le_obj->insertvon = $row->insertvon;
				$le_obj->updateamum = $row->updateamum;
				$le_obj->updatevon = $row->updatevon;
				$le_obj->ext_id = $row->ext_id;
				
				$this->lehreinheiten[] = $le_obj;
			}
		}
		else 
		{
			$this->errormsg = 'Fehler beim Laden der Lehreinheiten';
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
		if($this->lehreinheit_id!='' && !is_numeric($this->lehreinheit_id))
		{
			$this->errormsg = 'Lehreinheit_id muss eine gueltige Zahl sein';
			return false;
		}
		if(!is_numeric($this->lehrveranstaltung_id))
		{
			$this->errormsg = 'LehrveranstaltungsNr muss eine gueltige Zahl sein';
			return false;
		}
		if(strlen($this->studiensemester_kurzbz)>16)
		{
			$this->errormsg = 'Studiensemesterkurzbz darf nicht laenger als 16 Zeichen sein';
			return false;
		}
		if($this->studiensemester_kurzbz=='')
		{
			$this->errormsg = 'Studiensemester muss angegeben werden';
			return false;
		}
		if(!is_numeric($this->lehrfach_id))
		{
			$this->errormsg = 'Lehrfach_id muss eine gueltige Zahl sein';
			return false;
		}
		if(strlen($this->lehrform_kurzbz)>8)
		{
			$this->errormsg = 'Lehrform_kurzbz darf nicht laenger als 8 Zeichen sein';
			return false;
		}
		if($this->lehrform_kurzbz=='')
		{
			$this->lehrform_kurzbz='SO';
			//TODO
			//$this->errormsg = 'Lehrform muss angegeben werden';
			//return false;
		}
		if(!is_numeric($this->stundenblockung))
		{
			$this->errormsg = 'Stundenblockung muss eine gueltige Zahl sein';
			return false;
		}
		if(!is_numeric($this->wochenrythmus))
		{
			$this->errormsg = 'Wochenrythmus muss eine gueltige Zahl sein';
			return false;
		}
		if($this->start_kw!='' && !is_numeric($this->start_kw))
		{
			$this->errormsg = 'StartKW muss eine gueltige Zahl sein';
			return false;
		}
		if($this->start_kw!='' && ($this->start_kw>53 || $this->start_kw<1))
		{
			$this->errormsg = 'StartKW muss zwischen 1 und 53 liegen';
			return false;
		}
		if(strlen($this->raumtyp)>8)
		{
			$this->errormsg = 'Raumtyp darf nicht laenger als 8 Zeichen sein';
			return false;
		}
		if(strlen($this->raumtypalternativ)>8)
		{
			$this->errormsg = 'Raumtypalternativ darf nicht alenger als 8 Zeichen sein';
			return false;
		}
		if($this->raumtypalternativ=='')
		{
			//TODO
			$this->raumtypalternativ='Dummy';
		}
		if(!is_bool($this->lehre))
		{
			$this->errormsg = 'Lehre muss ein boolscher Wert sein';
			return false;
		}
		if(strlen($this->anmerkung)>255)
		{
			$this->errormsg = 'Anmerkung darf nicht laenger als 255 Zeichen sein';
			return false;
		}
		if($this->unr!='' && !is_numeric($this->unr))
		{
			$this->errormsg = 'UNR muss eine gueltige Zahl sein';
			return false;
		}
		if($this->ext_id!='' && !is_numeric($this->ext_id))
		{
			$this->errormsg = 'Ext_id muss eine gueltige Zahl sein';
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
	// * Speichert LE in die Datenbank
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
			//ToDo ID entfernen
			$qry = 'INSERT INTO lehre.tbl_lehreinheit (lehrveranstaltung_id, studiensemester_kurzbz, 
			                                     lehrfach_id, lehrform_kurzbz, stundenblockung, wochenrythmus, 
			                                     start_kw, raumtyp, raumtypalternativ, lehre, anmerkung, unr, lvnr, insertamum, insertvon, updateamum, updatevon,  ext_id)
			        VALUES('.$this->addslashes($this->lehrveranstaltung_id).','.
					$this->addslashes($this->studiensemester_kurzbz).','.
					$this->addslashes($this->lehrfach_id).','.
					$this->addslashes($this->lehrform_kurzbz).','.
					$this->addslashes($this->stundenblockung).','.
					$this->addslashes($this->wochenrythmus).','.
					$this->addslashes($this->start_kw).','.
					$this->addslashes($this->raumtyp).','.
					$this->addslashes($this->raumtypalternativ).','.
					($this->lehre?'true':'false').','.
					$this->addslashes($this->anmerkung).','.
					$this->addslashes($this->unr).','.
					$this->addslashes($this->lvnr).','.
					$this->addslashes($this->insertamum).','.
					$this->addslashes($this->insertvon).','.
					$this->addslashes($this->updateamum).','.
					$this->addslashes($this->updatevon).','.
					$this->addslashes($this->ext_id).');';
		}
		else
		{
			$qry = 'UPDATE lehre.tbl_lehreinheit SET'.
			       ' lehrveranstaltung_nr='.$this->addslashes($this->lehrveranstaltung_id).','.
			       ' studiensemester_kurzbz='.$this->addslashes($this->studiensemester_kurzbz).','.
			       ' lehrfach_id='.$this->addslashes($this->lehrfach_id).','.
			       ' lehrform_kurzbz='.$this->addslashes($this->lehrform_kurzbz).','.
			       ' stundenblockung='.$this->addslashes($this->stundenblockung).','.
			       ' wochenrythmus='.$this->addslashes($this->wochenrythmus).','.
			       ' start_kw='.$this->addslashes($this->start_kw).','.
			       ' raumtyp='.$this->addslashes($this->raumtyp).','.
			       ' raumtypalternativ='.$this->addslashes($this->raumtypalternativ).','.
			       ' lehre='.($this->lehre?'true':'false').','.
			       ' anmerkung='.$this->addslashes($this->anmerkung).','.
			       ' unr='.$this->addslashes($this->unr).','.
			       ' lvnr='.$this->addslashes($this->lvnr).','.
				   ' updateamum='.$this->addslashes($this->updateamum).','.
				   ' updatevon='.$this->addslashes($this->updatevon).','.
			       ' ext_id='.$this->addslashes($this->ext_id).
			       " WHERE lehreinheit_id=".$this->addslashes($this->lehreinheit_id).";";
		}

		if(pg_query($this->conn,$qry))
		{
			//Log schreiben
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern der LE:'.$qry;
			return false;
		}
	}
}
?>