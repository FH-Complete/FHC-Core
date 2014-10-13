<?php
/* Copyright (C) 2014 fhcomplete.org
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
 * Authors: Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>
 */
/**
 * Klasse zur Verwaltung der Anwesenheiten der Studierenden
 */
require_once(dirname(__FILE__).'/basis_db.class.php');

class anwesenheit extends basis_db
{
	public $new=true;			//  boolean
	public $result = array();

	public $anwesenheit_id; 	// serial
	public $uid; 				// varchar(32)
	public $einheiten; 			// numeric(3,1)
	public $datum; 				// date
	public $anwesend;			// boolean
	public $lehreinheit_id;		// bigint
	public $anmerkung;			// varchar(256)

	/**
	 * Konstruktor
	 * @param $anwesenheit_id ID des Datensatzes der geladen werden soll (Default=null)
	 */
	public function __construct($anwesenheit_id=null)
	{
		parent::__construct();
		
		if(!is_null($anwesenheit_id))
			$this->load($anwesenheit_id);
	}

	/**
	 * Laedt den Datensatz mit der ID $anwesenheit_id
	 * @param  $anwesenheit_id ID des Datensatzes
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function load($anwesenheit_id)
	{
		//Pruefen ob anwesenheit_id eine gueltige Zahl ist
		if(!is_numeric($anwesenheit_id) || $anwesenheit_id == '')
		{
			$this->errormsg = 'Anwesenheit_id muss eine Zahl sein';
			return false;
		}

		//Daten aus der Datenbank lesen
		$qry = "SELECT * FROM campus.tbl_anwesenheit WHERE anwesenheit_id=".$this->db_add_param($anwesenheit_id, FHC_INTEGER, false);

		if(!$this->db_query($qry))
		{
			$this->errormsg = 'Fehler bei einer Datenbankabfrage';
			return false;
		}

		if($row = $this->db_fetch_object())
		{
			$this->anwesenheit_id = $row->anwesenheit_id;
			$this->uid = $row->uid;
			$this->einheiten = $row->einheiten;
			$this->datum = $row->datum;
			$this->anwesend = $this->db_parse_bool($row->anwesend);
			$this->lehreinheit_id = $row->lehreinheit_id;
			$this->anmerkung = $row->anmerkung;
			$this->new=false;
		}
		else
		{
			$this->errormsg = 'Es ist kein Datensatz mit dieser ID vorhanden';
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
		if(!is_numeric($this->anwesenheit_id) && $this->anwesenheit_id!='')
		{
			$this->errormsg='anwesenheit_id enthaelt ungueltige Zeichen';
			return false;
		}

		//Gesamtlaenge pruefen
		if(mb_strlen($this->anmerkung)>255)
		{
			$this->errormsg = 'Anmerkung darf nicht länger als 255 Zeichen sein';
			return false;
		}

		$this->errormsg = '';
		return true;
	}
	
	/**
	 * Speichert den aktuellen Datensatz in die Datenbank
	 * Wenn $neu auf true gesetzt ist wird ein neuer Datensatz angelegt
	 * andernfalls wird der bestehende Datensatz aktualisiert
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function save()
	{
		//Variablen pruefen
		if(!$this->validate())
			return false;

		if($this->new)
		{
			//Neuen Datensatz einfuegen
			$qry='BEGIN;INSERT INTO campus.tbl_anwesenheit (uid, einheiten, anwesend, datum, lehreinheit_id, anmerkung) VALUES('.
			      $this->db_add_param($this->uid).', '.
			      $this->db_add_param($this->einheiten).', '.
			      $this->db_add_param($this->anwesend, FHC_BOOLEAN).', '.
			      $this->db_add_param($this->datum).', '.
			      $this->db_add_param($this->lehreinheit_id).', '.
			      $this->db_add_param($this->anmerkung).');';
		}
		else
		{
			//Pruefen ob id eine gueltige Zahl ist
			if(!is_numeric($this->anwesenheit_id))
			{
				$this->errormsg = 'anwesenheit_id muss eine gueltige Zahl sein';
				return false;
			}
			$qry='UPDATE campus.tbl_anwesenheit SET'.
				' uid='.$this->db_add_param($this->uid).', '.
				' einheiten='.$this->db_add_param($this->einheiten).', '.
				' anwesend='.$this->db_add_param($this->anwesend,FHC_BOOLEAN).', '.
		      	' datum='.$this->db_add_param($this->datum).', '.
		      	' lehreinheit_id='.$this->db_add_param($this->lehreinheit_id, FHC_INTEGER).', '.
		      	' anmerkung='.$this->db_add_param($this->anmerkung).' '.
		      	'WHERE anwesenheit_id='.$this->db_add_param($this->anwesenheit_id, FHC_INTEGER, false).';';
		}
        
		if($this->db_query($qry))
		{
			if($this->new)
			{
				//naechste ID aus der Sequence holen
				$qry="SELECT currval('campus.seq_anwesenheit_anwesenheit_id') as id;";
				if($this->db_query($qry))
				{
					if($row = $this->db_fetch_object())
					{
						$this->anwesenheit_id = $row->id;
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
		return $this->anwesenheit_id;
	}

	/**
	 * Laedt die Anwesenheiten einer Lehreinheit/Datum
	 * @param $lehreinheit_id
	 * @param $datum
	 * @return boolean true wenn ok, false im Fehlerfall
	 */
	public function getAnwesenheitLehreinheit($lehreinheit_id, $datum=null)
	{
		$qry = "SELECT * FROM campus.tbl_anwesenheit 
			WHERE 
				lehreinheit_id=".$this->db_add_param($lehreinheit_id, FHC_INTEGER);

		if(!is_null($datum))
			$qry.=" AND datum=".$this->db_add_param($datum);
		
		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				$obj = new anwesenheit();

				$obj->anwesenheit_id = $row->anwesenheit_id;
				$obj->uid = $row->uid;
				$obj->einheiten = $row->einheiten;
				$obj->datum = $row->datum;
				$obj->anwesend = $this->db_parse_bool($row->anwesend);
				$obj->lehreinheit_id = $row->lehreinheit_id;
				$obj->anmerkung = $row->anmerkung;
					
				$this->result[] = $obj;
			}
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Daten';
			return false;
		}
	}
}
?>
