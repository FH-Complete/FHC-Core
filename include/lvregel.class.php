<?php
/* Copyright (C) 2013 fhcomplete.org
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
 * Authors: Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 */
/**
 * Klasse lvregel
 * Verwaltet die Regeln zu einer Lehrveranstaltung zB
 *  - Anmeldung zu Lehrveranstaltungen
 *  - Vollstaendigkeit von Modulen etc
 */
require_once(dirname(__FILE__).'/basis_db.class.php');
require_once(dirname(__FILE__).'/student.class.php');
require_once(dirname(__FILE__).'/prestudent.class.php');
require_once(dirname(__FILE__).'/studiensemester.class.php');

class lvregel extends basis_db
{
	protected $new=true;		// boolean
	public $result = array();	// Result Objekt

	//Tabellenspalten
	protected $lvregel_id;		// serial
	protected $lvregeltyp_kurzbz;					// varchar(32)
	protected $studienplan_lehrveranstaltung_id; 	// integer
	protected $lvregel_id_parent;					// integer
	protected $lehrveranstaltung_id;				// intger
	protected $operator;		// varchar(1)
	protected $parameter;		// text
	protected $insertamum;		// timestamp
	protected $insertvon;		// varchar(32)
	protected $updateamum;		// timestamp
	protected $updatevon;		// varchar(32)

	protected $lvregeltyp_arr=array();
	protected $bezeichnung;	//varchar(256)

	protected $lehrveranstaltung_bezeichnung;
	protected $cache;

	/**
	 * Konstruktor
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Setter
	 */
	public function __set($name, $value)
	{
		$this->$name = $value;
	}

	/**
	 * Getter
	 */
	public function __get($name)
	{
		return $this->$name;
	}

