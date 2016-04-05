<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Message extends FHC_Controller {

	public function __construct()
    {
        parent::__construct();
        //$this->load->library('Messaging');
		$this->load->model('message/Message_model');
		$this->load->model('person/Person_model');
    }

	public function index()
	{
		$person=$this->Person_model->getPersonFromBenutzerUID('pam');
		$msg_id=1;
		$msg = $this->Message_model->getMessage($msg_id, $person[0]->person_id);
		//$this->load->view('welcome_message');
		//$msg = $this->Message_model->send_new_message(1, $msg_id, 'test', 'This is a test!', 1);
	}
}
