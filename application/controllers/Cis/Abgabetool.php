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
}
