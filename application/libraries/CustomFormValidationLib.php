<?php

/**
 * FH-Complete
 *
 * @package             FHC-Helper
 * @author              FHC-Team
 * @copyright           Copyright (c) 2022 fhcomplete.net
 * @license             GPLv3
 */

if (! defined('BASEPATH')) exit('No direct script access allowed');

class CustomFormValidationLib extends CI_Form_validation
{
	public function __construct($rules = array())
	{
		parent::__construct($rules);

		$this->_ci =& get_instance();
	}

	function explicit_integer($value)
	{
		if ($value === null) {
			return true;
		}

		if ($value === '') {
			return false;
		}

		if (filter_var($value, FILTER_VALIDATE_INT) !== false) {
			return true;
		}

		return false;
	}

	function explicit_numeric($value)
	{
		if ($value === null) {
			return true;
		}

		if ($value === '') {
			return false;
		}

		if (is_numeric($value)) {
			return true;
		}

		return false;
	}

	function explicit_boolean($value)
	{
		if ($value === null) {
			return true;
		}

		if ($value === '') {
			return false;
		}

		if ($value === 'true' || $value === 'false' || $value === true || $value === false || $value === 1 || $value === 0) {
			return true;
		}

		return false;
	}

	function does_exist($value, $params)
	{
		if ($value === null ) {
			return true;
		}

		if ($value === '') {
			return false;
		}

		$parts = explode('.', $params);
		if (count($parts) !== 3) {
			return false;
		}

		$subDatabase = $parts[0];
		$table = $parts[1];
		$field = $parts[2];

		$result = $this->_ci->db->select('COUNT(*) as count')
			->from("$subDatabase.$table")
			->where($field, $value)
			->get();

		if ($result === false) {
			return false;
		}
		
		return $result->row()->count > 0;
	}
}
