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
		// TODO: routing from index based on berechtigung?

		if(defined('CIS4') && CIS4) {
			$this->load->view('CisRouterView/CisRouterView.php', ['route' => 'Abgabetool']);
		} else {
			$this->load->view('Cis/Abgabetool.php', ['uid' => getAuthUID(), 'route' => 'Abgabetool']);
		}
	}

	public function Student($student_uid_prop = '')
	{
		if(defined('CIS4') && CIS4) {
			$this->load->view('CisRouterView/CisRouterView.php', ['route' => 'AbgabetoolStudent']);
		} else {
			$this->load->view('Cis/Abgabetool.php', ['uid' => getAuthUID(), 'route' => 'AbgabetoolStudent', 'student_uid_prop' => $student_uid_prop]);
		}
	}

	public function Mitarbeiter()
	{
		if(defined('CIS4') && CIS4) {
			$this->load->view('CisRouterView/CisRouterView.php', ['route' => 'AbgabetoolMitarbeiter']);
		} else {
			$this->load->view('Cis/Abgabetool.php', ['uid' => getAuthUID(), 'route' => 'AbgabetoolMitarbeiter']);
		}
	}

	public function Assistenz($stg_kz_prop = '')
	{
		if(defined('CIS4') && CIS4) {
			$this->load->view('CisRouterView/CisRouterView.php', ['route' => 'AbgabetoolAssistenz']);
		} else {
			$this->load->view('Cis/Abgabetool.php', ['uid' => getAuthUID(), 'route' => 'AbgabetoolAssistenz', 'stg_kz_prop' => $stg_kz_prop]);
		}
	}
	
	public function Deadlines()
	{
		if(defined('CIS4') && CIS4) {
			$this->load->view('CisRouterView/CisRouterView.php', ['route' => 'DeadlinesOverview']);
		} else {
			$this->load->view('Cis/Abgabetool.php', ['uid' => getAuthUID(), 'route' => 'DeadlinesOverview']);
		}
	}
}
