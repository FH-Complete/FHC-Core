<?php
/*
 * studienordnung.class.php
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
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>
 *			Andreas Österreicher <andreas.oesterreicher@technikum-wien.at>
 *          Cristina Hainberger <hainberg@technikum-wien.at>
 */

require_once(dirname(__FILE__).'/basis_db.class.php');
require_once 'studiensemester.class.php';

class studienordnung extends basis_db
{
	public $new = true;			// boolean
	public $result = array();		// Objekte

	//Tabellenspalten
	protected $studienordnung_id;		// integer (PK)
	protected $studiengang_kz;			// integer (FK Studiengang)
	protected $version; 				// varchar (256)
	protected $bezeichnung;				// varchar (512)
	protected $ects;					// numeric (5,2)
	protected $gueltigvon;				// varchar (FK Studiensemester)
	protected $gueltigbis;				// varchar (FK Studiensemester)
	protected $studiengangbezeichnung;	// varchar (256)
	protected $studiengangbezeichnung_englisch;	// varchar (256)
	protected $studiengangkurzbzlang;	// varchar (256)
	protected $akadgrad_id;				// integer (FK akadgrad)
	protected $status_kurzbz;  //varchar(32)
	protected $standort_id;
	protected $orgform_kurzbz;
	protected $updateamum;				// timestamp
	protected $updatevon;				// varchar
	protected $insertamum;				// timestamp
	protected $insertvon;				// varchar

	/**
	 * Konstruktor
	 */
	public function __construct()
	{
		parent::__construct();
	}

	public function __set($name,$value)
	{
		switch ($name)
		{
			case 'studiengang_kz':
				if (!is_numeric($value))
					throw new Exception('Attribute studiengang_kz must be numeric!"');
				$this->$name=$value;
				break;
			default:
				$this->$name=$value;
		}
	}

	public function __get($name)
	{
		return $this->$name;
	}

