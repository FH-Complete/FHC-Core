<?php  

if (! defined('BASEPATH')) exit('No direct script access allowed');
/**
* Name:        Messaging Library for FH-Complete
*
*
*/
class MessageLib
{
	private $recipients = array(); // not used anymore
	
    public function __construct()
    {
        $this->ci =& get_instance();

		// Loads message configuration
		$this->ci->config->load('message');
		// The second parameter is used to avoiding name collisions in the config array
		$this->ci->config->load("mail", true);

		// CI Email library
		$this->ci->load->library("email");
		// CI Parser library
		$this->ci->load->library("parser");
		// Loads LogLib
		$this->ci->load->library('LogLib');
		// Loads VorlageLib
		$this->ci->load->library('VorlageLib');
		
		// Initializing email library with the loaded configurations
		$this->ci->email->initialize($this->ci->config->config["mail"]);
		
		// Loading models
		$this->ci->load->model('system/Message_model', 'MessageModel');
		$this->ci->load->model('system/MsgStatus_model', 'MsgStatusModel');
		$this->ci->load->model('system/Recipient_model', 'RecipientModel');
		$this->ci->load->model('system/Attachment_model', 'AttachmentModel');

		// Loads fhc helper
		$this->ci->load->helper('fhc');
		
        //$this->ci->load->helper('language');
        $this->ci->lang->load('message');
    }

    // ------------------------------------------------------------------------

    /**
     * get_message() - will return a single message, including the status for specified user.
     *
     * @param   integer  $msg_id   REQUIRED
     * @return  array
     */
    function getMessage($msg_id)
    {
        if (!is_numeric($msg_id))
        	return $this->_invalid_id(MSG_ERR_INVALID_MSG_ID);
		
		$this->ci->MessageModel->addJoin('public.tbl_person', 'person_id');
		$msg = $this->ci->MessageModel->loadWhere(array('message_id' => $msg_id));
		//$msg = $this->ci->MessageModel->getMessage($msg_id);
		$stat = $this->ci->MsgStatusModel->loadWhere(array('message_id' => $msg_id));
		$msg->retval[0]->stat = $stat->retval;
		$recp = $this->ci->RecipientModel->loadWhere(array('message_id' => $msg_id));
		$msg->retval[0]->recp = $recp->retval;
		$attm = $this->ci->AttachmentModel->loadWhere(array('message_id' => $msg_id));
		$msg->retval[0]->attm = $attm->retval;
		
        return $msg;
    }

	/**
     * getMessagesByUID() - will return all messages, including the latest status for specified user. It don´t returns Attachments.
     *
     * @param   string  $uid   REQUIRED
     * @return  array
     */
    function getMessagesByUID($uid, $all = false)
    {
        if (empty($uid))
        	return $this->_error(MSG_ERR_INVALID_MSG_ID);
		
		$msg = $this->ci->MessageModel->getMessagesByUID($uid, $all);		

        return $msg;
    }

	/**
     * getMessagesByPerson() - will return all messages, including the latest status for specified user. It don´t returns Attachments.
     *
     * @param   bigint  $person_id   REQUIRED
     * @return  array
     */
    function getMessagesByPerson($person_id, $all = false)
    {
        if (empty($person_id))
        	return $this->_error(MSG_ERR_INVALID_MSG_ID);
		
		$msg = $this->ci->MessageModel->getMessagesByPerson($person_id, $all);		

        return $msg;
    }

    // ------------------------------------------------------------------------

    /**
     * getSubMessages() - will return all Messages subordinated from a specified message.
     *
     * @param   integer  $msg_id    REQUIRED
     * @return  array
     */
    function getSubMessages($msg_id)
    {
        if (!is_numeric($msg_id))
        	return $this->_invalid_id(MSG_ERR_INVALID_MSG_ID);
		
        return $this->getMessage($msg_id);
    }
	
