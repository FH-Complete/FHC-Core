<?php
/* Copyright (C) 2013 FH Technikum-Wien
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
/*
 * Moodle 2.4 Connector Klasse
 *
 * FHComplete Moodle Plugin muss installiert sein fuer
 * Webservice Funktion 'fhcomplete_courses_by_shortname'
 *                     'fhcomplete_get_course_grades'
 */
require_once(dirname(__FILE__).'/basis_db.class.php');
require_once(dirname(__FILE__).'/moodle.class.php');
require_once(dirname(__FILE__).'/datum.class.php');
require_once(dirname(__FILE__).'/studiensemester.class.php');
require_once(dirname(__FILE__).'/../config/global.config.inc.php');

class moodle24_course extends basis_db
{
	public $result = array();
	public $serverurl;

	//Vilesci Attribute
	public $moodle_id;
	public $mdl_course_id;
	public $lehreinheit_id;
	public $lehrveranstaltung_id;
	public $studiensemester_kurzbz;
	public $insertamum;
	public $insertvon;
	public $gruppen;

	//Moodle Attribute
	public $mdl_fullname;
	public $mdl_shortname;

	public $lehrveranstaltung_bezeichnung;
	public $lehrveranstaltung_semester;
	public	$lehrveranstaltung_studiengang_kz;

	// Kurs Resourcen - Anzahl
	public	$mdl_benotungen;
	public	$mdl_resource;
	public	$mdl_quiz;
	public	$mdl_chat;
	public	$mdl_forum;
	public	$mdl_choice;

	public $note;

	/**
	 * Konstruktor
	 *
	 */
	public function __construct()
	{
		$moodle = new moodle();
		$pfad = $moodle->getPfad('2.4');
		$this->serverurl=$pfad.'/webservice/soap/server.php?wsdl=1&wstoken='.MOODLE_TOKEN24.'&'.microtime(true);
		return true;
	}

