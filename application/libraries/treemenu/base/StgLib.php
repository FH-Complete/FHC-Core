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
 * Stg Base library
 */
class StgLib
{
	protected $_ci = null;

	public function __construct()
	{
		// Get code igniter instance
		$this->_ci =& get_instance();
	}

	public function studiengang($path_template, $permittedStgs = [])
	{
		$this->_ci->load->model('organisation/Studiengang_model', 'StudiengangModel');

		$this->_ci->StudiengangModel->addJoin('public.tbl_lehrverband v', 'studiengang_kz');
		
		$this->_ci->StudiengangModel->addDistinct();
		$this->_ci->StudiengangModel->addSelect("FORMAT(" . $this->_ci->StudiengangModel->escape($path_template) . ", LOWER(CONCAT(typ, kurzbz))) AS path");
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

		if ($permittedStgs)
			$this->_ci->StudiengangModel->db->where_in('studiengang_kz', $permittedStgs);

		$result = $this->_ci->StudiengangModel->loadWhere(['v.aktiv' => true]);

		return getData($result) ?: [];
	}

	public function semester($path_template, $studiengang_kz, $orgform_kurzbz = null)
	{
		$this->_ci->load->model('organisation/Studiengang_model', 'StudiengangModel');

		$this->_ci->StudiengangModel->addJoin('public.tbl_lehrverband v', 'studiengang_kz');
		
		$this->_ci->StudiengangModel->addDistinct();
		$this->_ci->StudiengangModel->addSelect("FORMAT(" . $this->_ci->StudiengangModel->escape($path_template) . ", semester) AS path", false);
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
		$this->_ci->StudiengangModel->addSelect('v.studiengang_kz AS stg_kz');
		$this->_ci->StudiengangModel->addSelect("ARRAY['link-strict', 'student-collection'] AS droplink");
		
		$this->_ci->StudiengangModel->addOrder('semester');

		if ($orgform_kurzbz !== null) {
			$this->_ci->StudiengangModel->addSelect($this->_ci->StudiengangModel->escape($orgform_kurzbz) . " AS orgform_kurzbz", false);
			$this->_ci->StudiengangModel->addDistinct();
			$this->_ci->StudiengangModel->db->group_start();
			$this->_ci->StudiengangModel->db->where('v.semester', 0);
			$this->_ci->StudiengangModel->db->or_where('v.orgform_kurzbz', $orgform_kurzbz);
			$this->_ci->StudiengangModel->db->group_end();
		}

		$result = $this->_ci->StudiengangModel->loadWhere([
			'studiengang_kz' => $studiengang_kz,
			'v.aktiv' => true
		]);


		return getData($result) ?: [];
	}

	public function group($path_template, $studiengang_kz, $semester, $orgform_kurzbz = null)
	{
		$this->_ci->load->model('organisation/Gruppe_model', 'GruppeModel');

		$this->_ci->GruppeModel->addDistinct();
		$this->_ci->GruppeModel->addSelect("FORMAT(" . $this->_ci->GruppeModel->escape($path_template) . ", gruppe_kurzbz) AS path", false);
		$this->_ci->GruppeModel->addSelect("CONCAT(gruppe_kurzbz, ' (', bezeichnung, ')') AS name", false);

		$this->_ci->GruppeModel->addSelect('sort');
		$this->_ci->GruppeModel->addSelect('gruppe_kurzbz');
		$this->_ci->GruppeModel->addSelect($this->_ci->GruppeModel->escape($studiengang_kz) . '::integer AS stg_kz', false);
		$this->_ci->GruppeModel->addSelect("ARRAY['link-strict', 'student-collection'] AS droplink");

		$this->_ci->GruppeModel->addOrder('sort');
		$this->_ci->GruppeModel->addOrder('gruppe_kurzbz');

		$where = [
			'studiengang_kz' => $studiengang_kz,
			'semester' => $semester,
			'lehre' => true,
			'sichtbar' => true,
			'aktiv' => true,
			'direktinskription' => false
		];

		if ($orgform_kurzbz !== null)
			$where['orgform_kurzbz'] = $orgform_kurzbz;

		$result = $this->_ci->GruppeModel->loadWhere($where);

		return getData($result) ?: [];
	}

