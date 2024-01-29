<?php
/* Copyright (C) 2006 FH Technikum-Wien
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
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>,
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>,
 *          Andreas Moik <moik@technikum-wien.at>,
 *			Simon Schwebler <simon.schwebler@technikum-wien.at>,
 *          Manfred Kindl <manfred.kindl@technikum-wien.at>
 */
/**
 * Klasse zur Verwaltung der Ablaeufe der Raihungstests
 */
require_once(dirname(__FILE__).'/basis_db.class.php');

class ablauf extends basis_db
{
	public $new;
	public $result = array();

	//Tabellenspalten
	public $studiengang_kz;
	public $gebiet_id;
	public $reihung;
	public $gewicht;
	public $ablauf_id;
	public $insertamum;
	public $insertvon;
	public $updateamum;
	public $updatevon;
	public $semester;
	public $ablauf_vorgaben_id;
	public $studienplan_id;
	public $sprachwahl;
	public $sprache;
	public $content_id;

	/**
	 * Konstruktor
	 * @param $ablauf_id ID des zu ladenden Datensatzes
	 */
	public function __construct($ablauf_id=null)
	{
		parent::__construct();

		if(!is_null($ablauf_id))
			$this->load($ablauf_id);
	}

	/**
	 * Laedt einen Datensatz
	 * @param abschlusspruefung_id ID des zu ladenden Datensatzes
	 */
	public function load($ablauf_id)
	{
		//id auf Gueltigkeit pruefen
		if(!is_numeric($ablauf_id))
		{
			$this->errormsg = 'ablauf_id muss eine gueltige Zahl sein';
			return false;
		}

		//laden des Datensatzes
		$qry = "SELECT
					tbl_ablauf.*,
					tbl_ablauf_vorgaben.sprache,
					tbl_ablauf_vorgaben.sprachwahl,
					tbl_ablauf_vorgaben.content_id
				FROM
					testtool.tbl_ablauf
				LEFT JOIN
					testtool.tbl_ablauf_vorgaben USING (ablauf_vorgaben_id)
				WHERE
					ablauf_id=".$this->db_add_param($ablauf_id, FHC_INTEGER, false).";";

		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				$obj = new ablauf();

				$obj->studiengang_kz = $row->studiengang_kz;
				$obj->gebiet_id = $row->gebiet_id;
				$obj->reihung = $row->reihung;
				$obj->gewicht = $row->gewicht;
				$obj->ablauf_id = $row->ablauf_id;
				$obj->semester = $row->semester;
				$obj->ablauf_vorgaben_id = $row->ablauf_vorgaben_id;
				$obj->studienplan_id = $row->studienplan_id;
				$obj->updateamum = $row->updateamum;
				$obj->updatevon = $row->updatevon;
				$obj->insertamum = $row->insertamum;
				$obj->insertvon = $row->insertvon;
				$obj->sprache = $row->sprache;
				$obj->sprachwahl = $this->db_parse_bool($row->sprachwahl);
				$obj->content_id = $row->content_id;

				$this->result[] = $obj;
				return true;
			}
			else
			{
				$this->errormsg = 'Fehler bei der Datenbankabfrage';
				return false;
			}
		}
		else
		{
			$this->errormsg = 'Fehler bei der Datenbankabfrage';
			return false;
		}
	}

	/**
	 * Loescht einen Datensatz
	 * @param abschlusspruefung_id ID des zu loeschenden Datensatzes
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function delete($ablauf_id)
	{
		//abschlusspruefung_id auf Gueltigkeit pruefen
		if(!is_numeric($ablauf_id))
		{
			$this->errormsg = 'ablauf_id muss eine gueltige Zahl sein';
			return false;
		}

		$qry = "DELETE FROM testtool.tbl_ablauf
				WHERE ablauf_id=".$this->db_add_param($ablauf_id, FHC_INTEGER, false).";";

		if($this->db_query($qry))
		{
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Loeschen';
			return false;
		}
	}

	/**
	 * Loescht einen Ablauf-Vorgabe Datensatz
	 * @param $ablauf_vorgabe_id ID des zu loeschenden Datensatzes
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function deleteAblaufVorgabe($ablauf_vorgabe_id)
	{
		//$ablauf_vorgabe_id auf Gueltigkeit pruefen
		if(!is_numeric($ablauf_vorgabe_id))
		{
			$this->errormsg = 'ablauf_vorgabe_id muss eine gueltige Zahl sein';
			return false;
		}

		$qry = "DELETE FROM testtool.tbl_ablauf_vorgaben
				WHERE ablauf_vorgaben_id=".$this->db_add_param($ablauf_vorgabe_id, FHC_INTEGER).";";

		if($this->db_query($qry))
		{
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Loeschen der Ablauf-Vorgabe mit der ID '.$ablauf_vorgabe_id;
			return false;
		}
	}

	/**
	 * Prueft die Daten vor dem Speichern
	 *
	 * @return true wenn ok, false wenn Fehler
	 */
	protected function validate()
	{
		if($this->studiengang_kz=='')
		{
			$this->errormsg = 'studiengang_kz muss eingegeben werden';
			return false;
		}
		if($this->gebiet_id=='')
		{
			$this->errormsg = 'gebiet_id muss eingetragen werden';
			return false;
		}
		if($this->reihung=='')
		{
			$this->errormsg = 'reihung muss eingetragen werden';
			return false;
		}
		if($this->gewicht=='')
		{
			$this->errormsg = 'gewicht muss eingetragen werden';
			return false;
		}
		if($this->semester=='')
		{
			$this->errormsg = 'semester muss eingetragen werden';
			return false;
		}
		return true;
	}

	/**
	 * Speichert den aktuellen Datensatz
	 * Wenn $neu auf true gesetzt ist wird ein neuer Datensatz angelegt
	 * andernfalls wird der Datensatz mit der ID in $akte_id aktualisiert
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function save($new=null)
	{
		if(!$this->validate())
			return false;
		if($new==null)
			$new = $this->new;

		if($new)
		{
			//Neuen Datensatz anlegen
			$qry = "BEGIN;INSERT INTO testtool.tbl_ablauf (studiengang_kz, gebiet_id, reihung,
					gewicht, semester, ablauf_vorgaben_id, studienplan_id,
					updateamum, updatevon, insertamum, insertvon) VALUES (".
						$this->db_add_param($this->studiengang_kz, FHC_INTEGER).', '.
						$this->db_add_param($this->gebiet_id, FHC_INTEGER).', '.
						$this->db_add_param($this->reihung, FHC_INTEGER).', '.
						$this->db_add_param($this->gewicht, FHC_INTEGER).', '.
						$this->db_add_param($this->semester, FHC_INTEGER).', '.
						$this->db_add_param($this->ablauf_vorgaben_id, FHC_INTEGER).', '.
						$this->db_add_param($this->studienplan_id).', '.
						$this->db_add_param($this->updateamum).', '.
						$this->db_add_param($this->updatevon).', '.
						$this->db_add_param($this->insertamum).', '.
						$this->db_add_param($this->insertvon).');';

		}
		else
		{
			//Bestehenden Datensatz aktualisieren
			$qry= "UPDATE testtool.tbl_ablauf SET".
				" studiengang_kz=".$this->db_add_param($this->studiengang_kz, FHC_INTEGER).",".
				" gebiet_id=".$this->db_add_param($this->gebiet_id, FHC_INTEGER).",".
				" reihung=".$this->db_add_param($this->reihung, FHC_INTEGER).",".
				" gewicht=".$this->db_add_param($this->gewicht, FHC_INTEGER).",".
				" semester=".$this->db_add_param($this->semester, FHC_INTEGER).",".
				" ablauf_vorgaben_id=".$this->db_add_param($this->ablauf_vorgaben_id, FHC_INTEGER).",".
				" studienplan_id=".$this->db_add_param($this->studienplan_id).",".
				" updateamum=".$this->db_add_param($this->updateamum).",".
				" updatevon=".$this->db_add_param($this->updatevon).
				" WHERE ablauf_id=".$this->db_add_param($this->ablauf_id, FHC_INTEGER);
		}

		if($this->db_query($qry))
		{
			if($new)
			{
				$qry = "SELECT currval('testtool.tbl_ablauf_ablauf_id_seq') as id";
				if($this->db_query($qry))
				{
					if($row = $this->db_fetch_object())
					{
						$this->ablauf_id = $row->id;
						$this->db_query('COMMIT;');
						return true;
					}
					else
					{
						$this->errormsg = 'Fehler beim Auslesen der Sequence';
						$this->db_query('ROLLBACK');
						return false;
					}
				}
				else
				{
					$this->errormsg = 'Fehler beim Auslesen der Sequence';
					$this->db_query('ROLLBACK');
					return false;
				}
			}
			else
				return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern des Datensatzes';
			return false;
		}
	}

	/**
	 * Laedt die zugehoerigen Gebiete zum angegebenen Studiengang (gegebenfalls auch Studienplan)
	 * @param $studiengang_kz ID des Studiengang
	 * @param $studienplan_id ID des Studienplans
	 * @param $semester
	 * @return boolean true wenn ok sonst false
	 */
	public function getAblaufGebiete($studiengang_kz, $studienplan_id=null, $semester=null)
	{
		$qry = "SELECT
					tbl_ablauf.*,
					tbl_ablauf_vorgaben.sprache,
					tbl_ablauf_vorgaben.sprachwahl,
					tbl_ablauf_vorgaben.content_id
				FROM
					testtool.tbl_ablauf
				LEFT JOIN
					testtool.tbl_ablauf_vorgaben USING (ablauf_vorgaben_id)
				WHERE
					tbl_ablauf.studiengang_kz=".$this->db_add_param($studiengang_kz, FHC_INTEGER);

		if (!is_null($studienplan_id))
			$qry .= " AND studienplan_id=".$this->db_add_param($studienplan_id, FHC_INTEGER);
		if (!is_null($semester))
			$qry .= " AND semester=".$this->db_add_param($semester, FHC_INTEGER);

		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				$obj = new stdClass();

				$obj->ablauf_id = $row->ablauf_id;
				$obj->gebiet_id = $row->gebiet_id;
				$obj->reihung = $row->reihung;
				$obj->gewicht = $row->gewicht;
				$obj->semester = $row->semester;
				$obj->studienplan_id = $row->studienplan_id;
				$obj->ablauf_vorgaben_id = $row->ablauf_vorgaben_id;
				$obj->sprache = $row->sprache;
				$obj->sprachwahl = $this->db_parse_bool($row->sprachwahl);
				$obj->content_id = $row->content_id;

				$this->result[]= $obj;
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
	 * Leadt die ablauf_id zu einer Kombination aus Studiengang und Gebiet
	 * @param $studiengang_kz Studiengang
	 * @param $gebiet_id Gebiet
	 * @return boolean true wenn ok sonst false
	 */
	public function getAblaufId($studiengang_kz, $gebiet_id)
	{
		$qry = "SELECT
					*
				FROM
					testtool.tbl_ablauf
				WHERE
					studiengang_kz=".$this->db_add_param($studiengang_kz, FHC_INTEGER)."
					AND gebiet_id=".$this->db_add_param($gebiet_id, FHC_INTEGER).";";

		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				$this->result[] = $row->ablauf_id;
				return true;
			}
			else
			{
				$this->errormsg = 'Fehler bei der Datenbankabfrage';
				return false;
			}
		}
	}

	/**
	 * Laedt eine Ablauf-Vorgabe
	 * @param $ablauf_vorgaben_id ID des zu ladenden Datensatzes
	 */
	public function loadAblaufVorgabe($ablauf_vorgaben_id)
	{
		//id auf Gueltigkeit pruefen
		if(!is_numeric($ablauf_vorgaben_id))
		{
			$this->errormsg = 'ablauf_vorgaben_id muss eine gueltige Zahl sein';
			return false;
		}

		//laden des Datensatzes
		$qry = "SELECT
					*
				FROM
					testtool.tbl_ablauf_vorgaben
				WHERE ablauf_vorgaben_id=".$this->db_add_param($ablauf_vorgaben_id, FHC_INTEGER, false).";";

		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				$this->studiengang_kz = $row->studiengang_kz;
				$this->sprache = $row->sprache;
				$this->sprachwahl = $this->db_parse_bool($row->sprachwahl);
				$this->content_id = $row->content_id;
				$this->updateamum = $row->updateamum;
				$this->updatevon = $row->updatevon;
				$this->insertamum = $row->insertamum;
				$this->insertvon = $row->insertvon;

				return true;
			}
			else
			{
				$this->errormsg = 'Fehler bei der Datenbankabfrage';
				return false;
			}
		}
		else
		{
			$this->errormsg = 'Fehler bei der Datenbankabfrage';
			return false;
		}
	}

	/**
	 * Speichert eine Ablauf-Vorgabe
	 * Wenn $neu auf true gesetzt ist wird ein neuer Datensatz angelegt
	 * andernfalls wird der Datensatz mit der ID in $ablauf_vorgaben_id aktualisiert
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function saveAblaufVorgabe($new=null)
	{
		if($new==null)
			$new = $this->new;

		if($new)
		{
			//Neuen Datensatz anlegen
			$qry = "BEGIN;INSERT INTO testtool.tbl_ablauf_vorgaben (studiengang_kz, sprache, sprachwahl,
					content_id, updateamum, updatevon, insertamum, insertvon) VALUES (".
						$this->db_add_param($this->studiengang_kz, FHC_INTEGER).', '.
						$this->db_add_param($this->sprache).', '.
						$this->db_add_param($this->sprachwahl, FHC_BOOLEAN).', '.
						$this->db_add_param($this->content_id, FHC_INTEGER).', '.
						$this->db_add_param($this->updateamum).', '.
						$this->db_add_param($this->updatevon).', '.
						$this->db_add_param($this->insertamum).', '.
						$this->db_add_param($this->insertvon).');';

		}
		else
		{
			//Bestehenden Datensatz aktualisieren
			$qry= "UPDATE testtool.tbl_ablauf_vorgaben SET".
				" studiengang_kz=".$this->db_add_param($this->studiengang_kz, FHC_INTEGER).",".
				" sprache=".$this->db_add_param($this->sprache).",".
				" sprachwahl=".$this->db_add_param($this->sprachwahl, FHC_BOOLEAN).",".
				" content_id=".$this->db_add_param($this->content_id, FHC_INTEGER).",".
				" updateamum=".$this->db_add_param($this->updateamum).",".
				" updatevon=".$this->db_add_param($this->updatevon).
				" WHERE ablauf_vorgaben_id=".$this->db_add_param($this->ablauf_vorgaben_id, FHC_INTEGER);
		}

		if($this->db_query($qry))
		{
			if($new)
			{
				$qry = "SELECT currval('testtool.tbl_ablauf_vorgaben_ablauf_vorgaben_id_seq') as id";
				if($this->db_query($qry))
				{
					if($row = $this->db_fetch_object())
					{
						$this->ablauf_vorgaben_id = $row->id;
						$this->db_query('COMMIT;');
						return true;
					}
					else
					{
						$this->errormsg = 'Fehler beim Auslesen der Sequence';
						$this->db_query('ROLLBACK');
						return false;
					}
				}
				else
				{
					$this->errormsg = 'Fehler beim Auslesen der Sequence';
					$this->db_query('ROLLBACK');
					return false;
				}
			}
			else
				return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern des Datensatzes';
			return false;
		}
	}

	/**
	 * Laedt alle Ablauf-Vorgaben Eintraege
	 * @return boolean true wenn ok sonst false
	 */
	public function getAllAblaufVorgaben()
	{
		$qry = "SELECT * FROM testtool.tbl_ablauf_vorgaben";

		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				$obj = new ablauf();

				$obj->ablauf_vorgaben_id = $row->ablauf_vorgaben_id;
				$obj->studiengang_kz = $row->studiengang_kz;
				$obj->sprache = $row->sprache;
				$obj->sprachwahl = $this->db_parse_bool($row->sprachwahl);
				$obj->content_id = $row->content_id;

				$this->result[]= $obj;
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
	 * Laedt einen Ablauf-Vorgabe Eintrag anhand der uebergebenen Studiengangskennzahl
	 * @param $studiengang_kz ID des Studiengang
	 * @return boolean true wenn ok sonst false
	 */
	public function getAblaufVorgabeStudiengang($studiengang_kz)
	{
		//id auf Gueltigkeit pruefen
		if(!is_numeric($studiengang_kz))
		{
			$this->errormsg = 'studiengang_kz muss eine gueltige Zahl sein';
			return false;
		}

		$qry = "SELECT * FROM testtool.tbl_ablauf_vorgaben
				WHERE studiengang_kz=".$this->db_add_param($studiengang_kz, FHC_INTEGER, false);

		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				$obj = new ablauf();

				$obj->ablauf_vorgaben_id = $row->ablauf_vorgaben_id;
				$obj->studiengang_kz = $row->studiengang_kz;
				$obj->sprache = $row->sprache;
				$obj->sprachwahl = $this->db_parse_bool($row->sprachwahl);
				$obj->content_id = $row->content_id;

				$this->result[]= $obj;
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
	 * Zaehlt, wie of die ablauf_vorgabe_id noch in tbl_ablauf verwendet wird
	 * @param integer $ablauf_vorgaben_id Ablauf-Vorlage-ID
	 * @return boolean true wenn ok sonst false
	 */
	public function countAblaufVorgabe($ablauf_vorgaben_id)
	{
		//id auf Gueltigkeit pruefen
		if(!is_numeric($ablauf_vorgaben_id))
		{
			$this->errormsg = 'ablauf_vorgaben_id muss eine gueltige Zahl sein';
			return false;
		}

		$qry = "SELECT count(*) FROM testtool.tbl_ablauf
				WHERE ablauf_vorgaben_id=".$this->db_add_param($ablauf_vorgaben_id, FHC_INTEGER);

		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				return $row->count;
			}
			else
			{
				$this->errormsg = 'Fehler bei der Datenbankabfrage';
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
	 * Berechnet die Dauer eines Tests
	 * @param integer $studiengang_kz Kennzahl des Studiengangs
	 * @param integer $studienplan_id Optional. Default NULL. ID des Studienplans
	 * @param integer $semester Optional. Default NULL.
	 * @return boolean true wenn ok sonst false
	 */
	public function getDauer($studiengang_kz, $studienplan_id=null, $semester=null)
	{
		$qry = "SELECT
					SUM (zeit) as dauer
				FROM
					testtool.tbl_ablauf
				JOIN
					testtool.tbl_gebiet USING (gebiet_id)
				WHERE
					studiengang_kz=".$this->db_add_param($studiengang_kz, FHC_INTEGER);

		if (!is_null($studienplan_id))
			$qry .= " AND studienplan_id=".$this->db_add_param($studienplan_id, FHC_INTEGER);
		if (!is_null($semester))
			$qry .= " AND semester=".$this->db_add_param($semester, FHC_INTEGER);

		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{

				return $row->dauer;
			}
			else
			{
				$this->errormsg = 'Fehler bei der Datenbankabfrage';
				return false;
			}
		}
		else
		{
			$this->errormsg = 'Fehler bei der Datenbankabfrage';
			return false;
		}
	}
}
?>
