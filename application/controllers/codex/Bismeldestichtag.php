<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Overview on Bismeldestichtage
 */
class Bismeldestichtag extends Auth_Controller
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct(
			array(
				'index' => 'admin:r',
				'addBismeldestichtag' => 'admin:rw',
				'getStudiensemester' => 'admin:r'
			)
		);

		// Loads WidgetLib
		$this->load->library('WidgetLib');

		// Load models
		$this->load->model('codex/Bismeldestichtag_model', 'BismeldestichtagModel');
		$this->load->model('organisation/Studiensemester_model', 'StudiensemesterModel');

		// Loads phrases system
		$this->loadPhrases(
			array(
				'bismeldestichtag'
			)
		);
	}

	// -----------------------------------------------------------------------------------------------------------------
	// Public methods

	/**
	 * Everything has a beginning
	 */
	public function index()
	{
		$this->load->view('codex/bismeldestichtag.php');
	}

	public function getStudiensemester()
	{
		// load semester list
		$semList = array();
		$this->StudiensemesterModel->addSelect('studiensemester_kurzbz');
		$this->StudiensemesterModel->addOrder('start', 'DESC');
		$semRes = $this->StudiensemesterModel->load();

		if (hasData($semRes))
		{
			$semList = getData($semRes);
		}

		// load current semester
		$currSem = null;
		$semRes = $this->StudiensemesterModel->getAkt();

		if (hasData($semRes))
		{
			$currSem = getData($semRes)[0]->studiensemester_kurzbz;
		}

		// output data
		$this->outputJsonSuccess(
			array('semList' => $semList, 'currSem' => $currSem)
		);
	}

	public function addBismeldestichtag()
	{
		// get request data
		$request = $this->getPostJSON();

		// check request data
		if (!property_exists($request, 'meldestichtag') || isEmptyString($request->meldestichtag))
			$this->terminateWithJsonError('Error occured: Meldestichtag missing');
		if (!property_exists($request, 'studiensemester_kurzbz') || isEmptyString($request->studiensemester_kurzbz))
			$this->terminateWithJsonError('Error occured: Studiensemester missing');

		$meldestichtag = $request->meldestichtag;
		$studiensemester_kurzbz = $request->studiensemester_kurzbz;

		// check if Bismeldestichtag already exists
		$this->BismeldestichtagModel->addSelect('1');
		$bismeldestichtagRes = $this->BismeldestichtagModel->loadWhere(
			array('meldestichtag' => $meldestichtag, 'studiensemester_kurzbz' => $studiensemester_kurzbz)
		);

		// return success if already exists
		if (hasData($bismeldestichtagRes))
			$this->outputJsonSuccess('Bismeldestichtag already exists');
		else
		{
			// insert new if Stichtag does not exist
			$this->outputJson($this->BismeldestichtagModel->insert(
				array('meldestichtag' => $request->meldestichtag, 'studiensemester_kurzbz' => $request->studiensemester_kurzbz)
			));
		}
	}
}
