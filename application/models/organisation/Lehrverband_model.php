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
	 * @param Integer $studiengang_kz
	 * @param Integer $semester
	 * @param char $verband
	 * @param char $gruppe
	 * @return 1: if Lehrverband exists, 0: if it doesn't
	 */
	public function checkIfLehrverbandExists($studiengang_kz, $semester, $verband, $gruppe)
	{
		$qry = "SELECT
					*
				FROM 
				    public.tbl_lehrverband
				WHERE
					studiengang_kz = ? 
				AND
				    semester = ?
				AND
				    verband = ?
				AND
				    gruppe = ?
				    
				    ";

		$result = $this->execQuery($qry, array($studiengang_kz, $semester, $verband, $gruppe));


		if(isError($result))
		{
			return error($result);
		}
		elseif(!hasData($result))
		{
			return success("0", "Kein Lehrverband vorhanden!");
		}
		else
		{
			return success("1","Lehrverband vorhanden!");
		}
	}
}