	/**
     * getMessagesByToken
     *
     * @param	token string
     * @return	array
     */
    function getMessagesByToken($token)
    {
        if (empty($token))
        	return $this->_error(MSG_ERR_INVALID_MSG_ID);
		
		$result = $this->ci->MessageModel->getMessagesByToken($token);
		if (is_object($result) && $result->error == EXIT_SUCCESS && is_array($result->retval) && count($result->retval) > 0)
		{
			if ($result->retval[0]->status == MSG_STATUS_UNREAD)
			{
				$statusKey = array(
					'message_id' => $result->retval[0]->message_id,
					'person_id' => $result->retval[0]->receiver_id,
					'status' => MSG_STATUS_UNREAD
				);
				$resTmp = $this->ci->MsgStatusModel->update($statusKey, array('status' => MSG_STATUS_READ));
				if (!is_object($resTmp) || (is_object($resTmp) && $resTmp->error != EXIT_SUCCESS))
				{
					$result = $resTmp;
				}
				else
				{
					$result->retval[0]->status = MSG_STATUS_READ;
				}
			}
		}

        return $result;
    }

    // ------------------------------------------------------------------------


    // ------------------------------------------------------------------------

    /**
     * updateMessageStatus() - will change status on message for particular user
     *
     * @param   integer  $msg_id     REQUIRED
     * @param   integer  $user_id    REQUIRED
     * @param   integer  $status_id  REQUIRED - should come from config/message.php list of constants
     * @return  array
     */
    function updateMessageStatus($message_id, $person_id, $status)
    {
        if (empty($message_id))
        {
            return $this->_invalid_id(MSG_ERR_INVALID_MSG_ID);
        }

        if (empty($person_id))
        {
            return $this->_invalid_id(MSG_ERR_INVALID_USER_ID);
        }

		// Not use empty otherwise if status is 0 it returns an error
        if (!isset($status))
        {
            return $this->_invalid_id(MSG_ERR_INVALID_STATUS_ID);
        }

		$result = $this->ci->MsgStatusModel->update(
			array('message_id' => $message_id, 'person_id' => $person_id),
			array('status' => $status)
		);
		
		return $result;
    }

    // ------------------------------------------------------------------------

    /**
     * add_participant() - adds user to existing thread - NOT used anymore
     *
     * @param   integer  $thread_id  REQUIRED
     * @param   integer  $user_id    REQUIRED
     * @return  array
     */
    function addRecipient($person_id)
    {
        if (!is_numeric($person_id))
        	return $this->_invalid_id(MSG_ERR_INVALID_MSG_ID);
		
		$this->recipients[] = $person_id;
		
		return true;
    }

    

    // ------------------------------------------------------------------------

    /**
     * sendMessage() - sends new internal message. This function will create a new thread
     *
     * @param   integer  $sender_id   REQUIRED
     * @param   mixed    $recipients  REQUIRED - a single integer or an array of integers, representing user_ids
     * @param   string   $subject
     * @param   string   $body
     * @param   integer  $priority
     * @return  array
     */
    function sendMessage($sender_id, $subject = '', $body = '', $priority = PRIORITY_NORMAL, $relationmessage_id = null, $oe_kurzbz = null)
    {
        if (!is_numeric($sender_id))
        	return $this->_invalid_id(MSG_ERR_INVALID_MSG_ID);
		
		// Start sending Message
		$this->ci->db->trans_start(false);
		//save Message
		$data = array(
			'person_id' => $sender_id,
			'subject' => $subject,
			'body' => $body,
			'priority' => $priority,
			'relationmessage_id' => $relationmessage_id,
			'oe_kurzbz' => $oe_kurzbz
		);
		
		$result = $this->ci->MessageModel->insert($data);
		if (is_object($result) && $result->error == EXIT_SUCCESS)
		{
			/**
			 * @TODO: sender_id must be a receiver_id
			 */
			$msg_id = $result->retval;
			$statusData = array(
				'message_id' => $msg_id,
				'person_id' => $sender_id,
				'status' => MSG_STATUS_UNREAD
			);
			$result = $this->ci->MsgStatusModel->insert($statusData);
		}

		$this->ci->db->trans_complete();

		if ($this->ci->db->trans_status() === FALSE || (is_object($result) && $result->error != EXIT_SUCCESS))
		{
			$this->ci->db->trans_rollback();
			return $this->_error($result->msg, EXIT_ERROR);
		}
		else
		{
			$this->ci->db->trans_commit();
			return $this->_success($msg_id);
		}
    }
	
