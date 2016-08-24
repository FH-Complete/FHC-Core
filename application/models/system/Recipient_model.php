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
		// Check rights
		if (! $this->fhc_db_acl->isBerechtigt($this->getBerechtigungKurzbz("public.tbl_msg_recipient"), "s"))
			return $this->_error(lang("fhc_".FHC_NORIGHT)." -> ".$this->getBerechtigungKurzbz("public.tbl_msg_recipient"), FHC_MODEL_ERROR);
		if (! $this->fhc_db_acl->isBerechtigt($this->getBerechtigungKurzbz("public.tbl_msg_message"), "s"))
			return $this->_error(lang("fhc_".FHC_NORIGHT)." -> ".$this->getBerechtigungKurzbz("public.tbl_msg_message"), FHC_MODEL_ERROR);
		if (! $this->fhc_db_acl->isBerechtigt($this->getBerechtigungKurzbz("public.tbl_kontakt"), "s"))
			return $this->_error(lang("fhc_".FHC_NORIGHT)." -> ".$this->getBerechtigungKurzbz("public.tbl_kontakt"), FHC_MODEL_ERROR);
	
		$query = "SELECT mm.message_id,
						 ks.kontakt as sender,
						 kr.kontakt as receiver,
						 mr.person_id as receiver_id,
						 mm.subject,
						 mm.body,
						 mr.sentinfo
					FROM public.tbl_msg_recipient mr INNER JOIN public.tbl_msg_message mm USING (message_id)
						LEFT JOIN (
							SELECT person_id, kontakt FROM public.tbl_kontakt WHERE kontakttyp = ?
						) ks ON (ks.person_id = mm.person_id)
						LEFT JOIN (
							SELECT person_id, kontakt FROM public.tbl_kontakt WHERE kontakttyp = ?
						) kr ON (kr.person_id = mr.person_id)";
		
		$parametersArray = array($kontaktType, $kontaktType);
		
		if (is_null($sent) || $sent == "")
		{
			$query .= " WHERE mr.sent IS NULL";
		}
		else
		{
			array_push($parametersArray, $sent);
			$query .= " WHERE mr.sent = ?";
		}
		
		if (!is_null($message_id))
		{
			array_push($parametersArray, $message_id);
			$query .= " AND mm.message_id = ?";
		}
		
		$query .= " ORDER BY mr.insertamum ASC";
		
		if (!is_null($limit))
		{
			$query .= " LIMIT ?";
			array_push($parametersArray, $limit);
		}
		
		// Get data of the messages to sent
		$result = $this->db->query($query, $parametersArray);
		if (is_object($result))
			return $this->_success($result->result());
		else
			return $this->_error($this->db->error(), FHC_DB_ERROR);
	}
}
