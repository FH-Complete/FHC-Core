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

	public function search($query_words)
	{
		$this->addSelect('CONCAT(UPPER(CONCAT(typ, kurzbz)), \'\', semester, verband, COALESCE(gruppe,\'\')) as gruppe_kurzbz,
												studiengang_kz,
												semester,
												tbl_lehrverband.bezeichnung,
												gid,
												\'true\' as lehrverband');
		$this->addJoin('public.tbl_studiengang', 'studiengang_kz');
		$this->addOrder('verband');
		$this->addOrder('gruppe');
		$this->db->where(array('tbl_lehrverband.aktiv' => true));

		$this->db->group_start();
		foreach ($query_words as $word)
		{
			$this->db->group_start();
			$this->db->where('CONCAT(CONCAT(typ, kurzbz), \'\', semester, verband, COALESCE(gruppe,\'\')) ILIKE', "%" . $word . "%");
			$this->db->or_where('tbl_lehrverband.bezeichnung ILIKE', "%" . $word . "%");
			$this->db->group_end();
		}
		$this->db->group_end();
		return $this->load();
	}
}
