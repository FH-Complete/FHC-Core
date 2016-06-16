<?php
if (! defined('BASEPATH')) exit('No direct script access allowed');

class Messages extends FHC_Controller 
{

	public function __construct()
    {
        parent::__construct();
        $this->load->library('messaging');
		//$this->load->model('person/Person_model');
		//$this->load->model('system/Message_model');
    }

	public function index()
	{
		//$messages = $this->Message_model->getMessages();
		$msg = $this->Message_model->load(1);
		if ($msg->error)
			show_error($msg->retval);
		
		$data = array
		(
			'message' => $msg->retval[0]
		);
		$v = $this->load->view('message.php', $data);
	}

	public function view($msg_id)
	{
		$msg = $this->messaging->getMessage($msg_id);
		//var_dump($msg);
		if ($msg->error)
			show_error($msg->retval);
		if (count($msg->retval) != 1)
			show_error('Nachricht nicht vorhanden! ID: '.$msg_id);

		$data = array
		(
			'message' => $msg->retval[0]
		);
		//var_dump($data['message']);
		$v = $this->load->view('system/messageView', $data);
	}

	public function write($vorlage_kurzbz = null)
	{
		$data = array
		(
			'subject' => 'TestSubject',
			'body' => 'TestDevelopmentBodyText'
		);		
		$v = $this->load->view('system/messageWrite', $data);
	}
	
	public function send()
	{
		$body = $this->input->post('body', TRUE);
		$subject = $this->input->post('subject', TRUE);
		if (! $this->messaging->addRecipient(1))
			show_error('Error: AddRecipient');
		$msg = $this->messaging->sendMessage(1,$body ,$subject);
		if ($msg->error)
			show_error($msg->retval);
		$msg_id = $msg->retval;

		redirect('/system/Message/view/'.$msg_id);
	}
}
