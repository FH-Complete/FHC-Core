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
require_once(APPPATH . 'traits/menu/Stg/OrgformTrait.php');

/**
 * 
 */
trait StgTrait
{
	use OrgformTrait;

	protected function initStg()
	{
		// TODO(chris): like this or with functions???
		return [
			// children as assoc array to rename them
			'children' => ['prestudent', 'semester', 'orgform'],
			#'identifiers' => 'getIdentifiersStg',
			'identifiers' => ['studiengang_kz'],
			'build' => 'getStg'
		];
	}

	protected function getStg($vars, $pathTemplate, $linkTemplate)
	{
		$stgs = $this->_ci->permissionlib->getSTG_isEntitledFor('admin') ?: [];
		$stgs = array_merge($stgs, $this->_ci->permissionlib->getSTG_isEntitledFor('assistenz') ?: []);

		if (!$stgs)
			return [];

		$this->_ci->load->model('organisation/Studiengang_model', 'StudiengangModel');

		$this->_ci->StudiengangModel->addJoin('public.tbl_lehrverband v', 'studiengang_kz');
		
		$this->_ci->StudiengangModel->addDistinct();
		$this->_ci->StudiengangModel->addSelect("FORMAT(" . $this->_ci->StudiengangModel->escape($pathTemplate) . ", v.studiengang_kz) AS path");
		$this->_ci->StudiengangModel->addSelect("FORMAT(" . $this->_ci->StudiengangModel->escape($linkTemplate) . ", v.studiengang_kz) AS link");
		$this->_ci->StudiengangModel->addSelect(
			"CONCAT(kurzbzlang, ' (', UPPER(CONCAT(typ, kurzbz)), ') - ', tbl_studiengang.bezeichnung) AS name",
			false
		);
		$this->_ci->StudiengangModel->addSelect("studiengang_kz AS title");
		$this->_ci->StudiengangModel->addSelect("studiengang_kz AS search");
		$this->_ci->StudiengangModel->addSelect('erhalter_kz');
		$this->_ci->StudiengangModel->addSelect('typ');
		$this->_ci->StudiengangModel->addSelect('kurzbz');
		$this->_ci->StudiengangModel->addSelect('studiengang_kz');
		$this->_ci->StudiengangModel->addSelect('studiengang_kz AS stg_kz');
		
		$this->_ci->StudiengangModel->addOrder('erhalter_kz');
		$this->_ci->StudiengangModel->addOrder('typ');
		$this->_ci->StudiengangModel->addOrder('kurzbz');

		$this->_ci->StudiengangModel->db->where_in('studiengang_kz', $stgs);

		$result = $this->_ci->StudiengangModel->loadWhere(['v.aktiv' => true]);

		return getData($result) ?: [];
	}
}
