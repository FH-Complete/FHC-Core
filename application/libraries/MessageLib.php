<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

/**
* Messaging Library for FH-Complete
*/
class MessageLib
{
	const MSG_INDX_PREFIX = 'message_';
	
	public function __construct()
	{
		// Get code igniter instance
        $this->ci =& get_instance();
		
		// Loads message configuration
		$this->ci->config->load('message');
		
		// CI Parser library
		$this->ci->load->library('parser');
		// Loads LogLib
		$this->ci->load->library('LogLib');
		// Loads VorlageLib
		$this->ci->load->library('VorlageLib');
		// Loads Mail library
		$this->ci->load->library('MailLib');
		
		// Loading models
		$this->ci->load->model('system/Message_model', 'MessageModel');
		$this->ci->load->model('system/MsgStatus_model', 'MsgStatusModel');
		$this->ci->load->model('system/Recipient_model', 'RecipientModel');
		$this->ci->load->model('system/Attachment_model', 'AttachmentModel');
		
		// Loads fhc helper
		$this->ci->load->helper('fhc');
		// Loads helper message to manage returning messages
		$this->ci->load->helper('message');
		
		// Loads phrases
        $this->ci->lang->load('message');
    }
    
    /**
     * getMessage() - returns the spicified received message for a specified person
     *
     * @param	string	$msg_id		REQUIRED
     * @param	string	$person_id	REQUIRED
     * @return	object
     */
    public function getMessage($msg_id, $person_id)
    {
        if (empty($msg_id))
        	return $this->_error('', MSG_ERR_INVALID_MSG_ID);
		if (empty($person_id))
        	return $this->_error('', MSG_ERR_INVALID_RECIPIENTS);
		
		$msg = $this->ci->RecipientModel->getMessage($msg_id, $person_id);
		
        return $msg;
    }
    
	/**
     * getMessagesByUID() - will return all messages, including the latest status for specified user. It don´t returns Attachments.
     *
     * @param   string  $uid   REQUIRED
     * @return  array
     */
    public function getMessagesByUID($uid, $all = false)
    {
        if (empty($uid))
        	return $this->_error('', MSG_ERR_INVALID_MSG_ID);
		
		$msg = $this->ci->RecipientModel->getMessagesByUID($uid, $all);

        return $msg;
    }
	
	/**
     * getMessagesByPerson() - will return all messages, including the latest status for specified user. It don´t returns Attachments.
     *
     * @param   bigint  $person_id   REQUIRED
     * @return  array
     */
    public function getMessagesByPerson($person_id, $all = false)
    {
        if (empty($person_id))
        	return $this->_error('', MSG_ERR_INVALID_MSG_ID);
		
		$msg = $this->ci->RecipientModel->getMessagesByPerson($person_id, $all);
		
        return $msg;
    }
	
	/**
     * getSentMessagesByPerson() - Get all sent messages from a person identified by person_id
     *
     * @param   bigint  $person_id   REQUIRED
     * @return  array
     */
    public function getSentMessagesByPerson($person_id, $all = false)
    {
        if (empty($person_id))
        	return $this->_error('', MSG_ERR_INVALID_MSG_ID);
		
		$msg = $this->ci->MessageModel->getMessagesByPerson($person_id, $all);
		
        return $msg;
    }
	
