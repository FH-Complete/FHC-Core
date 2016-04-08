<?php
defined('BASEPATH') || exit('No direct script access allowed');

class Vilesci extends CI_Controller
{

	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see http://codeigniter.com/user_guide/general/urls.html
	 * @return void
	 */
	public function index()
	{
		// ToDo: check if update is needed
		if (false && $this->dbupdate())
			echo 'System-DB needs update!';
		else
		{
			$this->load->view('templates/header');
			$this->load->view('vilesci_frameset');
			$this->load->view('templates/footer');
		}
	}

	/**
	 *
	 * @return bool
	 */
	private function __dbupdate()
	{
		// Check for update (codeigniter migration)
		$this->load->library('migration');
		if ($this->migration->current() === false)
			show_error($this->migration->error_string());
        if ($this->migration->current() != $this->migration->latest())
			return true;
		else
			return false;
	}
}
