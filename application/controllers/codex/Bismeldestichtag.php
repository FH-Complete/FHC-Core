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
				'getStudiensemester' => 'admin:r',
				'getBismeldestichtage' => 'admin:r',
				'addBismeldestichtag' => 'admin:rw',
				'deleteBismeldestichtag' => 'admin:rw'
			)
		);

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

	public function getBismeldestichtage()
	{
		$this->BismeldestichtagModel->addSelect(
			'meldestichtag_id, meldestichtag,
			tbl_bismeldestichtag.studiensemester_kurzbz, sem.start AS semester_start,
			tbl_bismeldestichtag.insertamum, tbl_bismeldestichtag.insertvon, tbl_bismeldestichtag.updateamum, tbl_bismeldestichtag.updatevon'
		);
		$this->BismeldestichtagModel->addJoin('public.tbl_studiensemester sem', 'studiensemester_kurzbz', 'LEFT');
		$this->BismeldestichtagModel->addOrder('semester_start');
		$this->BismeldestichtagModel->addOrder('meldestichtag', 'DESC');
		$this->BismeldestichtagModel->addOrder('meldestichtag_id', 'DESC');
		$this->outputJson($this->BismeldestichtagModel->load());
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
				array(
					'meldestichtag' => $request->meldestichtag,
					'studiensemester_kurzbz' => $request->studiensemester_kurzbz,
					'insertvon' => getAuthUID()
				)
			));
		}
	}

	public function deleteBismeldestichtag()
	{
		// get request data
		$request = $this->getPostJSON();

		// check request data
		if (!property_exists($request, 'meldestichtag_id'))
			$this->terminateWithJsonError('Error occured: Meldestichtag Id missing');

		$meldestichtag_id = $request->meldestichtag_id;

		// deletetion
		$this->outputJson($this->BismeldestichtagModel->delete($meldestichtag_id));
	}
}