	/**
     * getMessageByToken
     *
     * @param	token string
     * @return	array
     */
    public function getMessageByToken($token)
    {
        if (empty($token))
        	return $this->_error('', MSG_ERR_INVALID_TOKEN);
		
		$result = $this->ci->RecipientModel->getMessageByToken($token);
		if (is_object($result) && $result->error == EXIT_SUCCESS && is_array($result->retval) && count($result->retval) > 0)
		{
			// Searches for a status that is different from unread
			$found = -1;
			for ($i = 0; $i < count($result->retval); $i++)
			{
				if ($result->retval[$i]->status > MSG_STATUS_UNREAD)
				{
					$found = $i;
					break;
				}
			}
			
			// If not found then insert the read status
			if ($found == -1)
			{
				$statusKey = array(
					'message_id' => $result->retval[0]->message_id,
					'person_id' => $result->retval[0]->receiver_id,
					'status' => MSG_STATUS_READ
				);
				
				$resultIns = $this->ci->MsgStatusModel->insert($statusKey);
				// If an error occured while writing on data base, then return it
				if ($resultIns->error == EXIT_ERROR)
				{
					$result = $resultIns;
				}
			}
		}
		
        return $result;
    }
	
    /**
     * updateMessageStatus() - will change status on message for particular user
     *
     * @param   integer  $msg_id     REQUIRED
     * @param   integer  $user_id    REQUIRED
     * @param   integer  $status_id  REQUIRED - should come from config/message.php list of constants
     * @return  array
     */
    public function updateMessageStatus($message_id, $person_id, $status)
    {
        if (empty($message_id))
        {
            return $this->_error('', MSG_ERR_INVALID_MSG_ID);
        }
		
        if (empty($person_id))
        {
            return $this->_error('', MSG_ERR_INVALID_USER_ID);
        }
		
		// Not use empty otherwise if status is 0 it returns an error
        if (!isset($status))
        {
            return $this->_error('', MSG_ERR_INVALID_STATUS_ID);
        }
		
		// Searches if the status is already present
		$result = $this->ci->MsgStatusModel->load(array($message_id, $person_id, $status));
		if (is_object($result) && $result->error == EXIT_SUCCESS && is_array($result->retval) && count($result->retval) > 0)
		{
			// status already present
		}
		else
		{
			// Insert the new status
			$statusKey = array(
				'message_id' => $message_id,
				'person_id' => $person_id,
				'status' => $status
			);
				
			$result = $this->ci->MsgStatusModel->insert($statusKey);
		}
		
		return $result;
    }
	
    /**
     * sendMessage() - sends new internal message. This function will create a new thread
     *
     */
    public function sendMessage($sender_id, $receiver_id, $subject, $body, $priority = PRIORITY_NORMAL, $relationmessage_id = null, $oe_kurzbz = null)
    {
        if (!is_numeric($sender_id))
        {
			$sender_id = $this->ci->config->item('system_person_id');
        }
		
		$receivers = $this->_getReceivers($receiver_id, $oe_kurzbz);
		
		// If everything went ok
		if (is_object($receivers) && $receivers->error == EXIT_SUCCESS && is_array($receivers->retval))
		{
			// If no receivers were found for this organization unit
			if (count($receivers->retval) == 0)
			{
				$result = $this->_error($receivers->retval, MSG_ERR_OU_CONTACTS_NOT_FOUND);
			}
			
			// Looping on receivers
			for ($i = 0; $i < count($receivers->retval); $i++)
			{
				$receiver_id = $receivers->retval[$i]->person_id;
				
				// Checks if the receiver exists
				if ($this->_checkReceiverId($receiver_id))
				{
					// If the text and the subject of the template are not empty
					if (!empty($subject) && !empty($body))
					{
						$this->ci->db->trans_start(false);
						// Save Message
						$msgData = array(
							'person_id' => $sender_id,
							'subject' => $subject,
							'body' => $body,
							'priority' => PRIORITY_NORMAL,
							'relationmessage_id' => $relationmessage_id,
							'oe_kurzbz' => $oe_kurzbz
						);
						$result = $this->ci->MessageModel->insert($msgData);
						if (is_object($result) && $result->error == EXIT_SUCCESS)
						{
							// Link the message with the receiver
							$msg_id = $result->retval;
							$recipientData = array(
								'person_id' => $receiver_id,
								'message_id' => $msg_id,
								'token' => generateToken()
							);
							$result = $this->ci->RecipientModel->insert($recipientData);
							if (is_object($result) && $result->error == EXIT_SUCCESS)
							{
								// Save message status
								$statusData = array(
									'message_id' => $msg_id,
									'person_id' => $receiver_id,
									'status' => MSG_STATUS_UNREAD
								);
								$result = $this->ci->MsgStatusModel->insert($statusData);
								
								// If no errors were occurred
								/**
								* TODO: different config item???
								*/
								/*if (is_object($result) && $result->error == EXIT_SUCCESS)
								{
									// If the system is configured to send messages immediately
									if ($this->ci->config->item('send_immediately') === true)
									{
										// Send message by email!
										$resultSendEmail = $this->sendOne($msg_id, $subject, $body);
									}
								}*/
							}
						}
						
						$this->ci->db->trans_complete();
						
						if ($this->ci->db->trans_status() === false || (is_object($result) && $result->error != EXIT_SUCCESS))
						{
							$this->ci->db->trans_rollback();
							$result = $this->_error($result->msg, EXIT_ERROR);
							break;
						}
						else
						{
							$this->ci->db->trans_commit();
							$result = $this->_success($msg_id);
						}
					}
					else
					{
						if (!empty($subject))
						{
							$result = $this->_error('', MSG_ERR_SUBJECT_EMPTY);
							break;
						}
						else if (!empty($body))
						{
							$result = $this->_error('', MSG_ERR_BODY_EMPTY);
							break;
						}
					}
				}
				else
				{
					$result = $this->_error('', MSG_ERR_INVALID_RECEIVER_ID);
					break;
				}
			}
		}
		// If there was some errors then copy them into the returning variable
		else
		{
			$result = $receivers;
		}
		
		return $result;
    }
    
