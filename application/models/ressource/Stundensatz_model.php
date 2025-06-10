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

	public function getStundensatzForMitarbeiter($person_id, $studiensemester_kurzbz)
	{
		$this->load->config('stv');

		$useFixangestelltStundensatz = $this->config->item('tabs')['projektarbeit']['lvLektroinnenzuteilungFixangestelltStundensatz'];
		$defaultStundensatz = $this->config->item('tabs')['projektarbeit']['defaultProjektbetreuerStundensatz'];

		$stundensatz = '';

		if(isset($person_id) && isset($studiensemester_kurzbz))
		{
			$this->load->model('organisation/Studiensemester_model', 'StudiensemesterModel');

			$this->StudiensemesterModel->addSelect('start, ende');
			$result = $this->StudiensemesterModel->load($studiensemester_kurzbz);

			if (hasData($result))
			{
				$studiensemester = getData($result)[0];

				if (isset($useFixangestelltStundensatz) && !$useFixangestelltStundensatz)
				{
					// load Mitarbeiter
					$params = [$person_id];
					$qry = "
						SELECT
							mitarbeiter_uid, fixangestellt
						FROM
							public.tbl_mitarbeiter
							JOIN public.tbl_benutzer ON(tbl_benutzer.uid=tbl_mitarbeiter.mitarbeiter_uid)
						WHERE
							person_id=?
						ORDER BY
							tbl_mitarbeiter.insertamum DESC NULLS LAST
						LIMIT 1";

					$result = $this->execQuery($qry, $params);

					if (hasData($result))
					{
						foreach (getData($result) as $ma)
						{
							if (!$ma->fixangestellt)
							{
								$stundensatzRes = $this->getStundensatzByDatum(
									$ma->mitarbeiter_uid, $studiensemester->start, $studiensemester->ende, 'lehre'
								);

								if (hasData($stundensatzRes))
									$stundensatz = getData($stundensatzRes)[0]->stundensatz;
								else
									$stundensatz = '0.00';
							}
						}
					}
					else
					{
						$stundensatz = '0.00';
					}

				}
				else
				{
					$params = [$person_id, $studiensemester->ende, $studiensemester->start];
					$qry = "SELECT ss.stundensatz
							FROM hr.tbl_stundensatz ss
								JOIN public.tbl_mitarbeiter ON ss.uid = tbl_mitarbeiter.mitarbeiter_uid
								JOIN public.tbl_benutzer ON(tbl_benutzer.uid=tbl_mitarbeiter.mitarbeiter_uid)
							WHERE person_id=?
								AND stundensatztyp = 'lehre'
								AND gueltig_von <= ?
								AND (gueltig_bis >= ? OR gueltig_bis IS NULL)
							ORDER BY gueltig_bis DESC NULLS FIRST, gueltig_von DESC NULLS LAST LIMIT 1";

					$result = $this->execQuery($qry, $params);

					if (hasData($result))
					{
						$stundensatz = getData($result)[0]->stundensatz;
					}
					else
					{
						$stundensatz = $defaultStundensatz;
					}
				}
			}
		}

		return $stundensatz;
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
==== BASE ====
