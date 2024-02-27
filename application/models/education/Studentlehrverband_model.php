<?php
class Studentlehrverband_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'public.tbl_studentlehrverband';
		$this->pk = array('studiensemester_kurzbz', 'student_uid');
		$this->hasSequence = false;
	}

	/**
	 * Check if Rolle already exists
	 * @param integer $prestudent_id
	 * @param string $status_kurzbz
	 * @param string $studiensemester_kurzbz
	 * @param integer $ausbildungssemester
	 * @return 1: if Rolle exists, 0: if it doesn't
	 */
	public function checkIfLehrverbandExists($student_uid, $studiensemester_kurzbz)
	{
		$qry = "SELECT
					*
				FROM 
				    public.tbl_studentlehrverband
				WHERE
					student_uid = ? 
				AND
				    studiensemester_kurzbz = ?";

		$result = $this->execQuery($qry, array($student_uid, $studiensemester_kurzbz));

		if (isError($result))
		{
			return error($result);
		}
		elseif (!hasData($result))
		{
			return success("0", "Kein Lehrverband vorhanden!");
		}
		else
		{
			return success("1","Lehrverband vorhanden!");
		}
	}
}