	/**
     * sendMessageVorlage() - sends new internal message using a template
     *
     * @param   integer  $sender_id   REQUIRED
     * @param   mixed    $recipients  REQUIRED - a single integer or an array of integers, representing user_ids
     * @param   string   $subject
     * @param   string   $body
     * @param   integer  $priority
     * @return  array
     */
    public function sendMessageVorlage($sender_id, $receiver_id, $vorlage_kurzbz, $oe_kurzbz, $data, $relationmessage_id = null, $orgform_kurzbz = null)
    {
        if (!is_numeric($sender_id))
        {
			$sender_id = $this->ci->config->item('system_person_id');
        }
		
		$receivers = $this->_getReceivers($receiver_id, $oe_kurzbz);
		
		// If everything went ok
		if (is_object($receivers) && $receivers->error == EXIT_SUCCESS && is_array($receivers->retval))
		{
			// If no receivers were found for this organization unit
			if (count($receivers->retval) == 0)
			{
				$result = $this->_error($receivers->retval, MSG_ERR_OU_CONTACTS_NOT_FOUND);
			}
			else
			{
				// Load reveiver data to get its relative language
				$this->ci->load->model('person/Person_model', 'PersonModel');
			}
			
			// Looping on receivers
			for ($i = 0; $i < count($receivers->retval); $i++)
			{
				$receiver_id = $receivers->retval[$i]->person_id;
				
				$result = $this->ci->PersonModel->load($receiver_id);
				if (is_object($result) && $result->error == EXIT_SUCCESS && is_array($result->retval) && count($result->retval) > 0)
				{
					// Set the language with the global value
					$sprache = DEFAULT_LEHREINHEIT_SPRACHE;
					// If the receiver has a prefered language use this
					if (isset($result->retval[0]->sprache) && $result->retval[0]->sprache != '')
					{
						$sprache = $result->retval[0]->sprache;
					}
					
					// Loads template data
					$result = $this->ci->vorlagelib->loadVorlagetext($vorlage_kurzbz, $oe_kurzbz, $orgform_kurzbz, $sprache);
					if (is_object($result) && $result->error == EXIT_SUCCESS)
					{
						// If the text and the subject of the template are not empty
						if (is_array($result->retval) && count($result->retval) > 0 &&
							!empty($result->retval[0]->text) && !empty($result->retval[0]->subject))
						{
							// Parses template text
							$parsedText = $this->ci->vorlagelib->parseVorlagetext($result->retval[0]->text, $data);
							$subject = $result->retval[0]->subject;

							$this->ci->db->trans_start(false);
							// Save Message
							$msgData = array(
								'person_id' => $sender_id,
								'subject' => $subject,
								'body' => $parsedText,
								'priority' => PRIORITY_NORMAL,
								'relationmessage_id' => $relationmessage_id,
								'oe_kurzbz' => $oe_kurzbz
							);
							$result = $this->ci->MessageModel->insert($msgData);
							if (is_object($result) && $result->error == EXIT_SUCCESS)
							{
								// Link the message with the receiver
								$msg_id = $result->retval;
								$recipientData = array(
									'person_id' => $receiver_id,
									'message_id' => $msg_id,
									'token' => generateToken()
								);
								$result = $this->ci->RecipientModel->insert($recipientData);
								if (is_object($result) && $result->error == EXIT_SUCCESS)
								{
									// Save message status
									$statusData = array(
										'message_id' => $msg_id,
										'person_id' => $receiver_id,
										'status' => MSG_STATUS_UNREAD
									);
									$result = $this->ci->MsgStatusModel->insert($statusData);
									
									// If no errors were occurred
									if (is_object($result) && $result->error == EXIT_SUCCESS)
									{
										// If the system is configured to send messages immediately
										if ($this->ci->config->item('send_immediately') === true)
										{
											// Send message by email!
											$resultSendEmail = $this->sendOne($msg_id, $subject, $parsedText);
										}
									}
								}
							}

							$this->ci->db->trans_complete();

							if ($this->ci->db->trans_status() === false || (is_object($result) && $result->error != EXIT_SUCCESS))
							{
								$this->ci->db->trans_rollback();
								$result = $this->_error($result->msg, EXIT_ERROR);
								break;
							}
							else
							{
								$this->ci->db->trans_commit();
								$result = $this->_success($msg_id);
							}
						}
						else
						{
							// Better message error
							if (!is_array($result->retval) || (is_array($result->retval) && count($result->retval) == 0))
							{
								$result = $this->_error('', MSG_ERR_TEMPLATE_NOT_FOUND);
								break;
							}
							else if (is_array($result->retval) && count($result->retval) > 0)
							{
								if (is_null($result->retval[0]->oe_kurzbz))
								{
									$result = $this->_error('', MSG_ERR_TEMPLATE_NOT_FOUND);
									break;
								}
								else if (empty($result->retval[0]->text))
								{
									$result = $this->_error('', MSG_ERR_INVALID_TEMPLATE);
									break;
								}
								else if (empty($result->retval[0]->subject))
								{
									$result = $this->_error('', MSG_ERR_INVALID_TEMPLATE);
									break;
								}
							}
						}
					}
					else
					{
						$result = $this->_error($result->retval, EXIT_ERROR);
						break;
					}
				}
				else
				{
					$result = $this->_error('', MSG_ERR_INVALID_RECEIVER_ID);
					break;
				}
			}
		}
		// If there was some errors then copy them into the returning variable
		else
		{
			$result = $receivers;
		}
		
		return $result;
    }
	