	/**
     * sendMessage() - sends new internal message. This function will create a new thread
     *
     * @param   integer  $sender_id   REQUIRED
     * @param   mixed    $recipients  REQUIRED - a single integer or an array of integers, representing user_ids
     * @param   string   $subject
     * @param   string   $body
     * @param   integer  $priority
     * @return  array
     */
    function sendMessageVorlage($sender_id, $receiver_id, $vorlage_kurzbz, $oe_kurzbz, $data, $relationmessage_id = null, $orgform_kurzbz = null)
    {
        if (!is_numeric($sender_id) || !is_numeric($receiver_id))
        	return $this->_invalid_id(MSG_ERR_INVALID_MSG_ID);

		// Load reveiver data to get its relative language
		$this->ci->load->model('person/Person_model', 'PersonModel');
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

					$this->ci->db->trans_start(false);
					// Save Message
					$msgData = array(
						'person_id' => $sender_id,
						'subject' => $result->retval[0]->subject,
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
						}
					}

					$this->ci->db->trans_complete();

					if ($this->ci->db->trans_status() === FALSE || (is_object($result) && $result->error != EXIT_SUCCESS))
					{
						$this->ci->db->trans_rollback();
						return $this->_error($result->msg, EXIT_ERROR);
					}
					else
					{
						$this->ci->db->trans_commit();
						return $this->_success($msg_id);
					}
				}
				else
				{
					// Better message error
					if (!is_array($result->retval) || (is_array($result->retval) && count($result->retval) == 0))
					{
						$result = $this->_error('Vorlage not found', EXIT_ERROR);
					}
					else if (is_array($result->retval) && count($result->retval) > 0)
					{
						if (is_null($result->retval[0]->oe_kurzbz))
						{
							$result = $this->_error('Vorlage not found', EXIT_ERROR);
						}
						else if (empty($result->retval[0]->text))
						{
							$result = $this->_error('Vorlage has an empty text', EXIT_ERROR);
						}
						else if (empty($result->retval[0]->subject))
						{
							$result = $this->_error('Vorlage has an empty subject', EXIT_ERROR);
						}
					}
				}
			}
			else
			{
				$result = $this->_error($result->retval, EXIT_ERROR);
			}
		}
		
		return $result;
    }
    

    // ------------------------------------------------------------------------
    // Private Functions from here out!
    // ------------------------------------------------------------------------

    /** ---------------------------------------------------------------
	 * Success
	 *
	 * @param   mixed  $retval
	 * @return  array
	 */
	protected function _success($retval, $message = MSG_SUCCESS)
	{
		$return = new stdClass();
		$return->error = EXIT_SUCCESS;
		$return->Code = $message;
		$return->msg = lang('message_' . $message);
		$return->retval = $retval;
		return $return;
	}

	/** ---------------------------------------------------------------
	 * General Error
	 *
	 * @return  array
	 */
	protected function _error($retval = '', $message = MSG_ERROR)
	{
		$return = new stdClass();
		$return->error = EXIT_ERROR;
		$return->Code = $message;
		$return->msg = lang('message_' . $message);
		$return->retval = $retval;
		return $return;
	}
    /**
     * Invalid ID
     *
     * @param   integer  config.php error code numbers
     * @return  array
     */
    private function _invalid_id($error = '')
    {
        return array(
            'err'  => 1,
            'code' => $error,
            'msg'  => lang('message_'.$error)
        );
    }
	
	/**
	 * Gets an item from the email configuration array
	 */
	private function getEmailCfgItem($itemName)
	{
		return $this->ci->config->item($itemName, EMAIL_CONFIG_INDEX);
	}
	
	/**
	 * Sends a single email
	 */
	private function sendOne($from, $to, $subject, $message, $alias = "", $cc = null, $bcc = null)
	{
		$this->ci->email->from($from, $alias);
		$this->ci->email->to($to);
		if (!is_null($cc)) $this->ci->email->cc($cc);
		if (!is_null($bcc)) $this->ci->email->bcc($bcc);
		$this->ci->email->subject($subject);
		$this->ci->email->message($message);

		// Avoid printing on standard output ugly error messages
		return @$this->ci->email->send();
	}
	
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
		$parameters = array("sent" => "NOW()", "sentinfo" => null);
		
		return $this->_updateMessageRecipient($message_id, $receiver_id, $parameters);
	}
	
	/**
	 * Sets the sentInfo with the error
	 */
	private function setMessageError($message_id, $receiver_id, $sentInfo, $prevSentInfo = null)
	{
		if (!is_null($prevSentInfo) && $prevSentInfo != "")
		{
			$sentInfo = $prevSentInfo . SENT_INFO_NEWLINE . $sentInfo;
		}
		
		$parameters = array("sent" => null, "sentinfo" => $sentInfo);
		
		return $this->_updateMessageRecipient($message_id, $receiver_id, $parameters);
	}
	
	/**
	 * Gets all the messages from DB and sends them via email
	 */
	public function sendAll($numberToSent = null, $numberPerTimeRange = null, $email_time_range = null)
	{
		$sent = true; // optimistic expectation
		
		// Gets a number (email_number_to_sent) of unsent messages from DB
		// having EMAIL_KONTAKT_TYPE as relative contact type
		$result = $this->ci->RecipientModel->getMessages(
				EMAIL_KONTAKT_TYPE,
				null,
				$this->getEmailCfgItem("email_number_to_sent")
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
					if (!is_null($result->retval[$i]->receiver) && $result->retval[$i]->receiver != "")
					{
						// Using a template as email body
						$body = $this->ci->parser->parse("templates/mail", array("body" => $result->retval[$i]->body), true);
						if (is_null($body) || $body == "")
						{
							// $body = $result->retval[$i]->body;
							$this->ci->loglib->logError("Error while parsing the mail template");
						}
						
						// If the sender kontakt does not exist, then use system
						$sender = $this->getEmailCfgItem("email_from_system");
						if (!is_null($result->retval[$i]->sender) && $result->retval[$i]->sender != "")
						{
							$sender = $result->retval[$i]->sender;
						}
						
						// Sending email
						$sent = $this->sendOne(
							$sender,
							$result->retval[$i]->receiver,
							$result->retval[$i]->subject,
							$body
						);
						// If errors were occurred while sending the email
						if (!$sent)
						{
							$this->ci->loglib->logError("Error while sending an email");
							// Writing errors in tbl_message_status
							$sme = $this->setMessageError(
									$result->retval[$i]->message_id,
									$result->retval[$i]->receiver_id,
									"Error while sending an email",
									$result->retval[$i]->sentinfo
							);
							if (!$sme)
							{
								$this->ci->loglib->logError("Error while updating DB");
							}
						}
						else
						{
							// Setting the message as sent in DB
							$sent = $this->setMessageSent($result->retval[$i]->message_id, $result->retval[$i]->receiver_id);
							// If the email has been sent and the DB updated
							if ($sent)
							{
							// If it has been sent a specified number of emails, then it has to wait
								if ((is_numeric($numberPerTimeRange) && $numberPerTimeRange == $i + 1) ||
									$this->getEmailCfgItem("email_number_per_time_range") == $i + 1)
								{
									// Gets the number of seconds to wait until the next send
									$seconds = 0;

									if (is_numeric($email_time_range))
										$seconds = $email_time_range;
									else
										$seconds = $this->getEmailCfgItem("email_time_range");

									sleep($seconds); // Wait!!!
								}
							}
							else
							{
								$this->ci->loglib->logError("Error while updating DB");
							}
						}
					}
					else
					{
						$this->ci->loglib->logError("This person does not have an email account");
						// Writing errors in tbl_message_status
						$sme = $this->setMessageError(
								$result->retval[$i]->message_id,
								$result->retval[$i]->receiver_id,
								"This person does not have an email account",
								$result->retval[$i]->sentinfo
						);
						if (!$sme)
						{
							$this->ci->loglib->logError("Error while updating DB");
						}
						$sent = true; // Non blocking error
					}
				}
			}
			else
			{
				$this->ci->loglib->logInfo("There are no email to be sent");
				$sent = false;
			}
		}
		else
		{
			$this->ci->loglib->logError("Something went wrong while getting data from DB");
			$sent = false;
		}
		
		return $sent;
	}
}