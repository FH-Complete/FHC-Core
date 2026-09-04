<?php

class Stundengrenze_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'hr.tbl_stundengrenze';
		$this->pk = 'stundengrenze_id';
		$this->hasSequence = true;
	}

	public function getStundengrenze($mitarbeiter_uid, $studiensemester_kurzbz, $oe_kurzbz = null)
	{
		$parametersArray = [];
		$oeRecursive = '';
		$oeWhere = '';
		$oeOrder = '';

		if (isset($oe_kurzbz))
		{
			$oeRecursive = '
				WITH RECURSIVE oes(oe_kurzbz, oe_parent_kurzbz, level) as
				(
					SELECT oe_kurzbz, oe_parent_kurzbz, 0 AS level FROM public.tbl_organisationseinheit
					WHERE oe_kurzbz=?
					UNION ALL
					SELECT o.oe_kurzbz, o.oe_parent_kurzbz, oes.level + 1 AS level FROM public.tbl_organisationseinheit o, oes
					WHERE o.oe_kurzbz=oes.oe_parent_kurzbz
				)';
			$oeJoin = 'LEFT JOIN oes ON gr.oe_kurzbz = oes.oe_kurzbz';
			$oeWhere = 'AND (gr.oe_kurzbz IS NULL OR EXISTS (SELECT 1 FROM oes WHERE oe_kurzbz = gr.oe_kurzbz))';
			$oeOrder = 'oes.level NULLS LAST, ';

			$parametersArray[] = $oe_kurzbz;
		}
		$parametersArray = array_merge($parametersArray, [$mitarbeiter_uid, $studiensemester_kurzbz]);

		$qry = "
			{$oeRecursive}
			SELECT
				stundengrenze
			FROM
				hr.tbl_stundengrenze gr
				{$oeJoin}
			WHERE
				gr.mitarbeiter_uid = ?
				AND gr.studiensemester_kurzbz = ?
				{$oeWhere}
			ORDER BY
				{$oeOrder}stundengrenze_id DESC
			LIMIT 1";

		return $this->execQuery($qry, $parametersArray);
	}
}
