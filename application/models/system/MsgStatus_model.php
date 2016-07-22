<?php 

if ( ! defined("BASEPATH")) exit("No direct script access allowed");

class MsgStatus_model extends DB_Model
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'public.tbl_msg_status';
		$this->pk = array('message_id', 'person_id');
		$this->hasSequence = false;
	}
	
	/**
	 * getMessages
	 * 
	 * Gets all the messages to be sent
	 * 
	 * @param kontaktType specifies the type of the kontakt to get
	 * @param messageStatus specifies the status of the messages to get
	 * @param limit specifies the number of messages to get
	 */
	public function getMessages($kontaktType, $messageStatus, $limit = null)
	{
		// Check wrights
		if (! $this->fhc_db_acl->isBerechtigt($this->getBerechtigungKurzbz('public.tbl_msg_recipient'), 's'))
			return $this->_error(lang('fhc_'.FHC_NORIGHT).' -> '.$this->getBerechtigungKurzbz('public.tbl_msg_recipient'), FHC_MODEL_ERROR);
		if (! $this->fhc_db_acl->isBerechtigt($this->getBerechtigungKurzbz('public.tbl_msg_message'), 's'))
			return $this->_error(lang('fhc_'.FHC_NORIGHT).' -> '.$this->getBerechtigungKurzbz('public.tbl_msg_message'), FHC_MODEL_ERROR);
		if (! $this->fhc_db_acl->isBerechtigt($this->getBerechtigungKurzbz('public.tbl_msg_status'), 's'))
			return $this->_error(lang('fhc_'.FHC_NORIGHT).' -> '.$this->getBerechtigungKurzbz('public.tbl_msg_status'), FHC_MODEL_ERROR);
		if (! $this->fhc_db_acl->isBerechtigt($this->getBerechtigungKurzbz('public.tbl_kontakt'), 's'))
			return $this->_error(lang('fhc_'.FHC_NORIGHT).' -> '.$this->getBerechtigungKurzbz('public.tbl_kontakt'), FHC_MODEL_ERROR);
		
		$query = "SELECT ms.message_id,
						 ks.kontakt as sender,
						 kr.kontakt as receiver,
						 ms.person_id,
						 mm.subject,
						 mm.body
					FROM public.tbl_msg_status ms INNER JOIN public.tbl_msg_recipient mr USING (message_id)
						INNER JOIN public.tbl_msg_message mm USING (message_id)
						LEFT JOIN (
							SELECT person_id, kontakt FROM public.tbl_kontakt WHERE kontakttyp = ?
						) ks ON (ks.person_id = mm.person_id)
						LEFT JOIN (
							SELECT person_id, kontakt FROM public.tbl_kontakt WHERE kontakttyp = ?
						) kr ON (kr.person_id = mr.person_id)
					WHERE ms.status = ?";
		
		$parametersArray = array($kontaktType, $kontaktType, $messageStatus);
		
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