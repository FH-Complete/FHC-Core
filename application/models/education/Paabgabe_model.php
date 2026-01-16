<?php
class Paabgabe_model extends DB_Model
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'campus.tbl_paabgabe';
		$this->pk = 'paabgabe_id';
	}

	/**
	 * Gets last Endabgabe of a Projektarbeit, including filename.
	 * @param int $projektarbeit_id
	 * @return object
	 */
	public function getEndabgabe($projektarbeit_id)
	{
		$qry = "SELECT paabgabe_id, student_uid, paabg.datum, paabg.abgabedatum, projekttyp_kurzbz, titel, titel_english,
					paabgabe_id || '_' || student_uid || '.pdf' AS filename
				FROM campus.tbl_paabgabe paabg
				JOIN lehre.tbl_projektarbeit USING (projektarbeit_id)
				WHERE projektarbeit_id = ?
				AND paabgabetyp_kurzbz = 'end'
				AND paabg.abgabedatum IS NOT NULL
				ORDER BY paabg.abgabedatum DESC, paabg.datum DESC
				LIMIT 1";

		return $this->execQuery($qry, array($projektarbeit_id));
	}

	/**
	 * Gets all Paabgabe Termin Deadlines of zugewiesene Projektarbeiten as a Mitarbeiter for Terminübersicht Abgabetool.
	 * @param int $person_id
	 * @return object
	 */
	public function getDeadlines($person_id)
	{
		$qry = "SELECT
					DISTINCT TO_CHAR(tbl_paabgabe.datum, 'DD.MM.YYYY') as datum, tbl_paabgabe.fixtermin, tbl_paabgabe.kurzbz,
					person_student.vorname as stud_vorname, person_student.nachname as stud_nachname,
					person_student.titelpre as stud_titelpre, person_student.titelpost as stud_titelpost,
					tbl_lehrveranstaltung.semester, UPPER(tbl_studiengang.typ || tbl_studiengang.kurzbz) as stg,
					tbl_paabgabetyp.bezeichnung as typ_bezeichnung
				FROM
					campus.tbl_paabgabe
					JOIN lehre.tbl_projektarbeit USING(projektarbeit_id)
					JOIN lehre.tbl_projektbetreuer USING(projektarbeit_id)
					JOIN public.tbl_benutzer bn_student ON(tbl_projektarbeit.student_uid=bn_student.uid)
					JOIN public.tbl_person person_student ON(bn_student.person_id=person_student.person_id)
					JOIN lehre.tbl_lehreinheit ON(tbl_projektarbeit.lehreinheit_id=tbl_lehreinheit.lehreinheit_id)
					JOIN lehre.tbl_lehrveranstaltung ON(tbl_lehreinheit.lehrveranstaltung_id=tbl_lehrveranstaltung.lehrveranstaltung_id)
					JOIN public.tbl_studiengang ON(tbl_lehrveranstaltung.studiengang_kz=tbl_studiengang.studiengang_kz)
					JOIN campus.tbl_paabgabetyp USING(paabgabetyp_kurzbz)
				WHERE
					tbl_projektbetreuer.person_id= ? AND tbl_paabgabe.datum>=now() AND bn_student.aktiv
				ORDER BY datum";

		return $this->execReadOnlyQuery($qry, array($person_id));
	}

	public function findAbgabenNewOrUpdatedSince($interval)
	{

		$query = "SELECT projektarbeit_id, paabgabe_id, paabgabetyp_kurzbz, fixtermin, datum, campus.tbl_paabgabe.kurzbz, campus.tbl_paabgabetyp.bezeichnung, campus.tbl_paabgabe.abgabedatum,
			   campus.tbl_paabgabe.insertvon, campus.tbl_paabgabe.insertamum, campus.tbl_paabgabe.updatevon, campus.tbl_paabgabe.updateamum,
			   campus.tbl_paabgabe.note, upload_allowed, beurteilungsnotiz, student_uid, tbl_projektarbeit.note, lehre.tbl_projektarbeit.titel,
			   UPPER(tbl_studiengang.typ) as stgtyp, UPPER(tbl_studiengang.kurzbz) as stgkz, tbl_lehreinheit.studiensemester_kurzbz,
			   public.tbl_person.anrede, public.tbl_person.titelpre, public.tbl_person.vorname, public.tbl_person.nachname, public.tbl_person.titelpost
		FROM campus.tbl_paabgabe
				 JOIN campus.tbl_paabgabetyp USING (paabgabetyp_kurzbz)
				 JOIN lehre.tbl_projektarbeit USING (projektarbeit_id)
				 JOIN lehre.tbl_lehreinheit using(lehreinheit_id)
				 JOIN lehre.tbl_lehrveranstaltung using(lehrveranstaltung_id)
				 JOIN public.tbl_studiengang on(lehre.tbl_lehrveranstaltung.studiengang_kz=public.tbl_studiengang.studiengang_kz)
				 JOIN public.tbl_benutzer ON (public.tbl_benutzer.uid = student_uid)
				 JOIN public.tbl_person USING (person_id)
		
		WHERE campus.tbl_paabgabe.insertamum >= NOW() - INTERVAL ?
		   OR campus.tbl_paabgabe.updateamum >= NOW() - INTERVAL ?";

		return $this->execQuery($query, [$interval, $interval]);
	}

	public function findAbgabenNewOrUpdatedSinceByAbgabedatum($interval) {

		$query = "SELECT projektarbeit_id, paabgabe_id, paabgabetyp_kurzbz, fixtermin, datum, kurzbz, campus.tbl_paabgabetyp.bezeichnung, campus.tbl_paabgabe.abgabedatum,
						campus.tbl_paabgabe.insertvon, campus.tbl_paabgabe.insertamum, campus.tbl_paabgabe.updatevon, campus.tbl_paabgabe.updateamum,
						campus.tbl_paabgabe.note, upload_allowed, beurteilungsnotiz, student_uid, tbl_projektarbeit.note, lehre.tbl_projektarbeit.titel,
						lehre.tbl_projektbetreuer.betreuerart_kurzbz, lehre.tbl_projektbetreuer.person_id,
						public.tbl_person.anrede, public.tbl_person.titelpre, public.tbl_person.vorname, public.tbl_person.nachname, public.tbl_person.titelpost
				
				FROM campus.tbl_paabgabe 
					JOIN campus.tbl_paabgabetyp USING (paabgabetyp_kurzbz)
					JOIN lehre.tbl_projektarbeit USING (projektarbeit_id)
					JOIN lehre.tbl_projektbetreuer USING (projektarbeit_id)
					JOIN public.tbl_benutzer ON (public.tbl_benutzer.uid = student_uid)
				 	JOIN public.tbl_person ON (public.tbl_benutzer.person_id = public.tbl_person.person_id)
				
				WHERE campus.tbl_paabgabe.abgabedatum IS NOT NULL 
				AND campus.tbl_paabgabe.abgabedatum >= NOW() - INTERVAL ?
				ORDER BY abgabedatum DESC
	";

		return $this->execQuery($query, [$interval]);
	}
}
