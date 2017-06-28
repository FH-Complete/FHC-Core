<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

class Messages extends VileSci_Controller 
{
	public function __construct()
    {
        parent::__construct();
        
        // Loads the message library
        $this->load->library('MessageLib');
        
        // Loads the widget library
		$this->load->library('WidgetLib');
		
		$this->load->model('person/Person_model', 'PersonModel');
    }
	
	/**
	 * 
	 */
	public function write($sender_id, $msg_id = null, $receiver_id = null)
	{
		$prestudent_id = $this->input->post('prestudent_id');
		$msg = null;
		
		// Get message data if possible
		if (is_numeric($msg_id) && is_numeric($receiver_id))
		{
			$msg = $this->messagelib->getMessage($msg_id, $receiver_id);
			if ($msg->error)
			{
				show_error($msg->retval);
			}
			else
			{
				$msg = $msg->retval[0];
			}
		}
		
		// Get variables
		$this->load->model('system/Message_model', 'MessageModel');
		$msgVarsDataByPrestudentId = $this->MessageModel->getMsgVarsDataByPrestudentId($prestudent_id);
		if ($msgVarsDataByPrestudentId->error)
		{
			show_error($msgVarsDataByPrestudentId->retval);
		}
		
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
		
		// Organisation units
		$oe_kurzbz = array(); // A person can have more organisation units
		$this->load->model('person/Benutzerfunktion_model', 'BenutzerfunktionModel');
		$benutzerResult = $this->BenutzerfunktionModel->getByPersonId($sender_id);
		if (hasData($benutzerResult))
		{
			foreach($benutzerResult->retval as $val)
			{
				$oe_kurzbz[] = $val->oe_kurzbz;
			}
		}
		
		// Admin or commoner?
		$this->load->model('system/Benutzerrolle_model', 'BenutzerrolleModel');
		$isAdmin = $this->BenutzerrolleModel->isAdminByPersonId($sender_id);
		if (isError($isAdmin))
		{
			show_error($isAdmin->retval);
		}
		
		$data = array (
			'sender_id' => $sender_id,
			'receivers' => $msgVarsDataByPrestudentId->retval,
			'message' => $msg,
			'variables' => $variablesArray,
			'oe_kurzbz' => $oe_kurzbz,
			'isAdmin' => $isAdmin->retval
		);
		
		$v = $this->load->view('system/messageWrite', $data);
	}
	
	/**
	 * 
	 */
	public function send($sender_id)
	{
		$error = false;
		
		$subject = $this->input->post('subject');
		$body = $this->input->post('body');
		$prestudents = $this->input->post('prestudents');
		$relationmessage_id = $this->input->post('relationmessage_id');
		
		if (!isset($relationmessage_id) || $relationmessage_id == '')
		{
			$relationmessage_id = null;
		}
		
		$data = $this->MessageModel->getMsgVarsDataByPrestudentId($prestudents);
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
				
				$msg = $this->messagelib->sendMessage($sender_id, $dataArray['person_id'], $subject, $parsedText, PRIORITY_NORMAL, $relationmessage_id);
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
	
	/**
	 * 
	 */
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
	
	/**
	 * 
	 */
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
	
	/**
	 * 
	 */
	public function parseMessageText()
	{
		$prestudent_id = $this->input->get('prestudent_id');
		$text = $this->input->get('text');
		
		if (isset($prestudent_id))
		{
			$data = $this->MessageModel->getMsgVarsDataByPrestudentId($prestudent_id);
			
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