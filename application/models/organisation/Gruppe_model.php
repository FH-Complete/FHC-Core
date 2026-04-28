<?php
class Gruppe_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'public.tbl_gruppe';
		$this->pk = 'gruppe_kurzbz';
	}

	public function search($query_words)
	{
		$this->addSelect('gruppe_kurzbz,
											studiengang_kz,
											semester,
											bezeichnung,
											gid,
											\'false\' as lehrverband');
		$this->db->where(array('sichtbar' => true, 'aktiv' => true, 'lehre' => true, 'direktinskription' => false, 'semester IS NOT NULL' => null));
		$this->db->group_start();
		foreach ($query_words as $word)
		{
			$this->db->group_start();
			$this->db->where('gruppe_kurzbz ILIKE', "%" . $word . "%");
			$this->db->or_where('bezeichnung ILIKE', "%" . $word . "%");
			$this->db->group_end();
		}
		$this->db->group_end();

		return $this->load();
	}
}
