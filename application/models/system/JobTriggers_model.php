<?php

class JobTriggers_model extends DB_Model
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();

		$this->dbTable = 'system.tbl_jobtriggers';
		$this->pk = array('type', 'status', 'followingType');
		$this->hasSequence = false;
	}

	/**
	 *
	 */
	public function getJobtriggersByTypeStatuses($type, $triggeredStatuses)
	{
		$query = 'SELECT jt.type,
						jt.status,
						jt.following_type
					FROM system.tbl_jobtriggers jt
				   WHERE jt.type = ?
				     AND jt.status IN ?
				ORDER BY jt.type, jt.status';

		return $this->execQuery($query, array($type, $triggeredStatuses));
	}
}
