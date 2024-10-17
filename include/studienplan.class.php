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
 * 			Stefan Puraner	<puraner@technikum-wien.at>
 * 			Andreas Moik	<moik@technikum-wien.at>
 *          Cristina Hainberger <hainberg@technikum-wien.at>
 */

require_once(dirname(__FILE__).'/basis_db.class.php');
require_once(dirname(__FILE__).'/lehrveranstaltung.class.php');
require_once(dirname(__FILE__).'/studienordnung.class.php');

class studienplan extends basis_db
{
	public $new = true;			// boolean
	public $result = array();		// Objekte

	//Tabellenspalten
	public $studienplan_id;					// integer (PK)
	public $studienordnung_id;				// integer FK Studienordnung
	public $orgform_kurzbz; 				// varchar (3)
	public $version;						// varchar (256)
	public $bezeichnung;					// varchar (256)
	public $regelstudiendauer;				// integer
	public $sprache;						// varchar (16) FK Sprache
	public $aktiv=false;					// boolean
	public $semesterwochen;					// smallint
	public $testtool_sprachwahl=true;		// boolean
	public $updateamum;						// timestamp
	public $updatevon;						// varchar
	public $insertamum;						// timestamp
	public $insertvon;						// varchar
	public $ects_stpl;						//numeric(5,2)
	public $pflicht_sws;					// integer
	public $pflicht_lvs;					// integer
	public $onlinebewerbung_studienplan;	//boolean

	//Tabellenspalten für Zwischentabelle tbl_studienplan_lehrveranstaltung
	public $studienplan_lehrveranstaltung_id;		//integer
	public $lehrveranstaltung_id;					//integer
	public $semester;								//smallint
	public $studienplan_lehrveranstaltung_id_parent;	//integer
	public $pflicht;									//boolean
	public $koordinator;								//varchar(32)
	public $sort;
	public $curriculum=true;
	public $export=true;
	public $genehmigung=true;

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
			$this->ects_stpl = $row->ects_stpl;
			$this->pflicht_lvs = $row->pflicht_lvs;
			$this->pflicht_sws = $row->pflicht_sws;
			$this->onlinebewerbung_studienplan = $this->db_parse_bool($row->onlinebewerbung_studienplan);
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

