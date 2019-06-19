<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

class Messages extends Auth_Controller
{
	public function __construct()
	{
		parent::__construct(
			array(
				'writeTemplate' => array('basis/message:rw', 'infocenter:rw'),
				'sendImplicitTemplate' => array('basis/message:rw', 'infocenter:rw'),
				'sendExplicitTemplateJson' => array('basis/message:rw', 'infocenter:rw'),
				'getVorlage' => array('basis/message:r', 'infocenter:r'),
				'parseMessageText' => array('basis/message:r', 'infocenter:r'),
				'getMessageFromIds' => array('basis/message:r', 'infocenter:r')
			)
		);

		// Loads model CLMessagesModel which contains the GUI logic
		$this->load->model('CL/Messages_model', 'CLMessagesModel');

		// Phrases used in loaded views
		$this->loadPhrases(
			array(
				'global',
				'ui'
			)
		);
	}

	// -----------------------------------------------------------------------------------------------------------------
	// Methods with HTML output

	/**
	 * Initialize all the parameters used by view system/messages/htmlWriteTemplate
	 * to build a GUI used to write a messate to user/s using a template
	 */
	public function writeTemplate()
	{
		$persons = $this->input->post('person_id');

		// Loads the view to write a new message with a template
		$this->load->view(
			'system/messages/htmlWriteTemplate',
			$this->CLMessagesModel->prepareHtmlWriteTemplatePersons($persons)
		);
	}

	/**
	 * Send a new message or reply to user/s
	 * If a relationmessage_id this message is a reply to another one
	 * Body is a template and will be parsed using information present in persons parameter
	 */
	public function sendImplicitTemplate()
	{
		$subject = $this->input->post('subject');
		$body = $this->input->post('body');
		$persons = $this->input->post('persons');
		$relationmessage_id = $this->input->post('relationmessage_id');

		$sendImplicitTemplate = $this->CLMessagesModel->sendImplicitTemplate($persons, $subject, $body, $relationmessage_id);
		if (isSuccess($sendImplicitTemplate))
		{
			$this->load->view('system/messages/htmlSuccess');
		}
		else
		{
			$this->load->view('system/messages/htmlError');
		}
	}

	// -----------------------------------------------------------------------------------------------------------------
	// Methods with JSON output called by this controller and FASMessages (view system/messages/htmlWriteTemplate)

	/**
	 * Returns an object that represent a template store in database
	 * If no templates are found with the given parameter or the given parameter is an empty string,
	 * then an error is returned
	 */
	public function getVorlage()
	{
		$vorlage_kurzbz = $this->input->get('vorlage_kurzbz');

		$this->outputJson($this->CLMessagesModel->getVorlage($vorlage_kurzbz));
	}

	/**
	 * Parse the given given text using data from the given user
	 * Use the CI parser which performs simple text substitution for pseudo-variable
	 */
	public function parseMessageText()
	{
		$person_id = $this->input->get('person_id');
		$text = $this->input->get('text');

		$this->outputJson($this->CLMessagesModel->parseMessageText($person_id, $text));
	}

	// -----------------------------------------------------------------------------------------------------------------
	// Methods with JSON output called by infocenter

	/**
	 * Outputs message data for a message (identified my msg id and receiver id) in JSON format
	 */
	public function getMessageFromIds()
	{
		$message_id = $this->input->get('msg_id');
		$receiver_id = $this->input->get('receiver_id');

		$this->outputJson($this->CLMessagesModel->getMessageFromIds($message_id, $receiver_id));
	}

	/**
	 * Send a new message
	 * - The recipients are prestudents
	 * - An email template with message var may be provided
	 * - A global organisation unit may be provided, otherwise is used the prestudent one
	 * - A template is explicitly specified
	 */
	public function sendExplicitTemplateJson()
	{
		$prestudents = $this->input->post('prestudents');
		$oe_kurzbz = $this->input->post('oe_kurzbz');
		$vorlage_kurzbz = $this->input->post('vorlage_kurzbz');
		$msgVars = $this->input->post('msgvars');

		$sendExplicitTemplate = $this->CLMessagesModel->sendExplicitTemplate($prestudents, $oe_kurzbz, $vorlage_kurzbz, $msgVars);
		$this->outputJson(getData($sendExplicitTemplate));
	}
}
