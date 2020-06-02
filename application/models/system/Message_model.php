<?php

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Message_model extends DB_Model
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'public.tbl_msg_message';
		$this->pk = 'message_id';
	}

	/**
	 * Get all sent messages from a person identified by person_id
	 */
	public function getMessagesByPerson($person_id, $oe_kurzbz, $all)
	{
		$sql = 'SELECT m.message_id,
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
				  FROM public.tbl_msg_message m JOIN public.tbl_person p ON (p.person_id = m.person_id)
						LEFT JOIN (
							SELECT message_id, person_id, status, statusinfo, insertamum
							  FROM public.tbl_msg_status
							 %s
						  ORDER BY insertamum DESC
						) s ON (m.message_id = s.message_id AND m.person_id = s.person_id)
				 WHERE m.person_id = ?';

		$parametersArray = array($person_id);

		if ($all == 'true')
		{
			$sql = sprintf($sql, '');
		}
		else
		{
			$sql = sprintf($sql, 'WHERE status >= 3');
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
	 * Gets massages with a person being sender OR receiver.
	 * @param $person_id
	 * @param null $status message status. by default, latest status is returned
	 * @return array|null
	 */
	public function getMessagesOfPerson($person_id, $status = null)
	{
		$sql = 'SELECT m.message_id,
						m.person_id,
						m.subject,
						m.body,
						m.priority,
						m.insertamum,
						m.relationmessage_id,
						m.oe_kurzbz,
						se.person_id AS sepersonid,
						se.anrede AS seanrede,
						se.titelpost AS setitelpost,
						se.titelpre AS setitelpre,
						se.nachname AS senachname,
						se.vorname AS sevorname,
						se.vornamen AS sevornamen,
						re.person_id AS repersonid,
						re.anrede AS reanrede,
						re.titelpost AS retitelpost,
						re.titelpre AS retitelpre,
						re.nachname AS renachname,
						re.vorname AS revorname,
						re.vornamen AS revornamen,
						s.status,
						s.statusinfo,
						s.insertamum AS statusamum
				  FROM public.tbl_msg_message m
						JOIN public.tbl_msg_recipient r ON m.message_id = r.message_id
						JOIN public.tbl_person se ON (m.person_id = se.person_id)
						JOIN public.tbl_person re ON (r.person_id = re.person_id)
						LEFT JOIN (
							SELECT message_id, person_id, status, statusinfo, insertamum
							  FROM public.tbl_msg_status
							  %s
						  ORDER BY insertamum DESC
						  ) s ON (m.message_id = s.message_id AND re.person_id = s.person_id)
				 WHERE se.person_id = ?
				 OR re.person_id = ?
				 ';

		if (is_numeric($status))
		{
			$sql = sprintf($sql, 'WHERE status = '.$status);
		}
		else
		{
			$sql = sprintf($sql, '');
		}

		$parametersArray = array($person_id, $person_id);

		return $this->execQuery($sql, $parametersArray);
	}

	/**
	 * getMessageVars
	 */
	public function getMessageVars()
	{
		$result = $this->db->query('SELECT * FROM public.vw_msg_vars WHERE 0 = 1');

		if ($result)
		{
			return success($result->list_fields());
		}
		else
		{
			return error($this->db->error(), FHC_DB_ERROR);
		}
	}

	/**
	 * getMessageVars for person
	 */
	public function getMessageVarsPerson()
	{
		$result = $this->db->query('SELECT * FROM public.vw_msg_vars_person WHERE 0 = 1');

		if ($result)
		{
			return success($result->list_fields());
		}
		else
		{
			return error($this->db->error(), FHC_DB_ERROR);
		}
	}
	
	/**
	 * Get message variables for logged in user
	 */
	public function getMsgVarsLoggedInUser()
	{
		$result = $this->db->query('SELECT * FROM public.vw_msg_vars_user WHERE 0 = 1');
		
		if ($result)
		{
			return success($result->list_fields());
		}
		else
		{
			return error($this->db->error(), FHC_DB_ERROR);
		}
	}

	/**
	 * getMsgVarsDataByPrestudentId
	 */
	public function getMsgVarsDataByPrestudentId($prestudent_id)
	{
		$query = 'SELECT * FROM public.vw_msg_vars WHERE prestudent_id %s ?';

		return $this->execQuery(sprintf($query, is_array($prestudent_id) ? 'IN' : '='), array($prestudent_id));
	}

	/**
	 * getMsgVarsDataByPersonId
	 */
	public function getMsgVarsDataByPersonId($person_id)
	{
		$query = 'SELECT * FROM public.vw_msg_vars_person WHERE person_id %s ?';

		return $this->execQuery(sprintf($query, is_array($person_id) ? 'IN' : '='), array($person_id));
	}
	
	/**
	 * Get message vars data for logged in user
	 * @param string uid The UID should ONLY be passed if this method is called by a cronjob.
	 * This is to enable jobs to use templates which use logged-in-user fields ('Eigene Felder').
	 * @return array|null
	 */
	public function getMsgVarsDataByLoggedInUser($uid = null)
	{
		if (is_string($uid))
		{
			$params = array($uid);
		}
		else
		{
			$params = array(getAuthUID());
		}
		
		$query = 'SELECT * FROM public.vw_msg_vars_user WHERE my_uid = ?';
		
		return $this->execQuery($query, $params);
	}
}
