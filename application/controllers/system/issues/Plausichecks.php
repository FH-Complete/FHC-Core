<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

class Plausichecks extends Auth_Controller
{
	const GENERIC_ISSUE_OCCURED_TEXT = 'Issue aufgetreten';

	public function __construct()
	{
		parent::__construct(
			array(
				'index' => array('system/issues_verwalten:r'),
				'runChecks' => array('system/issues_verwalten:r')
			)
		);

		// Load libraries
		$this->load->library('issues/PlausicheckProducerLib', array('app' => 'core'));
		$this->load->library('issues/PlausicheckDefinitionLib');
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
		$allIssues = array();
		// all fehler kurzbz which are going to be checked
		$fehlerKurzbz = !isEmptyString($fehler_kurzbz) ? array($fehler_kurzbz) : $this->plausicheckdefinitionlib->getFehlerKurzbz();
		$fehlerLibMappings = $this->plausicheckdefinitionlib->getFehlerLibMappings();
		// set Studiengang to null if not passed
		if (isEmptyString($studiengang_kz)) $studiengang_kz = null;

		// get the data returned by Plausicheck
		foreach ($fehlerKurzbz as $fehler_kurzbz)
		{
			// get Text and fehlercode of the Fehler
			$this->FehlerModel->addSelect('fehlercode, fehlertext, fehlertyp_kurzbz');
			$fehlerRes = $this->FehlerModel->loadWhere(array('fehler_kurzbz' => $fehler_kurzbz));

			if (isError($fehlerRes)) $this->terminateWithJsonError(getError($fehlerRes));

			// do not check error if no data
			if (!hasData($fehlerRes)) continue;

			// get the error data
			$fehler = getData($fehlerRes)[0];

			// initialize issue array
			$allIssues[$fehler_kurzbz] = array('fehlercode' => $fehler->fehlercode, 'data' => array());

			// get library name for producing issue
			$libName = $fehlerLibMappings[$fehler_kurzbz];

			// execute the check
			$plausicheckRes = $this->plausicheckproducerlib->producePlausicheckIssue(
				$libName,
				$fehler_kurzbz,
				array(
					'studiensemester_kurzbz' => $studiensemester_kurzbz,
					'studiengang_kz' => $studiengang_kz
				)
			);

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

					// optionally replace fehler parameters in text, output the fehlertext
					if (!isEmptyString($fehler->fehlertext))
					{
						$fehlercode = $fehler->fehlercode;
						$fehlerText = $fehler->fehlertext;
						$fehlerTyp = $fehler->fehlertyp_kurzbz;

						if (!isEmptyArray($fehlertext_params))
						{
							// replace placeholder with params, if present
							if (count($fehlertext_params) != substr_count($fehlerText, '%s'))
								$this->terminateWithJsonError('Wrong number of parameters for Fehlertext, fehler_kurzbz ' . $fehler_kurzbz);

							$fehlerText = vsprintf($fehlerText, $fehlertext_params);
						}

						if (isset($person_id)) $fehlerText .= "; person_id: $person_id";
						if (isset($oe_kurzbz)) $fehlerText .= "; oe_kurzbz: $oe_kurzbz";

						$issueObj = new StdClass();
						$issueObj->fehlertext = $fehlerText;
						$issueObj->type = $fehlerTyp;
						$allIssues[$fehler_kurzbz]['data'][] = $issueObj;
					}
					else // if no issue text found, use generic text
					{
						$fehlerText = self::GENERIC_ISSUE_OCCURED_TEXT;
					}

					// add generic parameters to issue text
					if (isset($person_id)) $fehlerText .= "; person_id: $person_id";
					if (isset($oe_kurzbz)) $fehlerText .= "; oe_kurzbz: $oe_kurzbz";
				}
			}
		}

		$this->outputJsonSuccess($allIssues);
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

		$fehlerKurzbz = $this->plausicheckdefinitionlib->getFehlerKurzbz();

		$db = new DB_Model();

		// get fehlercodes for fehler_kurzbz
		$fehlerRes = $db->execReadOnlyQuery(
			'SELECT
				fehler_kurzbz, fehlercode
			FROM
				system.tbl_fehler
			WHERE
				fehler_kurzbz IN ?',
			array($fehlerKurzbz)
		);

		if (isError($fehlerRes)) show_error(getError($fehlerRes));

		$fehlerKurzbzCodeMappings = array();
		if (hasData($fehlerRes))
		{
			$fehler = getData($fehlerRes);
			foreach ($fehler as $fe)
			{
				$fehlerKurzbzCodeMappings[$fe->fehler_kurzbz] = $fe->fehlercode;
			}
		}

		return array(
			'semester' => hasData($studiensemesterRes) ? getData($studiensemesterRes) : array(),
			'currsemester' => hasData($currSemRes) ? getData($currSemRes) : array(),
			'studiengaenge' => hasData($studiengaengeRes) ? getData($studiengaengeRes) : array(),
			'fehlerKurzbzCodeMappings' => $fehlerKurzbzCodeMappings
		);
	}
}