	/**
	 * Laedt einen MoodleKurs
	 * @param mdl_course_id ID des Moodle Kurses
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function load($mdl_course_id)
	{
		$this->mdl_fullname = '';
		$this->mdl_shortname = '';

		$this->errormsg='';
		$this->result=array();

		if (!is_null($mdl_course_id))
			$this->mdl_course_id=$mdl_course_id;
		if (is_null($this->mdl_course_id)
			|| empty($this->mdl_course_id)
			|| !is_numeric($this->mdl_course_id))
		{
			$this->errormsg='Moodle Kurs ID fehlt';
			return false;
		}

		$client = new SoapClient($this->serverurl);
		$response = $client->core_course_get_courses(array('ids'=>array($this->mdl_course_id)));

		if($response)
		{
			if(isset($response[0]))
			{
				$this->mdl_fullname = $response[0]['fullname'];
				$this->mdl_shortname = $response[0]['shortname'];
				$this->mdl_course_id = $response[0]['id'];
				return true;
			}
			else
			{
				$this->errormsg = 'Kurs wurde nicht gefunden';
				return false;
			}
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden des Kurses';
			return false;
		}
	}

	/**
	 * Legt einen Eintrag in der tbl_moodle an
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function create_vilesci()
	{
		if($this->mdl_course_id=='')
		{
			$this->errormsg='mdl_course_id muss angegeben sein';
			return false;
		}

		$qry = 'BEGIN; INSERT INTO lehre.tbl_moodle(mdl_course_id, lehreinheit_id, lehrveranstaltung_id,
											studiensemester_kurzbz, insertamum, insertvon, gruppen, moodle_version)
				VALUES('.
				$this->db_add_param($this->mdl_course_id, FHC_INTEGER).','.
				$this->db_add_param($this->lehreinheit_id, FHC_INTEGER).','.
				$this->db_add_param($this->lehrveranstaltung_id, FHC_INTEGER).','.
				$this->db_add_param($this->studiensemester_kurzbz).','.
				$this->db_add_param($this->insertamum).','.
				$this->db_add_param($this->insertvon).','.
				$this->db_add_param($this->gruppen, FHC_BOOLEAN).", '2.4');";

		if($this->db_query($qry))
		{
			$qry = "SELECT currval('lehre.tbl_moodle_moodle_id_seq') as id;";
			if($this->db_query($qry))
			{
				if($row = $this->db_fetch_object())
				{
					$this->moodle_id = $row->id;
					$this->db_query('COMMIT;');
					return true;
				}
				else
				{
					$this->db_query('ROLLBACK');
					$this->errormsg = 'Fehler beim Lesen der Sequence';
					return false;
				}
			}
			else
			{
					$this->db_query('ROLLBACK');
					$this->errormsg = 'Fehler beim Lesen der Sequence';
					return false;
			}
		}
		else
		{
			$this->errormsg = 'Fehler beim Einfuegen des Datensatzes';
			return false;
		}
	}

	/**
	 * Legt einen Kurs im Moodle an
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function create_moodle()
	{
		//CourseCategorie ermitteln

		//lehrveranstalung ID holen falls die nur die lehreinheit_id angegeben wurde
		if($this->lehrveranstaltung_id=='')
		{
			$qry = "SELECT lehrveranstaltung_id FROM lehre.tbl_lehreinheit
					WHERE lehreinheit_id=".$this->db_add_param($this->lehreinheit_id, FHC_INTEGER);
			if($res=$this->db_query($qry))
			{
				if($row = $this->db_fetch_object($res))
				{
					$lvid = $row->lehrveranstaltung_id;
				}
				else
				{
					$this->errormsg = 'Fehler beim Ermitteln der LehrveranstaltungID';
					return false;
				}
			}
			else
			{
				$this->errormsg = 'Fehler beim Ermitteln der LehrveranstaltungID';
				return false;
			}
		}
		else
			$lvid = $this->lehrveranstaltung_id;

		//Studiengang und Semester holen
		$qry = "SELECT tbl_lehrveranstaltung.semester, UPPER(tbl_studiengang.typ::varchar(1) || tbl_studiengang.kurzbz) as stg,
				studiengang_kz, tbl_studiengang.oe_kurzbz
				FROM lehre.tbl_lehrveranstaltung JOIN public.tbl_studiengang USING(studiengang_kz)
				WHERE lehrveranstaltung_id=".$this->db_add_param($lvid, FHC_INTEGER);

		if($res=$this->db_query($qry))
		{
			if($row = $this->db_fetch_object($res))
			{
				$semester = $row->semester;
				$stg = $row->stg;
				$stg_kz = $row->studiengang_kz;
				$oe_kurzbz = $row->oe_kurzbz;
			}
			else
			{
				$this->errormsg = 'Fehler beim Ermitteln von Studiengang und Semester';
				return false;
			}
		}
		else
		{
			$this->errormsg = 'Fehler beim Ermitteln von Studiengang und Semester';
			return false;
		}

		// Kategoriebau Aufbauen
		if(defined('MOODLE_COURSE_SCHEMA') && MOODLE_COURSE_SCHEMA=='DEP-STG-JG-STSEM')
		{

			// Struktur: Department -> STG -> Jahrgang -> StSem (Informationstechnologie und Informationsmanagement -> BIMK -> Jahrgang 2014 -> WS2014)

			// Studiengang der Lehrveranstaltung holen
			// Uebergeordnetes Department ermitteln
			$qry = 'SELECT
						bezeichnung
					FROM
						public.tbl_organisationseinheit
					WHERE
						oe_kurzbz=(SELECT oe_parent_kurzbz FROM public.tbl_organisationseinheit WHERE oe_kurzbz='.$this->db_add_param($oe_kurzbz).')';

			if($result_department = $this->db_query($qry))
			{
				if($row_department = $this->db_fetch_object($result_department))
				{
					$department = $row_department->bezeichnung;
				}
				else
				{
					$this->errormsg = 'Fehler beim Ermitteln des Departments';
					return false;
				}
			}
			// Department
			if(!$id_department = $this->getCategorie($department, '0'))
			{
				if(!$id_department = $this->createCategorie($department, '0'))
					echo "<br>$this->errormsg";
			}

			// Studiengang
			if(!$id_stg = $this->getCategorie($stg, $id_department))
			{
				if(!$id_stg = $this->createCategorie($stg, $id_department))
					echo "<br>$this->errormsg";
			}

			// Jahrgang - 1. Studiensemester ermitteln (Stsem um Ausbsem -1 zurückspringen) und das Jahr ermitteln
			$studiensemester = new studiensemester();
			if($semester!=0)
			{
				$jahrgangstsem = $studiensemester->jump($this->studiensemester_kurzbz, ($semester-1)*-1);
				$studiensemester->load($jahrgangstsem);
			}
			else
			{
				$jahrgangstsem=$this->studiensemester_kurzbz;
				$studiensemester->load($jahrgangstsem);
			}

			$datum = new Datum();
			$jahr = $datum->formatDatum($studiensemester->start, 'Y');

			if(!$id_jahrgang = $this->getCategorie('Jahrgang '.$jahr, $id_stg))
			{
				if(!$id_jahrgang = $this->createCategorie('Jahrgang '.$jahr, $id_stg))
					echo "<br>$this->errormsg";
			}

			// Studiensemester
			if(!$id_stsem = $this->getCategorie($this->studiensemester_kurzbz, $id_jahrgang))
			{
				if(!$id_stsem = $this->createCategorie($this->studiensemester_kurzbz, $id_jahrgang))
					echo "<br>Fehler beim Anlegen des Studiensemesters";
			}

			$categoryid=$id_stsem;
		}
		else
		{
			// Struktur: STSEM -> STG -> Ausbsemester (WS2014 -> BEL -> 1)

			//Studiensemester Categorie holen
			if(!$id_stsem = $this->getCategorie($this->studiensemester_kurzbz, '0'))
			{
				if(!$id_stsem = $this->createCategorie($this->studiensemester_kurzbz, '0'))
					echo "<br>Fehler beim Anlegen des Studiensemesters";
			}
			//Studiengang Categorie holen
			if(!$id_stg = $this->getCategorie($stg, $id_stsem))
			{
				if(!$id_stg = $this->createCategorie($stg, $id_stsem))
					echo "<br>$this->errormsg";
			}
			//Semester Categorie holen
			if(!$id_sem = $this->getCategorie($semester, $id_stg))
			{
				if(!$id_sem = $this->createCategorie($semester, $id_stg))
					echo "<br>$this->errormsg";
			}
			$categoryid=$id_sem;
		}

		try
		{
			$client = new SoapClient($this->serverurl);

			$data = new stdClass();
			$data->fullname=$this->mdl_fullname;
			$data->shortname=$this->mdl_shortname;
			$data->categoryid=$categoryid;
			$data->format='topics';

			$stsem = new studiensemester();
			$stsem->load($this->studiensemester_kurzbz);
			$datum_obj = new datum();
			$data->startdate=$datum_obj->mktime_fromdate($stsem->start);

			$response = $client->core_course_create_courses(array($data));
			if(isset($response[0]))
			{
				$this->mdl_course_id=$response[0]['id'];
				return true;
			}
			else
			{
				$this->errormsg = 'Fehler beim Anlegen des Kurses';
				return false;
			}
		}
		catch (SoapFault $E)
		{
			$this->errormsg.="SOAP Fehler beim Anlegen des Kurses: ".$E->faultstring;
			return false;
		}
		return true;
	}

	/**
	 * Laedt die ID einer Kurskategorie anhand der Bezeichnung und der ParentID
	 *
	 * @param bezeichnung Bezeichnung der Kategorie
	 * @param parent ID der uebergeordneten Kurskategorie
	 *
	 * @return id der Kategorie oder false im Fehlerfall
	 */
	public function getCategorie($bezeichnung, $parent)
	{
		if($bezeichnung=='')
		{
			$this->errormsg = 'Bezeichnung muss angegeben werden';
			return false;
		}
		if($parent=='')
		{
			$this->errormsg = 'getCategorie: parent wurde nicht uebergeben';
			return false;
		}

		try
		{
			$client = new SoapClient($this->serverurl);
			$response = $client->core_course_get_categories(array(array('key'=>'name','value'=>$bezeichnung),array('key'=>'parent','value'=>$parent)));

			if(isset($response[0]))
			{
				return $response[0]['id'];
			}
			else
			{
				$this->errormsg = 'Fehler beim Laden der Kurskategorie';
				return false;
			}
		}
		catch (SoapFault $E)
		{
			$this->errormsg.="SOAP Fehler beim Laden der Kurskategorie: ".$E->faultstring;
			return false;
		}

	}

