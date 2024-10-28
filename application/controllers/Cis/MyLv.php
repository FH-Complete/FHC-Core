<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

/**
 *
 */
class MyLv extends Auth_Controller
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct([
			'index' => ['student/anrechnung_beantragen:r','user:r'], // TODO(chris): permissions?
			'Info' => ['student/anrechnung_beantragen:r','user:r'] // TODO(chris): permissions?
		]);
	}

	// -----------------------------------------------------------------------------------------------------------------
	// Public methods

	/**
	 * @return void
	 */
	public function index()
	{
		$this->load->view('Cis/MyLv');
	}

	public function Info($studien_semester,$lvid)
	{
		$this->load->view('Cis/LvInfo',['lvid'=> $lvid, 'studien_semester' => $studien_semester]);
	}
}
