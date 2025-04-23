<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

/**
 *
 */
class Abgabetool extends Auth_Controller
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct([
			'index' => self::PERM_LOGGED,
			'getStudentProjektarbeitAbgabeFile' => self::PERM_LOGGED,
			'Mitarbeiter' => self::PERM_LOGGED,
			'Student' => self::PERM_LOGGED
		]);
	}

	// -----------------------------------------------------------------------------------------------------------------
	// Public methods

	/**
	 * @return void
	 */
	public function index()
	{

		$viewData = array(
			'uid'=>getAuthUID(),
		);

		$this->load->view('CisRouterView/CisRouterView.php', ['viewData' => $viewData, 'route' => 'Abgabetool']);
	}

	public function Student()
	{

		$viewData = array(
			'uid'=>getAuthUID(),
		);

		$this->load->view('CisRouterView/CisRouterView.php', ['viewData' => $viewData, 'route' => 'Abgabetool']);
	}

	public function Mitarbeiter()
	{

		$viewData = array(
			'uid'=>getAuthUID(),
		);

		$this->load->view('CisRouterView/CisRouterView.php', ['viewData' => $viewData, 'route' => 'Abgabetool']);
	}


	public function getStudentProjektarbeitAbgabeFile() 
	{
		$this->_ci =& get_instance();
		$this->_ci->load->helper('download');
		
		$paabgabe_id = $this->_ci->input->get('paabgabe_id');
		$student_uid = $this->_ci->input->get('student_uid');

		if (!isset($paabgabe_id) || isEmptyString($paabgabe_id) || !isset($student_uid) || isEmptyString($student_uid))
			$this->terminateWithError($this->p->t('global', 'wrongParameters'), 'general');
		
		$this->_ci->load->model('education/Projektarbeit_model', 'ProjektarbeitModel');

		$isZugeteilterBetreuer = count($this->_ci->ProjektarbeitModel->checkZuordnung($student_uid, getAuthUID())->retval) > 0;
		
		if(getAuthUID() == $student_uid || $isZugeteilterBetreuer) {
			$file_path = PAABGABE_PATH.$paabgabe_id.'_'.$student_uid.'.pdf';
			if(file_exists($file_path)) {
				
				header('Content-Description: File Transfer');
				header('Content-Type: application/octet-stream');
				header('Expires: 0');
				header('Cache-Control: must-revalidate');
				header('Pragma: public');
				header('Content-Disposition: attachment; filename="'.basename($file_path).'"');
				header('Content-Length: ' . filesize($file_path));

				flush(); // send headers first just in case
				readfile($file_path); // read file content to output buffer
				
			} else {
				$this->terminateWithError('File not found');
			}
		} else {
			$this->terminateWithError('Keine Zuordnung!');
		}
	}
}
