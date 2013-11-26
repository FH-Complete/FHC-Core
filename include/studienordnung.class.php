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
 */

require_once(dirname(__FILE__).'/basis_db.class.php');

class studienordnung extends basis_db
{
	private $new = true;			// boolean
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
	protected $max_semester;			// smallint
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
			$this->max_semester		= $row->max_semester;
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
			$qry = 'SELECT 
						* 
					FROM 
						lehre.tbl_studienordnung 
					WHERE 
						studiengang_kz='.$this->db_add_param($studiengang_kz, FHC_INTEGER, false);
		}
		else
		{
			$qry = 'SELECT 
						* 
					FROM 
						lehre.tbl_studienordnung 
						LEFT JOIN lehre.tbl_studienordnung_semester USING (studienordnung_id) 
					WHERE 
						studiengang_kz='.$this->db_add_param($studiengang_kz, FHC_INTEGER, false);

			if (!is_null($studiensemester_kurzbz))
				$qry.=" AND studiensemester_kurzbz=".$this->db_add_param($studiensemester_kurzbz, FHC_STRING,false);
			if (!is_null($semester))
				$qry.=" AND semester=".$this->db_add_param($semester, FHC_INTEGER,false);
		}

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
			$obj->max_semester		= $row->max_semester;
			$obj->updateamum		= $row->updateamum;
			$obj->updatevon			= $row->updatevon;
			$obj->insertamum		= $row->insertamum;
			$obj->insertvon			= $row->insertvon;
			$obj->new				= false;

			if(!is_null($studiensemester_kurzbz))
			{
				$obj->studiensemester_kurzbz = $row->studiensemester_kurzbz;
				$obj->semester = $row->semester;
				$obj->studienordnung_semester_id = $row->studienordnung_semester_id;
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
		if(!is_numeric($this->ects))
		{
			$this->errormsg='ects enthaelt ungueltige Zeichen';
			return false;
		}
		if(!is_numeric($this->akadgrad_id))
		{
			$this->errormsg='akadgrad_id enthaelt ungueltige Zeichen';
			return false;
		}

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
			$qry='BEGIN;INSERT INTO lehre.tbl_studienordnung (studiengang_kz, version, bezeichnung, ects, gueltigvon, gueltigbis, studiengangbezeichnung, studiengangbezeichnung_englisch, studiengangkurzbzlang, akadgrad_id, max_semester, insertamum, insertvon) VALUES ('.
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
			      $this->db_add_param($this->max_semester,FHC_INTEGER).', now(), '.
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
		      	' max_semester='.$this->db_add_param($this->max_semester, FHC_INTEGER).', '.
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
		$qry="DELETE FROM public.tbl_studienordnung WHERE studienordnung_id=".$this->db_add_param($studienordnung_id, FHC_INTEGER, false).";";

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

				$obj->studienordnung_id= $row->studienordnung_id;
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
				$obj->max_semester		= $row->max_semester;
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
			$obj->max_semester		= $this->max_semester;
			$obj->updateamum		= $this->updateamum;
			$obj->updatevon		= $this->updatevon;
			$obj->insertamum		= $this->insertamum;
			$obj->insertvon		= $this->insertvon;
			$data[]=$obj;
		}
		return $data;
	}
}
?>
