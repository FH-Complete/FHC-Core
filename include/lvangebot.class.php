<?php
/*
 * lvangebot.class.php
 * 
 * Copyright 2013 fhcomplete.org
 * 
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 * MA 02110-1301, USA.
 * 
 *
 * Authors: Martin Tatzber <tatzberm@technikum-wien.at
 */

require_once(dirname(__FILE__).'/basis_db.class.php');

class lvangebot extends basis_db
{
	private $new = true;			// boolean
	public $result = array();		// Objekte
	
	//Tabellenspalten
	protected $lvangebot_id;			// integer (PK)
	protected $lehrveranstaltung_id;	// integer (FK Lehrveranstaltung)
	protected $studiensemester_kurzbz;	// varchar(16) (FK Studiensemester)
	protected $gruppe_kurzbz;			// varchar(32) (FK Gruppe)
	protected $incomingplaetze;			// smallint
	protected $gesamtplaetze;			// smallint
	protected $anmeldefenster_start;	// timestamp
	protected $anmeldefenster_ende;		// timestamp
	protected $updateamum;				// timestamp
	protected $updatevon;				// varchar(32)
	protected $insertamum;				// timestamp
	protected $insertvon;				// varchar(32)
	
	/**
	 * Konstruktor
	 */
	public function __construct()
	{
		parent::__construct();
	}
	
	public function __set($name,$value)
	{
		$this->$name=$value;
	}

	public function __get($name)
	{
		return $this->$name;
	}
	
	/**
	 * Laden von LV-Angebot
	 * @param lvangebot_id ID des Datensatzes, der geladen werden soll
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function load($lvangebot_id)
	{
		if(!is_numeric($lvangebot_id))
		{
			$this->errormsg = 'lvangebot_id muss eine gueltige Zahl sein';
			return false;
		}

		$qry = "SELECT * FROM lehre.tbl_lvangebot WHERE lvangebot_id=".$this->db_add_param($lvangebot_id, FHC_INTEGER, false);

		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				$this->lvangebot_id=$row->lvangebot_id;
				$this->lehrveranstaltung_id=$row->lehrveranstaltung_id;
				$this->studiensemester_kurzbz=$row->studiensemester_kurzbz;
				$this->gruppe_kurzbz=$row->gruppe_kurzbz;
				$this->incomingplaetze=$row->incomingplaetze;
				$this->gesamtplaetze=$row->gesamtplaetze;
				$this->anmeldefenster_start=$row->anmeldefenster_start;
				$this->anmeldefenster_ende=$row->anmeldefenster_ende;
				$this->insertamum=$row->insertamum;
				$this->insertvon=$row->insertvon;
				$this->updatenamum=$row->updateamum;
				$this->updatevon=$row->updatevon;
			}
		}
		else
		{
			$this->errormsg = 'Datensatz konnte nicht geladen werden';
			return false;
		}

		return true;
	}

	/**
	 * Laden aller Einträge von LV-Angebot, die zu einer bestimmten LV gehören
	 * @param lv_id ID der LV, dessen Angebote geladen werden sollen
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function getAllFromLvId($lv_id)
	{
		if(!is_numeric($lv_id))
		{
			$this->errormsg='lv_id muss eine gültige Zahl sein!';
			return false;
		}
		
		$qry='SELECT tbl_lvangebot.* FROM lehre.tbl_lvangebot, public.tbl_studiensemester as stsem
			WHERE tbl_lvangebot.studiensemester_kurzbz=stsem.studiensemester_kurzbz
			AND lehrveranstaltung_id='.$this->db_add_param($lv_id, FHC_INTEGER, false).
			'ORDER BY stsem.start';
		
		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$lvangebot=new lvangebot();
				$lvangebot->lvangebot_id=$row->lvangebot_id;
				$lvangebot->lehrveranstaltung_id=$row->lehrveranstaltung_id;
				$lvangebot->studiensemester_kurzbz=$row->studiensemester_kurzbz;
				$lvangebot->gruppe_kurzbz=$row->gruppe_kurzbz;
				$lvangebot->incomingplaetze=$row->incomingplaetze;
				$lvangebot->gesamtplaetze=$row->gesamtplaetze;
				$lvangebot->anmeldefenster_start=$row->anmeldefenster_start;
				$lvangebot->anmeldefenster_ende=$row->anmeldefenster_ende;
				$lvangebot->insertamum=$row->insertamum;
				$lvangebot->insertvon=$row->insertvon;
				$lvangebot->updatenamum=$row->updateamum;
				$lvangebot->updatevon=$row->updatevon;
				
				$this->result[]=$lvangebot;
			}
		}
		else
		{
			$this->errormsg = 'Datensätze konnten nicht geladen werden';
			return false;
		}
		return true;
	}
	
	/**
	 * Prueft die Variablen auf Gueltigkeit
	 * @return true wenn ok, false im Fehlerfall
	 */
	protected function validate()
	{
		//Zahlenfelder pruefen
		if(!is_numeric($this->lehrveranstaltung_id))
		{
			$this->errormsg='lehrveranstaltung_id ist ungueltig';
			return false;
		}
		if(!is_numeric($this->incomingplaetze) && $this->incomingplaetze!=='')
		{
			$this->errormsg='incomingplaetze enthaelt ungueltige Zeichen';
			return false;
		}
		if(!is_numeric($this->gesamtplaetze) && $this->gesamtplaetze!=='')
		{
			$this->errormsg='gesamtplaetze enthaelt ungueltige Zeichen';
			return false;
		}

		//not null prüfen
		if($this->studiensemester_kurzbz=='')
		{
			$this->errormsg='studiensemester_kurzbz ist leer';
		}
		
		//Gesamtlaenge pruefen
		if(mb_strlen($this->studiensemester_kurzbz)>32)
		{
			$this->errormsg = 'studiensemester_kurzbz darf nicht länger als 32 Zeichen sein';
			return false;
		}
		if(mb_strlen($this->gruppe_kurzbz)>32)
		{
			$this->errormsg = 'Gruppe darf nicht länger als 32 Zeichen sein';
			return false;
		}
		
		//Anmeldefenster Start und Ende prüfen
		if($this->anmeldefenster_start>$this->anmeldefenster_ende)
		{
			$this->errormsg = 'Anmeldefenster: Ende liegt vor Start';
			return false;
		}
		$this->errormsg = '';
		return true;
	}
	
