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
 * Tempus Menu library
 */
class TempusLib
{
	protected $_ci = null;

	public $config = [];

	public function __construct()
	{
		// Get code igniter instance
		$this->_ci =& get_instance();

		// Load Tree-config
		$this->_ci->load->config('treemenu/custom/tempus');
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

		return $this->_ci->stglib->studiengang($path_template, $permittedStgs);
	}

	public function semester($path_template, $has_children, $stg)
	{

		$stg = $this->getStgFromUrl($stg);

		if ($stg === null)
			return [];

		if (!$has_children)
			$this->_ci->StudiengangModel->db->select('TRUE AS leaf', false);

		return $this->_ci->stglib->semester($path_template, $stg->studiengang_kz);
	}

	public function verband($path_template, $stg, $semester)
	{
		$stg = $this->getStgFromUrl($stg);

		if ($stg === null)
			return [];

		$this->_ci->StudiengangModel->addSelect("CASE WHEN MAX(gruppe)='' OR MAX(gruppe)=' ' THEN TRUE ELSE FALSE END AS leaf");
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
			$this->_ci->load->model('organisation/Gruppe_model', 'GruppeModel');
			$this->_ci->GruppeModel->addSelect('semester');

			return $this->_ci->stglib->group($path_template, $stg->studiengang_kz, $semester);
		}


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

		$this->_ci->load->model('organisation/Studienordnung_model', 'StudienordnungModel');
		$this->_ci->StudienordnungModel->addSelect("p.orgform_kurzbz");

		return $this->_ci->stglib->orgform($path_template, $stg->studiengang_kz);
	}

	protected function getPermittedStudiengangKzs()
	{
		$permittedStgs = $this->_ci->permissionlib->getSTG_isEntitledFor('admin') ?: [];
		$permittedStgs = array_merge($permittedStgs, $this->_ci->permissionlib->getSTG_isEntitledFor('assistenz') ?: []);
		$permittedStgs = array_merge($permittedStgs, $this->_ci->permissionlib->getSTG_isEntitledFor('lehre/lvplan') ?: []);
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
