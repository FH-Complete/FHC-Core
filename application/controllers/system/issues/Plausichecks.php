<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

class Plausichecks extends Auth_Controller
{
	public function __construct()
	{
		parent::__construct(
			array(
				'index' => array('system/issues_verwalten:r'),
				'runChecks' => array('system/issues_verwalten:r')
			)
		);

		// Load libraries
		$this->load->library('issues/PlausicheckProducerLib');
		$this->load->library('WidgetLib');

		// Load models
		$this->load->model('system/Fehler_model', 'FehlerModel');
		$this->load->model('organisation/Studiensemester_model', 'StudiensemesterModel');
		$this->load->model('organisation/Studiengang_model', 'StudiengangModel');
	}

	/*
	 * Get data for filtering the plausichecks and load the view.
	 */
	public function index()
	{
		$filterData = $this->_getFilterData();
		$this->load->view('system/issues/plausichecks', $filterData);
	}

	/**
	 * Initiate plausichecks run.
	 */
	public function runChecks()
	{
		$studiensemester_kurzbz = $this->input->get('studiensemester_kurzbz');
		$studiengang_kz = $this->input->get('studiengang_kz');
		$fehler_kurzbz = $this->input->get('fehler_kurzbz');

		// issues array for passing issue texts
		$issueTexts = array();
		// all fehler kurzbz which are going to be checked
		$fehlerKurzbz = !isEmptyString($fehler_kurzbz) ? array($fehler_kurzbz) : $this->plausicheckproducerlib->getFehlerKurzbz();
		// set Studiengang to null if not passed
		if (isEmptyString($studiengang_kz)) $studiengang_kz = null;

		// get the data returned by Plausicheck
		foreach ($fehlerKurzbz as $fehler_kurzbz)
		{
			// execute the check
			$issueTexts[$fehler_kurzbz] = array();
			$plausicheckRes = $this->plausicheckproducerlib->producePlausicheckIssue($fehler_kurzbz, $studiensemester_kurzbz, $studiengang_kz);

			if (isError($plausicheckRes)) $this->terminateWithJsonError(getError($plausicheckRes));

			if (hasData($plausicheckRes))
			{
				$plausicheckData = getData($plausicheckRes);

				foreach ($plausicheckData as $plausiData)
				{
					// get the data needed for issue production
					$person_id = isset($plausiData['person_id']) ? $plausiData['person_id'] : null;
					$oe_kurzbz = isset($plausiData['oe_kurzbz']) ? $plausiData['oe_kurzbz'] : null;
					$fehlertext_params = isset($plausiData['fehlertext_params']) ? $plausiData['fehlertext_params'] : null;
					$resolution_params = isset($plausiData['resolution_params']) ? $plausiData['resolution_params'] : null;

					// get Text of the Fehler
					$this->FehlerModel->addSelect('fehlertext');
					$fehlerRes = $this->FehlerModel->loadWhere(array('fehler_kurzbz' => $fehler_kurzbz));

					if (isError($fehlerRes)) $this->outputJsonError(getError($fehlerRes));

					// optionally replace fehler parameters in text, output the fehlertext
					if (hasData($fehlerRes))
					{
						$fehlerText = getData($fehlerRes)[0]->fehlertext;

						if (!isEmptyArray($fehlertext_params))
						{
							if (count($fehlertext_params) != substr_count($fehlerText, '%s'))
								$this->terminateWithJsonError('Wrong number of parameters for Fehlertext, fehler_kurzbz ' . $fehler_kurzbz);

							$fehlerText = vsprintf($fehlerText, $fehlertext_params);
						}

						if (isset($person_id)) $fehlerText .= "; person_id: $person_id";
						if (isset($oe_kurzbz)) $fehlerText .= "; oe_kurzbz: $oe_kurzbz";
						$issueTexts[$fehler_kurzbz][] = $fehlerText;
					}
				}
			}
		}

		$this->outputJsonSuccess($issueTexts);
	}

	/**
	 * Get the data needed for filtering for limiting checks.
	 */
	private function _getFilterData()
	{
		$this->StudiensemesterModel->addOrder('start', 'DESC');
		$studiensemesterRes = $this->StudiensemesterModel->load();

		if (isError($studiensemesterRes)) show_error(getError($studiensemesterRes));

		$currSemRes = $this->StudiensemesterModel->getAkt();

		if (isError($currSemRes)) show_error(getError($currSemRes));

		$this->StudiengangModel->addSelect('studiengang_kz, tbl_studiengang.bezeichnung, tbl_studiengang.typ,
			tbl_studiengangstyp.bezeichnung AS typbezeichnung, UPPER(tbl_studiengang.typ::varchar(1) || tbl_studiengang.kurzbz) as kuerzel');
		$this->StudiengangModel->addJoin('public.tbl_studiengangstyp', 'typ');
		$this->StudiengangModel->addOrder('kuerzel, tbl_studiengang.bezeichnung, studiengang_kz');
		$studiengaengeRes = $this->StudiengangModel->loadWhere(array('aktiv' => true));

		if (isError($studiengaengeRes)) show_error(getError($studiengaengeRes));

		$fehlerKurzbz = $this->plausicheckproducerlib->getFehlerKurzbz();

		return array(
			'semester' => hasData($studiensemesterRes) ? getData($studiensemesterRes) : array(),
			'currsemester' => hasData($currSemRes) ? getData($currSemRes) : array(),
			'studiengaenge' => hasData($studiengaengeRes) ? getData($studiengaengeRes) : array(),
			'fehler' => $fehlerKurzbz
		);
	}
}