	public function deleteSemesterZuordnung($studienplan_id, $studiensemester_kurzbz, $ausbildungssemester = NULL)
	{
		if (!is_numeric($studienplan_id))
		{
			$this->errormsg = 'studienplan_id muss eine gueltige Zahl sein';
			return false;
		}

		if (!is_string($studiensemester_kurzbz) || strlen($studiensemester_kurzbz) != 6)
		{
			$this->errormsg = 'studiensemester_kurzbz muss ein String mit 6 Zeichen sein';
			return false;
		}

		$qry = 'DELETE FROM lehre.tbl_studienplan_semester
				WHERE studienplan_id=' . $this->db_add_param($studienplan_id) . ' AND
					studiensemester_kurzbz=' . $this->db_add_param($studiensemester_kurzbz) . '';

		if ($ausbildungssemester !== null)
			$qry.=' AND semester=' . $this->db_add_param($ausbildungssemester) . '';

		$qry.=';';

		if ($this->db_query($qry))
		{
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Löschen der Zuordnung' . "\n";
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
		$qry.=" ORDER BY bezeichnung";

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
				$obj->ects_stpl = $row->ects_stpl;
				$obj->pflicht_lvs = $row->pflicht_lvs;
				$obj->pflicht_sws = $row->pflicht_sws;
				$obj->onlinebewerbung_studienplan = $this->db_parse_bool($row->onlinebewerbung_studienplan);
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

		if(!is_numeric($this->regelstudiendauer))
		{
			$this->errormsg='regelstudiendauer enthaelt ungueltige Zeichen oder ist leer';
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
				pflicht_sws, pflicht_lvs, ects_stpl, onlinebewerbung_studienplan, insertamum, insertvon) VALUES ('.
			$this->db_add_param($this->studienordnung_id, FHC_INTEGER).', '.
			$this->db_add_param($this->orgform_kurzbz).', '.
			$this->db_add_param($this->version).', '.
			$this->db_add_param($this->bezeichnung).', '.
			$this->db_add_param($this->regelstudiendauer, FHC_INTEGER).', '.
			$this->db_add_param($this->sprache).', '.
			$this->db_add_param($this->aktiv, FHC_BOOLEAN).', '.
			$this->db_add_param($this->semesterwochen,FHC_INTEGER).', '.
			$this->db_add_param($this->testtool_sprachwahl,FHC_BOOLEAN).', '.
			$this->db_add_param($this->pflicht_sws) . ', ' .
			$this->db_add_param($this->pflicht_lvs) . ', ' .
			$this->db_add_param($this->ects_stpl) . ', ' .
			$this->db_add_param($this->onlinebewerbung_studienplan, FHC_BOOLEAN) . ', ' .
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
				' ects_stpl=' . $this->db_add_param($this->ects_stpl) . ',' .
				' pflicht_sws=' . $this->db_add_param($this->pflicht_sws, FHC_INTEGER) . ',' .
				' pflicht_lvs=' . $this->db_add_param($this->pflicht_lvs, FHC_INTEGER) . ',' .
				' onlinebewerbung_studienplan=' . $this->db_add_param($this->onlinebewerbung_studienplan, FHC_BOOLEAN) . ',' .
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
		$qry="DELETE FROM lehre.tbl_studienplan WHERE studienplan_id=".$this->db_add_param($studienplan_id, FHC_INTEGER, false).";";

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

	/**
	 * Baut die Datenstruktur für senden als JSON Objekt auf
	 */
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
				$obj->sort = $row->sort;
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
			$obj->sort = $this->sort;
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

		$qry = "SELECT 1 FROM
					lehre.tbl_studienplan_lehrveranstaltung
				WHERE
					studienplan_id=".$this->db_add_param($studienplan_id, FHC_INTEGER).
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
	 * Speichert die Zuordnung einer Lehrveranstaltung zu einem Studienplan
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function saveStudienplanLehrveranstaltung()
	{

		$lv = new lehrveranstaltung();
		$lv->load($this->lehrveranstaltung_id);
		if ($lv->lehrtyp_kurzbz == 'tpl') {
			$lv->lehrtyp_kurzbz = 'lv';
			$sp = new studienplan();
			$sp->loadStudienplan($this->studienplan_id);
			$so = new studienordnung();
			$so->loadStudienordnung($sp->studienordnung_id);
			$lv->studiengang_kz = $so->studiengang_kz;
			$lv->semester = $this->semester;
			$lv->lehrveranstaltung_template_id = $this->lehrveranstaltung_id;
			if (!$lv->save(true)) {
				$this->errormsg = "Fehler beim kopieren des Templates: " . $lv->errormsg;
				return false;
			}

			$this->lehrveranstaltung_id = $lv->lehrveranstaltung_id;
		}

		if ($this->new)
		{
			//Neuen Datensatz einfuegen
			$qry = 'BEGIN;INSERT INTO lehre.tbl_studienplan_lehrveranstaltung (studienplan_id, lehrveranstaltung_id,
				semester,studienplan_lehrveranstaltung_id_parent,pflicht, koordinator, curriculum, export, genehmigung,
				insertamum, insertvon) VALUES (' .
					$this->db_add_param($this->studienplan_id, FHC_INTEGER) . ', ' .
					$this->db_add_param($this->lehrveranstaltung_id, FHC_INTEGER) . ', ' .
					$this->db_add_param($this->semester, FHC_INTEGER) . ', ' .
					$this->db_add_param($this->studienplan_lehrveranstaltung_id_parent, FHC_INTEGER) . ', ' .
					$this->db_add_param($this->pflicht, FHC_BOOLEAN) . ', ' .
					$this->db_add_param($this->koordinator) . ', ' .
					$this->db_add_param($this->curriculum, FHC_BOOLEAN) . ', ' .
					$this->db_add_param($this->export, FHC_BOOLEAN) . ', ' .
					$this->db_add_param($this->genehmigung, FHC_BOOLEAN) . ', ' .
					'now(), ' .
					$this->db_add_param($this->insertvon) . ');';
		}
		else
		{
			//Pruefen ob studienplan_id eine gueltige Zahl ist
			if (!is_numeric($this->studienplan_lehrveranstaltung_id))
			{
				$this->errormsg = 'studienplan_lehrveranstaltung_id muss eine gueltige Zahl sein';
				return false;
			}

			$qry = 'UPDATE lehre.tbl_studienplan_lehrveranstaltung SET' .
					' studienplan_id=' . $this->db_add_param($this->studienplan_id, FHC_INTEGER) . ', ' .
					' lehrveranstaltung_id=' . $this->db_add_param($this->lehrveranstaltung_id, FHC_INTEGER) . ', ' .
					' semester=' . $this->db_add_param($this->semester, FHC_INTEGER) . ', ' .
					' studienplan_lehrveranstaltung_id_parent=' . $this->db_add_param($this->studienplan_lehrveranstaltung_id_parent, FHC_INTEGER) . ', ' .
					' pflicht=' . $this->db_add_param($this->pflicht, FHC_BOOLEAN) . ', ' .
					' koordinator=' . $this->db_add_param($this->koordinator) . ', ' .
					' curriculum=' . $this->db_add_param($this->curriculum, FHC_BOOLEAN) . ', ' .
					' export=' . $this->db_add_param($this->export, FHC_BOOLEAN) . ', ' .
					' genehmigung=' . $this->db_add_param($this->genehmigung, FHC_BOOLEAN) . ', ' .
					' updateamum= now(), ' .
					' updatevon=' . $this->db_add_param($this->updatevon) . ' ' .
					' WHERE studienplan_lehrveranstaltung_id=' . $this->db_add_param($this->studienplan_lehrveranstaltung_id, FHC_INTEGER, false) . ';';

			// Bei allen darunterliegenden Zuordnungen wird das Semester angepasst, damit beim
			// verschieben von Modulen die darunterliegenden Eintraege korrekt sind
			$qry.='
			UPDATE lehre.tbl_studienplan_lehrveranstaltung SET semester='.$this->db_add_param($this->semester).'
			WHERE studienplan_lehrveranstaltung_id IN(
			WITH RECURSIVE stpllv(studienplan_lehrveranstaltung_id, studienplan_lehrveranstaltung_id_parent) as
			(
				SELECT studienplan_lehrveranstaltung_id, studienplan_lehrveranstaltung_id_parent
				FROM lehre.tbl_studienplan_lehrveranstaltung
				WHERE studienplan_lehrveranstaltung_id='.$this->db_add_param($this->studienplan_lehrveranstaltung_id, FHC_INTEGER).'
				UNION ALL
				SELECT st.studienplan_lehrveranstaltung_id, st.studienplan_lehrveranstaltung_id_parent
				FROM lehre.tbl_studienplan_lehrveranstaltung st, stpllv
				WHERE st.studienplan_lehrveranstaltung_id_parent=stpllv.studienplan_lehrveranstaltung_id
			)
			SELECT studienplan_lehrveranstaltung_id
			FROM stpllv
			GROUP BY studienplan_lehrveranstaltung_id);';
		}

		if ($this->db_query($qry))
		{
			if ($this->new)
			{
				//naechste ID aus der Sequence holen
				$qry = "SELECT currval('lehre.seq_studienplan_studienplan_lehrveranstaltung_id') as id;";
				if ($this->db_query($qry))
				{
					if ($row = $this->db_fetch_object())
					{
						$this->studienplan_lehrveranstaltung_id = $row->id;
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
		return $this->studienplan_lehrveranstaltung_id;
	}

	/**
	 * Laedt einen StudienplanLehrveranstaltung Eintrag
	 *
	 * @param $studienplan_lehrveranstaltung_id ID der Zuordnung
	 */
	public function loadStudienplanLehrveranstaltung($studienplan_lehrveranstaltung_id)
	{
		$qry = "SELECT * FROM lehre.tbl_studienplan_lehrveranstaltung WHERE studienplan_lehrveranstaltung_id=".$this->db_add_param($studienplan_lehrveranstaltung_id);

		if($result = $this->db_query($qry))
		{
			if($row = $this->db_fetch_object($result))
			{
				$this->studienplan_lehrveranstaltung_id = $row->studienplan_lehrveranstaltung_id;
				$this->semester = $row->semester;
				$this->pflicht = $this->db_parse_bool($row->pflicht);
				$this->studienplan_id = $row->studienplan_id;
				$this->koordinator = $row->koordinator;
				$this->studienplan_lehrveranstaltung_id_parent = $row->studienplan_lehrveranstaltung_id_parent;
				$this->lehrveranstaltung_id = $row->lehrveranstaltung_id;
				$this->insertamum = $row->insertamum;
				$this->insertvon = $row->insertvon;
				$this->updateamum = $row->updateamum;
				$this->updatevon = $row->updatevon;
				$this->sort = $row->sort;
				$this->curriculum = $this->db_parse_bool($row->curriculum);
				$this->export = $this->db_parse_bool($row->export);
				$this->new=false;
				return true;
			}
			else
			{
				$this->errormsg = 'Fehler beim Laden der Daten';
				return false;
			}
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Daten';
			return false;
		}
	}

	/**
	 * Laedt die Lehrveranstaltungszuordnungen zu einem Studienplan
	 *
	 * @param $studienplan_id ID des Studienplanes
	 */
	public function loadStudienplanLV($studienplan_id)
	{
		$qry = "SELECT * FROM lehre.tbl_studienplan_lehrveranstaltung WHERE studienplan_id=".$this->db_add_param($studienplan_id);

		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				$obj = new studienplan();

				$obj->studienplan_lehrveranstaltung_id = $row->studienplan_lehrveranstaltung_id;
				$obj->semester = $row->semester;
				$obj->pflicht = $this->db_parse_bool($row->pflicht);
				$obj->studienplan_id = $row->studienplan_id;
				$obj->koordinator = $row->koordinator;
				$obj->studienplan_lehrveranstaltung_id_parent = $row->studienplan_lehrveranstaltung_id_parent;
				$obj->lehrveranstaltung_id = $row->lehrveranstaltung_id;
				$obj->insertamum = $row->insertamum;
				$obj->insertvon = $row->insertvon;
				$obj->updateamum = $row->updateamum;
				$obj->updatevon = $row->updatevon;
				$obj->new=false;

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

	/**
	 * Löscht eine Lehrveranstaltung aus dem Studienplan
	 * @param $studienplan_lehrveranstaltung_id ID der LV in der Zwischentabelle
	 * @return boolean
	 */
	public function deleteStudienplanLehrveranstaltung($studienplan_lehrveranstaltung_id)
	{
		//Pruefen ob studienplan_lehrveranstaltung_id eine gueltige Zahl ist
		if(!is_numeric($studienplan_lehrveranstaltung_id) || $studienplan_lehrveranstaltung_id === '')
		{
			$this->errormsg = 'studienplan_lehrveranstaltung_id muss eine gültige Zahl sein'."\n";
			return false;
		}

		$qry = "DELETE from lehre.tbl_studienplan_lehrveranstaltung
			WHERE studienplan_lehrveranstaltung_id=".$this->db_add_param($studienplan_lehrveranstaltung_id, FHC_INTEGER).";";

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

	/**
	 * Holt die aktiven Studienplaene eines Studiensemester / Ausbildungssemesters
	 * @param string $studiensemester_kurzbz Optional. Studiensemester in dem der Studienplan liegen soll
	 * @param integer $ausbildungssemester Optional. Ausbildungssemester in dem der Studienplan liegen soll
	 * @param string $orgform_kurzbz. Optional. Organisationsform des Studienplans
	 */
	function getStudienplaeneFromSem($studiengang_kz, $studiensemester_kurzbz="", $ausbildungssemester="", $orgform_kurzbz = "", $sprache = "")
	{
		$qry = "SELECT
					studienplan_id,
					studienordnung_id,
					orgform_kurzbz,
					tbl_studienplan.version AS version_studienplan,
					tbl_studienplan.bezeichnung AS bezeichnung_studienplan,
					regelstudiendauer,
					sprache,
					aktiv,
					semesterwochen,
					testtool_sprachwahl,
					tbl_studienplan.insertamum AS insertamum_studienplan,
					tbl_studienplan.insertvon AS insertvon_studienplan,
					tbl_studienplan.updateamum AS updateamum_studienplan,
					tbl_studienplan.updatevon AS updatevon_studienplan,
					ects_stpl,
					pflicht_sws,
					pflicht_lvs,
					onlinebewerbung_studienplan,
					studiengang_kz,
					tbl_studienordnung.version AS version_studienordnung,
					gueltigvon,
					gueltigbis,
					tbl_studienordnung.bezeichnung AS bezeichnung_studienordnung,
					ects,
					studiengangbezeichnung,
					studiengangbezeichnung_englisch,
					studiengangkurzbzlang,
					akadgrad_id,
					tbl_studienordnung.insertamum AS insertamum_studienordnung,
					tbl_studienordnung.insertvon AS insertvon_studienordnung,
					tbl_studienordnung.updateamum AS updateamum_studienordnung,
					tbl_studienordnung.updatevon AS updatevon_studienordnung,
					status_kurzbz,
					standort_id,
					studienplan_semester_id,
					studiensemester_kurzbz,
					semester
				FROM
					lehre.tbl_studienplan
					JOIN lehre.tbl_studienordnung USING(studienordnung_id)
					JOIN lehre.tbl_studienplan_semester USING(studienplan_id)
				WHERE
					tbl_studienplan.aktiv
					AND tbl_studienordnung.studiengang_kz=".$this->db_add_param($studiengang_kz, FHC_INTEGER);
		if($studiensemester_kurzbz!='')
			$qry.="	AND tbl_studienplan_semester.studiensemester_kurzbz = ".$this->db_add_param($studiensemester_kurzbz);

		if($ausbildungssemester!='')
			$qry.=" AND tbl_studienplan_semester.semester=".$this->db_add_param($ausbildungssemester);

		if($orgform_kurzbz!='')
			$qry.=" AND orgform_kurzbz=".$this->db_add_param($orgform_kurzbz);

		if($sprache != '')
			$qry.=" AND tbl_studienplan.sprache=".$this->db_add_param($sprache);

		$res = array();

		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				$res[] = $row;
			}

			$this->result = $res;
			return true;
		}

		$this->errormsg = 'Fehler bei einer Datenbankabfrage';
		return false;
	}

	/**
     * Holt den aktiven Studienplan eines Studiensemester / Ausbildungssemesters
     * @param studiensemester_kurzbz
     * @param $ausbuldungssemester
     * @param $orgform_kurzbz
     */
    function getStudienplan($studiengang_kz, $studiensemester_kurzbz, $ausbildungssemester, $orgform_kurzbz)
    {
	    $qry = "SELECT
				    tbl_studienplan.studienplan_id
			    FROM
				    lehre.tbl_studienplan
				    JOIN lehre.tbl_studienordnung USING(studienordnung_id)
				    JOIN lehre.tbl_studienplan_semester USING (studienplan_id)
			    WHERE
				    tbl_studienplan.aktiv
				    AND tbl_studienordnung.studiengang_kz=".$this->db_add_param($studiengang_kz, FHC_INTEGER)."
				    AND tbl_studienplan_semester.studiensemester_kurzbz = ".$this->db_add_param($studiensemester_kurzbz)."
				    AND tbl_studienplan_semester.semester=".$this->db_add_param($ausbildungssemester);

	    if($orgform_kurzbz!='')
	    {
		    $qry.=" AND orgform_kurzbz=".$this->db_add_param($orgform_kurzbz);
	    }

	    if($result = $this->db_query($qry))
	    {
		    if($row = $this->db_fetch_object($result))
		    {
			    return $row->studienplan_id;
		    }
	    }
    }

	/**
	 * Holt alle Studienplaene eines Studienganges
	 * @param $studiengang_kz
	 */
	function getStudienplaene($studiengang_kz)
	{
		$qry = "SELECT
					distinct tbl_studienplan.*, tbl_studiensemester.start as start_stsem
				FROM
					lehre.tbl_studienplan
					JOIN lehre.tbl_studienordnung USING(studienordnung_id)
					LEFT JOIN public.tbl_studiensemester ON(studiensemester_kurzbz=tbl_studienordnung.gueltigvon)
				WHERE
					tbl_studienordnung.studiengang_kz=".$this->db_add_param($studiengang_kz, FHC_INTEGER)."
				ORDER BY tbl_studiensemester.start";

		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
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
				$obj->ects_stpl = $row->ects_stpl;
				$obj->pflicht_lvs = $row->pflicht_lvs;
				$obj->pflicht_sws = $row->pflicht_sws;
				$obj->onlinebewerbung_studienplan = $this->db_parse_bool($row->onlinebewerbung_studienplan);
				$obj->updateamum = $row->updateamum;
				$obj->updatevon = $row->updatevon;
				$obj->insertamum = $row->insertamum;
				$obj->insertvon = $row->insertvon;
				$obj->new=false;

				$this->result[] = $obj;
			}
			return true;
		}
	}

	/**
	 * Speichert die Sortierung
	 * @param type $tudienplan_lehrveranstaltung_id
	 * @param type $sort
	 */
	function saveSortierung($studienplan_lehrveranstaltung_id = null, $sort = null)
	{
		if($studienplan_lehrveranstaltung_id==NULL)
			$studienplan_lehrveranstaltung_id = $this->studienplan_lehrveranstaltung_id;

		if($sort==NULL)
			$sort = $this->sort;

		if(!(is_numeric($sort) || is_null($sort)))
		{
			$this->errormsg = "Es muss eine Zahl als Sortierungswert angegeben werden.";
			return false;
		}
		$qry = 'UPDATE lehre.tbl_studienplan_lehrveranstaltung '
		. 'SET sort='.$this->db_add_param($sort)
		. ' WHERE studienplan_lehrveranstaltung_id='.$this->db_add_param($studienplan_lehrveranstaltung_id).';';

		$this->orgform_kurzbz = $qry;
		if(!$this->db_query($qry))
		{
			$this->errormsg = "Fehler beim speichern der Sortierung.";
			return false;
		}
		return true;
	}

	/**
	 * Laedt die Studienplaene zu denen eine Lehrveranstaltung zugeordnet ist
	 */
	public function getStudienplanLehrveranstaltung($lehrveranstaltung_id, $studiensemester_kurzbz = null)
	{
		$qry= "
			SELECT
				DISTINCT tbl_studienplan.*
			FROM
				lehre.tbl_studienplan
				JOIN lehre.tbl_studienplan_lehrveranstaltung 
					USING(studienplan_id)
			WHERE
				tbl_studienplan_lehrveranstaltung.lehrveranstaltung_id IN (
					SELECT 
						lv.lehrveranstaltung_id
					FROM
						lehre.tbl_lehrveranstaltung AS lv
						LEFT JOIN lehre.tbl_lehrveranstaltung AS t ON t.lehrveranstaltung_id=lv.lehrveranstaltung_template_id
					WHERE
						lv.lehrtyp_kurzbz<>'tpl'
						AND (lv.lehrveranstaltung_id=" . $this->db_add_param($lehrveranstaltung_id, FHC_INTEGER) . " OR (lv.lehrveranstaltung_template_id=" . $this->db_add_param($lehrveranstaltung_id, FHC_INTEGER) . " AND t.lehrtyp_kurzbz='tpl'))
					)
				AND EXISTS (
					SELECT 1 
					FROM 
						lehre.tbl_studienplan_semester
					WHERE studienplan_id=tbl_studienplan.studienplan_id".
						($studiensemester_kurzbz != null ? "
						AND studiensemester_kurzbz=".$this->db_add_param($studiensemester_kurzbz) : "")."
						AND semester = tbl_studienplan_lehrveranstaltung.semester)
			ORDER BY bezeichnung";

		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
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
				$obj->ects_stpl = $row->ects_stpl;
				$obj->pflicht_lvs = $row->pflicht_lvs;
				$obj->pflicht_sws = $row->pflicht_sws;
				$obj->onlinebewerbung_studienplan = $this->db_parse_bool($row->onlinebewerbung_studienplan);
				$obj->updateamum = $row->updateamum;
				$obj->updatevon = $row->updatevon;
				$obj->insertamum = $row->insertamum;
				$obj->insertvon = $row->insertvon;
				$obj->new=false;

				$this->result[] = $obj;
			}
			return true;
		}
	}

	/**
	* speichert die Semesterzuordnung für die Studieordnung
	* @param int $$studienplan_id Die ID des Studienplans
	* @param string $studiensemester_kurzbz Kurzbezeichnung des Studiensemesters
	* @param int $ausbildungssemester Ausbildungssemester als Zahl
	*/
	public function saveSemesterZuordnung($zuordnung = array())
	{
		if (is_array($zuordnung))
		{
			$qry = "";
			foreach ($zuordnung as $key)
			{
				if (!is_numeric($key["studienplan_id"]))
				{
					$this->errormsg = 'studienplan_id muss eine gueltige Zahl sein';
					return false;
				}

				if (!is_string($key["studiensemester_kurzbz"]) || strlen($key["studiensemester_kurzbz"]) != 6)
				{
					$this->errormsg = 'studiensemester_kurzbz muss ein String mit 6 Zeichen sein';
					return false;
				}

				if (!is_numeric($key["ausbildungssemester"]))
				{
					$this->errormsg = 'ausbildungssemester muss eine gueltige Zahl sein';
					return false;
				}


				$qry .= "INSERT INTO lehre.tbl_studienplan_semester (studienplan_id, studiensemester_kurzbz, semester) VALUES (" .
					$this->db_add_param($key["studienplan_id"]) . ', ' .
					$this->db_add_param($key["studiensemester_kurzbz"]) . ', ' .
					$this->db_add_param($key["ausbildungssemester"]) . '); ';
			}

			if($qry!='')
			{
				if (!$this->db_query($qry))
				{
					$this->errormsg = 'Fehler beim Speichern des Datensatzes';
					return false;
				}
				return true;
			}
			else
				return true;
		}
		else
		{
			$this->errormsg = 'Der übergebene Parameter ist kein Array.';
			return false;
		}
		return false;
	}

	/**
	 * lädt alle zugeordneten Semester eines Studienplans
	 * @param int $studienplan ID
	 */
	public function loadStudiensemesterFromStudienplan($studienplan_id)
	{
		if (!is_numeric($studienplan_id))
		{
			$this->errormsg = 'studienplan_id muss eine gueltige Zahl sein';
			return false;
		}

		$qry = 'SELECT DISTINCT studiensemester_kurzbz, tbl_studiensemester.start
			FROM
			lehre.tbl_studienplan_semester
			JOIN public.tbl_studiensemester USING(studiensemester_kurzbz)
			WHERE studienplan_id=' . $this->db_add_param($studienplan_id) . '
			ORDER BY tbl_studiensemester.start, studiensemester_kurzbz';

		if (!$this->db_query($qry))
		{
			$this->errormsg = 'Fehler bei einer Datenbankabfrage';
			return false;
		}

		$data = array();
		while ($row = $this->db_fetch_object())
		{
			$data[] = $row->studiensemester_kurzbz;
		}
		return $data;
	}

	public function loadAusbildungsemesterFromStudiensemester($studienplan_id, $studiensemester_kurzbz)
	{
		$qry = 'SELECT semester
			FROM lehre.tbl_studienplan_semester
			WHERE studienplan_id=' . $this->db_add_param($studienplan_id) . ' AND
				studiensemester_kurzbz=' . $this->db_add_param($studiensemester_kurzbz) . '
			ORDER BY semester;';

		if (!$this->db_query($qry))
		{
			return false;
		}

		$data = array();
		while ($row = $this->db_fetch_object())
		{
			$data[] = $row->semester;
		}
		return $data;
	}

	function isSemesterZugeordnet($studienplan_id, $studiensemester_kurzbz, $ausbildungssemester)
	{
		if (!is_numeric($studienplan_id))
		{
		$this->errormsg = 'studienplan_id muss eine gueltige Zahl sein';
		return false;
		}

		if (!is_string($studiensemester_kurzbz) || strlen($studiensemester_kurzbz) != 6)
		{
		$this->errormsg = 'studiensemester_kurzbz muss ein String mit 6 Zeichen sein';
		return false;
		}

		if (!is_numeric($ausbildungssemester))
		{
		$this->errormsg = 'ausbildungssemester muss eine gueltige Zahl sein';
		return false;
		}

		$qry = 'SELECT * FROM lehre.tbl_studienplan_semester WHERE
		studienplan_id=' . $this->db_add_param($studienplan_id) . ' AND
		studiensemester_kurzbz=' . $this->db_add_param($studiensemester_kurzbz) . ' AND
		semester=' . $this->db_add_param($ausbildungssemester) . ';';

		if ($this->db_query($qry))
		{
			if ($this->db_num_rows() == 1)
			{
				return true;
			}
			if ($this->db_num_rows() == 0)
			{
				return false;
			}
			return false;
		}
		return false;
	}

	/**
	 * prüft ob dem Studienplan Semester zugeordnet sind (Gültigkeit)
	 * @param int $studienplan_id Die ID des Studienplans
	 */
	public function hasSemesterZugeordnet($studienplan_id)
	{
		if (!is_numeric($studienplan_id))
		{
			$this->errormsg = 'studienplan_id muss eine gueltige Zahl sein';
			return false;
		}

		$qry = 'SELECT * FROM lehre.tbl_studienplan_semester WHERE
		studienplan_id=' . $this->db_add_param($studienplan_id) . ';';

		if ($this->db_query($qry))
		{
			if ($this->db_num_rows() >= 1)
			{
				return true;
			}
			return false;
		}
		return false;
	}

	/**
	 * Sucht nach Studienordnungen, die den Kriterien entsprechen
	 * @param string $searchItems Array aus Suchstrings
	 * @param boolean $aktiv Optional. Wenn true werden nur aktive Studienpläne geliefert, wenn false nur inaktive, wenn null (default) alle
	 * @param string $gueltigInStudiensemester Optional. Studiensemester_kurzbz in welchem der Studienplan gueltig ist
	 * @return boolean true wenn ok, false im Fehlerfall
	 */
	public function searchStudienplaene($searchItems, $aktiv = null, $gueltigInStudiensemester = null)
	{
		$qry= "
				SELECT DISTINCT
					studienplan_id, tbl_studienplan.bezeichnung, tbl_studiensemester.start, tbl_studienordnung.status_kurzbz
				FROM
					lehre.tbl_studienplan
				JOIN
					lehre.tbl_studienordnung USING (studienordnung_id)
				JOIN
					lehre.tbl_studienplan_semester USING (studienplan_id)
				JOIN
					public.tbl_studiengang USING (studiengang_kz)
				JOIN
					public.tbl_studiensemester ON (tbl_studienordnung.gueltigvon = tbl_studiensemester.studiensemester_kurzbz)
				WHERE
					1=1";

			if ($aktiv != '' && ($aktiv == true || $aktiv == false))
				$qry.=" AND tbl_studienplan.aktiv=".$this->db_add_param($aktiv, FHC_BOOLEAN);

			if ($gueltigInStudiensemester != '')
				$qry.=" AND tbl_studienplan_semester.studiensemester_kurzbz=".$this->db_add_param($gueltigInStudiensemester);

			foreach($searchItems as $value)
			$qry.=" AND
					(
						lower(tbl_studienplan.bezeichnung) LIKE lower('%".$this->db_escape($value)."%')
						OR
						lower(tbl_studienplan_semester.studiensemester_kurzbz) LIKE lower('%".$this->db_escape($value)."%')
						OR
						lower(tbl_studiengang.typ||tbl_studiengang.kurzbz) LIKE lower('%".$this->db_escape($value)."%')
						OR
						lower(tbl_studienplan.orgform_kurzbz) LIKE lower('%".$this->db_escape($value)."%')
						OR
						tbl_studienplan.studienplan_id::text = '".$this->db_escape($value)."'
					)";
			$qry.=" ORDER BY start DESC, studienplan_id DESC";

		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				$obj = new studienplan();

				$obj->studienplan_id = $row->studienplan_id;
				$obj->bezeichnung = $row->bezeichnung;
				$obj->status_kurzbz = $row->status_kurzbz;
				$obj->new=false;

				$this->result[] = $obj;
			}
			return true;
		}
		return false;
	}

	/**
	 * Laedt die Studienplaene zu denen eine Lehrveranstaltung zugeordnet ist
	 */
	public function getStudienplaenePerson($person_id)
	{
		$qry= "
			SELECT
			distinct tbl_studienplan.*
			FROM
				lehre.tbl_studienplan
				JOIN public.tbl_prestudentstatus USING(studienplan_id)
				JOIN public.tbl_prestudent USING(prestudent_id)
			WHERE
				person_id=".$this->db_add_param($person_id)."
			ORDER BY bezeichnung";

		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
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
				$obj->ects_stpl = $row->ects_stpl;
				$obj->pflicht_lvs = $row->pflicht_lvs;
				$obj->pflicht_sws = $row->pflicht_sws;
				$obj->onlinebewerbung_studienplan = $this->db_parse_bool($row->onlinebewerbung_studienplan);
				$obj->updateamum = $row->updateamum;
				$obj->updatevon = $row->updatevon;
				$obj->insertamum = $row->insertamum;
				$obj->insertvon = $row->insertvon;
				$obj->new=false;

				$this->result[] = $obj;
			}
			return true;
		}
	}

}
?>
