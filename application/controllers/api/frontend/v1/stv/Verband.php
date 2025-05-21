<?php
/**
 * Copyright (C) 2024 fhcomplete.org
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
 * This controller operates between (interface) the JS (GUI) and the back-end
 * Provides data to the ajax get calls about verbände
 * This controller works with JSON calls on the HTTP GET or POST and the output is always JSON
 */
class Verband extends FHCAPI_Controller
{
	public function __construct()
	{
		$permissions = [];
		$router = load_class('Router');
		$permissions[$router->method] = ['admin:r', 'assistenz:r'];
		parent::__construct($permissions);

		// Load Models
		$this->load->model('organisation/Studiengang_model', 'StudiengangModel');
	}
	
	/**
	 * Remap calls:
	 * /
	 * /(studiengang_kz)									=> getStudiengang
	 * /(studiengang_kz)/(semester)							=> getSemester
	 * /(studiengang_kz)/(semester)/(verband)				=> getVerband
	 * /(studiengang_kz)/(org_form)							=> getStudiengang
	 * /(studiengang_kz)/(org_form)/(semester)				=> getSemester
	 * /(studiengang_kz)/(org_form)/(semester)/(verband)	=> getVerband
	 *
	 * @param string		$method
	 * @param array			$params				(optional)
	 *
	 * @return void
	 */
	public function _remap($method, $params = [])
	{
		if ($method == '' || $method == 'index')
			return $this->getBase();

		// NOTE(chris): Test if access is allowed ($method is the Studiengang)
		if (!$this->permissionlib->isBerechtigt('assistenz', 's', $method)
			&& !$this->permissionlib->isBerechtigt('admin', 's', $method)
		) {
			return $this->_outputAuthError([$method => ['admin:r', 'assistenz:r']]);
		}

		$count = count($params);
		if (!$count)
			return $this->getStudiengang($method);

		if ($count == 1) {
			if (is_numeric($params[0]))
				return $this->getSemester($method, $params[0]);
			elseif ($params[0] == 'prestudent')
				return $this->terminateWithSuccess($this->getStdSem($method . '/prestudent/', $method));
			else
				return $this->getStudiengang($method, $params[0]);
		}
		if ($count == 2) {
			if (is_numeric($params[0]))
				return $this->getVerband($method, $params[0], $params[1]);
			elseif ($params[1] == 'prestudent')
				return $this->terminateWithSuccess($this->getStdSem($method . '/' . $params[0] . '/prestudent/', $method));
			else
				return $this->getSemester($method, $params[1], $params[0]);
		}
		if ($count == 3 && !is_numeric($params[0]) && is_numeric($params[1]) && !is_numeric($params[2]))
			return $this->getVerband($method, $params[1], $params[2], $params[0]);

		show_404();
	}

	/**
	 * @return void
	 */
	protected function getBase()
	{
		$this->StudiengangModel->addJoin('public.tbl_lehrverband v', 'studiengang_kz');
		
		$this->StudiengangModel->addDistinct();
		$this->StudiengangModel->addSelect("v.studiengang_kz AS link");
		$this->StudiengangModel->addSelect(
			"CONCAT(kurzbzlang, ' (', UPPER(CONCAT(typ, kurzbz)), ') - ', tbl_studiengang.bezeichnung) AS name",
			false
		);
		$this->StudiengangModel->addSelect('erhalter_kz');
		$this->StudiengangModel->addSelect('typ');
		$this->StudiengangModel->addSelect('kurzbz');
		$this->StudiengangModel->addSelect('studiengang_kz');
		$this->StudiengangModel->addSelect('studiengang_kz AS stg_kz');
		
		$this->StudiengangModel->addOrder('erhalter_kz');
		$this->StudiengangModel->addOrder('typ');
		$this->StudiengangModel->addOrder('kurzbz');

		$stgs = $this->permissionlib->getSTG_isEntitledFor('admin') ?: [];
		$stgs = array_merge($stgs, $this->permissionlib->getSTG_isEntitledFor('assistenz') ?: []);

		if (!$stgs)
			$this->terminateWithSuccess([]);

		$this->StudiengangModel->db->where_in('studiengang_kz', $stgs);

		$result = $this->StudiengangModel->loadWhere(['v.aktiv' => true]);

		$list = $this->getDataOrTerminateWithError($result);

		if ($this->permissionlib->isBerechtigt('inout/uebersicht'))
			$list[] = [
				'name' => 'International',
				'link' => 'inout',
				'children' => [
					[
						'name' => 'Incoming',
						'link' => 'inout/incoming',
						'leaf' => true
					],
					[
						'name' => 'Outgoing',
						'link' => 'inout/outgoing',
						'leaf' => true
					],
					[
						'name' => 'Gemeinsame Studien',
						'link' => 'inout/gemeinsamestudien',
						'leaf' => true
					]
				]
			];
		$this->terminateWithSuccess($list);
	}

