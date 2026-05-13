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

if (! defined('BASEPATH')) exit('No direct script access allowed');

require_once(APPPATH . 'traits/menu/Stg/Prestudent/Stdsem/StdsemFilterTrait.php');

/**
 * 
 */
trait StdsemTrait
{
	use StdsemFilterTrait;

	protected function initStdsem()
	{
		return [
			'children' => [
				'interessenten',
				'bewerber',
				'aufgenommen',
				'warteliste',
				'absage',
				'filterIncoming' => 'incoming'
			],
			'identifiers' => ['studiensemester_kurzbz'],
			'build' => 'getStdsem'
		];
	}

	protected function getStdsem($vars, $pathTemplate, $linkTemplate)
	{
		// TODO(chris): permission on stg
		// TODO(chris): check vars

		$number_displayed_past_studiensemester = null;
		
		$this->_ci->load->model('system/Variable_model', 'VariableModel');
		
		$result = $this->_ci->VariableModel->getVariables(getAuthUID(), ['number_displayed_past_studiensemester']);
		if (isError($result))
			return [];

		$data = getData($result);
		if ($data && isset($data['number_displayed_past_studiensemester'])) {
			$number_displayed_past_studiensemester = $data['number_displayed_past_studiensemester'];
		} else {
			$this->_ci->load->config('stv');
			$number_displayed_past_studiensemester = $this->_ci->config->item('number_displayed_past_studiensemester_default');
		}

		$this->_ci->load->model('organisation/Studiensemester_model', 'StudiensemesterModel');

		$this->_ci->StudiensemesterModel->addPlusMinus(null, $number_displayed_past_studiensemester);
		
		$this->_ci->StudiensemesterModel->addSelect("studiensemester_kurzbz AS name");
		$this->_ci->StudiensemesterModel->addSelect("FORMAT(" . $this->_ci->StudiensemesterModel->escape($pathTemplate) . ", studiensemester_kurzbz) AS path", false);
		$this->_ci->StudiensemesterModel->addSelect("FORMAT(" . $this->_ci->StudiensemesterModel->escape($linkTemplate) . ", studiensemester_kurzbz) AS link", false);
		$this->_ci->StudiensemesterModel->addSelect($this->_ci->StudiensemesterModel->escape($vars['studiengang_kz']) . " AS stg_kz", false);
		
		$this->_ci->StudiensemesterModel->addOrder('ende');

		$result = $this->_ci->StudiensemesterModel->load();

		return getData($result) ?: [];
	}
}
