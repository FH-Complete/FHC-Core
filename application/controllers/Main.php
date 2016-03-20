<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');
 
class Main extends CI_Controller
{
	/**
	 * Load Database und the url-helper
	 **/
	private function __construct()
    {
        parent::__construct();
		// Standard Libraries of codeigniter are required
        $this->load->database();
        $this->load->helper('url');
 		// Test the grocery CRUD
        $this->load->library('grocery_CRUD');
    }

    /**
	 * Show the Welcome Page by default
	 * @return void
	 **/
	public function index()
	{
		echo "<h1>Welcome to the world of Codeigniter</h1>";
		//Just an example to ensure that we get into the function die();
	}
 
    /**
	 * Test grocery CRUD for tbl_person
	 * @return void
	 **/
	public function person()
    {
        $this->grocery_crud->set_table('tbl_pperson');
        $output = $this->grocery_crud->render();
 		
		echo "<pre>";
        print_r($output);
        echo "</pre>";
        //die();

        $this->__exampleOutput($output);
    }

    /**
	 * example Output
	 * @param string $output The HTML-Output from grocery.
	 * @return void
	 **/
	private function __exampleOutput($output = null)
    {
        $this->load->view('our_template.php', $output);
    }
}
