<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Basic extends CI_Controller
{
	/**
	 * Loading the rdf-Library and Form- and Url-Helper
	 * @return void
	 */
	private function __construct()
	{
		parent::__construct();

		$this->load->library(array('rdf'));
		$this->load->helper(array('form', 'url'));
	}

	/**
	 * Load Basic View
	 * @return void
	 */
	public function index()
	{
			$this->load->library('Rdf');
			$d['title'] = '';
			$d['content'] = $this->load->view('rdf/basic', $d, true);
			$this->load->view('home', $d);
	}
	
	/**
	 * Load Sparql-View
	 * @return void
	 */
	public function sparql()
	{
			$this->load->library('Rdf');
			$d['title'] = '';
			$d['content'] = $this->load->view('rdf/basic_sparql', $d, true);
			$this->load->view('home', $d);
	}
	
	/**
	 * Load foaf-View
	 * @return void
	 */
	public function foafinfo()
	{
			$this->load->library('Rdf');
			$d['title'] = '';
			$d['content'] = $this->load->view('rdf/foafinfo', $d, true);
			$this->load->view('home', $d);
	}

	/**
	 * Load foafmaker View
	 * @return void
	 */
	public function foafmaker()
	{
			$d['title'] = '';
			$d['content'] = $this->load->view('rdf/foafmaker', $d, true);
			$this->load->view('home', $d);
	}
	
	/**
	 * Load converter View
	 * @return void
	 */
	public function converter()
	{
			$d['title'] = '';
			$d['content'] = $this->load->view('rdf/converter', $d, true);
			$this->load->view('home', $d);
	}
}
