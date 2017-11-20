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
	 * is already read then update updateamum
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
					   p.vornamen,
					   m.mitarbeiter_uid
				  FROM public.tbl_person p
			 LEFT JOIN public.tbl_benutzer b USING(person_id)
			 LEFT JOIN public.tbl_mitarbeiter m ON(b.uid = m.mitarbeiter_uid)
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

	/**
	 *
	 */
	public function isEmployee($person_id)
	{
		$sql = 'SELECT m.mitarbeiter_uid
				  FROM public.tbl_person p
			 LEFT JOIN public.tbl_benutzer b USING(person_id)
			 LEFT JOIN public.tbl_mitarbeiter m ON(b.uid = m.mitarbeiter_uid)
				 WHERE p.person_id = ?
				   AND b.aktiv = TRUE';

		$result = $this->db->query($sql, array($person_id));

		// If no errors occurred
		if ($result)
		{
			// If data are present
			if (is_array($result->result()) && count($result->result()) > 0)
			{
				$person = $result->result()[0];

				// If it is an employee
				if ($person->mitarbeiter_uid != null)
				{
					return true;
				}
			}

			return false;
		}
		else
		{
			return error($this->db->error());
		}
	}

	/**
	 * getRoot - Get the root of the organisation unit tree which belongs the given organisation unit parameter
	 */
	public function getOERoot($oe_kurzbz)
	{
		$sql = '
			WITH RECURSIVE organizations(rid, oe_kurzbz, oe_parent_kurzbz) AS
			(
				SELECT 1 AS rid, o.oe_kurzbz, o.oe_parent_kurzbz
	  			  FROM public.tbl_organisationseinheit o
				 WHERE o.oe_kurzbz = ?
				UNION ALL
				SELECT rid + 1 AS rid, oe.oe_kurzbz, oe.oe_parent_kurzbz
	  			  FROM organizations org, public.tbl_organisationseinheit oe
				 WHERE oe.oe_kurzbz = org.oe_parent_kurzbz
          		   AND oe.aktiv = TRUE
			)
			SELECT oe_kurzbz
			  FROM organizations orgs
		  ORDER BY rid DESC
		     LIMIT 1
		';

		$result = $this->db->query($sql, array($oe_kurzbz));
		if ($result) // If no errors occurred
		{
			// If data are present
			if (is_array($result->result())
				&& count($result->result()) > 0
				&& is_object($result->result()[0])
				&& isset($result->result()[0]->oe_kurzbz))
			{
				return success($result->result()[0]->oe_kurzbz);
			}
			else
			{
				return error();
			}
		}
		else
		{
			return error($this->db->error());
		}

		return $result;
	}
}
