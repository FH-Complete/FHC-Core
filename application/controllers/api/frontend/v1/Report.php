<?php
/**
 * Copyright (C) 2026 fhcomplete.org
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

if (!defined('BASEPATH')) exit('No direct script access allowed');

use \DateTime as DateTime;

class Report extends FHCAPI_Controller
{

	/**
	 * Object initialization
	 */
	public function __construct()
	{
		// TODO(chris): permissions
		parent::__construct([
			'list' => self::PERM_LOGGED,
			'vars' => self::PERM_LOGGED,
			'get' => self::PERM_LOGGED,
		]);

		$this->load->model('organisation/Statistik_model', 'StatistikModel');
	}

	//------------------------------------------------------------------------------------------------------------------
	// Public methods

	/**
	 * @access public
	 */
	public function list()
	{
		$this->StatistikModel->addSelect('statistik_kurzbz');
		$this->StatistikModel->addSelect('bezeichnung');
		$this->StatistikModel->addSelect('gruppe');
		$this->StatistikModel->addSelect('sql'); // TODO(chris): remove!
		
		$result = $this->StatistikModel->loadWhere([
			'sql IS NOT NULL' => null
		]);

		$data = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess($data);
	}

	/**
	 * @access public
	 */
	public function vars($statistik_kurzbz)
	{
		$result = $this->StatistikModel->load($statistik_kurzbz);
		$statistik = $this->getDataOrTerminateWithError($result);
		if (!$statistik)
			show_404();
		$statistik = current($statistik);

		$vars = $this->loadVars($statistik->sql);
		
		$this->terminateWithSuccess($vars);
	}

	/**
	 * @access protected
	 */
	protected function loadVars($sql)
	{
		$result = [];
		preg_match_all('/\$\w+/', $sql ?: '', $result);
		$result = $result[0];

		$vars = array_unique($result); // remove doubles
		
		$this->load->model('system/Filter_model', 'FilterModel');
		$vars = array_map([$this, 'varToFilter'], $vars); // prep them

		$vars = array_filter($vars); // remove null values (former: $user)

		return array_values($vars); // reindex
	}

	/**
	 * @access protected
	 */
	protected function varToFilter($value)
	{
		if ($value == '$user')
			return null;
		
		$value = str_replace('$', '', $value);
		
		$result = $this->FilterModel->loadWhere([
			'kurzbz' => $value
		]);
		$filter = $this->getDataOrTerminateWithError($result);

		$result = [];
		if (!$filter) {
			$result = [
				'kurzbz' => $value,
				'title' => $value,
				'type' => 'text',
			];
		} else {
			$filter = current($filter);
			$result = [
				'kurzbz' => $filter->kurzbz,
				'title' => $filter->bezeichnung ?: $filter->kurzbz,
				'type' => $filter->type,
			];
			switch ($filter->type) {
				case 'select':
					$sql = str_replace('$user', $this->FilterModel->escape(getAuthUID()), $filter->sql);
					$query = $this->FilterModel->execReadOnlyQuery($sql);
					$options = $this->getDataOrTerminateWithError($query);
					$showValue = $filter->showvalue;
					$result['options'] = array_map(function ($option) use ($showValue) {
						$label = '';
						if ($showValue)
							$label = '(' + $option->value + ') - ';
						if (property_exists($option, 'name'))
							$label .= $option->name;
						else
							$label .= ((array)$option)[array_find(array_keys((array)$option), function ($key) {
								return $key != 'value';
							})];
						return [
							'value' => $option->value,
							'label' => $label,
						];
					}, $options);

					if (preg_match('/(^|\s)multiple($|\s)/', $filter->htmlattr ?: '')) {
						$result['multiple'] = true;
					}
					break;
				
				default:
					if (preg_match('/(^|\s)placeholder="([^"]*)"($|\s)/', $filter->htmlattr ?: '', $matches)) {
						$result['placeholder'] = $matches[2];
					}
					break;
			}
		}
		
		return $result;
	}

	/**
	 * @access public
	 */
	public function get($statistik_kurzbz)
	{
		// TODO(chris): IMPLEMENT!
		$result = $this->StatistikModel->load($statistik_kurzbz);
		$statistik = $this->getDataOrTerminateWithError($result);
		if (!$statistik)
			show_404();
		$statistik = current($statistik);

		$vars = $this->loadVars($statistik->sql);
		$vars_values = [];
		
		if (count($vars)) {
			$this->load->library('form_validation');
			foreach ($vars as $var) {
				// TODO(chris): calculated stuff
				// TODO(chris): select values check
				$key = $var['kurzbz'];
				$label = $var['title'];
				$checks = 'required';
				
				// TODO(chris): select values check in multi?
				if (isset($var['multiple']) && $var['multiple']) {
					$key .= '[]';
				}

				if ($var['type'] == 'datepicker')
					$checks .= '|is_valid_date';

				$this->form_validation->set_rules($key, $label, $checks);
				
				$vars_values[$var['kurzbz']] = $this->input->post($var['kurzbz']);
				// TODO(chris): calculated stuff
				if ($var['type'] == 'datepicker') {
					try {
						$dt = new DateTime($vars_values[$var['kurzbz']]);
						$vars_values[$var['kurzbz']] = $dt->format('c');
					} catch(Exception $e) {
					}
				}
			}

			if (!$this->form_validation->run())
				$this->terminateWithValidationErrors($this->form_validation->error_array());
		}
		
		$db = $this->StatistikModel;
		$sql = preg_replace_callback('/\$\w+/', function ($match) use ($db, $vars_values) {
			if ($match[0] == '$user')
				return $db->escape(getAuthUID());

			$key = substr($match[0], 1);
			if (isset($vars_values[$key])) {
				$value = $vars_values[$key];
				if (is_array($value)) {
					$value = implode(', ', array_map(function ($v) use ($db) {
						return $db->escape($v);
					}, $value));
				} else {
					$value = $db->escape($value);
				}
				return $value;
			}

			return $match[0];
		}, $statistik->sql);

		$result = $this->StatistikModel->execReadOnlyQuery($sql);

		$data = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess($data);
	}
}
