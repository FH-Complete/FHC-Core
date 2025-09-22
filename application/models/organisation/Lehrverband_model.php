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
	 * Gets the maximum possible semester for one or more Studiengaenge.
	 * If there are more than one Studiengang each maximum is calculated and
	 * the smallest result is returned.
	 *
	 * @param array					$studiengang_kzs
	 *
	 * @return stdClass
	 */
	public function getMaxSemester($studiengang_kzs)
	{
		$sqls = [];
		foreach ($studiengang_kzs as $studiengang_kz) {
			$this->addSelect('MAX(semester) AS maxsem');
			$this->db->where('studiengang_kz', $studiengang_kz);
			$sqls[] = $this->db->get_compiled_select($this->dbTable);
		}
		
		$this->addSelect('MIN(a.maxsem) AS maxsem');
		
		$dbTable = $this->dbTable;
		$this->dbTable = '(' . implode(' UNION ', $sqls) . ') AS a';
		
		$result = $this->load();
		
		$this->dbTable = $dbTable;

		return $result;
	}
}
