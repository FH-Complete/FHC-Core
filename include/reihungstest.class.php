<?php
/* Copyright (C) 2007 Technikum-Wien
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
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>
 *          Manfred Kindl		< manfred.kindl@technikum-wien.at >
 *          Cristina Hainberger <hainberg@technikum-wien.at>
 */
/**
 * Klasse Reihungstest
 * @create 10-01-2007
 */
require_once(dirname(__FILE__).'/basis_db.class.php');

class reihungstest extends basis_db
{
	public $new = true;            //  boolean
	public $done = false;    //  boolean
	public $result = array();

	//Tabellenspalten
	public $reihungstest_id;//  integer
	public $studiengang_kz;    //  integer
	public $ort_kurzbz;        //  string
	public $anmerkung;        //  string
	public $datum;            //  date
	public $uhrzeit;        //  time without time zone
	public $ext_id;            //  integer
	public $insertamum;        //  timestamp
	public $insertvon;        //  bigint
	public $updateamum;        //  timestamp
	public $updatevon;        //  bigint
	public $freigeschaltet = false;    //  boolean
	public $oeffentlich = false;    //  boolean
	public $max_teilnehmer;    //  integer
	public $studiensemester_kurzbz; //string
	public $stufe;            //smallint
	public $anmeldefrist;    //date
	public $aufnahmegruppe_kurzbz; // varchar(32)

	public $rt_person_id; // integer
	public $rt_id;  // integer
	public $person_id; // integer
	public $studienplan_id; // integer
	public $anmeldedatum; // date
	public $teilgenommen; // boolean
	public $punkte; // numeric
	
	public $zugangs_ueberpruefung = false; //boolean
	public $zugangscode; //smallint


	/**
	 * Konstruktor
	 * @param int $reihungstest_id ID der Adresse die geladen werden soll (Default=null).
	 */
	public function __construct($reihungstest_id = null)
	{
		parent::__construct();

		if (!is_null($reihungstest_id))
		{
			$this->load($reihungstest_id);
		}
	}

	/**
	 * Laedt den Reihungstest mit der ID $reihungstest_id
	 * @param int $reihungstest_id ID des zu ladenden Reihungstests.
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function load($reihungstest_id)
	{
		if (!is_numeric($reihungstest_id))
		{
			$this->errormsg = 'Reihungstest_id ist ungueltig';
			return false;
		}

		$qry = "SELECT * FROM public.tbl_reihungstest
				WHERE reihungstest_id=".$this->db_add_param($reihungstest_id, FHC_INTEGER, false);

		if ($this->db_query($qry))
		{
			if ($row = $this->db_fetch_object())
			{
				$this->reihungstest_id = $row->reihungstest_id;
				$this->studiengang_kz = $row->studiengang_kz;
				$this->ort_kurzbz = $row->ort_kurzbz;
				$this->anmerkung = $row->anmerkung;
				$this->datum = $row->datum;
				$this->uhrzeit = $row->uhrzeit;
				$this->ext_id = $row->ext_id;
				$this->insertamum = $row->insertamum;
				$this->insertvon = $row->insertvon;
				$this->updateamum = $row->updateamum;
				$this->updatevon = $row->updatevon;
				$this->max_teilnehmer = $row->max_teilnehmer;
				$this->oeffentlich = $this->db_parse_bool($row->oeffentlich);
				$this->freigeschaltet = $this->db_parse_bool($row->freigeschaltet);
				$this->studiensemester_kurzbz = $row->studiensemester_kurzbz;
				$this->stufe = $row->stufe;
				$this->anmeldefrist = $row->anmeldefrist;
				$this->aufnahmegruppe_kurzbz = $row->aufnahmegruppe_kurzbz;
				$this->zugangs_ueberpruefung = $this->db_parse_bool($row->zugangs_ueberpruefung);
				$this->zugangscode = $row->zugangscode;

				return true;
			}
			else
			{
				$this->errormsg = 'Reihungstest existiert nicht';
				return false;
			}
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Reihungstests';
			return false;
		}
	}

	/**
	 * Liefert alle Reihungstests
	 * wenn ein Datum uebergeben wird, dann werden alle Reihungstests ab diesem
	 * Datum zurueckgeliefert
	 * @param date $datum Wenn das Datum uebergeben wird, dann werden nur RT von diesem Tag geliefert (optional).
	 * @return boolean true wenn erfolgreich, false im Fehlerfall
	 */
	public function getAll($datum = null)
	{
		$qry = "SELECT * FROM public.tbl_reihungstest ";
		if ($datum != null)
		{
			$qry .= " WHERE datum>=".$this->db_add_param($datum);
		}
		$qry .= " ORDER BY datum DESC, uhrzeit";

		if ($this->db_query($qry))
		{
			while ($row = $this->db_fetch_object())
			{
				$obj = new reihungstest();

				$obj->reihungstest_id = $row->reihungstest_id;
				$obj->studiengang_kz = $row->studiengang_kz;
				$obj->ort_kurzbz = $row->ort_kurzbz;
				$obj->anmerkung = $row->anmerkung;
				$obj->datum = $row->datum;
				$obj->uhrzeit = $row->uhrzeit;
				$obj->ext_id = $row->ext_id;
				$obj->insertamum = $row->insertamum;
				$obj->insertvon = $row->insertvon;
				$obj->updateamum = $row->updateamum;
				$obj->updatevon = $row->updatevon;
				$obj->max_teilnehmer = $row->max_teilnehmer;
				$obj->oeffentlich = $this->db_parse_bool($row->oeffentlich);
				$obj->freigeschaltet = $this->db_parse_bool($row->freigeschaltet);
				$obj->studiensemester_kurzbz = $row->studiensemester_kurzbz;
				$obj->stufe = $row->stufe;
				$obj->anmeldefrist = $row->anmeldefrist;
				$this->aufnahmegruppe_kurzbz = $row->aufnahmegruppe_kurzbz;

				$this->result[] = $obj;
			}
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Reihungstests';
			return false;
		}
	}