	/**
	 * Speichert den aktuellen Datensatz in die Datenbank
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function save()
	{
		//Variablen pruefen
		if(!$this->validate())
			return false;

		if($this->new)
		{
			//prüfen, ob Datensatz schon vorhanden
			$qry='SELECT studiensemester_kurzbz
				FROM lehre.tbl_lvangebot
				WHERE studiensemester_kurzbz='.$this->db_add_param($this->studiensemester_kurzbz).'
				AND lehrveranstaltung_id='.$this->db_add_param($this->lehrveranstaltung_id);
			if($this->db_query($qry))
			{
				if($row=$this->db_fetch_object())
				{
					$this->errormsg = 'Eintrag für '.$this->studiensemester_kurzbz.' existiert bereits';
					return false;
				}
			}
			
			//Neuen Datensatz einfuegen
			$qry='BEGIN;INSERT INTO lehre.tbl_lvangebot (lehrveranstaltung_id, studiensemester_kurzbz,
				gruppe_kurzbz, incomingplaetze, gesamtplaetze, anmeldefenster_start, anmeldefenster_ende,
				insertamum, insertvon) VALUES ('.

				$this->db_add_param($this->lehrveranstaltung_id, FHC_INTEGER).', '.
				$this->db_add_param($this->studiensemester_kurzbz).', '.
				$this->db_add_param($this->gruppe_kurzbz).', '.
				$this->db_add_param($this->incomingplaetze, FHC_INTEGER).', '.
				$this->db_add_param($this->gesamtplaetze, FHC_INTEGER).', '.
				$this->db_add_param($this->anmeldefenster_start).', '.
				$this->db_add_param($this->anmeldefenster_ende).', '.
				'now(), '.
				$this->db_add_param($this->insertvon).');';
		}
		else
		{
			//Pruefen ob lvangebot_id eine gueltige Zahl ist
			if(!is_numeric($this->lvangebot_id))
			{
				$this->errormsg = 'lvangebot_id muss eine gueltige Zahl sein';
				return false;
			}
			$qry='UPDATE lehre.tbl_lvangebot SET'.
				' lehrveranstaltung_id='.$this->db_add_param($this->lehrveranstaltung_id, FHC_INTEGER).', '.
				' studiensemester_kurzbz='.$this->db_add_param($this->studiensemester_kurzbz).', '.
				' gruppe_kurzbz='.$this->db_add_param($this->gruppe_kurzbz).', '.
				' incomingplaetze='.$this->db_add_param($this->incomingplaetze, FHC_INTEGER).', '.
		      	' gesamtplaetze='.$this->db_add_param($this->gesamtplaetze, FHC_INTEGER).', '.
		      	' anmeldefenster_start='.$this->db_add_param($this->anmeldefenster_start).', '.
		      	' anmeldefenster_ende='.$this->db_add_param($this->anmeldefenster_ende).', '.
		      	' updateamum= now(), '.
		      	' updatevon='.$this->db_add_param($this->updatevon).' '.
		      	' WHERE lvangebot_id='.$this->db_add_param($this->lvangebot_id, FHC_INTEGER, false).';';
		}
        
		if($this->db_query($qry))
		{
			if($this->new)
			{
				//naechste ID aus der Sequence holen
				$qry="SELECT currval('lehre.seq_lvangebot_lvangebot_id') as id;";
				if($this->db_query($qry))
				{
					if($row = $this->db_fetch_object())
					{
						$this->lvangebot_id = $row->id;
						$this->db_query('COMMIT');
					}
					else
					{
						$this->db_query('ROLLBACK');
						$this->errormsg = "Fehler beim Auslesen der Sequence";
						return false;
					}
				}
				else
				{
					$this->db_query('ROLLBACK');
					$this->errormsg = 'Fehler beim Auslesen der Sequence';
					return false;
				}
			}

		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern des Datensatzes';
			return false;
		}
		return $this->lvangebot_id;
	}

	/**
	 * Loescht den Datenensatz mit der ID die uebergeben wird
	 * @param $lvangebot_id ID die geloescht werden soll
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function delete($lvangebot_id)
	{
		//Pruefen ob lvangebot_id eine gueltige Zahl ist
		if(!is_numeric($lvangebot_id) || $lvangebot_id === '')
		{
			$this->errormsg = 'lvangebot_id muss eine gültige Zahl sein';
			return false;
		}

		//loeschen des Datensatzes
		$qry="DELETE FROM lehre.tbl_lvangebot WHERE lvangebot_id=".$this->db_add_param($lvangebot_id, FHC_INTEGER);

		if($this->db_query($qry))
		{
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Löschen von Eintrag '.$lvangebot_id;
			return false;
		}
	}
}
?>
