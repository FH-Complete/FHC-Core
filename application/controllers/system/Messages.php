<?php

if (! defined("BASEPATH")) exit("No direct script access allowed");

class Messages extends VileSci_Controller 
{
	public function __construct()
    {
        parent::__construct();
        $this->load->library("MessageLib");
		$this->load->model("person/Person_model", "PersonModel");
    }

	public function index()
	{
		$this->load->view("system/messages.php", array("person_id" => $this->getPersonId()));
	}

	public function inbox($person_id)
	{
		$msg = $this->messagelib->getMessagesByPerson($person_id);
		if ($msg->error)
		{
			show_error($msg->retval);
		}
		
		$person = $this->PersonModel->load($person_id);
		if ($person->error)
		{
			show_error($person->retval);
		}
		
		$data = array (
			"messages" => $msg->retval,
			"person" => $person->retval[0]
		);
			
		$this->load->view("system/messagesInbox.php", $data);
	}

	public function outbox($person_id)
	{
		$msg = $this->messagelib->getMessagesByPerson($person_id);
		if ($msg->error)
		{
			show_error($msg->retval);
		}
		
		$person = $this->PersonModel->load($person_id);
		if ($person->error)
		{
			show_error($person->retval);
		}
		
		$data = array (
			"messages" => $msg->retval,
			"person" => $person->retval[0]
		);
		
		$this->load->view("system/messagesOutbox.php", $data);
	}
	
	public function view($msg_id, $person_id)
	{
		$msg = $this->messagelib->getMessage($msg_id, $person_id);
		if ($msg->error)
		{
			show_error($msg->retval);
		}
		
		$v = $this->load->view("system/messageView", array("message" => $msg->retval[0]));
	}

	public function write($vorlage_kurzbz = null)
	{
		$data = array
		(
			"subject" => "TestSubject",
			"body" => "TestDevelopmentBodyText"
		);		
		$v = $this->load->view("system/messageWrite", $data);
	}
	
	public function send()
	{
		$body = $this->input->post("body", TRUE);
		$subject = $this->input->post("subject", TRUE);
		if (! $this->messagelib->addRecipient(1))
			show_error("Error: AddRecipient");
		$msg = $this->messagelib->sendMessage(1,$body ,$subject);
		if ($msg->error)
			show_error($msg->retval);
		$msg_id = $msg->retval;

		redirect("/system/Message/view/".$msg_id);
	}
	
	private function getPersonId()
	{
		$person_id = null;
		
		if ($this->input->get("person_id") !== null)
		{
			$person_id = $this->input->get("person_id");
		}
		else if ($this->input->post("person_id") !== null)
		{
			$person_id = $this->input->get("person_id");
		}
		
		if (!is_numeric($person_id))
		{
			show_error("Person_id is not numeric");
		}
		
		return $person_id;
	}
}
