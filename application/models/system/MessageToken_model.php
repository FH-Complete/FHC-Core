<?php

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

		return success($result->result());
	}
}
