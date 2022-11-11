<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

class FASMessages extends Auth_Controller
{
	/**
	 *
	 */
	public function __construct()
	{
		parent::__construct(
			array(
				'writeTemplate' => 'basis/message:rw',
				'writeReplyTemplate' => 'basis/message:rw'
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

	/**
	 * Writes a new message to a prestudent using templates
	 */
	public function writeTemplate()
 	{
		$prestudents = $this->input->post('prestudent_id'); // recipients prestudend_id(s)

		// Loads the view to write a new message with a template
		$this->load->view(
			'system/messages/FAShtmlWriteTemplate',
			$this->CLMessagesModel->prepareHtmlWriteTemplatePrestudents($prestudents)
		);
 	}

	/**
	 * Writes a reply to a message identified by parameters $message_id and $recipient_id
	 * The recipient is a prestudent
	 * Uses templates
	 */
	public function writeReplyTemplate($message_id, $recipient_id)
 	{
		$prestudents = $this->input->post('prestudent_id'); // recipients prestudend_id(s)

		// Loads the view to write a new message with a template
		$this->load->view(
			'system/messages/FAShtmlWriteTemplate',
			$this->CLMessagesModel->prepareHtmlWriteTemplatePrestudents($prestudents, $message_id, $recipient_id)
		);
 	}
}
