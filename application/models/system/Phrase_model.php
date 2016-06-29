<?php

class Phrase_model extends DB_Model
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'system.tbl_phrase';
		$this->pk = 'phrase_id';
	}

	/**
	 * 
	 */
	public function getPhrases($app, $sprache, $phrase = null, $orgeinheit_kurzbz = null, $orgform_kurzbz = null)
	{
		// Checks if the operation is permitted by the API caller
		if (! $this->fhc_db_acl->isBerechtigt($this->acl['system.tbl_phrase'], 's'))
			return $this->_error(lang('fhc_'.FHC_NORIGHT).' -> '.$this->acl['system.tbl_phrase'], FHC_MODEL_ERROR);
		if (! $this->fhc_db_acl->isBerechtigt($this->acl['system.tbl_phrasentext'], 's'))
			return $this->_error(lang('fhc_'.FHC_NORIGHT).' -> '.$this->acl['system.tbl_phrasentext'], FHC_MODEL_ERROR);
		
		$parametersArray = array('app' => $app, 'sprache' => $sprache);

		$query = 'SELECT phrase,
						 sprache,
						 orgeinheit_kurzbz,
						 orgform_kurzbz,
						 text
					FROM system.tbl_phrase JOIN system.tbl_phrasentext USING (phrase_id)
				   WHERE app = ? AND sprache = ?';

		if (isset($phrase))
		{
			$parametersArray['phrase'] = $phrase;
			
			if (is_array($phrase))
			{
				$query .= ' AND phrase IN ?';
			}
			else
			{
				$query .= ' AND phrase = ?';
			}
		}

		if (isset($orgeinheit_kurzbz))
		{
			$parametersArray['orgeinheit_kurzbz'] = $orgeinheit_kurzbz;
			$query .= ' AND orgeinheit_kurzbz = ?';
		}
		if (isset($orgform_kurzbz))
		{
			$parametersArray['orgform_kurzbz'] = $orgform_kurzbz;
			$query .= ' AND orgform_kurzbz = ?';
		}

		$result = $this->db->query($query, $parametersArray);
		
		if (is_object($result))
			return $this->_success($result->result());
		else
			return $this->_error($this->db->error(), FHC_DB_ERROR);
	}
}
