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
 * LvVw Menu library
 */
class LvVwLib
{
	protected $_ci = null;

	public $config = [];

	public function __construct()
	{
		// Get code igniter instance
		$this->_ci =& get_instance();

		// Load Tree-config
		$this->_ci->load->config('treemenu/custom/lvvw');
		$this->config = $this->_ci->config->item('root');

		// Load Libraries
		$this->_ci->load->library('treemenu/base/StgLib');

		// Load Model
		$this->_ci->load->model('organisation/Studiengang_model', 'StudiengangModel');
	}

	public function stg($path_template)
	{
		$permittedStgs = $this->getPermittedStudiengangKzs();

		if (!$permittedStgs)
			return [];

		$this->_ci->StudiengangModel->addSelect("ARRAY['link', FORMAT('{" .
			'"type": "verband",' .
			'"id": "lv/%1$s",' .
			'"gruppe_kurzbz": null,' .
			'"semester": null,' .
			'"verband": null,' .
			'"gruppe": null,' .
			'"lehrverband": true,' .
			'"stg_kz": "%1$s"' .
		"}', studiengang_kz)] AS draggable", false);

		return $this->_ci->stglib->studiengang($path_template, $permittedStgs);
	}

	public function semester($path_template, $has_children, $stg)
	{
		$stg = $this->getStgFromUrl($stg);

		if ($stg === null)
			return [];

		if (!$has_children)
			$this->_ci->StudiengangModel->db->select('TRUE AS leaf', false);
		$this->_ci->StudiengangModel->addSelect("ARRAY['link', FORMAT('{" .
			'"type": "verband",' .
			'"id": "lv/%1$s/%2$s",' .
			'"gruppe_kurzbz": null,' .
			'"semester": "%2$s",' .
			'"verband": null,' .
			'"gruppe": null,' .
			'"lehrverband": true,' .
			'"stg_kz": "%1$s"' .
		"}', v.studiengang_kz, v.semester)] AS draggable", false);

		return $this->_ci->stglib->semester($path_template, $stg->studiengang_kz);
	}

	public function verband($path_template, $stg, $semester)
	{
		$stg = $this->getStgFromUrl($stg);

		if ($stg === null)
			return [];

		$this->_ci->StudiengangModel->addSelect("CASE WHEN MAX(gruppe)='' OR MAX(gruppe)=' ' THEN TRUE ELSE FALSE END AS leaf");
		$this->_ci->StudiengangModel->addSelect("ARRAY['link', FORMAT('{" .
			'"type": "verband",' .
			'"id": "lv/%1$s/%2$s/%3$s",' .
			'"gruppe_kurzbz": null,' .
			'"semester": "%2$s",' .
			'"verband": "%3$s",' .
			'"gruppe": null,' .
			'"lehrverband": true,' .
			'"stg_kz": "%1$s"' .
		"}', v.studiengang_kz, v.semester, v.verband)] AS draggable", false);
		$this->_ci->StudiengangModel->addGroupBy('v.studiengang_kz, v.semester');

		return $this->_ci->stglib->verband($path_template, $stg->studiengang_kz, $semester);
	}

	public function group($path_template, $stg, $semester, $verband)
	{
		$stg = $this->getStgFromUrl($stg);

		if ($stg === null)
			return [];

		$this->_ci->StudiengangModel->db->select('TRUE AS leaf', false);
		
		if ($verband === null) {
			$this->_ci->StudiengangModel->addSelect("ARRAY['link', FORMAT('{" .
				'"type": "verband",' .
				'"id": "grp/%1$s/%2$s/%3$s",' .
				'"gruppe_kurzbz": "%3$s",' .
				'"semester": "%2$s",' .
				'"verband": null,' .
				'"gruppe": null,' .
				'"lehrverband": false,' .
				'"stg_kz": "%1$s"' .
			"}', studiengang_kz, semester, gruppe_kurzbz)] AS draggable", false);

			return $this->_ci->stglib->group($path_template, $stg->studiengang_kz, $semester);
		}

		$this->_ci->StudiengangModel->addSelect("ARRAY['link', FORMAT('{" .
			'"type": "verband",' .
			'"id": "lv/%1$s/%2$s/%3$s/%4$s",' .
			'"gruppe_kurzbz": null,' .
			'"semester": "%2$s",' .
			'"verband": "%3$s",' .
			'"gruppe": "%4$s",' .
			'"lehrverband": true,' .
			'"stg_kz": "%1$s"' .
		"}', v.studiengang_kz, v.semester, v.verband, v.gruppe)] AS draggable", false);

		return $this->_ci->stglib->verbandsgroup($path_template, $stg->studiengang_kz, $semester, $verband);
	}

	public function orgform($path_template, $stg)
	{
		$stg = $this->getStgFromUrl($stg);

		if ($stg === null)
			return [];

		if (!$stg->mischform)
			return [];
		
		$this->_ci->StudiengangModel->db->select('TRUE AS leaf', false);

		return $this->_ci->stglib->orgform($path_template, $stg->studiengang_kz);
	}

	protected function getPermittedStudiengangKzs()
	{
		$permittedStgs = $this->_ci->permissionlib->getSTG_isEntitledFor('admin') ?: [];
		$permittedStgs = array_merge($permittedStgs, $this->_ci->permissionlib->getSTG_isEntitledFor('assistenz') ?: []);

		return $permittedStgs;
	}

	protected function getStgFromUrl($stg)
	{
		$permittedStgs = $this->getPermittedStudiengangKzs();

		if (!$permittedStgs)
			return null;

		$this->_ci->StudiengangModel->db->where_in('studiengang_kz', $permittedStgs);
		$stg = $this->_ci->StudiengangModel->loadWhere([
			'LOWER(CONCAT(typ, kurzbz)) =' => $stg
		]);

		if (!hasData($stg))
			return null;

		return current(getData($stg));
	}
}
