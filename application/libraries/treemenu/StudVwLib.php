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
 * StudVw Menu library
 */
class StudVwLib
{
	protected $_ci = null;

	public $config = [];

	public $redirect_method = [
		'interessenten' => 'filter',
		'bewerbungnichtabgeschickt' => 'filter',
		'bewerbungabgeschickt' => 'filter',
		'zgv' => 'filter',
		'statusbestaetigt' => 'filter',
		'statusbestaetigtrtnichtangemeldet' => 'filter',
		'statusbestaetigtrtangemeldet' => 'filter',
		'reihungstestnichtangemeldet' => 'filter',
		'reihungstestangemeldet' => 'filter',
		'bewerber' => 'filter',
		'bewerberrtnichtangemeldet' => 'filter',
		'bewerberrtangemeldet' => 'filter',
		'bewerberrtangemeldetteilgenommen' => 'filter',
		'bewerberrtangemeldetnichtteilgenommen' => 'filter',
		'aufgenommen' => 'filter',
		'warteliste' => 'filter',
		'absage' => 'filter',
		'incoming' => 'filter',
		'incoming' => 'inout',
		'outgoing' => 'inout',
		'shared_studies' => 'inout'
	];

	public function __construct()
	{
		// Get code igniter instance
		$this->_ci =& get_instance();

		// Load Tree-config
		$this->_ci->load->config('treemenu/stg_base');
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

	public function prestudent($path_template, $stg)
	{
		// TODO(chris): permission check stg

		return [
			[
				'path' => sprintf($path_template, '1'),
				'name' => 'Prestudent',
				'no_sem_reload' => true
			]
		];
	}

	public function stdsem($path_template, $stg)
	{
		$stg = $this->getStgFromUrl($stg);

		if ($stg === null)
			return [];

		return $this->_ci->stglib->studiensemester($path_template, $stg->studiengang_kz);
	}

	public function semester($path_template, $stg, $orgform)
	{
		$stg = $this->getStgFromUrl($stg);

		if ($stg === null)
			return [];

		if ($orgform !== null) {
			$orgform = $this->getOrgform($orgform);
			if ($orgform === null)
				return [];
			$orgform = $orgform->orgform_kurzbz;
		}

		return $this->_ci->stglib->semester($path_template, $stg->studiengang_kz, $orgform);
	}

	public function verband($path_template, $stg, $semester, $orgform)
	{
		$stg = $this->getStgFromUrl($stg);

		if ($stg === null)
			return [];

		if ($orgform !== null) {
			$orgform = $this->getOrgform($orgform);
			if ($orgform === null)
				return [];
			$orgform = $orgform->orgform_kurzbz;
		}

		$this->_ci->StudiengangModel->addSelect("CASE WHEN MAX(gruppe)='' OR MAX(gruppe)=' ' THEN TRUE ELSE FALSE END AS leaf");

		return $this->_ci->stglib->verband($path_template, $stg->studiengang_kz, $semester, $orgform);
	}

	public function group($path_template, $stg, $semester, $verband, $orgform)
	{
		$stg = $this->getStgFromUrl($stg);

		if ($stg === null)
			return [];

		if ($orgform !== null) {
			$orgform = $this->getOrgform($orgform);
			if ($orgform === null)
				return [];
			$orgform = $orgform->orgform_kurzbz;
		}

		$this->_ci->StudiengangModel->db->select('TRUE AS leaf', false);
		
		if ($verband === null)
			return $this->_ci->stglib->group($path_template, $stg->studiengang_kz, $semester, $orgform);

		return $this->_ci->stglib->verbandsgroup($path_template, $stg->studiengang_kz, $semester, $verband, $orgform);
	}

	public function orgform($path_template, $stg)
	{
		$stg = $this->getStgFromUrl($stg);

		if ($stg === null)
			return [];

		if (!$stg->mischform)
			return [];

		return $this->_ci->stglib->orgform($path_template, $stg->studiengang_kz);
	}

	public function filter($original_method, $has_children, $path_template, $stg)
	{
		$stg = $this->getStgFromUrl($stg);

		if ($stg === null)
			return [];

		$item = [
			'path' => sprintf($path_template, '1'),
			'name' => $original_method
		];

		if (!$has_children)
			$item['leaf'] = true;

		return [ $item ];
	}

	public function inout($original_method, $has_children, $path_template)
	{
		if (!$this->_ci->permissionlib->isBerechtigt('inout/uebersicht'))
			return [];

		$item = [
			'path' => sprintf($path_template, '1'),
			'name' => $original_method
		];

		if (!$has_children)
			$item['leaf'] = true;

		return [ $item ];
	}

	protected function getOrgform($orgform)
	{
		$this->_ci->load->model('codex/Orgform_model', 'OrgformModel');
		$orgform = $this->_ci->OrgformModel->loadWhere([
			'LOWER(orgform_kurzbz) =' => $orgform
		]);

		if (!hasData($orgform))
			return null;

		return current(getData($orgform));
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
