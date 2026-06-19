<?php
class Kalender_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'lehre.tbl_kalender';
		$this->pk = 'kalender_id';

		$this->load->helper('hlp_common');
	}

	public function generateUniqueGroupId()
	{
		while (true) {
			$uniqueGroupId = generateUUID();
			$result = $this->loadWhere(['eindeutige_gruppen_id' => $uniqueGroupId]);
			if (!hasData($result)) {
				return $uniqueGroupId;
			}
		}
	}
}