	/**
	 * Erzeugt eine Kurskategorie anhand der Bezeichnung und der ParentID
	 * @param bezeichnung Bezeichnung der Kategorie
	 * @param parent ID der uebergeordneten Kategorie
	 */
	public function createCategorie($bezeichnung, $parent)
	{
		if($bezeichnung=='')
		{
			$this->errormsg = 'Bezeichnung muss angegeben werden';
			return false;
		}
		if($parent=='')
		{
			$this->errormsg = 'createCategorie: parent wurde nicht uebergeben';
			return false;
		}

		try
		{
			$client = new SoapClient($this->serverurl);
			$response = $client->core_course_create_categories(array(array('name'=>$bezeichnung,'parent'=>$parent)));

			if(isset($response[0]))
			{
				return $response[0]['id'];
			}
			else
			{
				$this->errormsg = 'Fehler beim Anlegen der Kategorie';
				return false;
			}
		}
		catch (SoapFault $E)
		{
			$this->errormsg.="SOAP Fehler beim Anlegen der Kategorie: ".$E->faultstring;
		}
	}


	/**
	 * Aktualisiert die Spalte gruppen in der tbl_moodle
	 * @param moodle_id ID der MoodleZuteilung
	 *        gruppen boolean true wenn syncronisiert
	 *                werden soll, false wenn nicht
	 * @return true wenn ok, false im Fehlerfall
	 *
	 * TODO eventuell auslagern in moodle.class oder ganz loeschen
	 */
	public function updateGruppenSync($moodle_id, $gruppen)
	{
		if(!is_numeric($moodle_id))
		{
			$this->errormsg = 'Moodle_id muss eine gueltige Zahl sein';
			return false;
		}

		$qry = "UPDATE lehre.tbl_moodle SET gruppen=".$this->db_add_param($gruppen, FHC_BOOLEAN)."
				WHERE moodle_id=".$this->db_add_param($moodle_id, FHC_INTEGER);

		if($this->db_query($qry))
		{
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Update';
			return false;
		}
	}