	/**
	 * Prueft die Variablen auf Gueltigkeit
	 * @return true wenn ok, false im Fehlerfall
	 */
	private function __validate()
	{
		//Zahlenfelder pruefen
		if (!is_numeric($this->studiengang_kz))
		{
			$this->errormsg = 'studiengang_kz enthaelt ungueltige Zeichen';
			return false;
		}
		//Gesamtlaenge pruefen
		if (mb_strlen($this->ort_kurzbz) > 32)
		{
			$this->errormsg = 'Ort_kurzbz darf nicht länger als 16 Zeichen sein';
			return false;
		}
		if (mb_strlen($this->anmerkung) > 64)
		{
			$this->errormsg = 'Anmerkung darf nicht länger als 64 Zeichen sein';
			return false;
		}

		$this->errormsg = '';
		return true;
	}

	/**
	 * Speichert den aktuellen Datensatz in die Datenbank
	 * Wenn $neu auf true gesetzt ist wird ein neuer Datensatz angelegt
	 * andernfalls wird der Datensatz mit der ID in $reihungstest_id aktualisiert
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function save()
	{
		if (!$this->__validate())
		{
			return false;
		}

		if ($this->new)
		{
			//Neuen Datensatz einfuegen

			$qry = 'BEGIN; INSERT INTO public.tbl_reihungstest (studiengang_kz, ort_kurzbz, anmerkung, datum, uhrzeit,
				insertamum, insertvon, updateamum, updatevon, max_teilnehmer, oeffentlich, freigeschaltet,
				studiensemester_kurzbz, stufe, anmeldefrist, aufnahmegruppe_kurzbz, zugangs_ueberpruefung, zugangscode) VALUES('.
				$this->db_add_param($this->studiengang_kz, FHC_INTEGER).', '.
				$this->db_add_param($this->ort_kurzbz).', '.
				$this->db_add_param($this->anmerkung).', '.
				$this->db_add_param($this->datum).', '.
				$this->db_add_param($this->uhrzeit).', now(), '.
				$this->db_add_param($this->insertvon).', now(), '.
				$this->db_add_param($this->updatevon).','.
				$this->db_add_param($this->max_teilnehmer).','.
				$this->db_add_param($this->oeffentlich, FHC_BOOLEAN).','.
				$this->db_add_param($this->freigeschaltet, FHC_BOOLEAN).','.
				$this->db_add_param($this->studiensemester_kurzbz).','.
				$this->db_add_param($this->stufe, FHC_INTEGER).','.
				$this->db_add_param($this->anmeldefrist).','.
				$this->db_add_param($this->aufnahmegruppe_kurzbz). ',' .
				$this->db_add_param($this->zugangs_ueberpruefung, FHC_BOOLEAN).','.
				$this->db_add_param($this->zugangscode) . ');';
		}
		else
		{
			$qry = 'UPDATE public.tbl_reihungstest SET '.
				'studiengang_kz='.$this->db_add_param($this->studiengang_kz, FHC_INTEGER).', '.
				'ort_kurzbz='.$this->db_add_param($this->ort_kurzbz).', '.
				'anmerkung='.$this->db_add_param($this->anmerkung).', '.
				'datum='.$this->db_add_param($this->datum).', '.
				'uhrzeit='.$this->db_add_param($this->uhrzeit).', '.
				'updateamum= now(), '.
				'updatevon='.$this->db_add_param($this->updatevon).', '.
				'max_teilnehmer='.$this->db_add_param($this->max_teilnehmer).', '.
				'oeffentlich='.$this->db_add_param($this->oeffentlich, FHC_BOOLEAN).', '.
				'freigeschaltet='.$this->db_add_param($this->freigeschaltet, FHC_BOOLEAN).', '.
				'studiensemester_kurzbz='.$this->db_add_param($this->studiensemester_kurzbz).', '.
				'stufe='.$this->db_add_param($this->stufe, FHC_INTEGER).', '.
				'anmeldefrist='.$this->db_add_param($this->anmeldefrist).', '.
				'aufnahmegruppe_kurzbz='.$this->db_add_param($this->aufnahmegruppe_kurzbz).', '.
				'zugangs_ueberpruefung='.$this->db_add_param($this->zugangs_ueberpruefung, FHC_BOOLEAN).', '.
				'zugangscode='.$this->db_add_param($this->zugangscode).' '.
				'WHERE reihungstest_id='.$this->db_add_param($this->reihungstest_id, FHC_INTEGER, false).';';
		}

		if ($this->db_query($qry))
		{
			if ($this->new)
			{
				$qry = "SELECT currval('public.tbl_reihungstest_reihungstest_id_seq') as id";
				if ($this->db_query($qry))
				{
					if ($row = $this->db_fetch_object())
					{
						$this->reihungstest_id = $row->id;
						$this->db_query('COMMIT');
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
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern der Daten';
			return false;
		}
	}

	/**
	 * Liefert die Reihungstests eines Studienganges
	 *
	 * @param int $studiengang_kz Kennzahl des Studiengangs.
	 * @param string $order Sortierung (optional).
	 * @param string $studiensemester_kurzbz Studiensemester Kurzbezeichnung.
	 * @return true wenn ok, sonst false
	 */
	public function getReihungstest($studiengang_kz, $order = null, $studiensemester_kurzbz = null)
	{
		$qry = "SELECT * FROM public.tbl_reihungstest WHERE 1=1 ";

		if (is_numeric($studiengang_kz) && $studiengang_kz != '')
		{
			$qry .= " AND studiengang_kz=".$this->db_add_param($studiengang_kz, FHC_INTEGER, false);
		}
		if ($studiensemester_kurzbz != null)
		{
			$qry .= " AND studiensemester_kurzbz=".$this->db_add_param($studiensemester_kurzbz, FHC_STRING, false);
		}

		if ($order != null)
		{
			$qry .= " ORDER BY ".$order;
		}

		$qry .= ";";

		if ($this->db_query($qry))
		{
			while ($row = $this->db_fetch_object())
			{
				$obj = new reihungstest();

				$obj->reihungstest_id = $row->reihungstest_id;
				$obj->studiengang_kz = $row->studiengang_kz;
				$obj->ort_kurzbz = $row->ort_kurzbz;
				$obj->anmerkung = $row->anmerkung;
				$obj->datum = $row->datum;
				$obj->uhrzeit = $row->uhrzeit;
				$obj->ext_id = $row->ext_id;
				$obj->insertamum = $row->insertamum;
				$obj->insertvon = $row->insertvon;
				$obj->updateamum = $row->updateamum;
				$obj->updatevon = $row->updatevon;
				$obj->max_teilnehmer = $row->max_teilnehmer;
				$obj->oeffentlich = $this->db_parse_bool($row->oeffentlich);
				$obj->freigeschaltet = $this->db_parse_bool($row->freigeschaltet);
				$obj->studiensemester_kurzbz = $row->studiensemester_kurzbz;
				$obj->stufe = $row->stufe;
				$obj->anmeldefrist = $row->anmeldefrist;
				$obj->aufnahmegruppe_kurzbz = $row->aufnahmegruppe_kurzbz;

				$this->result[] = $obj;
			}
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Reihungstests';
			return false;
		}
	}

