<?php

class Recipient_model extends DB_Model
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'public.tbl_msg_recipient';
		$this->pk = array('person_id', 'message_id');
		$this->hasSequence = false;
	}

	/**
	 * Get data for a received message
	 */
	public function getMessage($message_id, $person_id)
	{
		$query = 'SELECT mr.message_id,
						 mr.person_id,
						 mm.subject,
						 mm.body,
						 ks.kontakt,
						 p.nachname,
						 p.vorname,
						 b.uid
					FROM public.tbl_msg_recipient mr INNER JOIN public.tbl_msg_message mm USING (message_id)
						INNER JOIN public.tbl_person p ON (mm.person_id = p.person_id)
						LEFT JOIN public.tbl_benutzer b ON (mr.person_id = b.person_id)
						LEFT JOIN (
							SELECT person_id, kontakt FROM public.tbl_kontakt WHERE kontakttyp = \'email\'
						) ks ON (ks.person_id = mr.person_id)
				   WHERE mr.message_id = ? AND mr.person_id = ?';

		$parametersArray = array($message_id, $person_id);

		// Get data of the messages to sent
		return $this->execQuery($query, $parametersArray);
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
	 * Get all received messages for a person identified by person_id
	 */
	public function getMessagesByPerson($person_id, $oe_kurzbz, $all)
	{
		$sql = 'SELECT DISTINCT ON (r.message_id) r.message_id,
						m.person_id,
						m.subject,
						m.body,
						m.priority,
						m.insertamum,
						m.relationmessage_id,
						m.oe_kurzbz,
						p.anrede,
						p.titelpost,
						p.titelpre,
						p.nachname,
						p.vorname,
						p.vornamen,
						s.status,
						s.statusinfo,
						s.insertamum AS statusamum
				  FROM public.tbl_msg_recipient r JOIN public.tbl_msg_message m USING (message_id)
						JOIN public.tbl_person p ON (p.person_id = m.person_id)
						JOIN (
							SELECT message_id, person_id, status, statusinfo, insertamum
							  FROM public.tbl_msg_status
							 %s
						  ORDER BY insertamum DESC
						) s ON (m.message_id = s.message_id AND r.person_id = s.person_id)
				 WHERE r.person_id = ?';

		$parametersArray = array($person_id);

		if ($all == 'true')
		{
			$sql = sprintf($sql, '');
		}
		else
		{
			array_push($parametersArray, $person_id, $person_id);
			$sql = sprintf($sql, 'WHERE person_id = ? AND message_id NOT IN (SELECT message_id FROM public.tbl_msg_status WHERE status >= 3 AND person_id = ?)');
		}

		if ($oe_kurzbz != null)
		{
			array_push($parametersArray, $oe_kurzbz);
			$sql .= ' AND m.oe_kurzbz IN (
						WITH RECURSIVE organizations(_pk, _ppk) AS
							(
								SELECT o.oe_kurzbz, o.oe_parent_kurzbz
								  FROM public.tbl_organisationseinheit o
								 WHERE o.oe_kurzbz = ?
							 UNION ALL
								SELECT o.oe_kurzbz, o.oe_parent_kurzbz
								  FROM public.tbl_organisationseinheit o INNER JOIN organizations orgs ON (o.oe_parent_kurzbz = orgs._pk)
							)
							SELECT orgs._pk
							FROM organizations orgs
						)';
		}

		$sql .= ' ORDER BY r.message_id DESC, s.status DESC';

		return $this->execQuery($sql, $parametersArray);
	}

	/**
	 * Get all received messages for a person identified by uid
	 */
	public function getMessagesByUID($uid, $oe_kurzbz, $all)
	{
		// get Data
		$sql = 'SELECT DISTINCT ON (r.message_id) r.message_id,
						m.person_id,
						m.subject,
						m.body,
						m.priority,
						m.insertamum,
						m.relationmessage_id,
						m.oe_kurzbz,
						p.anrede,
						p.titelpost,
						p.titelpre,
						p.nachname,
						p.vorname,
						p.vornamen,
						s.status,
						s.statusinfo,
						s.insertamum AS statusamum
				  FROM public.tbl_msg_recipient r JOIN public.tbl_msg_message m USING (message_id)
						JOIN public.tbl_person p ON (r.person_id = p.person_id)
						JOIN public.tbl_benutzer b ON (r.person_id = b.person_id)
						JOIN (
							SELECT message_id, person_id, status, statusinfo, insertamum
							  FROM public.tbl_msg_status
						  ORDER BY insertamum DESC
						) s ON (m.message_id = s.message_id AND r.person_id = s.person_id)
				 WHERE b.uid = ?';

		$parametersArray = array($uid);

		if ($all == 'true')
		{
			$sql .= ' AND (status < 3 OR status IS NULL)';
		}

		if ($oe_kurzbz != null)
		{
			array_push($parametersArray, $oe_kurzbz);
			$sql .= ' AND m.oe_kurzbz IN (
						WITH RECURSIVE organizations(_pk, _ppk) AS
							(
								SELECT o.oe_kurzbz, o.oe_parent_kurzbz
								  FROM public.tbl_organisationseinheit o
								 WHERE o.oe_kurzbz = ?
							 UNION ALL
								SELECT o.oe_kurzbz, o.oe_parent_kurzbz
								  FROM public.tbl_organisationseinheit o INNER JOIN organizations orgs ON (o.oe_parent_kurzbz = orgs._pk)
							)
							SELECT orgs._pk
							FROM organizations orgs
						)';
		}

		return $this->execQuery($sql, $parametersArray);
	}

	/**
	 * getMessages
	 *
	 * Gets all the messages to be sent
	 *
	 * @param kontaktType specifies the type of the kontakt to get
	 * @param sent specifies the status of the messages to get (NULL never sent, otherwise the shipping date)
	 * @param limit specifies the number of messages to get
	 * @param message_id specifies a single message
	 */
	public function getMessages($kontaktType, $sent, $limit = null, $message_id = null)
	{
		$query = 'SELECT mm.message_id,
						 ks.kontakt as sender,
						 kr.kontakt as receiver,
						 mu.mitarbeiter_uid as employeeContact,
						 ms.mitarbeiter_uid as senderemployeeContact,
						 mr.person_id as receiver_id,
						 mr.token,
						 mm.subject,
						 mm.body,
						 mr.sentinfo
					FROM public.tbl_msg_recipient mr INNER JOIN public.tbl_msg_message mm USING (message_id)
						LEFT JOIN (
							SELECT person_id, kontakt FROM public.tbl_kontakt WHERE kontakttyp = ?
						) ks ON (ks.person_id = mm.person_id)
						LEFT JOIN (
							SELECT person_id, kontakt FROM public.tbl_kontakt WHERE kontakttyp = ?
						) kr ON (kr.person_id = mr.person_id)
						LEFT JOIN (
							SELECT b.person_id,
								   m.mitarbeiter_uid
							  FROM public.tbl_benutzer b INNER JOIN public.tbl_mitarbeiter m ON(b.uid = m.mitarbeiter_uid)
							 WHERE b.aktiv = TRUE
						) mu ON (mu.person_id = mr.person_id)
						LEFT JOIN (
							SELECT b.person_id,
								   m.mitarbeiter_uid
							  FROM public.tbl_benutzer b INNER JOIN public.tbl_mitarbeiter m ON(b.uid = m.mitarbeiter_uid)
							 WHERE b.aktiv = TRUE
						) ms ON (ms.person_id = mm.person_id)';

		$parametersArray = array($kontaktType, $kontaktType);

		if (is_null($sent) || $sent == '')
		{
			$query .= ' WHERE mr.sent IS NULL';
		}
		else
		{
			array_push($parametersArray, $sent);
			$query .= ' WHERE mr.sent = ?';
		}

		if (!is_null($message_id))
		{
			array_push($parametersArray, $message_id);
			$query .= ' AND mm.message_id = ?';
		}

		$query .= ' ORDER BY mr.insertamum ASC';

		if (!is_null($limit))
		{
			$query .= ' LIMIT ?';
			array_push($parametersArray, $limit);
		}

		return $this->execQuery($query, $parametersArray);
	}

	/**
	 * Get all unread messages for a person identified by person_id
	 */
	public function getCountUnreadMessages($person_id, $oe_kurzbz)
	{
		$sql = 'SELECT COUNT(r.message_id) AS unreadMessages
				  FROM public.tbl_msg_recipient r
				  JOIN public.tbl_msg_status s ON (r.message_id = s.message_id AND r.person_id = s.person_id)
				  JOIN public.tbl_msg_message m ON (r.message_id = m.message_id)
				 WHERE r.person_id = ?
				   AND s.status = ?
				   AND r.message_id NOT IN (
							SELECT r.message_id
							  FROM public.tbl_msg_recipient r JOIN public.tbl_msg_status s
								ON (r.message_id = s.message_id AND r.person_id = s.person_id)
							 WHERE r.person_id = ?
							   AND s.status > ?
						)';

		$parametersArray = array($person_id, MSG_STATUS_UNREAD, $person_id, MSG_STATUS_UNREAD);

		if ($oe_kurzbz != null)
		{
			array_push($parametersArray, $oe_kurzbz);
			$sql .= ' AND m.oe_kurzbz IN (
						WITH RECURSIVE organizations(_pk, _ppk) AS
							(
								SELECT o.oe_kurzbz, o.oe_parent_kurzbz
								  FROM public.tbl_organisationseinheit o
								 WHERE o.oe_kurzbz = ?
							 UNION ALL
								SELECT o.oe_kurzbz, o.oe_parent_kurzbz
								  FROM public.tbl_organisationseinheit o INNER JOIN organizations orgs ON (o.oe_parent_kurzbz = orgs._pk)
							)
							SELECT orgs._pk
							FROM organizations orgs
						)';
		}

		return $this->execQuery($sql, $parametersArray);
	}
}
