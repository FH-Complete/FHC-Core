<?php  

if (! defined('BASEPATH')) exit('No direct script access allowed');
/**
* Name:        Messaging Library for FH-Complete
*
*
*/
class MessageLib
{
	private $recipients = array();
	
    public function __construct()
    {
        $this->ci =& get_instance();
		
		$this->ci->config->load('message');

		$this->ci->load->model('system/Message_model', 'MessageModel');
		$this->ci->load->model('system/MsgStatus_model', 'MsgStatusModel');
		$this->ci->load->model('system/Recipient_model', 'RecipientModel');
		$this->ci->load->model('system/Attachment_model', 'AttachmentModel');
		
		$this->ci->load->library('VorlageLib');
		
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
		

        // General Error Occurred
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

        // General Error Occurred
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

        // General Error Occurred
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
     * add_participant() - adds user to existing thread
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
}
