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

class log
{
	var $conn;     // resource DB-Handle
	var $errormsg; // string
	var $new;      // boolean
	var $logs = array(); // lehreinheit Objekt

	//Tabellenspalten
	var $log_id;			// Serial
	var $executetime;		// timestamp
	var $sql;				// text
	var $sqlundo;			// text
	var $beschreibung;		// varchar(64)
	var $mitarbeiter_uid;	// varchar(16)


	// *************************************************************************
	// * Konstruktor - Uebergibt die Connection und laedt optional einen DS
	// * @param $conn        	Datenbank-Connection
	// * 		$log_id
	// *        $unicode     	Gibt an ob die Daten mit UNICODE Codierung
	// *                     	oder LATIN9 Codierung verarbeitet werden sollen
	// *************************************************************************
	function log($conn, $log_id=null, $unicode=false)
	{
		$this->conn = $conn;
/*
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
*/
		if($log_id!=null)
			$this->load($log_id);
	}

	// *********************************************************
	// * Laedt einen Log Eintrag
	// * @param log_id
	// *********************************************************
	function load($log_id)
	{
		if(!is_numeric($log_id))
		{
			$this->errormsg='Log_id muss eine gueltige Zahl sein';
			return false;
		}
		$qry = "SELECT * FROM public.tbl_log WHERE log_id='$log_id'";

		if($result=pg_query($this->conn, $qry))
		{
			if($row = pg_fetch_object($result))
			{
				$this->log_id = $row->log_id;
				$this->executetime = $row->executetime;
				$this->mitarbeiter_uid = $row->mitarbeiter_uid;
				$this->beschreibung = $row->beschreibung;
				$this->sql = $row->sql;
				$this->sqlundo = $row->sqlundo;
				return true;
			}
			else
			{
				$this->errormsg = "Es ist kein Log Eintrag mit der ID $log_id vorhanden";
				return false;
			}
		}
		else
		{
			$this->errormsg = 'Fehler beim laden des Log Eintrages';
			return false;
		}
	}

	// ********************************************
	// * Laedt die letzten 10 Undo Eintraege
	// * @param $uid UID des Mitarbeiters dessen
	// *        UNDO befehle geladen werden sollen
	// * @return true wenn ok , false im Fehlerfall
	// ********************************************
	function load_undo($uid)
	{
		$qry = "SELECT * FROM public.tbl_log WHERE mitarbeiter_uid='".addslashes($uid)."' AND sqlundo is not null ORDER BY executetime DESC LIMIT 10";

		if($result=pg_query($this->conn, $qry))
		{
			while($row = pg_fetch_object($result))
			{
				$log_obj = new log($this->conn, null, null);

				$log_obj->log_id = $row->log_id;
				$log_obj->executetime = $row->executetime;
				$log_obj->mitarbeiter_uid = $row->mitarbeiter_uid;
				$log_obj->beschreibung = $row->beschreibung;
				$log_obj->sql = $row->sql;
				$log_obj->sqlundo = $row->sqlundo;

				$this->logs[] = $log_obj;
			}
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim laden der Log-Eintraege';
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
	// * Speichert einen Log Eintrag in die Datenbank
	// * Wenn $new auf true gesetzt ist wird ein neuer Datensatz
	// * angelegt, ansonsten der Datensatz aktualisiert
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
			$qry = 'INSERT INTO public.tbl_log(executetime, mitarbeiter_uid, beschreibung, sql, sqlundo) VALUES(now(),'.
			        $this->addslashes($this->mitarbeiter_uid).','.
			        $this->addslashes($this->beschreibung).','.
			        $this->addslashes($this->sql).','.
			        $this->addslashes($this->sqlundo).');';
		}
		else
		{
			$qry = 'UPDATE public.tbl_log SET'.
			       ' executetime='.$this->addslashes($this->executetime).','.
			       ' mitarbeiter_uid='.$this->addslashes($this->mitarbeiter_uid).','.
			       ' beschreibung='.$this->addslashes($this->beschreibung).','.
			       ' sql='.$this->addslashes($this->sql).','.
			       ' sqlundo='.$this->addslashes($this->sqlundo).
			       " WHERE log_id=".$this->addslashes($this->log_id).";";
		}

		if(pg_query($this->conn,$qry))
		{
				return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern des Beispiels';
			return false;
		}
	}


	// **********************************
	// * Loescht einen Log Eintrag
	// * @param $log_id ID des DS
	// * @return true wenn ok sonst false
	// **********************************
	function delete($log_id)
	{
		if(!is_numeric($log_id))
		{
			$this->errormsg = 'Log_id ist ungueltig';
			return false;
		}

		$qry = "DELETE FROM public.tbl_log WHERE log_id='$log_id'";

		if(pg_query($this->conn, $qry))
			return true;
		else
		{
			$this->errormsg = 'Fehler beim loeschen des LOG Eintrages';
			return false;
		}
	}

	// ************************************
	// * Fuehrt einen UnDo Befehl aus und
	// * loescht anschliessend den Eintrag
	// * aus dem Log
	// * @param $log_id
	// * @return true wenn ok, sonst false
	// ************************************
	function undo($log_id)
	{
		if(!is_numeric($log_id))
		{
			$this->errormsg = 'Log_id ist ungueltig'.$log_id;
			return false;
		}
		pg_query($this->conn, 'BEGIN;');
		//Undo Befehl aus Log holen
		$qry = "SELECT * FROM public.tbl_log WHERE log_id='$log_id'";
		if($result = pg_query($this->conn, $qry))
		{
			if($row = pg_fetch_object($result))
			{
				if($row->sqlundo!='')
				{
					//UnDo Befehl ausfuehren
					if(pg_query($this->conn, $row->sqlundo))
					{
						//Log Eintrag aus Log entfernen
						$qry = "DELETE FROM public.tbl_log WHERE log_id='$log_id';";
						if(pg_query($this->conn, $qry))
						{
							pg_query($this->conn, 'COMMIT;');
							return true;
						}
						else
						{
							pg_query($this->conn, 'ROLLBACK;');
							$this->errormsg = 'UnDo Eintrag konnte nicht entfernt werden';
							return false;
						}
					}
					else
					{
						$this->errormsg ='UnDo Befehl konnte nicht durchgefuehrt werden';
						return false;
					}
				}
				else
				{
					$this->errormsg = 'Ungueltiger UnDo Befehl';
					return false;
				}
			}
			else
			{
				$this->errormsg = 'UnDo Befehl konnte nicht durchgefuehrt werden';
				return false;
			}
		}
		else
		{
			$this->errormsg = 'UnDo Befehl konnte nicht durchgefuehrt werden';
			return false;
		}
	}
}
?>