	/**
	 * Gets all the messages from DB and sends them via email
	 */
	public function sendAll($numberToSent = null, $numberPerTimeRange = null, $email_time_range = null, $email_from_system = null)
	{
		$sent = true; // optimistic expectation
		
		// Gets standard configs
		$cfg = $this->ci->maillib->getConfigs();
		$cfg->email_number_to_sent = $numberToSent;
		$cfg->email_number_per_time_range = $numberPerTimeRange;
		$cfg->email_time_range = $email_time_range;
		$cfg->email_from_system = $email_from_system;
		
		// Overrides configs with the parameters
		$this->ci->maillib->overrideConfigs($cfg);
		
		// Gets a number ($this->ci->maillib->getMaxEmailToSent()) of unsent messages from DB
		// having EMAIL_KONTAKT_TYPE as relative contact type
		$result = $this->ci->RecipientModel->getMessages(
			EMAIL_KONTAKT_TYPE,
			null,
			$this->ci->maillib->getConfigs()->email_number_to_sent
		);
		// Checks if errors were occurred
		if (is_object($result) && $result->error == EXIT_SUCCESS)
		{
			// If data are present
			if (is_array($result->retval) && count($result->retval) > 0)
			{
				// Iterating through the result set, if no errors occurred in the previous iteration
				for ($i = 0; $i < count($result->retval) && $sent; $i++)
				{
					// If the person has an email account
					if (!is_null($result->retval[$i]->receiver) && $result->retval[$i]->receiver != '')
					{
						$href = APP_ROOT . $this->ci->config->item('redirect_view_message_url') . $result->retval[$i]->token;
						// Using a template for the html email body
						$body = $this->ci->parser->parse(
							'templates/mailHTML',
							array(
								'src' => APP_ROOT . $this->ci->config->item('message_html_view_url') . $result->retval[$i]->token,
								'href' => $href
							),
							true
						);
						if (is_null($body) || $body == '')
						{
							$this->ci->loglib->logError('Error while parsing the mail template');
						}
						
						// Using a template for the plain text email body
						$altBody = $this->ci->parser->parse(
							'templates/mailTXT',
							array(
								'href' => $href
							),
							true
						);
						if (is_null($altBody) || $altBody == '')
						{
							$this->ci->loglib->logError('Error while parsing the mail template');
						}
						
						// If the sender kontakt does not exist, then use system
						$sender = $this->ci->maillib->getConfigs()->email_from_system;
						if (!is_null($result->retval[0]->sender) && $result->retval[0]->sender != '')
						{
							$sender = $result->retval[0]->sender;
						}
						// Sending email
						$sent = $this->ci->maillib->send(
							$sender,
							$result->retval[$i]->receiver,
							$result->retval[$i]->subject,
							$body,
							null,
							null,
							null,
							$altBody
						);
						// If errors were occurred while sending the email
						if (!$sent)
						{
							$this->ci->loglib->logError('Error while sending an email');
							// Writing errors in tbl_message_status
							$sme = $this->setMessageError(
								$result->retval[$i]->message_id,
								$result->retval[$i]->receiver_id,
								'Error while sending an email',
								$result->retval[$i]->sentinfo
							);
							if (!$sme)
							{
								$this->ci->loglib->logError('Error while updating DB');
							}
						}
						else
						{
							// Setting the message as sent in DB
							$sent = $this->setMessageSent($result->retval[$i]->message_id, $result->retval[$i]->receiver_id);
							// If some errors occurred
							if (!$sent)
							{
								$this->ci->loglib->logError('Error while updating DB');
							}
						}
					}
					else
					{
						$this->ci->loglib->logError('This person does not have an email account');
						// Writing errors in tbl_message_status
						$sme = $this->setMessageError(
								$result->retval[$i]->message_id,
								$result->retval[$i]->receiver_id,
								'This person does not have an email account',
								$result->retval[$i]->sentinfo
						);
						if (!$sme)
						{
							$this->ci->loglib->logError('Error while updating DB');
						}
						$sent = true; // Non blocking error
					}
				}
			}
			else
			{
				$this->ci->loglib->logInfo('There are no email to be sent');
				$sent = false;
			}
		}
		else
		{
			$this->ci->loglib->logError('Something went wrong while getting data from DB');
			$sent = false;
		}
		
		return $sent;
	}
	