	public function verband($path_template, $studiengang_kz, $semester, $orgform_kurzbz = null)
	{
		$this->_ci->load->model('organisation/Studiengang_model', 'StudiengangModel');
		
		$this->_ci->StudiengangModel->addJoin('public.tbl_lehrverband v', 'studiengang_kz');
		
		$this->_ci->StudiengangModel->addSelect("FORMAT(" . $this->_ci->StudiengangModel->escape($path_template) . ", verband) AS path", false);
		$this->_ci->StudiengangModel->addSelect("CONCAT(
			UPPER(CONCAT(typ, kurzbz)), 
			'-', 
			semester, 
			verband, 
			(
				SELECT 
					CASE 
						WHEN bezeichnung IS NULL OR bezeichnung='' 
						THEN ''::TEXT 
						ELSE CONCAT(' (', bezeichnung, ')') 
					END 
				FROM public.tbl_lehrverband 
				WHERE studiengang_kz=v.studiengang_kz 
				AND semester=v.semester 
				AND verband=v.verband 
				ORDER BY gruppe 
				LIMIT 1
			)
		) AS name", false);

		$this->_ci->StudiengangModel->addSelect($this->_ci->StudiengangModel->escape($semester) . ' AS semester');
		$this->_ci->StudiengangModel->addSelect('verband');
		$this->_ci->StudiengangModel->addSelect($this->_ci->StudiengangModel->escape($studiengang_kz) . '::integer AS stg_kz', false);
		$this->_ci->StudiengangModel->addSelect("ARRAY['link-strict', 'student-collection'] AS droplink");
		
		$this->_ci->StudiengangModel->addOrder('verband');

		$this->_ci->StudiengangModel->addGroupBy('path, name, verband');
		
		$where = [
			'v.studiengang_kz' => $studiengang_kz,
			'v.semester' => $semester,
			'v.verband !=' => '',
			'v.aktiv' => true
		];

		if ($orgform_kurzbz !== null && $semester) // NOTE(chris): on semester 0 show all?
			$where['v.orgform_kurzbz'] = $orgform_kurzbz;

		$result = $this->_ci->StudiengangModel->loadWhere($where);

		return getData($result) ?: [];
	}

	public function verbandsgroup($path_template, $studiengang_kz, $semester, $verband, $orgform_kurzbz = null)
	{
		$this->_ci->load->model('organisation/Studiengang_model', 'StudiengangModel');

		$this->_ci->StudiengangModel->addJoin('public.tbl_lehrverband v', 'studiengang_kz');
		
		$this->_ci->StudiengangModel->addDistinct();
		$this->_ci->StudiengangModel->addSelect("FORMAT(
			" . $this->_ci->StudiengangModel->escape($path_template) . ", 
			gruppe
		) AS path", false);
		$this->_ci->StudiengangModel->addSelect("CONCAT(
			UPPER(CONCAT(typ, kurzbz)), 
			'-', 
			semester, 
			verband, 
			gruppe, 
			(
				SELECT 
					CASE 
						WHEN bezeichnung IS NULL OR bezeichnung='' 
						THEN ''::TEXT 
						ELSE CONCAT(' (', bezeichnung, ')') 
					END 
				FROM public.tbl_lehrverband 
				WHERE studiengang_kz=v.studiengang_kz 
				AND semester=v.semester 
				AND verband=v.verband 
				AND gruppe=v.gruppe 
				ORDER BY gruppe 
				LIMIT 1
			)
		) AS name", false);

		$this->_ci->StudiengangModel->addSelect('v.semester');
		$this->_ci->StudiengangModel->addSelect('v.verband');
		$this->_ci->StudiengangModel->addSelect('gruppe');
		$this->_ci->StudiengangModel->addSelect($this->_ci->StudiengangModel->escape($studiengang_kz) . '::integer AS stg_kz', false);
		$this->_ci->StudiengangModel->addSelect("ARRAY['link-strict', 'student-collection'] AS droplink");
		
		$this->_ci->StudiengangModel->addOrder('gruppe');

		$where = [
			'v.studiengang_kz' => $studiengang_kz,
			'v.semester' => $semester,
			'v.verband' => $verband,
			'v.gruppe !=' => '',
			'v.aktiv' => true
		];

		if ($orgform_kurzbz !== null && $semester) // NOTE(chris): on semester 0 show all?
			$where['v.orgform_kurzbz'] = $orgform_kurzbz;

		$result = $this->_ci->StudiengangModel->loadWhere($where);

		return getData($result) ?: [];
	}

	public function orgform($path_template, $studiengang_kz)
	{
		$this->_ci->load->model('organisation/Studienordnung_model', 'StudienordnungModel');

		$this->_ci->StudienordnungModel->addDistinct();
		$this->_ci->StudienordnungModel->addSelect("FORMAT(
			" . $this->_ci->StudienordnungModel->escape($path_template) . ", 
			LOWER(p.orgform_kurzbz)
		) AS path");
		$this->_ci->StudienordnungModel->addSelect("p.orgform_kurzbz AS name");
		$this->_ci->StudienordnungModel->addSelect("studiengang_kz AS stg_kz");

		$this->_ci->StudienordnungModel->addJoin('lehre.tbl_studienplan p', 'studienordnung_id');

		$result = $this->_ci->StudienordnungModel->loadWhere([
			'aktiv' => true,
			'studiengang_kz' => $studiengang_kz,
			'p.orgform_kurzbz !=' => 'DDP'
		]);


		return getData($result) ?: [];
	}

	public function studiensemester($path_template, $studiengang_kz)
	{
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
		$this->_ci->StudiensemesterModel->addSelect("FORMAT(
			" . $this->_ci->StudiensemesterModel->escape($path_template) . ", 
			LOWER(studiensemester_kurzbz)
		) AS path", false);
		$this->_ci->StudiensemesterModel->addSelect($this->_ci->StudiensemesterModel->escape($studiengang_kz) . " AS stg_kz", false);
		
		$this->_ci->StudiensemesterModel->addOrder('ende');

		$result = $this->_ci->StudiensemesterModel->load();

		return getData($result) ?: [];
	}
}
