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

class lehreinheitmitarbeiter
{
	var $conn;     // resource DB-Handle
	var $errormsg; // string
	var $new;      // boolean
	var $lehreinheitmitarbeiter = array(); // lehreinheitmitarbeiter Objekt

	//Tabellenspalten
	var $lehreinheit_id;		// integer
	var $mitarbeiter_uid;		// varchar(16)
	var $mitarbeiter_uid_old;	// verwendet bei Update der UID
	var $semesterstunden;		// smalint
	var $planstunden;			// smalint
	var $stundensatz;			// numeric(6,2)
	var $faktor;				// numeric(2,1)
	var $anmerkung;				// varchar(256)
	var $lehrfunktion_kurzbz; 	// varchar(16)
	var $bismelden;				// boolean
	var $insertamum;			// timestamp
	var $insertvon;				// varchar(16)
	var $updateamum;			// timestamp
	var $updatevon;				// varchar(16)
	var $ext_id; 				// bigint

	// *************************************************************************
	// * Konstruktor - Uebergibt die Connection und laedt optional eine LE
	// * @param $conn        	Datenbank-Connection
	// *        $lehreinheit_id
	// *		$uid
	// *        $unicode     	Gibt an ob die Daten mit UNICODE Codierung
	// *                     	oder LATIN9 Codierung verarbeitet werden sollen
	// *************************************************************************
	function lehreinheitmitarbeiter($conn, $lehreinheit_id=null, $mitarbeiter_uid=null, $unicode=false)
	{
		$this->conn = $conn;

		if($unicode!=null)
		{
			if($unicode)
				$qry = "SET CLIENT_ENCODING TO 'UNICODE';";
			else
				$qry = "SET CLIENT_ENCODING TO 'LATIN9';";

			if(!pg_query($conn,$qry))
			{
				$this->errormsg	 = 'Encoding konnte nicht gesetzt werden';
				return false;
			}
		}

		if($lehreinheit_id!=null && $mitarbeiter_uid!=null)
			$this->load($lehreinheit_id, $mitarbeiter_uid);
	}

	// *********************************************************
	// * Laedt die LEMitarbeiter
	// * @param lehreinheit_id
	// *********************************************************
	function load($lehreinheit_id, $mitarbeiter_uid=null)
	{
		if(!is_numeric($lehreinheit_id))
		{
			$this->errormsg = 'Lehreinheit_id ist ungueltig';
			return false;
		}

		$qry = "SELECT * FROM lehre.tbl_lehreinheitmitarbeiter WHERE lehreinheit_id='$lehreinheit_id' AND mitarbeiter_uid='".addslashes($mitarbeiter_uid)."'";
		if($result = pg_query($this->conn, $qry))
		{
			if($row = pg_fetch_object($result))
			{
				$this->lehreinheit_id = $row->lehreinheit_id;
				$this->mitarbeiter_uid = $row->mitarbeiter_uid;
				$this->lehrfunktion_kurzbz = $row->lehrfunktion_kurzbz;
				$this->semesterstunden = $row->semesterstunden;
				$this->planstunden = $row->planstunden;
				$this->stundensatz = $row->stundensatz;
				$this->faktor = $row->faktor;
				$this->anmerkung = $row->anmerkung;
				$this->bismelden = ($row->bismelden=='t'?true:false);
				$this->updateamum = $row->updateamum;
				$this->updatevon = $row->updatevon;
				$this->insertamum = $row->insertamum;
				$this->insertvon = $row->insertvon;
				$this->ext_id = $row->ext_id;
				return true;
			}
			else
			{
				$this->errormsg = 'Fehler beim laden der Daten';
				return false;
			}
		}
		else
		{
			$this->errormsg = 'Fehler  beim laden der Daten';
			return false;
		}
	}

