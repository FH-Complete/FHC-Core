<?php
class LePruefung_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'lehre.tbl_pruefung';
		$this->pk = 'pruefung_id';
	}

	/**
	 * gets all Pruefungen for a student_uid
	 * @param string		$student_uid
	 * @param string		$studiensemester_kurzbz
	 *
	 * @return stdClass
	 */
	public function getPruefungenByStudentuid($student_uid, $studiensemester_kurzbz = null)
	{
		$this->addSelect('tbl_pruefung.datum');
		$this->addSelect("TO_CHAR(tbl_pruefung.datum::timestamp, 'DD.MM.YYYY') AS format_datum");
		$this->addSelect('tbl_pruefung.anmerkung');
		$this->addSelect('tbl_pruefung.pruefungstyp_kurzbz');
		$this->addSelect('tbl_pruefung.pruefung_id');
		$this->addSelect('tbl_pruefung.lehreinheit_id');
		$this->addSelect('tbl_pruefung.student_uid');
		$this->addSelect('tbl_pruefung.mitarbeiter_uid');
		$this->addSelect('tbl_pruefung.punkte');

		$this->addSelect('tbl_lehrveranstaltung.bezeichnung as lehrveranstaltung_bezeichnung');
		$this->addSelect('tbl_lehrveranstaltung.lehrveranstaltung_id');
		$this->addSelect('tbl_note.bezeichnung as note_bezeichnung');
		$this->addSelect('tbl_pruefungstyp.beschreibung as typ_beschreibung');
		$this->addSelect('tbl_lehreinheit.studiensemester_kurzbz as studiensemester_kurzbz');

		$this->addJoin('lehre.tbl_lehreinheit',  'lehre.tbl_pruefung.lehreinheit_id=lehre.tbl_lehreinheit.lehreinheit_id');
		$this->addJoin('lehre.tbl_lehrveranstaltung', 'lehrveranstaltung_id');
		$this->addJoin('lehre.tbl_note', 'note');
		$this->addJoin('lehre.tbl_pruefungstyp', 'pruefungstyp_kurzbz');

		if ($studiensemester_kurzbz)
			$this->db->where("tbl_lehreinheit.studiensemester_kurzbz = ", $studiensemester_kurzbz);

		$this->addOrder('tbl_pruefung.datum', 'DESC');
		$this->addOrder('tbl_pruefung.pruefung_id', 'DESC');

		return $this->loadWhere([
			'student_uid' => $student_uid
		]);
	}

	/**
	 * Alle Prüfungen einer Lehrveranstaltung in einem Studiensemester.
	 *
	 * Note und Prüfungstyp hängen als LEFT JOIN: eine Zeile mit einem Typ, den tbl_pruefungstyp nicht
	 * mehr kennt, muss im Verlauf bleiben. Sonst zählt die Regelprüfung einen Antritt zu wenig.
	 */
	public function getPruefungenByLvStudiensemester($lv_id, $sem_kurzbz) {
		$qry = "SELECT tbl_pruefung.*, tbl_lehrveranstaltung.bezeichnung as lehrveranstaltung_bezeichnung, tbl_lehrveranstaltung.lehrveranstaltung_id,
				   tbl_note.bezeichnung as note_bezeichnung, tbl_pruefungstyp.beschreibung as typ_beschreibung, tbl_lehreinheit.studiensemester_kurzbz as studiensemester_kurzbz
			FROM lehre.tbl_pruefung
				JOIN lehre.tbl_lehreinheit ON (tbl_pruefung.lehreinheit_id = tbl_lehreinheit.lehreinheit_id)
				JOIN lehre.tbl_lehrveranstaltung ON (tbl_lehreinheit.lehrveranstaltung_id = tbl_lehrveranstaltung.lehrveranstaltung_id)
				LEFT JOIN lehre.tbl_note ON (tbl_pruefung.note = tbl_note.note)
				LEFT JOIN lehre.tbl_pruefungstyp ON (tbl_pruefung.pruefungstyp_kurzbz = tbl_pruefungstyp.pruefungstyp_kurzbz)
			WHERE tbl_lehrveranstaltung.lehrveranstaltung_id = ?
			  AND tbl_lehreinheit.studiensemester_kurzbz = ?
			ORDER BY tbl_pruefung.datum DESC;";

		return $this->execReadOnlyQuery($qry, array($lv_id, $sem_kurzbz));
	}

	/**
	 * Die Prüfungen eines Studenten, optional je Typ, Lehrveranstaltung und Studiensemester.
	 *
	 * Note und Prüfungstyp hängen als LEFT JOIN. PruefungsverlaufLib leitet die Antrittsnummern aus
	 * dieser Liste ab. Eine fehlende Zeile verschiebt die ganze Kette.
	 */
	public function getPruefungenByUidTypLvStudiensemester($uid, $typ = null, $lv_id = null, $sem_kurzbz = null) {
		$params = [$uid];
		$qry = "SELECT tbl_pruefung.*, tbl_lehrveranstaltung.bezeichnung as lehrveranstaltung_bezeichnung, tbl_lehrveranstaltung.lehrveranstaltung_id,
			    tbl_note.bezeichnung as note_bezeichnung, tbl_pruefungstyp.beschreibung as typ_beschreibung, tbl_lehreinheit.studiensemester_kurzbz as studiensemester_kurzbz
			    FROM lehre.tbl_pruefung
					JOIN lehre.tbl_lehreinheit ON (tbl_pruefung.lehreinheit_id = tbl_lehreinheit.lehreinheit_id)
					JOIN lehre.tbl_lehrveranstaltung ON (tbl_lehreinheit.lehrveranstaltung_id = tbl_lehrveranstaltung.lehrveranstaltung_id)
					LEFT JOIN lehre.tbl_note ON (tbl_pruefung.note = tbl_note.note)
					LEFT JOIN lehre.tbl_pruefungstyp ON (tbl_pruefung.pruefungstyp_kurzbz = tbl_pruefungstyp.pruefungstyp_kurzbz)
			    WHERE student_uid= ?";
		if ($typ != null)
		{
			$qry .= " AND tbl_pruefung.pruefungstyp_kurzbz = ?";
			$params[] = $typ;
		}

		if ($lv_id != null)
		{
			$qry .= " AND tbl_lehrveranstaltung.lehrveranstaltung_id = ?";
			$params[] = $lv_id;
		}

		if ($sem_kurzbz != null)
		{
			$qry .= " AND tbl_lehreinheit.studiensemester_kurzbz = ?";
			$params[] = $sem_kurzbz;
		}


		$qry .= " ORDER BY tbl_pruefung.datum DESC";

		return $this->execReadOnlyQuery($qry, $params);
	}
}
