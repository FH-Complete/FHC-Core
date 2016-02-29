<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Basic extends CI_Controller {


function __construct()
  {
    parent::__construct();  

    $this->load->library(array('rdf'));
    $this->load->helper(array('form', 'url'));
  
  }
	public function index()
	{
			
			$this->load->library('Rdf');

			$d['title'] = '';
		
			
			$d['content']= $this->load->view('rdf/basic',$d,true);
			$this->load->view('home',$d);
	}
	public function sparql()
	{
			
			$this->load->library('Rdf');

			$d['title'] = '';
		
			
			$d['content']= $this->load->view('rdf/basic_sparql',$d,true);
			$this->load->view('home',$d);
	}
		public function foafinfo()
	{
			
			$this->load->library('Rdf');
			


			$d['title'] = '';
		
			
			$d['content']= $this->load->view('rdf/foafinfo',$d,true);
			$this->load->view('home',$d);
	}
	public function foafmaker()
	{
			
			$d['title'] = '';
		
			
			$d['content']= $this->load->view('rdf/foafmaker',$d,true);
			$this->load->view('home',$d);
	}
	public function converter()
	{
			
			$d['title'] = '';
					
			$d['content']= $this->load->view('rdf/converter',$d,true);
			$this->load->view('home',$d);
	}
}
