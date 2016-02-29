<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
 
class Main extends CI_Controller {
 
    function __construct()
    {
        parent::__construct();
 
        /* Standard Libraries of codeigniter are required */
        $this->load->database();
        $this->load->helper('url');
        /* ------------------ */ 
 
        $this->load->library('grocery_CRUD');
 
    }
 
    public function index()
    {
        echo "<h1>Welcome to the world of Codeigniter</h1>";//Just an example to ensure that we get into the function
                die();
    }
 
    public function prestudent()
    {
        $this->grocery_crud->set_table('tbl_prestudent');
        $output = $this->grocery_crud->render();
 		
		echo "<pre>";
        print_r($output);
        echo "</pre>";
        die();

        $this->_example_output($output);        
    }
 
    function _example_output($output = null)
 
    {
        $this->load->view('our_template.php',$output);    
    }
}
 
/* End of file main.php */
/* Location: ./application/controllers/main.php */
