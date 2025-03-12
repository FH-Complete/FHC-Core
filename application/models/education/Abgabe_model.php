<?php
class Abgabe_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'campus.tbl_abgabe';
		$this->pk = 'abgabe_id';
	}
	
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
	
	public function getProjektarbeitAbgabetermine($projektarbeit_id) {
		$qry ="SELECT campus.tbl_paabgabe.paabgabe_id, 
					   campus.tbl_paabgabe.projektarbeit_id,
					   campus.tbl_paabgabe.fixtermin,
					   campus.tbl_paabgabe.kurzbz,
					   campus.tbl_paabgabe.datum,
					   campus.tbl_paabgabetyp.bezeichnung, 
					   campus.tbl_paabgabe.abgabedatum
				FROM campus.tbl_paabgabe JOIN campus.tbl_paabgabetyp USING(paabgabetyp_kurzbz)
				WHERE campus.tbl_paabgabe.projektarbeit_id = ?
				ORDER BY campus.tbl_paabgabe.datum";
		
		return $this->execReadOnlyQuery($qry, array($projektarbeit_id));
	}
	
	public function getStudentProjektarbeitenWithBetreuer($studentUID) {
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
		
		// TODO: fetch senatsmitglieder
		return $this->execReadOnlyQuery($betreuerQuery, array($studentUID));
	}

	public function getStudentProjektarbeitenLegacy($studentUID)
	{
		$qry = "SELECT (SELECT nachname FROM public.tbl_person  WHERE person_id=tbl_projektbetreuer.person_id) AS bnachname,
	   (SELECT vorname FROM public.tbl_person WHERE person_id=tbl_projektbetreuer.person_id) AS bvorname,
	   (SELECT titelpre FROM public.tbl_person WHERE person_id=tbl_projektbetreuer.person_id) AS btitelpre,
	   (SELECT titelpost FROM public.tbl_person WHERE person_id=tbl_projektbetreuer.person_id) AS btitelpost,
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
	   tbl_projektbetreuer.person_id AS betreuer_person_id,
	   tbl_projekttyp.bezeichnung AS prjbez,
	   lehre.tbl_projektbetreuer.note as note,
	   public.tbl_benutzer.aktiv as aktiv,
	   (SELECT abgeschicktvon 
			FROM extension.tbl_projektarbeitsbeurteilung 
			WHERE projektarbeit_id = tbl_projektarbeit.projektarbeit_id 
			AND betreuer_person_id = tbl_projektbetreuer.person_id) AS babgeschickt,
	   (SELECT abgeschicktvon 
			FROM extension.tbl_projektarbeitsbeurteilung 
			WHERE projektarbeit_id = tbl_projektarbeit.projektarbeit_id 
			AND betreuerart_kurzbz IN ('Zweitbetreuer', 'Zweitbegutachter') LIMIT 1) AS zweitbetreuer_abgeschickt,
	   (SELECT datum FROM campus.tbl_paabgabe 
	                 WHERE paabgabetyp_kurzbz = 'end' 
	                 	AND abgabedatum IS NOT NULL 
	                    AND projektarbeit_id = tbl_projektarbeit.projektarbeit_id LIMIT 1) AS abgegeben,
	*
FROM lehre.tbl_projektarbeit
		 LEFT JOIN lehre.tbl_projektbetreuer USING(projektarbeit_id)
		 LEFT JOIN public.tbl_benutzer ON(uid=student_uid)
		 LEFT JOIN public.tbl_person ON(tbl_benutzer.person_id=tbl_person.person_id)
		 LEFT JOIN lehre.tbl_lehreinheit USING(lehreinheit_id)
		 LEFT JOIN lehre.tbl_lehrveranstaltung USING(lehrveranstaltung_id)
		 LEFT JOIN public.tbl_studiengang USING(studiengang_kz)
		 LEFT JOIN lehre.tbl_projekttyp USING (projekttyp_kurzbz)
		 LEFT JOIN lehre.tbl_betreuerart USING(betreuerart_kurzbz)
WHERE (projekttyp_kurzbz='Bachelor' OR projekttyp_kurzbz='Diplom')
  AND betreuerart_kurzbz IN ('Betreuer', 'Begutachter', 'Erstbegutachter', 'Senatsvorsitz')
  AND tbl_projektarbeit.student_uid= ?
ORDER BY studiensemester_kurzbz desc, tbl_lehrveranstaltung.kurzbz";
		
		return $this->execQuery($qry, array($studentUID));
	}
	
}
