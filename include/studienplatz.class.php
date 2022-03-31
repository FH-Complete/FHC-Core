<?php
/*
 * studienplatz.class.php
 *
 * Copyright 2013 Technikum-Wien
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty oferr
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
 *          Werner Masik <werner@gefi.at>
 */
require_once(dirname(__FILE__).'/basis_db.class.php');
require_once(__DIR__ .'/functions.inc.php');

class studienplatz extends basis_db
{
	/** @var $new boolean */
	private $new = true;
	/** @var DB-Result */
	private $result;
	/** @var object */
	public $studienplatz = array();

	//Tabellenspalten
	/** @var integer */
	private $studienplatz_id;
	/** @var integer */
	private $studiengang_kz;
	/** @var string */
	private $orgform_kurzbz;
	/** @var string */
	private $studiensemester_kurzbz;
	/** @var integer */
	private $ausbildungssemester;
	/** @var integer */
	private $gpz;
	/** @var integer */
	private $npz;
	/** @var timestamp */
	private $updateamum;
	/** @var string */
	private $updatevon;
	/** @var timestamp */
	private $insertamum;
	/** @var string */
	private $insertvon;
	/** @var integer */
	private $apz;
	/** @var integer */
	private $studienplan_id;

	/**
	 * Konstruktor
	 * @param integer ID des Studienplatz der geladen werden soll (Default=null)
	 */
	public function __construct($studienplatz_id=null)
	{
		parent::__construct();

		if(!is_null($studienplatz_id))
			$this->loadStudienplatz($studienplatz_id);
	}

