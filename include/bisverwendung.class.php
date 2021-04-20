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

class bisverwendung extends basis_db
{
	public $new;
	public $result = array();

	//Tabellenspalten
	public $bisverwendung_id;
	public $ba1code;
	public $ba2code;
	public $beschausmasscode;
	public $verwendung_code;
	public $mitarbeiter_uid;
	public $hauptberufcode;
	public $hauptberuflich;
	public $habilitation;
	public $beginn;
	public $ende;
	public $vertragsstunden;
	public $updateamum;
	public $updatevon;
	public $insertamum;
	public $insertvon;
	public $ext_id;
	public $dv_art;
	public $inkludierte_lehre;
	public $zeitaufzeichnungspflichtig;

	public $ba1bez;
	public $ba2bez;
	public $beschausmass;
	public $verwendung;
	public $hauptberuf;

	/**
	 * Konstruktor
	 * @param bisverwendung_id ID des zu ladenden Datensatzes
	 */
	public function __construct($bisverwendung_id=null)
	{
		parent::__construct();

		if(!is_null($bisverwendung_id))
			$this->load($bisverwendung_id);
	}

	/**
	 * Laedt einen Datensatz
	 * @param bisverwendung_id ID des zu ladenden Datensatzes
	 */
	public function load($bisverwendung_id)
	{
		//bisverwendung_id auf gueltigkeit pruefen
		if(!is_numeric($bisverwendung_id) || $bisverwendung_id == '')
		{
			$this->errormsg = 'bisverwendung_id muss eine gueltige Zahl sein';
			return false;
		}

		//laden des Datensatzes
		$qry = "SELECT
					*
				FROM
					bis.tbl_beschaeftigungsart1, bis.tbl_beschaeftigungsart2,
					bis.tbl_beschaeftigungsausmass, bis.tbl_verwendung, bis.tbl_bisverwendung
					LEFT JOIN bis.tbl_hauptberuf USING(hauptberufcode)
				WHERE
					tbl_bisverwendung.ba1code=tbl_beschaeftigungsart1.ba1code AND
					tbl_bisverwendung.ba2code=tbl_beschaeftigungsart2.ba2code AND
					tbl_bisverwendung.beschausmasscode=tbl_beschaeftigungsausmass.beschausmasscode AND
					tbl_bisverwendung.verwendung_code=tbl_verwendung.verwendung_code AND
					bisverwendung_id=".$this->db_add_param($bisverwendung_id, FHC_INTEGER).";";

		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				$this->bisverwendung_id = $row->bisverwendung_id;
				$this->ba1code = $row->ba1code;
				$this->ba2code = $row->ba2code;
				$this->beschausmasscode = $row->beschausmasscode;
				$this->verwendung_code = $row->verwendung_code;
				$this->mitarbeiter_uid = $row->mitarbeiter_uid;
				$this->hauptberufcode = $row->hauptberufcode;
                $this->hauptberuflich = $this->db_parse_bool($row->hauptberuflich);
                $this->habilitation = $this->db_parse_bool($row->habilitation);
				$this->beginn = $row->beginn;
				$this->ende = $row->ende;
				$this->updatevon = $row->updatevon;
				$this->updateamum = $row->updateamum;
				$this->insertamum = $row->insertamum;
				$this->insertvon = $row->insertvon;
				$this->ext_id = $row->ext_id;
				$this->ba1bez = $row->ba1bez;
				$this->ba2bez = $row->ba2bez;
				$this->beschausmass = $row->beschausmassbez;
				$this->verwendung = $row->verwendungbez;
				$this->hauptberuf = $row->bezeichnung;
				$this->vertragsstunden = $row->vertragsstunden;
				$this->dv_art = $row->dv_art;
				$this->inkludierte_lehre = $row->inkludierte_lehre;
				$this->zeitaufzeichnungspflichtig = $this->db_parse_bool($row->zeitaufzeichnungspflichtig);
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
	 * @param bisverwendung_id ID des zu loeschenden Datensatzes
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function delete($bisverwendung_id)
	{
		//akte_id auf gueltigkeit pruefen
		if(!is_numeric($bisverwendung_id) || $bisverwendung_id == '')
		{
			$this->errormsg = 'bisverwendung_id muss eine gueltige Zahl sein';
			return false;
		}
		$qry = "SELECT count(*) as anzahl FROM bis.tbl_bisfunktion WHERE bisverwendung_id=".$this->db_add_param($bisverwendung_id, FHC_INTEGER);
		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				if($row->anzahl>0)
				{
					$this->errormsg = 'Bitte zuerst alle zugehoerigen Funktionen loeschen';
					return false;
				}
			}
		}

