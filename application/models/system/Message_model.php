<?php 

if ( ! defined("BASEPATH")) exit("No direct script access allowed");

class Message_model extends DB_Model
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = "public.tbl_msg_message";
		$this->pk = "message_id";
	}
	
	public function getMessagesByUID($uid, $all)
	{
		// Check wrights
		// @ToDo: Define the special wright for reading own messages "basis/message:own"
		// if same user
		if ($uid === getAuthUID())
		{
			if (! $this->fhc_db_acl->isBerechtigt("basis/message", "s"))
				return $this->_error(lang("fhc_".FHC_NORIGHT)." -> basis/message", FHC_MODEL_ERROR);
		}
		// if different user, for reading messages from other users
		else
		{
			if (! $this->fhc_db_acl->isBerechtigt("basis/message", "s"))
				return $this->_error(lang("fhc_".FHC_NORIGHT)." -> basis/message:all", FHC_MODEL_ERROR);
		}

		// get Data
		$sql = "SELECT b.uid,
						m.person_id,
						m.message_id,
						m.subject,
						m.body,
						m.priority,
						m.relationmessage_id,
						m.oe_kurzbz,
						m.insertamum,
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
							SELECT * FROM public.tbl_msg_status ORDER BY insertamum DESC LIMIT 1
						) s ON (r.message_id = s.message_id AND r.person_id = s.person_id)
				 WHERE b.uid = ?";
		
		if (! $all)
			$sql .= " AND (status < 3 OR status IS NULL)";
		
		$result = $this->db->query($sql, array($uid));
		if (is_object($result))
			return $this->_success($result->result());
		else
			return $this->_error($this->db->error(), FHC_DB_ERROR);
	}

	public function getMessagesByPerson($person_id, $all)
	{
		// Check wrights
		if (! $this->fhc_db_acl->isBerechtigt($this->getBerechtigungKurzbz("public.tbl_msg_recipient"), "s"))
			return $this->_error(lang("fhc_".FHC_NORIGHT)." -> ".$this->getBerechtigungKurzbz("public.tbl_msg_recipient"), FHC_MODEL_ERROR);
		if (! $this->fhc_db_acl->isBerechtigt($this->getBerechtigungKurzbz("public.tbl_msg_message"), "s"))
			return $this->_error(lang("fhc_".FHC_NORIGHT)." -> ".$this->getBerechtigungKurzbz("public.tbl_msg_message"), FHC_MODEL_ERROR);
		if (! $this->fhc_db_acl->isBerechtigt($this->getBerechtigungKurzbz("public.tbl_person"), "s"))
			return $this->_error(lang("fhc_".FHC_NORIGHT)." -> ".$this->getBerechtigungKurzbz("public.tbl_person"), FHC_MODEL_ERROR);
		if (! $this->fhc_db_acl->isBerechtigt($this->getBerechtigungKurzbz("public.tbl_msg_status"), "s"))
			return $this->_error(lang("fhc_".FHC_NORIGHT)." -> ".$this->getBerechtigungKurzbz("public.tbl_msg_status"), FHC_MODEL_ERROR);
		
		$sql = "SELECT r.message_id,
						m.person_id,
						m.subject,
						m.body,
						m.insertamum,
						m.relationmessage_id,
						m.oe_kurzbz,
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
				 WHERE r.person_id = ?";
		
		$parametersArray = array($person_id);
		
		if ($all == "true")
		{
			$sql = sprintf($sql, "");
		}
		else
		{
			array_push($parametersArray, $person_id, $person_id);
			$sql = sprintf($sql, "WHERE person_id = ? AND message_id NOT IN (SELECT message_id FROM public.tbl_msg_status WHERE status >= 3 AND person_id = ?)");
		}
		
		$result = $this->db->query($sql, $parametersArray);
		if (is_object($result))
			return $this->_success($result->result());
		else
			return $this->_error($this->db->error(), FHC_DB_ERROR);
	}
	
	public function getMessagesByToken($token)
	{
		// Check wrights
		if (! $this->fhc_db_acl->isBerechtigt($this->getBerechtigungKurzbz("public.tbl_msg_recipient"), "s"))
			return $this->_error(lang("fhc_".FHC_NORIGHT)." -> ".$this->getBerechtigungKurzbz("public.tbl_msg_recipient"), FHC_MODEL_ERROR);
		if (! $this->fhc_db_acl->isBerechtigt($this->getBerechtigungKurzbz("public.tbl_msg_message"), "s"))
			return $this->_error(lang("fhc_".FHC_NORIGHT)." -> ".$this->getBerechtigungKurzbz("public.tbl_msg_message"), FHC_MODEL_ERROR);
		if (! $this->fhc_db_acl->isBerechtigt($this->getBerechtigungKurzbz("public.tbl_msg_status"), "s"))
			return $this->_error(lang("fhc_".FHC_NORIGHT)." -> ".$this->getBerechtigungKurzbz("public.tbl_msg_status"), FHC_MODEL_ERROR);
		
		$sql = "SELECT r.message_id,
						r.person_id as receiver_id,
						m.person_id as sender_id,
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
							SELECT * FROM public.tbl_msg_status ORDER BY insertamum DESC LIMIT 1
						) s ON (r.message_id = s.message_id AND r.person_id = s.person_id)
				 WHERE r.token = ?
				   AND status < ?
			  ORDER BY s.insertamum DESC";
		
		$result = $this->db->query($sql, array($token, MSG_STATUS_DELETED));
		if (is_object($result))
			return $this->_success($result->result());
		else
			return $this->_error($this->db->error(), FHC_DB_ERROR);
	}
}