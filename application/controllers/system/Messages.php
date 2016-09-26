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
		$msg = $this->messagelib->getSentMessagesByPerson($person_id);
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

	public function write($msg_id, $person_id)
	{
		$msg = $this->messagelib->getMessage($msg_id, $person_id);
		if ($msg->error)
		{
			show_error($msg->retval);
		}
		
		$v = $this->load->view("system/messageWrite", array("message" => $msg->retval[0]));
	}
	
	public function send($msg_id, $person_id)
	{
		$subject = $this->input->post("subject");
		$body = $this->input->post("body");
		
		$this->load->model("system/Message_model", "MessageModel");
		$originMsg = $this->MessageModel->load($msg_id);
		if ($originMsg->error)
		{
			show_error($originMsg->retval);
		}
		
		$msg = $this->messagelib->sendMessage($person_id, $originMsg->retval[0]->person_id, $subject, $body, PRIORITY_NORMAL, $msg_id);
		if ($msg->error)
		{
			show_error($msg->retval);
		}
		
		redirect("/system/Messages/view/" . $msg->retval . "/" . $originMsg->retval[0]->person_id);
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
