<?php
/**
 * PersonLog Extends from CI_Model instead of DB_Model
 * to be able to write Log without a loggedin User!
 */
class PersonLog_model extends CI_Model
{
	private $dbTable;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->load->database();

		$this->dbTable = 'system.tbl_log';
	}

	/**
	 * Inserts a Log for a Person
	 * @param array $data Data of Log Entry to save.
	 * @return success object if true
	 */
	public function insert($data)
	{
		$result = $this->db->insert($this->dbTable, $data);
		if ($result)
			return success($this->db->insert_id());
		else
			return error();
	}

	/**
	 * Loads the last Log Entry of a Person
	 * @param int $person_id ID of the Person.
	 * @param string $app Name of the App.
	 * @param string $oe_kurzbz Organisations Unit.
	 * @return object $result
	 */
	public function getLastLog($person_id, $app = null, $oe_kurzbz = null)
	{
		// Check Permissions
		$this->load->library('PermissionLib');
		if(!$this->permissionlib->isEntitled('system.tbl_log',PermissionLib::SELECT_RIGHT))
			show_error('Permission denied - You need Access to system.tbl_log');

		$this->db->order_by('zeitpunkt', 'DESC');
		$this->db->order_by('log_id', 'DESC');
		$this->db->limit(1);
		if (!is_null($app))
			$this->db->where('app='.$this->db->escape($app));
		if (!is_null($oe_kurzbz))
			$this->db->where('oe_kurzbz='.$this->db->escape($oe_kurzbz));

		$result = $this->db->get_where($this->dbTable, "person_id=".$this->db->escape($person_id));

		return success($result->result());
	}
}
