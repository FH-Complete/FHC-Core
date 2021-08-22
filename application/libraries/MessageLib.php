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
	const CFG_OU_RECEIVERS_NO_NOTICE = 'ou_receivers_no_notice';
	const CFG_OU_RECEIVERS_PRIVATE = 'ou_receivers_private';
	const CFG_OU_FUNCTION_WHITELIST = 'ou_function_whitelist';
	const CFG_REDIRECT_VIEW_MESSAGE_URL = 'redirect_view_message_url';

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
		$receiver = new stdClass();
		$receiver->person_id = $this->_ci->config->item(self::CFG_SYSTEM_PERSON_ID);
		$receivers = success(array($receiver));

		// Send the message and return the result
		return $this->_sendMessage($receivers, $receiversOU, $subject, $body, $sender_id, $senderOU, $relationmessage_id, $priority, $multiPartMime);
	}

	//------------------------------------------------------------------------------------------------------------------
	// Public methods called by a job

	/**
	 * Gets all messages for which notice emails are still not sent from DB and sends for each of them the notice email
	 * Wrapper for _sendNoticeEmail.
	 */
	public function sendAllEmailNotices($since, $numberToSent, $numberPerTimeRange, $emailTimeRange, $emailFromSystem)
	{
		// Overrides MailLib configs with the given parameters
		$this->_ci->maillib->overrideConfigs($numberToSent, $numberPerTimeRange, $emailTimeRange, $emailFromSystem);

		// Retrieves a certain amount of NOT sent messages
		$messagesResult = $this->_ci->RecipientModel->getNotSentMessages(
			$this->_ci->maillib->getEmailNumberToSent(),
			$since
		);

		if (isError($messagesResult) || !hasData($messagesResult)) return $messagesResult;

		// Collects all the message ids in an array
		$messageIds = array();
		foreach (getData($messagesResult) as $message)
		{
			$messageIds[] = $message->message_id;
		}

		// Send'em all
		return $this->_sendNoticeEmails($messageIds);
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
	 * Retrieves message vars from view vw_msg_vars
	 */
	public function getMessageVarsPrestudent()
	{
		// Retrieves message vars from view vw_msg_vars
		$messageVars = $this->_ci->MessageModel->getMessageVars();
		if (isSuccess($messageVars)) // if everything is ok
		{
			$variablesArray = array();
			$tmpVariablesArray = getData($messageVars);

			// Starts from 1 to skip the first element which is person_id
			for ($i = 1; $i < count($tmpVariablesArray); $i++)
			{
				$variablesArray['{'.str_replace(' ', '_', strtolower($tmpVariablesArray[$i])).'}'] = $tmpVariablesArray[$i];
			}

			return success($variablesArray);
		}

		return $messageVars; // otherwise returns the error
	}
	
	/**
	 * Retrieves message vars of the logged in user from view vw_msg_vars_user
	 */
	public function getMessageVarsLoggedInUser()
	{
		// Retrieves message vars from view vw_msg_vars
		$messageVars = $this->_ci->MessageModel->getMsgVarsLoggedInUser();
		if (isSuccess($messageVars)) // if everything is ok
		{
			$variablesArray = array();
			$tmpVariablesArray = getData($messageVars);

			// Starts from 1 to skip the first element which is uid
			for ($i = 1; $i < count($tmpVariablesArray); $i++)
			{
				$variablesArray['{'.str_replace(' ', '_', strtolower($tmpVariablesArray[$i])).'}']
					= strtoupper($tmpVariablesArray[$i]);
			}

			return success($variablesArray);
		}
		
		return $messageVars; // otherwise returns the error
	}

	/**
	 * Retrieves organisation units for each role that a user plays inside that organisation unit
	 */
	public function getOeKurzbz($sender_id)
	{
		$this->_ci->load->model('person/Benutzerfunktion_model', 'BenutzerfunktionModel');

		// Retrieves organisation units for a user from database
 		$benutzer = $this->_ci->BenutzerfunktionModel->getActiveFunctionsByPersonId($sender_id);
 		if (isSuccess($benutzer)) // if everything is ok
 		{
			$ouArray = array();

			// Copies organisation units in $ouArray array
 			foreach (getData($benutzer) as $val)
			{
				// If the function is in the white list then get the organisation unit
				if (in_array($val->funktion_kurzbz, $this->_ci->config->item(self::CFG_OU_FUNCTION_WHITELIST)))
				{
					$ouArray[] = $val->oe_kurzbz;
				}
			}

			return success($ouArray);
 		}

		return $benutzer; // otherwise returns the error
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
				$uid = null;
				if (!isEmptyString($messageTokenResult[0]->uid))
				{
					$uid = $messageTokenResult[0]->uid;
				}

				$statusData = array(
					'message_id' => getData($messageTokenResult)[0]->message_id,
					'person_id' => getData($messageTokenResult)[0]->receiver_id,
					'status' => MSG_STATUS_READ,
					'insertvon' => $uid
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
		$this->_ci->load->model('person/Person_model', 'PersonModel');

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
	 * Save a new message in DB
	 */
	private function _saveMessage($sender_id, $senderOU, $receiver_id, $receiverOU, $subject, $body, $priority, $relationmessage_id)
	{
		// Starts database transaction
		$this->_ci->db->trans_start(false);

		$this->_ci->load->model('person/Benutzer_model', 'BenutzerModel');

		$uid = null;
		$benutzerDB = $this->_ci->BenutzerModel->loadWhere(array('person_id' => $sender_id));
		if (hasData($benutzerDB))
		{
			$uid = getData($benutzerDB)[0]->uid;
		}

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
					'status' => MSG_STATUS_UNREAD,
					'insertvon' => $uid
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
	private function _updatedRecipientNoticeEmailInfo($message_id, $receiver_id, $sentInfo, $prevSentInfo)
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
	 * Sends notice emails to the recipient of a message
	 */
	private function _sendNoticeEmails($messageIds)
	{
		// Retrieves the messages information using the given message ids array
		$messagesResult = $this->_ci->RecipientModel->getMessagesById($messageIds);
		if (isError($messagesResult)) return $messageRecipientResult; // if an error occured then return it
		if (!hasData($messagesResult)) return error('No data found with the given message ids'); // if no data found then return an error

		$messages = array(); // all the worked messages will be added here

		// Loops through $messagesResult and stores data about a message in $message
		foreach (getData($messagesResult) as $message)
		{
			// If the recipient organisation unit is in the list of organisation units that do not receive notice emails
			if (array_search($message->receiver_ou, $this->_ci->config->item(self::CFG_OU_RECEIVERS_NO_NOTICE)))
			{
				// Then there is no need to send a notice email to this organisation unit
			}
			else // otherwise tries to retrieve the right email contact for the message recipient
			{
				$message->receiverContact = null; // by default set the recipient contact as null

				// If the message was sent to an organisation unit then retrives degree program email
				if ($message->receiver_id == $this->_ci->config->item(self::CFG_SYSTEM_PERSON_ID) && !isEmptyString($message->receiver_ou))
				{
					$this->_ci->load->model('organisation/Studiengang_model', 'StudiengangModel');

					$studiengangResult = $this->_ci->StudiengangModel->loadWhere(array('oe_kurzbz' => $message->receiver_ou));
					if (isError($studiengangResult)) return $studiengangResult; // if an error occured then return it

					// Use the degree program email
					if (hasData($studiengangResult)) $message->receiverContact = getData($studiengangResult)[0]->email;
				}
				// If message was sent from FAS and NOT from infocenter
				elseif (!isEmptyString($message->sender_ou)
					&& !array_search($message->sender_ou, $this->_ci->config->item(self::CFG_OU_RECEIVERS_NO_NOTICE)))
				{
					// If the recipient organisation unit is NOT in the list of organisation units that sent only to private emails
					if (array_search($message->receiver_ou, $this->_ci->config->item(self::CFG_OU_RECEIVERS_PRIVATE)) === false)
					{
						$this->_ci->load->model('person/Benutzer_model', 'BenutzerModel');

						// And the receiver has an active account for the given organisation unit
						$benutzerResult = $this->_ci->BenutzerModel->getActiveUserByPersonIdAndOrganisationUnit(
							$message->receiver_id,
							$message->sender_ou
						);

						if (isError($benutzerResult)) return $benutzerResult; // if an error occured then return it

						// If an active user for the given organization unit was found
						if (hasData($benutzerResult))
						{
							// Checks if the user was NOT created in the last 24 hours
							if (getData($benutzerResult)[0]->insertamum < date('Y-m-d H:i:s', strtotime('-1 day')))
							{
								// Use the uid + domain email
								$message->receiverContact = getData($benutzerResult)[0]->uid .'@'.DOMAIN;
							}
							// otherwise do NOT use the internal email account
						}
					}

					// Otherwise try with the private email
					if (isEmptyString($message->receiverContact))
					{
						$privateEmailResult = $this->_getPrivateEmail($message->receiver_id);
						if (isError($privateEmailResult)) return $privateEmailResult; // if an error occured then return it

						// Use the private email
						if (hasData($privateEmailResult)) $message->receiverContact = getData($privateEmailResult);
					}
				}
				else // the recipient is a person
				{
					$this->_ci->load->model('person/Benutzer_model', 'BenutzerModel');

					// The recipient has an active account
					$benutzerResult = $this->_ci->BenutzerModel->loadWhere(array('person_id' => $message->receiver_id, 'aktiv' => true));
					if (isError($benutzerResult)) return $benutzerResult; // if an error occured then return it

					// If the user is present and active
					if (hasData($benutzerResult))
					{
						$this->_ci->load->model('ressource/Mitarbeiter_model', 'MitarbeiterModel');

						$mitarbeiterResult = $this->_ci->MitarbeiterModel->loadWhere(array('mitarbeiter_uid' => getData($benutzerResult)[0]->uid));
						if (isError($mitarbeiterResult)) return $mitarbeiterResult; // if an error occured then return it

						// If employee
						if (hasData($mitarbeiterResult))
						{
							$message->receiverContact = getData($benutzerResult)[0]->uid .'@'.DOMAIN; // Use the uid + domain email
						}
						else // ...otherwise...
						{
							$this->_ci->load->model('crm/Prestudent_model', 'PrestudentModel');

							// ...try to get all the prestudent for this receiver
							$prestudentResults = $this->_ci->PrestudentModel->getOrganisationunitsByPersonId($message->receiver_id);
							if (isError($prestudentResults)) return $prestudentResults; // if an error occured then return it

							// If there are presetudent
							if (hasData($prestudentResults))
							{
								$privateOnly = false;
								$organisationUnits = getData($prestudentResults);

								// Look if any of the organization units of this prestudent are in the list of the
								// organization units that will not send the notice email to the internal account
								foreach ($organisationUnits as $organisationUnit)
								{
									// If the recipient organisation unit is NOT in the list of organisation units that sent only to private emails
									// NOTE: done in this way because it is easyer to check the result of array_search
									if (array_search($organisationUnit, $this->_ci->config->item(self::CFG_OU_RECEIVERS_PRIVATE)) === false)
									{
										// NOP
									}
									else // otherwise If the recipient organisation unit is the list of organisation units that sent only to private emails
									{
										$privateOnly = true;
										break;
									}
								}

								// If the recipient prestudent organization unit is not in in the list of the
								// organization units that will not send the notice email to the internal account
								if ($privateOnly)
								{
									// Then use the private email
									$privateEmailResult = $this->_getPrivateEmail($message->receiver_id);
									if (isError($privateEmailResult)) return $privateEmailResult; // if an error occured then return it

									if (hasData($privateEmailResult)) $message->receiverContact = getData($privateEmailResult);
								}
								else // Use the most recent UID + domain
								{
									$this->_ci->BenutzerModel->resetQuery();

									$this->_ci->BenutzerModel->addOrder('updateamum', 'DESC');
									$this->_ci->BenutzerModel->addOrder('insertamum', 'DESC');

									$benutzerResult = $this->_ci->BenutzerModel->loadWhere(
										array(
											'person_id' => $message->receiver_id
										)
									);
									if (isError($benutzerResult)) return $benutzerResult; // if an error occured then return it

									// If an active user for the given organization unit was found
									if (hasData($benutzerResult))
									{
										// For each benutzer found for this person
										foreach (getData($benutzerResult) as $benutzer)
										{
											// Checks if the user was NOT created in the last 24 hours
											if (getData($benutzerResult)[0]->insertamum < date('Y-m-d H:i:s', strtotime('-1 day')))
											{
												// Use the uid + domain as email address
												$message->receiverContact = getData($benutzerResult)[0]->uid .'@'.DOMAIN;
											}
										}
									}

									// Otherwise try with the private email
									if (isEmptyString($message->receiverContact))
									{
										// Then use the private email
										$privateEmailResult = $this->_getPrivateEmail($message->receiver_id);
										if (isError($privateEmailResult)) return $privateEmailResult; // if an error occured then return it

										if (hasData($privateEmailResult)) $message->receiverContact = getData($privateEmailResult);
									}
								}
							}
						}
					}
					else // otherwise use the private email
					{
						$privateEmailResult = $this->_getPrivateEmail($message->receiver_id);
						if (isError($privateEmailResult)) return $privateEmailResult; // if an error occured then return it

						// Use the private email
						if (hasData($privateEmailResult)) $message->receiverContact = getData($privateEmailResult);
					}
				}
			}

			$messages[] = $message; // add new message to be noticed into the messages array
		}

		return $this->_sendNoticeEmail($messages);
	}

	/**
	 *
	 */
	private function _getPrivateEmail($person_id)
	{
		$this->_ci->load->model('person/Kontakt_model', 'KontaktModel');

		$getPrivateEmail = $this->_ci->KontaktModel->getContactByPersonId($person_id, self::EMAIL_KONTAKT_TYPE);

		if (hasData($getPrivateEmail)) return success(getData($getPrivateEmail)[0]->kontakt);
		else return success();

		return $getPrivateEmail;
	}

	/**
	 * Core method to send one or more email notices for one or more messages
	 */
	private function _sendNoticeEmail($messages)
	{
		// Prefix for all links that will be subsequently generated
		$prefixLink = $this->_ci->config->item(self::CFG_MESSAGE_SERVER).$this->_ci->config->item(self::CFG_MESSAGE_HTML_VIEW_URL);

		// Loads all the needed templates for HTML and plain text. Main templates from database, fallback templates from file system
		$dbEmailNoticeTemplateHTML = $this->_loadDbNoticeEmailTemplate(self::NOTICE_TEMPLATE_HTML);
		$dbEmailNoticeTemplateTXT = $this->_loadDbNoticeEmailTemplate(self::NOTICE_TEMPLATE_TXT);
		$fsEmailNoticeTemplateHTML = $this->_loadFsNoticeEmailTemplate(self::NOTICE_TEMPLATE_FALLBACK_HTML);
		$fsEmailNoticeTemplateTXT = $this->_loadFsNoticeEmailTemplate(self::NOTICE_TEMPLATE_FALLBACK_TXT);

		// Loops through all the messages to be sent
		foreach ($messages as $messageData)
		{
			// Checks if this person has a valid email address where to send the notice email
			if (!isEmptyString($messageData->receiverContact))
			{
				// Create a link to the controller to view the message using a token
				$viewMessageLink = $prefixLink.$messageData->token;

				// Generates notice email body in HTML format
				$noticeHTMLBody = $this->_getNoticeBody(
					$dbEmailNoticeTemplateHTML, $fsEmailNoticeTemplateHTML, $viewMessageLink, $messageData->subject, $messageData->body
				);
				// If an error occured during the generation then the error itself is returned
				if (isError($noticeHTMLBody)) return $noticeHTMLBody;

				// Generates notice email body in plain text format
				$noticeTXTBody = $this->_getNoticeBody(
					$dbEmailNoticeTemplateTXT, $fsEmailNoticeTemplateTXT, $viewMessageLink, $messageData->subject, $messageData->body
				);
				// If an error occured during the generation then the error itself is returned
				if (isError($noticeTXTBody)) return $noticeTXTBody;

				// Sending email
				$sent = $this->_ci->maillib->send(
					null,
					$messageData->receiverContact,
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
					$sse = $this->_updatedRecipientNoticeEmailInfo(
						$messageData->message_id,
						$messageData->receiver_id,
						'An error occurred while sending the notice email', // current info
						$messageData->sentinfo  // previous info
					);

					// If database error occurred then return it, otherwise return a logic error
					return isError($sse) ? $sse : error('An error occurred while updating the recipient notice email info');
				}
				else // success!
				{
					// Set in database that the notice email was succesfully sent
					$sss = $this->_setSentSuccess($messageData->message_id, $messageData->receiver_id);
					if (isError($sss)) return $sss; // If database error occurred then return it
				}
			}
			else // Because was not possible to find a valid contact
			{
				$reason = 'Was not possible to find a valid contact for this user'; // default reason

				// In case that the organisation unit does not receive any email notices
				if (!isEmptyString($messageData->receiver_ou)) $reason = 'This organization unit does not receive email notices';

				// In case that a degree program sent a message to a user without a valid contact or UID
				if (!isEmptyString($messageData->sender_ou)) $reason = 'Sent from a degree program to a user that does not have a valid UID or a valid contact';

				// Set in database why this email is NOT going to be send
				$sse = $this->_updatedRecipientNoticeEmailInfo(
					$messageData->message_id,
					$messageData->receiver_id,
					$reason, // current info
					$messageData->sentinfo // previous info
				);

				// If database error occurred then return it
				if (isError($sse)) return $sse;
			}
		}

		return success('Notice emails sent successfully');
	}

	/**
	 * Sends new message core method, may be wrapped by other methods.
	 * If success then returns an array of successfully saved message ids
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
				$savedMessages[] = getData($saveMessageResult); // store the message id of the saved message
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
				$sendNotice = $this->_sendNoticeEmails(array($message_id));

				// If an error occurred then return it
				if (isError($sendNotice)) return $sendNotice;
			}
		}

		return success($savedMessages);
	}

	/**
	 * Checks if the given organisation unit exists in database
	 */
	private function _ouExists($ou)
	{
		$this->_ci->load->model('organisation/Organisationseinheit_model', 'OrganisationseinheitModel');

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
