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

require_once(APPPATH . 'traits/menu/Stg/PrestudentTrait.php');
require_once(APPPATH . 'traits/menu/Stg/SemesterTrait.php');

/**
 * 
 */
trait OrgformTrait
{
	use PrestudentTrait, SemesterTrait;

	protected function initOrgform()
	{
		return [
			'children' => ['prestudent', 'semester'],
			'identifiers' => ['orgform_kurzbz'],
			'build' => 'getOrgform'
		];
	}

	protected function getOrgform($vars, $pathTemplate, $linkTemplate)
	{
		// TODO(chris): permission on stg
		// TODO(chris): check vars

		$this->_ci->load->model('organisation/Studiengang_model', 'StudiengangModel');

		// NOTE(chris): if mischform show orgforms
		$result = $this->_ci->StudiengangModel->load($vars['studiengang_kz']);

		if (!hasData($result))
			return [];

		$stg = current(getData($result));

		if (!$stg->mischform)
			return [];

		$this->_ci->load->model('organisation/Studienordnung_model', 'StudienordnungModel');

		$this->_ci->StudienordnungModel->addDistinct();
		$this->_ci->StudienordnungModel->addSelect("FORMAT(" . $this->_ci->StudienordnungModel->escape($pathTemplate) . ", p.orgform_kurzbz) AS path");
		$this->_ci->StudienordnungModel->addSelect("FORMAT(" . $this->_ci->StudienordnungModel->escape($linkTemplate) . ", p.orgform_kurzbz) AS link");
		$this->_ci->StudienordnungModel->addSelect("p.orgform_kurzbz AS name");
		$this->_ci->StudienordnungModel->addSelect("studiengang_kz AS stg_kz");

		$this->_ci->StudienordnungModel->addJoin('lehre.tbl_studienplan p', 'studienordnung_id');

		$result = $this->_ci->StudienordnungModel->loadWhere([
			'aktiv' => true,
			'studiengang_kz' => $vars['studiengang_kz'],
			'p.orgform_kurzbz !=' => 'DDP'
		]);

		return getData($result) ?: [];
	}
}
