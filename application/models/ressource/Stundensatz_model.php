<?php

class Stundensatz_model extends DB_Model
{
	
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'hr.tbl_stundensatz';
		$this->pk = 'stundensatz_id';
		$this->hasSequence = true;
	}

	public function getStundensatzByDatum($uid, $beginn, $ende = null, $typ = null)
	{
		$qry = "SELECT
					*
				FROM
					hr.tbl_stundensatz
				WHERE
					uid = ?
					AND (gueltig_bis >= ? OR gueltig_bis is null)";

		$params = array($uid, $beginn);

		if (!is_null($ende))
		{
			$qry .=  " AND (gueltig_von <= ?)";
			$params[] = $ende;
		}

		if (!is_null($typ))
		{
			$qry .=  " AND stundensatztyp = ?";
			$params[] = $typ;
		}

		$qry .= " ORDER BY gueltig_bis DESC NULLS FIRST, gueltig_von DESC NULLS LAST LIMIT 1;";

		return $this->execQuery($qry, $params);
	}
}