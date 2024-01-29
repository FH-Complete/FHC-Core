<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');
 
/**
 *
 */
class Auth extends FHC_Controller
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();

		// Load Helpers
		$this->load->helper('form');
		$this->load->helper('hlp_authentication');

		// Loads phrases system
		$this->loadPhrases([
			'global'
		]);
	}

	// -----------------------------------------------------------------------------------------------------------------
	// Public methods

	/**
	 * @return void
	 */
	public function login()
	{
		$this->load->library('form_validation');

		$this->form_validation->set_rules('username', 'Username', 'required|trim|callback_validation');
		$this->form_validation->set_rules('password', 'Password', 'required|trim');


		if ($this->form_validation->run())
		{
			redirect($this->authlib->getLandingPage('/CisVue/Dashboard'));
		}
		else
		{
			$this->load->view('Cis/Login');
		}
	}

	/**
	 * @return boolean
	 */
	public function validation()
	{
		$username = $this->input->post('username');
		$password = $this->input->post('password');

		$this->load->library('AuthLib', [false]); // without authentication otherwise loooooop!

		$login = $this->authlib->loginLDAP($username, $password);
		if (isSuccess($login))
			return true;
		$this->form_validation->set_message('validation', 'Incorrect username/password.');
		return false;
	}

	/**
	 * @return void
	 */
	public function logout()
	{
		$this->load->library('AuthLib');
		$this->authlib->logout();
		redirect('/Cis/Auth/login', 'refresh');
	}
}
