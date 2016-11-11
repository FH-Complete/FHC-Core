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
 * Authors: Andreas Oesterreicher   <andreas.oesterreicher@technikum-wien.at>
 *          Karl Burkhart           <burkhart@technikum-wien.at>
 */
require_once(dirname(__FILE__).'/basis_db.class.php');

class moodle extends basis_db
{
	public $result = array();

	public $moodle_id;
	public $mdl_course_id;
	public $lehreinheit_id;
	public $lehrveranstaltung_id;
	public $studiensemester_kurzbz;
	public $insertamum;
	public $insertvon;
	public $gruppen;
	public $moodle_version;

	public $version;

	/**
	 * Konstruktor
	 *
	 */
	public function __construct()
	{
        parent::__construct();
		$this->getVersionen();
		return true;
	}


    public function load($moodle_id)
    {
        $qry = "SELECT * FROM lehre.tbl_moodle WHERE moodle_id =".$this->db_add_param($moodle_id, FHC_INTEGER).';';

        if($result=$this->db_query($qry))
        {
            if($row = $this->db_fetch_object())
            {
				$this->moodle_id = $row->moodle_id;
				$this->mdl_course_id = $row->mdl_course_id;
				$this->lehreinheit_id = $row->lehreinheit_id;
				$this->lehrveranstaltung_id = $row->lehrveranstaltung_id;
				$this->studiensemester_kurzbz = $row->studiensemester_kurzbz;
				$this->insertamum = $row->insertamum;
				$this->insertvon = $row->insertvon;
				$this->gruppen = $this->db_parse_bool($row->gruppen);
				$this->moodle_version = $row->moodle_version;
                return true;
            }
            else
            {
                $this->errormsg = "Kein Moodleeintrag gefunden";
                return false;
            }
        }
        else
        {
            $this->errormsg="Fehler bei der Abfrage aufgetreten";
            return false;
        }
    }