	/**
	 * Laedt die Regel mit der ID $lvregel_id
	 * @param  $lvregel_id ID der zu ladenden Regel
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function load($lvregel_id)
	{
		//Pruefen ob lvregel_id eine gueltige Zahl ist
		if(!is_numeric($lvregel_id) || $lvregel_id == '')
		{
			$this->errormsg = 'ID muss eine Zahl sein';
			return false;
		}

		//Daten aus der Datenbank lesen
		$qry = "SELECT * FROM lehre.tbl_lvregel WHERE lvregel_id=".$this->db_add_param($lvregel_id, FHC_INTEGER, false);

		if(!$this->db_query($qry))
		{
			$this->errormsg = 'Fehler bei einer Datenbankabfrage';
			return false;
		}

		if($row = $this->db_fetch_object())
		{
			$this->lvregel_id = $row->lvregel_id;
			$this->lvregeltyp_kurzbz = $row->lvregeltyp_kurzbz;
			$this->studienplan_lehrveranstaltung_id = $row->studienplan_lehrveranstaltung_id;
			$this->lvregel_id_parent = $row->lvregel_id_parent;
			$this->lehrveranstaltung_id = $row->lehrveranstaltung_id;
			$this->operator = $row->operator;
			$this->parameter = $row->parameter;
			$this->insertamum = $row->insertamum;
			$this->insertvon = $row->insertvon;
			$this->updatevon = $row->updatevon;
			$this->updateamum = $row->updateamum;
		
			$this->new = false;
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
		if(!is_numeric($this->lvregel_id) && $this->lvregel_id!='')
		{
			$this->errormsg='lvregel_id enthaelt ungueltige Zeichen';
			return false;
		}
		if(!is_numeric($this->studienplan_lehrveranstaltung_id) && $this->studienplan_lehrveranstaltung_id!='')
		{
			$this->errormsg = 'Studienplan_lehrveranstaltung_id ist ungueltigt';
			return false;
		}
		if(!is_numeric($this->lvregel_id_parent) && $this->lvregel_id_parent!='')
		{
			$this->errormsg = 'LVRegelIDParent ist ungueltig';
			return false;
		}
		if(!is_numeric($this->lehrveranstaltung_id) && $this->lehrveranstaltung_id!='')
		{
			$this->errormsg = 'Lehrveranstaltung_id ist ungueltig';
			return false;
		}

		//Gesamtlaenge pruefen
		if(mb_strlen($this->lvregeltyp_kurzbz)>32)
		{
			$this->errormsg = 'lvregeltyp darf nicht länger als 32 Zeichen sein';
			return false;
		}
		if(mb_strlen($this->operator)>1)
		{
			$this->errormsg = 'Operator darf nicht länger als 1 Zeichen sein';
			return false;
		}
		if($this->operator!='u' && $this->operator!='o' && $this->operator!='x')
		{
			$this->errormsg = 'Operator ist ungueltig';
			return false;
		}

		$this->errormsg = '';
		return true;
	}
	
	/**
	 * Speichert den aktuellen Datensatz in die Datenbank
	 * Wenn $neu auf true gesetzt ist wird ein neuer Datensatz angelegt
	 * andernfalls wird der Datensatz mit der ID in $lvregel_id aktualisiert
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
			$qry='BEGIN;INSERT INTO lehre.tbl_lvregel (lvregeltyp_kurzbz, studienplan_lehrveranstaltung_id,
				lvregel_id_parent, lehrveranstaltung_id, operator, parameter, insertamum, insertvon) VALUES('.
			      $this->db_add_param($this->lvregeltyp_kurzbz).', '.
			      $this->db_add_param($this->studienplan_lehrveranstaltung_id, FHC_INTEGER).', '.
			      $this->db_add_param($this->lvregel_id_parent, FHC_INTEGER).', '.
			      $this->db_add_param($this->lehrveranstaltung_id, FHC_INTEGER).', '.
			      $this->db_add_param($this->operator).', '.
			      $this->db_add_param($this->parameter).', '.
			      ' now(), '.
			      $this->db_add_param($this->insertvon).');';
		}
		else
		{
			//Pruefen ob lvregel_id eine gueltige Zahl ist
			if(!is_numeric($this->lvregel_id))
			{
				$this->errormsg = 'lvregel_id muss eine gueltige Zahl sein';
				return false;
			}
			$qry='UPDATE lehre.tbl_lvregel SET'.
				' lvregeltyp_kurzbz = '.$this->db_add_param($this->lvregeltyp_kurzbz).', '.
				' studienplan_lehrveranstaltung_id='.$this->db_add_param($this->studienplan_lehrveranstaltung_id, FHC_INTEGER).', '.
				' lvregel_id_parent='.$this->db_add_param($this->lvregel_id_parent, FHC_INTEGER).', '.
				' lehrveranstaltung_id='.$this->db_add_param($this->lehrveranstaltung_id, FHC_INTEGER).', '.
		      	' operator='.$this->db_add_param($this->operator).', '.
		      	' parameter='.$this->db_add_param($this->parameter).', '.
		      	' updateamum= now(), '.
		      	' updatevon='.$this->db_add_param($this->updatevon).' '.
		      	'WHERE lvregel_id='.$this->db_add_param($this->lvregel_id, FHC_INTEGER, false).';';
		}
        
		if($this->db_query($qry))
		{
			if($this->new)
			{
				//naechste ID aus der Sequence holen
				$qry="SELECT currval('lehre.seq_lvregel_lvregel_id') as id;";
				if($this->db_query($qry))
				{
					if($row = $this->db_fetch_object())
					{
						$this->lvregel_id = $row->id;
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
			$this->errormsg = 'Fehler beim Speichern des LVRegel-Datensatzes';
			return false;
		}
		return $this->lvregel_id;
	}

	/**
	 * Loescht den Datenensatz mit der ID die uebergeben wird
	 * @param $lvregel_id ID die geloescht werden soll
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function delete($lvregel_id)
	{
		//Pruefen ob lvregel_id eine gueltige Zahl ist
		if(!is_numeric($lvregel_id) || $lvregel_id == '')
		{
			$this->errormsg = 'lvregel_id muss eine gültige Zahl sein'."\n";
			return false;
		}

		//loeschen des Datensatzes
		$qry="DELETE FROM lehre.tbl_lvregel WHERE lvregel_id=".$this->db_add_param($lvregel_id, FHC_INTEGER, false).";";

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
	 * Laedt alle LVRegelTypen
	 */
	public function loadLVRegelTypen()
	{
		$qry = 'SELECT * FROM lehre.tbl_lvregeltyp ORDER BY bezeichnung';
		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				$obj = new lvregel();
				$obj->lvregeltyp_kurzbz = $row->lvregeltyp_kurzbz;
				$obj->bezeichnung = $row->bezeichnung;

				$this->result[] = $obj;
				$this->lvregeltyp_arr[$row->lvregeltyp_kurzbz]=$row->bezeichnung;
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
	 * Prueft ob eine Regel zu einer Lehrveranstaltungzuordnung vorhanden ist
	 */
	public function exists($studienplan_lehrveranstaltung_id)
	{
		$qry = 'SELECT 1 FROM lehre.tbl_lvregel WHERE studienplan_lehrveranstaltung_id='.$this->db_add_param($studienplan_lehrveranstaltung_id, FHC_INTEGER);

		if($result = $this->db_query($qry))
		{
			if($this->db_num_rows($result)>0)
				return true;
			else
				return false;
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Daten';
			return false;
		}
	}

	/**
	 * Laedt alle Regeln zu einer StudienplanLehrveranstaltung Zuordnung
	 * @param $studienplan_lehrveranstaltung_id
	 */
	public function loadLVRegeln($studienplan_lehrveranstaltung_id)
	{
		$qry = 'SELECT 
					*, (SELECT bezeichnung FROM lehre.tbl_lehrveranstaltung WHERE lehrveranstaltung_id=tbl_lvregel.lehrveranstaltung_id) as lvbezeichnung 
				FROM 
					lehre.tbl_lvregel 
				WHERE 
					studienplan_lehrveranstaltung_id='.$this->db_add_param($studienplan_lehrveranstaltung_id, FHC_INTEGER, false).'
				ORDER BY lvregel_id';

		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				$obj = new lvregel();

				$obj->lvregel_id = $row->lvregel_id;
				$obj->lvregeltyp_kurzbz = $row->lvregeltyp_kurzbz;
				$obj->studienplan_lehrveranstaltung_id = $row->studienplan_lehrveranstaltung_id;
				$obj->lvregel_id_parent = $row->lvregel_id_parent;
				$obj->lehrveranstaltung_id = $row->lehrveranstaltung_id;
				$obj->operator = $row->operator;
				$obj->parameter = $row->parameter;
				$obj->insertamum = $row->insertamum;
				$obj->insertvon = $row->insertvon;
				$obj->updatevon = $row->updatevon;
				$obj->updateamum = $row->updateamum;
				$obj->lehrveranstaltung_bezeichnung = $row->lvbezeichnung;

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
	 * Liefert die Lehrveranstaltungen als verschachtelten Tree
	 * @param $studienplan_lehrveranstaltung_id
	 * @return Array mit den verschachtelten Regeln
	 */
	public function getLVRegelTree($studienplan_lehrveranstaltung_id)
	{
		if($this->loadLVRegeln($studienplan_lehrveranstaltung_id))
		{
			$tree=array();
			foreach($this->result as $row)
			{
				if($row->lvregel_id_parent=='')
				{
					$tree[$row->lvregel_id]=$row->cleanResult();
					$tree[$row->lvregel_id]['childs'] = $this->getLVRegelTreeChilds($row->lvregel_id);
				}
			}
			return $tree;
		}
	}

	/**
	 * Generiert rekursiv die Subtrees des Lehrveranstaltungstrees
	 * @param $lvregel_id Regel ID des Teilbaumes
	 * @param Array mit den Subtree Elementen des Regelbaumes
	 */
	protected function getLVRegelTreeChilds($lvregel_id)
	{
		$childs = array();
		foreach($this->result as $row)
		{
			if($row->lvregel_id_parent===$lvregel_id)
			{
				$childs[$row->lvregel_id]=$row->cleanResult();
				$childs[$row->lvregel_id]['childs'] = $this->getLVRegelTreeChilds($row->lvregel_id);
			}
		}
		return $childs;
	}

	/**
	 * Erstellt ein Array aus dem Result Objekt für die Webservice Verwendung 
	 */
	public function cleanResult()
	{
		$data = array();

		if(count($this->result)>0)
		{
			foreach($this->result as $row)
			{
				$obj = new stdClass();

				$obj->lvregel_id = $row->lvregel_id;
				$obj->lvregeltyp_kurzbz = $row->lvregeltyp_kurzbz;
				$obj->studienplan_lehrveranstaltung_id = $row->studienplan_lehrveranstaltung_id;
				$obj->lvregel_id_parent = $row->lvregel_id_parent;
				$obj->lehrveranstaltung_id = $row->lehrveranstaltung_id;
				$obj->operator = $row->operator;
				$obj->parameter = $row->parameter;
				$obj->insertamum = $row->insertamum;
				$obj->insertvon = $row->insertvon;
				$obj->updatevon = $row->updatevon;
				$obj->updateamum = $row->updateamum;

				$obj->lvregeltyp_kurzbz = $row->lvregeltyp_kurzbz;
				$obj->bezeichnung = $row->bezeichnung;
				$obj->lehrveranstaltung_bezeichnung = $row->lehrveranstaltung_bezeichnung;
				$data[]=$obj;
			}
		}
		else
		{
			$obj = new stdClass();

			$obj->lvregel_id = $this->lvregel_id;
			$obj->lvregeltyp_kurzbz = $this->lvregeltyp_kurzbz;
			$obj->studienplan_lehrveranstaltung_id = $this->studienplan_lehrveranstaltung_id;
			$obj->lvregel_id_parent = $this->lvregel_id_parent;
			$obj->lehrveranstaltung_id = $this->lehrveranstaltung_id;
			$obj->operator = $this->operator;
			$obj->parameter = $this->parameter;
			$obj->insertamum = $this->insertamum;
			$obj->insertvon = $this->insertvon;
			$obj->updatevon = $this->updatevon;
			$obj->updateamum = $this->updateamum;

			$obj->lvregeltyp_kurzbz = $this->lvregeltyp_kurzbz;
			$obj->bezeichnung = $this->bezeichnung;
			$obj->lehrveranstaltung_bezeichnung = $this->lehrveranstaltung_bezeichnung;
			$data[]=$obj;
		}
		return $data;
	}

	/**
	 * Prüft ob sich ein Student zu einer Lehrveranstaltung anmelden darf
	 * @param $uid UID des Studierenden
	 * @param $studienplan_lehrveranstaltung_id ID der Lehrveranstaltungszuordnung
	 */
	public function isZugangsberechtigt($uid, $studienplan_lehrveranstaltung_id, $studiensemester_kurzbz=null)
	{
		$this->debug('Teste Zugangsberechtigung für '.$uid);
		if($result = $this->getLVRegelTree($studienplan_lehrveranstaltung_id))
		{
			return $this->TestRegeln($uid, $result, $studiensemester_kurzbz);
		}
	}

	/**
	 * Prueft die Regeln fuer einen Studierenden
	 * @param $uid UID des Studierenden
	 * @param $regel_obj Regel Baum
	 * @param $studiensemester_kurzbz Studiensemester das geprueft werden soll
	 */
	public function TestRegeln($uid, $regel_obj, $studiensemester_kurzbz=null)
	{
		$retval=true;
		foreach($regel_obj as $regel)
		{
			$this->debug('<br>');
			$testval = $this->Test($uid, $regel, $studiensemester_kurzbz);
			$retval = $this->Compare($regel[0]->operator, $retval, $testval);
			$this->debug(' - RETVAL:'.($retval?'TRUE':'FALSE'));
		}

		return $retval;
	}

	/**
	 * Vergleicht die Regeln untereinander anhand des Operators
	 * @param $operator Operator der Regel
	 * @param $retval Boolean Wert der Ausgangsregel
	 * @param $testval Boolean Wert der Vergleichsregel
	 * @return Boolean Ergebnis des Vergleichs
	 */
	public function Compare($operator, $retval, $testval)
	{
		switch($operator)
		{
			case 'u':
				$this->debug(($retval?'T':'F').' && '.($testval?'T':'F'));
				$retval=($retval && $testval);
				$this->debug('='.($retval?'T':'F'));
				break;
			case 'o':
				$this->debug(($retval?'T':'F').' || '.($testval?'T':'F'));
				$retval=($retval || $testval);
				$this->debug('='.($retval?'T':'F'));
				break;
			case 'x':
				$this->debug(($retval?'T':'F').' XOR '.($testval?'T':'F'));
				$retval=($retval xor $testval);
				$this->debug('='.($retval?'T':'F'));
				break;
		}
		return $retval;
	}

	/**
	 * Testet die Regel für einen Studenten
	 * @param $uid User
	 * @param $regel_obj
	 * @param $studiensemester_kurzbz
	 */
	public function Test($uid, $regel_obj, $studiensemester_kurzbz=null)
	{
		$regel = $regel_obj[0];

		$this->debug('Teste Regel '.$regel->lvregel_id);

		switch($regel->lvregeltyp_kurzbz)
		{
			case 'ausbsemmin':
				/* Prueft ob das Ausbildungssemester das mindestens erforderlich ist 
					um die Lehrveranstaltung zu besuchen */
				
				$this->debug('Regeltyp ausbsemmin');

				// Wenn das Studiensemester nicht gesetzt ist, wird das aktuelle verwendet
				if($studiensemester_kurzbz=='')
				{
					$studiensemester = new studiensemester();
					$studiensemester_kurzbz = $studiensemester->getaktorNext();
				}

				// Ausbildungssemester wird nur beim 1. durchlauf ermittelt
				if(!isset($this->cache[$uid]) && !isset($this->cache[$uid][$studiensemester_kurzbz]))
				{
					$student = new student();
					$student->load($uid);
				
					// Ausbildungssemester aus dem Status holen
					$prestudent = new prestudent();
					if($prestudent->getLastStatus($student->prestudent_id, $studiensemester_kurzbz))
					{
						$this->cache[$uid][$studiensemester_kurzbz]=$prestudent->semester;
					}
				}
				$ausbildungssemester = $this->cache[$uid][$studiensemester_kurzbz];

				// Vergleichen des Ausbildungssemesters mit dem RegelParameter
				if($ausbildungssemester>=$regel->parameter)
				{
					$this->debug('StudSem: '.$ausbildungssemester.' >= RegelParam: '.$regel->parameter);
					$this->debug('TRUE');
					$retval = true;
				}
				else
				{
					$this->debug('StudSem: '.$ausbildungssemester.' >= RegelParam: '.$regel->parameter);
					$this->debug('FALSE');
					$retval = false;
				}
				break;


			case 'lvpositiv':
				$this->debug('Regeltyp lvpositiv');
				$qry = "SELECT 
							* 
						FROM 
							lehre.tbl_zeugnisnote 
							JOIN lehre.tbl_note USING(note) 
						WHERE 
							tbl_note.positiv 
							AND student_uid=".$this->db_add_param($uid)."
							AND lehrveranstaltung_id=".$this->db_add_param($regel->lehrveranstaltung_id);

				if($result = $this->db_query($qry))
				{
					if($row = $this->db_fetch_object($result))
					{
						$this->debug('Positive Note gefunden:'.$row->note);
						$this->debug('TRUE');
						$retval = true;
					}
					else
					{
						$this->debug('Keine positive Note');
						$this->debug('FALSE');
						$retval = false;
					}
				}
				else
				{
					$this->debug('Fehler bei Abfrage');
					$this->errormsg = 'Fehler bei Abfrage';	
					$retval = false;
				}
				break;

			default: 
				// Eventuell in Addons nach Regeltypen suchen
				break;
		}

		// Subregeln dieser LVRegel pruefen
		if(isset($regel_obj['childs']) && count($regel_obj['childs'])>0)
		{
			$this->debug('<br> - Subregel '.$regel->lvregel_id.' -');
			$testval = $this->TestRegeln($uid, $regel_obj['childs']);
			$retval = $this->Compare($regel->operator, $retval, $testval);
			$this->debug('<br> - Subregel '.$regel->lvregel_id.' Ende-');
		}

		return $retval;
	}

	public function debug($msg)
	{
		echo ' '.$msg;
	}
}
?>
