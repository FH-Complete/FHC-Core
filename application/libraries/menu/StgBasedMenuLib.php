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

require_once(APPPATH . 'libraries/MenuBuilderLib.php');

/**
 * StudVw Menu library
 */
class StgBasedMenuLib extends MenuBuilderLib
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->config = $this->_ci->config->item('config_stg_based');
	}

	protected function mapUrlPartToVars($key, $value)
	{
		if ($key == 'stg')
			$key = 'studiengang_kz';
		if ($key == 'orgform')
			$key = 'org_form';
		return [ $key, $value ];
	}

	protected function getStgs($vars)
	{
		$stgs = $this->_ci->permissionlib->getSTG_isEntitledFor('admin') ?: [];
		$stgs = array_merge($stgs, $this->_ci->permissionlib->getSTG_isEntitledFor('assistenz') ?: []);

		if (!$stgs)
			return [];

		$pathTemplate = $this->getPathTemplate($vars);
		$linkTemplate = $this->getLinkTemplate($vars);

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

	protected function getSemester($vars)
	{
		// TODO(chris): permission on stg
		// TODO(chris): check vars

		$pathTemplate = $this->getPathTemplate($vars);
		$linkTemplate = $this->getLinkTemplate($vars);

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

	protected function getOrgforms($vars)
	{
		// TODO(chris): permission on stg
		// TODO(chris): check vars

		$pathTemplate = $this->getPathTemplate($vars);
		$linkTemplate = $this->getLinkTemplate($vars);

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

	protected function getGroups($vars)
	{
		// TODO(chris): permission on stg
		// TODO(chris): check vars

		$pathTemplate = $this->getPathTemplate($vars);
		$linkTemplate = $this->getLinkTemplate($vars);

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

	protected function getVerbaende($vars)
	{
		// TODO(chris): permission on stg
		// TODO(chris): check vars

		$pathTemplate = $this->getPathTemplate($vars);
		$linkTemplate = $this->getLinkTemplate($vars);

		$this->_ci->load->model('organisation/Studiengang_model', 'StudiengangModel');
		
		$this->_ci->StudiengangModel->addJoin('public.tbl_lehrverband v', 'studiengang_kz');
		
		$this->_ci->StudiengangModel->addSelect("FORMAT(" . $this->_ci->StudiengangModel->escape($pathTemplate) . ", verband) AS path", false);
		$this->_ci->StudiengangModel->addSelect("FORMAT(" . $this->_ci->StudiengangModel->escape($linkTemplate) . ", verband) AS link", false);
		$this->_ci->StudiengangModel->addSelect("CONCAT(UPPER(CONCAT(typ, kurzbz)), '-', semester, verband, (SELECT CASE WHEN bezeichnung IS NULL OR bezeichnung='' THEN ''::TEXT ELSE CONCAT(' (', bezeichnung, ')') END FROM public.tbl_lehrverband WHERE studiengang_kz=v.studiengang_kz AND semester=v.semester AND verband=v.verband ORDER BY gruppe LIMIT 1)) AS name", false);
		$this->_ci->StudiengangModel->addSelect("CASE WHEN MAX(gruppe)='' OR MAX(gruppe)=' ' THEN TRUE ELSE FALSE END AS leaf");

		$this->_ci->StudiengangModel->addSelect($this->_ci->StudiengangModel->escape($vars['semester']) . ' AS semester');
		$this->_ci->StudiengangModel->addSelect('verband');
		$this->_ci->StudiengangModel->addSelect($this->_ci->StudiengangModel->escape($vars['studiengang_kz']) . '::integer AS stg_kz', false);
		$this->_ci->StudiengangModel->addSelect("ARRAY['link-strict', 'student-collection'] AS droplink");
		
		$this->_ci->StudiengangModel->addOrder('verband');

		$this->_ci->StudiengangModel->addGroupBy('path, link, name, verband');
		
		$where = [
			'v.studiengang_kz' => $vars['studiengang_kz'],
			'v.semester' => $vars['semester'],
			'v.verband !=' => '',
			'v.aktiv' => true
		];

		if (isset($vars['org_form']) && $vars['semester']) // NOTE(chris): on semester 0 show all?
			$where['v.orgform_kurzbz'] = $vars['org_form'];

		$result = $this->_ci->StudiengangModel->loadWhere($where);

		return getData($result) ?: [];
	}

	protected function getVerbandGroups($vars)
	{
		// TODO(chris): permission on stg
		// TODO(chris): check vars

		$pathTemplate = $this->getPathTemplate($vars);
		$linkTemplate = $this->getLinkTemplate($vars);

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

	protected function getStdSemester($vars)
	{
		// TODO(chris): permission on stg
		// TODO(chris): check vars

		$pathTemplate = $this->getPathTemplate($vars);
		$linkTemplate = $this->getLinkTemplate($vars);

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