	/**
	 * Laedt alle Moodlekurse zu einer LV/Stsem
	 * plus die Moodlekurse die auf dessen LE haengen
	 *
	 * @param lehrveranstaltung_id
	 * @param studiensemester_kurzbz
	 *
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function getAll($lehrveranstaltung_id, $studiensemester_kurzbz)
	{
		$qry = "SELECT
					distinct on(mdl_course_id) *
				FROM
					lehre.tbl_lehrveranstaltung, lehre.tbl_lehreinheit, lehre.tbl_moodle
				WHERE
					tbl_lehrveranstaltung.lehrveranstaltung_id=tbl_lehreinheit.lehrveranstaltung_id
					AND	tbl_lehrveranstaltung.lehrveranstaltung_id = ".$this->db_add_param($lehrveranstaltung_id, FHC_INTEGER)."
					AND	tbl_lehreinheit.studiensemester_kurzbz=".$this->db_add_param($studiensemester_kurzbz)."
					AND	((tbl_lehrveranstaltung.lehrveranstaltung_id=tbl_moodle.lehrveranstaltung_id
						  AND tbl_moodle.studiensemester_kurzbz=tbl_lehreinheit.studiensemester_kurzbz)
						OR
						(tbl_lehreinheit.lehreinheit_id=tbl_moodle.lehreinheit_id))";

		if($result=$this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				$obj = new stdClass();

				$obj->moodle_id = $row->moodle_id;
				$obj->mdl_course_id = $row->mdl_course_id;
				$obj->lehreinheit_id = $row->lehreinheit_id;
				$obj->lehrveranstaltung_id = $row->lehrveranstaltung_id;
				$obj->studiensemester_kurzbz = $row->studiensemester_kurzbz;
				$obj->insertamum = $row->insertamum;
				$obj->insertvon = $row->insertvon;
				$obj->gruppen = $this->db_parse_bool($row->gruppen);
				$obj->moodle_version = $row->moodle_version;

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
     * gibt alle Moodlekurseinträge der Zwischentabelle für übergebenen Studiengang und Semester zurück
     * @param type $studiengang_kz
     * @param type $studiensemester
     */
    public function getAllMoodleForStudiengang($studiengang_kz, $studiensemester, $version='2.4')
    {
        $qry = '

            SELECT mdl_course_id, moodle.moodle_id, moodle.lehreinheit_id, moodle.lehrveranstaltung_id, moodle.studiensemester_kurzbz, moodle.insertamum, moodle.insertvon, gruppen, moodle_version FROM lehre.tbl_moodle moodle
            JOIN lehre.tbl_lehrveranstaltung lv USING(lehrveranstaltung_id)
            WHERE moodle.studiensemester_kurzbz = '.$this->db_add_param($studiensemester).'
            AND lv.studiengang_kz ='.$this->db_add_param($studiengang_kz).'
            AND moodle_version ='.$this->db_add_param($version).'
            AND moodle.lehreinheit_id is null

            UNION

SELECT distinct on(mdl_course_id) mdl_course_id, moodle.moodle_id, moodle.lehreinheit_id, moodle.lehrveranstaltung_id, moodle.studiensemester_kurzbz, moodle.insertamum, moodle.insertvon, gruppen, moodle_version FROM lehre.tbl_moodle moodle
            JOIN lehre.tbl_lehreinheit le ON(moodle.lehreinheit_id = le.lehreinheit_id)
            JOIN lehre.tbl_lehrveranstaltung lv ON(le.lehrveranstaltung_id = lv.lehrveranstaltung_id)
            WHERE moodle.studiensemester_kurzbz = '.$this->db_add_param($studiensemester).'
            AND lv.studiengang_kz ='.$this->db_add_param($studiengang_kz).'
            AND moodle_version ='.$this->db_add_param($version).'
            AND moodle.lehrveranstaltung_id is null
';

        if($result=$this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				$obj = new stdClass();

				$obj->moodle_id = $row->moodle_id;
				$obj->mdl_course_id = $row->mdl_course_id;
				$obj->lehreinheit_id = $row->lehreinheit_id;
				$obj->lehrveranstaltung_id = $row->lehrveranstaltung_id;
				$obj->studiensemester_kurzbz = $row->studiensemester_kurzbz;
				$obj->insertamum = $row->insertamum;
				$obj->insertvon = $row->insertvon;
				$obj->gruppen = $this->db_parse_bool($row->gruppen);
				$obj->moodle_version = $row->moodle_version;

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
     * Löscht den Zuordnungseintrag in der Moodletablle
     * @param type $moodle_id
     */
    public function deleteZuordnung($mdl_course_id)
    {
        $qry = "DELETE FROM lehre.tbl_moodle WHERE mdl_course_id=".$this->db_add_param($mdl_course_id, FHC_INTEGER).';';

        if($result=$this->db_query($qry))
            return true;
        else
        {
            $this->errormsg="Fehler beim Löschen der Daten";
            return false;
        }
    }

    /**
     * gibt alle LE Ids der Übergebenen Moodle_Course_ID zurück
     */
    public function getLeFromCourse($moodle_course_id)
    {
        $qry = "SELECT lehreinheit_id FROM lehre.tbl_moodle WHERE mdl_course_id =".$this->db_add_param($moodle_course_id, FHC_INTEGER).';';
        $le = array();
        if($result = $this->db_query($qry))
        {
            while($row = $this->db_fetch_object())
            {
                $le[] = $row->lehreinheit_id;
            }
        }
        return $le;
    }

	/**
	 * Schaut ob fuer diese LV/StSem schon ein
	 * Moodle Kurs existiert
	 *
	 * @param lehrveranstaltung_id
	 * @param studiensemester_kurzbz
	 * @return true wenn vorhanden, false wenn nicht
	 */
	public function course_exists_for_lv($lehrveranstaltung_id, $studiensemester_kurzbz)
	{
		$qry = "SELECT
					1
				FROM
					lehre.tbl_moodle
				WHERE
					lehrveranstaltung_id=".$this->db_add_param($lehrveranstaltung_id, FHC_INTEGER)."
					AND studiensemester_kurzbz=".$this->db_add_param($studiensemester_kurzbz);

		if($this->db_query($qry))
		{
			if($this->db_num_rows()>0)
				return true;
			else
				return false;
		}
		else
		{
			$this->errormsg = 'Fehler bei einer Datenbankabfrage';
			return false;
		}
	}

	/**
	 * Schaut ob fuer diese LE schon ein Moodle
	 * Kurs existiert
	 * @param lehreinheit_id
	 * @return true wenn vorhanden, false wenn nicht
	 */
	public function course_exists_for_le($lehreinheit_id)
	{
		$qry = "SELECT 1 FROM lehre.tbl_moodle WHERE lehreinheit_id=".$this->db_add_param($lehreinheit_id, FHC_INTEGER);
		if($this->db_query($qry))
		{
			if($this->db_num_rows()>0)
				return true;
			else
				return false;
		}
		else
		{
			$this->errormsg = 'Fehler bei Datenbankabfrage';
			return false;
		}
	}

	/**
	 * Prueft ob fuer alle Lehreinheiten dieser LV bereits ein Moodlekurs existiert
	 *
	 * @param lehrveranstaltung_id
	 * @param studiensemester_kurzbz
	 * @return true wenn vorhanden, false wenn nicht
	 */
	public function course_exists_for_allLE($lehrveranstaltung_id, $studiensemester_kurzbz)
	{
		$qry = "SELECT 1 FROM lehre.tbl_lehreinheit
				WHERE
					lehrveranstaltung_id=".$this->db_add_param($lehrveranstaltung_id, FHC_INTEGER)."
					AND studiensemester_kurzbz=".$this->db_add_param($studiensemester_kurzbz)."
					AND lehreinheit_id NOT IN (
						SELECT lehreinheit_id FROM lehre.tbl_moodle
						WHERE lehreinheit_id=tbl_lehreinheit.lehreinheit_id)";

		if($this->db_query($qry))
		{
			if($this->db_num_rows()>0)
				return false;
			else
				return true;
		}
		else
		{
			$this->errormsg = 'Fehler bei einer Datenbankabfrage';
			return false;
		}
	}

	/**
	 * Laedt die Moodle Versionsinformationen
	 */
	public function getVersionen()
	{
		$qry = "SELECT * FROM lehre.tbl_moodle_version";

		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				$this->version[$row->moodle_version]['bezeichnung']=$row->bezeichnung;
				$this->version[$row->moodle_version]['pfad']=$row->pfad;
			}
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Versionsdaten';
			return false;
		}
	}

