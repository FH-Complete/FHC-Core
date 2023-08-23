<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

class Studentenverwaltung extends FHC_Controller
{
	public function __construct()
	{
		// TODO(chris): access!
		parent::__construct();
	}

	/**
	 * @return void
	 */
	public function index()
	{
		// TODO(chris): load stgs (this is just for testing)
		$this->load->model('organisation/Studiengang_model', 'StudiengangModel');
		$result = $this->StudiengangModel->loadWhere(['aktiv' => true]);
		if (isError($result))
			return $this->outputJson($result);
		if (!hasData($result))
			return $this->outputJsonSuccess([]);
		$list = getData($result);
		$list[] = [
			'name' => 'International',
			'children' => [
				[
					'name' => 'Incoming',
					'leaf' => true
				],
				[
					'name' => 'Outgoing',
					'leaf' => true
				],
				[
					'name' => 'Gemeinsame Studien',
					'leaf' => true
				]
			]
		];
		$this->outputJsonSuccess($list);
	}

	/**
	 * @param integer		$studiengang_kz
	 * @return void
	 */
	public function getStudiengang($studiengang_kz)
	{
		// TODO(chris): load stgSemester + prestudent
		$this->outputJson([
			[
				'key' => 2,
				'name' => 'PreStudent'
			]
		]);
	}
}
