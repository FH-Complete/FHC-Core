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
					tbl_projektarbeit.* , tbl_projekttyp.bezeichnung
				FROM
					lehre.tbl_projektarbeit
				JOIN
					lehre.tbl_projekttyp USING (projekttyp_kurzbz), lehre.tbl_lehreinheit, lehre.tbl_lehrveranstaltung

				WHERE
					tbl_projektarbeit.lehreinheit_id=tbl_lehreinheit.lehreinheit_id AND
					tbl_lehreinheit.lehrveranstaltung_id = tbl_lehrveranstaltung.lehrveranstaltung_id AND
					tbl_projektarbeit.student_uid = ?";

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
		$betreuerQuery = "
		SELECT 
			vorname as bvorname,
			nachname as bnachname,
			titelpre as btitelpre,
			titelpost AS btitelpost,
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
			lehre.tbl_projektbetreuer.note as note,
			public.tbl_mitarbeiter.mitarbeiter_uid,
			lehre.tbl_projektarbeit.titel as titel,
			lehre.tbl_projektarbeit.sprache as sprache,
			lehre.tbl_projektarbeit.seitenanzahl as seitenanzahl,
			lehre.tbl_projektarbeit.kontrollschlagwoerter as kontrollschlagwoerter,
			lehre.tbl_projektarbeit.schlagwoerter as schlagwoerter,
			lehre.tbl_projektarbeit.schlagwoerter_en as schlagwoerter_en,
			lehre.tbl_projektarbeit.abstract as abstract,
			lehre.tbl_projektarbeit.abstract_en as abstract_en, 
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
		WHERE 
			tbl_projektarbeit.student_uid = ? AND
			(projekttyp_kurzbz='Bachelor' OR projekttyp_kurzbz='Diplom')
  		AND betreuerart_kurzbz IN ('Betreuer', 'Begutachter', 'Erstbegutachter', 'Senatsvorsitz')";
		
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
					campus.tbl_paabgabetyp.paabgabetyp_kurzbz, 
					campus.tbl_paabgabetyp.bezeichnung, 
					campus.tbl_paabgabe.abgabedatum
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
}
