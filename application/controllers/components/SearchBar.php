<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

/**
 *
 */
class SearchBar extends FHC_Controller
{
	const SEARCHSTR_PARAM = 'searchstr';
	const TYPES_PARAM = 'types';

	/**
	 * 
	 */
	public function __construct()
	{
		parent::__construct();

		//
		$this->load->library('SearchBarLib');
	}

	//------------------------------------------------------------------------------------------------------------------
	// Public methods

	public function index()
	{
		$this->load->view('test');
	}

	/**
	 *
	 */
	public function search()
	{
		//$searchstr = $this->input->post(self::SEARCHSTR_PARAM);
		//$types = $this->input->post(self::TYPES_PARAM);
		
		$json = json_decode($this->input->raw_input_stream, true);
		$searchstr = $json[self::SEARCHSTR_PARAM];
		$types = $json[self::TYPES_PARAM];

		$this->outputJson($this->searchbarlib->search($searchstr, $types));
	}
}

