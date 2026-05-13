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

/**
 * 
 */
trait VerbandsGroupTrait
{
	protected function initVerbandsGroup()
	{
		return [
			'identifiers' => ['group'],
			'build' => 'getVerbandsGroup'
		];
	}

	protected function getVerbandsGroup($vars, $pathTemplate, $linkTemplate)
	{
		// TODO(chris): permission on stg
		// TODO(chris): check vars

		$this->_ci->load->model('organisation/Studiengang_model', 'StudiengangModel');

		$this->_ci->StudiengangModel->addJoin('public.tbl_lehrverband v', 'studiengang_kz');
		
		$this->_ci->StudiengangModel->addDistinct();
		$this->_ci->StudiengangModel->addSelect("FORMAT(" . $this->_ci->StudiengangModel->escape($pathTemplate) . ", gruppe) AS path", false);
		$this->_ci->StudiengangModel->addSelect("FORMAT(" . $this->_ci->StudiengangModel->escape($linkTemplate) . ", gruppe) AS link", false);
		$this->_ci->StudiengangModel->addSelect("CONCAT(UPPER(CONCAT(typ, kurzbz)), '-', semester, verband, gruppe, (SELECT CASE WHEN bezeichnung IS NULL OR bezeichnung='' THEN ''::TEXT ELSE CONCAT(' (', bezeichnung, ')') END FROM public.tbl_lehrverband WHERE studiengang_kz=v.studiengang_kz AND semester=v.semester AND verband=v.verband AND gruppe=v.gruppe ORDER BY gruppe LIMIT 1)) AS name", false);
		$this->_ci->StudiengangModel->addSelect("TRUE AS leaf", false);

		$this->_ci->StudiengangModel->addSelect('v.semester');
		$this->_ci->StudiengangModel->addSelect('v.verband');
		$this->_ci->StudiengangModel->addSelect('gruppe');
		$this->_ci->StudiengangModel->addSelect($this->_ci->StudiengangModel->escape($vars['studiengang_kz']) . '::integer AS stg_kz', false);
		$this->_ci->StudiengangModel->addSelect("ARRAY['link-strict', 'student-collection'] AS droplink");
		
		$this->_ci->StudiengangModel->addOrder('gruppe');

		$where = [
			'v.studiengang_kz' => $vars['studiengang_kz'],
			'v.semester' => $vars['semester'],
			'v.verband' => $vars['verband'],
			'v.gruppe !=' => '',
			'v.aktiv' => true
		];

		if (isset($vars['org_form']) && $vars['semester']) // NOTE(chris): on semester 0 show all?
			$where['v.orgform_kurzbz'] = $vars['org_form'];

		$result = $this->_ci->StudiengangModel->loadWhere($where);

		return getData($result) ?: [];
	}
}