	// *********************************************************
	// * Laedt die Lektoren einer Lehreinheit
	// * @param lehreinheit_id
	// * @return array + true wenn ok / false im Fehlerfall
	// *********************************************************
	function getLehreinheitmitarbeiter($lehreinheit_id, $mitarbeiter_uid=null)
	{
		$qry = "SELECT * FROM lehre.tbl_lehreinheitmitarbeiter WHERE lehreinheit_id='$lehreinheit_id'";
		if($mitarbeiter_uid!=null)
			$qry.=" AND mitarbeiter_uid='".addslashes($mitarbeiter_uid)."'";
		$qry .=" ORDER BY mitarbeiter_uid";

		if($result = pg_query($this->conn, $qry))
		{
			while($row = pg_fetch_object($result))
			{
				$obj = new lehreinheitmitarbeiter($this->conn, null, null, null);
				$obj->lehreinheit_id = $row->lehreinheit_id;
				$obj->mitarbeiter_uid = $row->mitarbeiter_uid;
				$obj->lehrfunktion_kurzbz = $row->lehrfunktion_kurzbz;
				$obj->semesterstunden = $row->semesterstunden;
				$obj->planstunden = $row->planstunden;
				$obj->stundensatz = $row->stundensatz;
				$obj->faktor = $row->faktor;
				$obj->anmerkung = $row->anmerkung;
				$obj->bismelden = ($row->bismelden=='t'?true:false);
				$obj->updateamum = $row->updateamum;
				$obj->updatevon = $row->updatevon;
				$obj->insertamum = $row->insertamum;
				$obj->insertvon = $row->insertvon;
				$obj->ext_id = $row->ext_id;

				$this->lehreinheitmitarbeiter[] = $obj;
			}
			return true;
		}

		return false;
	}

	// *******************************************
	// * Prueft die Variablen vor dem Speichern
	// * auf Gueltigkeit.
	// * @return true wenn ok, false im Fehlerfall
	// *******************************************
	function validate()
	{
		if($this->stundensatz!='' && !is_numeric($this->stundensatz))
		{
			$this->errormsg = 'Stundensatz muss eine gueltige Zahl sein';
			return false;
		}
		if($this->planstunden!='' && !is_numeric($this->planstunden))
		{
			$this->errormsg = 'Planstunden muss eine gueltige Zahl sein';
			return false;
		}
		if($this->semesterstunden!='' && !is_numeric($this->semesterstunden))
		{
			$this->errormsg = 'Semesterstunden muss eine gueltige Zahl sein';
			return false;
		}
		if($this->faktor!='' && !is_numeric($this->faktor))
		{
			$this->errormsg = 'Faktor muss eine gueltige Zahl sein';
			return false;
		}
		if(strlen($this->anmerkung)>255)
		{
			$this->errormsg = 'Anmerkung darf nicht laenger als 255 Zeichen sein.';
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
	// * Speichert LEMitarbeiter in die Datenbank
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
			$qry = 'INSERT INTO lehre.tbl_lehreinheitmitarbeiter (lehreinheit_id, mitarbeiter_uid, semesterstunden, planstunden,
			                                                stundensatz, faktor, anmerkung, lehrfunktion_kurzbz, bismelden, ext_id, insertamum, insertvon)
			        VALUES('.$this->addslashes($this->lehreinheit_id).','.
					$this->addslashes($this->mitarbeiter_uid).','.
					$this->addslashes($this->semesterstunden).','.
					$this->addslashes($this->planstunden).','.
					$this->addslashes($this->stundensatz).','.
					$this->addslashes($this->faktor).','.
					$this->addslashes($this->anmerkung).','.
					$this->addslashes($this->lehrfunktion_kurzbz).','.
					($this->bismelden?'true':'false').','.
					$this->addslashes($this->ext_id).','.
					$this->addslashes($this->insertamum).','.
					$this->addslashes($this->insertvon).');';
		}
		else
		{
			if($this->mitarbeiter_uid_old=='')
				$this->mitarbeiter_uid_old = $this->mitarbeiter_uid;

			$qry = 'UPDATE lehre.tbl_lehreinheitmitarbeiter SET'.
			       ' semesterstunden='.$this->addslashes($this->semesterstunden).','.
			       ' planstunden='.$this->addslashes($this->planstunden).','.
			       ' stundensatz='.$this->addslashes($this->stundensatz).','.
			       ' faktor='.$this->addslashes($this->faktor).','.
			       ' anmerkung='.$this->addslashes($this->anmerkung).','.
			       ' lehrfunktion_kurzbz='.$this->addslashes($this->lehrfunktion_kurzbz).','.
			       ' mitarbeiter_uid='.$this->addslashes($this->mitarbeiter_uid).','.
			       ' bismelden='.($this->bismelden?'true':'false').','.
			       ' updateamum='.$this->addslashes($this->updateamum).','.
			       ' updatevon='.$this->addslashes($this->updatevon).','.
			       ' ext_id = '.$this->addslashes($this->ext_id).
			       " WHERE lehreinheit_id=".$this->addslashes($this->lehreinheit_id)." AND
			               mitarbeiter_uid=".$this->addslashes($this->mitarbeiter_uid_old).";";
		}

		if(pg_query($this->conn,$qry))
		{
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern der LEMitarbeiter:'.$qry.pg_errormessage($this->conn);
			return false;
		}
	}

