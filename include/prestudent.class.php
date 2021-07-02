<?php
/* Copyright (C) 2021 fhcomplete.org
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
 *		  Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>,
 *		  Rudolf Hangl <rudolf.hangl@technikum-wien.at> and
 *		  Manuela Thamer <manuela.thamer@technikum-wien.at>.
 */
require_once(dirname(__FILE__).'/person.class.php');
require_once(dirname(__FILE__).'/log.class.php');

require_once(dirname(__FILE__).'/phrasen.class.php');
require_once(dirname(__FILE__).'/globals.inc.php');
require_once(dirname(__FILE__).'/sprache.class.php');

$sprache = getSprache();
$lang = new sprache();
$lang->load($sprache);
$p = new phrasen($sprache);

class prestudent extends person
{
	//Tabellenspalten
	public $prestudent_id;	// varchar(16)
	public $aufmerksamdurch_kurzbz;
	public $studiengang_kz;
	public $berufstaetigkeit_code;
	public $ausbildungcode;
	public $zgv_code;
	public $zgvort;
	public $zgvdatum;
	public $zgvnation;
	public $zgvmas_code;
	public $zgvmaort;
	public $zgvmadatum;
	public $zgvmanation;
	public $ausstellungsstaat;
	public $aufnahmeschluessel;
	public $facheinschlberuf;
	public $anmeldungreihungstest;
	public $reihungstestangetreten;
	public $reihungstest_id;
	public $punkte; //rt_gesamtpunkte
	public $rt_punkte1;
	public $rt_punkte2;
	public $rt_punkte3 = 0;
	public $bismelden = true;
	public $anmerkung;
	public $anmerkung_status;
	public $mentor;
	public $ext_id_prestudent;
	public $dual = false;
	public $zgvdoktor_code;
	public $zgvdoktorort;
	public $zgvdoktordatum;
	public $zgvdoktornation;
	public $gsstudientyp_kurzbz='Intern';
	public $aufnahmegruppe_kurzbz;
	public $priorisierung = null;

	public $status_kurzbz;
	public $studiensemester_kurzbz;
	public $ausbildungssemester;
	public $datum;
	public $insertamum;
	public $insertvon;
	public $updateamum;
	public $updatevon;
	public $orgform_kurzbz;
	public $studienplan_id;
	public $studienplan_bezeichnung;
	public $bestaetigtam;
	public $bestaetigtvon;
	public $bewerbung_abgeschicktamum;
	public $statusgrund_id;
	public $rt_stufe;

	public $studiensemester_old = '';
	public $ausbildungssemester_old = '';

	// ErgebnisArray
	public $result = array();
	public $num_rows = 0;

	/**
	 * Konstruktor - Uebergibt die Connection und laedt optional einen Prestudent
	 * @param $prestudent_id Prestudent der geladen werden soll (default=null)
	 */
	public function __construct($prestudent_id=null)
	{
		parent::__construct();

		if($prestudent_id != null)
			$this->load($prestudent_id);
	}