	/**
	 * Gets one message from DB and sends it via email
	 */
	public function sendOne($message_id, $subject = null, $body = null)
	{
		$sent = true; // optimistic expectation
		
		// Get a specific message from DB having EMAIL_KONTAKT_TYPE as relative contact type
		$result = $this->ci->RecipientModel->getMessages(
				EMAIL_KONTAKT_TYPE,
				null,
				null,
				$message_id
		);
		// Checks if errors were occurred
		if (is_object($result) && $result->error == EXIT_SUCCESS)
		{
			// If data are present
			if (is_array($result->retval) && count($result->retval) > 0)
			{
				// If the person has an email account
				if (!is_null($result->retval[0]->receiver) && $result->retval[0]->receiver != '')
				{
					// Using a template as email body if it is not given as method parameter
					if (is_null($body))
					{
						// Using a template for the html email body
						$href = APP_ROOT . $this->ci->config->item('redirect_view_message_url') . $result->retval[$i]->token;
						$bodyMsg = $this->ci->parser->parse(
							'templates/mailHTML',
							array(
								'src' => APP_ROOT . $this->ci->config->item('message_html_view_url') . $result->retval[$i]->token,
								'href' => $href
							),
							true
						);
						if (is_null($bodyMsg) || $bodyMsg == '')
						{
							// $body = $result->retval[0]->body;
							$this->ci->loglib->logError('Error while parsing the html mail template');
						}
						
						// Using a template for the plain text email body
						$altBody = $this->ci->parser->parse(
							'templates/mailTXT',
							array(
								'href' => $href
							),
							true
						);
						if (is_null($altBody) || $altBody == '')
						{
							$this->ci->loglib->logError('Error while parsing the plain text mail template');
						}
					}
					else
					{
						$bodyMsg = $altBody = $body;
					}
					
					// If the sender kontakt does not exist, then use system
					$sender = $this->ci->maillib->getConfigs()->email_from_system;
					if (!is_null($result->retval[0]->sender) && $result->retval[0]->sender != '')
					{
						$sender = $result->retval[0]->sender;
					}
					
					// Sending email
					$sent = $this->ci->maillib->send(
						$sender,
						$result->retval[0]->receiver,
						is_null($subject) ? $result->retval[0]->subject : $subject, // if parameter subject is not null, use it!
						$bodyMsg,
						null,
						null,
						null,
						$altBody
					);
					// If errors were occurred while sending the email
					if (!$sent)
					{
						$this->ci->loglib->logError('Error while sending an email');
						// Writing errors in tbl_message_status
						$sme = $this->setMessageError(
								$result->retval[0]->message_id,
								$result->retval[0]->receiver_id,
								'Error while sending an email',
								$result->retval[0]->sentinfo
						);
						if (!$sme)
						{
							$this->ci->loglib->logError('Error while updating DB');
						}
					}
					else
					{
						// Setting the message as sent in DB
						$sent = $this->setMessageSent($result->retval[0]->message_id, $result->retval[0]->receiver_id);
						// If the email has been sent and the DB updated
						if (!$sent)
						{
							$this->ci->loglib->logError('Error while updating DB');
						}
					}
				}
				else
				{
					$this->ci->loglib->logError('This person does not have an email account');
					// Writing errors in tbl_message_status
					$sme = $this->setMessageError(
							$result->retval[0]->message_id,
							$result->retval[0]->receiver_id,
							'This person does not have an email account',
							$result->retval[0]->sentinfo
					);
					if (!$sme)
					{
						$this->ci->loglib->logError('Error while updating DB');
					}
					$sent = true; // Non blocking error
				}
			}
			else
			{
				$this->ci->loglib->logInfo('There are no email to be sent');
				$sent = false;
			}
		}
		else
		{
			$this->ci->loglib->logError('Something went wrong while getting data from DB');
			$sent = false;
		}
		
		return $sent;
	}
    