	// *******************************************************
	// * Prueft ob die Kombination Lehreinheit-Mitarbeiter
	// * bereits existiert
	// * @param $lehreinheit_id
	// *        $uid
	// * @return true wenn die zuteilung existiert sonst false
	// *******************************************************
	function exists($lehreinheit_id, $uid)
	{
		if(!is_numeric($lehreinheit_id))
		{
			$this->errormsg = 'lehreinheit_id muss eine gueltige Zahl sein';
			return false;
		}

		$qry = "SELECT * FROM lehre.tbl_lehreinheitmitarbeiter WHERE lehreinheit_id='$lehreinheit_id' AND mitarbeiter_uid='".addslashes($uid)."'";
		if($result=pg_query($this->conn, $qry))
		{
			if(pg_num_rows($result)>0)
				return true;
			else
				return false;
		}
		else
		{
			$this->errormsg = 'Fehler beim lesen der Lehreinheitmitarbeiter zuteilung';
			return false;
		}
	}

	// *******************************************
	// * Loescht die Zuteilung eines Mitarbeiters
	// * zu einer Lehreinheit
	// * @param $lehreinheit_id
	// *        $mitarbeiter_uid
	// * @return true wenn ok, false im fehlerfall
	// *******************************************
	function delete($lehreinheit_id, $mitarbeiter_uid)
	{
		if(!is_numeric($lehreinheit_id))
		{
			$this->errormsg = 'Lehreinheit_id ist ungueltig';
			return false;
		}
		$qry_del = "DELETE FROM lehre.tbl_lehreinheitmitarbeiter WHERE lehreinheit_id='$lehreinheit_id' AND mitarbeiter_uid='".addslashes($mitarbeiter_uid)."'";
		$qry = "SELECT * FROM lehre.tbl_lehreinheitmitarbeiter WHERE lehreinheit_id='$lehreinheit_id' AND mitarbeiter_uid='".addslashes($mitarbeiter_uid)."'";
		if($result = pg_query($this->conn, $qry))
		{
			if($row = pg_fetch_object($result))
			{
				$undo = 'INSERT INTO lehre.tbl_lehreinheitmitarbeiter (lehreinheit_id, mitarbeiter_uid, semesterstunden, planstunden, '.
			            ' stundensatz, faktor, anmerkung, lehrfunktion_kurzbz, bismelden, ext_id, insertamum, insertvon, updateamum, updatevon)'.
				       	' VALUES('.$this->addslashes($row->lehreinheit_id).','.
						$this->addslashes($row->mitarbeiter_uid).','.
						$this->addslashes($row->semesterstunden).','.
						$this->addslashes($row->planstunden).','.
						$this->addslashes($row->stundensatz).','.
						$this->addslashes($row->faktor).','.
						$this->addslashes($row->anmerkung).','.
						$this->addslashes($row->lehrfunktion_kurzbz).','.
						($row->bismelden=='t'?'true':'false').','.
						$this->addslashes($row->ext_id).','.
						$this->addslashes($row->insertamum).','.
						$this->addslashes($row->insertvon).','.
						$this->addslashes($row->updateamum).','.
						$this->addslashes($row->updatevon).');';

				$log = new log($this->conn, null, null);
				$log->sqlundo = $undo;
				$log->sql = $qry_del;
				$log->mitarbeiter_uid = get_uid();
				$log->beschreibung = "Lektorzuteilung loeschen $mitarbeiter_uid - $lehreinheit_id";
				pg_query($this->conn, 'BEGIN;');
				if($log->save(true))
				{
					if(pg_query($this->conn, $qry_del))
					{
						pg_query($this->conn, 'COMMIT;');
						return true;
					}
					else
					{
						pg_query($this->conn, 'ROLLBACK;');
						$this->errormsg = 'Fehler beim Loeschen der Zuteilung';
						return false;
					}
				}
				else
				{
					pg_query($this->conn, 'ROLLBACK;');
					$this->errormsg = 'UNDO Eintrag konnte nicht erstellt werden';
					return false;
				}
			}
			else
			{
				$this->errormsg = 'Datensatz wurde nicht gefunden';
				return false;
			}
		}
		else
		{
			$this->errormsg = 'Fehler beim lesen der Daten';
			return false;
		}
	}
}
?>