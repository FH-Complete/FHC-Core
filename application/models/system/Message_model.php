<?php 
if ( ! defined('BASEPATH')) 
	exit('No direct script access allowed');

class Message_model extends DB_Model
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		require_once APPPATH.'config/message.php';
		$this->lang->load('message');
		$this->dbTable = 'public.tbl_msg_message';
		$this->pk = 'message_id';
	}
	
	/** -----------------------------------------------------------------
     * getMessage() - will return a single message, including the status for specified user.
     *
     * @param   integer  $msg_id   EQUIRED
     * @param   integer  $person_id  REQUIRED
     * @return  array
     */
    /*function getMessage($msg_id)
    {
		// Validate
		if (empty($msg_id))
            return $this->_invalid_id(MSG_ERR_INVALID_MSG_ID);
       
        $sql = 'SELECT * FROM tbl_msg_message JOIN tbl_person USING (person_id) WHERE message_id=?' ;
        $result = $this->db->query($sql, array($msg_id));
        if ($result)
			return $this->_success($result->result());
		else
			return $this->_error($this->db->error(), FHC_DB_ERROR);
    }*/
 	/** -----------------------------------------------------------------
     * Get a Full Thread
     * get_full_thread() - will return a entire thread, including the status for specified user.
     *
     * @param   integer  $thread_id    REQUIRED
     * @param   integer  $person_id      REQUIRED
     * @param   boolean  $full_thread  OPTIONAL - If true, user will also see messages from thread posted BEFORE user became participant
     * @param   string   $order_by     OPTIONAL
     * @return  array
     */
    function get_full_thread($thread_id, $person_id, $full_thread = FALSE, $order_by = 'ASC')
    {
        // Validate
		 if (empty($thread_id))
        {
            return $this->_invalid_id(MSG_ERR_INVALID_THREAD_ID);
        }
        if (empty($person_id))
        {
            return $this->_invalid_id(MSG_ERR_INVALID_USER_ID);
        }
		$sql = 'SELECT m.*, s.status, t.subject, '."CONCAT(vorname, ' ', nachname) as user_name" .
        ' FROM ' . $this->db->dbprefix . 'tbl_msg_participant p ' .
        ' JOIN ' . $this->db->dbprefix . 'tbl_msg_thread t ON (t.thread_id = p.thread_id) ' .
        ' JOIN ' . $this->db->dbprefix . 'tbl_msg_message m ON (m.thread_id = t.thread_id) ' .
        ' JOIN ' . $this->db->dbprefix . 'public.tbl_person' . ' ON (' . 'tbl_person.person_id' . ' = m.sender_id) '.
        ' JOIN ' . $this->db->dbprefix . 'tbl_msg_status s ON (s.message_id = m.message_id AND s.person_id = ? ) ' .
        ' WHERE p.person_id = ? ' .
        ' AND p.thread_id = ? ';
        if ( ! $full_thread)
        {
            $sql .= ' AND m.cdate >= p.cdate';
        }
        $sql .= ' ORDER BY m.cdate ' . $order_by;
        $result = $this->db->query($sql, array($person_id, $person_id, $thread_id));
		if ($result)
			return $this->_success($result->result_array());
		else
			return $this->_general_error();
    }
    /** -----------------------------------------------------------------
     * get_all_threads() - will return all threads for user, including the status for specified user.
     *
     * @param   integer  $person_id      REQUIRED
     * @param   boolean  $full_thread  OPTIONAL - If true, user will also see messages from thread posted BEFORE user became participant
     * @param   string   $order_by     OPTIONAL
     * @return  array
     */
    function get_all_threads($person_id, $full_thread = FALSE, $order_by = 'asc')
    {
		if (empty($person_id))
        {
            return $this->_invalid_id(MSG_ERR_INVALID_USER_ID);
        }
        $sql = 'SELECT m.*, s.status, t.subject, '."CONCAT(vorname, ' ', nachname) as user_name" .
        ' FROM ' . $this->db->dbprefix . 'tbl_msg_participant p ' .
        ' JOIN ' . $this->db->dbprefix . 'tbl_msg_thread t ON (t.thread_id = p.thread_id) ' .
        ' JOIN ' . $this->db->dbprefix . 'tbl_msg_message m ON (m.thread_id = t.thread_id) ' .
        ' JOIN ' . $this->db->dbprefix . 'public.tbl_person' . ' ON (' . 'tbl_person.person_id' . ' = m.sender_id) '.
        ' JOIN ' . $this->db->dbprefix . 'tbl_msg_status s ON (s.message_id = m.message_id AND s.person_id = ? ) ' .
        ' WHERE p.person_id = ? ' ;
        if (!$full_thread)
        {
            $sql .= ' AND m.cdate >= p.cdate';
        }
        $sql .= ' ORDER BY t.thread_id ' . $order_by. ', m.cdate '. $order_by;
        $result = $this->db->query($sql, array($person_id, $person_id));
       if ($result)
			return $this->_success($result->result_array());
		else
			return $this->_general_error();
    }
    /** -----------------------------------------------------------------
     * Get all Threads Grouped
	 * get_all_threads_grouped() - will return all threads for user, including the status for specified user.
     *                           - messages are grouped in threads.
     *
     * @param   integer  $person_id      REQUIRED
     * @param   boolean  $full_thread  OPTIONAL - If true, user will also see messages from thread posted BEFORE user became participant
     * @param   string   $order_by     OPTIONAL
     * @return  array
     */
    function get_all_threads_grouped($person_id, $full_thread = FALSE, $order_by = 'ASC')
    {
        if (empty($person_id))
        {
            return $this->_invalid_id(MSG_ERR_INVALID_USER_ID);
        }
		$message = $this->get_all_threads($person_id, $full_thread, $order_by);
        if (is_array($message))
        {
            $threads = array();
            foreach ($message as $msg)
            {
                if ( ! isset($threads[$msg['thread_id']]))
                {
                    $threads[$msg['thread_id']]['thread_id'] = $msg['thread_id'];
                    $threads[$msg['thread_id']]['messages']  = array($msg);
                }
                else
                {
                    $threads[$msg['thread_id']]['messages'][] = $msg;
                }
            }
            return $this->_success($threads);
        }
        // General Error Occurred
        return $this->_general_error();
    }
    /** -----------------------------------------------------------------
     * Change Message Status
     * update_message_status() - will change status on message for particular user
     *
     * @param   integer  $msg_id     REQUIRED
     * @param   integer  $person_id    REQUIRED
     * @param   integer  $status_id  REQUIRED - should come from config/message.php list of constants
     * @return  array
     */
    function update_message_status($msg_id, $person_id, $status_id)
    {
        // Validate
		if (empty($msg_id))
        {
            return $this->_invalid_id(MSG_ERR_INVALID_MSG_ID);
        }
        if (empty($person_id))
        {
            return $this->_invalid_id(MSG_ERR_INVALID_USER_ID);
        }
        if (empty($status_id))
        {
            return $this->_invalid_id(MSG_ERR_INVALID_STATUS_ID);
        }
		$this->db->where(array('message_id' => $msg_id, 'person_id' => $person_id ));
        $this->db->update('tbl_msg_status', array('status' => $status_id ));
        $rows = $this->db->affected_rows();
		if ($rows == 1)
			return $this->_success($rows, MSG_STATUS_UPDATE);
		else
			return $this->_general_error();
    }
    /** -----------------------------------------------------------------
     * Add a Participant
     * add_participant() - adds user to existing thread
     *
     * @param   integer  $thread_id  REQUIRED
     * @param   integer  $person_id    REQUIRED
     * @return  array
     */
    function addParticipant($thread_id, $person_id)
    {
       
		if (empty($thread_id))
        {
            return $this->_invalid_id(MSG_ERR_INVALID_THREAD_ID);
        }
        if (empty($person_id))
        {
            return $this->_invalid_id(MSG_ERR_INVALID_USER_ID);
        }
		if ( ! $this->valid_new_participant($thread_id, $person_id))
        {
            $this->_participant_error(MSG_ERR_PARTICIPANT_EXISTS);
        }
		if ( ! $this->application_user($person_id))
        {
            $this->_participant_error(MSG_ERR_PARTICIPANT_NONSYSTEM);
        }
		$this->db->trans_start();
        $participants[] = array('thread_id' => $thread_id,'person_id' => $person_id);
        $this->_insert_participants($participants);
        // Get Messages by Thread
        $messages = $this->_get_messages_by_thread_id($thread_id);
        foreach ($messages as $message)
        {
            $statuses[] = array('message_id' => $message['message_id'], 'person_id' => $person_id, 'status' => MSG_STATUS_UNREAD);
        }
        $this->_insert_statuses($statuses);
        $this->db->trans_complete();
        if ($this->db->trans_status() === FALSE)
        {
            $this->db->trans_rollback();
            return $this->_general_error();
        }
        return $this->_success(NULL, MSG_PARTICIPANT_ADDED);
    }
    /** ---------------------------------------------------------------
     * Remove a Participant
    * remove_participant() - removes user from existing thread
     *
     * @param   integer  $thread_id  REQUIRED
     * @param   integer  $person_id    REQUIRED
     * @return  array
     */
    function remove_participant($thread_id, $person_id)
    {
        // Validate
		if (empty($thread_id))
        {
            return $this->_invalid_id(MSG_ERR_INVALID_THREAD_ID);
        }
        if (empty($person_id))
        {
            return $this->_invalid_id(MSG_ERR_INVALID_USER_ID);
        }
		$this->db->trans_start();
        $this->_delete_participant($thread_id, $person_id);
        $this->_delete_statuses($thread_id, $person_id);
        $this->db->trans_complete();
        if ($this->db->trans_status() === FALSE)
        {
            $this->db->trans_rollback();
            return $this->_success(NULL, MSG_PARTICIPANT_REMOVED);
        }
        return $this->_general_error();
    }
	/** ----------------------------------------------------------------
     * Send a New Message
     * send_new_message() - sends new internal message. This function will create a new thread
     *
     * @param   integer  $sender_id   REQUIRED
     * @param   mixed    $recipients  REQUIRED - a single integer or an array of integers, representing person_ids
     * @param   string   $subject
     * @param   string   $body
     * @param   integer  $priority
     * @return  array
     */
    function send_new_message($sender_id, $recipients, $subject, $body, $priority)
    {
        // Validate
		if (empty($sender_id))
        {
            return $this->_invalid_id(MSG_ERR_INVALID_SENDER_ID);
        }
        if (empty($recipients))
        {
            return array(
                'err'  => 1,
                'code' => MSG_ERR_INVALID_RECIPIENTS,
                'msg'  => lang('mahana_'.MSG_ERR_INVALID_RECIPIENTS)
            );
        }
		$this->db->trans_start();
        $thread_id = $this->_insert_thread($subject);
        $msg_id    = $this->_insert_message($thread_id, $sender_id, $body, $priority);
        // Create batch inserts
        $participants[] = array('thread_id' => $thread_id,'person_id' => $sender_id);
        $statuses[]     = array('message_id' => $msg_id, 'person_id' => $sender_id,'status' => MSG_STATUS_READ);
        if ( ! is_array($recipients) )
        {
			if ($sender_id != $recipients)
            {
				$participants[] = array('thread_id' => $thread_id,'person_id' => $recipients);
            	$statuses[]     = array('message_id' => $msg_id, 'person_id' => $recipients, 'status' => MSG_STATUS_UNREAD);
			}
        }
        else
        {
            foreach ($recipients as $recipient)
            {
				if ($sender_id != $recipient)
				{
                	$participants[] = array('thread_id' => $thread_id,'person_id' => $recipient);
                	$statuses[]     = array('message_id' => $msg_id, 'person_id' => $recipient, 'status' => MSG_STATUS_UNREAD);
				}
            }
        }
        $participants=array_unique($participants, SORT_REGULAR); // Clean if sender and recipient is the same
        $this->_insert_participants($participants);
        $this->_insert_statuses($statuses);
        $this->db->trans_complete();
        if ($this->db->trans_status() === FALSE)
        {
            $this->db->trans_rollback();
            return $this->_general_error();
        }
        return $this->_success($thread_id, MSG_MESSAGE_SENT);
    }
    /** --------------------------------------------------------------
     * Reply to Message
     * reply_to_message() - replies to internal message. This function will NOT create a new thread or participant list
     *
     * @param   integer  $msg_id     REQUIRED
     * @param   integer  $sender_id  REQUIRED
     * @param   string   $subject
     * @param   string   $body
     * @param   integer  $priority
     * @return  array
     */
    function reply_to_message($reply_msg_id, $sender_id, $body, $priority)
    {
		// Validate
		if (empty($sender_id))
        {
            return $this->_invalid_id(MSG_ERR_INVALID_SENDER_ID);
        }
        if (empty($msg_id))
        {
            return $this->_invalid_id(MSG_ERR_INVALID_MSG_ID);
        }        
		$this->db->trans_start();
        // Get the thread id to keep messages together
        if ( ! $thread_id = $this->_get_thread_id_from_message($reply_msg_id))
        {
            return FALSE;
        }
        // Add this message
        $msg_id = $this->_insert_message($thread_id, $sender_id, $body, $priority);
        if ($recipients = $this->_get_thread_participants($thread_id, $sender_id))
        {
            $statuses[] = array('message_id' => $msg_id, 'person_id' => $sender_id,'status' => MSG_STATUS_READ);
            foreach ($recipients as $recipient)
            {
                $statuses[] = array('message_id' => $msg_id, 'person_id' => $recipient['person_id'], 'status' => MSG_STATUS_UNREAD);
            }
            $this->_insert_statuses($statuses);
        }
        $this->db->trans_complete();
        if ($this->db->trans_status() === FALSE)
        {
            $this->db->trans_rollback();
            return $this->_general_error();
        }
        return $this->_success($msg_id, MSG_MESSAGE_SENT);
    }
    /** ----------------------------------------------------------------
     * Get Participant List
     * get_participant_list() - returns list of participants on given thread. If sender_id set, sender_id will be left off list
     *
     * @param   integer  $thread_id  REQUIRED
     * @param   integer  $sender_id  REQUIRED
     * @return  array
     */
    function get_participant_list($thread_id, $sender_id = 0)
    {
        // Validate
		if (empty($thread_id))
        {
            return $this->_invalid_id(MSG_ERR_INVALID_THREAD_ID);
        }
		
		if ($results = $this->_get_thread_participants($thread_id, $sender_id))
            return $this->_success($results);
		else
        	return $this->_general_error();
    }
    /** ----------------------------------------------------------------
     * Get Message Count
     * get_msg_count() - returns integer with count of message for user, by status. defaults to new messages
     *
     * @param   integer  $person_id    REQUIRED
     * @param   integer  $status_id  OPTIONAL - defaults to "Unread"
     * @return  array
     */
    function get_msg_count($person_id, $status_id = MSG_STATUS_UNREAD)
    {
        if (empty($person_id))
        {
            return $this->_invalid_id(MSG_ERR_INVALID_USER_ID);
        }
		$result = $this->db->select('COUNT(*) AS msg_count')->where(array('person_id' => $person_id, 'status' => $status_id ))->get('tbl_msg_status');
		$rows = $result->row()->msg_count;
		
		if (is_numeric($rows))
	        return $this->_success($rows);
		else
			 return $this->_general_error();
    }
    // ------------------------------------------------------------------------
    /**
     * Valid New Participant - because of CodeIgniter's DB Class return style,
     *                         it is safer to check for uniqueness first
     *
     * @param   integer $thread_id
     * @param   integer $person_id
     * @return  boolean
     */
    function valid_new_participant($thread_id, $person_id)
    {
        $sql = 'SELECT COUNT(*) AS count ' .
        ' FROM ' . $this->db->dbprefix . 'tbl_msg_participant p ' .
        ' WHERE p.thread_id = ? ' .
        ' AND p.person_id = ? ';
        $query = $this->db->query($sql, array($thread_id, $person_id));
        if ($query->row()->count)
        {
            return FALSE;
        }
        return TRUE;
    }
    
    /** ---------------------------------------------------------------
     * Application User
     *
     * @param   integer  $person_id`
     * @return  boolean
     */
    function application_user($person_id)
    {
        $sql = 'SELECT COUNT(*) AS count ' .
        ' FROM ' . $this->db->dbprefix . 'public.tbl_person' .
        ' WHERE ' . 'tbl_person.person_id' . ' = ?' ;
        $query = $this->db->query($sql, array($person_id));
        if ($query->row()->count)
        {
            return TRUE;
        }
        return FALSE;
    }
  
    // ------------------------------------------------------------------------
    // Private Functions from here out!
    // ------------------------------------------------------------------------
    /**
     * Insert Thread
     *
     * @param   string  $subject
     * @return  integer
     */
    private function _insert_thread($subject)
    {
        $insert_id = $this->db->insert('tbl_msg_thread', array('subject' => $subject));
        return $this->db->insert_id();
    }
    /**
     * Insert Message
     *
     * @param   integer  $thread_id
     * @param   integer  $sender_id
     * @param   string   $body
     * @param   integer  $priority
     * @return  integer
     */
    private function _insert_message($thread_id, $sender_id, $body, $priority)
    {
        $insert['thread_id'] = $thread_id;
        $insert['sender_id'] = $sender_id;
        $insert['body']      = $body;
        $insert['priority']  = $priority;
        $insert_id = $this->db->insert('tbl_msg_message', $insert);
        return $this->db->insert_id();
    }
    /**
     * Insert Participants
     *
     * @param   array  $participants
     * @return  bool
     */
    private function _insert_participants($participants)
    {
        return $this->db->insert_batch('tbl_msg_participant', $participants);
    }
    /**
     * Insert Statuses
     *
     * @param   array  $statuses
     * @return  bool
     */
    private function _insert_statuses($statuses)
    {
        return $this->db->insert_batch('tbl_msg_status', $statuses);
    }
    /**
     * Get Thread ID from Message
     *
     * @param   integer  $msg_id
     * @return  integer
     */
    private function _get_thread_id_from_message($msg_id)
    {
        $query = $this->db->select('thread_id')->get_where('tbl_msg_message', array('id' => $msg_id));
        if ($query->num_rows())
        {
            return $query->row()->thread_id;
        }
        return 0;
    }
    /**
     * Get Messages by Thread
     *
     * @param   integer  $thread_id
     * @return  array
     */
    private function _get_messages_by_thread_id($thread_id)
    {
        $query = $this->db->get_where('tbl_msg_message', array('thread_id' => $thread_id));
        return $query->result_array();
    }
    /**
     * Get Thread Particpiants
     *
     * @param   integer  $thread_id
     * @param   integer  $sender_id
     * @return  array
     */
    private function _get_thread_participants($thread_id, $sender_id = 0)
    {
        $array['thread_id'] = $thread_id;
        if ($sender_id) // If $sender_id 0, no one to exclude
        {
            $array['tbl_msg_participant.person_id != '] = $sender_id;
        }
        $this->db->select('tbl_msg_participant.person_id, '."CONCAT(vorname, ' ', nachname) as user_name", FALSE);
        $this->db->join('public.tbl_person', 'tbl_msg_participant.person_id = ' . 'tbl_person.person_id');
        $query = $this->db->get_where('tbl_msg_participant', $array);
        return $query->result_array();
    }
    /**
     * Delete Participant
     *
     * @param   integer  $thread_id
     * @param   integer  $person_id
     * @return  boolean
     */
    private function _delete_participant($thread_id, $person_id)
    {
        $this->db->delete('tbl_msg_participant', array('thread_id' => $thread_id, 'person_id' => $person_id));
        if ($this->db->affected_rows() > 0)
        {
            return TRUE;
        }
        return FALSE;
    }
    /**
     * Delete Statuses
     *
     * @param   integer  $thread_id
     * @param   integer  $person_id
     * @return  boolean
     */
    private function _delete_statuses($thread_id, $person_id)
    {
        $sql = 'DELETE s FROM tbl_msg_status s ' .
        ' JOIN ' . $this->db->dbprefix . 'tbl_msg_message m ON (m.message_id = s.message_id) ' .
        ' WHERE m.thread_id = ? ' .
        ' AND s.person_id = ? ';
        $query = $this->db->query($sql, array($thread_id, $person_id));
        return TRUE;
    }
	/** ---------------------------------------------------------------
     * Error Particpant Exists
     *
     * @return  array
     */
    private function _participant_error($error = '')
    {
        return array(
            'err'  => 1,
            'code' => 1,
            'msg'  => lang('mahana_' . $error)
        );
    }
}
/* end of file message_model.php */
