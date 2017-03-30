<?php

/**
 * This model was implemented to let to operate with messages
 * without authentication. It's ugly but useful.
 */
class MessageToken_model extends CI_Model
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();

		// Loads config file message
		$this->config->load('message');

		// Load return message helper
		$this->load->helper('message');

		// Loads the database object
		$this->load->database();
	}

	/**
	 * Get a received message identified by token
	 */
	public function getMessageByToken($token)
	{
		$sql = 'SELECT r.message_id,
						m.person_id as sender_id,
						r.person_id as receiver_id,
						m.subject,
						m.body,
						m.insertamum,
						m.relationmessage_id,
						m.oe_kurzbz,
						s.status,
						s.statusinfo,
						s.insertamum as statusamum
				  FROM public.tbl_msg_recipient r JOIN public.tbl_msg_message m USING (message_id)
						JOIN (
							SELECT * FROM public.tbl_msg_status WHERE status < ? ORDER BY insertamum DESC, status DESC
						) s ON (r.message_id = s.message_id AND r.person_id = s.person_id)
				 WHERE r.token = ?
				 LIMIT 1';

		$result = $this->db->query($sql, array(MSG_STATUS_DELETED, $token));
		
		// If no errors occurred
		if ($result)
		{
			return success($result->result());
		}
		else
		{
			return error($this->db->error());
		}
	}
	
	/**
	 * Set the status of a message to read. If the status of the message
	 * is already read, than update updateamum
	 */
	public function setReadMessageStatusByToken($token)
	{
		$sql = 'SELECT r.message_id,
						m.person_id as sender_id,
						r.person_id as receiver_id,
						m.subject,
						m.body,
						m.insertamum,
						m.relationmessage_id,
						m.oe_kurzbz,
						s.status,
						s.statusinfo,
						s.insertamum as statusamum
				  FROM public.tbl_msg_recipient r JOIN public.tbl_msg_message m USING (message_id)
						JOIN (
							SELECT * FROM public.tbl_msg_status WHERE status < ? ORDER BY insertamum DESC, status DESC
						) s ON (r.message_id = s.message_id AND r.person_id = s.person_id)
				 WHERE r.token = ?
				 LIMIT 1';
		
		$msgs = $this->db->query($sql, array(MSG_STATUS_ARCHIVED, $token));
		
		// If no errors occurred
		if ($msgs)
		{
			// If at least a record is present
			if (count($msgs->result()) > 0)
			{
				$msg = $msgs->result()[0];
				
				$msgStatusResult = false; // pessimistic expectation
				
				// If the status of the message is unread
				if ($msg->status == MSG_STATUS_UNREAD)
				{
					// Insert the read status
					$msgStatusResult = $this->db->insert(
						'public.tbl_msg_status',
						array(
							'message_id' => $msg->message_id,
							'person_id' => $msg->receiver_id,
							'status' => MSG_STATUS_READ,
							'statusinfo' => $msg->statusinfo,
							'insertamum' => 'NOW()',
							'insertvon' => null,
							'updateamum' => 'NOW()',
							'updatevon' => null
						)
					);
				}
				// If the status of the message is read
				else if ($msg->status == MSG_STATUS_READ)
				{
					// Update updateamum to current date
					$this->db->set('updateamum', 'NOW()');
					
					$this->db->where('message_id', $msg->message_id);
					$this->db->where('person_id', $msg->receiver_id);
					$this->db->where('status', MSG_STATUS_READ);
					
					$msgStatusResult = $this->db->update('public.tbl_msg_status');
				}
				
				// If some of the previous DB manipulation (update or insert) has failed
				if (!$msgStatusResult)
				{
					return error($this->db->error());
				}
			}
			
			return success($msgs->result());
		}
		else
		{
			return error($this->db->error());
		}
		
		return success($result->result());
	}
	
	/**
	 * Get data of the message sender
	 */
	public function getSenderData($person_id)
	{
		$sql = 'SELECT p.vorname,
					   p.nachname,
					   p.anrede,
					   p.titelpost,
					   p.titelpre,
					   p.vornamen
				  FROM public.tbl_person p
				 WHERE p.person_id = ?';

		$result = $this->db->query($sql, array($person_id));
		
		// If no errors occurred
		if ($result)
		{
			return success($result->result());
		}
		else
		{
			return error($this->db->error());
		}
	}
}