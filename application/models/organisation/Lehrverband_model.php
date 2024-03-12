<?php
class Lehrverband_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'public.tbl_lehrverband';
		$this->pk = array('gruppe', 'verband', 'semester', 'studiengang_kz');
	}

	/**
	 * Check if Lehrverband already exists
	 * @param string $student_id
	 * @param string $studiensemester_kurzbz
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