	/**
	 * Liefert die Reihungstests der Zukunft und einer bestimmten ID
	 * Und sortiert diese so, dass die des uebergebenen Studienganges zuerst geliefert werden
	 * @param int $include_id ReihungstestID die zusaetzlich enthalten sein soll.
	 * @param int $studiengang_kz Kennzahl des Studiengangs.
	 * @return true wenn ok, sonst false
	 */
	public function getZukuenftige($include_id, $studiengang_kz)
	{
		$qry = "
		SELECT *,
			(
				SELECT count(*) FROM public.tbl_prestudent
				WHERE reihungstest_id=a.reihungstest_id
			) as angemeldete_teilnehmer
		FROM
			(
			SELECT *, '1' as sortierung,
				(
					SELECT upper(typ || kurzbz) FROM public.tbl_studiengang
					WHERE studiengang_kz=tbl_reihungstest.studiengang_kz
				) as stg
			FROM
				public.tbl_reihungstest
			WHERE
				datum>=now()-'1 days'::interval AND studiengang_kz=".$this->db_add_param($studiengang_kz)."
			UNION
			SELECT *, '2' as sortierung,
				(
					SELECT upper(typ || kurzbz) FROM public.tbl_studiengang
					WHERE studiengang_kz=tbl_reihungstest.studiengang_kz
				) as stg
			FROM
				public.tbl_reihungstest
			WHERE datum>=now()-'1 days'::interval AND studiengang_kz!=".$this->db_add_param($studiengang_kz)."
			UNION
			SELECT *, '0' as sortierung,
				(
					SELECT upper(typ || kurzbz) FROM public.tbl_studiengang
					WHERE studiengang_kz=tbl_reihungstest.studiengang_kz
				) as stg
			FROM
				public.tbl_reihungstest
			WHERE reihungstest_id=".$this->db_add_param($include_id)."
			ORDER BY sortierung, stg, datum
			) a";

		if ($this->db_query($qry))
		{
			while ($row = $this->db_fetch_object())
			{
				$obj = new reihungstest();

				$obj->reihungstest_id = $row->reihungstest_id;
				$obj->studiengang_kz = $row->studiengang_kz;
				$obj->ort_kurzbz = $row->ort_kurzbz;
				$obj->anmerkung = $row->anmerkung;
				$obj->datum = $row->datum;
				$obj->uhrzeit = $row->uhrzeit;
				$obj->ext_id = $row->ext_id;
				$obj->insertamum = $row->insertamum;
				$obj->insertvon = $row->insertvon;
				$obj->updateamum = $row->updateamum;
				$obj->updatevon = $row->updatevon;
				$obj->max_teilnehmer = $row->max_teilnehmer;
				$obj->oeffentlich = $this->db_parse_bool($row->oeffentlich);
				$obj->freigeschaltet = $this->db_parse_bool($row->freigeschaltet);
				$obj->studiensemester_kurzbz = $row->studiensemester_kurzbz;
				$obj->angemeldete_teilnehmer = $row->angemeldete_teilnehmer;
				$obj->stufe = $row->stufe;
				$obj->anmeldefrist = $row->anmeldefrist;
				$obj->aufnahmegruppe_kurzbz = $row->aufnahmegruppe_kurzbz;

				$this->result[] = $obj;
			}
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Reihungstests';
			return false;
		}
	}

