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

class log extends basis_db
{
	public $new;      		// boolean
	public $logs = array(); // lehreinheit Objekt

	//Tabellenspalten
	public $log_id;				// Serial
	public $executetime;		// timestamp
	public $sql;				// text
	public $sqlundo;			// text
	public $beschreibung;		// varchar(64)
	public $mitarbeiter_uid;	// varchar(16)

	/**
	 * Konstruktor - Laedt optional einen DS
	 * @param $log_id
	 */
	public function __construct($log_id=null)
	{
		parent::__construct();

		if(!is_null($log_id))
			$this->load($log_id);
	}

	/**
	 * Laedt einen Log Eintrag
	 * @param log_id
	 */
	public function load($log_id)
	{
		if(!is_numeric($log_id))
		{
			$this->errormsg='Log_id muss eine gueltige Zahl sein';
			return false;
		}
		$qry = "SELECT * FROM public.tbl_log WHERE log_id=".$this->db_add_param($log_id, FHC_INTEGER).';';

		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
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
			$this->errormsg = 'Fehler beim Laden des Log Eintrages';
			return false;
		}
	}

	/**
	 * Laedt die letzten 10 Undo Eintraege
	 * @param $uid UID des Mitarbeiters dessen
	 *        UNDO befehle geladen werden sollen
	 * @return true wenn ok , false im Fehlerfall
	 */
	public function load_undo($uid)
	{
		$qry = "SELECT * FROM public.tbl_log WHERE mitarbeiter_uid=".$this->db_add_param($uid)." AND sqlundo is not null ORDER BY executetime DESC LIMIT 10;";

		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$log_obj = new log();

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
			$this->errormsg = 'Fehler beim Laden der Log-Eintraege';
			return false;
		}
	}

	/**
	 * Prueft die Variablen vor dem Speichern
	 * auf Gueltigkeit.
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function validate()
	{
		return true;
	}
	
	/**
	 * Speichert einen Log Eintrag in die Datenbank
	 * Wenn $new auf true gesetzt ist wird ein neuer Datensatz
	 * angelegt, ansonsten der Datensatz aktualisiert
	 * @return true wenn erfolgreich, false im Fehlerfall
	 */
	public function save($new=null)
	{
		if(is_null($new))
			$new = $this->new;

		//Variablen auf Gueltigkeit pruefen
		if(!$this->validate())
			return false;

		if($new)
		{
			$qry = 'INSERT INTO public.tbl_log(executetime, mitarbeiter_uid, beschreibung, sql, sqlundo) VALUES(now(),'.
			        $this->db_add_param($this->mitarbeiter_uid).','.
			        $this->db_add_param($this->beschreibung).','.
			        $this->db_add_param($this->sql).','.
			        $this->db_add_param($this->sqlundo).');';
		}
		else
		{
			$qry = 'UPDATE public.tbl_log SET'.
			       ' executetime='.$this->db_add_param($this->executetime).','.
			       ' mitarbeiter_uid='.$this->db_add_param($this->mitarbeiter_uid).','.
			       ' beschreibung='.$this->db_add_param($this->beschreibung).','.
			       ' sql='.$this->db_add_param($this->sql).','.
			       ' sqlundo='.$this->db_add_param($this->sqlundo).
			       " WHERE log_id=".$this->db_add_param($this->log_id, FHC_INTEGER).";";
		}

		if($this->db_query($qry))
		{
			if($new)
			{
				//Sequence Auslesen
				$qry = "SELECT currval('public.tbl_log_log_id_seq') as id";
				if($result = $this->db_query($qry))
				{
					if($row = $this->db_fetch_object($result))
					{
						$this->log_id = $row->id;
						$this->db_query('COMMIT;');
					}
					else
					{
						$this->errormsg = 'Fehler beim Auslesen der Sequence';
						$this->db_query('ROLLBACK');
					}
				}
				else
				{
					$this->errormsg = 'Fehler beim Auslesen der Sequence';
					$this->db_query('ROLLBACK');
				}
			}
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern des Undo-Befehls';
			return false;
		}
	}


	/**
	 * Loescht einen Log Eintrag
	 * @param $log_id ID des DS
	 * @return true wenn ok sonst false
	 */
	public function delete($log_id)
	{
		if(!is_numeric($log_id))
		{
			$this->errormsg = 'Log_id ist ungueltig';
			return false;
		}

		$qry = "DELETE FROM public.tbl_log WHERE log_id=".$this->db_add_param($log_id, FHC_INTEGER).';';

		if($this->db_query($qry))
			return true;
		else
		{
			$this->errormsg = 'Fehler beim Loeschen des LOG Eintrages';
			return false;
		}
	}

	/**
	 * Fuehrt einen UnDo Befehl aus und
	 * loescht anschliessend den Eintrag
	 * aus dem Log
	 * @param $log_id
	 * @return true wenn ok, sonst false
	 */
	public function undo($log_id)
	{
		if(!is_numeric($log_id))
		{
			$this->errormsg = 'Log_id ist ungueltig'.$log_id;
			return false;
		}
		$this->db_query('BEGIN;');
		
		//Undo Befehl aus Log holen
		$qry = "SELECT * FROM public.tbl_log WHERE log_id=".$this->db_add_param($log_id, FHC_INTEGER).";";
		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				if($row->sqlundo!='')
				{
					//UnDo Befehl ausfuehren
					if($this->db_query($row->sqlundo))
					{
						//Log Eintrag aus Log entfernen
						$qry = "DELETE FROM public.tbl_log WHERE log_id=".$this->db_add_param($log_id, FHC_INTEGER).';';
						if($this->db_query($qry))
						{
							$this->db_query('COMMIT;');
							return true;
						}
						else
						{
							$this->db_query('ROLLBACK;');
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