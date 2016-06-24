<?php
if (! defined('BASEPATH')) exit('No direct script access allowed');

class Messages extends VileSci_Controller 
{

	public function __construct()
    {
        parent::__construct();
        $this->load->library('MessageLib');
		$this->load->model('person/Person_model', 'PersonModel');
    }

	public function index($person_id = null)
	{
		$data = array('person_id' => $person_id);
		$this->load->view('system/messages.php', $data);
	}

	public function inbox($person_id = null)
	{
		if (empty($person_id))
			$person_id = $this->input->post('person_id', TRUE);
		if (empty($person_id))
			$msg = $this->messagelib->getMessagesByUID(getAuthUID());
		else
			$msg = $this->messagelib->getMessagesByPerson($person_id);
		if ($msg->error)
			show_error($msg->retval);
		
		$data = array
		(
			'uid' => getAuthUID(),
			'messages' => $msg->retval
		);
		if (!empty($person_id))
		{
			$person = $this->PersonModel->load($person_id);
			$data['person'] = $person->retval[0];
		}
		// var_dump ($data);
		$this->load->view('system/messagesInbox.php', $data);
	}

	public function outbox($person_id = null)
	{
		if (empty($person_id))
			$person_id = $this->input->post('person_id', TRUE);
		if (empty($person_id))
			$msg = $this->messagelib->getMessagesByUID(getAuthUID());
		else
			$msg = $this->messagelib->getMessagesByPerson($person_id);
		if ($msg->error)
			show_error($msg->retval);
		
		$data = array
		(
			'uid' => getAuthUID(),
			'messages' => $msg->retval
		);
		if (!empty($person_id))
		{
			$person = $this->PersonModel->load($person_id);
			$data['person'] = $person->retval[0];
		}
		//var_dump ($data);
		$this->load->view('system/messagesOutbox.php', $data);
	}
	
	public function view($msg_id)
	{
		$msg = $this->messagelib->getMessage($msg_id);
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
		if (! $this->messagelib->addRecipient(1))
			show_error('Error: AddRecipient');
		$msg = $this->messagelib->sendMessage(1,$body ,$subject);
		if ($msg->error)
			show_error($msg->retval);
		$msg_id = $msg->retval;

		redirect('/system/Message/view/'.$msg_id);
	}
}