		$qry = "DELETE FROM bis.tbl_bisverwendung WHERE bisverwendung_id = ".$this->db_add_param($bisverwendung_id, FHC_INTEGER).";";

		if($this->db_query($qry))
		{
			//Log schreiben
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Loeschen';
			return false;
		}
	}


	/**
	 * Prueft das Datum
	 * @param $date = string
	 * @return true wenn ok, sonst false
	 */
	static public function verifyDate($date, $strict = true)
	{
		$dateTime = DateTime::createFromFormat('Y-m-d', $date);
		if ($strict) {
			$errors = DateTime::getLastErrors();
			if (!empty($errors['warning_count'])) {
				return false;
			}
		}
		return $dateTime !== false;
	}

	/**
	 * Prueft die Daten vor dem Speichern
	 *
	 * @return true wenn ok, sonst false
	 */
	protected function validate()
	{
		if(!is_numeric($this->vertragsstunden) && $this->vertragsstunden!='')
		{
			$this->errormsg = 'Vertragsstunden sind ungueltig';
			return false;
		}
		elseif(!$this->verifyDate($this->beginn) && !empty($this->beginn))
		{
			$this->errormsg = 'Start Datum ist kein Valides Datum: '.$this->beginn;
			return false;
		}
		elseif(!$this->verifyDate($this->ende) && !empty($this->ende))
		{
			$this->errormsg = 'End Datum ist kein Valides Datum: '.$this->ende;
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

		if(is_bool($this->hauptberuflich))
			$hauptberuflich = $this->db_add_param($this->hauptberuflich, FHC_BOOLEAN);
		else
			$hauptberuflich = 'null';

		if(is_bool($this->zeitaufzeichnungspflichtig))
		{
			$zeitaufzeichnungspflichtig = $this->db_add_param($this->zeitaufzeichnungspflichtig, FHC_BOOLEAN);
		}
		else
		{
			$zeitaufzeichnungspflichtig = 'null';
		}

		if($new)
		{
			//Neuen Datensatz anlegen
			$qry = "BEGIN;INSERT INTO bis.tbl_bisverwendung (ba1code, ba2code, beschausmasscode,
					verwendung_code, mitarbeiter_uid, hauptberufcode, hauptberuflich, habilitation, beginn, ende, vertragsstunden,
					updateamum, updatevon, insertamum, insertvon, dv_art, inkludierte_lehre, zeitaufzeichnungspflichtig) VALUES (".
			       $this->db_add_param($this->ba1code, FHC_INTEGER).', '.
			       $this->db_add_param($this->ba2code, FHC_INTEGER).', '.
			       $this->db_add_param($this->beschausmasscode, FHC_INTEGER).', '.
			       $this->db_add_param($this->verwendung_code, FHC_INTEGER).', '.
			       $this->db_add_param($this->mitarbeiter_uid).', '.
			       $this->db_add_param($this->hauptberufcode, FHC_INTEGER).', '.
			       $hauptberuflich.', '.
			       $this->db_add_param($this->habilitation, FHC_BOOLEAN).', '.
			       $this->db_add_param($this->beginn).', '.
			       $this->db_add_param($this->ende).', '.
			       $this->db_add_param($this->vertragsstunden).', '.
			       $this->db_add_param($this->updateamum).', '.
			       $this->db_add_param($this->updatevon).', '.
			       $this->db_add_param($this->insertamum).', '.
			       $this->db_add_param($this->insertvon).', '.
				   $this->db_add_param($this->dv_art).','.
				   $this->db_add_param($this->inkludierte_lehre).','.
				   $zeitaufzeichnungspflichtig. ');';

		}
		else
		{
			//Bestehenden Datensatz aktualisieren
			$qry= "UPDATE bis.tbl_bisverwendung SET".
				  " ba1code=".$this->db_add_param($this->ba1code, FHC_INTEGER).",".
				  " ba2code=".$this->db_add_param($this->ba2code, FHC_INTEGER).",".
				  " beschausmasscode=".$this->db_add_param($this->beschausmasscode, FHC_INTEGER).",".
				  " verwendung_code=".$this->db_add_param($this->verwendung_code, FHC_INTEGER).",".
				  " mitarbeiter_uid=".$this->db_add_param($this->mitarbeiter_uid).",".
				  " hauptberufcode=".$this->db_add_param($this->hauptberufcode, FHC_INTEGER).",".
				  " hauptberuflich=".$hauptberuflich.",".
				  " habilitation=".$this->db_add_param($this->habilitation, FHC_BOOLEAN).",".
				  " beginn=".$this->db_add_param($this->beginn).",".
				  " ende=".$this->db_add_param($this->ende).",".
				  " vertragsstunden=".$this->db_add_param($this->vertragsstunden).",".
				  " updateamum=".$this->db_add_param($this->updateamum).",".
				  " updatevon=".$this->db_add_param($this->updatevon).",".
				  " insertamum=".$this->db_add_param($this->insertamum).",".
				  " insertvon=".$this->db_add_param($this->insertvon).",".
				  " dv_art=".$this->db_add_param($this->dv_art).",".
				  " inkludierte_lehre=".$this->db_add_param($this->inkludierte_lehre).",".
				  " zeitaufzeichnungspflichtig=". $zeitaufzeichnungspflichtig.
				  " WHERE bisverwendung_id=".$this->db_add_param($this->bisverwendung_id, FHC_INTEGER);
		}

		if($this->db_query($qry))
		{
			if($new)
			{
				$qry = "SELECT currval('bis.tbl_bisverwendung_bisverwendung_id_seq') as id;";
				if($this->db_query($qry))
				{
					if($row = $this->db_fetch_object())
					{
						$this->bisverwendung_id = $row->id;
						$this->db_query('COMMIT;');
						return true;
					}
					else
					{
						$this->errormsg = 'Fehler beim Auslesen der Sequence';
						$this->db_query('ROLLBACK;');
						return false;
					}
				}
				else
				{
					$this->errormsg = 'Fehler beim Auslesen der Sequence';
					$this->db_query('ROLLBACK;');
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
	 * Laedt alle Verwendungen eines Mitarbeiters
	 * @param $uid UID des Mitarbeiters
	 * @return true wenn ok, false wenn Fehler
	 */
	public function getVerwendung($uid)
	{
		//laden des Datensatzes
		$qry = "SELECT
					*
				FROM
					bis.tbl_beschaeftigungsart1, bis.tbl_beschaeftigungsart2,
					bis.tbl_beschaeftigungsausmass, bis.tbl_verwendung, bis.tbl_bisverwendung
					LEFT JOIN bis.tbl_hauptberuf USING(hauptberufcode)
				WHERE
					tbl_bisverwendung.ba1code=tbl_beschaeftigungsart1.ba1code AND
					tbl_bisverwendung.ba2code=tbl_beschaeftigungsart2.ba2code AND
					tbl_bisverwendung.beschausmasscode=tbl_beschaeftigungsausmass.beschausmasscode AND
					tbl_bisverwendung.verwendung_code=tbl_verwendung.verwendung_code AND
					mitarbeiter_uid=".$this->db_add_param($uid)." ORDER BY beginn;";

		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$obj = new bisverwendung();

				$obj->bisverwendung_id = $row->bisverwendung_id;
				$obj->ba1code = $row->ba1code;
				$obj->ba2code = $row->ba2code;
				$obj->beschausmasscode = $row->beschausmasscode;
				$obj->verwendung_code = $row->verwendung_code;
				$obj->mitarbeiter_uid = $row->mitarbeiter_uid;
				$obj->hauptberufcode = $row->hauptberufcode;
                $obj->hauptberuflich = $this->db_parse_bool($row->hauptberuflich);
                $obj->habilitation = $this->db_parse_bool($row->habilitation);
				$obj->beginn = $row->beginn;
				$obj->ende = $row->ende;
				$obj->updatevon = $row->updatevon;
				$obj->updateamum = $row->updateamum;
				$obj->insertamum = $row->insertamum;
				$obj->insertvon = $row->insertvon;
				$obj->ext_id = $row->ext_id;
				$obj->ba1bez = $row->ba1kurzbz;
				$obj->ba2bez = $row->ba2bez;
				$obj->beschausmass = $row->beschausmassbez;
				$obj->verwendung = $row->verwendungbez;
				$obj->hauptberuf = $row->bezeichnung;
				$obj->vertragsstunden = $row->vertragsstunden;
				$obj->dv_art = $row->dv_art;
				$obj->inkludierte_lehre = $row->inkludierte_lehre;
				$obj->zeitaufzeichnungspflichtig = $this->db_parse_bool($row->zeitaufzeichnungspflichtig);

				$this->result[] = $obj;
			}
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler bei der Datenbankabfrage';
			return false;
		}
	}

	/**
	 * Laedt alle Verwendungen eines Mitarbeiters deren Datumsbereich das Datum einschliesst
	 * @param $uid UID des Mitarbeiters
	 * @param $monat Monat in dem die Verwendung liegen soll
	 * @return true wenn ok, false wenn Fehler
	 */
	public function getVerwendungDatum($uid, $datum)
	{
		$datum_obj = new datum();
		//laden des Datensatzes
		$qry = "SELECT
					*
				FROM
					bis.tbl_bisverwendung
				WHERE
					mitarbeiter_uid=".$this->db_add_param($uid)."
					AND (beginn<=".$this->db_add_param($datum_obj->formatDatum($datum,'Y-m-d'))." OR beginn is null)
					AND (ende>=".$this->db_add_param($datum_obj->formatDatum($datum,'Y-m-d'))." OR ende is null)
				ORDER BY ende desc;";

		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$obj = new bisverwendung();

				$obj->bisverwendung_id = $row->bisverwendung_id;
				$obj->ba1code = $row->ba1code;
				$obj->ba2code = $row->ba2code;
				$obj->beschausmasscode = $row->beschausmasscode;
				$obj->verwendung_code = $row->verwendung_code;
				$obj->mitarbeiter_uid = $row->mitarbeiter_uid;
				$obj->hauptberufcode = $row->hauptberufcode;
				$obj->hauptberuflich = $this->db_parse_bool($row->hauptberuflich);
				$obj->habilitation = $this->db_parse_bool($row->habilitation);
				$obj->beginn = $row->beginn;
				$obj->ende = $row->ende;
				$obj->updatevon = $row->updatevon;
				$obj->updateamum = $row->updateamum;
				$obj->insertamum = $row->insertamum;
				$obj->insertvon = $row->insertvon;
				$obj->vertragsstunden = $row->vertragsstunden;
				$obj->dv_art = $row->dv_art;
				$obj->inkludierte_lehre = $row->inkludierte_lehre;
				$obj->zeitaufzeichnungspflichtig = $this->db_parse_bool($row->zeitaufzeichnungspflichtig);

				$this->result[] = $obj;
			}
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler bei der Datenbankabfrage';
			return false;
		}
	}

	/**
	 * Laedt alle Verwendungen eines Mitarbeiters deren Datumsbereich das Monat einschliesst
	 * @param $uid UID des Mitarbeiters
	 * @param $datum Monat in dem die Verwendung liegen soll
	 * @return true wenn ok, false wenn Fehler
	 */
	public function getVerwendungDatumMonat($uid, $datum)
	{
		$datum_obj = new datum();

		$qry = "SELECT
					*
				FROM
					bis.tbl_bisverwendung
				WHERE
					 mitarbeiter_uid=".$this->db_add_param($uid)."
					 AND ((beginn<=".$this->db_add_param($datum_obj->formatDatum($datum,'Y-m-d'))." OR beginn is null)
					 AND (ende>=".$this->db_add_param($datum_obj->formatDatum($datum,'Y-m-d'))." OR ende is null)
					 or  (EXTRACT(MONTH FROM Date ".$this->db_add_param($datum_obj->formatDatum($datum,'Y-m-d')).") = EXTRACT(MONTH FROM ende) and EXTRACT(Year FROM Date ".$this->db_add_param($datum_obj->formatDatum($datum,'Y-m-d')).") = EXTRACT(Year FROM ende)))
				ORDER BY ende desc;";

		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$obj = new bisverwendung();

				$obj->bisverwendung_id = $row->bisverwendung_id;
				$obj->ba1code = $row->ba1code;
				$obj->ba2code = $row->ba2code;
				$obj->beschausmasscode = $row->beschausmasscode;
				$obj->verwendung_code = $row->verwendung_code;
				$obj->mitarbeiter_uid = $row->mitarbeiter_uid;
				$obj->hauptberufcode = $row->hauptberufcode;
				$obj->hauptberuflich = $this->db_parse_bool($row->hauptberuflich);
				$obj->habilitation = $this->db_parse_bool($row->habilitation);
				$obj->beginn = $row->beginn;
				$obj->ende = $row->ende;
				$obj->updatevon = $row->updatevon;
				$obj->updateamum = $row->updateamum;
				$obj->insertamum = $row->insertamum;
				$obj->insertvon = $row->insertvon;
				$obj->vertragsstunden = $row->vertragsstunden;
				$obj->dv_art = $row->dv_art;
				$obj->inkludierte_lehre = $row->inkludierte_lehre;
				$obj->zeitaufzeichnungspflichtig = $this->db_parse_bool($row->zeitaufzeichnungspflichtig);

				$this->result[] = $obj;
			}
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler bei der Datenbankabfrage';
			return false;
		}
	}

	/**
	 * Laedt die Letzte eingetragene Verwendungen eines Mitarbeiters
	 * @param $uid UID des Mitarbeiters
	 * @return true wenn ok, false wenn Fehler
	 */
	public function getLastVerwendung($uid)
	{
		//laden des Datensatzes
		$qry = "SELECT
					*
				FROM
					bis.tbl_bisverwendung
				WHERE
					mitarbeiter_uid=".$this->db_add_param($uid)."
				ORDER BY ende DESC NULLS LAST,beginn DESC NULLS LAST LIMIT 1;";

		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				$this->bisverwendung_id = $row->bisverwendung_id;
				$this->ba1code = $row->ba1code;
				$this->ba2code = $row->ba2code;
				$this->beschausmasscode = $row->beschausmasscode;
				$this->verwendung_code = $row->verwendung_code;
				$this->mitarbeiter_uid = $row->mitarbeiter_uid;
				$this->hauptberufcode = $row->hauptberufcode;
				$this->hauptberuflich = $this->db_parse_bool($row->hauptberuflich);
				$this->habilitation = $this->db_parse_bool($row->habilitation);
				$this->beginn = $row->beginn;
				$this->ende = $row->ende;
				$this->updatevon = $row->updatevon;
				$this->updateamum = $row->updateamum;
				$this->insertamum = $row->insertamum;
				$this->insertvon = $row->insertvon;
				$this->vertragsstunden = $row->vertragsstunden;
				$this->dv_art = $row->dv_art;
				$this->inkludierte_lehre = $row->inkludierte_lehre;
				$this->zeitaufzeichnungspflichtig = $this->db_parse_bool($row->zeitaufzeichnungspflichtig);
			}
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler bei der Datenbankabfrage';
			return false;
		}
	}

	/**
	 * Laedt die Letzte (aktuellste) Verwendungen eines Mitarbeiters
	 * @param $uid UID des Mitarbeiters
	 * @return true wenn ok, false wenn Fehler
	 */
	public function getLastAktVerwendung($uid)
	{
		//laden des Datensatzes
		$qry = "SELECT
					*
				FROM
					bis.tbl_bisverwendung
				WHERE
					mitarbeiter_uid=".$this->db_add_param($uid)."
				AND
					(beginn<=now() OR beginn IS NULL)
				AND
					(ende>=now() OR ende IS NULL)
				ORDER BY ende DESC NULLS LAST,beginn DESC NULLS LAST LIMIT 1;";

		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				$this->bisverwendung_id = $row->bisverwendung_id;
				$this->ba1code = $row->ba1code;
				$this->ba2code = $row->ba2code;
				$this->beschausmasscode = $row->beschausmasscode;
				$this->verwendung_code = $row->verwendung_code;
				$this->mitarbeiter_uid = $row->mitarbeiter_uid;
				$this->hauptberufcode = $row->hauptberufcode;
				$this->hauptberuflich = $this->db_parse_bool($row->hauptberuflich);
				$this->habilitation = $this->db_parse_bool($row->habilitation);
				$this->beginn = $row->beginn;
				$this->ende = $row->ende;
				$this->updatevon = $row->updatevon;
				$this->updateamum = $row->updateamum;
				$this->insertamum = $row->insertamum;
				$this->insertvon = $row->insertvon;
				$this->vertragsstunden = $row->vertragsstunden;
				$this->dv_art = $row->dv_art;
				$this->inkludierte_lehre = $row->inkludierte_lehre;
				$this->zeitaufzeichnungspflichtig = $this->db_parse_bool($row->zeitaufzeichnungspflichtig);
			}
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler bei der Datenbankabfrage';
			return false;
		}
	}

	/**
	 * Laedt alle Verwendungen eines Mitarbeiters die in einen Datumsbereich fallen
	 * @param $uid UID des Mitarbeiters
	 * @param $von
	 * @param $bis
	 * @return true wenn ok, false wenn Fehler
	 */
	public function getVerwendungRange($uid, $von, $bis)
	{
		$datum_obj = new datum();
		//laden des Datensatzes
		$qry = "
		SELECT
			*
		FROM
			bis.tbl_bisverwendung
		WHERE
			mitarbeiter_uid=".$this->db_add_param($uid)."
			AND
			(
				".$this->db_add_param($datum_obj->formatDatum($von,'Y-m-d'))." BETWEEN COALESCE(beginn,'1970-01-01') AND COALESCE(ende,'2999-12-31')
				OR
				".$this->db_add_param($datum_obj->formatDatum($bis,'Y-m-d'))." BETWEEN COALESCE(beginn,'1970-01-01') AND COALESCE(ende,'2999-12-31')
			)
		ORDER BY ende desc;";

		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{

				$obj = new bisverwendung();

				$obj->bisverwendung_id = $row->bisverwendung_id;
				$obj->ba1code = $row->ba1code;
				$obj->ba2code = $row->ba2code;
				$obj->beschausmasscode = $row->beschausmasscode;
				$obj->verwendung_code = $row->verwendung_code;
				$obj->mitarbeiter_uid = $row->mitarbeiter_uid;
				$obj->hauptberufcode = $row->hauptberufcode;
				$obj->hauptberuflich = $this->db_parse_bool($row->hauptberuflich);
				$obj->habilitation = $this->db_parse_bool($row->habilitation);
				$obj->beginn = $row->beginn;
				$obj->ende = $row->ende;
				$obj->updatevon = $row->updatevon;
				$obj->updateamum = $row->updateamum;
				$obj->insertamum = $row->insertamum;
				$obj->insertvon = $row->insertvon;
				$obj->vertragsstunden = $row->vertragsstunden;
				$obj->dv_art = $row->dv_art;
				$obj->inkludierte_lehre = $row->inkludierte_lehre;
				$obj->zeitaufzeichnungspflichtig = $this->db_parse_bool($row->zeitaufzeichnungspflichtig);

				$this->result[] = $obj;
			}
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler bei der Datenbankabfrage';
			return false;
		}
	}

	/*
	 * Prueft, ob Mitarbeiter habilitiert ist
	 * @param $uid UID des Mitarbeiters
	 * @return bool
	 */
	public function isHabilitiert($uid)
	{
		$qry = '
			SELECT
				*
			FROM
				bis.tbl_bisverwendung
			WHERE
				mitarbeiter_uid = '. $this->db_add_param($uid). '
  			AND
  				habilitation = true;
		';

		if ($this->db_query($qry))
		{
			return $this->db_num_rows() > 0;
		}
	}

	/**
	 * Holt alle Verwendungen eines Mitarbeiters innerhalb des BIS Meldungszeitraums
	 * @param $uid	UID des Mitarbeiters
	 * @param $stichtag
	 * @return bool
	 */
	public function getVerwendungenBISMeldung($uid, $stichtag)
	{
		$datetime = new DateTime($stichtag);
		$stichtag = $datetime->format('Y-m-d');
		$bismeldung_jahr = $datetime->format('Y');

		$qry = '
				SELECT
					*,
				CASE
					WHEN (beginn is null OR beginn < make_date('. $this->db_add_param($bismeldung_jahr). '::INTEGER, 1, 1))
						THEN make_date('. $this->db_add_param($bismeldung_jahr). '::INTEGER, 1, 1)
					ELSE beginn
					END as beginn_imBISMeldungsJahr,
				CASE
					WHEN (ende is null OR ende > make_date('. $this->db_add_param($bismeldung_jahr). '::INTEGER, 12, 31))
						THEN make_date('. $this->db_add_param($bismeldung_jahr). '::INTEGER, 12, 31)
					ELSE ende
					END as ende_imBISMeldungsJahr
				FROM
					bis.tbl_bisverwendung
					JOIN bis.tbl_beschaeftigungsart1 USING (ba1code)
				WHERE
					mitarbeiter_uid = '. $this->db_add_param($uid).'
					AND (beginn <= '. $this->db_add_param($stichtag).' OR beginn is null)
					AND (ende >= make_date('. $this->db_add_param($bismeldung_jahr). '::INTEGER, 1, 1) OR ende is null)
				ORDER BY ende
		';

		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$obj = new StdClass();

				$obj->bisverwendung_id = $row->bisverwendung_id;
				$obj->mitarbeiter_uid = $row->mitarbeiter_uid;
				$obj->vertragsstunden = $row->vertragsstunden;
				$obj->ba1code = $row->ba1code_bis;
				$obj->ba2code = $row->ba2code;
				$obj->verwendung_code = $row->verwendung_code;
				$obj->beschausmasscode = $row->beschausmasscode;
				$obj->hauptberufcode = $row->hauptberufcode;
				$obj->hauptberuflich = $this->db_parse_bool($row->hauptberuflich);
				$obj->beginn = $row->beginn;
				$obj->ende = $row->ende;
				$obj->beginn_imBISMeldungsJahr = $row->beginn_imbismeldungsjahr;
				$obj->ende_imBISMeldungsJahr = $row->ende_imbismeldungsjahr;

				$this->result[] = $obj;
			}
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler bei der Datenbankabfrage';
			return false;
		}
	}

	/**
	 * Laedt die vorhandenen Verwendungen
	 */
	public function getVerwendungCodex()
	{
		$qry = "SELECT * FROM bis.tbl_verwendung ORDER BY verwendung_code";

		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$obj = new StdClass();

				$obj->verwendung_code = $row->verwendung_code;
				$obj->verwendungbez = $row->verwendungbez;

				$this->result[] = $obj;
			}
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler bei der Datenbankabfrage';
			return false;
		}
	}
}
?>