	public function __set($name,$value)
	{
		switch ($name)
		{
			case 'gpz':
			case 'npz':
			case 'ausbildungssemester':
			case 'studienplatz_id':
			case 'studienplan_id':
				if ($value != null && !is_numeric($value))
					throw new Exception("Attribute $name must be numeric! ($value)");
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
	 * Laedt einzelnen Studienplatz der ID $studienplatz_id
	 * @param integer $studienplatz_id ID des zu ladenden Studienplatzes
	 * @return boolean Description true wenn ok, false im Fehlerfall
	 */
	public function loadStudienplatz($studienplatz_id)
	{
		//Pruefen ob adress_id eine gueltige Zahl ist
		if(!is_numeric($studienplatz_id) || $studienplatz_id == '')
		{
			$this->errormsg = 'studienplatz_id muss eine Zahl sein';
			return false;
		}

		//Daten aus der Datenbank lesen
		$qry = "SELECT * FROM lehre.tbl_studienplatz WHERE studienplatz_id=".$this->db_add_param($studienplatz_id, FHC_INTEGER, false);

		if(!$this->db_query($qry))
		{
			$this->errormsg = 'Fehler bei einer Datenbankabfrage';
			return false;
		}

		if($row = $this->db_fetch_object())
		{
			$this->mapRow($this, $row);
		}
		else
		{
			$this->errormsg = 'Es ist kein Datensatz mit dieser ID vorhanden';
			return false;
		}
		return true;
	}

	/**
	 * Helper
	 * @param type $target
	 * @param type $row
	 */
	private function mapRow($target,$row) {
		$target->studienplatz_id					= $row->studienplatz_id;
		$target->studiengang_kz 					= $row->studiengang_kz;
		$target->orgform_kurzbz						= $row->orgform_kurzbz;
		$target->studiensemester_kurzbz		= $row->studiensemester_kurzbz;
		$target->ausbildungssemester			= $row->ausbildungssemester;
		$target->gpz											= $row->gpz;
		$target->npz											= $row->npz;
		$target->updateamum								= $row->updateamum;
		$target->updatevon								= $row->updatevon;
		$target->insertamum								= $row->insertamum;
		$target->insertvon								= $row->insertvon;
		$target->apz											= $row->apz;
		$target->studienplan_id						= $row->studienplan_id;
		$target->new											= false;
	}

	/**
	 * Laedt alle Studienplaetze zu einem Studiengang und Semester. Ergebnis
	 * steht in result, wobei es nur einen Datensatz geben kann, wenn
	 * keinAusbildungssemester = true ist.
	 * @param integer $studiengang_kz
	 * @param string studiensemester_kurzbz
	 * @param string orgform_kurzbz
	 * @param boolean Ausbildungssemester ist optional. Wenn true werden
	 *  nur Datensätze geladen wo das Ausbildungssemester null ist. Wenn
	 *  false werden alle geladen wo ein Ausbildungssemester eingetragen
	 *  ist.
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function load_studiengang_studiensemester_orgform($studiengang_kz, $studiensemester_kurzbz, $orgform, $keinAusbildungssemester = true)
	{
		//Pruefen ob $studiengang_kz eine gueltige Zahl ist
		if(!is_numeric($studiengang_kz) || $studiengang_kz == '')
		{
			$this->errormsg = 'studiengang_kz muss eine gültige Zahl sein';
			return false;
		}

		//Lesen der Daten aus der Datenbank
		$qry = "SELECT * FROM lehre.tbl_studienplatz WHERE studiengang_kz=".
				$this->db_add_param($studiengang_kz, FHC_INTEGER, false).
				" AND studiensemester_kurzbz=".$this->db_add_param($studiensemester_kurzbz, FHC_STRING, false).
				//" AND art=".$this->db_add_param($art, FHC_STRING, false).
				" AND orgform_kurzbz=".$this->db_add_param($orgform, FHC_STRING, false).
				" AND ausbildungssemester ".($keinAusbildungssemester ? 'is null':'is not null');
		$this->result = array();
		if(!$this->db_query($qry))
		{
			return false;
		}

		while($row = $this->db_fetch_object())
		{
			$rec = new studienplatz();
			$this->mapRow($rec, $row);
			$this->result[] = $rec;
		}
		return true;
	}

	/**
	 * Laedt alle Studienplaetze zu einem Studiengang und Semester. Ergebnis
	 * steht in result, wobei es nur einen Datensatz geben kann, wenn
	 * keinAusbildungssemester = true ist.
	 * @param integer $studiengang_kz
	 * @param string studiensemester_kurzbz
	 * @param boolean Ausbildungssemester ist optional. Wenn true werden
	 *  nur Datensätze geladen wo das Ausbildungssemester null ist. Wenn
	 *  false werden alle geladen wo ein Ausbildungssemester eingetragen
	 *  ist.
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function load_studiengang_studiensemester($studiengang_kz, $studiensemester_kurzbz, $keinAusbildungssemester = true)
	{
		//Pruefen ob $studiengang_kz eine gueltige Zahl ist
		if(!is_numeric($studiengang_kz) || $studiengang_kz == '')
		{
			$this->errormsg = 'studiengang_kz muss eine gültige Zahl sein';
			return false;
		}

		//Lesen der Daten aus der Datenbank
		$qry = "SELECT * FROM lehre.tbl_studienplatz RIGHT JOIN lehre.tbl_studienplan using(studienplan_id) WHERE studiengang_kz=".
			$this->db_add_param($studiengang_kz, FHC_INTEGER, false).
			" AND studiensemester_kurzbz=".$this->db_add_param($studiensemester_kurzbz, FHC_STRING, false).
			" AND ausbildungssemester ".($keinAusbildungssemester ? 'is null':'is not null').
			" AND studienplan_id IS NOT NULL;";

		$this->result = array();
		if(!$this->db_query($qry))
		{
			return false;
		}

		while($row = $this->db_fetch_object())
		{
			$this->result[] = $row;
		}
		return true;
	}


	/**
	 * Liefert Array mit GPZ und NPZ für die FÖBis relevanten Orgformen (BB, VZ, VBB).
	 * Dient als Ersatz für load_studiengang_studiensemester_orgform weil hiermit
	 * alle Daten mit einer Abfrage geholt werden können.
	 * @param string $studienjahr  z.B: 2013
	 * @param int $zeitraum     z.B: 10  [Semester]
	 * @return boolean|array  false bei Fehler
	 */
	public function getAnzahlAlleOrgformen($studienjahr, $zeitraum)
	{

		// Semesterliste für where klausel erzeugen:
		$startSemester= 'WS'.substr($studienjahr,0,4);
		$semesterList = generateSemesterList($startSemester,($zeitraum*2)-1);
		$semesterList_comma_separated = "'".join("','",$semesterList)."'";

		//Lesen der Daten aus der Datenbank
		$qry =  "SELECT  studiensemester_kurzbz,studiengang_kz,
				sum(case when orgform_kurzbz='VZ' then npz else 0 end) as npz_vz,
				sum(case when orgform_kurzbz='BB' then npz else 0 end) as npz_bb,
				sum(case when orgform_kurzbz='VBB' then npz else 0 end) as npz_vbb,
				sum(npz) as npz_gesamt,
				sum(case when orgform_kurzbz='VZ' then gpz else 0 end) as gpz_vz,
				sum(case when orgform_kurzbz='BB' then gpz else 0 end) as gpz_bb,
				sum(case when orgform_kurzbz='VBB' then gpz else 0 end) as gpz_vbb,
				sum(gpz) as gpz_gesamt
				FROM lehre.tbl_studienplatz
				WHERE ausbildungssemester is null and studiensemester_kurzbz IN ($semesterList_comma_separated)
				group by studiensemester_kurzbz,studiengang_kz";
		$this->result = array();
		if(!$this->db_query($qry))
		{
			return false;
		}
		$result = array();
		while($row = $this->db_fetch_object())
		{
			$result[$row->studiensemester_kurzbz][$row->studiengang_kz]['BB']['NPZ'] = $row->npz_bb;
			$result[$row->studiensemester_kurzbz][$row->studiengang_kz]['VZ']['NPZ'] = $row->npz_vz;
			$result[$row->studiensemester_kurzbz][$row->studiengang_kz]['VBB']['NPZ'] = $row->npz_vbb;
			$result[$row->studiensemester_kurzbz][$row->studiengang_kz]['gesamt']['NPZ'] = $row->npz_gesamt;
			$result[$row->studiensemester_kurzbz][$row->studiengang_kz]['BB']['GPZ'] = $row->gpz_bb;
			$result[$row->studiensemester_kurzbz][$row->studiengang_kz]['VZ']['GPZ'] = $row->gpz_vz;
			$result[$row->studiensemester_kurzbz][$row->studiengang_kz]['VBB']['GPZ'] = $row->gpz_vbb;
			$result[$row->studiensemester_kurzbz][$row->studiengang_kz]['gesamt']['GPZ'] = $row->gpz_gesamt;
		}
		return $result;
	}


	/**
	 * Liefert alle Studienplätze eines Studiengangs und Semesters
	 * @param string $stg_kz  z.B: -16
	 * @param string $studiensemester_kurzbz  z.B: WS2015
	 * @return boolean|array  false bei Fehler
	 */
	public function getFromStgKzAndStudsem($stg_kz, $studiensemester_kurzbz)
	{

		if(!is_numeric($stg_kz))
		{
			$this->errormsg='stg_kz muss eine gueltige Zahl sein';
			return false;
		}

		if($studiensemester_kurzbz == "")
		{
			$this->errormsg='Es wurde kein gueltiges studiensemester_kurzbz uerbergeben';
			return false;
		}
	
		//Lesen der Daten aus der Datenbank
		$qry = "SELECT * FROM lehre.tbl_studienplatz
			JOIN public.tbl_studiengang using(studiengang_kz)
			WHERE studiensemester_kurzbz=".$this->db_add_param($studiensemester_kurzbz)."
			AND studiengang_kz=".$this->db_add_param($stg_kz, FHC_INTEGER);

		if($this->db_query($qry))
		{
			$result = array();
			while($row = $this->db_fetch_object())
			{
				$result[] = $row;
			}
			return $result;
		}
		return false;
	}

	/**
	 * Prueft die Variablen auf Gueltigkeit
	 * @return boolean true wenn ok, false im Fehlerfall
	 */
	protected function validate()
	{
		if(!is_numeric($this->studienplatz_id) && $this->studienplatz_id!='')
		{
			$this->errormsg='studienplatz_id enthaelt ungueltige Zeichen';
			return false;
		}
		if(!is_numeric($this->studiengang_kz) && $this->studiengang_kz!='')
		{
			$this->errormsg='studiengang_kz enthaelt ungueltige Zeichen';
			return false;
		}
		if(mb_strlen($this->orgform_kurzbz)>3)
		{
			$this->errormsg = 'orgform_kurzbz darf nicht länger als 3 Zeichen sein';
			return false;
		}
		if(mb_strlen($this->studiensemester_kurzbz)>16)
		{
			$this->errormsg = 'studiensemester_kurzbz darf nicht länger als 16 Zeichen sein';
			return false;
		}
		if(!is_numeric($this->ausbildungssemester) && $this->ausbildungssemester!='')
		{
			$this->errormsg='ausbildungssemester enthaelt ungueltige Zeichen';
			return false;
		}
		if(!is_numeric($this->gpz) && $this->gpz!='')
		{
			$this->errormsg='gpz enthaelt ungueltige Zeichen';
			return false;
		}if(!is_numeric($this->npz) && $this->npz!='')
		{
			$this->errormsg='npz enthaelt ungueltige Zeichen';
			return false;
		}if(!is_numeric($this->apz) && $this->apz!='')
		{
			$this->errormsg='apz enthaelt ungueltige Zeichen';
			return false;
		}if(!is_numeric($this->studienplan_id) && $this->studienplan_id!='')
		{
			$this->errormsg='studienplan_id enthaelt ungueltige Zeichen';
			return false;
		}
		$this->errormsg = '';
		return true;
	}

	/**
	 * Speichert den aktuellen Datensatz in die Datenbank
	 * Wenn $neu auf true gesetzt ist wird ein neuer Datensatz angelegt
	 * andernfalls wird der Datensatz mit der ID in $adresse_id aktualisiert
	 * @return boolean true wenn ok, false im Fehlerfall
	 */
	public function save()
	{
		//Variablen pruefen
		if(!$this->validate())
			return false;

		if($this->new)
		{
			//Neuen Datensatz einfuegen
			$qry='BEGIN;INSERT INTO lehre.tbl_studienplatz ('.
				'studiengang_kz, orgform_kurzbz, studiensemester_kurzbz, '.
				'ausbildungssemester, gpz, npz, apz, studienplan_id, insertamum, insertvon, '.
				'updateamum, updatevon) VALUES('.
				$this->db_add_param($this->studiengang_kz, FHC_INTEGER).', '.
				$this->db_add_param($this->orgform_kurzbz).', '.
				$this->db_add_param($this->studiensemester_kurzbz).', '.
				$this->db_add_param($this->ausbildungssemester, FHC_INTEGER).', '.
				//$this->db_add_param($this->art).', '.
				$this->db_add_param($this->gpz, FHC_INTEGER).', '.
				$this->db_add_param($this->npz, FHC_INTEGER).', '.
				$this->db_add_param($this->apz, FHC_INTEGER).', '.
				$this->db_add_param($this->studienplan_id, FHC_INTEGER).', '.
				'now(), '.
				$this->db_add_param($this->insertvon).', '.
				'now(), '.
				$this->db_add_param($this->updatevon).');';
		}
		else
		{
			//Pruefen ob studienplatz_id eine gueltige Zahl ist
			if(!is_numeric($this->studienplatz_id))
			{
				$this->errormsg = 'studienplatz_id muss eine gueltige Zahl sein';
				return false;
			}
			$qry='UPDATE lehre.tbl_studienplatz SET'.
				' studiengang_kz='.$this->db_add_param($this->studiengang_kz).', '.
				' orgform_kurzbz='.$this->db_add_param($this->orgform_kurzbz).', '.
				' studiensemester_kurzbz='.$this->db_add_param($this->studiensemester_kurzbz).', '.
				' ausbildungssemester='.$this->db_add_param($this->ausbildungssemester).', '.
				//' art='.$this->db_add_param($this->art).', '.
				' gpz='.$this->db_add_param($this->gpz).', '.
				' npz='.$this->db_add_param($this->npz).', '.
				' apz='.$this->db_add_param($this->apz, FHC_INTEGER).', '.
				' studienplan_id='.$this->db_add_param($this->studienplan_id, FHC_INTEGER).', '.
				' updateamum= now(), '.
				' updatevon='.$this->db_add_param($this->updatevon).
				' WHERE studienplatz_id='.$this->db_add_param($this->studienplatz_id, FHC_INTEGER, false).';';
		}

		if($this->db_query($qry))
		{
			if($this->new)
			{
				//naechste ID aus der Sequence holen
				$qry="SELECT currval('lehre.seq_studienplatz_studienplatz_id') as id;";
				if($this->db_query($qry))
				{
					if($row = $this->db_fetch_object())
					{
						$this->studienplatz_id = $row->id;
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
			return false;
		}
		return $this->studienplatz_id;
	}

	/**
	 * Loescht den Datenensatz mit der ID die uebergeben wird
	 * @param $studienplatz_id ID die geloescht werden soll
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function delete($studienplatz_id)
	{
		//Pruefen ob studienplatz_id eine gueltige Zahl ist
		if(!is_numeric($studienplatz_id) || $studienplatz_id == '')
		{
			$this->errormsg = 'studienplatz_id muss eine gültige Zahl sein'."\n";
			return false;
		}

		//loeschen des Datensatzes
		$qry="DELETE FROM lehre.tbl_studienplatz WHERE studienplatz_id=".$this->db_add_param($studienplatz_id, FHC_INTEGER, false).";";

		if($this->db_query($qry))
		{
			return true;
		}
		else
		{
			return false;
		}
	}
}
?>