	/**
	 * @param integer		$studiengang_kz
	 * @param string		$orgform			(optional)
	 *
	 * @return void
	 */
	protected function getStudiengang($studiengang_kz, $org_form = null)
	{
		$link = $studiengang_kz . '/';
		if ($org_form !== null)
			$link .= $org_form . '/';

		$this->StudiengangModel->addJoin('public.tbl_lehrverband v', 'studiengang_kz');
		
		$this->StudiengangModel->addDistinct();
		$this->StudiengangModel->addSelect("CONCAT(" . $this->StudiengangModel->escape($link) . ", semester) AS link", false);
		$this->StudiengangModel->addSelect("CONCAT(UPPER(CONCAT(typ, kurzbz)), '-', semester, (SELECT CASE WHEN bezeichnung IS NULL OR bezeichnung='' THEN ''::TEXT ELSE CONCAT(' (', bezeichnung, ')') END FROM public.tbl_lehrverband WHERE studiengang_kz=v.studiengang_kz AND semester=v.semester ORDER BY verband, gruppe LIMIT 1)) AS name", false);

		$this->StudiengangModel->addSelect('semester');
		$this->StudiengangModel->addSelect($this->StudiengangModel->escape($studiengang_kz) . '::integer AS stg_kz', false);
		
		$this->StudiengangModel->addOrder('semester');

		if ($org_form !== null) {
			$this->StudiengangModel->db->group_start();
			$this->StudiengangModel->db->where('v.semester', 0);
			$this->StudiengangModel->db->or_where('v.orgform_kurzbz', $org_form);
			$this->StudiengangModel->db->group_end();
		}

		$result = $this->StudiengangModel->loadWhere([
			'v.studiengang_kz' => $studiengang_kz,
			'v.aktiv' => true
		]);
		$list = $this->getDataOrTerminateWithError($result);

		array_unshift($list, [
			'name' => 'PreStudent',
			'link' => $link . 'prestudent',
			'children' => $this->getStdSem($link . 'prestudent/', $studiengang_kz)
		]);

		if ($org_form === null) {
			// NOTE(chris): if mischform show orgforms
			$result = $this->StudiengangModel->load($studiengang_kz);
			$result = $this->getDataOrTerminateWithError($result);
			if ($result) {
				if (current($result)->mischform) {
					$this->load->model('organisation/Studienordnung_model', 'StudienordnungModel');

					$this->StudienordnungModel->addDistinct();
					$this->StudienordnungModel->addSelect("CONCAT(studiengang_kz, '/', p.orgform_kurzbz) AS link");
					$this->StudienordnungModel->addSelect("p.orgform_kurzbz AS name");

					$this->StudienordnungModel->addJoin('lehre.tbl_studienplan p', 'studienordnung_id');

					$result = $this->StudienordnungModel->loadWhere([
						'aktiv' => true,
						'studiengang_kz' => $studiengang_kz,
						'p.orgform_kurzbz !=' => 'DDP'
					]);
					$result = $this->getDataOrTerminateWithError($result);

					$list = array_merge($list, $result);
				}
			}

		}
		$this->terminateWithSuccess($list);
	}

