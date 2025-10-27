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
			'getStudentProjektarbeitAbgabeFile' => array('basis/abgabe_student:rw', 'basis/abgabe_lektor:rw', 'basis/abgabe_assistenz:rw'),
			'Mitarbeiter' => array('basis/abgabe_lektor:rw', 'basis/abgabe_assistenz:rw'),
			'Assistenz' => array('basis/abgabe_assistenz:rw'),
			'Student' =>  array('basis/abgabe_student:rw', 'basis/abgabe_lektor:rw', 'basis/abgabe_assistenz:rw'),
			'Deadlines' => array('basis/abgabe_lektor:rw', 'basis/abgabe_assistenz:rw')
		]);
	}

	// -----------------------------------------------------------------------------------------------------------------
	// Public methods

	/**
	 * @return void
	 */
	public function index()
	{
		// TODO: do we even need this?

		$viewData = array(
			'uid'=>getAuthUID(),
		);

		if(defined('CIS4') && CIS4) {
			$this->load->view('CisRouterView/CisRouterView.php', ['viewData' => $viewData, 'route' => 'Abgabetool']);
		} else {
			$this->load->view('Cis/Abgabetool.php', ['uid' => getAuthUID(), 'route' => 'Abgabetool']);
		}
	}

	public function Student($student_uid_prop = '')
	{

		$viewData = array(
			'uid'=>getAuthUID(),
		);

		
		
		if(defined('CIS4') && CIS4) {
			$this->load->view('CisRouterView/CisRouterView.php', ['viewData' => $viewData, 'route' => 'AbgabetoolStudent']);
		} else {
			$this->load->view('Cis/Abgabetool.php', ['uid' => getAuthUID(), 'route' => 'AbgabetoolStudent', 'student_uid_prop' => $student_uid_prop]);
		}
	}

	public function Mitarbeiter()
	{

		$viewData = array(
			'uid'=>getAuthUID(),
		);

		if(defined('CIS4') && CIS4) {
			$this->load->view('CisRouterView/CisRouterView.php', ['viewData' => $viewData, 'route' => 'AbgabetoolMitarbeiter']);
		} else {
			$this->load->view('Cis/Abgabetool.php', ['uid' => getAuthUID(), 'route' => 'AbgabetoolMitarbeiter']);
		}
	}

	public function Assistenz()
	{

		$viewData = array(
			'uid'=>getAuthUID(),
		);

		if(defined('CIS4') && CIS4) {
			$this->load->view('CisRouterView/CisRouterView.php', ['viewData' => $viewData, 'route' => 'AbgabetoolAssistenz']);
		} else {
			$this->load->view('Cis/Abgabetool.php', ['uid' => getAuthUID(), 'route' => 'AbgabetoolAssistenz']);
		}
	}
	
	public function Deadlines()
	{

		$viewData = array(
			'uid'=>getAuthUID(),
		);

		if(defined('CIS4') && CIS4) {
			$this->load->view('CisRouterView/CisRouterView.php', ['viewData' => $viewData, 'route' => 'DeadlinesOverview']);
		} else {
			$this->load->view('Cis/Abgabetool.php', ['uid' => getAuthUID(), 'route' => 'DeadlinesOverview']);
		}
	}


	public function getStudentProjektarbeitAbgabeFile() 
	{
		$this->_ci =& get_instance();
		$this->_ci->load->helper('download');
		
		$paabgabe_id = $this->_ci->input->get('paabgabe_id');
		$student_uid = $this->_ci->input->get('student_uid');

		if (!isset($paabgabe_id) || isEmptyString($paabgabe_id) || !isset($student_uid) || isEmptyString($student_uid))
			$this->terminateWithJsonError($this->p->t('global', 'wrongParameters'), 'general');
		
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
				$this->terminateWithJsonError('File not found');
			}
		} else {
			$this->terminateWithJsonError('Keine Zuordnung!');
		}
	}
}
