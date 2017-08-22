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
	 * getPhrases
	 */
	public function getPhrases($app, $sprache, $phrase = null, $orgeinheit_kurzbz = null, $orgform_kurzbz = null)
	{
		// Checks if the operation is permitted by the API caller
		if (isError($ent = $this->isEntitled('system.tbl_phrase', PermissionLib::SELECT_RIGHT, FHC_NORIGHT, FHC_MODEL_ERROR)))
			return $ent;
		if (isError($ent = $this->isEntitled('system.tbl_phrasentext', PermissionLib::SELECT_RIGHT, FHC_NORIGHT, FHC_MODEL_ERROR)))
			return $ent;
		
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
		
		return $this->execQuery($query, $parametersArray);
	}
}
