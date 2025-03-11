<?php
class Vorlagestudiengang_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'public.tbl_vorlagestudiengang';
		$this->pk = 'vorlagestudiengang_id';
	}

	/**
	 * Gets the Current Vorlagestudiengang
	 *
	 * @param string				$vorlage_kurzbz
	 * @param string				$oe_kurzbz Or studiengang_kz
	 * @param integer				$version (optional)
	 * @param boolean|null			$active (optional)
	 *
	 * @return stdClass
	 */
	public function getCurrent($vorlage_kurzbz, $oe_kurzbz, $version = null, $active = true)
	{
		if (is_numeric($oe_kurzbz)) {
			$initselect = "SELECT oe_kurzbz, 1 AS l FROM public.tbl_studiengang WHERE studiengang_kz = " . $this->escape($oe_kurzbz);
		} else {
			$initselect = "SELECT oe_kurzbz, 1 AS l FROM public.tbl_organisationseinheit WHERE oe_kurzbz = " . $this->escape($oe_kurzbz);
		}

		$this->addJoin("(
			WITH RECURSIVE tmp (oe_kurzbz, l) AS (
				" . $initselect . "
				UNION ALL
				SELECT o.oe_parent_kurzbz AS oe_kurzbz, l+1 AS l
					FROM tmp
					JOIN public.tbl_organisationseinheit o USING (oe_kurzbz)
					WHERE o.oe_parent_kurzbz IS NOT NULL
			) SELECT * FROM tmp
		) oe", "oe_kurzbz");

		if (!is_null($version))
			$this->db->where('version', $version);
		if ($active)
			$this->db->where('aktiv', true);

		$this->addOrder('l', 'ASC');
		$this->addOrder('version', 'DESC');
		$this->addLimit(1);

		$result = $this->loadWhere([
			'vorlage_kurzbz' => $vorlage_kurzbz
		]);
		
		return $result;
	}
}
