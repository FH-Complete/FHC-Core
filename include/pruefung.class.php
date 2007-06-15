<?php
/* Copyright (C) 2007 Technikum-Wien
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
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at> and
 *          Gerald Raab <gerald.raab@technikum-wien.at>.
 */

class pruefung
{
	var $conn;    					// resource DB-Handle
	var $new;      					// boolean
	var $errormsg;					// string
	var $result = array();			// pruefung Objekt

	var $lehreinheit_id;			// integer
	var $student_uid;				// varchar(16)
	var $mitarbeiter_uid;			// varchar(16)
	var $note;						// smallint
	var $pruefungstyp_kurzbz;		// varchar(16)
	var $datum;						// Date
	var $anmerkung;					// varchar(256)
	var $insertamum;				// timestamp)
	var $insertvon;					// varchar(16)
	var $updateamum;				// timestamp
	var $updatevon;					// varchar(16)
	var $ext_id;					// bigint



	
	// **************************************************************
	// * Konstruktor
	// * @param conn Connection zur Datenbank
	// *        
	// **************************************************************
	function pruefung($conn, $pruefung_id=null, $unicode=false)
	{
		$this->conn = $conn;

		if($unicode)
			$qry = "SET CLIENT_ENCODING TO 'UNICODE';";
		else
			$qry = "SET CLIENT_ENCODING TO 'LATIN9';";

		if(!pg_query($conn,$qry))
		{
			$this->errormsg	 = "Encoding konnte nicht gesetzt werden";
			return false;
		}

		if(is_numeric($pruefung_id))
			$this->load($pruefung_id);
		
	}

	// *****************************************************
	// * Laedt einen Pr&uuml;fungsdatensatz
	// * @param pruefung_id ID
	// * @return true wenn ok, false im Fehlerfall
	// *****************************************************
	function load($pruefung_kz)
	{
		if(!is_numeric($pruefung_kz))
		{
			$this->errormsg = 'pruefung_kz muss eine gueltige Zahl sein';
			return false;
		}

		$qry = "SELECT * FROM lehre.tbl_pruefung WHERE pruefung_id=$pruefung_id";

		if($res = pg_query($this->conn, $qry))
		{
			if($row = pg_fetch_object($res))
			{
				$this->lehreinheit_id=$row->lehreinheit_id;
				$this->student_uid=$row->student_uid;
				$this->mitarbeiter_uid=$row->mitarbeiter_uid;
				$this->note=$row->note;
				$this->pruefungstyp_kurzbz=$row->pruefungstyp_kurzbz;
				$this->datum=$row->datum;
				$this->anmerkung=$row->anmerkung;
				$this->insertamum=$row->insertamum;
				$this->insertvon=$row->insertvon;
				$this->updateamum=$row->updateamum;
				$this->updatevon=$row->updatevon;
				$this->ext_id=$row->ext_id;
			}
		}
		else
		{
			$this->errormsg = 'Datensatz konnte nicht geladen werden';
			return false;
		}

		return true;
	}

	// *******************************************
	// * Liefert alle Studiengaenge
	// * @return true wenn ok, false im Fehlerfall
	// *******************************************
	function getAll($order=null, $student=null)
	{
		$qry = 'SELECT * FROM lehre.tbl_pruefung';
		if ($student)
			$qry.=' WHERE student ="'.$student.'"';

		if($order!=null)
		 	$qry .=" ORDER BY $order";

		if(!$res = pg_query($this->conn, $qry))
		{
			$this->errormsg = 'Datensatz konnte nicht geladen werden';
			return false;
		}

		while($row = pg_fetch_object($res))
		{
			$pruef_obj = new pruefung($this->conn);
			$pruef_obj->lehreinheit_id=$row->lehreinheit_id;
			$pruef_obj->student_uid=$row->student_uid;
			$pruef_obj->mitarbeiter_uid=$row->mitarbeiter_uid;
			$pruef_obj->note=$row->note;
			$pruef_obj->pruefungstyp_kurzbz=$row->pruefungstyp_kurzbz;
			$pruef_obj->datum=$row->datum;
			$pruef_obj->anmerkung=$row->anmerkung;
			$pruef_obj->insertamum=$row->insertamum;
			$pruef_obj->insertvon=$row->insertvon;
			$pruef_obj->updateamum=$row->updateamum;
			$pruef_obj->updatevon=$row->updatevon;
			$pruef_obj->ext_id=$row->ext_id;

			$this->result[] = $pruef_obj;
		}

		return true;
	}

