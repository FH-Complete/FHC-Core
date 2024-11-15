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
	 * Liefert die Note zu Punkten einer Lehrveranstaltung
	 *
	 * @param number					$points
	 * @param integer					$lehrveranstaltung_id
	 * @param string					$studiensemester_kurzbz
	 *
	 * @return stdClass
	 */
	public function getNote($points, $lehrveranstaltung_id, $studiensemester_kurzbz)
	{
		$this->load->model('education/Notenschluesselzuordnung_model', 'NotenschluesselzuordnungModel');
		$notenschluessel_kurzbz = $this->NotenschluesselzuordnungModel->getKurzbzForLv($lehrveranstaltung_id, $studiensemester_kurzbz);

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
			return error("Es wurde kein passender eintrag gefunden"); // TODO(chris): phrase
		return success(current(getData($result))->note);
	}
}
