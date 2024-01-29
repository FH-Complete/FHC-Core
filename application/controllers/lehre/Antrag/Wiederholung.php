<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

use \DateTime as DateTime;
use \Studierendenantrag_model as Studierendenantrag_model;
use \REST_Controller as REST_Controller;

/**
 */
class Wiederholung extends Auth_Controller
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
        parent::__construct([
			'assistenz'=> 'student/studierendenantrag:w'
		]);

		$this->load->library('AntragLib');

		// Load language phrases
		$this->loadPhrases([
			'studierendenantrag'
		]);
	}

	// -----------------------------------------------------------------------------------------------------------------
	// Public methods
	public function assistenz($antrag_id)
	{

		$result = $this->antraglib->getDetailsForAntrag($antrag_id);

		if (isError($result))
			return show_error(getError($result));

		if (!hasData($result))
			return show_404();

		$this->load->view('lehre/Antrag/Wiederholung/Student', [
			'antrag_id' => $antrag_id,
			'antrag' => getData($result)
		]);
	}
}
