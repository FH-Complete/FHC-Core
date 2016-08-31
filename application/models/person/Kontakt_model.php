<?php

class Kontakt_model extends DB_Model
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = "public.tbl_kontakt";
		$this->pk = "kontakt_id";
	}
	
	public function getWholeKontakt($kontakt_id, $person_id = null, $kontakttyp = null)
    {
		$result = null;
		
		$this->addJoin("public.tbl_standort", "standort_id", "LEFT");
		$this->addJoin("public.tbl_firma", "firma_id", "LEFT");
		
		if (isset($kontakt_id))
		{
			$result = $this->load($kontakt_id);
		}
		else
		{
			$parametersArray = array();
			
			if (!is_null($person_id))
			{
				$parametersArray["person_id"] = $person_id;
			}
			if (!is_null($kontakttyp))
			{
				$parametersArray["kontakttyp"] = $kontakttyp;
			}
			
			if (count($parametersArray) > 0)
			{
				$result = $this->loadWhere($parametersArray);
			}
		}
		
		return $result;
    }
}