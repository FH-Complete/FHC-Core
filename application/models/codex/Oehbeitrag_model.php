<?php
class Oehbeitrag_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'bis.tbl_oehbeitrag';
		$this->pk = 'oehbeitrag_id';
	}

	/**
	 * Gets oehbeitrag data valid for a certain Studiensemester.
	 * @param string $studiensemester_kurzbz
	 * @return object
	 */
	public function getByStudiensemester($studiensemester_kurzbz)
	{
		$qry = "WITH semstart AS (
					SELECT start FROM public.tbl_studiensemester
					WHERE studiensemester_kurzbz = ?
				)
				SELECT * FROM bis.tbl_oehbeitrag oehb
				JOIN public.tbl_studiensemester semvon ON oehb.von_studiensemester_kurzbz = semvon.studiensemester_kurzbz
				LEFT JOIN public.tbl_studiensemester sembis ON oehb.bis_studiensemester_kurzbz = sembis.studiensemester_kurzbz
				JOIN semstart ON semstart.start::date >= semvon.start::date AND (sembis.studiensemester_kurzbz IS NULL OR semstart.start::date <= sembis.start::date)
				ORDER BY semvon.start
				LIMIT 1";

		return $this->execQuery($qry, array($studiensemester_kurzbz));
	}
}
