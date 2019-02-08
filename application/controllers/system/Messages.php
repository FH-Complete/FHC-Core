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
		$sender_id = null;

		$authUser = $this->_getAuthUser();
		if (isError($authUser))
		{
			show_error($authUser->retval);
		}
		else
		{
			$sender_id = getData($authUser)[0]->person_id;
		}

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

		$this->load->view('system/messages/messageWrite', $data);
	}

	/**
	 * Send message
	 */
	public function send()
	{
		$persons = $this->input->post('persons');
		$relationmessage_id = $this->input->post('relationmessage_id');

		$msgVarsData = $this->MessageModel->getMsgVarsDataByPersonId($persons);

		$send = $this->_send($msgVarsData, $relationmessage_id);

		$this->load->view('system/messages/messageSent', array('success' => isSuccess($send)));
	}

	/**
	 * Send message, response is in JSON format
	 */
	public function sendJson()
	{
		$prestudents = $this->input->post('prestudents');
		$vorlage_kurzbz = $this->input->post('vorlage_kurzbz');
		$oe_kurzbz = $this->input->post('oe_kurzbz');
		$msgVars = $this->input->post('msgvars');

		$msgVarsData = $this->MessageModel->getMsgVarsDataByPrestudentId($prestudents);

		$this->load->model('crm/Prestudent_model', 'PrestudentModel');
		$prestudentsData = $this->PrestudentModel->getOrganisationunits($prestudents);

		if (isEmptyString($oe_kurzbz) && hasData($msgVarsData) && hasData($prestudentsData))
		{
			for ($i = 0; $i < count($msgVarsData->retval); $i++)
			{
				for ($p = 0; $p < count($prestudentsData->retval); $p++)
				{
					if ($prestudentsData->retval[$p]->prestudent_id == $msgVarsData->retval[$i]->prestudent_id)
					{
						$msgVarsData->retval[$i]->oe_kurzbz = $prestudentsData->retval[$p]->oe_kurzbz;
						break;
					}
				}
			}
		}

		$send = $this->_send($msgVarsData, null, $oe_kurzbz, $vorlage_kurzbz, $msgVars);
		if (isError($send))
		{
			$this->outputJsonError($send->retval);
		}
		else
		{
			$this->outputJsonSuccess($send->retval);
		}
	}

	// -----------------------------------------------------------------------------------------------------------------
	// Private methods

	/**
	 * Executes message sending
	 * @param $sender_id
	 * @return array wether execution was successfull - error or success
	 */
	private function _send($msgVarsData, $relationmessage_id = null, $oe_kurzbz = null, $vorlage_kurzbz = null, $msgVars = null)
	{
		$subject = $this->input->post('subject');
		$body = $this->input->post('body');

		$authUser = $this->_getAuthUser();
		if (isError($authUser)) return $authUser;

		$sender_id = getData($authUser)[0]->person_id;

		// send message(s)
		if (hasData($msgVarsData))
		{
			// Loads the person log library
			$this->load->library('PersonLogLib');

			for ($i = 0; $i < count($msgVarsData->retval); $i++)
			{
				$parsedText = "";
				$msgVarsDataArray = (array)$msgVarsData->retval[$i];

				// Send without vorlage
				if (isEmptyString($vorlage_kurzbz))
				{
					$parsedText = $this->messagelib->parseMessageText($body, $msgVarsDataArray);
					$msg = $this->messagelib->sendMessage($sender_id, $msgVarsDataArray['person_id'], $subject, $parsedText, PRIORITY_NORMAL, $relationmessage_id, $oe_kurzbz);
				}
				// Send with vorlage
				else
				{
					if (isset($msgVars) && is_array($msgVars))
					{
						// Additional message variables
						foreach ($msgVars as $key => $msgvar)
						{
							$msgVarsDataArray[$key] = $msgvar;
						}
					}
					$msg = $this->messagelib->sendMessageVorlage($sender_id, $msgVarsDataArray['person_id'], $vorlage_kurzbz, $oe_kurzbz, $msgVarsDataArray);
				}

				if (isError($msg)) return $msg;

				//write log entry
				$this->personloglib->log(
					$msgVarsDataArray['person_id'],
					'Action',
					array(
						'name' => 'Message sent',
						'message' => 'Message sent from person '.$sender_id.' to '.$msgVarsDataArray['person_id'].', messageid '.$msg->retval,
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
		$result = null;

		if (!isEmptyString($vorlage_kurzbz))
		{
			$this->load->model('system/Vorlagestudiengang_model', 'VorlagestudiengangModel');
			$this->VorlagestudiengangModel->addOrder('version','DESC');

			$result = $this->VorlagestudiengangModel->loadWhere(array('vorlage_kurzbz' => $vorlage_kurzbz));
		}
		else
		{
			$result = error('The given vorlage_kurzbz is not valid');
		}

		if (isError($result) || !hasData($result))
		{
			$this->outputJsonError($result->retval);
		}
		else
		{
			$this->outputJsonSuccess($result->retval);
		}
	}

	/**
	 * parseMessageText
	 */
	public function parseMessageText()
	{
		$person_id = $this->input->get('person_id');
		$text = $this->input->get('text');
		$parsedText = '';
		$data = null;

		if (is_numeric($person_id))
		{
			$data = $this->MessageModel->getMsgVarsDataByPersonId($person_id);
		}
		else
		{
			$data = error('The given person_id is not a valid number');
		}

		if (isError($data) || !hasData($data))
		{
			$this->outputJsonError($data->retval);
		}
		else
		{
			$parsedText = $this->messagelib->parseMessageText($text, (array)$data->retval[0]);

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
	private function _getAuthUser()
	{
		$sender_id = null;

		$this->load->model('person/Person_model', 'PersonModel');
		$authUser = $this->PersonModel->getByUid(getAuthUID());

		if (!hasData($authUser)) $authUser = error('The current logged user person_id is not defined');

		return $authUser;
	}
}