	/**
	 * Liefert den Pfad zur Moodle Installation
	 * @param version Versionsnummer
	 * @return pfad (URL) zur Moodle Installation
	 */
	public function getPfad($version)
	{
		if(isset($this->version[$version]['pfad']))
			return $this->version[$version]['pfad'];
	}


	/**
	 * Liefert alle Kurse dieser LV zu denen der Student
	 * zugeteilt ist
	 *
	 * @param lehrveranstaltung_id
	 * @param studiensemester_kurzbz
	 * @param student_uid
	 * @return array mit Moodle Kurs IDs
	 */
	public function getCourse($lehrveranstaltung_id, $studiensemester_kurzbz, $student_uid)
	{
		//alle betreffenden Kurse holen
		$qry = "SELECT
					tbl_lehreinheit.lehreinheit_id, mdl_course_id, tbl_moodle.moodle_version
				FROM
					lehre.tbl_moodle
					JOIN lehre.tbl_lehreinheit USING(lehrveranstaltung_id, studiensemester_kurzbz)
				WHERE
					tbl_moodle.lehrveranstaltung_id=".$this->db_add_param($lehrveranstaltung_id, FHC_INTEGER)."
					AND tbl_moodle.studiensemester_kurzbz=".$this->db_add_param($studiensemester_kurzbz)."
				UNION
				SELECT
					tbl_lehreinheit.lehreinheit_id, mdl_course_id, tbl_moodle.moodle_version
				FROM
					lehre.tbl_moodle
					JOIN lehre.tbl_lehreinheit USING(lehreinheit_id)
				WHERE
					tbl_lehreinheit.lehrveranstaltung_id=".$this->db_add_param($lehrveranstaltung_id, FHC_INTEGER)."
					AND tbl_lehreinheit.studiensemester_kurzbz=".$this->db_add_param($studiensemester_kurzbz);

		$courses = array();
		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				//schauen in welchen Kursen der Student ist
				$qry = "SELECT 1 FROM campus.vw_student_lehrveranstaltung
						WHERE
							uid=".$this->db_add_param($student_uid)."
							AND lehreinheit_id=".$this->db_add_param($row->lehreinheit_id, FHC_INTEGER);

				if($result_vw = $this->db_query($qry))
				{
					if($this->db_num_rows($result_vw)>0)
					{
						if(!array_key_exists($row->mdl_course_id, $courses))
						{
							$obj = new stdClass();
							$obj->mdl_course_id = $row->mdl_course_id;
							$obj->moodle_version = $row->moodle_version;
							$this->result[] = $obj;
						}
					}
				}
			}
		}
		return true;
	}
}
