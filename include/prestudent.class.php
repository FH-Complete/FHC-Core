<?php
/* Copyright (C) 2007 fhcomplete.org
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
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at> and
 *          Andreas Moik <moik@technikum-wien.at>.
 */
require_once(dirname(__FILE__).'/person.class.php');
require_once(dirname(__FILE__).'/log.class.php');

require_once(dirname(__FILE__).'/phrasen.class.php');
require_once(dirname(__FILE__).'/globals.inc.php');
require_once(dirname(__FILE__).'/sprache.class.php');
require_once(dirname(__FILE__).'/authentication.class.php');

$sprache = getSprache();
$lang = new sprache();
$lang->load($sprache);
$p = new phrasen($sprache);

class prestudent extends person
{
	//Tabellenspalten
	public $prestudent_id;	// int
	public $uid;
	public $perskz;
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

	public $semester;
	public $verband;
	public $gruppe;

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
	 * @param $prestudent_id ID des Prestudenten der geladen werden soll
	 */
	public function load($prestudent_id)
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
				$this->uid = $row->uid;
				$this->perskz = $row->perskz;
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
			$qry = 'BEGIN;INSERT INTO public.tbl_prestudent (uid, perskz, aufmerksamdurch_kurzbz, person_id,
					studiengang_kz, berufstaetigkeit_code, ausbildungcode, zgv_code, zgvort, zgvdatum, zgvnation,
					zgvmas_code, zgvmaort, zgvmadatum, zgvmanation, aufnahmeschluessel, facheinschlberuf,
					reihungstest_id, anmeldungreihungstest, reihungstestangetreten, rt_gesamtpunkte,
					rt_punkte1, rt_punkte2, rt_punkte3, bismelden, insertamum, insertvon,
					updateamum, updatevon, anmerkung, dual, ausstellungsstaat, mentor) VALUES('.
						$this->db_add_param($this->uid).",".
						$this->db_add_param($this->perskz).",".
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
						$this->db_add_param($this->mentor).");";
		}
		else
		{
			$qry = 'UPDATE public.tbl_prestudent SET'.
			       ' uid='.$this->db_add_param($this->uid).",".
			       ' perskz='.$this->db_add_param($this->perskz).",".
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
			       ' dual='.$this->db_add_param($this->dual, FHC_BOOLEAN).",".
				   ' ausstellungsstaat='.$this->db_add_param($this->ausstellungsstaat).
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

        if ($this->zgvmas_code && $this->zgvmanation) {

            $this->ausstellungsstaat = $this->zgvmanation;

        } elseif ($this->zgv_code && $this->zgvnation) {

            $this->ausstellungsstaat = $this->zgvnation;

        }

    }

	/**
	 * Laden aller Prestudenten, die an $datum zum Reihungstest geladen sind.
	 * Wenn $equal auf true gesetzt ist wird genau dieses Datum verwendet,
	 * ansonsten werden auch alle mit späterem Datum geladen. ---> von kindlm am 30.03.2012 geändert
	 * da zukünftige Teilnehmer nicht mehr angezeigt werden sollen.
	 * @return true wenn erfolgreich, false im Fehlerfall
	 */
	public function getPrestudentRT($datum, $equal=false)
	{
		$sql_query='SELECT DISTINCT * FROM public.vw_prestudent WHERE rt_datum';
		if ($equal)
			$sql_query.='=';
		else
			$sql_query.='=';
		$sql_query.="'$datum' ORDER BY nachname,vorname";

		if(!$this->db_query($sql_query))
		{
			$this->errormsg = 'Fehler beim Speichern des Benutzer-Datensatzes:'.$sql_query;
			return false;
		}

		$this->num_rows=0;

		while($row = $this->db_fetch_object())
		{
			$ps=new prestudent();
			$ps->prestudent_id = $row->prestudent_id;
			$ps->uid = $row->uid;
			$ps->perskz = $row->perskz;
			$ps->person_id = $row->person_id;
			$ps->reihungstest_id = $row->reihungstest_id;
			$ps->staatsbuergerschaft = $row->staatsbuergerschaft;
			$ps->geburtsnation = $row->geburtsnation;
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
			// $ps->foto = $row->foto;
			$ps->anmerkungen = $row->anmerkungen;
			$ps->homepage = $row->homepage;
			$ps->svnr = $row->svnr;
			$ps->ersatzkennzeichen = $row->ersatzkennzeichen;
			$ps->familienstand = $row->familienstand;
			$ps->geschlecht = $row->geschlecht;
			$ps->anzahlkinder = $row->anzahlkinder;
			$ps->aktiv = $this->db_parse_bool($row->aktiv);
			$ps->aufmerksamdurch_kurzbz = $row->aufmerksamdurch_kurzbz;
			$ps->studiengang_kz = $row->studiengang_kz;
			$ps->berufstaetigkeit_code = $row->berufstaetigkeit_code;
			$ps->ausbildungcode = $row->ausbildungcode;
			$ps->zgv_code = $row->zgv_code;
			$ps->zgvort = $row->zgvort;
			$ps->zgvdatum = $row->zgvdatum;
			//$ps->zgvnation = $row->zgvnation;
			$ps->zgvmas_code = $row->zgvmas_code;
			$ps->zgvmaort = $row->zgvmaort;
			$ps->zgvmadatum = $row->zgvmadatum;
			//$ps->zgvmanation = $row->zgvmanation;
			$ps->aufnahmeschluessel = $row->aufnahmeschluessel;
			$ps->facheinschlberuf = $this->db_parse_bool($row->facheinschlberuf);
			$ps->anmeldungreihungstest = $row->anmeldungreihungstest;
			$ps->reihungstestangetreten = $this->db_parse_bool($row->reihungstestangetreten);
			$ps->punkte = $row->punkte;
			$ps->rt_punkte1 = $row->rt_punkte1;
			$ps->rt_punkte2 = $row->rt_punkte2;
			$ps->bismelden = $this->db_parse_bool($row->bismelden);
			$ps->rt_studiengang_kz = $row->rt_studiengang_kz;
			$ps->rt_ort = $row->rt_ort;
			$ps->rt_datum = $row->rt_datum;
			$ps->rt_uhrzeit = $row->rt_uhrzeit;
			$ps->updateamum = $row->updateamum;
			$ps->updatevon = $row->updatevon;
			$ps->insertamum = $row->insertamum;
			$ps->insertvon = $row->insertvon;
			//$ps->ext_id_prestudent = $row->ext_id_prestudent;
			$this->result[]=$ps;
			$this->num_rows++;
		}
		return true;
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
				$this->result[] = $rolle;
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
	 * @param $studiensemester_kurzbz Studiensemester fuer das die Int. und Bewerber
	 *                                geladen werden sollen
	 */
	public function loadIntessentenUndBewerber($studiensemester_kurzbz, $studiengang_kz, $semester=null, $typ=null, $orgform=null)
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
				$qry.=" AND a.rolle='Interessent' AND a.anmeldungreihungstest is not null";
				break;
			case "reihungstestnichtangemeldet":
				$qry.=" AND a.rolle='Interessent' AND a.anmeldungreihungstest is null";
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
				$ps->uid = $row->uid;
				$ps->perskz = $row->perskz;
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
	 * fuer einen Studiengang besitzt
	 * @param person_id
	 *        studiengang_kz
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
					bewerbung_abgeschicktamum, studienplan_id) VALUES('.
			       $this->db_add_param($this->prestudent_id, FHC_INTEGER).",".
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
				   $this->db_add_param($this->studienplan_id,FHC_INTEGER).");";
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
			       ' orgform_kurzbz='.$this->db_add_param($this->orgform_kurzbz).
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
	 * Loescht einen Prestudentstatus
	 * @param $prestudent_id
	 *        $status_kurzbz
	 *        $studiensemester_kurzbz
	 *		  $ausbildungssemester
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
			$log->sqlundo = 'INSERT INTO public.tbl_prestudentstatus(prestudent_id, uid, perskz, status_kurzbz, studiensemester_kurzbz,'
							. ' ausbildungssemester, datum, insertamum, insertvon, updateamum, updatevon, ext_id, orgform_kurzbz,'
							. ' bestaetigtam, bestaetigtvon, anmerkung, bewerbung_abgeschicktamum, studienplan_id) VALUES('.
							$this->db_add_param($this->prestudent_id).','.
							$this->db_add_param($this->uid).",".
							$this->db_add_param($this->perskz).",".
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
							$this->db_add_param($this->studienplan_id, FHC_INTEGER).');';
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
				' bestaetigtvon='.$this->db_add_param($user)." ".
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
	 * @return true wenn ok, false wenn Fehler
	 */
	public function getPrestudenten($person_id)
	{
		if(!is_numeric($person_id) || $person_id=='')
		{
			$this->errormsg='ID ist ungueltig';
			return false;
		}

		$qry = "SELECT * FROM public.tbl_prestudent WHERE person_id=".$this->db_add_param($person_id, FHC_INTEGER)." ORDER BY prestudent_id";

		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$obj = new prestudent();

				$obj->prestudent_id = $row->prestudent_id;
				$obj->uid = $row->uid;
				$obj->perskz = $row->perskz;
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
	 * Laedt alle Prestudenten der Person
	 * @return true wenn ok, false wenn Fehler
	 */
	public function getPrestudentenFromStg($person_id, $studiengang_kz)
	{
		if(!is_numeric($person_id) || $person_id=='')
		{
			$this->errormsg='ID ist ungueltig';
			return false;
		}
		if(!is_numeric($studiengang_kz) || $studiengang_kz=='')
		{
			$this->errormsg='studiengang_kz ist ungueltig';
			return false;
		}

		$qry = "SELECT * FROM public.tbl_prestudent
			WHERE person_id=".$this->db_add_param($person_id, FHC_INTEGER)."
			AND studiengang_kz=".$this->db_add_param($studiengang_kz, FHC_INTEGER)."
			ORDER BY prestudent_id DESC";

		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$obj = new prestudent();

				$obj->prestudent_id = $row->prestudent_id;
				$obj->uid = $row->uid;
				$obj->perskz = $row->perskz;
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
	 * @param $studiensemester_kurzbz Studiensemester
	 * @param $studiengang_kz Kennzahl des Studienganges (optional)
	 * @param $orgform_kurzbz Organisationsform (optional)
	 * @param $ausbildungssemester Ausbildungssemester (optional)
	 * @return Anzahl der Bewerber oder false im Fehlerfall
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
	 * @param $studiensemester_kurzbz Studiensemester
	 * @param $studiengang_kz Kennzahl des Studienganges (optional)
	 * @param $orgform_kurzbz Organisationsform (optional)
	 * @param $ausbildungssemester Ausbildungssemester (optional)
	 * @return Anzahl der Interessenten oder false im Fehlerfall
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
	 * @param $studiensemester_kurzbz Studiensemester
	 * @param $studiengang_kz Kennzahl des Studienganges (optional)
	 * @param $orgform_kurzbz Organisationsform (optional)
	 * @param $ausbildungssemester Ausbildungssemester (optional)
	 * @return Anzahl der Interessenten mit ZGV oder false im Fehlerfall
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
	 * @param type $studiensemester_kurzbz
	 * @param type $studiengang_kz
	 * @param type $orgform_kurzbz
	 * @param type $ausbildungssemester
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
	 * @param type $studiensemester_kurzbz
	 * @param type $studiengang_kz
	 * @param type $orgform_kurzbz
	 * @param type $ausbildungssemester
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
					JOIN public.tbl_studiensemester USING (studiensemester_kurzbz)
				WHERE
					status_kurzbz IN ('Student', 'Diplomand','Incoming')
				 	AND uid = ". $this->db_add_param($uid)."
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
	 * Laedt alle Studenten eines Studienplans und eines Studiensemesters
	 * @param $studienplan_id
	 * @param $studiensemester_kurzbz
	 * @param $studiengang_kz
	 * @return array mit allen Prestudenten, welche sich für den angegebenen Studienplan im angegebenen Semester beworben haben
	 */
	public function getAllStudentenFromStudienplanAndStudsem($studienplan_id, $studiensemester_kurzbz, $studiengang_kz)
	{
		if(!is_numeric($studienplan_id))
		{
			$this->errormsg = 'studienplan_id ist ungueltig';
			return false;
		}

		if(!$studiensemester_kurzbz || $studiensemester_kurzbz == "")
		{
			$this->errormsg = 'studiensemester_kurzbz ist ungueltig';
			return false;
		}

		$stg_obj = new studiengang();
		$stg_obj->load($studiengang_kz);

		if($stg_obj->typ=='m')
		{
			$qry = "SELECT DISTINCT prestudent_id, vorname, nachname, gebdatum, rt_gesamtpunkte, tbl_prestudent.studiengang_kz, bis.tbl_zgvgruppe.bezeichnung, get_rolle_prestudent(prestudent_id, null) as laststatus,
					(Select anmerkung from public.tbl_prestudentstatus where prestudent_id=tbl_prestudent.prestudent_id AND studiensemester_kurzbz=". $this->db_add_param($studiensemester_kurzbz)."
						AND status_kurzbz='Bewerber') as anmerkung
			FROM
				public.tbl_prestudent
					JOIN public.tbl_person USING(person_id)
					LEFT JOIN bis.tbl_zgvgruppe_zuordnung USING(zgvmas_code)
					LEFT JOIN bis.tbl_zgvgruppe USING(gruppe_kurzbz)
			WHERE
				tbl_prestudent.studiengang_kz=". $this->db_add_param($studiengang_kz)."
				AND EXISTS(
					SELECT
						1
					FROM
						public.tbl_prestudentstatus
					WHERE
						tbl_prestudent.prestudent_id=tbl_prestudentstatus.prestudent_id
						AND studiensemester_kurzbz=". $this->db_add_param($studiensemester_kurzbz)."
						AND status_kurzbz='Bewerber'
						AND (
							studienplan_id=". $this->db_add_param($studienplan_id)."
							OR
							(anmerkung like '%' || (SELECT orgform_kurzbz || '_' || sprache FROM lehre.tbl_studienplan WHERE studienplan_id=". $this->db_add_param($studienplan_id).") || '%')
					)
			);";
		}
		else
		{
			$qry = "SELECT DISTINCT prestudent_id, vorname, nachname, gebdatum, rt_gesamtpunkte, tbl_prestudent.studiengang_kz, bis.tbl_zgvgruppe.bezeichnung, get_rolle_prestudent(prestudent_id, null) as laststatus,
				(Select anmerkung from public.tbl_prestudentstatus where prestudent_id=tbl_prestudent.prestudent_id AND studiensemester_kurzbz=". $this->db_add_param($studiensemester_kurzbz)."
						AND status_kurzbz='Bewerber') as anmerkung
			FROM
				public.tbl_prestudent
					JOIN public.tbl_person USING(person_id)
					LEFT JOIN bis.tbl_zgvgruppe_zuordnung USING(zgv_code)
					LEFT JOIN bis.tbl_zgvgruppe USING(gruppe_kurzbz)
			WHERE
				tbl_prestudent.studiengang_kz=". $this->db_add_param($studiengang_kz)."
				AND EXISTS(
					SELECT
						1
					FROM
						public.tbl_prestudentstatus
					WHERE
						tbl_prestudent.prestudent_id=tbl_prestudentstatus.prestudent_id
						AND studiensemester_kurzbz=". $this->db_add_param($studiensemester_kurzbz)."
						AND status_kurzbz='Bewerber'
						AND (
							studienplan_id=". $this->db_add_param($studienplan_id)."
							OR
							(anmerkung like '%' || (SELECT orgform_kurzbz || '_' || sprache FROM lehre.tbl_studienplan WHERE studienplan_id=". $this->db_add_param($studienplan_id).") || '%')
					)
			);";
		}


		if($result = $this->db_query($qry))
		{
			$ret = array();

			while($row = $this->db_fetch_object($result))
				$ret[] = $row;

			return $ret;
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Daten';
			return false;
		}
	}
	/**
	 * Laedt die StudentLehrverband Zuteilung
	 * @param prestudent_id
	 * @param studiensemester_kurzbz
	 * @return true wenn vorhanden, false wenn nicht
	 */
	public function load_studentlehrverband($studiensemester_kurzbz)
	{
		if(!is_numeric($this->prestudent_id))
		{
			$this->errormsg = 'PrestudentID ist ungueltig';
			return false;
		}
		if($studiensemester_kurzbz == "")
		{
			$this->errormsg = 'studiensemester_kurzbz muss angegeben werden';
			return false;
		}


		$qry = "SELECT * FROM public.tbl_studentlehrverband
				WHERE prestudent_id=".$this->db_add_param($this->prestudent_id)."
				AND studiensemester_kurzbz=".$this->db_add_param($studiensemester_kurzbz);

		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				$this->studiensemester_kurzbz = $row->studiensemester_kurzbz;
				$this->studiengang_kz = $row->studiengang_kz;
				$this->semester = $row->semester;
				$this->verband = $row->verband;
				$this->gruppe = $row->gruppe;

				return true;
			}
			else
			{
				$this->errormsg = 'Fehler beim Ermitteln des Lehrverbandes';
				return false;
			}
		}
		else
		{
			$this->errormsg ='Fehler beim Ermitteln des Lehrverbandes';
			return false;
		}
	}

	/**
	 * Rueckgabewert ist die Anzahl der Ergebnisse. Bei Fehler negativ und die
	 * Fehlermeldung liegt in errormsg.
	 * Wenn der Parameter stg_kz NULL ist tritt gruppe in Kraft.
	 * @param string $einheit_kurzbz    Einheit
	 * @param string grp    Gruppe
	 * @param string ver    Verband
	 * @param integer sem    Semester
	 * @param integer stg_kz    Kennzahl des Studiengangs
	 * @return integer Anzahl der gefundenen Einträge; <b>negativ</b> bei Fehler
	 */
	public function getPrestudents($stg_kz,$sem=null,$ver=null,$grp=null,$gruppe=null, $stsem=null)
	{
		$where = '';
		if ($gruppe!=null)
		{
			$where=" gruppe_kurzbz=".$this->db_add_param($gruppe)." AND tbl_benutzer.uid=tbl_benutzergruppe.uid";
			if($stsem!=null)
				$where.=" AND tbl_benutzergruppe.studiensemester_kurzbz=".$this->db_add_param($stsem);
		}
		else
		{
			$where.=" tbl_studentlehrverband.studiengang_kz=".$this->db_add_param($stg_kz);
			if ($sem!=null)
				$where.=" AND tbl_studentlehrverband.semester=".$this->db_add_param($sem);
			if ($ver!=null)
				$where.=" AND tbl_studentlehrverband.verband=".$this->db_add_param($ver);
			if ($grp!=null)
				$where.=" AND tbl_studentlehrverband.gruppe=".$this->db_add_param($grp);
		}

		if($stsem!=null)
				$where.=" AND tbl_studentlehrverband.studiensemester_kurzbz=".$this->db_add_param($stsem);

		$sql_query = "SELECT *
					  FROM public.tbl_person, public.tbl_prestudent, public.tbl_benutzer, public.tbl_studentlehrverband";
		if($gruppe!=null)
			$sql_query.= ",public.tbl_benutzergruppe";
		$sql_query.= " WHERE tbl_person.person_id=tbl_benutzer.person_id AND tbl_benutzer.uid = tbl_prestudent.uid AND tbl_studentlehrverband.prestudent_id=tbl_prestudent.prestudent_id AND $where ORDER BY nachname, vorname";
	    //echo $sql_query;
		if(!$this->db_query($sql_query))
		{
			$this->errormsg=$this->db_last_error();
			return false;
		}
		$result=array();

		while($row = $this->db_fetch_object())
		{
			$l=new prestudent();

			// Personendaten
			$l->uid=$row->uid;
			$l->person_id=$row->person_id;
			$l->prestudent_id=$row->prestudent_id;
			$l->titelpre=$row->titelpre;
			$l->titelpost=$row->titelpost;
			$l->vornamen=$row->vornamen;
			$l->vorname=$row->vorname;
			$l->nachname=$row->nachname;
			$l->gebdatum=$row->gebdatum;
			$l->gebort=$row->gebort;
			$l->gebzeit=$row->gebzeit;
			$l->familienstand = $row->familienstand;
			$l->svnr=$row->svnr;
			$l->foto=$row->foto;
			$l->anmerkungen=$row->anmerkung;
			$l->aktiv=$this->db_parse_bool($row->aktiv);
			$l->alias=$row->alias;
			$l->homepage=$row->homepage;
			$l->updateamum=(isset($row->updateamum)?$row->updateamum:'');
			$l->updatevon=(isset($row->updatevon)?$row->updatevon:'');

			// Studentendaten
			$l->matrikelnr=$row->matrikelnr;
			$l->gruppe=$row->lvb_gruppe;
			$l->verband=$row->lvb_verband;
			$l->semester=$row->lvb_semester;
			$l->studiengang_kz=$row->lvb_studiengang_kz;
			$l->staatsbuergerschaft = $row->staatsbuergerschaft;

			$l->zgv_code = $row->zgv_code;
			$l->zgvort = $row->zgvort;
			$l->zgvdatum = $row->zgvdatum;
			$l->zgvmas_code = $row->zgvmas_code;
			$l->zgvmaort = $row->zgvmaort;
			$l->zgvmadatum = $row->zgvmadatum;

			$result[]=$l;

		}
		return $result;
	}


	/**
	 * Laden aller Prestudenten zu einer UID
	 * @return true wenn erfolgreich, false im Fehlerfall
	 */
	public function getPrestudentsFromUid($uid)
	{
		if(!isset($uid) || $uid == "")
		{
			$this->errormsg = "Diese UID ist ungueltig";
			return false;
		}

		$qry='SELECT prestudent_id FROM public.tbl_prestudent WHERE uid='.$this->db_add_param($uid);


		if(!$this->db_query($qry))
		{
			$this->errormsg = 'Fehler beim Laden der Prestudenten';
			return false;
		}

		while($row = $this->db_fetch_object())
		{
			$ps=new prestudent($row->prestudent_id);
			$this->result[]=$ps;
		}
		return true;
	}

	public function statusExists($prestudent_id, $studiensemester_kurzbz, $status_kurzbz = null)
	{
		if(!isset($studiensemester_kurzbz) && $studiensemester_kurzbz == "")
		{
			$this->errormsg = "studiensemester_kurzbz ist ungueltig";
			return false;
		}

		$qry = "SELECT status_kurzbz FROM tbl_prestudentstatus
			WHERE prestudent_id=".$this->db_add_param($prestudent_id, FHC_INTEGER)."
			AND studiensemester_kurzbz=".$this->db_add_param($studiensemester_kurzbz);

		if(isset($status_kurzbz))
			$qry .= " AND status_kurzbz=".$this->db_add_param($status_kurzbz);

		if($res = $this->db_query($qry))
		{
			if($row = $this->db_num_rows($res) > 0)
			{
				return true;
			}
			else
			{
				$this->errormsg = "Es wurde kein Status gefunden";
				return false;
			}
		}
		$this->errormsg = "Fehler bei der Datenabfrage";
		return false;
	}





	/**
	 * Laedt Prestudent mit dem uebergebenen Personenkennzeichen
	 * @param $perskz Personenkennzeichen des Prestudenten, der geladen werden soll
	 */
	public function loadFromPerskz($perskz)
	{
		if(!is_numeric($perskz))
		{
			$this->errormsg = 'perskz ist ungueltig';
			return false;
		}

		$qry = "SELECT prestudent_id FROM public.tbl_prestudent WHERE perskz=".$this->db_add_param($perskz);

		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				return $this->load($row->prestudent_id);
			}
			else
			{
				$this->errormsg = 'Prestudent nicht gefunden';
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
	 * Liefert die Tabellenelemente die den Kriterien der Parameter entsprechen
	 * Ueberschreibt die Methode aus der Klasse Person
	 * @param $filter String mit Vorname oder Nachname
	 * @param $order Sortierkriterium
	 * @return array mit Personen oder false wenn ein Fehler auftritt
	 */
	public function getTab($filter, $order='person_id')
	{
		$sql_query = "SELECT
							tbl_prestudent.person_id, staatsbuergerschaft, geburtsnation, sprache, anrede, titelpost, titelpre,
							nachname, vorname, vornamen, gebdatum, gebort, gebzeit, tbl_prestudent.anmerkung, homepage, svnr,
							ersatzkennzeichen, familienstand, geschlecht, anzahlkinder, tbl_person.aktiv, kurzbeschreibung,
							tbl_benutzer.aktiv as bnaktiv, tbl_prestudent.studiengang_kz, tbl_studentlehrverband.semester, tbl_studentlehrverband.verband,
							tbl_studentlehrverband.gruppe, tbl_prestudent.prestudent_id
						FROM
							public.tbl_person
						JOIN public.tbl_benutzer ON(tbl_person.person_id=tbl_benutzer.person_id)
						JOIN public.tbl_prestudent ON(tbl_benutzer.uid=tbl_prestudent.uid)
						JOIN public.tbl_studentlehrverband ON(tbl_studentlehrverband.prestudent_id = tbl_prestudent.prestudent_id)
					WHERE true ";

		if($filter!='')
		{
			$sql_query.=" AND 	nachname ~* ".$this->db_add_param($filter)." OR
								vorname ~* ".$this->db_add_param($filter)." OR
								(nachname || ' ' || vorname) ~* ".$this->db_add_param($filter)." OR
								(vorname || ' ' || nachname) ~* ".$this->db_add_param($filter);
		}

		$sql_query .= " ORDER BY $order";
		if($filter=='')
		   $sql_query .= " LIMIT 30";

		if($this->db_query($sql_query))
		{
			while($row = $this->db_fetch_object())
			{
				$l = new prestudent();
				$l->person_id = $row->person_id;
				$l->staatsbuergerschaft = $row->staatsbuergerschaft;
				$l->geburtsnation = $row->geburtsnation;
				$l->sprache = $row->sprache;
				$l->anrede = $row->anrede;
				$l->titelpost = $row->titelpost;
				$l->titelpre = $row->titelpre;
				$l->nachname = $row->nachname;
				$l->vorname = $row->vorname;
				$l->vornamen = $row->vornamen;
				$l->gebdatum = $row->gebdatum;
				$l->gebort = $row->gebort;
				$l->gebzeit = $row->gebzeit;
				$l->anmerkungen = $row->anmerkung;
				$l->homepage = $row->homepage;
				$l->svnr = $row->svnr;
				$l->ersatzkennzeichen = $row->ersatzkennzeichen;
				$l->familienstand = $row->familienstand;
				$l->geschlecht = $row->geschlecht;
				$l->anzahlkinder = $row->anzahlkinder;
				$l->aktiv = $this->db_parse_bool($row->aktiv);
				$l->kurzbeschreibung = $row->kurzbeschreibung;
				$l->bnaktiv = $this->db_parse_bool($row->bnaktiv);
				$l->studiengang_kz = $row->studiengang_kz;
				$l->semester = $row->semester;
				$l->verband = $row->verband;
				$l->gruppe = $row->gruppe;
				$l->prestudent_id = $row->prestudent_id;
				$this->result[]=$l;
			}
		}
		else
		{
			$this->errormsg = $this->db_last_error();
			return false;
		}
		return true;
	}
}
