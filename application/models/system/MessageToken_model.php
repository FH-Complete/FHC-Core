<?php

class MessageToken_model extends DB_Model
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();

		// Loads message configuration
		$this->config->load('message');
	}

	/**
	 * Get a received message identified by token
	 */
	public function getMessageByToken($token)
	{
		$sql = 'SELECT r.message_id,
						m.person_id as sender_id,
						r.person_id as receiver_id,
						r.sent,
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

		return $this->execQuery($sql, array(MSG_STATUS_DELETED, $token));
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

		$msgsResult = $this->execQuery($sql, array(MSG_STATUS_ARCHIVED, $token));

		// If no errors occurred
		if (isSuccess($msgsResult))
		{
			// If at least a record is present
			if (hasData($msgsResult))
			{
				$msg = getData($msgsResult)[0];
				$msgStatusResult = error();

				$this->load->model('system/MsgStatus_model', 'MsgStatusModel');

				// If the status of the message is unread
				if ($msg->status == MSG_STATUS_UNREAD)
				{
					// Insert the read status
					$msgStatusResult = $this->MsgStatusModel->insert(
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
					$msgStatusResult = $this->MsgStatusModel->update(
						array(
							'message_id' => $msg->message_id,
							'person_id' => $msg->receiver_id,
							'status' => MSG_STATUS_READ
						),
						array('updateamum' => 'NOW()')
					);
				}

				return $msgStatusResult;
			}
		}
		else
		{
			return $msgsResult;
		}
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

		return $this->execQuery($sql, array($person_id));
	}

	/**
	 * Searchs for a person by its person_id and checks if it is an employee
	 */
	public function isEmployee($person_id)
	{
		$sql = 'SELECT m.mitarbeiter_uid
				  FROM public.tbl_person p
			 	  JOIN public.tbl_benutzer b USING(person_id)
			 	  JOIN public.tbl_mitarbeiter m ON(b.uid = m.mitarbeiter_uid)
				 WHERE p.person_id = ?
				   AND b.aktiv = TRUE';

		return $this->execQuery($sql, array($person_id));
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

		return $this->execQuery($sql, array($oe_kurzbz));
	}

	/**
	 *
	 */
	public function crossClientData($token, $relationmessage_id, $receiver_id)
	{
		$sql = 'SELECT mm.message_id
			  FROM public.tbl_msg_message mm
			  JOIN public.tbl_msg_recipient mr USING(message_id)
			 WHERE mr.token = ?
			   AND mm.message_id = ?
			   AND mm.person_id = ?';

		return $this->execQuery($sql, array($token, $relationmessage_id, $receiver_id));
	}
}