	/**
	 * Liefert die Reihungstest eines studiengangs die in der Zukunft liegen
	 * @param int $stg Kennzahl des Studiengangs.
	 * @return boolean true wenn ok, false im Fehlerfall
	 */
	public function getStgZukuenftige($stg)
	{
		$qry = "SELECT * ".
			"FROM public.tbl_reihungstest ".
			"WHERE studiengang_kz = ".$this->db_add_param($stg, FHC_INTEGER)." ".
			"AND datum>=now()-'1 days'::interval ".
			"AND oeffentlich;";

		if ($result = $this->db_query($qry))
		{
			while ($row = $this->db_fetch_object($result))
			{
				$obj = new reihungstest();

				$obj->reihungstest_id = $row->reihungstest_id;
				$obj->studiengang_kz = $row->studiengang_kz;
				$obj->ort_kurzbz = $row->ort_kurzbz;
				$obj->anmerkung = $row->anmerkung;
				$obj->datum = $row->datum;
				$obj->uhrzeit = $row->uhrzeit;
				$obj->ext_id = $row->ext_id;
				$obj->insertamum = $row->insertamum;
				$obj->insertvon = $row->insertvon;
				$obj->updateamum = $row->updateamum;
				$obj->updatevon = $row->updatevon;
				$obj->max_teilnehmer = $row->max_teilnehmer;
				$obj->oeffentlich = $this->db_parse_bool($row->oeffentlich);
				$obj->freigeschaltet = $this->db_parse_bool($row->freigeschaltet);
				$obj->studiensemester_kurzbz = $row->studiensemester_kurzbz;
				$obj->stufe = $row->stufe;
				$obj->anmeldefrist = $row->anmeldefrist;
				$obj->aufnahmegruppe_kurzbz = $row->aufnahmegruppe_kurzbz;

				$this->result[] = $obj;
			}
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Laedt die Anzahl der Teilnehmer zu einem Reihungstest
	 * @param int $reihungstest_id ID des Reihungstests.
	 * @return anzahl der Teilnehmer oder false im Fehlerfall
	 */
	public function getTeilnehmerAnzahl($reihungstest_id)
	{
		$qry = 'SELECT
					count(*) AS anzahl
				FROM
					public.tbl_rt_person
				WHERE
					rt_id = '.$this->db_add_param($reihungstest_id, FHC_INTEGER);

		if ($result = $this->db_query($qry))
		{
			if ($row = $this->db_fetch_object($result))
			{
				return $row->anzahl;
			}
			else
			{
				$this->errormsg = 'Fehler beim Laden der Teilnehmer';
				return false;
			}
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Teilnehmer';
			return false;
		}
	}

	/**
	 * Laedt die Anzahl an verfügbaren Plätzen bei einem Reihungstest.
	 * Wenn max_teilnehmer gesetzt ist, wird dieser Wert zurückgegeben.
	 * Ansonsten wird die Anzahl der verfügbaren Arbeitspläte aus den zugeteilten Räumen ermittelt.
	 * Hier kann optional ein Prozentanteil für den Schwund übergeben werden, der von den verfügbaren Plätzen abgezogen wird.
	 *
	 * @param integer $reihungstest_id ID des Reihungstests.
	 * @param integer $anteilSchwund Prozentanteil für den Schwund, der herausgerechnet werden soll
	 * @return integer Anzahl der Teilnehmer oder false im Fehlerfall
	 */
	public function getVerfuegbarePlaetzeReihungstest($reihungstest_id, $anteilSchwund = null)
	{
		$qry = "SELECT (
					CASE 
						WHEN (
								SELECT max_teilnehmer
								FROM PUBLIC.tbl_reihungstest
								WHERE reihungstest_id = rt.reihungstest_id
								) IS NOT NULL
							THEN (
									SELECT max_teilnehmer
									FROM PUBLIC.tbl_reihungstest
									WHERE reihungstest_id = rt.reihungstest_id
									)
						ELSE (";
		if ($anteilSchwund != '' && is_numeric($anteilSchwund))
		{
			$qry .= "			SELECT sum(arbeitsplaetze) - (round((sum(arbeitsplaetze)::FLOAT / 100)::FLOAT * ".$anteilSchwund.")) AS arbeitsplaetze
								FROM PUBLIC.tbl_rt_ort
								JOIN PUBLIC.tbl_ort USING (ort_kurzbz)
								WHERE rt_id = rt.reihungstest_id";
		}
		else
		{
			$qry .= "	SELECT sum(arbeitsplaetze) AS arbeitsplaetze
								FROM PUBLIC.tbl_rt_ort
								JOIN PUBLIC.tbl_ort USING (ort_kurzbz)
								WHERE rt_id = rt.reihungstest_id";
		}
		$qry .= "	)
						END
					) AS anzahl_plaetze
			FROM PUBLIC.tbl_reihungstest rt WHERE reihungstest_id=".$this->db_add_param($reihungstest_id, FHC_INTEGER);

		if ($result = $this->db_query($qry))
		{
			if ($row = $this->db_fetch_object($result))
			{
				return $row->anzahl_plaetze;
			}
			else
			{
				$this->errormsg = 'Fehler beim Laden der verfuegbaren Plaetze';
				return false;
			}
		}
		else
		{
			$this->errormsg = 'Fehler beim ausfuehren der Query';
			return false;
		}
	}

	/**
	 * Loescht einen Rehungstest
	 * @param int $reihungstest_id ID des Reihungstests der geloescht werden soll.
	 * @return true wenn erfolgreich, false im Fehlerfall
	 */
	public function delete($reihungstest_id)
	{
		$qry = "DELETE FROM public.tbl_reihungstest
			WHERE reihungstest_id=".$this->db_add_param($reihungstest_id, FHC_INTEGER);

		if (!$this->db_query($qry))
		{
			$this->errormsg = 'Fehler beim Löschen der Daten';
			return false;
		}

		return true;
	}

	/**
	 * Laedt den Reihungstest-Person-Datensatz mit der ID $rt_person_id
	 * @param int $rt_person_id ID des zu ladenden Datensatzes.
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function loadReihungstestPerson($rt_person_id)
	{
		if (!is_numeric($rt_person_id))
		{
			$this->errormsg = 'rt_person_id ist ungueltig';
			return false;
		}

		$qry = "SELECT
					*
				FROM
					public.tbl_rt_person
				WHERE
					rt_person_id=".$this->db_add_param($rt_person_id, FHC_INTEGER, false);

		if ($result = $this->db_query($qry))
		{
			if ($row = $this->db_fetch_object($result))
			{
				$this->rt_person_id = $row->rt_person_id;
				$this->reihungstest_id = $row->rt_id;
				$this->person_id = $row->person_id;
				$this->studienplan_id = $row->studienplan_id;
				$this->anmeldedatum = $row->anmeldedatum;
				$this->teilgenommen = $this->db_parse_bool($row->teilgenommen);
				$this->ort_kurzbz = $row->ort_kurzbz;
				$this->punkte = $row->punkte;
				$this->insertamum = $row->insertamum;
				$this->insertvon = $row->insertvon;
				$this->updateamum = $row->updateamum;
				$this->updatevon = $row->updatevon;
				$this->new = false;
				return true;
			}
			else
			{
				$this->errormsg = 'Eintrag nicht gefunden';
				return false;
			}
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden des rt_person_id Datensatzes';
			return false;
		}
	}

	/**
	 * Laedt die Reihungstest Zuordnungen einer Person
	 * Optional kann ein Studiensemester übergeben werden, in welchem der Reihungstest liegen soll
	 * @param int $person_id ID der Person.
	 * @return boolean true wenn erfolgreich, false im Fehlerfall
	 */
	public function getReihungstestPerson($person_id, $studiensemester_kurzbz = null)
	{
		$qry = "SELECT 
					tbl_rt_person.*,
					tbl_reihungstest.studiengang_kz,
					tbl_reihungstest.anmerkung,
					tbl_reihungstest.datum,
					tbl_reihungstest.uhrzeit,
					tbl_reihungstest.ext_id,
					tbl_reihungstest.max_teilnehmer,
					tbl_reihungstest.oeffentlich,
					tbl_reihungstest.freigeschaltet,
					tbl_reihungstest.studiensemester_kurzbz,
					tbl_reihungstest.stufe,
					tbl_reihungstest.anmeldefrist,
					tbl_reihungstest.aufnahmegruppe_kurzbz,
					tbl_studiengang.typ,
					UPPER(typ::varchar(1) || kurzbz) AS stg_kuerzel,
					so.studiengangbezeichnung,
       				so.studiengangbezeichnung_englisch
				FROM
					public.tbl_rt_person
				JOIN public.tbl_reihungstest ON (rt_id=reihungstest_id)
				JOIN public.tbl_studiengang ON tbl_reihungstest.studiengang_kz = tbl_studiengang.studiengang_kz
				JOIN lehre.tbl_studienplan sp USING(studienplan_id)
				JOIN lehre.tbl_studienordnung so USING(studienordnung_id)
				WHERE
					tbl_rt_person.person_id=".$this->db_add_param($person_id);

		if ($studiensemester_kurzbz != '')
		{
			$qry .= " AND tbl_reihungstest.studiensemester_kurzbz =  ".$this->db_add_param($studiensemester_kurzbz);
		}
		$qry .= " ORDER BY datum, uhrzeit ASC";

		if ($result = $this->db_query($qry))
		{
			while ($row = $this->db_fetch_object($result))
			{
				$obj = new stdClass();

				$obj->rt_person_id = $row->rt_person_id;
				$obj->reihungstest_id = $row->rt_id;
				$obj->person_id = $row->person_id;
				$obj->studienplan_id = $row->studienplan_id;
				$obj->anmeldedatum = $row->anmeldedatum;
				$obj->teilgenommen = $this->db_parse_bool($row->teilgenommen);
				$obj->ort_kurzbz = $row->ort_kurzbz;
				$obj->punkte = $row->punkte;
				$obj->insertamum = $row->insertamum;
				$obj->insertvon = $row->insertvon;
				$obj->updateamum = $row->updateamum;
				$obj->updatevon = $row->updatevon;
				$obj->studiengang_kz = $row->studiengang_kz;
				$obj->anmerkung = $row->anmerkung;
				$obj->datum = $row->datum;
				$obj->uhrzeit = $row->uhrzeit;
				$obj->max_teilnehmer = $row->max_teilnehmer;
				$obj->oeffentlich = $this->db_parse_bool($row->oeffentlich);
				$obj->freigeschaltet = $this->db_parse_bool($row->freigeschaltet);
				$obj->studiensemester_kurzbz = $row->studiensemester_kurzbz;
				$obj->stufe = $row->stufe;
				$obj->anmeldefrist = $row->anmeldefrist;
				$obj->aufnahmegruppe_kurzbz = $row->aufnahmegruppe_kurzbz;
				$obj->typ = $row->typ;
				$obj->stg_kuerzel = $row->stg_kuerzel;
				$obj->studiengangbezeichnung = $row->studiengangbezeichnung;
				$obj->studiengangbezeichnung_englisch = $row->studiengangbezeichnung_englisch;

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
	 * Liefert die Zuordnung einer Person zu einem Reihungstest
	 * @param int $person_id ID der Person.
	 * @param int $reihungstest_id ID des Reihungstests.
	 * @param int $studienplan_id Optional. Studienplan ID
	 * @return boolean true wenn erfolgreich, false im Fehlerfall
	 */
	public function getPersonReihungstest($person_id, $reihungstest_id, $studienplan_id = null)
	{
		$qry = "SELECT
					*
				FROM
					public.tbl_rt_person
				WHERE
					tbl_rt_person.person_id=".$this->db_add_param($person_id)."
					AND rt_id=".$this->db_add_param($reihungstest_id);

		if ($studienplan_id != '')
		{
			$qry .= " AND studienplan_id = ".$this->db_add_param($studienplan_id);
		}
		if ($result = $this->db_query($qry))
		{
			if ($row = $this->db_fetch_object($result))
			{
				$this->rt_person_id = $row->rt_person_id;
				$this->reihungstest_id = $row->rt_id;
				$this->person_id = $row->person_id;
				$this->studienplan_id = $row->studienplan_id;
				$this->anmeldedatum = $row->anmeldedatum;
				$this->teilgenommen = $this->db_parse_bool($row->teilgenommen);
				$this->ort_kurzbz = $row->ort_kurzbz;
				$this->punkte = $row->punkte;
				$this->insertamum = $row->insertamum;
				$this->insertvon = $row->insertvon;
				$this->updateamum = $row->updateamum;
				$this->updatevon = $row->updatevon;

				return true;
			}
			else
			{
				$this->errormsg = 'Eintrag nicht gefunden';
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
	 * Liefert die Personen, die einem Ort des Reihungstests zugeteilt sind
	 * @param int $reihungstest_id ID des Reihungstests.
	 * @param string $ort_kurzbz Ort des Reihungstests mit der ID $reihungstest_id.
	 * @return true wenn ok, sonst false
	 */
	public function getPersonReihungstestOrt($reihungstest_id, $ort_kurzbz)
	{
		$qry = "SELECT
					*
				FROM
					public.tbl_rt_person
				WHERE
					tbl_rt_person.rt_id=".$this->db_add_param($reihungstest_id)."
					AND tbl_rt_person.ort_kurzbz=".$this->db_add_param($ort_kurzbz);
		if ($result = $this->db_query($qry))
		{
			while ($row = $this->db_fetch_object($result))
			{
				$obj = new stdClass();

				$obj->rt_person_id = $row->rt_person_id;
				$obj->reihungstest_id = $row->rt_id;
				$obj->person_id = $row->person_id;
				$obj->studienplan_id = $row->studienplan_id;
				$obj->anmeldedatum = $row->anmeldedatum;
				$obj->teilgenommen = $this->db_parse_bool($row->teilgenommen);
				$obj->ort_kurzbz = $row->ort_kurzbz;
				$obj->punkte = $row->punkte;
				$obj->insertamum = $row->insertamum;
				$obj->insertvon = $row->insertvon;
				$obj->updateamum = $row->updateamum;
				$obj->updatevon = $row->updatevon;

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
	 * Speichern die Zuordnung einer Person zu einem Reihungstest
	 * @return boolean true wenn erfolgreich, false im Fehlerfall
	 */
	public function savePersonReihungstest()
	{
		if ($this->new)
		{
			$qry = "BEGIN;INSERT INTO public.tbl_rt_person(person_id, rt_id, studienplan_id, anmeldedatum,
				teilgenommen, ort_kurzbz, punkte, insertamum, insertvon) VALUES(".
				$this->db_add_param($this->person_id, FHC_INTEGER).','.
				$this->db_add_param($this->reihungstest_id, FHC_INTEGER).','.
				$this->db_add_param($this->studienplan_id, FHC_INTEGER).','.
				$this->db_add_param($this->anmeldedatum).','.
				$this->db_add_param($this->teilgenommen, FHC_BOOLEAN).','.
				$this->db_add_param($this->ort_kurzbz).','.
				$this->db_add_param($this->punkte).','.
				$this->db_add_param($this->insertamum).','.
				$this->db_add_param($this->insertvon).');';
		}
		else
		{
			$qry = "UPDATE public.tbl_rt_person SET ".
				' rt_id = '.$this->db_add_param($this->reihungstest_id).','.
				' studienplan_id = '.$this->db_add_param($this->studienplan_id).','.
				' anmeldedatum='.$this->db_add_param($this->anmeldedatum).','.
				' teilgenommen='.$this->db_add_param($this->teilgenommen, FHC_BOOLEAN).','.
				' ort_kurzbz='.$this->db_add_param($this->ort_kurzbz).','.
				' punkte='.$this->db_add_param($this->punkte).','.
				' updateamum='.$this->db_add_param($this->updateamum).','.
				' updatevon='.$this->db_add_param($this->updatevon).' '.
				' WHERE rt_person_id='.$this->db_add_param($this->rt_person_id, FHC_INTEGER).';';
		}

		if ($this->db_query($qry))
		{
			if ($this->new)
			{
				$qry = "SELECT currval('public.tbl_rt_person_rt_person_id_seq') as id";
				if ($this->db_query($qry))
				{
					if ($row = $this->db_fetch_object())
					{
						$this->rt_person_id = $row->id;
						$this->db_query('COMMIT');
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
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern der Daten';
			return false;
		}
	}

	/**
	 * Loescht einen Person Reihungstest Eintrag
	 * @param int $rt_person_id ID der PersonReihungstestzuordnung die geloescht werden soll.
	 * @return boolean true wenn erfolgreich, false im Fehlerfall.
	 */
	public function deletePersonReihungstest($rt_person_id)
	{
		$qry = "DELETE FROM public.tbl_rt_person
			WHERE rt_person_id=".$this->db_add_param($rt_person_id, FHC_INTEGER);

		if ($this->db_query($qry))
		{
			return true;
		}
		else
		{
			$this->erromsg = 'Fehler beim Löschen der Daten';
			return false;
		}
	}

	/**
	 * Liefert die Orte, die einem Reihungstest zugeordnet sind
	 * @param int $reihungstest_id ID des Reihungstests, dessen Ort zurueckgegeben werden sollen.
	 * @return true wenn ok, sonst false
	 */
	public function getOrteReihungstest($reihungstest_id)
	{
		$qry = "SELECT
					*
				FROM
					public.tbl_rt_ort
				WHERE
					tbl_rt_ort.rt_id=".$this->db_add_param($reihungstest_id)."
				ORDER BY
					ort_kurzbz";

		if ($result = $this->db_query($qry))
		{
			while ($row = $this->db_fetch_object($result))
			{
				$obj = new stdClass();

				$obj->reihungstest_id = $row->rt_id;
				$obj->ort_kurzbz = $row->ort_kurzbz;
				$obj->uid = $row->uid;

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
	 * Speichert eine Raumzuteilung zu einem Reihungstesttermin
	 * Wenn $neu auf true gesetzt ist wird ein neuer Datensatz angelegt
	 * andernfalls wird der Datensatz mit der ID $reihungstest_id und $ort_kurzbz aktualisiert
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function saveOrtReihungstest()
	{
		if ($this->new)
		{
			$qry = "INSERT INTO public.tbl_rt_ort(rt_id, ort_kurzbz, uid) VALUES(".
				$this->db_add_param($this->reihungstest_id, FHC_INTEGER).','.
				$this->db_add_param($this->ort_kurzbz).','.
				$this->db_add_param($this->uid).');';
		}
		else
		{
			$qry = "UPDATE public.tbl_rt_ort SET ".
				' ort_kurzbz='.$this->db_add_param($this->ort_kurzbz).','.
				' uid='.$this->db_add_param($this->uid).' '.
				' WHERE rt_id='.$this->db_add_param($this->reihungstest_id, FHC_INTEGER).' AND '.
				' ort_kurzbz='.$this->db_add_param($this->ort_kurzbz);
		}

		if ($this->db_query($qry))
		{
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern der Daten';
			return false;
		}
	}

	/**
	 * Loescht einen Ort zu einem Reihungstest Eintrag
	 * @param int $reihungstest_id ID des Reihungstests.
	 * @param string $ort_kurzbz Kurzbezeichnung des Orts.
	 * @return boolean true wenn erfolgreich, false im Fehlerfall
	 */
	public function deleteOrtReihungstest($reihungstest_id, $ort_kurzbz)
	{
		$qry = "DELETE FROM public.tbl_rt_ort
			WHERE rt_id=".$this->db_add_param($reihungstest_id, FHC_INTEGER)."
			AND ort_kurzbz=".$this->db_add_param($ort_kurzbz);

		if ($this->db_query($qry))
		{
			return true;
		}
		else
		{
			$this->erromsg = 'Fehler beim Löschen der Daten';
			return false;
		}
	}

	/**
	 * Speichert eine Studienplanzuordnung zu einem Reihungstesttermin
	 * Wenn $neu auf true gesetzt ist wird ein neuer Datensatz angelegt
	 * andernfalls wird der Datensatz mit der ID $reihungstest_id und $studienplan_id aktualisiert
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function saveStudienplanReihungstest()
	{
		if ($this->new)
		{
			$qry = "INSERT INTO public.tbl_rt_studienplan(reihungstest_id, studienplan_id) VALUES(".
				$this->db_add_param($this->reihungstest_id, FHC_INTEGER).','.
				$this->db_add_param($this->studienplan_id).');';
		}
		else
		{
			$qry = "UPDATE public.tbl_rt_studienplan SET ".
				' studienplan_id='.$this->db_add_param($this->studienplan_id).' '.
				' WHERE reihungstest_id='.$this->db_add_param($this->reihungstest_id, FHC_INTEGER).' AND '.
				' studienplan_id='.$this->db_add_param($this->studienplan_id);
		}

		if ($this->db_query($qry))
		{
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern der Daten';
			return false;
		}
	}

	/**
	 * Laedt alle Studienplan IDs, die einem Reihungstest zugeordnet sind
	 * @param int $reihungstest_id ID des Reihungstests.
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function getStudienplaeneReihungstest($reihungstest_id)
	{
		$qry = "SELECT
					studienplan_id
				FROM
					public.tbl_rt_studienplan
				WHERE
					tbl_rt_studienplan.reihungstest_id=".$this->db_add_param($reihungstest_id)."
				ORDER BY
					studienplan_id";
		if ($result = $this->db_query($qry))
		{
			while ($row = $this->db_fetch_object($result))
			{
				$obj = new stdClass();

				$obj->studienplan_id = $row->studienplan_id;

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
	 * Loescht eine Studienplanzuordnung zu einem Reihungstest Eintrag
	 * @param int $reihungstest_id ID des Reihungstests.
	 * @param int $studienplan_id ID des Studienplans.
	 * @return true wenn ok, false im Fehlerfall.
	 */
	public function deleteStudienplanReihungstest($reihungstest_id, $studienplan_id)
	{
		$qry = "DELETE FROM public.tbl_rt_studienplan
			WHERE reihungstest_id=".$this->db_add_param($reihungstest_id, FHC_INTEGER)."
			AND studienplan_id=".$this->db_add_param($studienplan_id, FHC_INTEGER);

		if ($this->db_query($qry))
		{
			return true;
		}
		else
		{
			$this->erromsg = 'Fehler beim Löschen der Studienplanzuordnung';
			return false;
		}
	}

	/**
	 * Liefert alle Reihungstests zu den uebergebenen StudienplanIDs
	 *
	 * @param array $studienplan_arr Array mit StudienplanIDs zu denen die RT geladen werden sollen.
	 * @param array $include_ids Array mit ReihungstestIDs die zusaetzlich geladen werden sollen.
	 * @return boolean true wenn erfolgreich, false im Fehlerfall.
	 */
	public function getReihungstestStudienplan($studienplan_arr, $include_ids = null)
	{
		$qry = "SELECT
					distinct a.*,
					(
						SELECT count(*) FROM public.tbl_rt_person
						WHERE rt_id=a.reihungstest_id
					) as angemeldete_teilnehmer
				FROM
					public.tbl_reihungstest a
					JOIN public.tbl_rt_studienplan USING(reihungstest_id)
				WHERE studienplan_id IN(".$this->db_implode4Sql($studienplan_arr).")";

		if (!is_null($include_ids) && is_array($include_ids) && count($include_ids) > 0)
		{
			$qry .= " OR reihungstest_id in(".$this->db_implode4SQL($include_ids).")";
		}
		$qry .= "	ORDER BY a.datum DESC";

		if ($this->db_query($qry))
		{
			while ($row = $this->db_fetch_object())
			{
				$obj = new reihungstest();

				$obj->reihungstest_id = $row->reihungstest_id;
				$obj->studiengang_kz = $row->studiengang_kz;
				$obj->ort_kurzbz = $row->ort_kurzbz;
				$obj->anmerkung = $row->anmerkung;
				$obj->datum = $row->datum;
				$obj->uhrzeit = $row->uhrzeit;
				$obj->ext_id = $row->ext_id;
				$obj->insertamum = $row->insertamum;
				$obj->insertvon = $row->insertvon;
				$obj->updateamum = $row->updateamum;
				$obj->updatevon = $row->updatevon;
				$obj->max_teilnehmer = $row->max_teilnehmer;
				$obj->oeffentlich = $this->db_parse_bool($row->oeffentlich);
				$obj->freigeschaltet = $this->db_parse_bool($row->freigeschaltet);
				$obj->studiensemester_kurzbz = $row->studiensemester_kurzbz;
				$obj->stufe = $row->stufe;
				$obj->anmeldefrist = $row->anmeldefrist;
				$obj->aufnahmegruppe_kurzbz = $row->aufnahmegruppe_kurzbz;
				$obj->angemeldete_teilnehmer = $row->angemeldete_teilnehmer;

				$this->result[] = $obj;
			}
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Reihungstests';
			return false;
		}
	}

	/**
	 * Laedt Reihungstests die den Suchkriterien entsprechen
	 * @param date $datum Datum.
	 * @param string $studiensemester_kurzbz Studiensemester optional.
	 * @param int $stufe Stufe optional.
	 * @return boolean true wenn erfolgreich, false im Fehlerfall
	 */
	public function findReihungstest($datum, $studiensemester_kurzbz = null, $stufe = null)
	{
		$qry = "SELECT
					*
				FROM
					public.tbl_reihungstest
				WHERE
					datum=".$this->db_add_param($datum);

		if (!is_null($studiensemester_kurzbz))
		{
			$qry .= " AND studiensemester_kurzbz=".$this->db_add_param($studiensemester_kurzbz);
		}
		if (!is_null($stufe))
		{
			$qry .= " AND stufe=".$this->db_add_param($stufe);
		}
		$qry .= " ORDER BY reihungstest_id";

		if ($result = $this->db_query($qry))
		{
			while ($row = $this->db_fetch_object($result))
			{
				$obj = new reihungstest();

				$obj->reihungstest_id = $row->reihungstest_id;
				$obj->studiengang_kz = $row->studiengang_kz;
				$obj->ort_kurzbz = $row->ort_kurzbz;
				$obj->anmerkung = $row->anmerkung;
				$obj->datum = $row->datum;
				$obj->uhrzeit = $row->uhrzeit;
				$obj->ext_id = $row->ext_id;
				$obj->insertamum = $row->insertamum;
				$obj->insertvon = $row->insertvon;
				$obj->updateamum = $row->updateamum;
				$obj->updatevon = $row->updatevon;
				$obj->max_teilnehmer = $row->max_teilnehmer;
				$obj->oeffentlich = $this->db_parse_bool($row->oeffentlich);
				$obj->freigeschaltet = $this->db_parse_bool($row->freigeschaltet);
				$obj->studiensemester_kurzbz = $row->studiensemester_kurzbz;
				$obj->stufe = $row->stufe;
				$obj->anmeldefrist = $row->anmeldefrist;
				$obj->aufnahmegruppe_kurzbz = $row->aufnahmegruppe_kurzbz;

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
	 * Laedt die Reihungstest-Zuordnungen eines Prestudenten zu einem Datum
	 * @param int $prestudent_id ID des Prestudenten.
	 * @param date $datum Datum an dem der Reihugnstest stattfindet.
	 * @return boolean true wenn erfolgreich geladen, false im Fehlerfall
	 */
	public function getReihungstestPersonDatum($prestudent_id, $datum)
	{
		$qry = "SELECT rt_person.*
				FROM tbl_prestudent ps
				JOIN tbl_prestudentstatus pss ON ps.prestudent_id = pss.prestudent_id
				JOIN tbl_rt_person rt_person ON ps.person_id = rt_person.person_id
				JOIN tbl_reihungstest rt ON rt_person.rt_id = rt.reihungstest_id
				JOIN tbl_rt_studienplan rtstp ON rt.reihungstest_id = rtstp.reihungstest_id
				WHERE ps.prestudent_id = ".$this->db_add_param($prestudent_id)."
				AND rtstp.studienplan_id = pss.studienplan_id
				AND rt.datum=".$this->db_add_param($datum);

		if ($result = $this->db_query($qry))
		{
			while ($row = $this->db_fetch_object($result))
			{
				$obj = new stdClass();

				$obj->rt_person_id = $row->rt_person_id;
				$obj->reihungstest_id = $row->rt_id;
				$obj->person_id = $row->person_id;
				$obj->studienplan_id = $row->studienplan_id;
				$obj->anmeldedatum = $row->anmeldedatum;
				$obj->teilgenommen = $this->db_parse_bool($row->teilgenommen);
				$obj->ort_kurzbz = $row->ort_kurzbz;
				$obj->punkte = $row->punkte;
				$obj->insertamum = $row->insertamum;
				$obj->insertvon = $row->insertvon;
				$obj->updateamum = $row->updateamum;
				$obj->updatevon = $row->updatevon;

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
	 * Ermittelt die Anzahl der maximalen Platzanzahl aufgrund der Raumzuordnung
	 * @param int $reihungstest_id ID des Reihungstests.
	 * @return Anzahl der Plaetze
	 */
	public function getPlatzAnzahlRaum($reihungstest_id)
	{
		$qry = "
			SELECT
				sum(tbl_ort.arbeitsplaetze) as anzahl
			FROM
				public.tbl_rt_ort
				JOIN public.tbl_ort USING(ort_kurzbz)
			WHERE
				tbl_rt_ort.rt_id = ".$this->db_add_param($reihungstest_id, FHC_INTEGER);

		if ($result = $this->db_query($qry))
		{
			if ($row = $this->db_fetch_object($result))
			{
				return $row->anzahl;
			}
			else
			{
				$this->errormsg = 'Fehler beim Laden der Arbeitsplaetze';
				return false;
			}
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Arbeitsplaetze';
			return false;
		}
	}

	/**
	 * Liefert die Raeume mit den Aufsichtspersonen (uid aus tbl_rt_ort), die den Kriterien $uid entsprechen
	 * @param string|array $uid Optional. Default NULL. UID oder array von UIDs deren Aufsichtszuteilungen geladen werden sollen
	 * @param integer $studiengang_kz Optional. Default NULL. Kennzahl des Studiengangs, auf den gefiltert werden soll
	 * @return true
	 */
	public function getOrteByUid($uid = null, $studiengang_kz = null)
	{
		if ($uid != null && is_array($uid))
		{
			$uid = $this->db_implode4SQL($include_ids);
		}
		elseif ($uid != null)
		{
			$uid = $this->db_add_param($uid);
		}

		$qry = "
			SELECT
				tbl_rt_ort.ort_kurzbz AS ort, *
			FROM
				public.tbl_rt_ort
			JOIN
				public.tbl_reihungstest ON (rt_id = tbl_reihungstest.reihungstest_id)
			WHERE
				datum>=now()";
		if ($uid != null)
		{
			$qry .= " AND uid IN (".$uid.")";
		}


		if ($studiengang_kz != null)
		{
			$qry .= " AND studiengang_kz = ".$this->db_add_param($studiengang_kz);
		}

		if ($result = $this->db_query($qry))
		{
			while ($row = $this->db_fetch_object($result))
			{
				$obj = new stdClass();

				$obj->reihungstest_id = $row->rt_id;
				$obj->ort_kurzbz = $row->ort;
				$obj->uid = $row->uid;

				$obj->studiengang_kz = $row->studiengang_kz;
				$obj->anmerkung = $row->anmerkung;
				$obj->datum = $row->datum;
				$obj->uhrzeit = $row->uhrzeit;
				$obj->ext_id = $row->ext_id;
				$obj->insertamum = $row->insertamum;
				$obj->insertvon = $row->insertvon;
				$obj->updateamum = $row->updateamum;
				$obj->updatevon = $row->updatevon;
				$obj->max_teilnehmer = $row->max_teilnehmer;
				$obj->oeffentlich = $this->db_parse_bool($row->oeffentlich);
				$obj->freigeschaltet = $this->db_parse_bool($row->freigeschaltet);
				$obj->studiensemester_kurzbz = $row->studiensemester_kurzbz;
				$obj->stufe = $row->stufe;
				$obj->anmeldefrist = $row->anmeldefrist;
				$obj->aufnahmegruppe_kurzbz = $row->aufnahmegruppe_kurzbz;

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
	 * Prueft ob eine Person-Reihungstest-Studienplan zuteilung existiert (Muss in der DB unique sein)
	 * @param int $person_id ID der Person.
	 * @param int $rt_id ID des Reihungstests.
	 * @param int $studienplan_id Studienplan ID.
	 * @return boolean true wenn vorhanden, false wenn nicht oder im Fehlerfall
	 */
	public function checkPersonRtStudienplanExists($person_id, $rt_id, $studienplan_id)
	{
		$qry = "SELECT
					*
				FROM
					public.tbl_rt_person
				WHERE
					tbl_rt_person.person_id=".$this->db_add_param($person_id)."
					AND tbl_rt_person.rt_id=".$this->db_add_param($rt_id)."
					AND tbl_rt_person.studienplan_id=".$this->db_add_param($studienplan_id);
		if ($result = $this->db_query($qry))
		{
			if ($this->db_num_rows($result) > 0)
			{
				return true;
			}
			else
			{
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
	 * Laedt alle person_ids, die einem Reihungstest zugeteilt sind
	 * @param integer $reihungstest_id ID des Reihungstests.
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function getPersonenReihungstest($reihungstest_id)
	{
		$qry = "SELECT
					person_id
				FROM
					public.tbl_rt_person
				WHERE
					tbl_rt_person.rt_id=".$this->db_add_param($reihungstest_id)."
				ORDER BY
					person_id";
		if ($result = $this->db_query($qry))
		{
			while ($row = $this->db_fetch_object($result))
			{
				$obj = new stdClass();

				$obj->person_id = $row->person_id;

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