	/**
	 * Laedt Prestudent mit der uebergebenen ID
	 * @param integer $prestudent_id ID des Prestudenten der geladen werden soll
	 */
	public function load($prestudent_id=null)
	{
		if(!is_numeric($prestudent_id))
		{
			$this->errormsg = 'ID ist ungueltig';
			return false;
		}

		$qry = 'SELECT * '
				. 'FROM public.tbl_prestudent '
				. 'WHERE prestudent_id = '.$this->db_add_param($prestudent_id, FHC_INTEGER);

		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				$this->prestudent_id = $row->prestudent_id;
				$this->aufmerksamdurch_kurzbz = $row->aufmerksamdurch_kurzbz;
				$this->studiengang_kz = $row->studiengang_kz;
				$this->berufstaetigkeit_code = $row->berufstaetigkeit_code;
				$this->ausbildungcode = $row->ausbildungcode;
				$this->zgv_code = $row->zgv_code;
				$this->zgvort = $row->zgvort;
				$this->zgvdatum = $row->zgvdatum;
				$this->zgvnation = $row->zgvnation;
				$this->zgvmas_code = $row->zgvmas_code;
				$this->zgvmaort = $row->zgvmaort;
				$this->zgvmadatum = $row->zgvmadatum;
				$this->zgvmanation = $row->zgvmanation;
				$this->aufnahmeschluessel = $row->aufnahmeschluessel;
				$this->facheinschlberuf = $this->db_parse_bool($row->facheinschlberuf);
				$this->anmeldungreihungstest = $row->anmeldungreihungstest;
				$this->reihungstestangetreten = $this->db_parse_bool($row->reihungstestangetreten);
				$this->reihungstest_id = $row->reihungstest_id;
				$this->punkte = $row->rt_gesamtpunkte;
				$this->rt_punkte1 = $row->rt_punkte1;
				$this->rt_punkte2 = $row->rt_punkte2;
				$this->rt_punkte3 = $row->rt_punkte3;
				$this->bismelden = $this->db_parse_bool($row->bismelden);
				$this->person_id = $row->person_id;
				$this->anmerkung = $row->anmerkung;
				$this->mentor = $row->mentor;
				$this->ext_id_prestudent = $row->ext_id;
				$this->dual = $this->db_parse_bool($row->dual);
				$this->ausstellungsstaat = $row->ausstellungsstaat;
				$this->zgvdoktor_code = $row->zgvdoktor_code;
				$this->zgvdoktorort = $row->zgvdoktorort;
				$this->zgvdoktordatum = $row->zgvdoktordatum;
				$this->zgvdoktornation = $row->zgvdoktornation;
				$this->gsstudientyp_kurzbz = $row->gsstudientyp_kurzbz;
				$this->aufnahmegruppe_kurzbz = $row->aufnahmegruppe_kurzbz;
				$this->priorisierung = $row->priorisierung;

				if(!person::load($row->person_id))
					return false;
				else
					return true;
			}
			else
			{
				$this->errormsg = "Kein Prestudent Eintrag gefunden";
				return false;
			}
		}
		else
		{
			$this->errormsg = "Fehler beim Laden des Prestudenten";
			return false;
		}
	}

	/**
	 * Prueft die Variablen vor dem Speichern
	 * auf Gueltigkeit.
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function validate()
	{
		if($this->punkte>9999.9999)
		{
			$this->errormsg = 'Reihungstestgesamtpunkte darf nicht groesser als 9999.9999 sein';
			return false;
		}
		if($this->rt_punkte1>9999.9999)
		{
			$this->errormsg = 'Reihungstestpunkte1 darf nicht groesser als 9999.9999 sein';
			return false;
		}
		if($this->rt_punkte2>9999.9999)
		{
			$this->errormsg = 'Reihungstestpunkte2 darf nicht groesser als 9999.9999 sein';
			return false;
		}
		if($this->rt_punkte3>9999.9999)
		{
			$this->errormsg = 'Reihungstestpunkte3 darf nicht groesser als 9999.9999 sein';
			return false;
		}
		if(mb_strlen($this->zgvort)>64)
		{
			$this->errormsg = 'ZGV Ort darf nicht länger als 64 Zeichen sein.';
			return false;
		}
		if(mb_strlen($this->zgvmaort)>64)
		{
			$this->errormsg = 'ZGV Master Ort darf nicht länger als 64 Zeichen sein.';
			return false;
		}

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
		//Personen Datensatz speichern
		//if(!person::save())
		//	return false;

		$this->checkAusstellungsstaat();

		//Variablen auf Gueltigkeit pruefen
		if(!prestudent::validate())
			return false;

		if($this->new) //Wenn new true ist dann ein INSERT absetzen ansonsten ein UPDATE
		{
			$qry = 'BEGIN;INSERT INTO public.tbl_prestudent (aufmerksamdurch_kurzbz, person_id,
					studiengang_kz, berufstaetigkeit_code, ausbildungcode, zgv_code, zgvort, zgvdatum, zgvnation,
					zgvmas_code, zgvmaort, zgvmadatum, zgvmanation, aufnahmeschluessel, facheinschlberuf,
					reihungstest_id, anmeldungreihungstest, reihungstestangetreten, rt_gesamtpunkte,
					rt_punkte1, rt_punkte2, rt_punkte3, bismelden, insertamum, insertvon,
					updateamum, updatevon, anmerkung, dual, ausstellungsstaat, mentor,
					gsstudientyp_kurzbz, aufnahmegruppe_kurzbz, priorisierung) VALUES('.
					$this->db_add_param($this->aufmerksamdurch_kurzbz).",".
					$this->db_add_param($this->person_id).",".
					$this->db_add_param($this->studiengang_kz).",".
					$this->db_add_param($this->berufstaetigkeit_code).",".
					$this->db_add_param($this->ausbildungcode).",".
					$this->db_add_param($this->zgv_code).",".
					$this->db_add_param($this->zgvort).",".
					$this->db_add_param($this->zgvdatum).",".
					$this->db_add_param($this->zgvnation).",".
					$this->db_add_param($this->zgvmas_code).",".
					$this->db_add_param($this->zgvmaort).",".
					$this->db_add_param($this->zgvmadatum).",".
					$this->db_add_param($this->zgvmanation).",".
					$this->db_add_param($this->aufnahmeschluessel).",".
					$this->db_add_param($this->facheinschlberuf, FHC_BOOLEAN).",".
					$this->db_add_param($this->reihungstest_id).",".
					$this->db_add_param($this->anmeldungreihungstest).",".
					$this->db_add_param($this->reihungstestangetreten, FHC_BOOLEAN).",".
					$this->db_add_param($this->punkte).",".
					$this->db_add_param($this->rt_punkte1).",".
					$this->db_add_param($this->rt_punkte2).",".
					$this->db_add_param($this->rt_punkte3).",".
					$this->db_add_param($this->bismelden, FHC_BOOLEAN).",".
					$this->db_add_param($this->insertamum).",".
					$this->db_add_param($this->insertvon).",".
					$this->db_add_param($this->updateamum).",".
					$this->db_add_param($this->updatevon).",".
					$this->db_add_param($this->anmerkung).",".
					$this->db_add_param($this->dual, FHC_BOOLEAN).",".
					$this->db_add_param($this->ausstellungsstaat).",".
					$this->db_add_param($this->mentor).",".
					$this->db_add_param($this->gsstudientyp_kurzbz).",".
					$this->db_add_param($this->aufnahmegruppe_kurzbz).",".
					$this->db_add_param($this->priorisierung).");";
		}
		else
		{
			$qry = 'UPDATE public.tbl_prestudent SET'.
					' aufmerksamdurch_kurzbz='.$this->db_add_param($this->aufmerksamdurch_kurzbz).",".
					' person_id='.$this->db_add_param($this->person_id).",".
					' studiengang_kz='.$this->db_add_param($this->studiengang_kz).",".
					' berufstaetigkeit_code='.$this->db_add_param($this->berufstaetigkeit_code).",".
					' ausbildungcode='.$this->db_add_param($this->ausbildungcode).",".
					' zgv_code='.$this->db_add_param($this->zgv_code).",".
					' zgvort='.$this->db_add_param($this->zgvort).",".
					' zgvdatum='.$this->db_add_param($this->zgvdatum).",".
					' zgvnation='.$this->db_add_param($this->zgvnation).",".
					' zgvmas_code='.$this->db_add_param($this->zgvmas_code).",".
					' zgvmaort='.$this->db_add_param($this->zgvmaort).",".
					' zgvmadatum='.$this->db_add_param($this->zgvmadatum).",".
					' zgvmanation='.$this->db_add_param($this->zgvmanation).",".
					' aufnahmeschluessel='.$this->db_add_param($this->aufnahmeschluessel).",".
					' facheinschlberuf='.$this->db_add_param($this->facheinschlberuf, FHC_BOOLEAN).",".
					' reihungstest_id='.$this->db_add_param($this->reihungstest_id).",".
					' anmeldungreihungstest='.$this->db_add_param($this->anmeldungreihungstest).",".
					' reihungstestangetreten='.$this->db_add_param($this->reihungstestangetreten, FHC_BOOLEAN).",".
					' rt_gesamtpunkte='.$this->db_add_param($this->punkte).",".
					' rt_punkte1='.$this->db_add_param($this->rt_punkte1).",".
					' rt_punkte2='.$this->db_add_param($this->rt_punkte2).",".
					' rt_punkte3='.$this->db_add_param($this->rt_punkte3).",".
					' bismelden='.$this->db_add_param($this->bismelden, FHC_BOOLEAN).",".
					' updateamum='.$this->db_add_param($this->updateamum).",".
					' updatevon='.$this->db_add_param($this->updatevon).",".
					' anmerkung='.$this->db_add_param($this->anmerkung).",".
					' mentor='.$this->db_add_param($this->mentor).",".
					' gsstudientyp_kurzbz='.$this->db_add_param($this->gsstudientyp_kurzbz).",".
					' dual='.$this->db_add_param($this->dual, FHC_BOOLEAN).",".
					' ausstellungsstaat='.$this->db_add_param($this->ausstellungsstaat).",".
					' aufnahmegruppe_kurzbz='.$this->db_add_param($this->aufnahmegruppe_kurzbz).",".
					' priorisierung='.$this->db_add_param($this->priorisierung).' '.
					" WHERE prestudent_id=".$this->db_add_param($this->prestudent_id).";";
		}

		if($this->db_query($qry))
		{
			if($this->new)
			{
				$qry = "SELECT currval('public.tbl_prestudent_prestudent_id_seq') as id;";
				if($this->db_query($qry))
				{
					if($row = $this->db_fetch_object())
					{
						$this->prestudent_id = $row->id;
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
			//Log schreiben
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern des Prestudent-Datensatzes';
			return false;
		}
	}

	/**
	 * Falls ZGV vorhanden, setze Ausstellungsstaat (für BIS-Meldung)
	 * auf Nation der höchsten angegebenen ZGV
	 */
	private function checkAusstellungsstaat()
	{
		if ($this->zgvmas_code && $this->zgvmanation)
		{
			$this->ausstellungsstaat = $this->zgvmanation;
		}
		elseif ($this->zgv_code && $this->zgvnation)
		{
			$this->ausstellungsstaat = $this->zgvnation;
		}
	}

	/**
	 * Laden aller Prestudenten, die an $datum zum Reihungstest geladen sind.
	 * da zukünftige Teilnehmer nicht mehr angezeigt werden sollen.
	 * @param string $datum Datum in der Form YYYY-MM-DD an dem der Reihungstest stattfindet
	 * @return true wenn erfolgreich, false im Fehlerfall
	 */
	public function getPrestudentRT($datum)
	{
		$sql_query='SELECT * FROM (
					SELECT
						DISTINCT on(tbl_prestudent.prestudent_id) tbl_prestudent.prestudent_id,
						tbl_person.vorname, tbl_person.nachname, tbl_person.person_id, tbl_person.titelpre,
						tbl_person.titelpost, tbl_person.gebdatum,
						tbl_reihungstest.*,
						tbl_prestudent.studiengang_kz as studiengang_kz
					FROM
						public.tbl_prestudent
						JOIN public.tbl_person USING(person_id)
						JOIN public.tbl_rt_person USING(person_id)
						JOIN public.tbl_reihungstest ON(tbl_reihungstest.reihungstest_id=tbl_rt_person.rt_id)
					WHERE
						tbl_reihungstest.datum='.$this->db_add_param($datum).'
						AND tbl_rt_person.studienplan_id IN (SELECT studienplan_id FROM public.tbl_prestudentstatus WHERE prestudent_id=tbl_prestudent.prestudent_id)
						AND EXISTS(SELECT * FROM public.tbl_prestudentstatus JOIN public.tbl_studiensemester USING(studiensemester_kurzbz)
							WHERE prestudent_id=tbl_prestudent.prestudent_id AND tbl_studiensemester.ende>'.$this->db_add_param($datum).')
					) a
					ORDER BY nachname,vorname';

		if(!$this->db_query($sql_query))
		{
			$this->errormsg = 'Fehler beim Laden der Daten';
			return false;
		}

		$this->num_rows=0;

		while($row = $this->db_fetch_object())
		{
			$ps=new prestudent();
			$ps->prestudent_id = $row->prestudent_id;
			$ps->person_id = $row->person_id;
			$ps->reihungstest_id = $row->reihungstest_id;
			$ps->titelpost = $row->titelpost;
			$ps->titelpre = $row->titelpre;
			$ps->nachname = $row->nachname;
			$ps->vorname = $row->vorname;
			$ps->gebdatum = $row->gebdatum;
			$ps->studiengang_kz = $row->studiengang_kz;
			$this->result[]=$ps;
			$this->num_rows++;
		}
		return true;
	}

	/**
	 * Laden aller Prestudenten, die an $datum zum Reihungstest geladen sind.
	 * Wenn es mehrere Bewerbungen für ein Person gibt, wird nur die höchste Prestudent_id zurückgeliefert
	 * @param string $datum Datum in der Form YYYY-MM-DD an dem der Reihungstest stattfindet
	 * @return true wenn erfolgreich, false im Fehlerfall
	 */
	public function getFirstPrioPrestudentRT($datum)
	{
		$sql_query='SELECT DISTINCT
						ps.prestudent_id,
						pers.vorname, pers.nachname, pers.person_id, pers.titelpre,
						pers.titelpost, pers.gebdatum,
						tbl_reihungstest.*,
						ps.studiengang_kz as studiengang_kz,
						tbl_studiengang.typ
					FROM
						public.tbl_prestudent ps
						JOIN public.tbl_person pers USING (person_id)
						JOIN public.tbl_rt_person USING (person_id)
						JOIN public.tbl_reihungstest ON (tbl_reihungstest.reihungstest_id=tbl_rt_person.rt_id)
						JOIN public.tbl_studiengang ON (ps.studiengang_kz=tbl_studiengang.studiengang_kz)
						JOIN public.tbl_prestudentstatus ON (tbl_prestudentstatus.prestudent_id=ps.prestudent_id
																AND status_kurzbz=\'Interessent\'
																AND tbl_prestudentstatus.studiensemester_kurzbz=tbl_reihungstest.studiensemester_kurzbz)
					WHERE
						tbl_reihungstest.datum='.$this->db_add_param($datum).'
						/*AND tbl_rt_person.studienplan_id IN (SELECT studienplan_id FROM public.tbl_prestudentstatus WHERE prestudent_id=ps.prestudent_id)*/
						AND tbl_prestudentstatus.studienplan_id IN (SELECT studienplan_id FROM public.tbl_rt_studienplan WHERE reihungstest_id=tbl_rt_person.rt_id)
						AND EXISTS(SELECT * FROM public.tbl_prestudentstatus JOIN public.tbl_studiensemester USING(studiensemester_kurzbz)
							WHERE prestudent_id=ps.prestudent_id AND tbl_studiensemester.ende > '.$this->db_add_param($datum).')
						AND priorisierung = (SELECT priorisierung FROM public.tbl_prestudent
									WHERE person_id = pers.person_id
									AND get_rolle_prestudent (ps.prestudent_id,NULL) IN (\'Interessent\',\'Bewerber\',\'Wartender\',\'Aufgenommener\')
									--AND tbl_prestudent.studiengang_kz=ps.studiengang_kz
									ORDER BY priorisierung ASC LIMIT 1)
					ORDER BY nachname,vorname';

		if(!$this->db_query($sql_query))
		{
			$this->errormsg = 'Fehler beim Laden der Daten';
			return false;
		}

		$this->num_rows=0;

		while($row = $this->db_fetch_object())
		{
			$ps=new prestudent();
			$ps->prestudent_id = $row->prestudent_id;
			$ps->person_id = $row->person_id;
			$ps->reihungstest_id = $row->reihungstest_id;
			$ps->titelpost = $row->titelpost;
			$ps->titelpre = $row->titelpre;
			$ps->nachname = $row->nachname;
			$ps->vorname = $row->vorname;
			$ps->gebdatum = $row->gebdatum;
			$ps->studiengang_kz = $row->studiengang_kz;
			$this->result[]=$ps;
			$this->num_rows++;
		}
		return true;
	}

	/**
	 * Laedt über einen Prestudenten alle anderen Prestudenten einer Person, die aktuell an STG interessiert sind.
	 * @integer $prestudent_id Prestudent ID, über die alle weiteren Prestudenten ermittelt werden sollen.
	 * @boolean $prio Wenn true, dann wird nur der Prestudent mit dem am höchsten priorisierten Studiengang zurückgegeben.
     * @string $typ Ergebnis nach STG-Typ filtern.
     * @string $studiengang_kz Ergebnis nach STG-Kennzahl filtern.
	 * return Objekt-Array mit allen Prestudenten einer Person, die aktuell an STG interessiert sind.
	 */
	public function getActualInteressenten($prestudent_id, $prio = false, $typ = NULL, $studiengang_kz = NULL)
	{
		if (is_numeric($prestudent_id))
		{
			$qry = "
			SELECT DISTINCT ON (priorisierung, prestudent_id)
				priorisierung,
				prestudent_id,
				tbl_prestudentstatus.studienplan_id,
				studiengang_kz,
				typ,
				tbl_studiengangstyp.bezeichnung AS typ_bz,
				ausbildungssemester,
				tbl_orgform.bezeichnung_mehrsprachig
			FROM
				public.tbl_prestudentstatus
			JOIN
				public.tbl_prestudent USING (prestudent_id)
			JOIN
				public.tbl_studiengang USING (studiengang_kz)
			JOIN
				public.tbl_studiengangstyp USING (typ)
			JOIN
				lehre.tbl_studienplan ON (tbl_prestudentstatus.studienplan_id = tbl_studienplan.studienplan_id)
			JOIN
				bis.tbl_orgform ON (tbl_studienplan.orgform_kurzbz = tbl_orgform.orgform_kurzbz)
			WHERE
				tbl_prestudent.person_id = (
				SELECT
						person_id
					FROM
						public.tbl_prestudent
					WHERE
						prestudent_id = ". $this->db_add_param($prestudent_id). "
				)

			/* Filter only future studiensemester (incl. actual one) */
			AND
				studiensemester_kurzbz IN (
			SELECT
						studiensemester_kurzbz
					FROM
						public.tbl_studiensemester
					WHERE
						ende > now()
				)

			AND
				status_kurzbz = 'Interessent'";

			if (!is_null($typ) && is_string($typ))
			{
				$qry .= "
				 	AND tbl_studiengang.typ = ". $this->db_add_param($typ);
			}

			if (!is_null($studiengang_kz) && is_numeric($studiengang_kz))
			{
				$qry .= "
				 	AND tbl_studiengang.studiengang_kz = ". $this->db_add_param($studiengang_kz);
			}

			$qry .= "
			  -- Order to get the very last status and highest prio on top
			 ORDER BY
				priorisierung NULLS LAST,
				prestudent_id,
				datum DESC,
				tbl_prestudentstatus.insertamum DESC,
				tbl_prestudentstatus.ext_id DESC
			" ;

			if ($prio)
			{
				$qry .= "
				 LIMIT 1
				";
			}

			//echo "<br>". $qry;

			if($this->db_query($qry))
			{
				while($row = $this->db_fetch_object())
				{
					$obj = new stdClass();

					$obj->prestudent_id = $row->prestudent_id;
					$obj->studienplan_id = $row->studienplan_id;
					$obj->studiengang_kz = $row->studiengang_kz;
					$obj->typ = $row->typ;
					$obj->typ_bz = $row->typ_bz;
					$obj->ausbildungssemester = $row->ausbildungssemester;
					$obj->orgform_bezeichnung = $this->db_parse_lang_array($row->bezeichnung_mehrsprachig);

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
		else
		{
			$this->errormsg = 'prestudent_id muss numerisch sein.';
			return false;
		}
	}


	/**
	 * Laedt die Rolle(n) eines Prestudenten
	 */
	public function getPrestudentRolle($prestudent_id, $status_kurzbz=null, $studiensemester_kurzbz=null, $order="datum, insertamum", $ausbildungssemester=null)
	{
		if(!is_numeric($prestudent_id))
		{
			$this->errormsg = 'Prestudent_id muss eine gueltige Zahl sein';
			return false;
		}

		$qry = "SELECT
					tbl_prestudentstatus.*, tbl_studienplan.bezeichnung as studienplan_bezeichnung
				FROM public.tbl_prestudentstatus
					LEFT JOIN lehre.tbl_studienplan USING(studienplan_id)
				WHERE
					prestudent_id=".$this->db_add_param($prestudent_id, FHC_INTEGER);
		if($status_kurzbz!=null)
			$qry.= " AND status_kurzbz=".$this->db_add_param($status_kurzbz);
		if($studiensemester_kurzbz!=null)
			$qry.= " AND studiensemester_kurzbz=".$this->db_add_param($studiensemester_kurzbz);
		if($ausbildungssemester!=null)
			$qry.= " AND ausbildungssemester=".$this->db_add_param($ausbildungssemester);

		if($order!='')
			$qry.=" ORDER BY ".$order;

		if($this->db_query($qry))
		{
			$this->num_rows=0;

			while($row = $this->db_fetch_object())
			{
				$rolle = new prestudent();

				$rolle->prestudent_id = $row->prestudent_id;
				$rolle->status_kurzbz = $row->status_kurzbz;
				$rolle->studiensemester_kurzbz = $row->studiensemester_kurzbz;
				$rolle->ausbildungssemester = $row->ausbildungssemester;
				$rolle->datum = $row->datum;
				$rolle->insertamum = $row->insertamum;
				$rolle->insertvon = $row->insertvon;
				$rolle->updateamum = $row->updateamum;
				$rolle->updatevon = $row->updatevon;
				$rolle->orgform_kurzbz = $row->orgform_kurzbz;
				$rolle->studienplan_id = $row->studienplan_id;
				$rolle->studienplan_bezeichnung = $row->studienplan_bezeichnung;
				$rolle->bestaetigtam = $row->bestaetigtam;
				$rolle->bestaetigtvon = $row->bestaetigtvon;
				$rolle->anmerkung_status = $row->anmerkung;
				$rolle->bewerbung_abgeschicktamum = $row->bewerbung_abgeschicktamum;
				$rolle->rt_stufe = $row->rt_stufe;
				$rolle->statusgrund_id = $row->statusgrund_id;
				$this->result[] = $rolle;
				$this->num_rows++;
			}
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der PrestudentDaten';
			return false;
		}
	}

	/**
	 * Laedt die Rolle
	 *
	 * @param $prestudent_id
	 * @param $status_kurzbz
	 * @param $studiensemester_kurzbz
	 * @param $ausbildungssemester
	 * @return boolean
	 */
	public function load_rolle($prestudent_id, $status_kurzbz, $studiensemester_kurzbz, $ausbildungssemester)
	{
		if(!is_numeric($prestudent_id) || $prestudent_id=='')
		{
			$this->errormsg = 'Prestudent_id muss eine gueltige Zahl sein';
			return false;
		}

		$qry = "SELECT * FROM public.tbl_prestudentstatus WHERE prestudent_id=".$this->db_add_param($prestudent_id).
			   " AND status_kurzbz=".$this->db_add_param($status_kurzbz).
			   " AND studiensemester_kurzbz=".$this->db_add_param($studiensemester_kurzbz).
			   " AND ausbildungssemester=".$this->db_add_param($ausbildungssemester);

		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				$this->prestudent_id = $row->prestudent_id;
				$this->status_kurzbz = $row->status_kurzbz;
				$this->studiensemester_kurzbz = $row->studiensemester_kurzbz;
				$this->ausbildungssemester = $row->ausbildungssemester;
				$this->datum = $row->datum;
				$this->insertamum = $row->insertamum;
				$this->insertvon = $row->insertvon;
				$this->updateamum = $row->updateamum;
				$this->updatevon = $row->updatevon;
				$this->ext_id_prestudent = $row->ext_id;
				$this->orgform_kurzbz = $row->orgform_kurzbz;
				$this->studienplan_id = $row->studienplan_id;
				$this->bestaetigtam = $row->bestaetigtam;
				$this->bestaetigtvon = $row->bestaetigtvon;
				$this->anmerkung_status = $row->anmerkung;
				$this->bewerbung_abgeschicktamum = $row->bewerbung_abgeschicktamum;
				$this->statusgrund_id = $row->statusgrund_id;
				$this->rt_stufe = $row->rt_stufe;

				return true;
			}
			else
			{
				$this->errormsg = 'Rolle existiert nicht';
				return false;
			}
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der PrestudentDaten';
			return false;
		}
	}

	/**
	 * Laedt die Interessenten und Bewerber fuer ein bestimmtes Studiensemester
	 *
	 * @param string $studiensemester_kurzbz Studiensemester fuer das die Int. und Bewerber geladen werden sollen.
	 * @param integer $studiengang_kz Kennzahl des Studiengangs.
	 * @param integer $semester Ausbildungssemester.
	 * @param string $typ Filter fuer Typ von Interessenten/Bewerber
	 * @param string $orgform Organisationsform.
	 * @return boolean true wenn erfolgreich, false im Fehlerfall
	 */
	public function loadInteressentenUndBewerber($studiensemester_kurzbz, $studiengang_kz, $semester=null, $typ=null, $orgform=null)
	{
		$stsemqry='';
		if(!is_null($studiensemester_kurzbz) && $studiensemester_kurzbz!='')
			$stsemqry=" AND studiensemester_kurzbz=".$this->db_add_param($studiensemester_kurzbz);

		$qry = "SELECT
					*, a.anmerkung, tbl_person.anmerkung as anmerkungen
				FROM
					(
						SELECT
							*, (SELECT status_kurzbz FROM tbl_prestudentstatus
								WHERE prestudent_id=prestudent.prestudent_id $stsemqry
								ORDER BY datum DESC, insertamum DESC, ext_id DESC LIMIT 1) AS rolle
						FROM tbl_prestudent prestudent ORDER BY prestudent_id
					) a, tbl_prestudentstatus, tbl_person
				WHERE a.rolle=tbl_prestudentstatus.status_kurzbz AND
					a.person_id=tbl_person.person_id AND
					a.prestudent_id = tbl_prestudentstatus.prestudent_id AND
					a.studiengang_kz=".$this->db_add_param($studiengang_kz);

		if(!is_null($studiensemester_kurzbz) && $studiensemester_kurzbz!='')
			$qry.=" AND studiensemester_kurzbz=".$this->db_add_param($studiensemester_kurzbz);

		if($semester!=null)
			$qry.=" AND ausbildungssemester=".$this->db_add_param($semester);
		if($orgform!=null && $orgform!='')
			$qry.=" AND tbl_prestudentstatus.orgform_kurzbz=".$this->db_add_param($orgform);

		switch ($typ)
		{
			case "interessenten":
				$qry.=" AND a.rolle='Interessent'";
				break;
			case "bewerbungnichtabgeschickt":
				$qry.=" AND a.rolle='Interessent' AND bewerbung_abgeschicktamum is null";
				break;
			case "bewerbungabgeschickt":
				$qry.=" AND a.rolle='Interessent' AND bewerbung_abgeschicktamum is not null AND bestaetigtam is null";
				break;
			case "statusbestaetigt":
				$qry.=" AND a.rolle='Interessent' AND bestaetigtam is not null";
				break;
			case "zgv":
				$stg_obj = new studiengang();
				$stg_obj->load($studiengang_kz);
				if($stg_obj->typ=='m')
					$qry.=" AND a.rolle='Interessent' AND a.zgvmas_code is not null";
				else
					$qry.=" AND a.rolle='Interessent' AND a.zgv_code is not null";
				break;
			case "reihungstestangemeldet":
				$qry.="
					AND a.rolle='Interessent'
					AND EXISTS(
						SELECT
							1
						FROM
							public.tbl_rt_person
							JOIN public.tbl_reihungstest ON(rt_id = reihungstest_id)
						WHERE
							person_id=a.person_id
							AND studienplan_id IN(
								SELECT studienplan_id FROM lehre.tbl_studienplan
								JOIN lehre.tbl_studienordnung USING(studienordnung_id)
								WHERE tbl_studienordnung.studiengang_kz=a.studiengang_kz)
							AND tbl_reihungstest.studiensemester_kurzbz=".$this->db_add_param($studiensemester_kurzbz)."
						)";
				break;
			case "reihungstestnichtangemeldet":
				$qry.=" AND a.rolle='Interessent'
					AND NOT EXISTS(SELECT 1 FROM public.tbl_rt_person
					WHERE
						person_id=a.person_id
						AND studienplan_id IN(
							SELECT studienplan_id FROM lehre.tbl_studienplan
							JOIN lehre.tbl_studienordnung USING(studienordnung_id)
							WHERE tbl_studienordnung.studiengang_kz=a.studiengang_kz)
					)";
				break;
			case "bewerber":
				$qry.=" AND a.rolle='Bewerber'";
				break;
			case "aufgenommen":
				$qry.=" AND a.rolle='Aufgenommener'";
				break;
			case "warteliste":
				$qry.=" AND a.rolle='Wartender'";
				break;
			case "absage":
				$qry.=" AND a.rolle='Abgewiesener'";
				break;
			case "prestudent":
				if($studiensemester_kurzbz=='' || is_null($studiensemester_kurzbz))
					$qry = "SELECT *, '' as status_kurzbz, '' as studiensemester_kurzbz, '' as ausbildungssemester, '' as datum, tbl_person.anmerkung as anmerkungen, '' as orgform_kurzbz FROM public.tbl_prestudent prestudent, public.tbl_person WHERE NOT EXISTS (select * from tbl_prestudentstatus WHERE prestudent_id=prestudent.prestudent_id) AND studiengang_kz=".$this->db_add_param($studiengang_kz, FHC_INTEGER)." AND prestudent.person_id=tbl_person.person_id";
				else
					$qry .= " AND a.rolle IN('Interessent', 'Bewerber', 'Aufgenommener', 'Wartender', 'Abgewiesener')";
				break;
			case "absolvent":
				$qry.=" AND a.rolle='Absolvent'";
				break;
			case "diplomand":
				$qry.=" AND a.rolle='Diplomand'";
				break;
			default:
				break;
		}

		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$ps = new prestudent();

				$ps->person_id = $row->person_id;
				$ps->staatsbuergerschaft = $row->staatsbuergerschaft;
				$ps->gebnation = $row->geburtsnation;
				$ps->sprache = $row->sprache;
				$ps->anrede = $row->anrede;
				$ps->titelpost = $row->titelpost;
				$ps->titelpre = $row->titelpre;
				$ps->nachname = $row->nachname;
				$ps->vorname = $row->vorname;
				$ps->vornamen = $row->vornamen;
				$ps->gebdatum = $row->gebdatum;
				$ps->gebort = $row->gebort;
				$ps->gebzeit = $row->gebzeit;
				//$ps->foto = $row->foto;
				$ps->anmerkungen = $row->anmerkungen;
				$ps->homepage = $row->homepage;
				$ps->svnr = $row->svnr;
				$ps->ersatzkennzeichen = $row->ersatzkennzeichen;
				$ps->familienstand = $row->familienstand;
				$ps->geschlecht = $row->geschlecht;
				$ps->anzahlkinder = $row->anzahlkinder;
				$ps->aktiv = $this->db_parse_bool($row->aktiv);

				$ps->prestudent_id = $row->prestudent_id;
				$ps->aufmerksamdurch_kurzbz = $row->aufmerksamdurch_kurzbz;
				$ps->studiengang_kz = $row->studiengang_kz;
				$ps->berufstaetigkeit_code = $row->berufstaetigkeit_code;
				$ps->ausbildungcode = $row->ausbildungcode;
				$ps->zgv_code = $row->zgv_code;
				$ps->zgvort = $row->zgvort;
				$ps->zgvdatum = $row->zgvdatum;
				$ps->zgvnation = $row->zgvnation;
				$ps->zgvmas_code = $row->zgvmas_code;
				$ps->zgvmaort = $row->zgvmaort;
				$ps->zgvmadatum = $row->zgvmadatum;
				$ps->zgvmanation = $row->zgvmanation;
				$ps->ausstellungsstaat = $row->ausstellungsstaat;
				$ps->aufnahmeschluessel = $row->aufnahmeschluessel;
				$ps->facheinschlberuf = $this->db_parse_bool($row->facheinschlberuf);
				$ps->anmeldungreihungstest = $row->anmeldungreihungstest;
				$ps->reihungstestangetreten = $this->db_parse_bool($row->reihungstestangetreten);
				$ps->reihungstest_id = $row->reihungstest_id;
				$ps->punkte = $row->rt_gesamtpunkte;
				$ps->rt_punkte1 = $row->rt_punkte1;
				$ps->rt_punkte2 = $row->rt_punkte2;
				$ps->rt_punkte3 = $row->rt_punkte3;
				$ps->bismelden = $this->db_parse_bool($row->bismelden);
				$ps->anmerkung = $row->anmerkung;
				$ps->dual = $this->db_parse_bool($row->dual);
				$ps->gsstudientyp_kurzbz = $row->gsstudientyp_kurzbz;
				$ps->aufnahmegruppe_kurzbz = $row->aufnahmegruppe_kurzbz;
				$ps->priorisierung = $row->priorisierung;

				$ps->status_kurzbz = $row->status_kurzbz;
				$ps->studiensemester_kurzbz = $row->studiensemester_kurzbz;
				$ps->ausbildungssemester = $row->ausbildungssemester;
				$ps->datum = $row->datum;
				$ps->orgform_kurzbz = $row->orgform_kurzbz;

				$this->result[] = $ps;
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
	 * Prueft ob eine Person bereits einen PreStudenteintrag
	 * fuer einen Studiengang und optional ein Studiensemester besitzt
	 * @param integer $person_id
	 * @param integer $studiengang_kz
	 * @return true wenn vorhanden
	 *		 false wenn nicht vorhanden
	 *		 false und errormsg wenn Fehler aufgetreten ist
	 */
	public function exists($person_id, $studiengang_kz)
	{
		if(!is_numeric($person_id))
		{
			$this->errormsg = 'Person_id muss eine gueltige Zahl sein';
			return false;
		}

		if(!is_numeric($studiengang_kz))
		{
			$this->errormsg = 'Studiengang_kz muss eine gueltige Zahl sein';
			return false;
		}

		$qry = "SELECT count(*) as anzahl FROM public.tbl_prestudent
				WHERE person_id=".$this->db_add_param($person_id, FHC_INTEGER)."
				AND studiengang_kz=".$this->db_add_param($studiengang_kz, FHC_INTEGER);

		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				if($row->anzahl>0)
				{
					$this->errormsg = '';
					return true;
				}
				else
				{
					$this->errormsg = '';
					return false;
				}
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
	 * Prueft ob eine Person bereits einen PreStudentstatus-Eintrag
	 * fuer einen Studiengang, optional ein Studiensemester, optional eine Status_kurzbz und optional einen Studienplan besitzt
	 * @param integer $person_id
	 * @param integer $studiengang_kz
	 * @param string $studiensemester_kurzbz Optional.
	 * @param string $status_kurzbz Optional.
	 * @param integer $studienplan_id Optional.
	 * @return true wenn vorhanden
	 *		 false wenn nicht vorhanden
	 *		 false und errormsg wenn Fehler aufgetreten ist
	 */
	public function existsPrestudentstatus($person_id, $studiengang_kz, $studiensemester_kurzbz = null, $status_kurzbz = null, $studienplan_id = null)
	{
		if(!is_numeric($person_id))
		{
			$this->errormsg = 'Person_id muss eine gueltige Zahl sein';
			return false;
		}

		if(!is_numeric($studiengang_kz))
		{
			$this->errormsg = 'Studiengang_kz muss eine gueltige Zahl sein';
			return false;
		}

		$qry = "SELECT count(*) as anzahl FROM public.tbl_prestudent
				JOIN public.tbl_prestudentstatus USING (prestudent_id)
				WHERE person_id=".$this->db_add_param($person_id, FHC_INTEGER)."
				AND studiengang_kz=".$this->db_add_param($studiengang_kz, FHC_INTEGER);

		if ($studiensemester_kurzbz != '')
			$qry .= " AND studiensemester_kurzbz=".$this->db_add_param($studiensemester_kurzbz);

		if ($status_kurzbz != '')
			$qry .= " AND status_kurzbz=".$this->db_add_param($status_kurzbz);

		if ($studienplan_id != '')
			$qry .= " AND studienplan_id=".$this->db_add_param($studienplan_id);

		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				if($row->anzahl>0)
				{
					$this->errormsg = '';
					return true;
				}
				else
				{
					$this->errormsg = '';
					return false;
				}
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
	 * Speichert den Prestudentstatus
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function save_rolle()
	{
		global $p;
		if($this->new)
		{
			//pruefen ob die Rolle schon vorhanden ist
			if($this->load_rolle($this->prestudent_id, $this->status_kurzbz, $this->studiensemester_kurzbz, $this->ausbildungssemester))
			{
				//$this->errormsg = 'Diese Rolle existiert bereits';
				$this->errormsg = $p->t('errors/rolleExistiertBereits');
				return false;
			}

			$qry = 'INSERT INTO public.tbl_prestudentstatus (prestudent_id, status_kurzbz,
					studiensemester_kurzbz, ausbildungssemester, datum, insertamum, insertvon,
					updateamum, updatevon, ext_id, orgform_kurzbz, bestaetigtam, bestaetigtvon, anmerkung,
					bewerbung_abgeschicktamum, studienplan_id, rt_stufe, statusgrund_id) VALUES('.
				   $this->db_add_param($this->prestudent_id).",".
				   $this->db_add_param($this->status_kurzbz).",".
				   $this->db_add_param($this->studiensemester_kurzbz).",".
				   $this->db_add_param($this->ausbildungssemester).",".
				   $this->db_add_param($this->datum).",".
				   $this->db_add_param($this->insertamum).",".
				   $this->db_add_param($this->insertvon).",".
				   $this->db_add_param($this->updateamum).",".
				   $this->db_add_param($this->updatevon).",".
				   $this->db_add_param($this->ext_id_prestudent).",".
				   $this->db_add_param($this->orgform_kurzbz).",".
				   $this->db_add_param($this->bestaetigtam).",".
				   $this->db_add_param($this->bestaetigtvon).",".
				   $this->db_add_param($this->anmerkung_status).",".
				   $this->db_add_param($this->bewerbung_abgeschicktamum).",".
				   $this->db_add_param($this->studienplan_id,FHC_INTEGER).",".
				   $this->db_add_param($this->rt_stufe,FHC_INTEGER).",".
					$this->db_add_param($this->statusgrund_id, FHC_INTEGER).");";
		}
		else
		{
			if($this->studiensemester_old=='')
				$this->studiensemester_old = $this->studiensemester_kurzbz;
			if($this->ausbildungssemester_old=='')
				$this->ausbildungssemester_old = $this->ausbildungssemester;

			//wenn der PrimaryKey geaendert wird, schauen ob schon ein Eintrag mit diesem Key vorhanden ist
			if($this->studiensemester_old!=$this->studiensemester_kurzbz || $this->ausbildungssemester_old!=$this->ausbildungssemester)
			{
				if($this->load_rolle($this->prestudent_id, $this->status_kurzbz, $this->studiensemester_kurzbz, $this->ausbildungssemester))
				{
					//$this->errormsg = 'Diese Rolle existiert bereits';
					$this->errormsg = $p->t('errors/rolleExistiertBereits');
					return false;
				}
			}
			$qry = 'UPDATE public.tbl_prestudentstatus SET'.
				   ' ausbildungssemester='.$this->db_add_param($this->ausbildungssemester).",".
				   ' studiensemester_kurzbz='.$this->db_add_param($this->studiensemester_kurzbz).",".
				   ' datum='.$this->db_add_param($this->datum).",".
				   ' updateamum='.$this->db_add_param($this->updateamum).",".
				   ' updatevon='.$this->db_add_param($this->updatevon).",".
				   ' bestaetigtam='.$this->db_add_param($this->bestaetigtam).",".
				   ' bestaetigtvon='.$this->db_add_param($this->bestaetigtvon).",".
				   ' bewerbung_abgeschicktamum='.$this->db_add_param($this->bewerbung_abgeschicktamum).",".
				   ' studienplan_id='.$this->db_add_param($this->studienplan_id, FHC_INTEGER).",".
				   ' anmerkung='.$this->db_add_param($this->anmerkung_status).",".
				   ' orgform_kurzbz='.$this->db_add_param($this->orgform_kurzbz).",".
				   ' rt_stufe='.$this->db_add_param($this->rt_stufe).",".
				   ' statusgrund_id='.$this->db_add_param($this->statusgrund_id, FHC_INTEGER)." ".
				   " WHERE
						prestudent_id=".$this->db_add_param($this->prestudent_id, FHC_INTEGER, false)."
						AND status_kurzbz=".$this->db_add_param($this->status_kurzbz, FHC_STRING, false)."
						AND studiensemester_kurzbz=".$this->db_add_param($this->studiensemester_old, FHC_STRING, false)."
						AND ausbildungssemester=".$this->db_add_param($this->ausbildungssemester_old, FHC_STRING, false).";";
		}

		if($this->db_query($qry))
		{
			//Log schreiben
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern des Prestudentstatus';
			return false;
		}
	}

	/**
	 * Loescht einen Prestudentstatus und legt einen Log-Eintrag dafuer an
	 * @param integer $prestudent_id
	 * @param string $status_kurzbz
	 * @param string $studiensemester_kurzbz
	 * @param integer $ausbildungssemester
	 * @return true wenn ok, false wenn Fehler
	 */
	public function delete_rolle($prestudent_id, $status_kurzbz, $studiensemester_kurzbz, $ausbildungssemester)
	{
		if(!is_numeric($prestudent_id))
		{
			$this->errormsg = 'Prestudent_id ist ungueltig';
			return false;
		}

		$qry = "DELETE FROM public.tbl_prestudentstatus
				WHERE
					prestudent_id=".$this->db_add_param($prestudent_id, FHC_INTEGER)."
					AND status_kurzbz=".$this->db_add_param($status_kurzbz)."
					AND studiensemester_kurzbz=".$this->db_add_param($studiensemester_kurzbz)."
					AND ausbildungssemester=".$this->db_add_param($ausbildungssemester);

		if($this->load_rolle($prestudent_id, $status_kurzbz, $studiensemester_kurzbz, $ausbildungssemester))
		{
			$this->db_query('BEGIN;');

			$log = new log();

			$log->executetime = date('Y-m-d H:i:s');
			$log->beschreibung = 'Loeschen der Rolle '.$status_kurzbz.' bei '.$prestudent_id;
			$log->mitarbeiter_uid = get_uid();
			$log->sql = $qry;
			$log->sqlundo = 'INSERT INTO public.tbl_prestudentstatus(prestudent_id, status_kurzbz, studiensemester_kurzbz,'
							. ' ausbildungssemester, datum, insertamum, insertvon, updateamum, updatevon, ext_id, orgform_kurzbz,'
							. ' bestaetigtam, bestaetigtvon, anmerkung, bewerbung_abgeschicktamum, studienplan_id, '
							. ' rt_stufe, statusgrund_id) VALUES('.
							$this->db_add_param($this->prestudent_id).','.
							$this->db_add_param($this->status_kurzbz).','.
							$this->db_add_param($this->studiensemester_kurzbz).','.
							$this->db_add_param($this->ausbildungssemester).','.
							$this->db_add_param($this->datum).','.
							$this->db_add_param($this->insertamum).','.
							$this->db_add_param($this->insertvon).','.
							$this->db_add_param($this->updateamum).','.
							$this->db_add_param($this->updatevon).','.
							$this->db_add_param($this->ext_id_prestudent).','.
							$this->db_add_param($this->orgform_kurzbz).','.
							$this->db_add_param($this->bestaetigtam).','.
							$this->db_add_param($this->bestaetigtvon).','.
							$this->db_add_param($this->anmerkung_status).','.
							$this->db_add_param($this->bewerbung_abgeschicktamum).','.
							$this->db_add_param($this->studienplan_id, FHC_INTEGER).','.
							$this->db_add_param($this->rt_stufe, FHC_INTEGER).','.
							$this->db_add_param($this->statusgrund_id, FHC_INTEGER).');';
			if($log->save(true))
			{
				if($this->db_query($qry))
				{
					$this->db_query('COMMIT');
					$this->log_id = $log->log_id;
					return true;
				}
				else
				{
					$this->db_query('ROLLBACK');
					$this->errormsg = 'Fehler beim Loeschen der Daten';
					return false;
				}
			}
			else
			{
				$this->db_query('ROLLBACK');
				$this->errormsg = 'Fehler beim Speichern des Log-Eintrages';
				return false;
			}
		}
		else
		{
			return false;
		}
	}

	public function bestaetige_rolle($prestudent_id, $status_kurzbz, $studiensemester_kurzbz, $ausbildungssemester, $user)
	{
		if(!is_numeric($prestudent_id))
		{
			$this->errormsg = 'Prestudent_id ist ungueltig';
			return false;
		}

	$qry = 'UPDATE public.tbl_prestudentstatus SET'.
				' bestaetigtam='.$this->db_add_param(date('Y-m-d')).','.
				' bestaetigtvon='.$this->db_add_param($user).", ".
				' updateamum='.$this->db_add_param(date('Y-m-d H:i:s')).','.
				' updatevon='.$this->db_add_param($user)." ".
				' WHERE
					prestudent_id='.$this->db_add_param($prestudent_id, FHC_INTEGER).'
					AND status_kurzbz='.$this->db_add_param($status_kurzbz).'
					AND studiensemester_kurzbz='.$this->db_add_param($studiensemester_kurzbz).'
					AND ausbildungssemester='.$this->db_add_param($ausbildungssemester);

		if($this->db_query($qry))
		{
			return true;
		}
		else
		{
			$this->errormsg='Fehler beim Speichern der Daten';
			return false;
		}
	}

	/**
	 * Liefert den Letzten Status eines Prestudenten in einem Studiensemester
	 * Wenn kein Studiensemester angegeben wird, wird der letztgueltige Status ermittelt
	 * @param $prestudent_id
	 * @param $studiensemester_kurzbz
	 * @return boolean
	 */
	public function getLastStatus($prestudent_id, $studiensemester_kurzbz='', $status_kurzbz = '')
	{
		if($prestudent_id=='' || !is_numeric($prestudent_id))
		{
			$this->errormsg = 'Prestudent_id ist ungueltig';
			return false;
		}

		$qry = "SELECT tbl_prestudentstatus.*, bezeichnung AS studienplan_bezeichnung,
				tbl_status.bezeichnung_mehrsprachig
				FROM public.tbl_prestudentstatus
				LEFT JOIN lehre.tbl_studienplan USING (studienplan_id)
				JOIN public.tbl_status USING (status_kurzbz)
				WHERE tbl_status.status_kurzbz = tbl_prestudentstatus.status_kurzbz
				AND prestudent_id=".$this->db_add_param($prestudent_id, FHC_INTEGER);

		if($studiensemester_kurzbz!='')
			$qry.=" AND studiensemester_kurzbz=".$this->db_add_param($studiensemester_kurzbz);

		if($status_kurzbz !='')
			$qry.= " AND status_kurzbz =".$this->db_add_param($status_kurzbz);

		$qry.=" ORDER BY datum DESC, insertamum DESC, ext_id DESC LIMIT 1";
		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				$this->prestudent_id = $row->prestudent_id;
				$this->status_kurzbz = $row->status_kurzbz;
				$this->status_mehrsprachig = $this->db_parse_lang_array($row->bezeichnung_mehrsprachig);
				$this->studiensemester_kurzbz = $row->studiensemester_kurzbz;
				$this->ausbildungssemester = $row->ausbildungssemester;
				$this->datum = $row->datum;
				$this->insertamum = $row->insertamum;
				$this->insertvon = $row->insertvon;
				$this->updateamum = $row->updateamum;
				$this->updatevon = $row->updatevon;
				$this->bestaetigtam = $row->bestaetigtam;
				$this->bestaetigtvon = $row->bestaetigtvon;
				$this->bewerbung_abgeschicktamum = $row->bewerbung_abgeschicktamum;
				$this->orgform_kurzbz = $row->orgform_kurzbz;
				$this->studienplan_id = $row->studienplan_id;
				$this->studienplan_bezeichnung = $row->studienplan_bezeichnung;
				$this->rt_stufe = $row->rt_stufe;
				$this->statusgrund_id = $row->statusgrund_id;
				$this->anmerkung = $row->anmerkung;
				return true;
			}
			else
			{
				$this->errormsg = 'Keine Rolle vorhanden';
				return false;
			}
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der PrestudentDaten';
			return false;
		}
	}

	/**
	 * Liefert den Ersten Status eines Prestudenten mit der übergebenen Statuskurzbezeichnung
	 * @param $prestudent_id
	 * @param $studiensemester_kurzbz
	 * @return boolean
	 */
	public function getFirstStatus($prestudent_id, $status_kurzbz)
	{
		if($prestudent_id=='' || !is_numeric($prestudent_id))
		{
			$this->errormsg = 'Prestudent_id ist ungueltig';
			return false;
		}

		$qry = "SELECT * FROM public.tbl_prestudentstatus
				WHERE
					prestudent_id=".$this->db_add_param($prestudent_id, FHC_INTEGER)."
					AND status_kurzbz = ".$this->db_add_param($status_kurzbz)."
				ORDER BY datum ASC, insertamum ASC, ext_id ASC LIMIT 1";
		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				$this->prestudent_id = $row->prestudent_id;
				$this->status_kurzbz = $row->status_kurzbz;
				$this->studiensemester_kurzbz = $row->studiensemester_kurzbz;
				$this->ausbildungssemester = $row->ausbildungssemester;
				$this->datum = $row->datum;
				$this->insertamum = $row->insertamum;
				$this->insertvon = $row->insertvon;
				$this->updateamum = $row->updateamum;
				$this->updatevon = $row->updatevon;
				$this->bestaetigtam = $row->bestaetigtam;
				$this->bestaetigtvon = $row->bestaetigtvon;
				$this->bewerbung_abgeschicktamum = $row->bewerbung_abgeschicktamum;
				$this->orgform_kurzbz = $row->orgform_kurzbz;
				$this->studienplan_id = $row->studienplan_id;
				$this->rt_stufe = $row->rt_stufe;
				$this->statusgrund_id = $row->statusgrund_id;
				return true;
			}
			else
			{
				$this->errormsg = 'Keine Rolle vorhanden';
				return false;
			}
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der PrestudentDaten';
			return false;
		}
	}

	/**
	 * Laedt alle Prestudenten der Person
	 * @param integer $person_id
	 * @return true wenn ok, false wenn Fehler
	 */
	public function getPrestudenten($person_id)
	{
		if(!is_numeric($person_id) || $person_id=='')
		{
			$this->errormsg='ID ist ungueltig';
			return false;
		}

		$qry = "SELECT
					*
				FROM
					public.tbl_prestudent
				WHERE
					person_id=".$this->db_add_param($person_id, FHC_INTEGER)."
				ORDER BY prestudent_id";

		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$obj = new prestudent();

				$obj->prestudent_id = $row->prestudent_id;
				$obj->aufmerksamdurch_kurzbz = $row->aufmerksamdurch_kurzbz;
				$obj->studiengang_kz = $row->studiengang_kz;
				$obj->berufstaetigkeit_code = $row->berufstaetigkeit_code;
				$obj->ausbildungcode = $row->ausbildungcode;
				$obj->zgv_code = $row->zgv_code;
				$obj->zgvort = $row->zgvort;
				$obj->zgvdatum = $row->zgvdatum;
				$obj->zgvnation = $row->zgvnation;
				$obj->zgvmas_code = $row->zgvmas_code;
				$obj->zgvmaort = $row->zgvmaort;
				$obj->zgvmadatum = $row->zgvmadatum;
				$obj->zgvmanation = $row->zgvmanation;
				$obj->aufnahmeschluessel = $row->aufnahmeschluessel;
				$obj->facheinschlberuf = $this->db_parse_bool($row->facheinschlberuf);
				$obj->anmeldungreihungstest = $row->anmeldungreihungstest;
				$obj->reihungstestangetreten = $this->db_parse_bool($row->reihungstestangetreten);
				$obj->reihungstest_id = $row->reihungstest_id;
				$obj->punkte = $row->rt_gesamtpunkte;
				$obj->rt_punkte1 = $row->rt_punkte1;
				$obj->rt_punkte2 = $row->rt_punkte2;
				$obj->rt_punkte3 = $row->rt_punkte3;
				$obj->bismelden = $this->db_parse_bool($row->bismelden);
				$obj->person_id = $row->person_id;
				$obj->anmerkung = $row->anmerkung;
				$obj->mentor = $row->mentor;
				$obj->ext_id_prestudent = $row->ext_id;
				$obj->dual = $this->db_parse_bool($row->dual);
				$obj->ausstellungsstaat = $row->ausstellungsstaat;
				$obj->zgvdoktor_code = $row->zgvdoktor_code;
				$obj->zgvdoktorort = $row->zgvdoktorort;
				$obj->zgvdoktordatum = $row->zgvdoktordatum;
				$obj->zgvdoktornation = $row->zgvdoktornation;
				$obj->gsstudientyp_kurzbz = $row->gsstudientyp_kurzbz;
				$obj->aufnahmegruppe_kurzbz = $row->aufnahmegruppe_kurzbz;
				$obj->priorisierung = $row->priorisierung;

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
	 * Gibt die eingetragenen ZGV zurück
	 * @return array
	 */
	public function getZgv()
	{

		$zgv = array
		(
			'bachelor' => array(),
			'master' => array(),
			//'doktor' => array(),
		);
		$attribute = array
		(
			'art',
			'ort',
			'datum',
			'nation',
		);
		$db_attribute = array
		(
			'zgv_code',
			'zgvort',
			'zgvdatum',
			'zgvnation',
			'zgvmas_code',
			'zgvmaort',
			'zgvmadatum',
			'zgvmanation',
			'zgvdoktor_code',
			'zgvdoktorort',
			'zgvdoktordatum',
			'zgvdoktornation',
		);

		foreach($this->result as $prestudent)
		{
			foreach($zgv as &$value)
			{
				foreach($attribute as $attribut)
				{
					$db_attribute_name = current($db_attribute);
					if($prestudent->$db_attribute_name)
					{
						$value[$attribut] = $prestudent->$db_attribute_name;
					}
					next($db_attribute);
				}
			}
			reset($db_attribute);
		}
	return $zgv;
	}

	/**
	 * Liefert die Anzahl der Bewerber im ausgewaehlten Bereich
	 * @param string $studiensemester_kurzbz Studiensemester
	 * @param integer $studiengang_kz Kennzahl des Studienganges (optional)
	 * @param string $orgform_kurzbz Organisationsform (optional)
	 * @param integer $ausbildungssemester Ausbildungssemester (optional)
	 * @return integer Anzahl der Bewerber oder false im Fehlerfall
	 */
	public function getAnzBewerber($studiensemester_kurzbz, $studiengang_kz=null, $orgform_kurzbz=null, $ausbildungssemester=null)
	{
		$qry = "SELECT
					count(*) as anzahl
				FROM
					public.tbl_prestudent
					JOIN public.tbl_prestudentstatus USING(prestudent_id)
				WHERE
					tbl_prestudentstatus.status_kurzbz='Bewerber'
					AND tbl_prestudentstatus.studiensemester_kurzbz=".$this->db_add_param($studiensemester_kurzbz);

		if(!is_null($studiengang_kz))
			$qry.=" AND tbl_prestudent.studiengang_kz=".$this->db_add_param($studiengang_kz, FHC_INTEGER);

		if(!is_null($orgform_kurzbz))
			$qry.=" AND (tbl_prestudentstatus.orgform_kurzbz=".$this->db_add_param($orgform_kurzbz)." OR (tbl_prestudentstatus.orgform_kurzbz IS NULL AND EXISTS(SELECT 1 FROM public.tbl_studiengang WHERE studiengang_kz=tbl_prestudent.studiengang_kz AND orgform_kurzbz=".$this->db_add_param($orgform_kurzbz).")))";

		if(!is_null($ausbildungssemester))
			$qry.=" AND tbl_prestudentstatus.ausbildungssemester=".$this->db_add_param($ausbildungssemester);

		if($result = $this->db_query($qry))
		{
			if($row = $this->db_fetch_object($result))
			{
				return $row->anzahl;
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
	 * Liefert die Anzahl der Interessenten im ausgewaehlten Bereich
	 * @param string $studiensemester_kurzbz Studiensemester
	 * @param integer $studiengang_kz Kennzahl des Studienganges (optional)
	 * @param string $orgform_kurzbz Organisationsform (optional)
	 * @param integer $ausbildungssemester Ausbildungssemester (optional)
	 * @return integer Anzahl der Interessenten oder false im Fehlerfall
	 */
	public function getAnzInteressenten($studiensemester_kurzbz, $studiengang_kz=null, $orgform_kurzbz=null, $ausbildungssemester=null)
	{
		$qry = "SELECT
					count(*) as anzahl
				FROM
					public.tbl_prestudent
					JOIN public.tbl_prestudentstatus USING(prestudent_id)
				WHERE
					tbl_prestudentstatus.status_kurzbz='Interessent'
					AND tbl_prestudentstatus.studiensemester_kurzbz=".$this->db_add_param($studiensemester_kurzbz);

		if(!is_null($studiengang_kz))
			$qry.=" AND tbl_prestudent.studiengang_kz=".$this->db_add_param($studiengang_kz, FHC_INTEGER);

		if(!is_null($orgform_kurzbz))
			$qry.=" AND (tbl_prestudentstatus.orgform_kurzbz=".$this->db_add_param($orgform_kurzbz)." OR (tbl_prestudentstatus.orgform_kurzbz IS NULL AND EXISTS(SELECT 1 FROM public.tbl_studiengang WHERE studiengang_kz=tbl_prestudent.studiengang_kz AND orgform_kurzbz=".$this->db_add_param($orgform_kurzbz).")))";

		if(!is_null($ausbildungssemester))
			$qry.=" AND tbl_prestudentstatus.ausbildungssemester=".$this->db_add_param($ausbildungssemester);

		if($result = $this->db_query($qry))
		{
			if($row = $this->db_fetch_object($result))
			{
				return $row->anzahl;
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
	 * Liefert die Anzahl der Interessenten mit Zugangsvoraussetzung im ausgewaehlten Bereich
	 * @param string $studiensemester_kurzbz Studiensemester
	 * @param integer $studiengang_kz Kennzahl des Studienganges (optional)
	 * @param string $orgform_kurzbz Organisationsform (optional)
	 * @param integer $ausbildungssemester Ausbildungssemester (optional)
	 * @return integer Anzahl der Interessenten mit ZGV oder false im Fehlerfall
	 */
	public function getAnzInteressentenZGV($studiensemester_kurzbz, $studiengang_kz=null, $orgform_kurzbz=null, $ausbildungssemester=null)
	{
		$qry = "SELECT
					count(*) as anzahl
				FROM
					public.tbl_prestudent
					JOIN public.tbl_prestudentstatus USING(prestudent_id)
					JOIN public.tbl_studiengang USING(studiengang_kz)
				WHERE
					tbl_prestudentstatus.status_kurzbz='Interessent'
					AND tbl_prestudentstatus.studiensemester_kurzbz=".$this->db_add_param($studiensemester_kurzbz)."
					AND ((tbl_studiengang.typ<>'m' AND zgv_code IS NOT NULL) OR zgvmas_code IS NOT NULL)";

		if(!is_null($studiengang_kz))
			$qry.=" AND tbl_prestudent.studiengang_kz=".$this->db_add_param($studiengang_kz, FHC_INTEGER);

		if(!is_null($orgform_kurzbz))
			$qry.=" AND (tbl_prestudentstatus.orgform_kurzbz=".$this->db_add_param($orgform_kurzbz)." OR (tbl_prestudentstatus.orgform_kurzbz IS NULL AND tbl_studiengang.orgform_kurzbz=".$this->db_add_param($orgform_kurzbz)."))";

		if(!is_null($ausbildungssemester))
			$qry.=" AND tbl_prestudentstatus.ausbildungssemester=".$this->db_add_param($ausbildungssemester);

		if($result = $this->db_query($qry))
		{
			if($row = $this->db_fetch_object($result))
			{
				return $row->anzahl;
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
	 * Liefert ein Array mit den Bewerberzahlen
	 * @param $studiensemester_kurzbz (optional)
	 * @return true wenn ok, false im Fehlerfall DatenArray in $this->result
	 * Bsp:
	 * $prestudent->result[$stsem][$stg_kz]['anzahl']
	 * $prestudent->result[$stsem][$stg_kz][$orgform][$semester]['anzahl']
	 */
	public function listAnzBewerber($studiensemester_kurzbz=null)
	{
		$qry = "SELECT
					tbl_prestudentstatus.studiensemester_kurzbz,
					tbl_prestudent.studiengang_kz,
					tbl_prestudentstatus.ausbildungssemester,
					COALESCE(tbl_prestudentstatus.orgform_kurzbz, tbl_studiengang.orgform_kurzbz) as orgform_kurzbz
				FROM
					public.tbl_prestudent
					JOIN public.tbl_prestudentstatus USING(prestudent_id)
					JOIN public.tbl_studiengang USING(studiengang_kz)
				WHERE
					tbl_prestudentstatus.status_kurzbz='Bewerber'";

		if(!is_null($studiensemester_kurzbz))
			$qry.=" AND tbl_prestudentstatus.studiensemester_kurzbz=".$this->db_add_param($studiensemester_kurzbz);

		$this->result = array();
		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				// Studiensemester
				if(!isset($this->result[$row->studiensemester_kurzbz]['anzahl']))
					$this->result[$row->studiensemester_kurzbz]['anzahl']=0;

				$this->result[$row->studiensemester_kurzbz]['anzahl']++;

				// Studiengang
				if(!isset($this->result[$row->studiensemester_kurzbz][$row->studiengang_kz]['anzahl']))
					$this->result[$row->studiensemester_kurzbz][$row->studiengang_kz]['anzahl']=0;

				$this->result[$row->studiensemester_kurzbz][$row->studiengang_kz]['anzahl']++;

				// Orgform
				if(!isset($this->result[$row->studiensemester_kurzbz][$row->studiengang_kz][$row->orgform_kurzbz]['anzahl']))
					$this->result[$row->studiensemester_kurzbz][$row->studiengang_kz][$row->orgform_kurzbz]['anzahl']=0;

				$this->result[$row->studiensemester_kurzbz][$row->studiengang_kz][$row->orgform_kurzbz]['anzahl']++;

				// Ausbildungssemester
				if(!isset($this->result[$row->studiensemester_kurzbz][$row->studiengang_kz][$row->orgform_kurzbz][$row->ausbildungssemester]['anzahl']))
					$this->result[$row->studiensemester_kurzbz][$row->studiengang_kz][$row->orgform_kurzbz][$row->ausbildungssemester]['anzahl']=0;

				$this->result[$row->studiensemester_kurzbz][$row->studiengang_kz][$row->orgform_kurzbz][$row->ausbildungssemester]['anzahl']++;
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
	 * Liefert ein Array mit den Interessentenzahlen
	 * @param $studiensemester_kurzbz (optional)
	 * @return true wenn ok, false im Fehlerfall DatenArray in $this->result
	 * Bsp:
	 * $prestudent->result[$stsem][$stg_kz]['anzahl']
	 * $prestudent->result[$stsem][$stg_kz][$orgform][$semester]['anzahl']
	 */
	public function listAnzInteressenten($studiensemester_kurzbz=null)
	{
		$qry = "SELECT
					tbl_prestudentstatus.studiensemester_kurzbz,
					tbl_prestudent.studiengang_kz,
					tbl_prestudentstatus.ausbildungssemester,
					COALESCE(tbl_prestudentstatus.orgform_kurzbz, tbl_studiengang.orgform_kurzbz) as orgform_kurzbz
				FROM
					public.tbl_prestudent
					JOIN public.tbl_prestudentstatus USING(prestudent_id)
					JOIN public.tbl_studiengang USING(studiengang_kz)
				WHERE
					bismelden=true
					AND tbl_prestudentstatus.status_kurzbz='Interessent'";

		if(!is_null($studiensemester_kurzbz))
			$qry.="	AND tbl_prestudentstatus.studiensemester_kurzbz=".$this->db_add_param($studiensemester_kurzbz);

		$this->result = array();
		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				// Studiensemester
				if(!isset($this->result[$row->studiensemester_kurzbz]['anzahl']))
					$this->result[$row->studiensemester_kurzbz]['anzahl']=0;

				$this->result[$row->studiensemester_kurzbz]['anzahl']++;

				// Studiengang
				if(!isset($this->result[$row->studiensemester_kurzbz][$row->studiengang_kz]['anzahl']))
					$this->result[$row->studiensemester_kurzbz][$row->studiengang_kz]['anzahl']=0;

				$this->result[$row->studiensemester_kurzbz][$row->studiengang_kz]['anzahl']++;

				// Orgform
				if(!isset($this->result[$row->studiensemester_kurzbz][$row->studiengang_kz][$row->orgform_kurzbz]['anzahl']))
					$this->result[$row->studiensemester_kurzbz][$row->studiengang_kz][$row->orgform_kurzbz]['anzahl']=0;

				$this->result[$row->studiensemester_kurzbz][$row->studiengang_kz][$row->orgform_kurzbz]['anzahl']++;

				// Ausbildungssemester
				if(!isset($this->result[$row->studiensemester_kurzbz][$row->studiengang_kz][$row->orgform_kurzbz][$row->ausbildungssemester]['anzahl']))
					$this->result[$row->studiensemester_kurzbz][$row->studiengang_kz][$row->orgform_kurzbz][$row->ausbildungssemester]['anzahl']=0;

				$this->result[$row->studiensemester_kurzbz][$row->studiengang_kz][$row->orgform_kurzbz][$row->ausbildungssemester]['anzahl']++;
			}
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Daten';
			return false;
		}
	}


	public function listAnzAbbrecher($studiensemester_kurzbz=null)
	{
		$qry = "SELECT
					tbl_prestudentstatus.studiensemester_kurzbz,
					tbl_prestudent.studiengang_kz,
					tbl_prestudentstatus.ausbildungssemester,
					COALESCE(tbl_prestudentstatus.orgform_kurzbz, tbl_studiengang.orgform_kurzbz) as orgform_kurzbz
				FROM
					public.tbl_prestudent
					JOIN public.tbl_prestudentstatus USING(prestudent_id)
					JOIN public.tbl_studiengang USING(studiengang_kz)
				WHERE
					tbl_prestudentstatus.status_kurzbz='Abbrecher'
					AND bismelden=true";

		if(!is_null($studiensemester_kurzbz))
			$qry.=" AND tbl_prestudentstatus.studiensemester_kurzbz=".$this->db_add_param($studiensemester_kurzbz);

		$this->result = array();
		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				// Studiensemester
				if(!isset($this->result[$row->studiensemester_kurzbz]['anzahl']))
					$this->result[$row->studiensemester_kurzbz]['anzahl']=0;

				$this->result[$row->studiensemester_kurzbz]['anzahl']++;

				// Studiengang
				if(!isset($this->result[$row->studiensemester_kurzbz][$row->studiengang_kz]['anzahl']))
					$this->result[$row->studiensemester_kurzbz][$row->studiengang_kz]['anzahl']=0;

				$this->result[$row->studiensemester_kurzbz][$row->studiengang_kz]['anzahl']++;

				// Orgform
				if(!isset($this->result[$row->studiensemester_kurzbz][$row->studiengang_kz][$row->orgform_kurzbz]['anzahl']))
					$this->result[$row->studiensemester_kurzbz][$row->studiengang_kz][$row->orgform_kurzbz]['anzahl']=0;

				$this->result[$row->studiensemester_kurzbz][$row->studiengang_kz][$row->orgform_kurzbz]['anzahl']++;

				// Ausbildungssemester
				if(!isset($this->result[$row->studiensemester_kurzbz][$row->studiengang_kz][$row->orgform_kurzbz][$row->ausbildungssemester]['anzahl']))
					$this->result[$row->studiensemester_kurzbz][$row->studiengang_kz][$row->orgform_kurzbz][$row->ausbildungssemester]['anzahl']=0;

				$this->result[$row->studiensemester_kurzbz][$row->studiengang_kz][$row->orgform_kurzbz][$row->ausbildungssemester]['anzahl']++;
			}
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Daten';
			return false;
		}
	}

	public function listAnzStudierende($studiensemester_kurzbz=null)
	{
		$qry = "SELECT
					distinct on(prestudent_id) prestudent_id,
					tbl_prestudentstatus.studiensemester_kurzbz,
					tbl_prestudent.studiengang_kz,
					tbl_prestudentstatus.ausbildungssemester,
					COALESCE(tbl_prestudentstatus.orgform_kurzbz, tbl_studiengang.orgform_kurzbz) as orgform_kurzbz
				FROM
					public.tbl_prestudent
					JOIN public.tbl_prestudentstatus USING(prestudent_id)
					JOIN public.tbl_studiengang USING(studiengang_kz)
				WHERE
					tbl_prestudentstatus.status_kurzbz IN ('Student','Unterbrecher','Diplomand')
					AND bismelden=true";

		if(!is_null($studiensemester_kurzbz))
			$qry.=" AND tbl_prestudentstatus.studiensemester_kurzbz=".$this->db_add_param($studiensemester_kurzbz);



		$this->result = array();
		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				// Studiensemester
				if(!isset($this->result[$row->studiensemester_kurzbz]['anzahl']))
					$this->result[$row->studiensemester_kurzbz]['anzahl']=0;

				$this->result[$row->studiensemester_kurzbz]['anzahl']++;

				// Studiengang
				if(!isset($this->result[$row->studiensemester_kurzbz][$row->studiengang_kz]['anzahl']))
					$this->result[$row->studiensemester_kurzbz][$row->studiengang_kz]['anzahl']=0;

				$this->result[$row->studiensemester_kurzbz][$row->studiengang_kz]['anzahl']++;

				// Orgform
				if(!isset($this->result[$row->studiensemester_kurzbz][$row->studiengang_kz][$row->orgform_kurzbz]['anzahl']))
					$this->result[$row->studiensemester_kurzbz][$row->studiengang_kz][$row->orgform_kurzbz]['anzahl']=0;

				$this->result[$row->studiensemester_kurzbz][$row->studiengang_kz][$row->orgform_kurzbz]['anzahl']++;

				// Ausbildungssemester
				if(!isset($this->result[$row->studiensemester_kurzbz][$row->studiengang_kz][$row->orgform_kurzbz][$row->ausbildungssemester]['anzahl']))
					$this->result[$row->studiensemester_kurzbz][$row->studiengang_kz][$row->orgform_kurzbz][$row->ausbildungssemester]['anzahl']=0;

				$this->result[$row->studiensemester_kurzbz][$row->studiengang_kz][$row->orgform_kurzbz][$row->ausbildungssemester]['anzahl']++;
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
	 * Anzahl der Abbrecher liefern.<br>
	 * WM: Kopie von getBewerber() => @TODO: überprüfen!!!
	 * @param string $studiensemester_kurzbz
	 * @param integer $studiengang_kz
	 * @param string $orgform_kurzbz
	 * @param integer $ausbildungssemester
	 * @return boolean
	 */
	public function getAnzAbbrecher($studiensemester_kurzbz, $studiengang_kz=null, $orgform_kurzbz=null, $ausbildungssemester=null)
	{
		$qry = "SELECT
					count(*) as anzahl
				FROM
					public.tbl_prestudent
					JOIN public.tbl_prestudentstatus USING(prestudent_id)
				WHERE
					tbl_prestudentstatus.status_kurzbz='Abbrecher'
					AND bismelden=true
					AND tbl_prestudentstatus.studiensemester_kurzbz=".$this->db_add_param($studiensemester_kurzbz);

		if(!is_null($studiengang_kz))
			$qry.=" AND tbl_prestudent.studiengang_kz=".$this->db_add_param($studiengang_kz, FHC_INTEGER);

		if(!is_null($orgform_kurzbz))
			$qry.=" AND (tbl_prestudentstatus.orgform_kurzbz=".$this->db_add_param($orgform_kurzbz)." OR (tbl_prestudentstatus.orgform_kurzbz IS NULL AND EXISTS(SELECT 1 FROM public.tbl_studiengang WHERE studiengang_kz=tbl_prestudent.studiengang_kz AND orgform_kurzbz=".$this->db_add_param($orgform_kurzbz).")))";

		if(!is_null($ausbildungssemester))
			$qry.=" AND tbl_prestudentstatus.ausbildungssemester=".$this->db_add_param($ausbildungssemester);

		if($result = $this->db_query($qry))
		{
			if($row = $this->db_fetch_object($result))
			{
				return $row->anzahl;
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
	 * Anzahl der Studierenden liefern.<br>
	 * WM: Kopie von getBewerber() => @TODO: überprüfen!!!
	 * @param string $studiensemester_kurzbz
	 * @param integer $studiengang_kz
	 * @param string $orgform_kurzbz
	 * @param integer $ausbildungssemester
	 * @return boolean
	 */
	public function getAnzStudierende($studiensemester_kurzbz, $studiengang_kz=null, $orgform_kurzbz=null, $ausbildungssemester=null)
	{
		$qry = "SELECT count(*) as anzahl FROM (
				SELECT
					distinct on(prestudent_id) prestudent_id
				FROM
					public.tbl_prestudent
					JOIN public.tbl_prestudentstatus USING(prestudent_id)
				WHERE
					tbl_prestudentstatus.status_kurzbz IN ('Student','Unterbrecher','Diplomand')
					AND bismelden=true
					AND tbl_prestudentstatus.studiensemester_kurzbz=".$this->db_add_param($studiensemester_kurzbz);

		if(!is_null($studiengang_kz))
			$qry.=" AND tbl_prestudent.studiengang_kz=".$this->db_add_param($studiengang_kz, FHC_INTEGER);

		if(!is_null($orgform_kurzbz))
			$qry.=" AND (tbl_prestudentstatus.orgform_kurzbz=".$this->db_add_param($orgform_kurzbz)." OR (tbl_prestudentstatus.orgform_kurzbz IS NULL AND EXISTS(SELECT 1 FROM public.tbl_studiengang WHERE studiengang_kz=tbl_prestudent.studiengang_kz AND orgform_kurzbz=".$this->db_add_param($orgform_kurzbz).")))";

		if(!is_null($ausbildungssemester))
			$qry.=" AND tbl_prestudentstatus.ausbildungssemester=".$this->db_add_param($ausbildungssemester);

		$qry.=") as sub";

		if($result = $this->db_query($qry))
		{
			if($row = $this->db_fetch_object($result))
			{
				return $row->anzahl;
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
	 * Laedt die Studiensemester eines Studenten
	 * @param $uid
	 * @return array mit Studiensemestern
	 */
	public function getSemesterZuUid($uid)
	{

		$qry = "SELECT
					tbl_studiensemester.studiensemester_kurzbz, tbl_studiensemester.bezeichnung
				FROM
					public.tbl_prestudentstatus
					JOIN public.tbl_prestudent USING (prestudent_id)
					JOIN public.tbl_student USING (prestudent_id)
					JOIN public.tbl_studiensemester USING (studiensemester_kurzbz)
				WHERE
					status_kurzbz IN ('Student', 'Diplomand','Incoming')
				 	AND student_uid = ". $this->db_add_param($uid)."
				 ORDER BY ausbildungssemester";

		if($result = $this->db_query($qry))
		{
			$semester = array();

			while($row = $this->db_fetch_object($result))
				$semester[$row->studiensemester_kurzbz] = $row->bezeichnung;

			return $semester;
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Daten';
			return false;
		}
	}

	/**
	 * Loescht einen Prestudenten und legt einen Log-Eintrag dafuer an
	 * @param integer $prestudent_id
	 * @return true wenn ok, false wenn Fehler
	 */
	public function deletePrestudent($prestudent_id)
	{
		if(!is_numeric($prestudent_id))
		{
			$this->errormsg = 'Prestudent_id ist ungueltig';
			return false;
		}

		$qry = "DELETE FROM public.tbl_prestudent
				WHERE
					prestudent_id=".$this->db_add_param($prestudent_id, FHC_INTEGER);

		if($this->load($prestudent_id))
		{
			$this->db_query('BEGIN;');

			$log = new log();

			$log->executetime = date('Y-m-d H:i:s');
			$log->beschreibung = 'Loeschen der Prestudent ID '.$prestudent_id;
			$log->mitarbeiter_uid = get_uid();
			$log->sql = $qry;
			$log->sqlundo = 'INSERT INTO public.tbl_prestudent(
				prestudent_id, aufmerksamdurch_kurzbz, studiengang_kz, berufstaetigkeit_code, ausbildungcode,
				zgv_code, zgvort, zgvdatum, zgvnation, zgvmas_code, zgvmaort, zgvmadatum, zgvmanation,
				aufnahmeschluessel, facheinschlberuf, anmeldungreihungstest, reihungstestangetreten, reihungstest_id,
				punkte, rt_punkte1, rt_punkte2, rt_punkte3, bismelden, person_id, anmerkung, mentor, ext_id_prestudent,
				dual, ausstellungsstaat, zgvdoktor_code, zgvdoktorort, zgvdoktordatum, zgvdoktornation,
				gsstudientyp_kurzbz, aufnahmegruppe_kurzbz, priorisierung) VALUES('.
				$this->db_add_param($this->prestudent_id).','.
				$this->db_add_param($this->aufmerksamdurch_kurzbz).','.
				$this->db_add_param($this->studiengang_kz).','.
				$this->db_add_param($this->berufstaetigkeit_code).','.
				$this->db_add_param($this->ausbildungcode).','.
				$this->db_add_param($this->zgv_code).','.
				$this->db_add_param($this->zgvort).','.
				$this->db_add_param($this->zgvdatum).','.
				$this->db_add_param($this->zgvnation).','.
				$this->db_add_param($this->zgvmas_code).','.
				$this->db_add_param($this->zgvmaort).','.
				$this->db_add_param($this->zgvmadatum).','.
				$this->db_add_param($this->zgvmanation).','.
				$this->db_add_param($this->aufnahmeschluessel).','.
				$this->db_add_param($this->facheinschlberuf, FHC_BOOLEAN).','.
				$this->db_add_param($this->anmeldungreihungstest).','.
				$this->db_add_param($this->reihungstestangetreten, FHC_BOOLEAN).','.
				$this->db_add_param($this->reihungstest_id).','.
				$this->db_add_param($this->punkte).','.
				$this->db_add_param($this->rt_punkte1).','.
				$this->db_add_param($this->rt_punkte2).','.
				$this->db_add_param($this->rt_punkte3).','.
				$this->db_add_param($this->bismelden, FHC_BOOLEAN).','.
				$this->db_add_param($this->person_id).','.
				$this->db_add_param($this->anmerkung).','.
				$this->db_add_param($this->mentor).','.
				$this->db_add_param($this->ext_id_prestudent).','.
				$this->db_add_param($this->dual, FHC_BOOLEAN).','.
				$this->db_add_param($this->ausstellungsstaat).','.
				$this->db_add_param($this->zgvdoktor_code).','.
				$this->db_add_param($this->zgvdoktorort).','.
				$this->db_add_param($this->zgvdoktordatum).','.
				$this->db_add_param($this->zgvdoktornation).','.
				$this->db_add_param($this->gsstudientyp_kurzbz).','.
				$this->db_add_param($this->aufnahmegruppe_kurzbz).','.
				$this->db_add_param($this->priorisierung).');';

			if($log->save(true))
			{
				if($this->db_query($qry))
				{
					$this->db_query('COMMIT');
					return true;
				}
				else
				{
					$this->db_query('ROLLBACK');
					$this->errormsg = 'Fehler beim Loeschen des PreStudenten';
					return false;
				}
			}
			else
			{
				$this->db_query('ROLLBACK');
				$this->errormsg = 'Fehler beim Speichern des Log-Eintrages';
				return false;
			}
		}
		else
		{
			return false;
		}
	}

	/**
	 * Liefert die Priorität des PreStudenten einer Person in einem Studiensemester
	 * Per Default wird die Höchste Priorisierung (ORDER BY DESC NULLS LAST) zurueckgegeben.
	 * @param integer $person_id Person ID deren höchste Priorität geladen werden soll
	 * @param string $studiensemester_kurzbz Studiensemester dessen höchste Priorität geladen werden soll
	 * @param string $order Default höchste Priorisierung (priorisierung DESC NULLS LAST)
	 * @return object mit priorisierung und studiengang_kz oder false im Fehlerfall
	 */
	public function getPriorisierungPersonStudiensemester($person_id, $studiensemester_kurzbz, $order = "priorisierung DESC NULLS LAST")
	{
		$qry = "SELECT
					priorisierung, studiengang_kz
				FROM
					public.tbl_prestudent
					JOIN public.tbl_prestudentstatus USING(prestudent_id)
				WHERE
					tbl_prestudent.person_id=".$this->db_add_param($person_id, FHC_INTEGER)."
					AND tbl_prestudentstatus.status_kurzbz='Interessent'
					AND tbl_prestudentstatus.studiensemester_kurzbz=".$this->db_add_param($studiensemester_kurzbz)."
				ORDER BY ".$order."
				LIMIT 1";

		if($result = $this->db_query($qry))
		{
			if($row = $this->db_fetch_object($result))
			{
				$this->priorisierung = $row->priorisierung;
				$this->studiengang_kz = $row->studiengang_kz;
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
	 * Liefert die relative Priorität (zB 1, 2, 3) eines PreStudenten Anhand seiner absoluten Priorität (zB 9, 10, 11)

	 * @param integer $prestudent_id PrestudentID deren relative Priorität ermittelt werden soll
	 * @param string $priorisierungAbsolut Absolute Priorität deren relative Platzierung ermittelt werden soll

	 * @return integer Relative Platzierung des PreStudenten oder false im Fehlerfall
	 */
	public function getRelativePriorisierungFromAbsolut($prestudent_id, $priorisierungAbsolut)
	{
		$qry = "SELECT count(*) AS prio_relativ
				FROM (
					SELECT *,
						(
							SELECT status_kurzbz
							FROM PUBLIC.tbl_prestudentstatus
							WHERE prestudent_id = pss.prestudent_id
							ORDER BY datum DESC,
								tbl_prestudentstatus.insertamum DESC LIMIT 1
							) AS laststatus
					FROM PUBLIC.tbl_prestudent pss
					JOIN PUBLIC.tbl_prestudentstatus USING (prestudent_id)
					WHERE person_id = (
							SELECT person_id
							FROM PUBLIC.tbl_prestudent
							WHERE prestudent_id = ".$this->db_add_param($prestudent_id, FHC_INTEGER)."
							)
						AND studiensemester_kurzbz = (
							SELECT studiensemester_kurzbz
							FROM PUBLIC.tbl_prestudentstatus
							WHERE prestudent_id = ".$this->db_add_param($prestudent_id, FHC_INTEGER)."
								AND status_kurzbz = 'Interessent' LIMIT 1
							)
						AND status_kurzbz = 'Interessent'
					) prest
				WHERE laststatus NOT IN ('Abbrecher', 'Abgewiesener', 'Absolvent')
				AND priorisierung <= ".$this->db_add_param($priorisierungAbsolut, FHC_INTEGER);

		if ($result = $this->db_query($qry))
		{
			if ($row = $this->db_fetch_object($result))
			{
				return $row->prio_relativ;
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
	 * Prueft, ob eine Person einen aktuellen PreStudentstatus-Eintrag besitzt, der die ZGV Master ersetzt
	 * @param int $person_id ID der zu überprüfenden Person.
	 * @return true wenn vorhanden
	 *		 false wenn nicht vorhanden
	 *		 false und errormsg wenn Fehler aufgetreten ist
	 */
	public function existsZGVIntern($person_id)
	{
		if (!is_numeric($person_id))
		{
			$this->errormsg = 'Person_id muss eine gueltige Zahl sein';
			return false;
		}


		$qry = "SELECT count(*) as anzahl FROM public.tbl_prestudent
				JOIN public.tbl_prestudentstatus USING (prestudent_id)
				JOIN public.tbl_studiengang USING (studiengang_kz)
				WHERE person_id = ".$this->db_add_param($person_id, FHC_INTEGER)."
				AND status_kurzbz in ('Absolvent','Diplomand','Unterbrecher','Student')
				AND typ in ('b','m','d')";


		if ($this->db_query($qry))
		{
			if ($row = $this->db_fetch_object())
			{
				if ($row->anzahl > 0)
				{
					$this->errormsg = '';
					return true;
				}
				else
				{
					$this->errormsg = '';
					return false;
				}
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
	 * Befüllt MasterZGV-Felder: Nation mit Österreich und MasterZGV-code mit FH-Bachelor(I)
	 * @param int $person_id Personenkennzeichen.
	 * @param varchar $ort Ort.
	 * @return true wenn erfolgreich durchgeführt
	 *		 false und errormsg wenn ein Fehler aufgetreten ist
	 */
	public function setZGVMasterFields($person_id, $ort)
	{
		if (!is_numeric($person_id))
		{
			$this->errormsg = 'Person_id muss eine gueltige Zahl sein';
			return false;
		}

		$db = new basis_db();
		$arrayleereManations = array();

		//all prestudent_ids mit leerer ZGV_Nation und Status Interessent
		$qry = "SELECT
					*
				FROM
					public.tbl_prestudent
				JOIN
					public.tbl_studiengang USING (studiengang_kz)
				WHERE
					person_id = ".$this->db_add_param($person_id)."
				AND
					zgvmanation is NULL
				AND
					typ ='m'
				And
					get_rolle_prestudent(prestudent_id, null) = 'Interessent';";

		if ($db->db_query($qry))
		{
			$num_rows = $db->db_num_rows();

			if ($num_rows > 0)
			{
				while ($row = $db->db_fetch_object())
				{
					$arrayleereManations[] = $row->prestudent_id;
				}

				if ($arrayleereManations)
				{
					$qry = "UPDATE
						public.tbl_prestudent
					SET
						(zgvmanation, zgvmaort, zgvmas_code) = ('A',".$this->db_add_param($ort).",1)
					WHERE
						prestudent_id in (";

					foreach ($arrayleereManations as $prestudent_id)
					{
						$qry .= $prestudent_id;

						if (next($arrayleereManations) == true)
						{
							$qry .=  ",";
						}
					}
					$qry .=  ");";

					if ($this->db_query($qry))
					{
						return true;
					}
					else
					{
						$this->errormsg = 'Fehler beim Eintragen zgvMasterFields';
						return false;
					}
				}
			}
			else
				return true;
		}
	}


	/**
	 * Prueft, ob eine Person einen aktuellen PreStudentstatus-Eintrag Interessent für einen Masterstudiengang besitzt
	 * @param int $person_id ID der zu überprüfenden Person.
	 * @return true wenn vorhanden, false wenn nicht vorhanden
	 */
	public function existsStatusInteressentMaster($person_id)
	{
		if (!is_numeric($person_id))
		{
			$this->errormsg = 'Person_id muss eine gueltige Zahl sein';
			return false;
		}

		$db = new basis_db();
		$prestudentsOfMaster = array();

		$qry = "SELECT
					prestudent_id
				FROM
					tbl_prestudent ps, tbl_studiengang sg
				WHERE
					ps.studiengang_kz = sg.studiengang_kz
				AND
					sg.typ in ('m')
				AND
					person_id = ".$this->db_add_param($person_id)."
				And
					get_rolle_prestudent(prestudent_id, null) = 'Interessent';";

		if ($db->db_query($qry))
		{
			$num_rows = $db->db_num_rows();
			if ($num_rows > 0)
			{
				return true;
			}
		}
		else
			return false;
	}


	/**
	 * Liefert den wahrscheinlichen Studiengang der MasterZGV einer Person
	 * @param int $person_id ID der zu überprüfenden Person.
	 * @return string studiengangkurzbzlang
	 */
	public function getZGVMasterStg($person_id)
	{
		if (!is_numeric($person_id))
		{
			$this->errormsg = 'Person_id muss eine gueltige Zahl sein';
			return false;
		}

		$qry = "SELECT kurzbzlang
				FROM public.tbl_prestudent
				JOIN public.tbl_prestudentstatus USING (prestudent_id)
				JOIN public.tbl_studiengang USING (studiengang_kz)
				WHERE person_id = ".$this->db_add_param($person_id, FHC_INTEGER)."
				AND status_kurzbz in ('Absolvent','Diplomand','Unterbrecher','Student')
				AND typ in ('b','m','d')
				ORDER BY status_kurzbz ASC
				LIMIT 1;";

		if ($this->db_query($qry))
		{
			if ($row = $this->db_fetch_object())
			{
				$stg = $row->kurzbzlang;
				return $stg;
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
}