	/**
	 * Loescht einen Studiengang
	 * @param $stg_id ID des zu loeschenden Studienganges
	 * @return true wenn ok, false im Fehlerfall
	 */
	function delete($stg_id)
	{
		$this->errormsg = 'Noch nicht implementiert';
		return false;
	}
	function addslashes($var)
	{
		return ($var!=''?"'".addslashes($var)."'":'null');
	}
	/**
	 * Prueft die Gueltigkeit der Variablen
	 * @return true wenn ok, false im Fehlerfall
	 */
	function checkvars()
	{
		$this->anmerkung = str_replace("'",'´',$this->anmerkung);
		$this->insertvon = str_replace("'",'´',$this->insertvon);
		$this->updatevon = str_replace("'",'´',$this->updatevon);

		//Laenge Pruefen
		if(strlen($this->anmerkung)>256)
		{
			$this->errormsg = "Anmerkung darf nicht laenger als 256 Zeichen sein bei <b>$this->ext_id</b> - $this->anmerkung";
			return false;
		}
		if(strlen($this->insertvon)>16)
		{
			$this->errormsg = "Insertvon darf nicht laenger als 16 Zeichen sein bei <b>$this->ext_id</b> - $this->insertvon";
			return false;
		}
		if(strlen($this->updatevon)>10)
		{
			$this->errormsg = "Updatevon darf nicht laenger als 16 Zeichen sein bei <b>$this->ext_id</b> - $this->updatevon";
			return false;
		}
		$this->errormsg = '';
		return true;
	}
	/**
	 * Speichert den aktuellen Datensatz
	 * @return true wenn ok, false im Fehlerfall
	 */
	function save()
	{
		//Gueltigkeit der Variablen pruefen
		if(!$this->checkvars())
		{
			return false;
		}

		if($this->new)
		{
			//Pruefen ob pruefung_id gueltig ist
			/*
			if(!is_numeric($this->pruefung_id))
			{
				$this->errormsg = 'pruefung_id ungueltig! ('.$this->pruefung_id.'/'.$this->ext_id.')';
				return false;
			}
			*/
			//Neuen Datensatz anlegen
			$qry = 'INSERT INTO lehre.tbl_pruefung (lehreinheit_id, student_uid, mitarbeiter_uid, note, pruefungstyp_kurzbz, datum, anmerkung, insertamum, insertvon, updateamum, updatevon, ext_id) VALUES ('.
				$this->addslashes($this->lehreinheit_id).', '.
				$this->addslashes($this->student_uid).', '.
				$this->addslashes($this->mitarbeiter_uid).', '.
				$this->addslashes($this->note).', '.
				$this->addslashes($this->pruefungstyp_kurzbz).', '.
				$this->addslashes($this->datum).', '.
				$this->addslashes($this->anmerkung).', '.
				$this->addslashes($this->insertamum).', '.
				$this->addslashes($this->insertvon).', '.
				$this->addslashes($this->updateamum).', '.
				$this->addslashes($this->updatevon).', '.
				$this->addslashes($this->ext_id).');';
		}
		else
		{
			//bestehenden Datensatz akualisieren

			//Pruefen ob pruefung_id gueltig ist
			if(!is_numeric($this->pruefung_id))
			{
				$this->errormsg = 'pruefung_id ungueltig.';
				return false;
			}

			$qry = 'UPDATE lehre.tbl_pruefung SET '.
				'lehreinheit_id='.$this->addslashes($this->lehreinheit_id).', '.
				'student_uid='.$this->addslashes($this->student_uid).', '.
				'mitarbeiter_uid='.$this->addslashes($this->mitarbeiter_uid).', '.
				'note='.$this->addslashes($this->note).', '.
				'pruefungstyp_kurzbz='.$this->addslashes($this->pruefungstyp_kurzbz).', '.
				'datum='.$this->addslashes($this->datum).', '.
				'anmerkung='.$this->addslashes($this->anmerkung).', '.
				'insertamum='.$this->addslashes($this->insertamum).', '.
				'insertvon='.$this->addslashes($this->insertvon).', '.
				'updateamum='.$this->addslashes($this->updateamum).', '.
				'updatevon='.$this->addslashes($this->updatevon).', '.
				'ext_id='.$this->addslashes($this->ext_id).' '.
				'WHERE pruefung_id='.$this->addslashes($this->pruefung_id).';';
		}
		//echo $qry;
		if(pg_query($this->conn, $qry))
		{
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern des Datensatzes';
			return false;
		}
	}


}
?>