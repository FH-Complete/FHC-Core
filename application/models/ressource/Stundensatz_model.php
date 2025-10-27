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

	public function getDefaultStundensatz($mitarbeiter_uid, $beginn, $ende = null, $typ = null)
	{
		$stundensatz_result = $this->getStundensatzByDatum($mitarbeiter_uid, $beginn, $ende, $typ);
		$default_stundensatz = hasData($stundensatz_result) ? getData($stundensatz_result)[0]->stundensatz : null;
		if (defined('FAS_LV_LEKTORINNENZUTEILUNG_FIXANGESTELLT_STUNDENSATZ') && !FAS_LV_LEKTORINNENZUTEILUNG_FIXANGESTELLT_STUNDENSATZ)
		{
			$this->load->model('vertragsbestandteil/Dienstverhaeltnis_model','DienstverhaeltnisModel');
			$echterdv_result = $this->DienstverhaeltnisModel->existsDienstverhaeltnis($mitarbeiter_uid, $beginn, $ende, 'echterdv');
			if (hasData($echterdv_result))
			{
				$default_stundensatz = null;
			}
		}
		return $default_stundensatz;
	}
}