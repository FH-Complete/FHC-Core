<?php

class Studienplan_model extends DB_Model
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = "lehre.tbl_studienplan";
		$this->pk = "studienplan_id";
	}

	public function getStudienplaene($studiengang_kz)
	{
		$this->addJoin("lehre.tbl_studienordnung", "studienordnung_id");

		return $this->loadWhere(array("studiengang_kz" => $studiengang_kz));
	}

	public function getStudienplaeneBySemester($studiengang_kz, $studiensemester_kurzbz, $ausbildungssemester = null, $orgform_kurzbz = null, $sprache = null)
	{
		$this->addJoin("lehre.tbl_studienordnung", "studienordnung_id");
		$this->addJoin("lehre.tbl_studienplan_semester", "studienplan_id");

		$whereArray = array(
			"tbl_studienplan.aktiv" => "TRUE",
			"tbl_studienordnung.studiengang_kz" => $studiengang_kz,
			"tbl_studienplan_semester.studiensemester_kurzbz" => $studiensemester_kurzbz
		);

		if(!is_null($ausbildungssemester))
		{
			$whereArray["tbl_studienplan_semester.semester"] = $ausbildungssemester;
		}

		if(!is_null($orgform_kurzbz))
		{
			$whereArray["orgform_kurzbz"] = $orgform_kurzbz;
		}

		if(!is_null($sprache))
		{
			$whereArray["tbl_studienplan.sprache"] = $sprache;
		}

		return $this->loadWhere($whereArray);
	}

	public function getStudienplanLehrveranstaltung($studienplan_id, $semester)
	{
		$this->addJoin('lehre.tbl_studienplan_lehrveranstaltung', 'studienplan_id');
		$this->addJoin('lehre.tbl_lehrveranstaltung', 'lehrveranstaltung_id');
		$this->addOrder('tbl_lehrveranstaltung.sort');

		return $this->loadWhere(array(
			'studienplan_id' => $studienplan_id,
			'tbl_studienplan_lehrveranstaltung.semester' => $semester
		));
	}

	public function getStudienplanLehrveranstaltungForPrestudent($studienplan_id, $semester, $prestudent_id, $note_stsem)
	{
		$lang = 'SELECT index FROM public.tbl_sprache WHERE sprache=' . $this->escape(getUserLanguage());
		$sql = 'SELECT student_uid FROM public.tbl_student WHERE prestudent_id=' . $this->escape($prestudent_id);

		$this->addSelect($this->dbTable . '.*');
		$this->addSelect('lv.*');
		$this->addSelect('COALESCE(n.bezeichnung_mehrsprachig[(' . $lang . ')], NULL) AS note');
		$this->addSelect('n.positiv');
		$this->addSelect('lehre.tbl_studienplan_lehrveranstaltung.studienplan_lehrveranstaltung_id');
		$this->addSelect('lehre.tbl_studienplan_lehrveranstaltung.sort plan_sort');
		$this->addSelect('lehre.tbl_studienplan_lehrveranstaltung.studienplan_lehrveranstaltung_id_parent');

		$this->addJoin('lehre.tbl_studienplan_lehrveranstaltung', 'studienplan_id');
		$this->addJoin('lehre.tbl_lehrveranstaltung lv', 'lehrveranstaltung_id');
		$this->addJoin(
			'lehre.tbl_zeugnisnote zn',
			'zn.lehrveranstaltung_id=lv.lehrveranstaltung_id AND zn.student_uid=(' . $sql . ') AND zn.studiensemester_kurzbz=' . $this->escape($note_stsem),
			'LEFT'
		);
		$this->addJoin('lehre.tbl_note n', 'n.note=zn.note', 'LEFT');

		$this->addOrder('lehre.tbl_studienplan_lehrveranstaltung.sort');
		$this->addOrder('lv.sort');

		return $this->loadWhere(array(
			'studienplan_id' => $studienplan_id,
			'tbl_studienplan_lehrveranstaltung.semester' => $semester
		));
	}
}
