<?php
class Notenschluesselaufteilung_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'lehre.tbl_notenschluesselaufteilung';
		$this->pk = 'notenschluesselaufteilung_id';
	}

	/**
	 * Gives the grade for the points of a course.
	 *
	 * @param number					$points
	 * @param integer					$lehrveranstaltung_id
	 * @param string					$studiensemester_kurzbz
	 *
	 * @return stdClass		returns success(null) if no entry is found
	 */
	public function getNote($points, $lehrveranstaltung_id, $studiensemester_kurzbz)
	{
		// Without a valid points value you cannot derive a grade. Without this guard the query
		// builder makes a bad statement from "punkte <=" => null ('punkte' < 'IS' 'NULL'). The
		// request then fails with HTTP 500 instead of an answer that says "no grade".
		if (!is_numeric($points))
			return success(null);

		$this->load->model('education/Notenschluesselzuordnung_model', 'NotenschluesselzuordnungModel');
		$notenschluessel_kurzbz = $this->NotenschluesselzuordnungModel->getKurzbzForLv($lehrveranstaltung_id, $studiensemester_kurzbz);

		if($notenschluessel_kurzbz == null)
			return success(null);
		
		$this->addSelect("note");
		$this->addOrder("punkte", "DESC");
		$this->addLimit(1);

		$result = $this->loadWhere([
			"notenschluessel_kurzbz" => $notenschluessel_kurzbz,
			"punkte <=" => $points
		]);

		if (isError($result))
			return $result;
		if (!hasData($result))
			return success(null);
		return success(current(getData($result))->note);
	}
}
