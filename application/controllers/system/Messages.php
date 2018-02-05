<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

class Messages extends VileSci_Controller
{
	private $uid; // contains the UID of the logged user

	/**
	 *
	 */
	public function __construct()
    {
        parent::__construct();

        // Loads the message library
        $this->load->library('MessageLib');

        // Loads the widget library
		$this->load->library('WidgetLib');

		$this->load->model('person/Person_model', 'PersonModel');

		$this->_setAuthUID(); // sets property uid
    }

	/**
	 * write
	 */
	public function write($sender_id, $msg_id = null, $receiver_id = null)
	{
		$prestudent_id = $this->input->post('prestudent_id');
		$person_id = $this->input->post('person_id');

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

		$variablesArray = array();
		$msgVarsData = array();

		// Get variables
		$this->load->model('system/Message_model', 'MessageModel');
		if($prestudent_id !== null)
			$this->getPrestudentMsgData($prestudent_id, $variablesArray, $msgVarsData);
		elseif($person_id !== null)
			$this->getPersonMsgData($person_id, $variablesArray, $msgVarsData);

		// Organisation units used to get the templates
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
			'receivers' => isset($msgVarsData->retval) ? $msgVarsData->retval : $msgVarsData,
			'message' => $msg,
			'variables' => $variablesArray,
			'oe_kurzbz' => $oe_kurzbz, // used to get the templates
			'isAdmin' => $isAdmin->retval
		);

		$v = $this->load->view('system/messageWrite', $data);
	}

	private function getPrestudentMsgData($prestudent_id, &$variablesArray, &$msgVarsData)
	{
		$msgVarsData = $this->MessageModel->getMsgVarsDataByPrestudentId($prestudent_id);
		if ($msgVarsData->error)
		{
			show_error($msgVarsData->retval);
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
	}

	private function getPersonMsgData($person_id, &$variablesArray, &$msgVarsData)
	{
		$msgVarsData = $this->MessageModel->getMsgVarsDataByPersonId($person_id);
		if ($msgVarsData->error)
		{
			show_error($msgVarsData->retval);
		}

		if (!hasData($variables = $this->MessageModel->getMessageVarsPerson()))
		{
			unset($variables);
		}
		else
		{
			$variablesArray = array();
			// Skip person_id
			for($i = 1; $i < count($variables->retval); $i++)
			{
				$variablesArray['{'.str_replace(" ", "_", strtolower($variables->retval[$i])).'}'] = $variables->retval[$i];
			}
			array_shift($variables->retval); // Remove person_id
		}
	}

	/**
	 * send
	 */
	public function send($sender_id)
	{
		$error = false;

		$subject = $this->input->post('subject');
		$body = $this->input->post('body');
		$prestudents = $this->input->post('prestudents');
		$persons = $this->input->post('persons');
		$relationmessage_id = $this->input->post('relationmessage_id');

		if (!isset($relationmessage_id) || $relationmessage_id == '')
		{
			$relationmessage_id = null;
		}

		// get message data of prestudents or persons
		$prestudentsData = array();
		if($prestudents !== null)
		{
			$data = $this->MessageModel->getMsgVarsDataByPrestudentId($prestudents);
			//
			$this->load->model('crm/Prestudent_model', 'PrestudentModel');
			$prestudentsData = $this->PrestudentModel->getOrganisationunits($prestudents);
		}
		else
			$data = $this->MessageModel->getMsgVarsDataByPersonId($persons);

		// send message(s)
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

				$oe_kurzbz = null;
				if(hasData($prestudentsData))
				{
					for ($p = 0; $p < count($prestudentsData->retval); $p++)
					{
						if ($prestudentsData->retval[$p]->prestudent_id == $data->retval[$i]->prestudent_id)
						{
							$oe_kurzbz = $prestudentsData->retval[$p]->oe_kurzbz;
						}
					}
				}

				$msg = $this->messagelib->sendMessage($sender_id, $dataArray['person_id'], $subject, $parsedText, PRIORITY_NORMAL, $relationmessage_id, $oe_kurzbz);
				if ($msg->error)
				{
					show_error($msg->retval);
					$error = true;
					break;
				}

				// Loads the person log library
				$this->load->library('PersonLogLib');

				//write log entry
				$this->personloglib->log(
					$dataArray['person_id'],
					'Action',
					array(
						'name' => 'Message sent',
						'message' => 'Message sent from person '.$sender_id.' to '.$dataArray['person_id'].', messageid '.$msg->retval,
						'success' => 'true'
					),
					'kommunikation',
					'core',
					null,
					$this->uid
				);
			}
		}

		if (!$error)
		{
			echo "Messages sent successfully";
		}
	}

	/**
	 * getPersonId
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
	 * Retrieve the UID of the logged user and checks if it is valid
	 */
	private function _setAuthUID()
	{
		$this->uid = getAuthUID();

		if (!$this->uid) show_error('User authentification failed');
	}

	/**
	 * getVorlage
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
	 * parseMessageText
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

	/**
	 * Outputs message data for a message (identified my msg id and receiver id) in JSON format
	 * @param $msg_id
	 * @param $receiver_id
	 */
	public function getMessageFromIds($msg_id, $receiver_id)
	{
/*		$this->MessageModel->addSelect('subject, body, oe_kurzbz');
		$msg = $this->MessageModel->load($msg_id);*/
		$msg = $this->messagelib->getMessage($msg_id, $receiver_id);

		$this->output
			->set_content_type('application/json')
			->set_output(json_encode(array($msg->retval[0])));
	}

}
