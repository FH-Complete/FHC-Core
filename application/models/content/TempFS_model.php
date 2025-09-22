<?php

/**
 * Model for writing temporary files
 */
class TempFS_model extends FS_Model
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		// default temp directory of server is used
		parent::__construct(sys_get_temp_dir());
	}
}
