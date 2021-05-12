<?php

class Statusgrund_model extends DB_Model
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = "public.tbl_status_grund";
		$this->pk = "statusgrund_id";
	}

	public function getStatus($status_kurzbz = null, $aktiv = null)
	{
		$where = array();
		if (!is_null($status_kurzbz))
			$where['status_kurzbz'] = $status_kurzbz;
		if (!is_null($aktiv))
			$where['aktiv'] = $aktiv;

		$status = $this->loadWhere($where);

		return success($status->retval);
	}
}
