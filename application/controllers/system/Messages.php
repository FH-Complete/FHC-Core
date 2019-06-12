<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

class Messages extends Auth_Controller
{
	/**
	 * MessageLib is loaded by CLMessagesModel
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
	// Public methods - HTML output

	/**
	 * Initialize all the parameters used by view system/messages/messageWrite
	 * to build a GUI used to write a messate to user/s
	 */
	public function write()
	{
		$persons = $this->input->post('person_id');

		$authUser = $this->CLMessagesModel->getAuthUser();
		if (isError($authUser)) show_error(getData($authUser));

		$sender_id = getData($authUser)[0]->person_id;

		// Retrieves person information
		$msgVarsData = $this->MessageModel->getMsgVarsDataByPersonId($persons);
		if (isError($msgVarsData)) show_error(getData($msgVarsData));

		// Retrieves message vars from view vw_msg_vars_person
		$variables = $this->messagelib->getMessageVarsPerson();
		if (isError($variables)) show_error(getData($variables));

 		// Organisation units used to get the templates
		$oe_kurzbz = $this->messagelib->getOeKurzbz($sender_id);
		if (isError($oe_kurzbz)) show_error(getData($oe_kurzbz));

 		// Admin or commoner?
		$isAdmin = $this->messagelib->getIsAdmin($sender_id);
		if (isError($isAdmin)) show_error(getData($isAdmin));

		$this->load->view(
			'system/messages/messageWrite',
			array (
				'recipients' => getData($msgVarsData), // recipients data
				'variables' => getData($variables), // message vars
				'oe_kurzbz' => getData($oe_kurzbz), // used to get the templates
				'isAdmin' => getData($isAdmin) // is admin?
			)
		);
	}

	/**
	 * Send a new message or reply to user/s
	 * If a relationmessage_id this message is a reply to another one
	 */
	public function send()
	{
		$persons = $this->input->post('persons');
		$relationmessage_id = $this->input->post('relationmessage_id');

		// Retrieves message vars data for the fiven user/s
		$msgVarsData = $this->MessageModel->getMsgVarsDataByPersonId($persons);

		// Send the message
		$send = $this->CLMessagesModel->send($msgVarsData, $relationmessage_id);

		$this->load->view('system/messages/messageSent', array('success' => isSuccess($send)));
	}

	// -----------------------------------------------------------------------------------------------------------------
	// Public methods - JSON output

	/**
	 * Send a new message
	 * - The recipients are prestudents
	 * - An email template with message var may be provided
	 * - A global organisation unit may be provided, otherwise is used the prestudent one
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

		$this->outputJson(getData($send));
	}

	/**
	 * Returns an object that represent a template store in database
	 * If no templates are found with the given parameter or the given parameter is an empty string,
	 * then an error is returned
	 */
	public function getVorlage()
	{
		$vorlage_kurzbz = $this->input->get('vorlage_kurzbz');
		$result = error('The given vorlage_kurzbz is not valid');

		if (!isEmptyString($vorlage_kurzbz))
		{
			$this->load->model('system/Vorlagestudiengang_model', 'VorlagestudiengangModel');
			$this->VorlagestudiengangModel->addOrder('version','DESC');

			$result = $this->VorlagestudiengangModel->loadWhere(array('vorlage_kurzbz' => $vorlage_kurzbz));
		}

		if (isError($result) || !hasData($result))
		{
			$this->outputJsonError(getData($result));
		}
		else
		{
			$this->outputJsonSuccess(getData($result));
		}
	}

	/**
	 * Parse the given given text using data from the given user
	 * Use the CI parser which performs simple text substitution for pseudo-variable
	 */
	public function parseMessageText()
	{
		$person_id = $this->input->get('person_id');
		$text = $this->input->get('text');
		$msgVarsData = error('The given person_id is not a valid number');

		if (is_numeric($person_id))
		{
			$msgVarsData = $this->MessageModel->getMsgVarsDataByPersonId($person_id);
		}

		if (isError($msgVarsData) || !hasData($msgVarsData))
		{
			$this->outputJsonError(getData($msgVarsData));
		}
		else
		{
			$this->outputJsonSuccess(
				parseText(
					$text,
					$this->CLMessagesModel->replaceKeys((array)getData($msgVarsData)[0])
				)
			);
		}
	}

	/**
	 * Outputs message data for a message (identified my msg id and receiver id) in JSON format
	 */
	public function getMessageFromIds()
	{
		$msg_id = $this->input->get('msg_id');
		$receiver_id = $this->input->get('receiver_id');

		$msg = $this->messagelib->getMessage($msg_id, $receiver_id);

		if (isError($msg) || !hasData($msg))
		{
			$this->outputJson(array());
		}
		else
		{
			$this->outputJson(array(getData($msg)[0]));
		}
	}
}
