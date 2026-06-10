<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

use \REST_Controller as REST_Controller;

/**
 */
class Attachment extends FHC_Controller
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();

		$this->load->model('education/Studierendenantrag_model', 'StudierendenantragModel');

		$this->load->library('DmsLib');
		$this->load->library('AuthLib');
		$this->load->library('PermissionLib');
	}

	// -----------------------------------------------------------------------------------------------------------------
	// Public methods

	/**
	 * @param integer		$dms_id
	 *
	 * @return void
	 */
	public function show($dms_id)
	{
		$result = $this->StudierendenantragModel->loadWhere(['dms_id' => $dms_id]);
		if (!getData($result))
			return show_404();

		if (!$this->permissionlib->isBerechtigt('student/antragfreigabe'))
		{
			$isSamePerson = false;
			$antraege = getData($result);
			$this->load->model('crm/Prestudent_model', 'PrestudentModel');
			foreach ($antraege as $antrag)
			{
				$prestudent = $this->PrestudentModel->load($antrag->prestudent_id);
				if(hasData($prestudent))
				{
					if(current(getData($prestudent))->person_id == getAuthPersonId())
					{
						$isSamePerson = true;
						break;
					}
				}
			}

			if ($isSamePerson == false)
			{
				$this->output->set_status_header(REST_Controller::HTTP_FORBIDDEN); // set the HTTP header as unauthorized

				$this->load->library('EPrintfLib'); // loads the EPrintfLib to format the output

				// Prints the main error message
				$this->eprintflib->printError('You are not allowed to access to this content');
				// Prints the called controller name
				$this->eprintflib->printInfo('Controller name: '.$this->router->class);
				// Prints the called controller method name
				$this->eprintflib->printInfo('Method name: '.$this->router->method);
				// Prints the required permissions needed to access to this method
				$this->eprintflib->printInfo('Required permissions: student/antragfreigabe');

				return show_error('You are not entitled to read this document');
			}
		}

		$result = $this->dmslib->download($dms_id);
		if (isError($result))
			return show_error(getError($result));

		$this->outputFile(getData($result));
	}
}
