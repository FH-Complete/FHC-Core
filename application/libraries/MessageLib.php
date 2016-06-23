<?php  
	if (! defined('BASEPATH'))
		exit('No direct script access allowed');
/**
* Name:        Messaging Library for FH-Complete
*
*
*/

class MessageLib
{
	private $recipients = array();
	
    public function __construct($params = null)
    {
        $this->ci =& get_instance();
		$this->ci->config->load('message');

		$this->ci->load->model('system/Message_model', 'MessageModel');
		$this->ci->load->model('system/MsgStatus_model', 'MsgStatusModel');
		$this->ci->load->model('system/Recipient_model', 'RecipientModel');
		$this->ci->load->model('system/Attachment_model', 'AttachmentModel');
		
		if (is_array($params) && isset($params['uid']))
		{
			$this->ci->load->library('VorlageLib', array('uid' => $params['uid']));
			$this->ci->MessageModel->setUID($params['uid']);
			$this->ci->MsgStatusModel->setUID($params['uid']);
			$this->ci->RecipientModel->setUID($params['uid']);
			$this->ci->AttachmentModel->setUID($params['uid']);
		}
		else
		{
			$this->ci->load->library('VorlageLib');
		}
		
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
    function updateMessageStatus($msg_id, $user_id, $status_id )
    {
        if (empty($msg_id))
        {
            return $this->_invalid_id(MSG_ERR_INVALID_MSG_ID);
        }

        if (empty($user_id))
        {
            return $this->_invalid_id(MSG_ERR_INVALID_USER_ID);
        }

        if (empty($status_id))
        {
            return $this->_invalid_id(MSG_ERR_INVALID_STATUS_ID);
        }

        if ($this->ci->message_model->update_message_status($msg_id, $user_id, $status_id))
        {
            return $this->_success(NULL, MSG_STATUS_UPDATE);
        }

        // General Error Occurred
        return $this->_general_error();

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
		
        if (empty($this->recipients))
        	return $this->_error('No Recipients! Use addRecipient()', MSG_ERR_INVALID_RECIPIENTS);

		// Start sending Message
		$this->ci->db->trans_start(false);
        
		//save Message
		$data = array(
			'person_id' => $sender_id,
			'subject' => $subject,
			'body' => $body,
			'priority' => $priority,
			'relationmessage_id' => $relationmessage_id,
			'oe_kurzbz' => $oe_kurzbz);
		if (! $msg = $this->ci->MessageModel->insert($data))
        	return $this->_error($msg->msg.$msg->retval, MSG_ERR_GENERAL);
		$msg_id = $msg->retval;
		$this->ci->db->trans_complete();
        if ($this->ci->db->trans_status() === FALSE)
		{
        	// generate an error... or use the log_message() function to log your error
			// General Error Occurred
        	return $this->_error();
		}
		else
			return $this->_success($msg_id);
		
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
    function sendMessageVorlage($sender_id, $receiver_id, $vorlage_kurzbz, $oe_kurzbz, $data, $orgform_kurzbz = null)
    {
		var_dump($data);
		
		exit;
		
        if (!is_numeric($sender_id) || !is_numeric($receiver_id))
        	return $this->_invalid_id(MSG_ERR_INVALID_MSG_ID);

		$result = $this->ci->vorlagelib->getVorlage($vorlage_kurzbz);
		if (is_object($result) && $result->error == EXIT_SUCCESS)
		{
			if (is_array($result->retval) && count($result->retval) > 0)
			{
				$parsedText = $this->ci->vorlagelib->parseVorlagetext($result->retval[0]->text, $data);
				
				error_log($parsedText);
				
				$this->ci->db->trans_start(false);
				//save Message
				$msgData = array(
					'person_id' => $sender_id,
					//'subject' => $subject,
					'body' => $parsedText,
					'priority' => PRIORITY_NORMAL,
					//'relationmessage_id' => $relationmessage_id,
					'oe_kurzbz' => $oe_kurzbz
				);
				
				$result = $this->ci->MessageModel->insert($msgData);
				if (is_object($result) && $result->error == EXIT_SUCCESS)
				{
					$msg_id = $result->retval;
					$recipientData = array(
						'person_id' => $receiver_id,
						'message_id' => $msg_id
					);
					$result = $this->ci->RecipientModel->insert($recipientData);
					/*if (is_object($result) && $result->error == EXIT_SUCCESS)
					{
						
					}*/
				}
				
				$this->ci->db->trans_complete();
				
				if ($this->ci->db->trans_status() === FALSE)
				{
					$this->db->trans_rollback();
					return $this->_error($result->msg, EXIT_ERROR);
				}
				else
				{
					$this->db->trans_commit();
					return $this->_success($msg_id);
				}
			}
			else
			{
				$result = $this->_error('Vorlage not found', EXIT_ERROR);
			}
		}
		else
		{
			$result = $this->_error($result->msg, EXIT_ERROR);
		}
		
		return $result;
    }

    // ------------------------------------------------------------------------

    /**
     * reply_to_message() - replies to internal message. This function will NOT create a new thread or participant list
     *
     * @param   integer  $msg_id     REQUIRED
     * @param   integer  $sender_id  REQUIRED
     * @param   string   $subject
     * @param   string   $body
     * @param   integer  $priority
     * @return  array
     */
    function reply_to_message($msg_id, $sender_id, $subject = '', $body = '', $priority = PRIORITY_NORMAL)
    {
        if (empty($sender_id))
        {
            return $this->_invalid_id(MSG_ERR_INVALID_SENDER_ID);
        }

        if (empty($msg_id))
        {
            return $this->_invalid_id(MSG_ERR_INVALID_MSG_ID);
        }

        if ($new_msg_id = $this->ci->message_model->reply_to_message($msg_id, $sender_id, $body, $priority))
        {
            return $this->_success($new_msg_id, MSG_MESSAGE_SENT);
        }

        // General Error Occurred
        return $this->_general_error();
    }

    // ------------------------------------------------------------------------

    /**
     * get_participant_list() - returns list of participants on given thread. If sender_id set, sender_id will be left off list
     *
     * @param   integer  $thread_id  REQUIRED
     * @param   integer  $sender_id  REQUIRED
     * @return  array
     */
    function get_participant_list($thread_id, $sender_id = 0)
    {
        if (empty($thread_id))
        {
            return $this->_invalid_id(MSG_ERR_INVALID_THREAD_ID);
        }

        if ($participants = $this->ci->message_model-> get_participant_list($thread_id, $sender_id))
        {
            return $this->_success($participants);
        }

        // General Error Occurred
        return $this->_general_error();
    }

    // ------------------------------------------------------------------------

    /**
     * get_msg_count() - returns integer with count of message for user, by status. defaults to new messages
     *
     * @param   integer  $user_id    REQUIRED
     * @param   integer  $status_id  OPTIONAL - defaults to "Unread"
     * @return  array
     */
    function get_msg_count($user_id, $status_id = MSG_STATUS_UNREAD)
    {
        if (empty($user_id))
        {
            return $this->_invalid_id(MSG_ERR_INVALID_USER_ID);
        }

        if (is_numeric($message = $this->ci->message_model->get_msg_count($user_id, $status_id)))
        {
            return $this->_success($message);
        }

        // General Error Occurred
        return $this->_general_error();
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