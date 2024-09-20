<?php

/**
 * Copyright (C) 2023 fhcomplete.org
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

class PersonLog_model extends DB_Model
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();

		$this->dbTable = 'system.tbl_log';
	}

	/**
	 * Inserts a Log for a Person
	 * @param array $data Data of Log Entry to save.
	 * @return success object if true
	 */
	public function insert($data, $encryptedColumns = null)
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
	 * @param string $taetigkeit_kurzbz Verarbeitungstätigkeit
	 * @param string $app Name of the App.
	 * @param string $oe_kurzbz Organisations Unit.
	 * @return object $result
	 */
	public function getLastLog($person_id, $taetigkeit_kurzbz = null, $app = null, $oe_kurzbz = null)
	{
		$this->db->order_by('zeitpunkt', 'DESC');
		$this->db->order_by('log_id', 'DESC');
		$this->db->limit(1);
		if (!is_null($taetigkeit_kurzbz))
			$this->db->where('taetigkeit_kurzbz='.$this->db->escape($oe_kurzbz));
		if (!is_null($app))
			$this->db->where('app='.$this->db->escape($app));
		if (!is_null($oe_kurzbz))
			$this->db->where('oe_kurzbz='.$this->db->escape($oe_kurzbz));

		$result = $this->db->get_where($this->dbTable, "person_id=".$this->db->escape($person_id));

		return success($result->result());
	}

	/**
	 * Load logs for a person, filtered by parameters
	 * @param int $person_id ID of the Person.
	 * @param string $taetigkeit_kurzbz Verarbeitungstätigkeit
	 * @param string $app Name of the App.
	 * @param string $oe_kurzbz Organisations Unit.
	 * @return object $result
	 */
	public function filterLog($person_id, $taetigkeit_kurzbz = null, $app = null, $oe_kurzbz = null)
	{
		$this->db->order_by('zeitpunkt', 'DESC');
		$this->db->order_by('log_id', 'DESC');
		if (!is_null($taetigkeit_kurzbz))
			$this->db->where('taetigkeit_kurzbz='.$this->db->escape($taetigkeit_kurzbz));
		if (!is_null($app))
			$this->db->where('app='.$this->db->escape($app));
		if (!is_null($oe_kurzbz))
			$this->db->where('oe_kurzbz='.$this->db->escape($oe_kurzbz));

		$result = $this->db->get_where($this->dbTable, "person_id=".$this->db->escape($person_id));

		return success($result->result());
	}

	/**
	 * Gets all logs with zeitpunkt > today
	 * @param $person_id
	 * @return array
	 */
	public function getLogsInFuture($person_id)
	{
		$this->db->order_by('zeitpunkt', 'DESC');
		$this->db->order_by('log_id', 'DESC');

		$where = "logtype_kurzbz = 'Processstate'
					AND person_id=".$this->db->escape($person_id)."
					AND zeitpunkt >= now()";

		$result = $this->db->get_where($this->dbTable, $where);

		return success($result->result());
	}

	/**
	 * Deletes a log
	 * @param $log_id
	 * @return array
	 */
	public function deleteLog($log_id)
	{
		$this->db->where('log_id', $log_id);
		$result = $this->db->delete($this->dbTable);

		return success($result);
	}
}