    // ------------------------------------------------------------------------
    // Private Functions from here out!
    // ------------------------------------------------------------------------
    
	/**
	 * Update the table tbl_message_recipient
	 */
	private function _updateMessageRecipient($message_id, $receiver_id, $parameters)
	{
		$updated = false;
		
		// Changes the status of the message from unread to read
		$resultUpdate = $this->ci->RecipientModel->update(array($receiver_id, $message_id), $parameters);
		// Checks if errors were occurred
		if (is_object($resultUpdate) && $resultUpdate->error == EXIT_SUCCESS && is_array($resultUpdate->retval))
		{
			$updated = true;
		}
		
		return $updated;
	}
	
	/**
	 * Changes the status of the message from unsent to sent
	 */
	private function setMessageSent($message_id, $receiver_id)
	{
		$parameters = array('sent' => 'NOW()', 'sentinfo' => null);
		
		return $this->_updateMessageRecipient($message_id, $receiver_id, $parameters);
	}
	
	/**
	 * Sets the sentInfo with the error
	 */
	private function setMessageError($message_id, $receiver_id, $sentInfo, $prevSentInfo = null)
	{
		if (!is_null($prevSentInfo) && $prevSentInfo != '')
		{
			$sentInfo = $prevSentInfo . SENT_INFO_NEWLINE . $sentInfo;
		}
		
		$parameters = array('sent' => null, 'sentinfo' => $sentInfo);
		
		return $this->_updateMessageRecipient($message_id, $receiver_id, $parameters);
	}
	