	/**
	 * @param integer		$studiengang_kz
	 * @param integer		$semester
	 * @param string		$orgform
	 *
	 * @return void
	 */
	protected function getSemester($studiengang_kz, $semester, $org_form = null)
	{
		$link = $studiengang_kz . '/';
		if ($org_form !== null)
			$link .= $org_form . '/';
		$link .= $semester . '/';


		$this->load->model('organisation/Gruppe_model', 'GruppeModel');

		$this->GruppeModel->addDistinct();
		$this->GruppeModel->addSelect("CONCAT(" . $this->GruppeModel->escape($link . 'grp/') . ", gruppe_kurzbz) AS link", false);
		$this->GruppeModel->addSelect("CONCAT(gruppe_kurzbz, ' (', bezeichnung, ')') AS name", false);
		$this->GruppeModel->addSelect("TRUE AS leaf", false);

		$this->GruppeModel->addSelect('sort');
		$this->GruppeModel->addSelect('gruppe_kurzbz');
		$this->GruppeModel->addSelect($this->GruppeModel->escape($studiengang_kz) . '::integer AS stg_kz', false);

		$this->GruppeModel->addOrder('sort');
		$this->GruppeModel->addOrder('gruppe_kurzbz');

		$where = [
			'studiengang_kz' => $studiengang_kz,
			'semester' => $semester,
			'lehre' => true,
			'sichtbar' => true,
			'aktiv' => true,
			'direktinskription' => false
		];

		if ($org_form !== null)
			$where['orgform_kurzbz'] = $org_form;

		$result = $this->GruppeModel->loadWhere($where);

		$list = $this->getDataOrTerminateWithError($result);

		$this->StudiengangModel->addJoin('public.tbl_lehrverband v', 'studiengang_kz');
		
		$this->StudiengangModel->addSelect("CONCAT(" . $this->StudiengangModel->escape($link) . ", verband) AS link", false);
		$this->StudiengangModel->addSelect("CONCAT(UPPER(CONCAT(typ, kurzbz)), '-', semester, verband, (SELECT CASE WHEN bezeichnung IS NULL OR bezeichnung='' THEN ''::TEXT ELSE CONCAT(' (', bezeichnung, ')') END FROM public.tbl_lehrverband WHERE studiengang_kz=v.studiengang_kz AND semester=v.semester AND verband=v.verband ORDER BY gruppe LIMIT 1)) AS name", false);
		$this->StudiengangModel->addSelect("CASE WHEN MAX(gruppe)='' OR MAX(gruppe)=' ' THEN TRUE ELSE FALSE END AS leaf");

		$this->StudiengangModel->addSelect('verband');
		$this->StudiengangModel->addSelect($this->StudiengangModel->escape($studiengang_kz) . '::integer AS stg_kz', false);
		
		$this->StudiengangModel->addOrder('verband');

		$this->StudiengangModel->addGroupBy('link, name, verband');
		
		$where = [
			'v.studiengang_kz' => $studiengang_kz,
			'v.semester' => $semester,
			'v.verband !=' => '',
			'v.aktiv' => true
		];

		if ($org_form !== null && $semester) // NOTE(chris): on semester 0 show all?
			$where['v.orgform_kurzbz'] = $org_form;

		$result = $this->StudiengangModel->loadWhere($where);
		$result = $this->getDataOrTerminateWithError($result);

		$list = array_merge($list, $result);

		$this->terminateWithSuccess($list);
	}

	/**
	 * @param integer		$studiengang_kz
	 * @param integer		$semester
	 * @param integer		$verband
	 * @param string		$orgform
	 *
	 * @return void
	 */
	protected function getVerband($studiengang_kz, $semester, $verband, $org_form = null)
	{
		$link = $studiengang_kz . '/';
		if ($org_form !== null)
			$link .= $org_form . '/';
		$link .= $semester . '/'. $verband . '/';


		$this->StudiengangModel->addJoin('public.tbl_lehrverband v', 'studiengang_kz');
		
		$this->StudiengangModel->addDistinct();
		$this->StudiengangModel->addSelect("CONCAT(" . $this->StudiengangModel->escape($link) . ", gruppe) AS link", false);
		$this->StudiengangModel->addSelect("CONCAT(UPPER(CONCAT(typ, kurzbz)), '-', semester, verband, gruppe, (SELECT CASE WHEN bezeichnung IS NULL OR bezeichnung='' THEN ''::TEXT ELSE CONCAT(' (', bezeichnung, ')') END FROM public.tbl_lehrverband WHERE studiengang_kz=v.studiengang_kz AND semester=v.semester AND verband=v.verband AND gruppe=v.gruppe ORDER BY gruppe LIMIT 1)) AS name", false);
		$this->StudiengangModel->addSelect("TRUE AS leaf", false);

		$this->StudiengangModel->addSelect('gruppe');
		$this->StudiengangModel->addSelect($this->StudiengangModel->escape($studiengang_kz) . '::integer AS stg_kz', false);
		
		$this->StudiengangModel->addOrder('gruppe');

		$where = [
			'v.studiengang_kz' => $studiengang_kz,
			'v.semester' => $semester,
			'v.verband' => $verband,
			'v.gruppe !=' => '',
			'v.aktiv' => true
		];

		if ($org_form !== null && $semester) // NOTE(chris): on semester 0 show all?
			$where['v.orgform_kurzbz'] = $org_form;

		$result = $this->StudiengangModel->loadWhere($where);

		$list = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess($list);
	}