	/**
	 * Legt einen Testkurs an
	 */
	public function createTestkurs($lehrveranstaltung_id, $studiensemester_kurzbz)
	{
		//CourseCategorie ermitteln

		//Studiengang und Semester holen

		$qry = "SELECT
					tbl_lehrveranstaltung.semester,
					UPPER(tbl_studiengang.typ::varchar(1) || tbl_studiengang.kurzbz) as stg
				FROM
					lehre.tbl_lehrveranstaltung
					JOIN public.tbl_studiengang USING(studiengang_kz)
				WHERE
					lehrveranstaltung_id=".$this->db_add_param($lehrveranstaltung_id, FHC_INTEGER);

		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				$semester = $row->semester;
				$stg = $row->stg;
			}
			else
			{
				$this->errormsg = 'Fehler beim Ermitteln von Studiengang und Semester';
				return false;
			}
		}
		else
		{
			$this->errormsg = 'Fehler beim Ermitteln von Studiengang und Semester';
			return false;
		}

		//Testkurs Categorie holen
		if(!$id_testkurs = $this->getCategorie('Testkurse', '0'))
		{
			if(!$id_testkurs = $this->createCategorie('Testkurse', '0'))
			{
				$this->errormsg= "Fehler beim Anlegen der Testkurskategorie";
				return false;
			}
		}
		//StSem Categorie holen
		if(!$id_stsem = $this->getCategorie($studiensemester_kurzbz, $id_testkurs))
		{
			if(!$id_stsem = $this->createCategorie($studiensemester_kurzbz, $id_testkurs))
			{
				$this->errormsg = 'Fehler beim Anlegen der Studiensemester Kategorie';
				return false;
			}
		}

		$client = new SoapClient($this->serverurl);

		$data = new stdClass();
		$data->fullname=$this->mdl_fullname;
		$data->shortname=$this->mdl_shortname;
		$data->categoryid=$id_stsem;
		$data->format='topics';

		$response = $client->core_course_create_courses(array($data));
		if(isset($response[0]))
		{
			$this->mdl_course_id=$response[0]['id'];
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Anlegen des Testkurses';
			return false;
		}
	}

	/**
	 * Laedt den Testkurs zu dieser Lehrveranstaltung
	 * @param lehrveranstaltung_id
	 *        studiensemester_kurzbz
	 * @return ID wenn gefunden, false wenn nicht vorhanden
	 */
	public function loadTestkurs($lehrveranstaltung_id, $studiensemester_kurzbz)
	{
		$qry = "SELECT
					UPPER(tbl_studiengang.typ::varchar(1) || tbl_studiengang.kurzbz) as kuerzel,
					tbl_lehrveranstaltung.semester, tbl_lehrveranstaltung.kurzbz
				FROM
					lehre.tbl_lehrveranstaltung JOIN public.tbl_studiengang USING(studiengang_kz)
				WHERE
					lehrveranstaltung_id=".$this->db_add_param($lehrveranstaltung_id, FHC_INTEGER, false);

		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				$shortname = mb_strtoupper('TK-'.$studiensemester_kurzbz.'-'.$row->kuerzel.'-'.$row->semester.'-'.$row->kurzbz);
			}
			else
			{
				$this->errormsg = 'Fehler beim Laden des Testkurses';
				return false;
			}
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden des Testkurses';
			return false;
		}

		//Testkurs Categorie holen
		if(!$id_testkurs = $this->getCategorie('Testkurse', '0'))
		{
			$this->errormsg = 'Categorie nicht gefunden';
			return false;
		}

		//StSem Categorie holen
		if(!$id_stsem = $this->getCategorie($studiensemester_kurzbz, $id_testkurs))
		{
			$this->errormsg = 'Categorie nicht gefunden';
			return false;
		}

		$client = new SoapClient($this->serverurl);
		$response = $client->fhcomplete_courses_by_shortname(array('shortnames'=>array($shortname)));

		if(isset($response[0]))
		{
			$this->mdl_fullname = $response[0]['fullname'];
			$this->mdl_shortname = $response[0]['shortname'];
			$this->mdl_course_id = $response[0]['id'];
			return true;
		}
		else
		{
			$this->errormsg='Es wurde kein Testkurs gefunden';
			return false;
		}
	}


	/**
	 * Laedt die Moodle Noten zu allen Moodlekursen einer Lehrveranstaltung
	 * @param lehrveranstaltung_id
	 * @param $studiensemester_kurzbz
	 *
	 * @return objekt mit den Noten der Teilnehmer dieses Kurses
	 */
	public function loadNoten($lehrveranstaltung_id, $studiensemester_kurzbz)
	{
		$this->errormsg='';
		$this->result=null;

		if($lehrveranstaltung_id=='' || $studiensemester_kurzbz=='')
		{
			$this->errormsg = 'LehrveranstaltungID und Studiensemester_kurzbz muss uebergeben werden';
			return false;
		}

		// Ermitteln die Lehreinheiten und Moodle ID
		$qry = "
		SELECT
			distinct mdl_course_id
		FROM
			lehre.tbl_moodle
			JOIN lehre.tbl_lehreinheit USING(lehrveranstaltung_id, studiensemester_kurzbz)
		WHERE
			tbl_moodle.lehrveranstaltung_id > 0
			AND moodle_version='2.4'
			AND tbl_moodle.lehrveranstaltung_id =".$this->db_add_param($lehrveranstaltung_id)."
			AND tbl_moodle.studiensemester_kurzbz =".$this->db_add_param($studiensemester_kurzbz)."
		UNION
		SELECT
			distinct mdl_course_id
		FROM
			lehre.tbl_moodle
			JOIN lehre.tbl_lehreinheit USING(lehreinheit_id)
		WHERE
			tbl_lehreinheit.lehrveranstaltung_id > 0
			AND moodle_version='2.4'
			AND tbl_lehreinheit.lehrveranstaltung_id =".$this->db_add_param($lehrveranstaltung_id)."
			AND tbl_moodle.studiensemester_kurzbz =".$this->db_add_param($studiensemester_kurzbz).";";

		if(!$result_moodle=$this->db_query($qry))
		{
			$this->errormsg = 'Fehler beim Lesen der Moodle Kurse , '.$this->errormsg;
			return false;
		}


		while($row_moodle = $this->db_fetch_object($result_moodle))
		{
			try
			{
				$client = new SoapClient($this->serverurl);
				if(CIS_GESAMTNOTE_PUNKTE)
					$type=2; // Prozentpunkte
				else
					$type=3; // Noten aufgrund Skala
				// 1 = Punkte, 2 = Prozentpunkte, 3 = Note laut Skala

				$response = $client->fhcomplete_get_course_grades($row_moodle->mdl_course_id, $type);

				if (count($response)>0)
				{

					foreach($response as $row)
					{
						if($row['note']!='-')
						{
							$userobj = new stdClass();
							$userobj->mdl_course_id = $row_moodle->mdl_course_id;
							$userobj->vorname = $row['vorname'];
							$userobj->nachname = $row['nachname'];
							$userobj->idnummer = $row['idnummer'];
							$userobj->uid = $row['username'];
							$userobj->note = $row['note'];
							$this->result[]=$userobj;
						}
					}
				}
			}
			catch(SoapFault $e)
			{
				//echo print_r($e, true);
				//return false;
			}

		}
		return true;
	}


	/**
	 * Loescht einen Moodle Course im Moodel
     * Wenn erfolgreich gelöscht wird kein Wert in response zurückgegeben
	 * @param mdl_course_id
	 *
	 */
	public function deleteKurs($mdl_course_id)
	{
		$client = new SoapClient($this->serverurl);

		$data = array($mdl_course_id);

		$response = $client->core_course_delete_courses(array($mdl_course_id));
		if(isset($response[0]))
		{
            $this->errormsg = $response[0];
			return false;
		}

		return true;

	}
}
