<?php
class Stundenplandev_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'lehre.tbl_stundenplandev';
		$this->pk = 'stundenplandev_id';
	}

	public function getMissingDirectGroups($studiensemester_kurzbz = null)
	{
		$qry = "
		SELECT
			distinct lehreinheit_id, datum, stunde, mitarbeiter_uid, ort_kurzbz
		FROM
			lehre.tbl_stundenplandev stpl
		WHERE
			lehreinheit_id IN(
				SELECT
					lehreinheit_id
				FROM
					lehre.tbl_lehreinheit
					JOIN lehre.tbl_lehreinheitgruppe USING(lehreinheit_id)
					JOIN public.tbl_gruppe USING(gruppe_kurzbz)
				WHERE
					tbl_gruppe.direktinskription = true
					";

		$parametersArray = array();

		if (!is_null($studiensemester_kurzbz))
		{
			$parametersArray[] = $studiensemester_kurzbz;
			$qry .= ' AND tbl_lehreinheit.studiensemester_kurzbz = ?';
		}
		$qry .= ")
			AND NOT EXISTS(
				SELECT
					1
				FROM
					lehre.tbl_stundenplandev
				WHERE
					datum=stpl.datum
					AND stunde=stpl.stunde
					AND lehreinheit_id=stpl.lehreinheit_id
					AND gruppe_kurzbz=(SELECT
							gruppe_kurzbz
						FROM
							lehre.tbl_lehreinheitgruppe
							JOIN public.tbl_gruppe USING(gruppe_kurzbz)
						WHERE
							lehreinheit_id=stpl.lehreinheit_id
							AND tbl_gruppe.direktinskription = true
						)
				)";

		return $this->execQuery($qry, $parametersArray);
	}
}
