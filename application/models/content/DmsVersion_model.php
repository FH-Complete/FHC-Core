<?php

class DmsVersion_model extends DB_Model
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'campus.tbl_dms_version';
		$this->pk = array('dms_id', 'version');
		$this->hasSequence = false;
	}
	
	/**
	 * 
	 */
	public function filterFields($dms, $dms_id = null, $fileName = null)
	{
		$fieldsArray = array(
			'version',
			'mimetype',
			'name',
			'beschreibung',
			'letzterzugriff',
			'insertamum',
			'insertvon',
			'updateamum',
			'updatevon'
		);
		$returnArray = array();
		
		foreach ($fieldsArray as $value)
		{
			if (isset($dms[$value]))
			{
				$returnArray[$value] = $dms[$value];
			}
		}
		
		if (isset($dms_id))
		{
			$returnArray['dms_id'] = $dms_id;
		}
		if (isset($fileName))
		{
			$returnArray['filename'] = $fileName;
		}
		
		return $returnArray;
	}
}