<?php

class DmsFS_model extends FS_Model
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct(DMS_PATH);
	}
}

