<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

class Messages extends Auth_Controller
{
	/**
	 *
	 */
	public function __construct()
	{
		parent::__construct(
			array(
				'write' => array('basis/message:rw', 'infocenter:rw'),
				'send' => array('basis/message:rw', 'infocenter:rw'),
				'sendJson' => array('basis/message:rw', 'infocenter:rw'),
				'getVorlage' => array('basis/message:r', 'infocenter:r'),
				'parseMessageText' => array('basis/message:r', 'infocenter:r'),
				'getMessageFromIds' => array('basis/message:r', 'infocenter:r')
			)
		);

		// Loads the message library
		$this->load->library('MessageLib');

		// Loads the widget library
		$this->load->library('WidgetLib');

		$this->load->model('system/Message_model', 'MessageModel');

		$this->loadPhrases(
			array(
				'global',
				'ui'
			)
		);
	}

	// -----------------------------------------------------------------------------------------------------------------
	// Public methods

	/**
	 * Write a new message
	 */
	public function write()
	{
		$person_id = $this->input->post('person_id');

		$sender_id = $this->_getAuthPersonId();

		$msgVarsData = $this->_getMsgVarsData($person_id);

		// Retrieves message vars for a person from view view vw_msg_vars_person
		$variablesArray = $this->messagelib->getMessageVarsPerson();

 		// Organisation units used to get the templates
		$oe_kurzbz = $this->messagelib->getOeKurzbz($sender_id);

 		// Admin or commoner?
 		$isAdmin = $this->messagelib->getIsAdmin($sender_id);

		$data = array (
			'recipients' => $msgVarsData->retval,
			'variables' => $variablesArray,
			'oe_kurzbz' => $oe_kurzbz, // used to get the templates
			'isAdmin' => $isAdmin
		);

		$this->load->view('system/messageWrite', $data);
	}

	/**
	 * Send message
	 */
	public function send()
	{
		$result = $this->_execSend();

		if (isSuccess($result))
		{
			echo "Messages sent successfully";
		}
		else
		{
			echo "Error when sending message";
		}
	}

	/**
	 * Send message, response is in JSON format
	 * @param $sender_id
	 */
	public function sendJson()
	{
		$result = $this->_execSend();

		$this->output
			->set_content_type('application/json')
			->set_output(json_encode($result));
	}

	/**
	 * Executes message sending
	 * @param $sender_id
	 * @return array wether execution was successfull - error or success
	 */
	private function _execSend()
	{
		$sender_id = $this->_getAuthPersonId();

		$subject = $this->input->post('subject');
		$body = $this->input->post('body');

		$prestudents = $this->input->post('prestudents');
		// OR
		$persons = $this->input->post('persons');

		$relationmessage_id = $this->input->post('relationmessage_id');

		// From infocenterDetails
		$vorlage_kurzbz = $this->input->post('vorlage_kurzbz');
		$oe_kurzbz = $this->input->post('oe_kurzbz');
		$msgvars = $this->input->post('msgvars');

		if (!is_numeric($relationmessage_id))
		{
			$relationmessage_id = null;
		}

		// get message data of prestudents or persons
		$prestudentsData = array();
		if ($prestudents !== null)
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
				foreach ($dataArray as $key => $val)
				{
					$newKey = str_replace(" ", "_", strtolower($key));
					$dataArray[$newKey] = $dataArray[$key];
				}

				// if oe not given, get from prestudent
				if (isEmptyString($oe_kurzbz) && hasData($prestudentsData))
				{
					for ($p = 0; $p < count($prestudentsData->retval); $p++)
					{
						if ($prestudentsData->retval[$p]->prestudent_id == $data->retval[$i]->prestudent_id)
						{
							$oe_kurzbz = $prestudentsData->retval[$p]->oe_kurzbz;
						}
					}
				}

				// send without vorlage
				if (isEmptyString($vorlage_kurzbz))
				{
					$parsedText = $this->messagelib->parseMessageText($body, $dataArray);
					$msg = $this->messagelib->sendMessage($sender_id, $dataArray['person_id'], $subject, $parsedText, PRIORITY_NORMAL, $relationmessage_id, $oe_kurzbz);
				}
				// send with vorlage
				else
				{
					if (isset($msgvars) && is_array($msgvars))
					{
						//additional message variables
						foreach ($msgvars as $key => $msgvar)
						{
							$dataArray[$key] = $msgvar;
						}
					}
					$msg = $this->messagelib->sendMessageVorlage($sender_id, $dataArray['person_id'], $vorlage_kurzbz, $oe_kurzbz, $dataArray);
				}

				if ($msg->error)
				{
					return error($msg->msg);
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
					getAuthUID()
				);

			}
			return success('success');
		}
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
			$this->VorlagestudiengangModel->addOrder('version','DESC');
			$result = $this->VorlagestudiengangModel->loadWhere(array('vorlage_kurzbz' => $vorlage_kurzbz));

			$this->outputJsonSuccess($result);
		}
	}

	/**
	 * parseMessageText
	 */
	public function parseMessageText()
	{
		$prestudent_id = $this->input->get('prestudent_id');
		$person_id = $this->input->get('person_id');
		$text = $this->input->get('text');
		$parsedText = '';
		$data = null;

		if (is_numeric($person_id))
		{
			$data = $this->MessageModel->getMsgVarsDataByPersonId($person_id);
		}
		elseif (is_numeric($prestudent_id))
		{
			$data = $this->MessageModel->getMsgVarsDataByPrestudentId($prestudent_id);
		}

		if (is_error($data) || !hasData($data))
		{
			$this->outputJsonError($data->retval);
		}
		else
		{
			$dataArray = (array)$data->retval[0];
			foreach ($dataArray as $key => $val)
			{
				$newKey = str_replace(" ", "_", strtolower($key));
				$dataArray[$newKey] = $dataArray[$key];
			}

			$parsedText = $this->messagelib->parseMessageText($text, $dataArray);

			$this->outputJsonSuccess($parsedText);
		}
	}

	/**
	 * Outputs message data for a message (identified my msg id and receiver id) in JSON format
	 * @param $msg_id
	 * @param $receiver_id
	 */
	public function getMessageFromIds()
	{
		$msg_id = $this->input->get('msg_id');
		$receiver_id = $this->input->get('receiver_id');
		$msg = $this->messagelib->getMessage($msg_id, $receiver_id);

		$this->output
			->set_content_type('application/json')
			->set_output(json_encode(array($msg->retval[0])));
	}

	// -----------------------------------------------------------------------------------------------------------------
	// Private methods

	/**
	 * Retrieves message vars from view vw_msg_vars
	 */
	private function _getMsgVarsData($person_id)
	{
		$msgVarsData = $this->MessageModel->getMsgVarsDataByPersonId($person_id);

		if (isError($msgVarsData))
		{
			show_error($msgVarsData->retval);
		}

		return $msgVarsData;
	}

	/**
	 *
	 */
	private function _getAuthPersonId()
	{
		$sender_id = null;

		$this->load->model('person/Person_model', 'PersonModel');
		$authUser = $this->PersonModel->getByUid(getAuthUID());
		if (isError($authUser))
		{
			show_error($authUser->retval);
		}
		elseif (!hasData($authUser))
		{
			show_error('The current logged user person_id is not defined');
		}
		else
		{
			$sender_id = $authUser->retval[0]->person_id;
		}

		return $sender_id;
	}
}
