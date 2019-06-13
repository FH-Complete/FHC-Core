<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Messaging Library for FH-Complete
 */
class MessageLib
{
	// Config entries
	const CFG_SYSTEM_PERSON_ID = 'system_person_id';
	const CFG_SEND_IMMEDIATELY = 'send_immediately';
	const CFG_MESSAGE_SERVER = 'message_server';
	const CFG_MESSAGE_HTML_VIEW_URL = 'message_html_view_url';
	const CFG_OU_RECEIVERS = 'ou_receivers';

	// Templates names
	const NOTICE_TEMPLATE_HTML = 'MessageMailHTML';
	const NOTICE_TEMPLATE_TXT = 'MessageMailTXT';
	const NOTICE_TEMPLATE_FALLBACK_HTML = 'templates/mailHTML';
	const NOTICE_TEMPLATE_FALLBACK_TXT = 'templates/mailTXT';

	const EMAIL_KONTAKT_TYPE = 'email'; // Email kontakt type
	const SENT_INFO_NEWLINE = '\n'; // tbl_msg_recipient->sentInfo separator

	private $_ci;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		// Get code igniter instance
		$this->_ci =& get_instance();

		// Loads message configuration
		$this->_ci->config->load('message');

		// Loads VorlageLib
		$this->_ci->load->library('VorlageLib');
		// Loads Mail library
		$this->_ci->load->library('MailLib');

		// Loads message models
		$this->_ci->load->model('system/Message_model', 'MessageModel');
		$this->_ci->load->model('system/MsgStatus_model', 'MsgStatusModel');
		$this->_ci->load->model('system/Recipient_model', 'RecipientModel');
		$this->_ci->load->model('system/Attachment_model', 'AttachmentModel');