	/**
	 * Laedt die Studienordnung mit der ID $studienordnung_id
	 * @param  $studienordnung_id ID der zu ladenden Studienordnung
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function loadStudienordnung($studienordnung_id)
	{
		//Pruefen ob studienordnung_id eine gueltige Zahl ist
		if(!is_numeric($studienordnung_id) || $studienordnung_id == '')
		{
			$this->errormsg = 'Studienordnung_id muss eine Zahl sein';
			return false;
		}

		//Daten aus der Datenbank lesen
		$qry = "SELECT * FROM lehre.tbl_studienordnung WHERE studienordnung_id=".$this->db_add_param($studienordnung_id, FHC_INTEGER, false);

		if(!$this->db_query($qry))
		{
			$this->errormsg = 'Fehler bei einer Datenbankabfrage';
			return false;
		}

		if($row = $this->db_fetch_object())
		{
			$this->studienordnung_id= $row->studienordnung_id;
			$this->studiengang_kz	= $row->studiengang_kz;
			$this->version			= $row->version;
			$this->bezeichnung		= $row->bezeichnung;
			$this->ects				= $row->ects;
			$this->gueltigvon		= $row->gueltigvon;
			$this->gueltigbis		= $row->gueltigbis;
			$this->studiengangbezeichnung	= $row->studiengangbezeichnung;
			$this->studiengangbezeichnung_englisch	= $row->studiengangbezeichnung_englisch;
			$this->studiengangkurzbzlang	= $row->studiengangkurzbzlang;
			$this->akadgrad_id		= $row->akadgrad_id;
			$this->status_kurzbz	= $row->status_kurzbz;
			$this->standort_id		= $row->standort_id;
			$this->updateamum		= $row->updateamum;
			$this->updatevon		= $row->updatevon;
			$this->insertamum		= $row->insertamum;
			$this->insertvon		= $row->insertvon;
		}
		else
		{
			$this->errormsg = 'Es ist kein Datensatz mit dieser ID vorhanden';
			return false;
		}
		$this->new=false;
		return true;
	}

	/**
	 * Laedt alle Studienordnungen zu einem Studiengang der uebergeben wird
	 * @param $studiengang_kz Kennzahl des Studiengangs
	 * @param $studiensemester_kurzbz
	 * @param $semester
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function loadStudienordnungSTG($studiengang_kz,$studiensemester_kurzbz=null, $semester=null)
	{
		//Pruefen ob studiengang_kz eine gueltige Zahl ist
		if(!is_numeric($studiengang_kz) || $studiengang_kz === '')
		{
			$this->errormsg = 'studiengang_kz muss eine gültige Zahl sein';
			return false;
		}

		if(is_null($studiensemester_kurzbz))
		{
			$qry = 'SELECT sto.*, s.bezeichnung as status_bezeichnung, tbl_studiensemester.start as gueltig_startdatum
					FROM lehre.tbl_studienordnung sto
					LEFT JOIN lehre.tbl_studienordnungstatus s USING(status_kurzbz)
					LEFT JOIN public.tbl_studiensemester ON(sto.gueltigvon=tbl_studiensemester.studiensemester_kurzbz)
					LEFT JOIN public.tbl_studiensemester stoende ON(sto.gueltigbis=stoende.studiensemester_kurzbz)
					WHERE studiengang_kz='.$this->db_add_param($studiengang_kz, FHC_INTEGER, false);
		}
		else
		{
			$qry = 'SELECT distinct sto.*, s.bezeichnung as status_bezeichnung, sem.*, tbl_studiensemester.start as gueltig_startdatum
					FROM lehre.tbl_studienordnung sto
					JOIN lehre.tbl_studienordnungstatus s USING(status_kurzbz)
					LEFT JOIN lehre.tbl_studienplan USING(studienordnung_id)
					LEFT JOIN lehre.tbl_studienplan_semester sem USING (studienplan_id)
					LEFT JOIN public.tbl_studiensemester ON(sto.gueltigvon=tbl_studiensemester.studiensemester_kurzbz)
					LEFT JOIN public.tbl_studiensemester stoende ON(sto.gueltigbis=stoende.studiensemester_kurzbz)
					WHERE studiengang_kz='.$this->db_add_param($studiengang_kz, FHC_INTEGER, false);

			if (!is_null($studiensemester_kurzbz))
				$qry.=" AND sem.studiensemester_kurzbz=".$this->db_add_param($studiensemester_kurzbz, FHC_STRING,false);
			if (!is_null($semester))
				$qry.=" AND semester=".$this->db_add_param($semester, FHC_INTEGER,false);
		}

		$qry.=" ORDER BY gueltig_startdatum desc, stoende.start nulls first";

		if(!$this->db_query($qry))
		{
			$this->errormsg = 'Fehler bei einer Datenbankabfrage';
			return false;
		}

		while($row = $this->db_fetch_object())
		{
			$obj = new studienordnung();

			$obj->studienordnung_id	= $row->studienordnung_id;
			$obj->studiengang_kz	= $row->studiengang_kz;
			$obj->version			= $row->version;
			$obj->bezeichnung		= $row->bezeichnung;
			$obj->ects				= $row->ects;
			$obj->gueltigvon		= $row->gueltigvon;
			$obj->gueltigbis		= $row->gueltigbis;
			$obj->studiengangbezeichnung	= $row->studiengangbezeichnung;
			$obj->studiengangbezeichnung_englisch	= $row->studiengangbezeichnung_englisch;
			$obj->studiengangkurzbzlang	= $row->studiengangkurzbzlang;
			$obj->akadgrad_id		= $row->akadgrad_id;
			$obj->status_kurzbz		= $row->status_kurzbz;
			$obj->status_bezeichnung	= $row->status_bezeichnung;
			$obj->standort_id		= $row->standort_id;
			$obj->updateamum		= $row->updateamum;
			$obj->updatevon			= $row->updatevon;
			$obj->insertamum		= $row->insertamum;
			$obj->insertvon			= $row->insertvon;
			$obj->new				= false;

			if(!is_null($studiensemester_kurzbz))
			{
				$obj->studiensemester_kurzbz = $row->studiensemester_kurzbz;
				$obj->semester = $row->semester;
				//$obj->studienordnung_semester_id = $row->studienordnung_semester_id;
				$obj->studienplan_semester_id = $row->studienplan_semester_id;
			}
			$this->result[] = $obj;
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
		if(!is_numeric($this->studiengang_kz) && $this->studiengang_kz!='')
		{
			$this->errormsg='studiengang_kz enthaelt ungueltige Zeichen';
			return false;
		}
		//changes for addon Studiengangsverwaltung; ECTS and akadgrad_id are optional
//		if(!is_numeric($this->ects))
//		{
//			$this->errormsg='ects enthaelt ungueltige Zeichen';
//			return false;
//		}
//		if(!is_numeric($this->akadgrad_id))
//		{
//			$this->errormsg='akadgrad_id enthaelt ungueltige Zeichen';
//			return false;
//		}

		//Gesamtlaenge pruefen
		if(mb_strlen($this->version)>256)
		{
			$this->errormsg = 'Version darf nicht länger als 256 Zeichen sein';
			return false;
		}
		if(mb_strlen($this->bezeichnung)>512)
		{
			$this->errormsg = 'Bezeichnung darf nicht länger als 512 Zeichen sein';
			return false;
		}
		if(mb_strlen($this->gueltigvon)>16)
		{
			$this->errormsg = 'Gueltig Von darf nicht laenger als 16 Zeichen sein';
			return false;
		}
		if(mb_strlen($this->gueltigbis)>16)
		{
			$this->errormsg = 'Gueltig Bis darf nicht laenger als 16 Zeichen sein';
			return false;
		}
		if(mb_strlen($this->studiengangbezeichnung)>256)
		{
			$this->errormsg = 'Studiengangbezeichnung darf nicht länger als 256 Zeichen sein';
			return false;
		}
		if(mb_strlen($this->studiengangbezeichnung_englisch)>256)
		{
			$this->errormsg = 'Studiengangbezeichnung Englisch darf nicht länger als 256 Zeichen sein';
			return false;
		}
		if(mb_strlen($this->studiengangkurzbzlang)>8)
		{
			$this->errormsg = 'Studiengangkurzbzlang darf nicht länger als 8 Zeichen sein';
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
			$qry='BEGIN;INSERT INTO lehre.tbl_studienordnung (studiengang_kz, version, bezeichnung, ects, gueltigvon, gueltigbis, studiengangbezeichnung, studiengangbezeichnung_englisch, studiengangkurzbzlang, akadgrad_id, standort_id, status_kurzbz, insertamum, insertvon) VALUES ('.
			      $this->db_add_param($this->studiengang_kz, FHC_INTEGER).', '.
			      $this->db_add_param($this->version).', '.
			      $this->db_add_param($this->bezeichnung).', '.
			      $this->db_add_param($this->ects).', '.
			      $this->db_add_param($this->gueltigvon).', '.
			      $this->db_add_param($this->gueltigbis).', '.
			      $this->db_add_param($this->studiengangbezeichnung).', '.
			      $this->db_add_param($this->studiengangbezeichnung_englisch).', '.
			      $this->db_add_param($this->studiengangkurzbzlang).', '.
			      $this->db_add_param($this->akadgrad_id,FHC_INTEGER).', '.
			      $this->db_add_param($this->standort_id,FHC_INTEGER).', '.
			      $this->db_add_param($this->status_kurzbz).', '.
			      ' now(), '.
			      $this->db_add_param($this->insertvon).');';
		}
		else
		{
			//Pruefen ob studienordnung_id eine gueltige Zahl ist
			if(!is_numeric($this->studienordnung_id))
			{
				$this->errormsg = 'studienordnung_id muss eine gueltige Zahl sein';
				return false;
			}
			$qry='UPDATE lehre.tbl_studienordnung SET'.
				' studiengang_kz='.$this->db_add_param($this->studiengang_kz, FHC_INTEGER).', '.
				' version='.$this->db_add_param($this->version).', '.
				' bezeichnung='.$this->db_add_param($this->bezeichnung).', '.
				' ects='.$this->db_add_param($this->ects).', '.
				' gueltigvon='.$this->db_add_param($this->gueltigvon).', '.
				' gueltigbis='.$this->db_add_param($this->gueltigbis).', '.
				' studiengangbezeichnung='.$this->db_add_param($this->studiengangbezeichnung).', '.
				' studiengangbezeichnung_englisch='.$this->db_add_param($this->studiengangbezeichnung_englisch).', '.
				' studiengangkurzbzlang='.$this->db_add_param($this->studiengangkurzbzlang).','.
				' akadgrad_id='.$this->db_add_param($this->akadgrad_id, FHC_INTEGER).', '.
				' standort_id='.$this->db_add_param($this->standort_id, FHC_INTEGER).', '.
				' status_kurzbz='.$this->db_add_param($this->status_kurzbz).', '.
				' updateamum= now(), '.
				' updatevon='.$this->db_add_param($this->updatevon).' '.
				' WHERE studienordnung_id='.$this->db_add_param($this->studienordnung_id, FHC_INTEGER, false).';';
		}

		if($this->db_query($qry))
		{
			if($this->new)
			{
				//naechste ID aus der Sequence holen
				$qry="SELECT currval('lehre.seq_studienordnung_studienordnung_id') as id;";
				if($this->db_query($qry))
				{
					if($row = $this->db_fetch_object())
					{
						$this->studienordnung_id = $row->id;
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
		return $this->studienordnung_id;
	}

	/**
	 * Loescht den Datenensatz mit der ID die uebergeben wird
	 * @param $studienordnung_id ID die geloescht werden soll
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function delete($studienordnung_id)
	{
		//Pruefen ob studienordnung_id eine gueltige Zahl ist
		if(!is_numeric($studienordnung_id) || $studienordnung_id == '')
		{
			$this->errormsg = 'studienordnung_id muss eine gültige Zahl sein'."\n";
			return false;
		}

		//loeschen des Datensatzes
		$qry="DELETE FROM lehre.tbl_studienordnung WHERE studienordnung_id=".$this->db_add_param($studienordnung_id, FHC_INTEGER, false).";";

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

				$obj->studienordnung_id = $row->studienordnung_id;
				$obj->studiengang_kz	= $row->studiengang_kz;
				$obj->version			= $row->version;
				$obj->bezeichnung		= $row->bezeichnung;
				$obj->ects				= $row->ects;
				$obj->gueltigvon		= $row->gueltigvon;
				$obj->gueltigbis		= $row->gueltigbis;
				$obj->studiengangbezeichnung	= $row->studiengangbezeichnung;
				$obj->studiengangbezeichnung_englisch	= $row->studiengangbezeichnung_englisch;
				$obj->studiengangkurzbzlang	= $row->studiengangkurzbzlang;
				$obj->akadgrad_id		= $row->akadgrad_id;
				$obj->standort_id		= $row->standort_id;
				$obj->status_kurzbz		= $row->status_kurzbz;
				$obj->updateamum		= $row->updateamum;
				$obj->updatevon		= $row->updatevon;
				$obj->insertamum		= $row->insertamum;
				$obj->insertvon		= $row->insertvon;

				$data[]=$obj;
			}
		}
		else
		{
			$obj = new stdClass();

			$obj->studienordnung_id= $this->studienordnung_id;
			$obj->studiengang_kz	= $this->studiengang_kz;
			$obj->version			= $this->version;
			$obj->bezeichnung		= $this->bezeichnung;
			$obj->ects				= $this->ects;
			$obj->gueltigvon		= $this->gueltigvon;
			$obj->gueltigbis		= $this->gueltigbis;
			$obj->studiengangbezeichnung	= $this->studiengangbezeichnung;
			$obj->studiengangbezeichnung_englisch	= $this->studiengangbezeichnung_englisch;
			$obj->studiengangkurzbzlang	= $this->studiengangkurzbzlang;
			$obj->akadgrad_id		= $this->akadgrad_id;
			$obj->standort_id		= $this->standort_id;
			$obj->status_kurzbz		= $this->status_kurzbz;
			$obj->updateamum		= $this->updateamum;
			$obj->updatevon		= $this->updatevon;
			$obj->insertamum		= $this->insertamum;
			$obj->insertvon		= $this->insertvon;
			$data[]=$obj;
		}
		return $data;
	}

	/**
	 * prüft ob die Studienordnung aktiv ist
	 * @param int $studienordnung_id Die ID der Studienordnung
	 * @param string $studiensemester_kurzbz Kurzbezeichnung des Studiensemesters
	 * @param int $ausbildungssemester Ausbildungssemester als Zahl
	 */
	public function isAktiv($studienordnung_id)
	{
		if(!is_numeric($studienordnung_id))
		{
			$this->errormsg = 'studienordnung_id muss eine gueltige Zahl sein';
			return false;
		}

        $qry = 'SELECT 1 FROM lehre.tbl_studienplan_semester
            WHERE studienplan_id
            IN (SELECT studienplan_id FROM lehre.tbl_studienplan
                WHERE studienordnung_id ='.$this->db_add_param($studienordnung_id).';';

		if($this->db_query($qry))
		{
			if($this->db_num_rows() >= 1)
			{
				return true;
			}
			return false;
		}
		return false;
	}

	protected function isZuordnungGuelitg($studiensemester_kurzbz)
	{
		$studiensemester = new studiensemester();
		$studiensemester->getTimestamp($studiensemester_kurzbz);

		$semGueltigVon = $studiensemester->begin->start;
		//$semGueltigBis = $studiensemester->ende->ende;

		$studiensemester = new studiensemester();
		$studiensemester->getTimestamp($this->gueltigvon);

		$stoGueltigVon = $studiensemester->begin->start;

		if($this->gueltigbis != null)
		{
			$studiensemester = new studiensemester();
			$studiensemester->getTimestamp($this->gueltigbis);
			$stoGueltigBis = $studiensemester->ende->ende;
		}
		else
		{
			$stoGueltigBis = null;
		}
		if(($semGueltigVon >= $stoGueltigVon && $semGueltigVon <= $stoGueltigBis) || ($semGueltigVon >= $stoGueltigVon && $stoGueltigBis == null))
		{
			return true;
		}
		return false;
	}

	/**
	 * Laedt die Studienordnung zu der uebergebenen studienplan_id
	 * @param  $studienplan_id der zu ladenden Studienordnung
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function getStudienordnungFromStudienplan($studienplan_id)
	{
		//Pruefen ob studienplan_id eine gueltige Zahl ist
		if(!is_numeric($studienplan_id) || $studienplan_id == '')
		{
			$this->errormsg = 'Studienplan_id muss eine Zahl sein';
			return false;
		}

		//Daten aus der Datenbank lesen
		$qry = "SELECT tbl_studienordnung.* FROM lehre.tbl_studienordnung JOIN lehre.tbl_studienplan USING (studienordnung_id) WHERE studienplan_id=".$this->db_add_param($studienplan_id, FHC_INTEGER, false);

		if(!$this->db_query($qry))
		{
			$this->errormsg = 'Fehler bei einer Datenbankabfrage';
			return false;
		}

		if($row = $this->db_fetch_object())
		{
			$this->studienordnung_id= $row->studienordnung_id;
			$this->studiengang_kz	= $row->studiengang_kz;
			$this->version			= $row->version;
			$this->bezeichnung		= $row->bezeichnung;
			$this->ects				= $row->ects;
			$this->gueltigvon		= $row->gueltigvon;
			$this->gueltigbis		= $row->gueltigbis;
			$this->studiengangbezeichnung	= $row->studiengangbezeichnung;
			$this->studiengangbezeichnung_englisch	= $row->studiengangbezeichnung_englisch;
			$this->studiengangkurzbzlang	= $row->studiengangkurzbzlang;
			$this->akadgrad_id		= $row->akadgrad_id;
			$this->standort_id		= $row->standort_id;
			$this->status_kurzbz		= $row->status_kurzbz;
			$this->updateamum		= $row->updateamum;
			$this->updatevon		= $row->updatevon;
			$this->insertamum		= $row->insertamum;
			$this->insertvon		= $row->insertvon;
		}
		else
		{
			$this->errormsg = 'Es ist kein Datensatz mit dieser ID vorhanden';
			return false;
		}
		$this->new=false;
		return true;
	}

	/**
	 * Laedt alle Studienordnungen zu einem Studiengang der uebergeben wird, die noch nicht aktiv sind
	 * @param $studiengang_kz Kennzahl des Studiengangs
	 * @param $studiensemester_kurzbz
	 * @param $semester
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function loadStudienordnungSTGInaktiv($studiengang_kz)
	{
		//Pruefen ob studiengang_kz eine gueltige Zahl ist
		if(!is_numeric($studiengang_kz) || $studiengang_kz === '')
		{
			$this->errormsg = 'studiengang_kz muss eine gültige Zahl sein';
			return false;
		}

		$qry = 'SELECT
					*
				FROM
					lehre.tbl_studienordnung
				WHERE
					studiengang_kz='.$this->db_add_param($studiengang_kz, FHC_INTEGER, false);

		if(!$this->db_query($qry))
		{
			$this->errormsg = 'Fehler bei einer Datenbankabfrage';
			return false;
		}

		while($row = $this->db_fetch_object())
		{
			$obj = new studienordnung();

			$obj->studienordnung_id	= $row->studienordnung_id;
			$obj->studiengang_kz	= $row->studiengang_kz;
			$obj->version			= $row->version;
			$obj->bezeichnung		= $row->bezeichnung;
			$obj->ects				= $row->ects;
			$obj->gueltigvon		= $row->gueltigvon;
			$obj->gueltigbis		= $row->gueltigbis;
			$obj->studiengangbezeichnung	= $row->studiengangbezeichnung;
			$obj->studiengangbezeichnung_englisch	= $row->studiengangbezeichnung_englisch;
			$obj->studiengangkurzbzlang	= $row->studiengangkurzbzlang;
			$obj->akadgrad_id		= $row->akadgrad_id;
			$this->standort_id		= $row->standort_id;
			$this->status_kurzbz		= $row->status_kurzbz;
			$obj->updateamum		= $row->updateamum;
			$obj->updatevon			= $row->updatevon;
			$obj->insertamum		= $row->insertamum;
			$obj->insertvon			= $row->insertvon;
			$obj->new				= false;
			$this->result[] = $obj;
		}

		foreach($this->result as $key => $obj)
		{
		    if($this->isAktiv($obj->studienordnung_id))
		    {
			unset($this->result[$key]);
		    }
		}
		array_values($this->result);

		return true;
	}

	/**
	 * Laedt die Studienordnungen eines Studiengangs inklusive Status
	 * @param $studiengang_kz Kennzahl des Studiengangs
	 * @param $status_kurzbz Status
	 * @return boolean true wenn ok, false im Fehlerfall
	 */
	public function loadStudienordnungWithStatus($studiengang_kz, $status_kurzbz)
    {
		$qry = "SELECT sto.*, s.bezeichnung as status_bezeichnung "
			. "FROM lehre.tbl_studienordnung sto "
			. "JOIN lehre.tbl_studienordnungstatus s USING(status_kurzbz) "
			. "WHERE status_kurzbz=" . $this->db_add_param($status_kurzbz, FHC_STRING) . ""
			. " AND studiengang_kz=" . $this->db_add_param($studiengang_kz, FHC_INTEGER) . ";";

		if (!$this->db_query($qry))
		{
			$this->errormsg = 'Fehler bei einer Datenbankabfrage';
			return false;
		}

		while ($row = $this->db_fetch_object())
		{
			$obj = new studienordnung();

			$obj->studienordnung_id = $row->studienordnung_id;
			$obj->studiengang_kz = $row->studiengang_kz;
			$obj->version = $row->version;
			$obj->bezeichnung = $row->bezeichnung;
			$obj->ects = $row->ects;
			$obj->gueltigvon = $row->gueltigvon;
			$obj->gueltigbis = $row->gueltigbis;
			$obj->studiengangbezeichnung = $row->studiengangbezeichnung;
			$obj->studiengangbezeichnung_englisch = $row->studiengangbezeichnung_englisch;
			$obj->studiengangkurzbzlang = $row->studiengangkurzbzlang;
			$obj->akadgrad_id = $row->akadgrad_id;
			$obj->status_kurzbz = $row->status_kurzbz;
			$obj->status_bezeichnung = $row->status_bezeichnung;
			$obj->begruendung = json_decode($row->begruendung);
			$obj->studiengangsart = $row->studiengangsart;
			$obj->standort_id = $row->standort_id;
			$obj->updateamum = $row->updateamum;
			$obj->updatevon = $row->updatevon;
			$obj->insertamum = $row->insertamum;
			$obj->insertvon = $row->insertvon;
			$obj->new = false;
			$this->result[] = $obj;
		}
		return true;
	}

	/**
	 * Aendert den Status einer Studienordnung
	 * @param $studienordnung_id ID der Studienordnung
	 * @param $status_kurzbz Kurzbezeichnung des Status
	 * @return boolean true wenn erfolgreich, false im Fehlerfall
	 */
	public function changeState($studienordnung_id, $status_kurzbz)
	{
		$qry = "UPDATE lehre.tbl_studienordnung SET status_kurzbz=" . $this->db_add_param($status_kurzbz)
			. " WHERE studienordnung_id=" . $this->db_add_param($studienordnung_id) . ";";

		if (!$this->db_query($qry))
		{
			$this->errormsg = "Status konnte nicht geändert werden.";
			return false;
		}
		return true;
	}

	/**
	 * Laedt alle vorhandenen Studienordnungsstatus Einträge
	 * @return boolean true wenn ok, false im Fehlerfall
	 */
	public function getStatus()
	{
		$qry = "SELECT * FROM lehre.tbl_studienordnungstatus order by reihenfolge";

		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				$obj = new stdClass();
				$obj->status_kurzbz = $row->status_kurzbz;
				$obj->bezeichnung = $row->bezeichnung;
				$obj->reihenfolge = $row->reihenfolge;
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
