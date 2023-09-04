<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

class Verband extends FHC_Controller
{
	public function __construct()
	{
		// TODO(chris): access!
		parent::__construct();
		
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

		$count = count($params);
		if (!$count)
			return $this->getStudiengang($method);

		if ($count == 1) {
			if (is_numeric($params[0]))
				return $this->getSemester($method, $params[0]);
			else
				return $this->getStudiengang($method, $params[0]);
		}
		if ($count == 2) {
			if (is_numeric($params[0]))
				return $this->getVerband($method, $params[0], $params[1]);
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
		$this->StudiengangModel->addSelect("CONCAT(kurzbzlang, ' (', UPPER(CONCAT(typ, kurzbz)), ') - ', tbl_studiengang.bezeichnung) AS name", false);
		$this->StudiengangModel->addSelect('erhalter_kz');
		$this->StudiengangModel->addSelect('typ');
		$this->StudiengangModel->addSelect('kurzbz');
		
		$this->StudiengangModel->addOrder('erhalter_kz');
		$this->StudiengangModel->addOrder('typ');
		$this->StudiengangModel->addOrder('kurzbz');

		$result = $this->StudiengangModel->loadWhere(['v.aktiv' => true]);
		if (isError($result)) {
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			return $this->outputJson(getError($result));
		}

		$list = getData($result) ?: [];
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
		$this->outputJson($list);
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
		if (isError($result)) {
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			return $this->outputJson(getError($result));
		}

		$list = getData($result) ?: [];
		array_unshift($list, [
			'name' => 'PreStudent',
			'link' => $link . 'prestudent',
			'children' => $this->getStdSem($link . 'prestudent/')
		]);

		if ($org_form === null) {
			// NOTE(chris): if mischform show orgforms
			$result = $this->StudiengangModel->load($studiengang_kz);
			if (isError($result)) {
				$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
				return $this->outputJson(getError($result));
			}
			if (hasData($result)) {
				if (current(getData($result))->mischform) {
					$this->load->model('organisation/Studienordnung_model', 'StudienordnungModel');

					$this->StudienordnungModel->addDistinct();
					$this->StudienordnungModel->addSelect("CONCAT(studiengang_kz, '/', p.orgform_kurzbz) AS link");
					$this->StudienordnungModel->addSelect("p.orgform_kurzbz AS name");

					// TODO(chris): semester for gruppe_kurzbz <- what did i mean by that?

					$this->StudienordnungModel->addJoin('lehre.tbl_studienplan p', 'studienordnung_id');

					$result = $this->StudienordnungModel->loadWhere([
						'aktiv' => true,
						'studiengang_kz' => $studiengang_kz,
						'p.orgform_kurzbz !=' => 'DDP'
					]);
					if (isError($result)) {
						$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
						return $this->outputJson(getError($result));
					}

					if (hasData($result))
						$list = array_merge($list, getData($result));
				}
			}

		}
		$this->outputJson($list);
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
		if (isError($result)) {
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			return $this->outputJson(getError($result));
		}

		$list = getData($result) ?: [];


		$this->StudiengangModel->addJoin('public.tbl_lehrverband v', 'studiengang_kz');
		
		$this->StudiengangModel->addSelect("CONCAT(" . $this->StudiengangModel->escape($link) . ", verband) AS link", false);
		$this->StudiengangModel->addSelect("CONCAT(UPPER(CONCAT(typ, kurzbz)), '-', semester, verband, (SELECT CASE WHEN bezeichnung IS NULL OR bezeichnung='' THEN ''::TEXT ELSE CONCAT(' (', bezeichnung, ')') END FROM public.tbl_lehrverband WHERE studiengang_kz=v.studiengang_kz AND semester=v.semester AND verband=v.verband ORDER BY gruppe LIMIT 1)) AS name", false);
		$this->StudiengangModel->addSelect("CASE WHEN MAX(gruppe)='' OR MAX(gruppe)=' ' THEN TRUE ELSE FALSE END AS leaf");

		$this->StudiengangModel->addSelect('verband');
		
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
		if (isError($result)) {
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			return $this->outputJson(getError($result));
		}

		$list = array_merge($list, getData($result) ?: []);

		$this->outputJson($list);
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
		if (isError($result)) {
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			return $this->outputJson(getError($result));
		}

		$list = getData($result) ?: [];

		$this->outputJson($list);
	}

	/**
	 * @param string		$link
	 *
	 * @return array
	 */
	protected function getStdSem($link)
	{
		$this->load->model('organisation/Studiensemester_model', 'StudiensemesterModel');

		/**
		 * TODO(chris): filter with variable:
		 * - $number_displayed_past_studiensemester from Variable
		 * - then: $stsem_obj->getPlusMinus(NULL, $number_displayed_past_studiensemester, 'ende ASC');
		 */
		$result = $this->StudiensemesterModel->load();
		if (isError($result)) {
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			$this->outputJson(getError($result));
			exit;
		}

		$studiensemester = getData($result) ?: [];
		$result = [];

		foreach ($studiensemester as $sem) {
			$semlink = $link . $sem->studiensemester_kurzbz;
			$intlink = $semlink . '/interessenten';
			$result[] = [
				'name' => $sem->studiensemester_kurzbz,
				'link' => $semlink,
				'children' => [
					[
						'name' => 'Interessenten',
						'link' => $intlink,
						'children' => [
							[
								'name' => 'Bewerbung nicht abgeschickt',
								'link' => $intlink . '/bewerbungnichtabgeschickt',
								'leaf' => true
							],
							[
								'name' => 'Bewerbung abgeschickt, Status unbestätigt',
								'link' => $intlink . '/bewerbungabgeschickt',
								'leaf' => true
							],
							[
								'name' => 'ZGV erfüllt',
								'link' => $intlink . '/zgv',
								'leaf' => true
							],
							[
								'name' => 'Status bestätigt',
								'link' => $intlink . '/statusbestaetigt',
								'children' => [
									[
										'name' => 'Nicht zum Reihungstest angemeldet',
										'link' => $intlink . '/statusbestaetigt/reihungstestnichtangemeldet',
										'leaf' => true
									],
									[
										'name' => 'Reihungstest angemeldet',
										'link' => $intlink . '/statusbestaetigt/reihungstestangemeldet',
										'leaf' => true
									]
								]
							],
							[
								'name' => 'Nicht zum Reihungstest angemeldet',
								'link' => $intlink . '/reihungstestnichtangemeldet',
								'leaf' => true
							],
							[
								'name' => 'Reihungstest angemeldet',
								'link' => $intlink . '/reihungstestangemeldet',
								'leaf' => true
							]
						]
					],
					[
						'name' => 'Bewerber',
						'link' => $semlink . '/bewerber',
						'leaf' => true
					],
					[
						'name' => 'Aufgenommen',
						'link' => $semlink . '/aufgenommen',
						'leaf' => true
					],
					[
						'name' => 'Warteliste',
						'link' => $semlink . '/warteliste',
						'leaf' => true
					],
					[
						'name' => 'Absage',
						'link' => $semlink . '/absage',
						'leaf' => true
					],
					[
						'name' => 'Incoming',
						'link' => $semlink . '/incoming',
						'leaf' => true
					]
				]
			];
		}

		return $result;
	}
}
