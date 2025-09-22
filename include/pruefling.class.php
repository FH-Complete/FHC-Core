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
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>.
 */
require_once(dirname(__FILE__).'/basis_db.class.php');
require_once(dirname(__FILE__).'/ablauf.class.php');

class pruefling extends basis_db
{
	//Tabellenspalten
	public $pruefling_id;
	public $studiengang_kz;
	public $idnachweis;
	public $registriert;
	public $prestudent_id;
	public $semester;
	public $gesperrt;

	// ErgebnisArray
	public $result=array();
	public $num_rows=0;
	public $new;

	/**
	 * Konstruktor - Laedt optional einen pruefling
	 * @param $frage_id       Frage die geladen werden soll (default=null)
	 */
	public function __construct($pruefling_id=null)
	{
		parent::__construct();

		if($pruefling_id != null)
			$this->load($pruefling_id);
	}

	/**
	 * Laedt Pruefling mit der uebergebenen ID
	 * @param $pruefling_id ID der Frage die geladen werden soll
	 */
	public function load($pruefling_id)
	{
		$qry = "SELECT * FROM testtool.tbl_pruefling WHERE pruefling_id=".$this->db_add_param($pruefling_id, FHC_INTEGER);

		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				$this->pruefling_id = $row->pruefling_id;
				$this->studiengang_kz = $row->studiengang_kz;
				$this->idnachweis = $row->idnachweis;
				$this->registriert = $row->registriert;
				$this->prestudent_id = $row->prestudent_id;
				$this->semester = $row->semester;
				$this->gesperrt = $row->gesperrt;
				return true;
			}
			else
			{
				$this->errormsg = "Kein Eintrag gefunden fuer $pruefling_id";
				return false;
			}
		}
		else
		{
			$this->errormsg = "Fehler beim Laden";
			return false;
		}
	}

	/**
	 * Prueft die Variablen vor dem Speichern
	 * auf Gueltigkeit.
	 * @return true wenn ok, false im Fehlerfall
	 */
	protected function validate()
	{
		return true;
	}

	/**
	 * Speichert die Benutzerdaten in die Datenbank
	 * Wenn $new auf true gesetzt ist wird ein neuer Datensatz angelegt
	 * ansonsten der Datensatz mit $uid upgedated
	 * @return true wenn erfolgreich, false im Fehlerfall
	 */
	public function save()
	{
		//Variablen auf Gueltigkeit pruefen
		if(!$this->validate())
			return false;

		if($this->new) //Wenn new true ist dann ein INSERT absetzen ansonsten ein UPDATE
		{
			$qry = 'BEGIN;INSERT INTO testtool.tbl_pruefling (studiengang_kz, idnachweis, registriert, prestudent_id, semester, gesperrt) VALUES('.
			       $this->db_add_param($this->studiengang_kz).",".
			       $this->db_add_param($this->idnachweis).",".
			       $this->db_add_param($this->registriert).",".
			       $this->db_add_param($this->prestudent_id).",".
			       $this->db_add_param($this->semester).",".
			       $this->db_add_param($this->gesperrt, FHC_BOOLEAN).");";
		}
		else
		{
			$qry = 'UPDATE testtool.tbl_pruefling SET'.
			       ' studiengang_kz='.$this->db_add_param($this->studiengang_kz, FHC_INTEGER).','.
			       ' idnachweis='.$this->db_add_param($this->idnachweis).','.
			       ' registriert='.$this->db_add_param($this->registriert).','.
			       ' semester='.$this->db_add_param($this->semester).','.
			       ' prestudent_id='.$this->db_add_param($this->prestudent_id, FHC_INTEGER).','.
			       ' gesperrt='.$this->db_add_param($this->gesperrt, FHC_BOOLEAN).
			       " WHERE pruefling_id=".$this->db_add_param($this->pruefling_id, FHC_INTEGER, false).";";
		}

		if($this->db_query($qry))
		{
			if($this->new)
			{
				$qry = "SELECT currval('testtool.tbl_pruefling_pruefling_id_seq') as id";
				if($this->db_query($qry))
				{
					if($row = $this->db_fetch_object())
					{
						$this->pruefling_id = $row->id;
						$this->db_query('COMMIT;');
						return true;
					}
					else
					{
						$this->db_query('ROLLBACK;');
						$this->errormsg = 'Fehler beim Lesen der Sequence';
						return false;
					}
				}
				else
				{
					$this->db_query('ROLLBACK;');
					$this->errormsg = 'Fehler beim Lesen der Sequence';
					return false;
				}
			}
			else
			{
				return true;
			}
		}
		else
		{
			$this->db_query('ROLLBACK');
			$this->errormsg = 'Fehler beim Speichern der Frage';
			return false;
		}
	}

	/**
	 * Laedt einen Pruefling anhand der Prestudent_id
	 *
	 * @param $prestudent_id
	 * @return boolean
	 */
	public function getPruefling($prestudent_id)
	{
		$qry = "SELECT * FROM testtool.tbl_pruefling WHERE prestudent_id=".$this->db_add_param($prestudent_id, FHC_INTEGER);

		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				$this->pruefling_id = $row->pruefling_id;
				$this->studiengang_kz = $row->studiengang_kz;
				$this->idnachweis = $row->idnachweis;
				$this->registriert = $row->registriert;
				$this->prestudent_id = $row->prestudent_id;
				$this->semester = $row->semester;
				$this->gesperrt = $row->gesperrt;
				return true;
			}
			else
			{
				$this->errormsg = "Kein Eintrag gefunden";
				return false;
			}
		}
		else
		{
			$this->errormsg = "Fehler beim Laden";
			return false;
		}
	}

	/**
	 * Laedt alle Prueflinge anhand der Prestudent_id
	 *
	 * @param $prestudent_id
	 * @return boolean
	 */
	public function getPrueflinge($prestudent_id)
	{
		$qry = "SELECT * FROM testtool.tbl_pruefling WHERE prestudent_id=".$this->db_add_param($prestudent_id, FHC_INTEGER);

		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$obj = new pruefling();

				$obj->pruefling_id = $row->pruefling_id;
				$obj->studiengang_kz = $row->studiengang_kz;
				$obj->idnachweis = $row->idnachweis;
				$obj->registriert = $row->registriert;
				$obj->prestudent_id = $row->prestudent_id;
				$obj->semester = $row->semester;
				$obj->gesperrt = $row->gesperrt;

				$this->result[] = $obj;
			}
			return true;
		}
		else
		{
			$this->errormsg = "Fehler beim Laden";
			return false;
		}
	}

	/**
	 * Ermittelt den aktuellen Level (schwierigkeitsgrad der Frage)
	 * des Prueflings fuer das uebergebene Gebiet
	 *
	 * @param $pruefling_id
	 * @param $gebiet_id
	 */
	public function getPrueflingLevel($pruefling_id, $gebiet_id)
	{
		$gebiet = new gebiet($gebiet_id);

		//wenn Levelsystem fuer dieses Gebiet aktiviert ist
		if($gebiet->level_start!='')
		{
			//Maximal und Minimal Level fuer dieses Gebiet ermitteln
			$max_level = 0;
			$min_level = 0;

			$qry = "SELECT max(level) as max, min(level) as min FROM testtool.tbl_frage
					WHERE gebiet_id=".$this->db_add_param($gebiet_id, FHC_INTEGER);

			if($this->db_query($qry))
			{
				if($row = $this->db_fetch_object())
				{
					$max_level = $row->max;
					$min_level = $row->min;
				}
				else
				{
					$this->errormsg = 'unbekannter Fehler in getPrueflingLevel';
					return false;
				}
			}
			else
			{
				$this->errormsg = 'Fehler beim Ermitteln des Pruefling-Levels';
				return false;
			}

			//alle bisherigen Antworten fuer dieses Gebiet holen
			$qry = "SELECT
						tbl_vorschlag.punkte
					FROM
						testtool.tbl_pruefling_frage
						JOIN testtool.tbl_vorschlag USING(frage_id)
						JOIN testtool.tbl_antwort USING(vorschlag_id)
						JOIN testtool.tbl_frage USING(frage_id)
					WHERE
						tbl_frage.gebiet_id=".$this->db_add_param($gebiet_id, FHC_INTEGER)." AND
						tbl_pruefling_frage.pruefling_id=".$this->db_add_param($pruefling_id, FHC_INTEGER)." AND
						tbl_antwort.pruefling_id = tbl_pruefling_frage.pruefling_id
					ORDER BY tbl_pruefling_frage.nummer ASC";

			$aktueller_level=$gebiet->level_start;
			$anzahl_richtig=0;
			$anzahl_falsch=0;
			if($this->db_query($qry))
			{
				while($row = $this->db_fetch_object())
				{
					if($row->punkte>0)
					{
						//wenn die Frage richtig beantwortet wurde dann richtig-zaehler erhoehen
						$anzahl_richtig++;
						$anzahl_falsch=0;
					}
					else
					{
						//wenn die Frage falsch beantwortet wurde dann falsch-zaehler erhoehen
						$anzahl_richtig=0;
						$anzahl_falsch++;
					}

					//wenn einer der Zaehler das Sprunglevel erreicht hat, dann
					//in ein anderes Level springen
					if($anzahl_richtig==$gebiet->level_sprung_auf)
					{
						$aktueller_level++;
						$anzahl_richtig=0;
						$anzahl_falsch=0;
					}
					elseif($anzahl_falsch==$gebiet->level_sprung_ab)
					{
						$aktueller_level--;
						$anzahl_richtig=0;
						$anzahl_falsch=0;
					}

					//aktueller level darf nicht kleiner/groesser als der minimal/maximal Level sein
					if($aktueller_level<$min_level)
						$aktueller_level=$min_level;
					if($aktueller_level>$max_level)
						$aktueller_level=$max_level;
				}

				return $aktueller_level;
			}
			else
			{
				$this->errormsg = 'Fehler bei einer Abfrage';
				return false;
			}
		}
		else
			return -1;
	}

	/**
	 * Berechnet das Reihungstestergebnis fuer eine Person und ggf Reihungstest
	 * @param $person_id ID der Person.
	 * @param $punkte Wenn true werden Punkte geliefert, sonst Prozentsumme.
	 * @param $reihungstest_id ID des Reihungstests.
	 * @param $has_excluded_gebiete Wenn true werden die Punkte der Fragengebiete, die im config-array
	 * definiert sind, bei der Berechnung der Endpunkte nicht berücksichtigt.
	 * @param $studiengang_kz Wenn eine Studiengangskennzahl übergeben wird, dann werden nur die Punkte der
	 * Basis-Fragengebiete (ohne Quereinsteiger) bei der Berechnung der Endpunkte berücksichtigt.
	 * @param $gewichtung_studiengang_kz Wenn diese studiengang_kz übergeben wird, wird das Ergebnis entsprechend des Gewichtungsschemas des Studienganges gewichtet
	 * @return Endpunkte des Reihungstests oder False wenn keine Punkte vorhanden
	 */
	public function getReihungstestErgebnisPerson($person_id, $punkte=false, $reihungstest_id, $has_excluded_gebiete = false, $studiengang_kz = null, $gewichtung_studiengang_kz = null)
	{
		if(is_numeric($reihungstest_id))
		{
			$ergebnis=0;

			$qry = "
				SELECT DISTINCT ON (vw_auswertung_ablauf.gebiet_id) gebiet_id,
					vw_auswertung_ablauf.*,
					tbl_studiengang.typ
				FROM
					testtool.vw_auswertung_ablauf
				JOIN
					public.tbl_studiengang USING (studiengang_kz)
				WHERE
					reihungstest_id = ".$this->db_add_param($reihungstest_id, FHC_INTEGER);

			//	Ggf. die Basis-Fragengebiete ermitteln (ohne Quereinsteigergebiete)
			if (is_numeric($studiengang_kz))
			{
				$ablauf = new Ablauf();
				$ablauf->getAblaufGebiete($studiengang_kz, NULL, 1);
				$basis_gebiet_id_arr = array();
				$basis_gebiet_id_toString = '';

				foreach ($ablauf->result as $obj)
				{
					$basis_gebiet_id_arr []= $obj->gebiet_id;
				}
				$basis_gebiet_id_toString = implode(', ', $basis_gebiet_id_arr);

				if (!empty($basis_gebiet_id_toString))
				{
					$qry .= "
					AND
						gebiet_id IN (". $basis_gebiet_id_toString. ")
				";
				}
			}

			// Ggf. Fragengebiete exkludieren
			if ($has_excluded_gebiete)
			{
				if (defined('FAS_REIHUNGSTEST_EXCLUDE_GEBIETE') && !empty(FAS_REIHUNGSTEST_EXCLUDE_GEBIETE))
				{
					$excluded_gebiete = unserialize(FAS_REIHUNGSTEST_EXCLUDE_GEBIETE);
					$exclude_gebiet_id_arr = $excluded_gebiete;
					if (is_array($exclude_gebiet_id_arr) && count($exclude_gebiet_id_arr) > 0)
					{
						$exclude_gebiet_id_toString = implode(', ', $exclude_gebiet_id_arr);
						$qry .= "
							AND
								gebiet_id NOT IN (". $exclude_gebiet_id_toString. ")
							AND
								typ = 'b'
						";
					}
				}
			}

			/**
			 * Quercheck der PrestudentID ueber den Status damit bei Personen
			 * die den Reihungstest oefter im selben Studiengang gemacht haben nicht das
			 * Ergebniss der beiden Tests summiert bekommen
			 * Im Zweifelsfall wird der neuere Reihungstest genommen */
			$qry .= "
					AND prestudent_id = (
						SELECT
							prestudent_id
						FROM
							public.tbl_rt_person
						JOIN
							public.tbl_prestudent USING(person_id)
						JOIN
							public.tbl_prestudentstatus USING (prestudent_id, studienplan_id)
						JOIN
							tbl_reihungstest ON (
								tbl_rt_person.rt_id = tbl_reihungstest.reihungstest_id
							)
						WHERE
							tbl_rt_person.person_id = ".$this->db_add_param($person_id, FHC_INTEGER)."
						AND
							tbl_rt_person.rt_id = ".$this->db_add_param($reihungstest_id, FHC_INTEGER)."
						AND
							tbl_prestudentstatus.status_kurzbz='Interessent'
						AND
							tbl_prestudentstatus.studiensemester_kurzbz = tbl_reihungstest.studiensemester_kurzbz
						ORDER BY tbl_reihungstest.datum DESC, tbl_prestudent.priorisierung ASC LIMIT 1
					)
				";

			//calculate Gewichte for Studiengang if set
			$gewichte = array();
			if (isset($gewichtung_studiengang_kz))
			{
				$ablauf = new ablauf();
				$ablauf->getAblaufGebiete($gewichtung_studiengang_kz);

				foreach ($ablauf->result as $abl)
				{
					$gewichte[$abl->gebiet_id] = $abl->gewicht;
				}
			}

			if($result = $this->db_query($qry))
			{
				// Wenn keine Eintraege vorhanden dann false
				if($this->db_num_rows($result)==0)
					return false;

				$summeGewicht = 0;

				while($row = $this->db_fetch_object($result))
				{
					if (!isset($row->punkte))
						continue;

					//wenn maxpunkte ueberschritten wurde -> 100%
					if($row->punkte>=$row->maxpunkte)
					{
						$prozent=100;
						$row->punkte = $row->maxpunkte;
					}
					else
						$prozent = (($row->punkte + $row->offsetpunkte)/($row->maxpunkte + $row->offsetpunkte))*100;

					if($punkte)
					{
						$ergebnis += $row->punkte;
					}
					else
					{
						$gew = isset($gewichte[$row->gebiet_id]) ? $gewichte[$row->gebiet_id] : 1;
						$ergebnis += $prozent * $gew;
						$summeGewicht += $gew;
					}
				}
				return $summeGewicht > 0 ? $ergebnis/$summeGewicht : $ergebnis;
			}
			else
			{
				$this->errormsg = 'Fehler bei einer Abfrage';
				return false;
			}

		}
		else
		{
			$this->errormsg = 'reihungstest_id muss numerisch sein';
			return false;
		}
	}

	/**
	 * Berechnet das Reihungstestergebnis fuer einen Prestudenten und ggf Reihungstest
	 *
	 * @param $prestudent_id ID des Prestudenten
	 * @param $punkte Wenn true werden Punkte geliefert, sonst Prozentsumme.
	 * @param $reihungstest_id ID des Reihungstests.
	 * @return Endpunkte des Reihungstests oder false wenn keine Punkte vorhanden
	 */
	public function getReihungstestErgebnisPrestudent($prestudent_id, $punkte=false, $reihungstest_id=null)
	{
		$qry = "SELECT * FROM testtool.vw_auswertung
				WHERE prestudent_id=".$this->db_add_param($prestudent_id, FHC_INTEGER);

		if(!is_null($reihungstest_id))
			$qry.=" AND reihungstest_id=".$this->db_add_param($reihungstest_id, FHC_INTEGER);

		$ergebnis=0;

		if($result = $this->db_query($qry))
		{
			if($this->db_num_rows($result)==0)
				return false;

			while($row = $this->db_fetch_object())
			{
				//wenn maxpunkte ueberschritten wurde -> 100%
				if($row->punkte>=$row->maxpunkte)
				{
					$prozent=100;
					$row->punkte = $row->maxpunkte;
				}
				else
					$prozent = ($row->punkte/$row->maxpunkte)*100;

				if($punkte)
					$ergebnis +=$row->punkte;
				else
					$ergebnis+=$prozent*$row->gewicht;
			}
			return $ergebnis;
		}
		else
		{
			$this->errormsg = 'Fehler bei einer Abfrage';
			return false;
		}
	}
	
	public function isGesperrt($pruefling_id = null, $prestudent_id = null)
	{
		if (is_null($pruefling_id) && is_null($prestudent_id))
		{
			$this->errormsg = 'Falsche Parameterübergabe';
			return false;
		}

		$qry = "SELECT spruefling.gesperrt
				FROM testtool.tbl_pruefling
				RIGHT JOIN public.tbl_prestudent USING(prestudent_id)
				JOIN public.tbl_person USING (person_id)
				JOIN public.tbl_prestudent pss ON pss.person_id = tbl_person.person_id
				JOIN testtool.tbl_pruefling spruefling ON pss.prestudent_id = spruefling.prestudent_id
				WHERE spruefling.gesperrt";

		if (!is_null($pruefling_id))
			$qry .= " AND tbl_pruefling.pruefling_id = ".$this->db_add_param($pruefling_id, FHC_INTEGER);
		
		if (!is_null($prestudent_id))
			$qry .= " AND tbl_prestudent.prestudent_id = ".$this->db_add_param($prestudent_id, FHC_INTEGER);
		
		$qry .= " LIMIT 1";

		if($result = $this->db_query($qry))
		{
			if ($this->db_num_rows($result) == 0)
				return false;
			else
				return true;
		}
		else
		{
			$this->errormsg = 'Fehler bei einer Abfrage';
			return false;
		}
	}
}
?>
