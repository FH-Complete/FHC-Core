<?php
/*
 * studienplan.class.php
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
 * Authors: Andreas Österreicher <andreas.oesterreicher@technikum-wien.at>
 */

require_once(dirname(__FILE__).'/basis_db.class.php');

class studienplan extends basis_db
{
	private $new = true;			// boolean
	public $result = array();		// Objekte
	
	//Tabellenspalten
	protected $studienplan_id;			// integer (PK)
	protected $studienordnung_id;		// integer FK Studienordnung
	protected $orgform_kurzbz; 			// varchar (3)
	protected $version;					// varchar (256)
	protected $bezeichnung;				// varchar (256)
	protected $regelstudiendauer;		// integer
	protected $sprache;					// varchar (16) FK Sprache
	protected $aktiv=false;				// boolean
	protected $semesterwochen;			// smallint
	protected $testtool_sprachwahl=true;// boolean
	protected $updateamum;				// timestamp
	protected $updatevon;				// varchar
	protected $insertamum;				// timestamp
	protected $insertvon;				// varchar
	
	//Tabellenspalten für Zwischentabelle tbl_studienplan_lehrveranstaltung
	protected $studienplan_lehrveranstaltung_id;		//integer
	protected $lehrveranstaltung_id;					//integer
	protected $semester;								//smallint
	protected $studienplan_lehrveranstaltung_id_parent;	//integer
	protected $pflicht;									//boolean
	protected $koordinator;								//varchar(32)



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
	 * Laedt Studienplan mit der ID $studienplan_id
	 * @param  $studienplan_id ID des zu ladenden Studienplanes
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function loadStudienplan($studienplan_id)
	{
		//Pruefen ob studienplan_id eine gueltige Zahl ist
		if(!is_numeric($studienplan_id) || $studienplan_id === '')
		{
			$this->errormsg = 'Studienplan_id muss eine Zahl sein';
			return false;
		}

		//Daten aus der Datenbank lesen
		$qry = "SELECT * FROM lehre.tbl_studienplan WHERE studienplan_id=".$this->db_add_param($studienplan_id, FHC_INTEGER, false);

		if(!$this->db_query($qry))
		{
			$this->errormsg = 'Fehler bei einer Datenbankabfrage';
			return false;
		}

		if($row = $this->db_fetch_object())
		{
			$this->studienplan_id = $row->studienplan_id;
			$this->studienordnung_id = $row->studienordnung_id;
			$this->orgform_kurzbz = $row->orgform_kurzbz;
			$this->version = $row->version;
			$this->bezeichnung = $row->bezeichnung;
			$this->regelstudiendauer = $row->regelstudiendauer;
			$this->sprache = $row->sprache;
			$this->aktiv = $this->db_parse_bool($row->aktiv);
			$this->semesterwochen = $row->semesterwochen;
			$this->testtool_sprachwahl = $this->db_parse_bool($row->testtool_sprachwahl);
			$this->updateamum = $row->updateamum;
			$this->updatevon = $row->updatevon;
			$this->insertamum = $row->insertamum;
			$this->insertvon = $row->insertvon;
			$this->new=false;

			return true;
		}
		else
		{
			$this->errormsg = 'Es ist kein Datensatz mit dieser ID vorhanden';
			return false;
		}
	}

	/**
	 * Laedt die Studienplaene einer Studienordnung und Optional einer Organisationsform
	 *
	 * @param $studienordnung_id ID der Studienordnung
	 * @param $orgform_kurzbz Organisationsform
	 * @return boolean true wenn ok, false im Fehlerfall
	 */
	public function loadStudienplanSTO($studienordnung_id, $orgform_kurzbz=null)
	{
		//Pruefen ob studienordnung_id eine gueltige Zahl ist
		if(!is_numeric($studienordnung_id) || $studienordnung_id === '')
		{
			$this->errormsg = 'Studienordnung_id muss eine Zahl sein';
			return false;
		}

		//Daten aus der Datenbank lesen
		$qry = "SELECT * FROM lehre.tbl_studienplan WHERE studienordnung_id=".$this->db_add_param($studienordnung_id, FHC_INTEGER, false);

		if(!is_null($orgform_kurzbz))
			$qry.=" AND orgform_kurzbz=".$this->db_add_param($orgform_kurzbz);
		
		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$obj = new studienplan();

				$obj->studienplan_id = $row->studienplan_id;
				$obj->studienordnung_id = $row->studienordnung_id;
				$obj->orgform_kurzbz = $row->orgform_kurzbz;
				$obj->version = $row->version;
				$obj->bezeichnung = $row->bezeichnung;
				$obj->regelstudiendauer = $row->regelstudiendauer;
				$obj->sprache = $row->sprache;
				$obj->aktiv = $this->db_parse_bool($row->aktiv);
				$obj->semesterwochen = $row->semesterwochen;
				$obj->testtool_sprachwahl = $this->db_parse_bool($row->testtool_sprachwahl);
				$obj->updateamum = $row->updateamum;
				$obj->updatevon = $row->updatevon;
				$obj->insertamum = $row->insertamum;
				$obj->insertvon = $row->insertvon;
				$obj->new=false;

				$this->result[] = $obj;
			}