		// Loads extra models
		$this->_ci->load->model('person/Person_model', 'PersonModel');
		$this->_ci->load->model('system/Benutzerrolle_model', 'BenutzerrolleModel');
		$this->_ci->load->model('person/Benutzerfunktion_model', 'BenutzerfunktionModel');
		$this->_ci->load->model('organisation/Organisationseinheit_model', 'OrganisationseinheitModel');
	}

	//------------------------------------------------------------------------------------------------------------------
	// Public methods

	/**
	 * Returns the specified message for a specified person
	 */
	public function getMessage($msg_id, $person_id)
	{
		if (!is_numeric($msg_id)) return error('The given message id is not valid', MSG_ERR_INVALID_MSG_ID);
		if (!is_numeric($person_id)) return error('The given person id is not valid', MSG_ERR_INVALID_RECIPIENTS);

		return $this->_ci->RecipientModel->getMessage($msg_id, $person_id);
	}

	/**
	 * Sends a message to persons ($receiversPersonId)
	 */
	public function sendMessageUser(
		$receiversPersonId, $subject, $body, // Required parameters
		$sender_id = null, $senderOU = null, $relationmessage_id = null, $priority = MSG_PRIORITY_NORMAL, $multiPartMime = true
	)
	{
		// Retrieves receiver id and checks that is valid
		$receivers = $this->_getReceiversByPersonId($receiversPersonId);
		if (isError($receivers)) return $receivers;

		// Send the message and return the result
		return $this->_sendMessage($receivers, null, $subject, $body, $sender_id, $senderOU, $relationmessage_id, $priority, $multiPartMime);
	}

	/**
	 * Sends a message to persons ($receiversPersonId)
	 */
	public function sendMessageUserTemplate(
		$receiversPersonId, $vorlage, $parseData, // Required parameters
		$orgform = null, $sender_id = null, $senderOU = null, $relationmessage_id = null, $priority = MSG_PRIORITY_NORMAL, $multiPartMime = true
	)
	{
		// Loads template data
		$templateResult = $this->_ci->vorlagelib->loadVorlagetext($vorlage, $senderOU, $orgform, getUserLanguage());
		if (hasData($templateResult)) // if a template is found
		{
			$template = getData($templateResult)[0]; // template object

			// Parses template subject
			$subject = parseText($template->subject, $parseData);
			// Parses template text
			$body = parseText($template->text, $parseData);

			return $this->sendMessageUser(
				$receiversPersonId, $subject, $body, $sender_id, $senderOU, $relationmessage_id, $priority, $multiPartMime
			);
		}
		elseif (isError($templateResult)) // if an error occured
		{
			return $templateResult; // return it
		}
		else // if a template was not found
		{
			return error('Template was not found', MSG_ERR_INVALID_TEMPLATE);
		}
	}

	/**
	 * Sends a message to all the persons that are enabled to read messages for the given organisation unit ($receiversOU)
	 */
	public function sendMessageOU(
		$receiversOU, $subject, $body, // Required parameters
		$sender_id = null, $senderOU = null, $relationmessage_id = null, $priority = MSG_PRIORITY_NORMAL, $multiPartMime = true
	)
	{
		// If the recipient is an organisation unit that would be possible to send the same message (same message id)
		// to the entire organisation unit (one to many functionality)
		// In this case the receiver id is a the one present in message configuration
		$receivers = success(array($this->_ci->config->item(self::CFG_SYSTEM_PERSON_ID)));

		// Send the message and return the result
		return $this->_sendMessage($receivers, $receiversOU, $subject, $body, $sender_id, $senderOU, $relationmessage_id, $priority, $multiPartMime);
	}

	//------------------------------------------------------------------------------------------------------------------
	// Public methods called by a job

	/**
	 * Gets all NOT sent messages from DB and sends for each of them the notice email
	 * Does not return anything, it logs info and errors on CI logs and in tbl_msg_recipient table
	 * Wrapper for _sendNotice.
	 */
	public function sendAllNotices($numberToSent, $numberPerTimeRange, $emailTimeRange, $emailFromSystem)
	{
		// Overrides MailLib configs with the given parameters
		$this->_ci->maillib->overrideConfigs($numberToSent, $numberPerTimeRange, $emailTimeRange, $emailFromSystem);

		// Retrieves a certain amount of NOT sent messages, the amount is given by maillib->email_number_to_sent
		$messagesResult = $this->_ci->RecipientModel->getMessages(
			self::EMAIL_KONTAKT_TYPE,
			null,
			$this->_ci->maillib->getEmailNumberToSent()
		);

		if (isError($messagesResult)) terminateWithError(getData($messagesResult)); // If an error occurred then log it and terminate

		$sendNotice = $this->_sendNotice(getData($messagesResult));

		if (isError($sendNotice)) terminateWithError(getData($sendNotice)); // If an error occurred then log it and terminate
	}

	//------------------------------------------------------------------------------------------------------------------
	// Public methods used by to build the GUI to write messages to user/s

	/**
	 * Retrieves message vars from view vw_msg_vars_person
	 */
	public function getMessageVarsPerson()
	{
		// Retrieves message vars from view vw_msg_vars_person
		$messageVarsPerson = $this->_ci->MessageModel->getMessageVarsPerson();
		if (isSuccess($messageVarsPerson)) // if everything is ok
		{
			$variablesArray = array();
			$tmpVariablesArray = getData($messageVarsPerson);

			// Starts from 1 to skip the first element which is person_id
			for ($i = 1; $i < count($tmpVariablesArray); $i++)
			{
				$variablesArray['{'.str_replace(' ', '_', strtolower($tmpVariablesArray[$i])).'}'] = $tmpVariablesArray[$i];
			}

			return success($variablesArray);
		}

		return $messageVarsPerson; // otherwise returns the error
	}

	/**
	 * Retrieves organisation units for each role that a user plays inside that organisation unit
	 */
	public function getOeKurzbz($sender_id)
	{
		// Retrieves organisation units for a user from database
 		$benutzer = $this->_ci->BenutzerfunktionModel->getByPersonId($sender_id);
 		if (isSuccess($benutzer)) // if everything is ok
 		{
			$ouArray = array();

			// Copies organisation units in $ouArray array
 			foreach (getData($benutzer) as $val) $ouArray[] = $val->oe_kurzbz;

			return success($ouArray);
 		}

		return $benutzer; // otherwise returns the error
	}

	/**
	 * Admin or commoner?
	 */
	public function getIsAdmin($sender_id)
	{
 		return $this->_ci->BenutzerrolleModel->isAdminByPersonId($sender_id);
	}

	//------------------------------------------------------------------------------------------------------------------
	// Public methods used by REST API

	/**
	 * Return all messages, including the latest status for specified user. It don´t returns Attachments.
	 * The sender organisation unit may be specified to filter messages
	 */
	public function getMessagesByUID($uid, $senderOU = null, $all = false)
	{
		if (isEmptyString($uid)) return error('The given message id is not valid', MSG_ERR_INVALID_MSG_ID);

		return $this->_ci->RecipientModel->getMessagesByUID($uid, $senderOU, $all);
	}

	/**
	 * Return all messages, including the latest status for specified user. It does not return attachments
	 * The sender organisation unit may be specified to filter messages
	 */
	public function getMessagesByPerson($person_id, $senderOU = null, $all = false)
	{
		if (!is_numeric($person_id)) return error('The given message id is not valid', MSG_ERR_INVALID_MSG_ID);

		return $this->_ci->RecipientModel->getMessagesByPerson($person_id, $senderOU, $all);
	}

	/**
	 * Get all sent messages from a person identified by person_id
	 * The sender organisation unit may be specified to filter messages
	 */
	public function getSentMessagesByPerson($person_id, $senderOU = null, $all = false)
	{
		if (!is_numeric($person_id)) return error('The given message id is not valid', MSG_ERR_INVALID_MSG_ID);

		return $this->_ci->MessageModel->getMessagesByPerson($person_id, $senderOU, $all);
	}

	/**
	 * Retrieves a message by its token
	 * If a message is found with the given token then this message is set as read
	 */
	public function getMessageByToken($token)
	{
		if (isEmptyString($token)) return error('The given token is not valid', MSG_ERR_INVALID_TOKEN);

		$messageTokenResult = $this->_ci->RecipientModel->getMessageByToken($token);
		if (hasData($messageTokenResult))
		{
			// Searches for a status that is NOT unread
			$found = false;

			foreach (getData($messageTokenResult) as $message)
			{
				if ($message->status > MSG_STATUS_UNREAD)
				{
					$found = true;
					break;
				}
			}

			// If NOT found then insert the read status
			if (!$found)
			{
				$statusData = array(
					'message_id' => getData($messageTokenResult)[0]->message_id,
					'person_id' => getData($messageTokenResult)[0]->receiver_id,
					'status' => MSG_STATUS_READ
				);

				$messageTokenResultIns = $this->_ci->MsgStatusModel->insert($statusData);
				// If an error occured while writing on data base, then return it
				if (isError($messageTokenResultIns)) $messageTokenResult = $messageTokenResultIns;
			}
		}

		return $messageTokenResult;
	}

	/**
	 * Counts the unread messages for the given user
	 * The sender organisation unit may be specified to filter messages
	 */
	public function getCountUnreadMessages($person_id, $senderOU = null)
	{
		if (!is_numeric($person_id)) return error('The given person id is not valid', MSG_ERR_INVALID_RECIPIENTS);

		return $this->_ci->RecipientModel->getCountUnreadMessages($person_id, $senderOU);
	}

	/**
	 * Change the message status of the given message specified by message_id and person_id, using the given status
	 * NOTE: it performs an insert NOT an update
	 */
	public function updateMessageStatus($message_id, $person_id, $status)
	{
		if (!is_numeric($message_id)) return error('The given message id is not valid', MSG_ERR_INVALID_MSG_ID);
		if (!is_numeric($person_id)) return error('The given person id is not valid', MSG_ERR_INVALID_RECIPIENTS);
		if (!is_numeric($status)) return error('The given status is not valid', MSG_ERR_INVALID_STATUS_ID);

		$this->_ci->MsgStatusModel->resetQuery(); // Reset an eventually already buit query

		// Searches if the status is already present
		$updMessageStatusResult = $this->_ci->MsgStatusModel->load(array($message_id, $person_id, $status));
		if (!hasData($updMessageStatusResult)) // if not found
		{
			// Insert the new status
			$statusKey = array(
				'message_id' => $message_id,
				'person_id' => $person_id,
				'status' => $status
			);
			$updMessageStatusResult = $this->_ci->MsgStatusModel->insert($statusKey);
		}

		return $updMessageStatusResult;
	}

	//------------------------------------------------------------------------------------------------------------------
	// Private methods

	/**
	 *
	 */
	private function _getSender($sender_id)
	{
		// By default the sender is defined in message configuration
		$sender = success($this->_ci->config->item(self::CFG_SYSTEM_PERSON_ID));
		if ($sender_id != null) // if it was given as parameter
		{
			if (is_numeric($sender_id)) // if it valid -> it is a number
			{
				$sender = success($sender_id); // return it as a success object
			}
			else
			{
				// Otherwise returns an error
				$sender = error('The given sender is not valid', MSG_ERR_INVALID_SENDER);
			}
		}

		return $sender;
	}

	/**
	 * Checks if the given receiver ids belong to persons in database
	 */
	private function _getReceiversByPersonId($receiver_id)
	{
		// Reset an eventually already buit query
		$this->_ci->PersonModel->resetQuery();

		// Get only this columns
		$this->_ci->PersonModel->addSelect('person_id');

		// Loads from database the person by its person_id
		$personResult = $this->_ci->PersonModel->load($receiver_id);
		if (hasData($personResult)) // if data are retrieved
		{
			return $personResult; // return them
		}
		else // otherwise an error occurred (blocking error or data not found)
		{
			return error('The given person id is not valid', MSG_ERR_INVALID_RECIPIENTS);
		}
	}

	/**
	 * Gets the receivers id that are enabled to read messages for that oe_kurzbz
	 */
	private function _getReceiversByOekurzbz($oe_kurzbz)
	{
		// Load Benutzerfunktion_model
		$this->_ci->load->model('person/Benutzerfunktion_model', 'BenutzerfunktionModel');
		// Join with table public.tbl_benutzer on field uid
		$this->_ci->BenutzerfunktionModel->addJoin('public.tbl_benutzer', 'uid');
		// Get all the valid receivers id using the oe_kurzbz
		$receivers = $this->_ci->BenutzerfunktionModel->loadWhere(
			'oe_kurzbz = '.$this->_ci->db->escape($oe_kurzbz).
			' AND funktion_kurzbz = '.$this->_ci->db->escape($this->_ci->config->item(self::CFG_OU_RECEIVERS)).
			' AND (NOW() BETWEEN COALESCE(datum_von, NOW()) AND COALESCE(datum_bis, NOW()))'
		);
		return $receivers;
	}

	/**
	 * Save a new message in DB
	 */
	private function _saveMessage($sender_id, $senderOU, $receiver_id, $receiverOU, $subject, $body, $priority, $relationmessage_id)
	{
		// Starts database transaction
		$this->_ci->db->trans_start(false);

		// Store message information in tbl_msg_message
		$messageData = array(
			'person_id' => $sender_id,
			'subject' => $subject,
			'body' => $body,
			'priority' => $priority,
			'relationmessage_id' => $relationmessage_id,
			'oe_kurzbz' => $senderOU
		);

		$saveMessageResult = $this->_ci->MessageModel->insert($messageData);
		if (isSuccess($saveMessageResult))
		{
			$messageId = getData($saveMessageResult); // Gets the message id generated by database

			// Store message information in tbl_msg_recipient
			$recipientData = array(
				'person_id' => $receiver_id,
				'message_id' => $messageId,
				'token' => generateToken(),
				'oe_kurzbz' => $receiverOU
			);

			$saveMessageResult = $this->_ci->RecipientModel->insert($recipientData);
			if (isSuccess($saveMessageResult))
			{
				// Store message information in tbl_msg_status
				$statusData = array(
					'message_id' => $messageId,
					'person_id' => $receiver_id,
					'status' => MSG_STATUS_UNREAD
				);
				$saveMessageResult = $this->_ci->MsgStatusModel->insert($statusData);
			}
		}

		$this->_ci->db->trans_complete(); // Ends database transaction

		// If the transaction failed...
		if ($this->_ci->db->trans_status() === false || isError($saveMessageResult))
		{
			$this->_ci->db->trans_rollback(); // ...then rollback
		}
		else // otherwise commit...
		{
			$this->_ci->db->trans_commit();
			$saveMessageResult = success($messageId); // ...and returns the message id
		}

		return $saveMessageResult;
	}

	/**
	 * Set the message as sent successfully by setting columns 'sent' and 'sentinfo' of table tbl_msg_recipient
	 * sent column is set with date of delivery
	 * sentinfo is set to null
	 */
	private function _setSentSuccess($message_id, $receiver_id)
	{
		return $this->_ci->RecipientModel->update(array($receiver_id, $message_id), array('sent' => 'NOW()', 'sentinfo' => null));
	}

	/**
	 * Set the message as sent with error by setting columns 'sent' and 'sentinfo' of table tbl_msg_recipient
	 * Stores the type of error in 'sentinfo' column keeping en eventual previous error
	 * sent column is set to null
	 */
	private function _setSentError($message_id, $receiver_id, $sentInfo, $prevSentInfo)
	{
		if (!isEmptyString($prevSentInfo))
		{
			$sentInfo .= self::SENT_INFO_NEWLINE.$prevSentInfo;
		}

		return $this->_ci->RecipientModel->update(array($receiver_id, $message_id), array('sent' => null, 'sentinfo' => $sentInfo));
	}

	/**
	 * Returns the notice body. Tries to use the template present in database and then falling back
	 * on the one present in filesystem. If both fail then an error is returned
	 */
	private function _getNoticeBody($dbEmailNoticeTemplate, $fsEmailNoticeTemplate, $viewMessageLink, $subject, $body)
	{
		$noticeBody = null; // pessimistic expectation

		if (!isEmptyString($dbEmailNoticeTemplate))
		{
			$noticeBody = parseText(
				$dbEmailNoticeTemplate,
				array(
					'href' => $viewMessageLink,
					'subject' => $subject,
					'body' => $body
				)
			);
		}
		else
		{
			$noticeBody = parseText(
				$fsEmailNoticeTemplate,
				array(
					'href' => $viewMessageLink,
					'subject' => $subject,
					'body' => $body
				)
			);
		}

		if (isEmptyString($noticeBody)) return error('An error occurred while generating the notice body');

		return success($noticeBody);
	}

	/**
	 * Sends a notice email that notices to a user about a new received message
	 * It gets just one message. Wrapper for _sendNotice.
	 */
	private function _sendOneNotice($message_id)
	{
		// Get the message and related data (sender, recipient, etc...)
		$messageResult = $this->_ci->RecipientModel->getMessages(
			self::EMAIL_KONTAKT_TYPE,
			$message_id
		);

		if (isError($messageResult)) return $messageResult; // if an error occured then return it
		if (!hasData($messageResult)) return error('No data found with the given message id'); // if no data found then return an error

		return $this->_sendNotice(getData($messageResult));
	}

	/**
	 * Core method to send one or more email notices for one or more messages
	 */
	private function _sendNotice($messagesResult)
	{
		// Prefix for all links that will be subsequently generated
		$prefixLink = $this->_ci->config->item(self::CFG_MESSAGE_SERVER).$this->_ci->config->item(self::CFG_MESSAGE_HTML_VIEW_URL);

		// Loads all the needed templates for HTML and plain text. Main templates from database, fallback templates from file system
		$dbEmailNoticeTemplateHTML = $this->_loadDbNoticeEmailTemplate(self::NOTICE_TEMPLATE_HTML);
		$dbEmailNoticeTemplateTXT = $this->_loadDbNoticeEmailTemplate(self::NOTICE_TEMPLATE_TXT);
		$fsEmailNoticeTemplateHTML = $this->_loadFsNoticeEmailTemplate(self::NOTICE_TEMPLATE_FALLBACK_HTML);
		$fsEmailNoticeTemplateTXT = $this->_loadFsNoticeEmailTemplate(self::NOTICE_TEMPLATE_FALLBACK_TXT);

		// Loops through all the messages to be sent
		foreach ($messagesResult as $messageData)
		{
			// Checks if this person has a valid email address where to send the notice email
			if (isEmptyString($messageData->receiver) && isEmptyString($messageData->employeecontact))
			{
				// Set in database why this email is NOT going to be send
				$sse = $this->_setSentError(
					$messageData->message_id,
					$messageData->receiver_id,
					'This person does not have an email account',
					$messageData->sentinfo
				);

				// If database error occurred then return it
				if (isError($sse)) return $sse;

				continue; // Skip the rest, continue with the next one
			}

			// Create a link to the controller to view the message using a token
			$viewMessageLink = $prefixLink.$messageData->token;

			// Generates notice email body in HTML and plain text version.
			// If an error occured during the generation then the error itself is returned
			$noticeHTMLBody = $this->_getNoticeBody(
				$dbEmailNoticeTemplateHTML, $fsEmailNoticeTemplateHTML, $viewMessageLink, $messageData->subject, $messageData->body
			);
			if (isError($noticeHTMLBody)) return $noticeHTMLBody;
			$noticeTXTBody = $this->_getNoticeBody(
				$dbEmailNoticeTemplateTXT, $fsEmailNoticeTemplateTXT, $viewMessageLink, $messageData->subject, $messageData->body
			);
			if (isError($noticeTXTBody)) return $noticeTXTBody;

			// If an employeecontact contact is present then use it, otherwise use the personal contacts
			$receiverContact = $messageData->receiver;
			if (!isEmptyString($messageData->employeecontact)) $receiverContact = $messageData->employeecontact.'@'.DOMAIN;

			// Sending email
			$sent = $this->_ci->maillib->send(
				null,
				$receiverContact,
				$messageData->subject,
				getData($noticeHTMLBody),
				null,
				null,
				null,
				getData($noticeTXTBody)
			);

			// If errors occurred while sending the email
			if (!$sent)
			{
				// Set in database why this email is NOT going to be send
				$sse = $this->_setSentError(
					$messageData->message_id,
					$messageData->receiver_id,
					'An error occurred while sending the email',
					$messageData->sentinfo
				);

				// If database error occurred then return it, otherwise return a logic error
				return isError($sse) ? $sse : error('An error occurred while sending the email');
			}
			else // success!
			{
				// Set in database that the notice email was succesfully sent
				$sss = $this->_setSentSuccess($messageData->message_id, $messageData->receiver_id);
				if (isError($sss)) return $sss; // If database error occurred then return it
			}
		}

		return success('Notice emails sent successfully');
	}

	/**
	 * Sends new message core method, may be wrapped by other methods.
	 */
	private function _sendMessage(
		$receivers, $receiversOU, $subject, $body, $sender_id, $senderOU, $relationmessage_id, $priority, $multiPartMime
	)
	{
		// Checks if sender is fine
		$sender = $this->_getSender($sender_id);
		if (!hasData($sender)) return $sender;
		// Checks if the sender and receiver organisation unit are valid
		if (($receiversOU != null && !$this->_ouExists($receiversOU)) || ($senderOU != null && !$this->_ouExists($senderOU)))
		{
			return error('The given organisation unit is not valid', MSG_ERR_INVALID_OU);
		}
		// Checks subjects
		if (isEmptyString($subject)) return error('The given subject is an empty string', MSG_ERR_INVALID_SUBJECT);
		// Checks body
		if (isEmptyString($body)) return error('The given body is an empty string', MSG_ERR_INVALID_BODY);

		$savedMessages = array(); // This array contains all the message ids of the saved messages

		// Looping on receivers
		foreach (getData($receivers) as $receiver)
		{
			// Save message in database
			$saveMessageResult = $this->_saveMessage(
				getData($sender), $senderOU, $receiver->person_id, $receiversOU, $subject, $body, $priority, $relationmessage_id
			);
			if (isSuccess($saveMessageResult)) // If successfully saved
			{
				// If the system is configured to send messages immediately
				if ($this->_ci->config->item(self::CFG_SEND_IMMEDIATELY) === true)
				{
					$savedMessages[] = getData($saveMessageResult); // store the message id of the saved message
				}
			}
			else
			{
				return $saveMessageResult; // If an error occured while saving
			}
		}

		// If the system is configured to send messages immediately
		if ($this->_ci->config->item(self::CFG_SEND_IMMEDIATELY) === true)
		{
			// Looping through saved messages ids
			foreach ($savedMessages as $message_id)
			{
				// Send message notice via email!
				$sendNotice = $this->_sendOneNotice($message_id);
				// If an error occurred then return it
				if (isError($sendNotice)) return $sendNotice;
			}
		}

		return success('Message sent successfully');
	}

	/**
	 * Checks if the given organisation unit exists in database
	 */
	private function _ouExists($ou)
	{
		// Reset an eventually already buit query
		$this->_ci->OrganisationseinheitModel->resetQuery();
		// Get only this columns
		$this->_ci->OrganisationseinheitModel->addSelect('oe_kurzbz');
		// Retrieves the given organisation unit from database
		$ouResults = $this->_ci->OrganisationseinheitModel->loadWhere(array('oe_kurzbz' => $ou));

		return hasData($ouResults);
	}

	/**
	 * Loads a the specified template from database
	 * Returns null if not found or on failure
	 */
	private function _loadDbNoticeEmailTemplate($dbTemplateName)
	{
		$emailNoticeTemplate = null;

		$vorlageResult = $this->_ci->vorlagelib->loadVorlagetext($dbTemplateName);

		if (hasData($vorlageResult))
		{
			$emailNoticeTemplate = getData($vorlageResult)[0]->text;
		}

		return $emailNoticeTemplate;
	}

	/**
	 * Loads a the specified template from database
	 */
	private function _loadFsNoticeEmailTemplate($fsTemplateName)
	{
		return $this->_ci->load->view($fsTemplateName, null, true);
	}
}
