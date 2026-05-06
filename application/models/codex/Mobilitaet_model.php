<?php
class Mobilitaet_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'bis.tbl_mobilitaet';
		$this->pk = 'mobilitaet_id';
	}
	
	public function getMobilityZusatzForUids($uids) {
		$qry = "SELECT distinct on(nachname, vorname, public.tbl_benutzer.person_id) uid,
			tbl_mitarbeiter.mitarbeiter_uid,
			tbl_note.lkt_ueberschreibbar, tbl_note.anmerkung,
			tbl_mobilitaet.mobilitaetstyp_kurzbz,
			(CASE WHEN bis.tbl_mobilitaet.studiensemester_kurzbz = vw_student_lehrveranstaltung.studiensemester_kurzbz THEN 1 ELSE 0 END) as doubledegree,
			public.tbl_prestudent.gsstudientyp_kurzbz as ddtype,
			(SELECT status_kurzbz FROM public.tbl_prestudentstatus
				WHERE prestudent_id=tbl_student.prestudent_id
				ORDER BY datum DESC, insertamum DESC, ext_id DESC LIMIT 1) as studienstatus
		FROM
			campus.vw_student_lehrveranstaltung
			 JOIN public.tbl_benutzer USING(uid)
			 JOIN public.tbl_person USING(person_id)
			 LEFT JOIN public.tbl_student ON(uid=student_uid)
			 LEFT JOIN public.tbl_mitarbeiter ON(uid=mitarbeiter_uid)
			 LEFT JOIN public.tbl_studentlehrverband USING(student_uid,studiensemester_kurzbz)
			 LEFT JOIN lehre.tbl_zeugnisnote on(vw_student_lehrveranstaltung.lehrveranstaltung_id=tbl_zeugnisnote.lehrveranstaltung_id
				AND tbl_zeugnisnote.student_uid=tbl_student.student_uid
				AND tbl_zeugnisnote.studiensemester_kurzbz=tbl_studentlehrverband.studiensemester_kurzbz)
			LEFT JOIN lehre.tbl_note USING (note)
			LEFT JOIN bis.tbl_bisio ON(uid=tbl_bisio.student_uid)
			LEFT JOIN public.tbl_studiengang ON(tbl_student.studiengang_kz=tbl_studiengang.studiengang_kz)
				LEFT JOIN bis.tbl_mobilitaet USING(prestudent_id)
				LEFT JOIN public.tbl_prestudent USING(prestudent_id)
		WHERE uid IN ?";
		
		return $this->execReadOnlyQuery($qry, [$uids]);
	}

	public function formatZusatz($entry, $erhalter_kz) {
		$zusatz = '';

		if (isset($entry->studienstatus) && $entry->studienstatus === 'Incoming') {
			$zusatz = '(i)';
		}

		if (isset($entry->lkt_ueberschreibbar) && $entry->lkt_ueberschreibbar === false) {
			$zusatz .= ' (' . ($entry->anmerkung ?? '') . ')';
		}

		if (isset($entry->mitarbeiter_uid) && $entry->mitarbeiter_uid !== null) {
			$zusatz .= ' (ma)';
		}

		if (isset($entry->stg_kz_student) && $entry->stg_kz_student == $erhalter_kz) {
			$zusatz .= ' (a.o.)';
		}

		if (
			isset($entry->mobilitaetstyp_kurzbz) && $entry->mobilitaetstyp_kurzbz &&
			isset($entry->doubledegree) && $entry->doubledegree === 1
		) {
			$zusatz .= ' (d.d.';

			$ddtype = $entry->ddtype ?? null;

			if ($ddtype == 'Intern') {
				$zusatz .= 'i.)';
			} elseif ($ddtype == 'Extern') {
				$zusatz .= 'o.)';
			} else {
				$zusatz .= ')';
			}
		}

		return $zusatz;
	}
}
