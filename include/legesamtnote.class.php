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

class legesamtnote
{
	var $conn;     // resource DB-Handle
	var $errormsg; // string
	var $new;      // boolean
	var $legesamtnoten = array(); // lehreinheit Objekt

	//Tabellenspalten
	var $student_uid;		// varchar(16)
	var $lehreinheit_id;		// int
	var $note;		// smallint
	var $benotungsdatum;	//date
	var $updateamum;		// timestamp
	var $updatevon;			// varchar(16)
	var $insertamum;		// timestamp
	var $insertvon;			// varchar(16)




	// *************************************************************************
	// * Konstruktor - Uebergibt die Connection und laedt optional eine Uebung
	// * @param $conn        	Datenbank-Connection
	// * 		$uebung_id
	// *        $unicode     	Gibt an ob die Daten mit UNICODE Codierung
	// *                     	oder LATIN9 Codierung verarbeitet werden sollen
	// *************************************************************************
	function legesamtnote($conn, $student_uid=null, $lehreinheit_id=null, $unicode=false)
	{
		$this->conn = $conn;
/*
		if($unicode)
			$qry = "SET CLIENT_ENCODING TO 'UNICODE';";
		else
			$qry = "SET CLIENT_ENCODING TO 'LATIN9';";

		if(!pg_query($this->conn,$qry))
		{
			$this->errormsg	 = 'Encoding konnte nicht gesetzt werden';
			return false;
		}
*/
		if($student_uid != null)
			$this->load($student_uid, $lehreinheit_id);
	}

	// *********************************************************
	// * Laedt die legesamtnote
	// * @param student_uid, lehreinheit_id
	// *********************************************************
	function load($student_uid, $lehreinheit_id)
	{
		if(!is_numeric($lehreinheit_id))
		{
			$this->errormsg='lehreinheit_id muss eine gueltige Zahl sein';
			return false;
		}
		$qry = "SELECT * FROM campus.tbl_legesamtnote where student_uid = '".$student_uid."' and lehreinheit_id = '".$lehreinheit_id."'";

		if($result=pg_query($this->conn, $qry))
		{
			if($row = pg_fetch_object($result))
			{
				$this->lehreinheit_id = $row->lehreinheit_id;
				$this->student_uid = $row->student_uid;
				$this->note = $row->note;
				$this->benotungsdatum = $row->benotungsdatum;
				$this->updateamum = $row->updateamum;
				$this->updatevon = $row->updatevon;
				$this->insertamum = $row->insertamum;
				$this->insertvon = $row->insertvon;
				return true;
			}
			else
			{
				$this->errormsg = "Es ist keine legesamtnote mit der fuer diesen studenten vorhanden";
				return false;
			}
		}
		else
		{
			$this->errormsg = 'Fehler beim laden der Uebung';
			return false;
		}
	}
	

	function load_legesamtnote($lehreinheit_id)
	{
		if(!is_numeric($lehreinheit_id))
		{
			$this->errormsg = 'Lehreinheit_id muss eine gueltige Zahl sein';
			return false;
		}

		$qry = "SELECT * FROM campus.tbl_legesamtnote WHERE lehreinheit_id='".$lehreinheit_id."' order by student_uid";


		if($result=pg_query($this->conn, $qry))
		{
			while($row = pg_fetch_object($result))
			{
				$legesamtnote_obj = new uebung($this->conn);

				$legesamtnote_obj->student_uid = $row->student_uid;
				$legesamtnote_obj->note = $row->note;
				$legesamtnote_obj->lehreinheit_id = $row->lehreinheit_id;
				$legesamtnote_obj->benotungsdatum = $row->benotungsdatum;
				$legesamtnote_obj->updateamum = $row->updateamum;
				$legesamtnote_obj->updatevon = $row->updatevon;
				$legesamtnote_obj->insertamum = $row->insertamum;
				$legesamtnote_obj->insertvon = $row->insertvon;

				$this->legesamtnoten[] = $legesamtnote_obj;
			}
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim laden der legesamtnoten';
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
		if(!is_numeric($this->lehreinheit_id))
		{
			$this->errormsg = 'Lehreinheit_id muss eine gueltige Zahl sein';
			return false;
		}
		if(!is_numeric($this->note))
		{
			$this->errormsg = 'Note muss eine gueltige Zahl sein';
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
	// * Speichert Uebung in die Datenbank
	// * Wenn $new auf true gesetzt ist wird ein neuer Datensatz
	// * angelegt, ansonsten der Datensatz upgedated
	// * @return true wenn erfolgreich, false im Fehlerfall
	// ************************************************************
	function save()
	{
		//if(is_null($new))
		//	$new = $this->new;

		//Variablen auf Gueltigkeit pruefen
		if(!$this->validate())
			return false;

		if($this->new)
		{
			$qry = 'INSERT INTO campus.tbl_legesamtnote(student_uid, lehreinheit_id, note, benotungsdatum, updateamum, updatevon, insertamum, insertvon) VALUES('.
			        $this->addslashes($this->student_uid).','.
			        $this->addslashes($this->lehreinheit_id).','.
			        $this->addslashes($this->note).','.
			        $this->addslashes($this->benotungsdatum).','.
			        'null,'.
			        'null,'.
			        $this->addslashes($this->insertamum).','.
			        $this->addslashes($this->insertvon).');';
		}
		else
		{
			$qry = 'UPDATE campus.tbl_legesamtnote SET'.
			       ' student_uid='.$this->addslashes($this->student_uid).','.
			       ' lehreinheit_id ='.$this->addslashes($this->lehreinheit_id).','.
			       ' note='.$this->addslashes($this->note).','.
			       ' benotungsdatum='.$this->addslashes($this->benotungsdatum).','.
			       ' updateamum='.$this->addslashes($this->updateamum).','.
			       ' updatevon='.$this->addslashes($this->updatevon).
			       " WHERE lehreinheit_id=".$this->addslashes($this->lehreinheit_id)." and student_uid = '".$this->student_uid."';";
		}

		if(pg_query($this->conn,$qry))
		{
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern der legesamtnote:'.$qry;
			return false;
		}
	}

}
?>