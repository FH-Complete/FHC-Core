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

	public function index()
	{
		$this->load->view('system/messages.php', array('person_id' => $this->getPersonId()));
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
			'messages' => $msg->retval,
			'person' => $person->retval[0]
		);
			
		$this->load->view('system/messagesInbox.php', $data);
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
			'messages' => $msg->retval,
			'person' => $person->retval[0]
		);
		
		$this->load->view('system/messagesOutbox.php', $data);
	}
	
	public function view($msg_id, $person_id)
	{
		$msg = $this->messagelib->getMessage($msg_id, $person_id);
		if ($msg->error)
		{
			show_error($msg->retval);
		}
		
		$v = $this->load->view('system/messageView', array('message' => $msg->retval[0]));
	}

	public function reply($msg_id, $person_id)
	{
		$msg = $this->messagelib->getMessage($msg_id, $person_id);
		if ($msg->error)
		{
			show_error($msg->retval);
		}
		
		$v = $this->load->view('system/messageReply', array('message' => $msg->retval[0]));
	}
	
	public function sendReply($msg_id, $person_id)
	{
		$subject = $this->input->post('subject');
		$body = $this->input->post('body');
		
		$this->load->model('system/Message_model', 'MessageModel');
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
		
		redirect('/system/Messages/view/' . $msg->retval . '/' . $originMsg->retval[0]->person_id);
	}
	
	public function write($sender_id)
	{
		$prestudent_id = $this->input->post('prestudent_id');
		
		$this->load->model('crm/Prestudent_model', 'PrestudentModel');
		$prestudent = $this->MessageModel->getMsgVarsData($prestudent_id);
		if ($prestudent->error)
		{
			show_error($prestudent->retval);
		}
		
		$this->load->model('system/Message_model', 'MessageModel');
		if (!hasData($variables = $this->MessageModel->getMessageVars()))
		{
			unset($variables);
		}
		else
		{
			$variablesArray = array();
			// Skip person_id and prestudent_id
			for($i = 2; $i < count($variables->retval); $i++)
			{
				$variablesArray['{'.str_replace(" ", "_", strtolower($variables->retval[$i])).'}'] = $variables->retval[$i];
			}
		}
		
		array_shift($variables->retval); // Remove person_id
		array_shift($variables->retval); // Remove prestudent_id
		
		$data = array (
			'sender_id' => $sender_id,
			'receivers' => $prestudent->retval,
			'variables' => $variablesArray
		);
		
		$v = $this->load->view('system/messageWrite', $data);
	}
	
	public function send($sender_id)
	{
		$error = false;
		
		$subject = $this->input->post('subject');
		$body = $this->input->post('body');
		$prestudents = $this->input->post('prestudents');
		$data = $this->MessageModel->getMsgVarsData($prestudents);
		if (hasData($data))
		{
			for ($i = 0; $i < count($data->retval); $i++)
			{
				$parsedText = "";
				$dataArray = (array)$data->retval[$i];
				foreach($dataArray as $key => $val)
				{
					$newKey = str_replace(" ", "_", strtolower($key));
					$dataArray[$newKey] = $dataArray[$key];
				}
				
				$parsedText = $this->messagelib->parseMessageText($body, $dataArray);
				
				$msg = $this->messagelib->sendMessage($sender_id, $dataArray['person_id'], $subject, $parsedText, PRIORITY_NORMAL);
				if ($msg->error)
				{
					show_error($msg->retval);
					$error = true;
					break;
				}
			}
		}
		
		if (!$error)
		{
			echo "Messages sent successfully";
		}
	}
	
	private function getPersonId()
	{
		$person_id = null;
		
		if ($this->input->get('person_id') !== null)
		{
			$person_id = $this->input->get('person_id');
		}
		else if ($this->input->post('person_id') !== null)
		{
			$person_id = $this->input->get('person_id');
		}
		
		if (!is_numeric($person_id))
		{
			show_error('Person_id is not numeric');
		}
		
		return $person_id;
	}
	
	public function getVorlage()
	{
		$vorlage_kurzbz = $this->input->get('vorlage_kurzbz');
		
		if (isset($vorlage_kurzbz))
		{
			$this->load->model('system/Vorlagestudiengang_model', 'VorlagestudiengangModel');
			$result = $this->VorlagestudiengangModel->loadWhere(array('vorlage_kurzbz' => $vorlage_kurzbz));
			
			$this->output
				->set_content_type('application/json')
				->set_output(json_encode($result));
		}
	}
	
	public function parseMessageText()
	{
		$prestudent_id = $this->input->get('prestudent_id');
		$text = $this->input->get('text');
		
		if (isset($prestudent_id))
		{
			$data = $this->MessageModel->getMsgVarsData($prestudent_id);
			
			$parsedText = "";
			if (hasData($data))
			{
				$dataArray = (array)$data->retval[0];
				foreach($dataArray as $key => $val)
				{
					$newKey = str_replace(" ", "_", strtolower($key));
					$dataArray[$newKey] = $dataArray[$key];
				}
				
				$parsedText = $this->messagelib->parseMessageText($text, $dataArray);
			}
			
			$this->output
				->set_content_type('application/json')
				->set_output(json_encode($parsedText));
		}
	}
}