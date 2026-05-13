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

require_once(APPPATH . 'traits/menu/Stg/Semester/GroupTrait.php');
require_once(APPPATH . 'traits/menu/Stg/Semester/VerbandTrait.php');

/**
 * 
 */
trait SemesterTrait
{
	use GroupTrait, VerbandTrait;

	protected function initSemester()
	{
		return [
			'children' => ['group', 'verband'],
			'identifiers' => ['semester'],
			'build' => 'getSemester'
		];
	}

	protected function getSemester($vars, $pathTemplate, $linkTemplate)
	{
		// TODO(chris): permission on stg
		// TODO(chris): check vars

		$this->_ci->load->model('organisation/Studiengang_model', 'StudiengangModel');

		$this->_ci->StudiengangModel->addJoin('public.tbl_lehrverband v', 'studiengang_kz');
		
		$this->_ci->StudiengangModel->addDistinct();
		$this->_ci->StudiengangModel->addSelect("FORMAT(" . $this->_ci->StudiengangModel->escape($pathTemplate) . ", semester) AS path", false);
		$this->_ci->StudiengangModel->addSelect("FORMAT(" . $this->_ci->StudiengangModel->escape($linkTemplate) . ", semester) AS link", false);
		$this->_ci->StudiengangModel->addSelect("CONCAT(
			UPPER(CONCAT(typ, kurzbz)), 
			'-', 
			semester, 
			(
				SELECT CASE WHEN bezeichnung IS NULL OR bezeichnung='' THEN ''::TEXT ELSE CONCAT(' (', bezeichnung, ')') END 
				FROM public.tbl_lehrverband 
				WHERE studiengang_kz=v.studiengang_kz AND semester=v.semester 
				ORDER BY verband, gruppe LIMIT 1
			)
		) AS name", false);

		$this->_ci->StudiengangModel->addSelect('semester');
		$this->_ci->StudiengangModel->addSelect($this->_ci->StudiengangModel->escape($vars['studiengang_kz']) . '::integer AS stg_kz', false);
		$this->_ci->StudiengangModel->addSelect("ARRAY['link-strict', 'student-collection'] AS droplink");
		
		$this->_ci->StudiengangModel->addOrder('semester');

		if (isset($vars['org_form'])) {
			$this->_ci->StudiengangModel->addSelect("v.orgform_kurzbz");
			$this->_ci->StudiengangModel->db->group_start();
			$this->_ci->StudiengangModel->db->where('v.semester', 0);
			$this->_ci->StudiengangModel->db->or_where('v.orgform_kurzbz', $vars['org_form']);
			$this->_ci->StudiengangModel->db->group_end();
		}

		$result = $this->_ci->StudiengangModel->loadWhere([
			'v.studiengang_kz' => $vars['studiengang_kz'],
			'v.aktiv' => true
		]);

		return getData($result) ?: [];
	}
}
