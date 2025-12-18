<?php
class Projektarbeit_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'lehre.tbl_projektarbeit';
		$this->pk = 'projektarbeit_id';
	}

	/**
	 * Gets Projektarbeit(en) of a student for a Studiengang, Semester, Projekttyp, final.
	 * @param $student_uid
	 * @param $studiengang_kz
	 * @param $studiensemester_kurzbz
	 * @param $projekttyp
	 * @param $final
	 * @return object
	 */
	public function getProjektarbeit($student_uid, $studiengang_kz = null, $studiensemester_kurzbz = null, $projekttyp = null, $final = null)
	{
		$qry = "SELECT
					pa.*, tbl_projekttyp.bezeichnung,
					tbl_lehreinheit.studiensemester_kurzbz, tbl_lehrveranstaltung.lehrveranstaltung_id,
					tbl_firma.name AS firma_name,
					(
						SELECT
							STRING_AGG(trim(COALESCE(titelpre,'')||' '||COALESCE(vorname,'')||' '||COALESCE(nachname,'')||' '||COALESCE(titelpost,'')), ', ')
						FROM
							lehre.tbl_projektbetreuer
							JOIN public.tbl_person USING (person_id)
						WHERE
							projektarbeit_id = pa.projektarbeit_id
							AND student_uid = pa.student_uid
						GROUP BY projektarbeit_id
					) AS projektbetreuer
				FROM
					lehre.tbl_projektarbeit pa
					JOIN lehre.tbl_projekttyp USING (projekttyp_kurzbz)
					JOIN lehre.tbl_lehreinheit USING (lehreinheit_id)
					JOIN lehre.tbl_lehrveranstaltung USING (lehrveranstaltung_id)
					LEFT JOIN public.tbl_firma USING (firma_id)
				WHERE
					pa.student_uid = ?";

		$params = array($student_uid);

		if (isset($studiengang_kz))
		{
			$qry .= ' AND tbl_lehrveranstaltung.studiengang_kz=?';
			$params[] = $studiengang_kz;
		}

		if (isset($studiensemester_kurzbz))
		{
			$qry .= ' AND tbl_lehreinheit.studiensemester_kurzbz=?';
			$params[] = $studiensemester_kurzbz;
		}

		if (isset($projekttyp))
		{
			if (is_array($projekttyp))
				$qry .= ' AND tbl_projektarbeit.projekttyp_kurzbz IN ?';
			else
				$qry .= ' AND tbl_projektarbeit.projekttyp_kurzbz=?';

			$params[] = $projekttyp;
		}

		if (isset($final))
		{
			$qry .= ' AND tbl_projektarbeit.final=?';
			$params[] = $final;
		}

		$qry .= ' ORDER BY beginn DESC, projektarbeit_id DESC';

		return $this->execQuery($qry, $params);
	}

	/**
	 * Update a Projektarbeit of a student by projektarbeit_id with 
	 * the paramenters used by the student endupload page in cis4 abgabetool.
	 */
	public function updateProjektarbeit($projektarbeit_id,$sprache,$abstract,$abstract_en,
										$schlagwoerter, $schlagwoerter_en,$seitenanzahl)
	{
		$qry = "UPDATE lehre.tbl_projektarbeit SET
					seitenanzahl = ?,
					abgabedatum = now(),
					sprache = ?,
					schlagwoerter_en = ?,
					schlagwoerter = ?,
					abstract = ?,
					abstract_en = ?
					WHERE projektarbeit_id = ?";

		return $this->execQuery($qry, array($seitenanzahl, $sprache, $schlagwoerter_en,
			$schlagwoerter, $abstract, $abstract_en, $projektarbeit_id));
	}

	/**
	 * Get a List of Projektarbeiten of a student with betreuer 
	 * used by the student cis4 abgabetool.
	 */
	public function getStudentProjektarbeitenWithBetreuer($studentUID)
	{
		$betreuerQuery = "SELECT * FROM (SELECT DISTINCT ON(projektarbeit_id)
			vorname as bvorname,
			nachname as bnachname,
			titelpre as btitelpre,
			titelpost AS btitelpost,
			tbl_betreuerart.beschreibung AS betreuerart_beschreibung,
		
			(SELECT person_id
			 FROM lehre.tbl_projektbetreuer
			 WHERE projektarbeit_id=tbl_projektarbeit.projektarbeit_id
			   AND betreuerart_kurzbz IN ('Zweitbetreuer', 'Zweitbegutachter') LIMIT 1) AS zweitbetreuer_person_id,
			(SELECT betreuerart_kurzbz
			 FROM lehre.tbl_projektbetreuer
			 WHERE projektarbeit_id=tbl_projektarbeit.projektarbeit_id
			   AND betreuerart_kurzbz IN ('Zweitbetreuer', 'Zweitbegutachter') LIMIT 1) AS zweitbetreuer_betreuerart_kurzbz,
			(SELECT tbl_betreuerart.beschreibung
			 FROM lehre.tbl_projektbetreuer JOIN lehre.tbl_betreuerart USING(betreuerart_kurzbz)
			 WHERE projektarbeit_id=tbl_projektarbeit.projektarbeit_id
			   AND betreuerart_kurzbz IN ('Zweitbetreuer', 'Zweitbegutachter', 'Senatsmitglied') LIMIT 1) AS zweitbetreuer_betreuerart_beschreibung,
		
			tbl_betreuerart.betreuerart_kurzbz,
			person_id as bperson_id,
			projektarbeit_id,
			lehre.tbl_projekttyp.bezeichnung as projekttypbezeichnung,
			lehre.tbl_lehreinheit.studiensemester_kurzbz,
			lehre.tbl_lehrveranstaltung.studiengang_kz,
			public.tbl_studiengang.kurzbzlang,
			lehre.tbl_projektarbeit.note as note,
			lehre.tbl_note.bezeichnung as note_bezeichnung,
			public.tbl_mitarbeiter.mitarbeiter_uid,
			lehre.tbl_projektarbeit.titel as titel,
			lehre.tbl_projektarbeit.sprache as sprache,
			lehre.tbl_projektarbeit.seitenanzahl as seitenanzahl,
			lehre.tbl_projektarbeit.kontrollschlagwoerter as kontrollschlagwoerter,
			lehre.tbl_projektarbeit.schlagwoerter as schlagwoerter,
			lehre.tbl_projektarbeit.schlagwoerter_en as schlagwoerter_en,
			lehre.tbl_projektarbeit.abstract as abstract,
			lehre.tbl_projektarbeit.abstract_en as abstract_en,
			lehre.tbl_projektarbeit.insertamum as insertamum,
			(SELECT abgeschicktvon FROM extension.tbl_projektarbeitsbeurteilung WHERE projektarbeit_id = tbl_projektarbeit.projektarbeit_id AND betreuer_person_id = tbl_projektbetreuer.person_id) AS babgeschickt,
			(SELECT abgeschicktvon FROM extension.tbl_projektarbeitsbeurteilung WHERE projektarbeit_id = tbl_projektarbeit.projektarbeit_id AND betreuerart_kurzbz IN ('Zweitbetreuer', 'Zweitbegutachter') LIMIT 1) AS zweitbetreuer_abgeschickt,
			(SELECT datum FROM campus.tbl_paabgabe WHERE paabgabetyp_kurzbz = 'end' AND abgabedatum IS NOT NULL AND projektarbeit_id = tbl_projektarbeit.projektarbeit_id LIMIT 1) AS abgegeben
		
		FROM lehre.tbl_projektarbeit
				 LEFT JOIN lehre.tbl_projektbetreuer USING(projektarbeit_id)
				 LEFT JOIN public.tbl_person USING(person_id)
				 LEFT JOIN public.tbl_benutzer USING(person_id)
				 LEFT JOIN lehre.tbl_projekttyp USING (projekttyp_kurzbz)
				 LEFT JOIN lehre.tbl_betreuerart USING(betreuerart_kurzbz)
				 LEFT JOIN lehre.tbl_lehreinheit USING(lehreinheit_id)
				 LEFT JOIN lehre.tbl_lehrveranstaltung USING(lehrveranstaltung_id)
				 LEFT JOIN public.tbl_mitarbeiter ON(public.tbl_mitarbeiter.mitarbeiter_uid = public.tbl_benutzer.uid)
				 LEFT JOIN public.tbl_studiengang USING(studiengang_kz)
				 LEFT JOIN lehre.tbl_note ON(lehre.tbl_projektarbeit.note = lehre.tbl_note.note)
		WHERE
			tbl_projektarbeit.student_uid = ? AND mitarbeiter_uid IS NOT NULL AND
			(projekttyp_kurzbz='Bachelor' OR projekttyp_kurzbz='Diplom')
		  AND betreuerart_kurzbz IN ('Betreuer', 'Begutachter', 'Erstbegutachter', 'Senatsvorsitz')) as base
		ORDER BY insertamum DESC";
				
		return $this->execReadOnlyQuery($betreuerQuery, array($studentUID));
	}

	/**
	 * Get a List of Projektarbeit Abgabetermin used by the student cis4 abgabetool.
	 */
	public function getProjektarbeitAbgabetermine($projektarbeit_id) {
		$qry ="SELECT campus.tbl_paabgabe.paabgabe_id, 
					campus.tbl_paabgabe.projektarbeit_id,
					campus.tbl_paabgabe.fixtermin,
					campus.tbl_paabgabe.kurzbz,
					campus.tbl_paabgabe.datum,
					campus.tbl_paabgabe.note,
					campus.tbl_paabgabe.upload_allowed,
					campus.tbl_paabgabe.beurteilungsnotiz,
					campus.tbl_paabgabetyp.paabgabetyp_kurzbz, 
					campus.tbl_paabgabetyp.bezeichnung, 
					campus.tbl_paabgabe.abgabedatum,
					campus.tbl_paabgabe.insertvon
				FROM campus.tbl_paabgabe JOIN campus.tbl_paabgabetyp USING(paabgabetyp_kurzbz)
				WHERE campus.tbl_paabgabe.projektarbeit_id = ?
				ORDER BY campus.tbl_paabgabe.datum";

		return $this->execReadOnlyQuery($qry, array($projektarbeit_id));
	}

	public function getProjektbetreuerAnrede($bperson_id) {
		$qry_betr="SELECT distinct trim(COALESCE(titelpre,'')||' '||COALESCE(vorname,'')||' '||COALESCE(nachname,'')||' '||COALESCE(titelpost,'')) as first,
						public.tbl_mitarbeiter.mitarbeiter_uid, anrede
						FROM public.tbl_person JOIN lehre.tbl_projektbetreuer ON(lehre.tbl_projektbetreuer.person_id=public.tbl_person.person_id)
						JOIN public.tbl_benutzer ON(public.tbl_benutzer.person_id=public.tbl_person.person_id)
						JOIN public.tbl_mitarbeiter ON(public.tbl_benutzer.uid=public.tbl_mitarbeiter.mitarbeiter_uid)
						WHERE public.tbl_person.person_id= ?";

		return $this->execReadOnlyQuery($qry_betr, [$bperson_id]);
	}
	
	public function getProjektbetreuerEmail($projektarbeit_id) {
		$qry = "SELECT (
				   SELECT kontakt
				   FROM public.tbl_kontakt
				   WHERE kontakttyp = 'email'
					 AND person_id = pers.person_id
				   ORDER BY
					   CASE WHEN zustellung THEN 0 ELSE 1 END,
					   insertamum DESC NULLS LAST
				   LIMIT 1
			   ) AS private_email
		FROM lehre.tbl_projektarbeit pa
				 JOIN lehre.tbl_projektbetreuer USING (projektarbeit_id)
				 JOIN public.tbl_person pers USING (person_id)
				 LEFT JOIN public.tbl_benutzer ben USING (person_id)
				 LEFT JOIN public.tbl_mitarbeiter ma ON ben.uid = ma.mitarbeiter_uid
		WHERE (ben.aktiv OR ben.aktiv IS NULL)
		  AND projektarbeit_id = ?";

		return $this->execReadOnlyQuery($qry, [$projektarbeit_id]);
	}

	public function getProjektarbeitBenutzer($uid) {
		$qry="SELECT * FROM campus.vw_benutzer where uid=?";
		return $this->execReadOnlyQuery($qry, [$uid]);
	}
	
	/**
	 * Checks if mitarbeiter has a projektbetreuer zuordnung to student.
	 */
	public function checkZuordnung($studentUID, $maUID) {
		//oder Lektor mit Betreuung dieses Studenten
		$qry = "
			SELECT 1
			FROM
				lehre.tbl_projektarbeit
				JOIN lehre.tbl_projektbetreuer USING(projektarbeit_id)
				JOIN campus.vw_benutzer on(vw_benutzer.person_id=tbl_projektbetreuer.person_id)
			WHERE
				tbl_projektarbeit.student_uid = ? AND
				vw_benutzer.uid = ?";

		return $this->execReadOnlyQuery($qry, array($studentUID, $maUID));
	}

	/**
	 * Get a List of Projektarbeiten of a mitarbeiter with zuordnung
	 * used by the mitarbeiter cis4 abgabetool.
	 */
	public function getMitarbeiterProjektarbeiten($uid, $showAll){
		$qry = "SELECT
					*
				FROM
					(SELECT tbl_person.vorname, tbl_person.nachname, tbl_studiengang.typ, tbl_studiengang.kurzbz,
							tbl_projektarbeit.projekttyp_kurzbz, tbl_projekttyp.bezeichnung, tbl_projektarbeit.titel, tbl_projektarbeit.projektarbeit_id,
							tbl_projektbetreuer.person_id as betreuer_person_id, tbl_projektbetreuer.betreuerart_kurzbz, tbl_betreuerart.beschreibung AS betreuerart_beschreibung,
							tbl_benutzer.uid, tbl_student.matrikelnr, tbl_lehreinheit.studiensemester_kurzbz
					 FROM lehre.tbl_projektarbeit
							  LEFT JOIN lehre.tbl_projektbetreuer using(projektarbeit_id)
							  LEFT JOIN lehre.tbl_betreuerart using(betreuerart_kurzbz)
							  LEFT JOIN public.tbl_benutzer on(uid=student_uid)
							  LEFT JOIN public.tbl_student on(public.tbl_benutzer.uid=public.tbl_student.student_uid)
							  LEFT JOIN public.tbl_person on(tbl_benutzer.person_id=tbl_person.person_id)
							  LEFT JOIN lehre.tbl_lehreinheit using(lehreinheit_id)
							  LEFT JOIN lehre.tbl_lehrveranstaltung using(lehrveranstaltung_id)
							  LEFT JOIN public.tbl_studiengang on(lehre.tbl_lehrveranstaltung.studiengang_kz=public.tbl_studiengang.studiengang_kz)
							  LEFT JOIN lehre.tbl_projekttyp USING (projekttyp_kurzbz)
					 WHERE (projekttyp_kurzbz='Bachelor' OR projekttyp_kurzbz='Diplom')
					   AND tbl_projektbetreuer.person_id IN (SELECT person_id FROM public.tbl_benutzer
															 WHERE public.tbl_benutzer.person_id=lehre.tbl_projektbetreuer.person_id
															   AND public.tbl_benutzer.uid= ? )
						 ".($showAll?'':' AND public.tbl_benutzer.aktiv AND lehre.tbl_projektarbeit.note IS NULL ')."
							AND betreuerart_kurzbz IN ('Betreuer', 'Begutachter', 'Erstbegutachter', 'Zweitbegutachter', 'Erstbetreuer', 'Senatsvorsitz', 'Senatsmitglied')
					 ORDER BY tbl_projektarbeit.projektarbeit_id, betreuerart_kurzbz desc) as xy
				ORDER BY nachname;";

		return $this->execReadOnlyQuery($qry, array($uid));
	}

	/**
	 * Fetch Student info relevant to a projektarbeit_id
	 */
	public function getStudentInfoForProjektarbeitId($projektarbeit_id) {
		
		$qry = "SELECT *
				FROM campus.vw_student 
				WHERE uid IN(
					SELECT student_uid 
					FROM lehre.tbl_projektarbeit 
					WHERE projektarbeit_id = ? )";
		
		return $this->execReadOnlyQuery($qry, array($projektarbeit_id));
	}

	/**
	 * Prüft ob Projektarbeit aktuell ist (also zurzeit online bewertet wird).
	 * @param $projektarbeit_id
	 * @return boolean
	 */
	public function projektarbeitIsCurrent($projektarbeit_id) {
		$version = $this->getVersion($projektarbeit_id);
		// paarbeit sollte nur ab einem Studiensemester online bewertet werden
		return $version === null ? null : $version->isCurrent;
	}

	/**
	 * Holt sich Version der Projektarbeit.
	 * Liefert auch mit, ob die Version die aktuellste ist.
	 * z.B.: Masterarbeiten waren ab der Änderung zur Gewichtung der Punkte aktuell,
	 * Bachelorarbeiten waren ab dem Umstieg auf das Online Beurteilungsformular aktuell.
	 * @param $projektarbeit_id
	 * @return objekt mit Versionsinfo, null im Fehlerfall
	 */
	private function getVersion($projektarbeit_id) {
		$_versions_query = array(
			'Diplom' => array(
				'SS2025',
				'SS2023',
				'SS2022'
			),
			'Others' => array(
				'SS2025',
				'SS2022',
			)
		);

		$_versions_check = array(
			'Diplom' => array(
				'SS2025' => 3,
				'SS2023' => 2,
				'SS2022' => 1
			),
			'Others' => array(
				'SS2025' => 2,
				'SS2022' => 1
			)
		);
		
		// paarbeit sollte nur ab einem Studiensemester online bewertet werden
		$qry="
			SELECT
				CASE
					WHEN semesters_diplom.studiensemester_kurzbz IS NOT NULL
					THEN semesters_diplom.studiensemester_kurzbz
					ELSE semesters.studiensemester_kurzbz 
				END AS version_studiensemester_kurzbz,
				pa.projekttyp_kurzbz
			FROM
				lehre.tbl_projektarbeit pa
				JOIN lehre.tbl_lehreinheit USING(lehreinheit_id)
				JOIN public.tbl_studiensemester sem USING(studiensemester_kurzbz)
				LEFT JOIN (
					SELECT
						start, studiensemester_kurzbz
					FROM
						public.tbl_studiensemester
					WHERE
						studiensemester_kurzbz IN ?
				) semesters ON sem.start >= semesters.start AND pa.projekttyp_kurzbz <> 'Diplom'
				LEFT JOIN (
					SELECT
						start, studiensemester_kurzbz
					FROM
						public.tbl_studiensemester
					WHERE
						studiensemester_kurzbz IN ?
				) semesters_diplom ON sem.start >= semesters_diplom.start AND pa.projekttyp_kurzbz = 'Diplom'
			WHERE
				projektarbeit_id = ?
			ORDER BY
				semesters.start DESC, semesters_diplom.start DESC
			LIMIT 1";

		$resultociniBambini = $this->execReadOnlyQuery($qry, array($_versions_query['Others'], $_versions_query['Diplom'], $projektarbeit_id));
		
		if(hasData($resultociniBambini)) {
			$data =	getData($resultociniBambini);
			if(count($data) > 0) {
				$row = $data[0];
				
				// known project types
				if (isset($_versions_check[$row->projekttyp_kurzbz][$row->version_studiensemester_kurzbz]))
				{
					$row->versionNumber = $_versions_check[$row->projekttyp_kurzbz][$row->version_studiensemester_kurzbz];
					$row->isCurrent =
						$_versions_check[$row->projekttyp_kurzbz][$row->version_studiensemester_kurzbz]
						== max($_versions_check[$row->projekttyp_kurzbz]);

				}
				elseif (isset($_versions_check['Others'][$row->version_studiensemester_kurzbz]))
				{
					$row->versionNumber = $_versions_check['Others'][$row->version_studiensemester_kurzbz];
					$row->isCurrent =
						$_versions_check['Others'][$row->version_studiensemester_kurzbz]
						== max($_versions_check['Others']);
				}
				else
				{
					$row->isCurrent = false;
					$row->versionNumber = 0;
				}
				return $row;
				
			} else {
				return null;
			}
		} else {
			return null;
		}

	/*
	 *
	 * @param
	 * @return object success or error
	 */
	public function hasBerechtigungForProjektarbeit($projektarbeit_id)
	{
		if (!$projektarbeit_id || !is_numeric($projektarbeit_id))
			return false;

		$this->ProjektarbeitModel->addSelect('studiengang_kz');
		$this->ProjektarbeitModel->addJoin('public.tbl_student', 'student_uid');
		$result = $this->ProjektarbeitModel->load($projektarbeit_id);
		if (isError($result) || !hasData($result))
			return false;

		$studiengang_kz = getData($result)[0]->studiengang_kz;

		if ($this->permissionlib->isBerechtigt('admin', 'suid', $studiengang_kz))
			return true;
		if ($this->permissionlib->isBerechtigt('assistenz', 'suid', $studiengang_kz))
			return true;

		return false;

	}
}
