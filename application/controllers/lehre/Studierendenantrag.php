<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

use \stdClass as stdClass;

/**
 */
class Studierendenantrag extends FHC_Controller
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
        parent::__construct();

		// Load Libraries
		$this->load->library('AuthLib');
		$this->load->library('AntragLib');

		// Load Models
		$this->load->model('education/Studierendenantrag_model', 'StudierendenantragModel');

		// Load language phrases
		$this->loadPhrases([
			'studierendenantrag'
		]);

        if (strtolower($this->router->method) === 'leitung')
        	$this->_isAllowed([
        		'leitung' => ['student/studierendenantrag:r', 'student/antragfreigabe:r']
        	]);
	}

	// -----------------------------------------------------------------------------------------------------------------
	// Public methods
	public function index()
	{
		$dataAntrag = $this->StudierendenantragModel->loadForPerson(getAuthPersonId());
		if (isError($dataAntrag))
			return show_error(getError($dataAntrag));
		$dataAntrag = (getData($dataAntrag) ? : []);
		$prestudentenArr = array();

		foreach ($dataAntrag as $antrag)
		{
			if (!isset($prestudentenArr[$antrag->prestudent_id]))
			{
				$prestudentenArr[$antrag->prestudent_id] = array(
					'allowedNewTypes' => array(),
					'antraege'=> array(),
					'bezeichnungStg' => $antrag->bezeichnung,
					'bezeichnungOrgform' => $antrag->orgform
				);
				
				$result = $this->antraglib->getPrestudentWiederholungsBerechtigt($antrag->prestudent_id);
				if (getData($result) == 1)
					$prestudentenArr[$antrag->prestudent_id]['allowedNewTypes'][] = 'Wiederholung';

				$result = $this->antraglib->getPrestudentUnterbrechungsBerechtigt($antrag->prestudent_id);
				if (getData($result) == 1)
					$prestudentenArr[$antrag->prestudent_id]['allowedNewTypes'][] = 'Unterbrechung';

				$result = $this->antraglib->getPrestudentAbmeldeBerechtigt($antrag->prestudent_id);
				if (getData($result) == 1)
					$prestudentenArr[$antrag->prestudent_id]['allowedNewTypes'][] = 'Abmeldung';
			}
			if ($antrag->studierendenantrag_id == null)
				continue;
			if ($antrag->typ == Studierendenantrag_model::TYP_ABMELDUNG_STGL && (!$antrag->isapproved))
				continue;

			$prestudentenArr[$antrag->prestudent_id]['antraege'][] = $antrag;
		}

		$this->load->view('lehre/Antrag/Student/List', [
			'antraege' => $prestudentenArr
		]);
	}

	public function leitung()
	{
		$stgL = $this->permissionlib->getSTG_isEntitledFor('student/antragfreigabe') ?: [];

		$stgA = $this->permissionlib->getSTG_isEntitledFor('student/studierendenantrag') ?: [];

		$this->load->view('lehre/Antrag/Leitung/List', [
			'stgA' => $stgA,
			'stgL' => $stgL
		]);
	}

	public function abmeldung($prestudent_id, $studierendenantrag_id = null)
	{
		$this->load->view('lehre/Antrag/Create', [
			'prestudent_id' => $prestudent_id,
			'studierendenantrag_id' => $studierendenantrag_id,
			'antrag_type' => 'Abmeldung'
		]);
	}

	public function abmeldungstgl($prestudent_id, $studierendenantrag_id = null)
	{
		$this->load->view('lehre/Antrag/Create', [
			'prestudent_id' => $prestudent_id,
			'studierendenantrag_id' => $studierendenantrag_id,
			'antrag_type' => 'AbmeldungStgl'
		]);
	}

	public function unterbrechung($prestudent_id, $studierendenantrag_id = null)
	{
		$this->load->view('lehre/Antrag/Create', [
			'prestudent_id' => $prestudent_id,
			'studierendenantrag_id' => $studierendenantrag_id,
			'antrag_type' => 'Unterbrechung'
		]);
	}

	public function wiederholung($prestudent_id, $studierendenantrag_id = null)
	{
		$this->load->view('lehre/Antrag/Create', [
			'prestudent_id' => $prestudent_id,
			'studierendenantrag_id' => $studierendenantrag_id,
			'antrag_type' => 'Wiederholung'
		]);
	}

	/**
	 * Checks if the caller is allowed to access to this content with the given permissions
	 * If it is not allowed will set the HTTP header with code 401
	 * Wrapper for permissionlib->isEntitled
	 */
	private function _isAllowed($requiredPermissions)
	{
		// Loads permission lib
		$this->load->library('PermissionLib');

		// Checks if this user is entitled to access to this content
		if (!$this->permissionlib->isEntitled($requiredPermissions, $this->router->method))
		{
			$this->output->set_status_header(REST_Controller::HTTP_UNAUTHORIZED); // set the HTTP header as unauthorized

			$this->load->library('EPrintfLib'); // loads the EPrintfLib to format the output

			// Prints the main error message
			$this->eprintflib->printError('You are not allowed to access to this content');
			// Prints the called controller name
			$this->eprintflib->printInfo('Controller name: '.$this->router->class);
			// Prints the called controller method name
			$this->eprintflib->printInfo('Method name: '.$this->router->method);
			// Prints the required permissions needed to access to this method
			$this->eprintflib->printInfo('Required permissions: '.$this->_rpsToString($requiredPermissions, $this->router->method));

			exit; // immediately terminate the execution
		}
	}

	/**
	 * Converts an array of permissions to a string that contains them as a comma separated list
	 * Ex: "<permission 1>, <permission 2>, <permission 3>"
	 */
	private function _rpsToString($requiredPermissions, $method)
	{
		$strRequiredPermissions = ''; // string that contains all the required permissions needed to access to this method

		if (isset($requiredPermissions[$method])) // if the called method is present in the permissions array
		{
			// If it is NOT then convert it into an array
			$rpsMethod = $requiredPermissions[$method];
			if (!is_array($rpsMethod))
			{
				$rpsMethod = array($rpsMethod);
			}

			// Copy all the permissions into $strRequiredPermissions separated by a comma
			for ($i = 0; $i < count($rpsMethod); $i++)
			{
				$strRequiredPermissions .= $rpsMethod[$i].', ';
			}

			$strRequiredPermissions = rtrim($strRequiredPermissions, ', ');
		}

		return $strRequiredPermissions;
	}
}