	/**
     * Gets the receivers id that are enabled to read messages for that oe_kurzbz
     */
    private function _getReceiversByOekurzbz($oe_kurzbz)
    {
		// Load Benutzerfunktion_model
		$this->ci->load->model('person/Benutzerfunktion_model', 'BenutzerfunktionModel');
		// Join with table public.tbl_benutzer on field uid
		$this->ci->BenutzerfunktionModel->addJoin('public.tbl_benutzer', 'uid');
		// Get all the valid receivers id using the oe_kurzbz
		$receivers = $this->ci->BenutzerfunktionModel->loadWhere(
			'oe_kurzbz = \'' . $oe_kurzbz . '\''.
			' AND funktion_kurzbz = \'ass\'' .
			' AND (NOW() BETWEEN COALESCE(datum_von, NOW()) AND COALESCE(datum_bis, NOW()))'
		);
		
		return $receivers;
    }
    
    /**
     * Gets the receivers id
     */
    private function _getReceivers($receiver_id, $oe_kurzbz = null)
    {
		$receivers = null;
		
		// If no receiver_id is given...
		if (is_null($receiver_id))
		{
			// ...a oe_kurzbz must be specified
			if (is_null($oe_kurzbz))
			{
				$receivers = $this->_error('', MSG_ERR_INVALID_OU);
			}
			else
			{
				$receivers = $this->_getReceiversByOekurzbz($oe_kurzbz);
			}
		}
		// Else if the receiver id is given
		else
		{
			$receivers = $this->_success(array(new stdClass()));
			$receivers->retval[0]->person_id = $receiver_id;
		}
		
		return $receivers;
    }
    
    /**
     * Checks if the given receiver id is a valid person
     */
    private function _checkReceiverId($receiver_id)
    {
		// Load Person_model
		$this->ci->load->model('person/Person_model', 'PersonModel');
		$result = $this->ci->PersonModel->load($receiver_id);
		if (is_object($result) && $result->error == EXIT_SUCCESS && is_array($result->retval) && count($result->retval) > 0)
		{
			return true;
		}
		
		return false;
    }
    
    /**
     * Wrapper for function error
     */
    private function _error($retval = '', $code = null)
    {
		return error($retval, $code, MessageLib::MSG_INDX_PREFIX);
    }
    
    /**
     * Wrapper for function success
     */
    private function _success($retval = '', $code = null)
    {
		return success($retval, $code, MessageLib::MSG_INDX_PREFIX);
    }
}