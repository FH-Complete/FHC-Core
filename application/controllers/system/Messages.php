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
		$this->load->model('CL/Messages_model', 'CLMessagesModel');

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

		$authUser = $this->CLMessagesModel->getAuthUser();
		if (isError($authUser))
		{
			show_error(getError($authUser));
		}
		else
		{
			$sender_id = getData($authUser)[0]->person_id;
		}

		$msgVarsData = $this->MessageModel->getMsgVarsDataByPersonId($person_id);
		if (isError($msgVarsData)) show_error(getError($msgVarsData));

		// Retrieves message vars for a person from view view vw_msg_vars_person
		$variables = $this->messagelib->getMessageVarsPerson();
		if (isError($variables)) show_error(getError($variables));

 		// Organisation units used to get the templates
		$oe_kurzbz = $this->messagelib->getOeKurzbz($sender_id);
		if (isError($oe_kurzbz)) show_error(getError($oe_kurzbz));

 		// Admin or commoner?
		$isAdmin = $this->messagelib->getIsAdmin($sender_id);
		if (isError($isAdmin)) show_error(getError($isAdmin));

		$data = array (
			'recipients' => getData($msgVarsData),
			'variables' => getData($variables),
			'oe_kurzbz' => getData($oe_kurzbz), // used to get the templates
			'isAdmin' => getData($isAdmin)
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

		$send = $this->CLMessagesModel->send($msgVarsData, $relationmessage_id);

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

		// Adds the organisation unit to each prestudent
		if (isEmptyString($oe_kurzbz) && hasData($msgVarsData) && hasData($prestudentsData))
		{
			$this->CLMessagesModel->addOeToPrestudents($msgVarsData, $prestudentsData);
		}

		$send = $this->CLMessagesModel->send($msgVarsData, null, $oe_kurzbz, $vorlage_kurzbz, $msgVars);
		if (isError($send))
		{
			$this->outputJsonError(getError($send));
		}
		else
		{
			$this->outputJsonSuccess(getData($send));
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
			$this->outputJsonError(getError($result));
		}
		else
		{
			$this->outputJsonSuccess(getData($result));
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
			$this->outputJsonError(getError($data));
		}
		else
		{
			$parsedText = $this->messagelib->parseMessageText($text, $this->CLMessagesModel->replaceKeys((array)getData($data)[0]));

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
			->set_output(json_encode(array(getData($msg)[0])));
	}
}
