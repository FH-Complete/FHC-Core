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
trait GroupTrait
{
	protected function initGroup()
	{
		return [
			'identifiers' => ['gruppe_kurzbz'],
			'build' => 'getGroup'
		];
	}

	protected function getGroup($vars, $pathTemplate, $linkTemplate)
	{
		// TODO(chris): permission on stg
		// TODO(chris): check vars

		$this->_ci->load->model('organisation/Gruppe_model', 'GruppeModel');

		$this->_ci->GruppeModel->addDistinct();
		$this->_ci->GruppeModel->addSelect("FORMAT(" . $this->_ci->GruppeModel->escape($pathTemplate) . ", gruppe_kurzbz) AS path", false);
		$this->_ci->GruppeModel->addSelect("FORMAT(" . $this->_ci->GruppeModel->escape($linkTemplate) . ", gruppe_kurzbz) AS link", false);
		$this->_ci->GruppeModel->addSelect("CONCAT(gruppe_kurzbz, ' (', bezeichnung, ')') AS name", false);
		$this->_ci->GruppeModel->addSelect("TRUE AS leaf", false);

		$this->_ci->GruppeModel->addSelect('sort');
		$this->_ci->GruppeModel->addSelect('gruppe_kurzbz');
		$this->_ci->GruppeModel->addSelect($this->_ci->GruppeModel->escape($vars['studiengang_kz']) . '::integer AS stg_kz', false);
		$this->_ci->GruppeModel->addSelect("ARRAY['link-strict', 'student-collection'] AS droplink");

		$this->_ci->GruppeModel->addOrder('sort');
		$this->_ci->GruppeModel->addOrder('gruppe_kurzbz');

		$where = [
			'studiengang_kz' => $vars['studiengang_kz'],
			'semester' => $vars['semester'],
			'lehre' => true,
			'sichtbar' => true,
			'aktiv' => true,
			'direktinskription' => false
		];

		if (isset($vars['org_form']))
			$where['orgform_kurzbz'] = $vars['org_form'];

		$result = $this->_ci->GruppeModel->loadWhere($where);

		return getData($result) ?: [];
	}
}