			return true;
		}
		else
		{
			$this->errormsg = 'Fehler bei einer Datenbankabfrage';
			return false;
		}
	}

	/**
	 * Prueft die Variablen auf Gueltigkeit
	 * @return true wenn ok, false im Fehlerfall
	 */
	protected function validate()
	{
		//Zahlenfelder pruefen
		if(!is_numeric($this->studienordnung_id))
		{
			$this->errormsg='studienordnung_id enthaelt ungueltige Zeichen';
			return false;
		}

		if(!is_numeric($this->regelstudiendauer) && $this->regelstudiendauer!=='')
		{
			$this->errormsg='regelstudiendauer enthaelt ungueltige Zeichen';
			return false;
		}
		if(!is_numeric($this->semesterwochen) && $this->semesterwochen!=='')
		{
			$this->errormsg='semesterwochen enthaelt ungueltige Zeichen';
			return false;
		}

		//Gesamtlaenge pruefen
		if(mb_strlen($this->version)>256)
		{
			$this->errormsg = 'Version darf nicht länger als 256 Zeichen sein';
			return false;
		}
		if(mb_strlen($this->bezeichnung)>256)
		{
			$this->errormsg = 'Bezeichnung darf nicht länger als 256 Zeichen sein';
			return false;
		}
		if(mb_strlen($this->orgform_kurzbz)>3)
		{
			$this->errormsg = 'Orgform_kurzbz darf nicht laenger als 3 Zeichen sein';
			return false;
		}
		if(mb_strlen($this->sprache)>16)
		{
			$this->errormsg = 'sprache darf nicht laenger als 16 Zeichen sein';
			return false;
		}
		if(!is_bool($this->aktiv))
		{
			$this->errormsg='Aktiv ist ungueltig';
			return false;
		}
		if(!is_bool($this->testtool_sprachwahl))
		{
			$this->errormsg='Testtool_sprachwahl ist ungueltig';
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
			//Neuen Datensatz einfuegen
			$qry='BEGIN;INSERT INTO lehre.tbl_studienplan (studienordnung_id, orgform_kurzbz,version, 
				bezeichnung, regelstudiendauer, sprache, aktiv, semesterwochen, testtool_sprachwahl,
				insertamum, insertvon) VALUES ('.
			      $this->db_add_param($this->studienordnung_id, FHC_INTEGER).', '.
			      $this->db_add_param($this->orgform_kurzbz).', '.
			      $this->db_add_param($this->version).', '.
			      $this->db_add_param($this->bezeichnung).', '.
			      $this->db_add_param($this->regelstudiendauer, FHC_INTEGER).', '.
			      $this->db_add_param($this->sprache).', '.
			      $this->db_add_param($this->aktiv, FHC_BOOLEAN).', '.
			      $this->db_add_param($this->semesterwochen,FHC_INTEGER).', '.
			      $this->db_add_param($this->testtool_sprachwahl,FHC_BOOLEAN).', '.
			      'now(), '.
			      $this->db_add_param($this->insertvon).');';
		}
		else
		{
			//Pruefen ob studienplan_id eine gueltige Zahl ist
			if(!is_numeric($this->studienplan_id))
			{
				$this->errormsg = 'studienplan_id muss eine gueltige Zahl sein';
				return false;
			}
			$qry='UPDATE lehre.tbl_studienplan SET'.
				' studienordnung_id='.$this->db_add_param($this->studienordnung_id, FHC_INTEGER).', '.
				' orgform_kurzbz='.$this->db_add_param($this->orgform_kurzbz).', '.
				' version='.$this->db_add_param($this->version).', '.
				' bezeichnung='.$this->db_add_param($this->bezeichnung).', '.
		      	' regelstudiendauer='.$this->db_add_param($this->regelstudiendauer, FHC_INTEGER).', '.
		      	' sprache='.$this->db_add_param($this->sprache).', '.
		      	' aktiv='.$this->db_add_param($this->aktiv, FHC_BOOLEAN).', '.
		      	' semesterwochen='.$this->db_add_param($this->semesterwochen, FHC_INTEGER).', '.
		      	' testtool_sprachwahl='.$this->db_add_param($this->testtool_sprachwahl, FHC_BOOLEAN).','.
		      	' updateamum= now(), '.
		      	' updatevon='.$this->db_add_param($this->updatevon).' '.
		      	' WHERE studienplan_id='.$this->db_add_param($this->studienplan_id, FHC_INTEGER, false).';';
		}
        
		if($this->db_query($qry))
		{
			if($this->new)
			{
				//naechste ID aus der Sequence holen
				$qry="SELECT currval('lehre.seq_studienplan_studienplan_id') as id;";
				if($this->db_query($qry))
				{
					if($row = $this->db_fetch_object())
					{
						$this->studienplan_id = $row->id;
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
		return $this->studienplan_id;
	}

	/**
	 * Loescht den Datenensatz mit der ID die uebergeben wird
	 * @param $studienplan_id ID die geloescht werden soll
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function delete($studienplan_id)
	{
		//Pruefen ob studienplan_id eine gueltige Zahl ist
		if(!is_numeric($studienplan_id) || $studienplan_id === '')
		{
			$this->errormsg = 'studienplan_id muss eine gültige Zahl sein'."\n";
			return false;
		}

		//loeschen des Datensatzes
		$qry="DELETE FROM public.tbl_studienplan WHERE studienplan_id=".$this->db_add_param($studienplan_id, FHC_INTEGER, false).";";

		if($this->db_query($qry))
		{
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Löschen der Daten'."\n";
			return false;
		}
	}

	public function cleanResult()
	{
		$data = array();

		if(count($this->result)>0)
		{
			foreach($this->result as $row)
			{
				$obj = new stdClass();
				$obj->studienplan_id = $row->studienplan_id;
				$obj->studienordnung_id = $row->studienordnung_id;
				$obj->orgform_kurzbz = $row->orgform_kurzbz;
				$obj->version = $row->version;
				$obj->bezeichnung = $row->bezeichnung;
				$obj->regelstudiendauer = $row->regelstudiendauer;
				$obj->sprache = $row->sprache;
				$obj->aktiv = $row->aktiv;
				$obj->semesterwochen = $row->semesterwochen;
				$obj->testtool_sprachwahl = $row->testtool_sprachwahl;
				$obj->updateamum = $row->updateamum;
				$obj->updatevon = $row->updatevon;
				$obj->insertamum = $row->insertamum;
				$obj->insertvon = $row->insertvon;
				$obj->studienplan_lehrveranstaltung_id = $row->studienplan_lehrveranstaltung_id;
				$obj->lehrveranstaltung_id = $row->lehrveranstaltung_id;
				$obj->semester = $row->semester;
				$obj->studienplan_lehrveranstaltung_id_parent = $row->studienplan_lehrveranstaltung_id_parent;
				$obj->pflicht = $row->pflicht;
				$obj->koordinator = $row->koordinator;	
				$data[]=$obj;
			}
		}
		else
		{
			$obj = new stdClass();
			$obj->studienplan_id = $this->studienplan_id;
			$obj->studienordnung_id = $this->studienordnung_id;
			$obj->orgform_kurzbz = $this->orgform_kurzbz;
			$obj->version = $this->version;
			$obj->bezeichnung = $this->bezeichnung;
			$obj->regelstudiendauer = $this->regelstudiendauer;
			$obj->sprache = $this->sprache;
			$obj->aktiv = $this->aktiv;
			$obj->semesterwochen = $this->semesterwochen;
			$obj->testtool_sprachwahl = $this->testtool_sprachwahl;
			$obj->updateamum = $this->updateamum;
			$obj->updatevon = $this->updatevon;
			$obj->insertamum = $this->insertamum;
			$obj->insertvon = $this->insertvon;
			$obj->studienplan_lehrveranstaltung_id = $this->studienplan_lehrveranstaltung_id;
			$obj->lehrveranstaltung_id = $this->lehrveranstaltung_id;
			$obj->semester = $this->semester;
			$obj->studienplan_lehrveranstaltung_id_parent = $this->studienplan_lehrveranstaltung_id_parent;
			$obj->pflicht = $this->pflicht;
			$obj->koordinator = $this->koordinator;
			$data[]=$obj;
		}
		return $data;
	}
	
	/**
	 * Prüft ob eine Lehrveranstaltung im Studienplan enthalten ist
	 * @return true wenn ja, sonst false
	 */
	public function containsLehrveranstaltung($studienplan_id, $lehrveranstaltung_id)
		{
		if (!is_numeric($studienplan_id) || $studienplan_id === '')
		{
			$this->errormsg = 'StudienplanID ist ungueltig';
			return false;
		}
		if (!is_numeric($lehrveranstaltung_id) || $lehrveranstaltung_id === '')
		{
			$this->errormsg = 'LehrveranstaltungID ist ungueltig';
			return false;
		}

		$qry = "SELECT 
					studienplan_lehrveranstaltung_id,
					semester as stpllv_semester,
					pflicht as stpllv_pflicht,
					koordinator as stpllv_koordinator,
					studienplan_lehrveranstaltung_id_parent
				FROM 
					lehre.tbl_studienplan_lehrveranstaltung
				WHERE
					studienplan_id=" . $this->db_add_param($studienplan_id, FHC_INTEGER).
					" AND lehrveranstaltung_id=".$this->db_add_param($lehrveranstaltung_id, FHC_INTEGER).";";
	
		if (!$this->db_query($qry))
		{
			$this->errormsg = 'Datensatz konnte nicht geladen werden';
			return false;
		}
		if($this->db_num_rows()!=0)
		{
			return true;
		} 
		else
		{
			return false;
		}
	}
	
	/**
	 * Lädt eine Lehrveranstaltung eines Studienplans aus der
	 * Zwischentabelle tbl_studienplan_lehrveranstaltung
	 * @param Studienplan ID
	 * @param Lehrveranstaltung ID
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function loadLehrveranstaltungStudienplanByLvId($studienplan_id, $lehrveranstaltung_id){
		if($this->containsLehrveranstaltung($studienplan_id, $lehrveranstaltung_id))
		{
			if (!is_numeric($studienplan_id) || $studienplan_id === '') {
				$this->errormsg = 'StudienplanID ist ungueltig';
				return false;
			}
			if (!is_numeric($lehrveranstaltung_id) || $lehrveranstaltung_id === '') {
				$this->errormsg = 'LehrveranstaltungID ist ungueltig';
				return false;
			}

			$qry = "SELECT 
						tbl_lehrveranstaltung.*,
						tbl_studienplan_lehrveranstaltung.studienplan_id,
						tbl_studienplan_lehrveranstaltung.studienplan_lehrveranstaltung_id,
						tbl_studienplan_lehrveranstaltung.semester as stpllv_semester,
						tbl_studienplan_lehrveranstaltung.pflicht as stpllv_pflicht,
						tbl_studienplan_lehrveranstaltung.koordinator as stpllv_koordinator,
						tbl_studienplan_lehrveranstaltung.studienplan_lehrveranstaltung_id_parent
					FROM 
						lehre.tbl_lehrveranstaltung
						JOIN lehre.tbl_studienplan_lehrveranstaltung USING(lehrveranstaltung_id)
					WHERE
						tbl_studienplan_lehrveranstaltung.studienplan_id=" . $this->db_add_param($studienplan_id, FHC_INTEGER).
						" AND tbl_lehrveranstaltung.lehrveranstaltung_id=".$this->db_add_param($lehrveranstaltung_id, FHC_INTEGER).";";

			if (!$this->db_query($qry)) {
				$this->errormsg = 'Datensatz konnte nicht geladen werden';
				return false;
			}

			if ($row = $this->db_fetch_object()) {
				$this->studienplan_id = $row->studienplan_id;
				$this->stpllv_semester = $row->stpllv_semester;
				$this->stpllv_pflicht = $this->db_parse_bool($row->stpllv_pflicht);
				$this->stpllv_koordinator = $row->stpllv_koordinator;
				$this->studienplan_lehrveranstaltung_id = $row->studienplan_lehrveranstaltung_id;
				$this->studienplan_lehrveranstaltung_id_parent = $row->studienplan_lehrveranstaltung_id_parent;
				$this->new = false;
			}
			return true;
		}
		else
		{
			return false;
		}
	}
	
	/**
	 * Speichert die Zuordnung einer Lehrveranstaltung zu einem Studienplan
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function saveStudienplanLehrveranstaltung() {

		if ($this->new) {
			//Neuen Datensatz einfuegen
			$qry = 'BEGIN;INSERT INTO lehre.tbl_studienplan_lehrveranstaltung (studienplan_id, lehrveranstaltung_id,
				semester,studienplan_lehrveranstaltung_id_parent,pflicht, koordinator,
				insertamum, insertvon) VALUES (' .
					$this->db_add_param($this->studienplan_id, FHC_INTEGER) . ', ' .
					$this->db_add_param($this->lehrveranstaltung_id, FHC_INTEGER) . ', ' .
					$this->db_add_param($this->stpllv_semester, FHC_INTEGER) . ', ' .
					$this->db_add_param($this->studienplan_lehrveranstaltung_id_parent, FHC_INTEGER) . ', ' .
					$this->db_add_param($this->stpllv_pflicht, FHC_BOOLEAN) . ', ' .
					$this->db_add_param($this->stpllv_koordinator) . ', ' .
					'now(), ' .
					$this->db_add_param($this->insertvon) . ');';
		} else {
			//Pruefen ob studienplan_id eine gueltige Zahl ist
			if (!is_numeric($this->studienplan_lehrveranstaltung_id)) {
				$this->errormsg = 'studienplan_lehrveranstaltung_id muss eine gueltige Zahl sein';
				return false;
			}
			$qry = 'UPDATE lehre.tbl_studienplan_lehrveranstaltung SET' .
					' studienplan_id=' . $this->db_add_param($this->studienplan_id, FHC_INTEGER) . ', ' .
					' lehrveranstaltung_id=' . $this->db_add_param($this->lehrveranstaltung_id, FHC_INTEGER) . ', ' .
					' semester=' . $this->db_add_param($this->stpllv_semester, FHC_INTEGER) . ', ' .
					' studienplan_lehrveranstaltung_id_parent=' . $this->db_add_param($this->studienplan_lehrveranstaltung_id_parent, FHC_INTEGER) . ', ' .
					' pflicht=' . $this->db_add_param($this->stpllv_pflicht, FHC_BOOLEAN) . ', ' .
					//TODO sprache in Tabelle nicht vorhanden' sprache=' . $this->db_add_param($this->sprache) . ', ' .
					' koordinator=' . $this->db_add_param($this->stpllv_koordinator) . ', ' .
					' updateamum= now(), ' .
					' updatevon=' . $this->db_add_param($this->updatevon) . ' ' .
					' WHERE studienplan_lehrveranstaltung_id=' . $this->db_add_param($this->studienplan_lehrveranstaltung_id, FHC_INTEGER, false) . ';';
		}

		if ($this->db_query($qry)) {
			if ($this->new) {
				//naechste ID aus der Sequence holen
				$qry = "SELECT currval('lehre.seq_studienplan_studienplan_lehrveranstaltung_id') as id;";
				if ($this->db_query($qry)) {
					if ($row = $this->db_fetch_object()) {
						$this->studienplan_lehrveranstaltung_id = $row->id;
						$this->db_query('COMMIT');
					} else {
						$this->db_query('ROLLBACK');
						$this->errormsg = "Fehler beim Auslesen der Sequence";
						return false;
					}
				} else {
					$this->db_query('ROLLBACK');
					$this->errormsg = 'Fehler beim Auslesen der Sequence';
					return false;
				}
			}
		} else {
			$this->errormsg = 'Fehler beim Speichern des Datensatzes';
			return false;
		}
		return $this->studienplan_lehrveranstaltung_id;
	}
}
?>
