<?php
class Ablauf_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'testtool.tbl_ablauf';
		$this->pk = 'ablauf_id';
	}

	/**
	 * Returns Weighting of the respective ranking test areas
	 * @param $studiengang_kz Studiengang_kz
	 * @param $semester Integer optional
	 * @return array of weightings per ranking test areas of given studiengang
	 */
	public function getAblaufGebieteAndGewichte($studiengang_kz, $semester = null)
	{
		$parametersArray = array($studiengang_kz);

		$qry =  "
				SELECT
				tbl_ablauf.gebiet_id, tbl_ablauf.gewicht
				FROM
					testtool.tbl_ablauf
				WHERE
					tbl_ablauf.studiengang_kz= ?";

		if($semester)
		{
			$qry .= " AND semester = ?";
			array_push($parametersArray, $semester);

		}
		return $this->execQuery($qry, $parametersArray);
	}
}