	/**
	 * @param string		$link
	 * @param integer		$studiengang_kz
	 *
	 * @return array
	 */
	protected function getStdSem($link, $studiengang_kz)
	{
		$this->load->model('organisation/Studiensemester_model', 'StudiensemesterModel');

		$this->load->model('system/Variable_model', 'VariableModel');
		$result = $this->VariableModel->getVariables(getAuthUID(), ['number_displayed_past_studiensemester']);
		$data = $this->getDataOrTerminateWithError($result);
		$number_displayed_past_studiensemester = $data['number_displayed_past_studiensemester'] ?? null;

		$this->StudiensemesterModel->addPlusMinus(null, $number_displayed_past_studiensemester);
		$this->StudiensemesterModel->addOrder('ende');
		$result = $this->StudiensemesterModel->load();

		$studiensemester = $this->getDataOrTerminateWithError($result);
		$result = [];

		$studiengang_kz = (int)$studiengang_kz;

		foreach ($studiensemester as $sem) {
			$semlink = $link . $sem->studiensemester_kurzbz;
			$intlink = $semlink . '/interessenten';
			$result[] = [
				'name' => $sem->studiensemester_kurzbz,
				'link' => $semlink,
				'stg_kz' => $studiengang_kz,
				'children' => [
					[
						'name' => 'Interessenten',
						'link' => $intlink,
						'stg_kz' => $studiengang_kz,
						'children' => [
							[
								'name' => 'Bewerbung nicht abgeschickt',
								'link' => $intlink . '/bewerbungnichtabgeschickt',
								'stg_kz' => $studiengang_kz,
								'leaf' => true
							],
							[
								'name' => 'Bewerbung abgeschickt, Status unbestätigt',
								'link' => $intlink . '/bewerbungabgeschickt',
								'stg_kz' => $studiengang_kz,
								'leaf' => true
							],
							[
								'name' => 'ZGV erfüllt',
								'link' => $intlink . '/zgv',
								'stg_kz' => $studiengang_kz,
								'leaf' => true
							],
							[
								'name' => 'Status bestätigt',
								'link' => $intlink . '/statusbestaetigt',
								'stg_kz' => $studiengang_kz,
								'children' => [
									[
										'name' => 'Nicht zum Reihungstest angemeldet',
										'link' => $intlink . '/statusbestaetigtrtnichtangemeldet',
										'leaf' => true
									],
									[
										'name' => 'Reihungstest angemeldet',
										'link' => $intlink . '/statusbestaetigtrtangemeldet',
										'leaf' => true
									]
								]
							],
							[
								'name' => 'Nicht zum Reihungstest angemeldet',
								'link' => $intlink . '/reihungstestnichtangemeldet',
								'stg_kz' => $studiengang_kz,
								'leaf' => true
							],
							[
								'name' => 'Reihungstest angemeldet',
								'link' => $intlink . '/reihungstestangemeldet',
								'stg_kz' => $studiengang_kz,
								'leaf' => true
							]
						]
					],
					[
						'name' => 'Bewerber',
						'link' => $semlink . '/bewerber',
						'stg_kz' => $studiengang_kz,
						'children' => [
							[
								'name' => 'Nicht zum Reihungstest angemeldet',
								'link' => $intlink . '/bewerberrtnichtangemeldet',
								'stg_kz' => $studiengang_kz,
								'leaf' => true
							],
							[
								'name' => 'Reihungstest angemeldet',
								'link' => $intlink . '/bewerberrtangemeldet',
								'stg_kz' => $studiengang_kz,
								'children' => [
									[
										'name' => 'Teilgenommen',
										'link' => $intlink . '/bewerberrtangemeldetteilgenommen',
										'stg_kz' => $studiengang_kz,
										'leaf' => true
									],
									[
										'name' => 'Nicht teilgenommen',
										'link' => $intlink . '/bewerberrtangemeldetnichtteilgenommen',
										'stg_kz' => $studiengang_kz,
										'leaf' => true
									]
								]
							]
						]
					],
					[
						'name' => 'Aufgenommen',
						'link' => $semlink . '/aufgenommen',
						'stg_kz' => $studiengang_kz,
						'leaf' => true
					],
					[
						'name' => 'Warteliste',
						'link' => $semlink . '/warteliste',
						'stg_kz' => $studiengang_kz,
						'leaf' => true
					],
					[
						'name' => 'Absage',
						'link' => $semlink . '/absage',
						'stg_kz' => $studiengang_kz,
						'leaf' => true
					],
					[
						'name' => 'Incoming',
						'link' => $semlink . '/incoming',
						'stg_kz' => $studiengang_kz,
						'leaf' => true
					]
				]
			];
		}

		return $result;
	}
}
