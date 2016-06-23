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
		//require_once APPPATH.'config/message.php';
		$this->dbTable = 'public.tbl_msg_message';
		$this->pk = 'message_id';
	}
	
	public function getMessagesByUID($uid, $all)
	{
		// Check wrights
		// @ToDo: Define the special wright for reading own messages "basis/message:own"
		// if same user
		if ($uid === $this->getUID())
		{
			if (! $this->fhc_db_acl->isBerechtigt('basis/message', 's'))
				return $this->_error(lang('fhc_'.FHC_NORIGHT).' -> basis/message', FHC_MODEL_ERROR);
		}
		// if different user, for reading messages from other users
		else
		{
			if (! $this->fhc_db_acl->isBerechtigt('basis/message', 's'))
				return $this->_error(lang('fhc_'.FHC_NORIGHT).' -> basis/message:all', FHC_MODEL_ERROR);
		}

		// get Data
		$sql = 'SELECT uid,
						person_id,
						message_id,
						subject,
						body,
						priority,
						relationmessage_id,
						oe_kurzbz,
						m.insertamum,
						anrede,
						titelpost,
						titelpre,
						nachname,
						vorname,
						vornamen,
						status,
						statusinfo,
						s.insertamum AS statusamum
				  FROM public.tbl_msg_message m JOIN public.tbl_person USING (person_id)
						JOIN public.tbl_benutzer USING (person_id)
						LEFT OUTER JOIN (
							SELECT message_id, person_id, status, statusinfo, tbl_msg_status.insertamum
							  FROM public.tbl_msg_status INNER JOIN	(
										SELECT message_id, person_id, max(insertamum) AS insertamum
										  FROM public.tbl_msg_status
									  GROUP BY message_id, person_id
									) status USING (message_id, person_id)
							 WHERE tbl_msg_status.insertamum=status.insertamum
						) s USING (message_id, person_id)
				 WHERE uid = ?';
		
		if (! $all)
			$sql .= ' AND (status < 3 OR status IS NULL)';
		$result = $this->db->query($sql, array($uid));
		if (is_object($result))
			return $this->_success($result->result());
		else
			return $this->_error($this->db->error(), FHC_DB_ERROR);
	}

public function getMessagesByPerson($person_id, $all)
	{
		// Check wrights
		if (! $this->fhc_db_acl->isBerechtigt('basis/message', 's'))
			return $this->_error(lang('fhc_'.FHC_NORIGHT).' -> basis/message', FHC_MODEL_ERROR);

		// prepare parameters
		$person_id = (int)$person_id;
		// get Data
		$sql = 'SELECT person_id,
						message_id,
						subject,
						body,
						priority,
						relationmessage_id,
						oe_kurzbz,
						m.insertamum,
						anrede,
						titelpost,
						titelpre,
						nachname,
						vorname,
						vornamen,
						status,
						statusinfo,
						s.insertamum AS statusamum
				  FROM public.tbl_msg_message m JOIN public.tbl_person USING (person_id)
						LEFT OUTER JOIN (
							SELECT message_id, person_id, status, statusinfo, tbl_msg_status.insertamum
							  FROM public.tbl_msg_status INNER JOIN (
										SELECT message_id, person_id, max(insertamum) AS insertamum
										  FROM public.tbl_msg_status
									  GROUP BY message_id, person_id
									) status USING (message_id, person_id)
							 WHERE tbl_msg_status.insertamum=status.insertamum
						) s USING (message_id, person_id)
				 WHERE person_id = ?';
		
		if (! $all)
			$sql .= ' AND (status < 3 OR status IS NULL)';
		$result = $this->db->query($sql, array($person_id));
		//var_dump($result);
		if (is_object($result))
			return $this->_success($result->result());
		else
			return $this->_error($this->db->error(), FHC_DB_ERROR);
	}
}
