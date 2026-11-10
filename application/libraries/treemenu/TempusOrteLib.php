<?php
if (! defined('BASEPATH')) exit('No direct script access allowed');

class TempusOrteLib
{
	protected $_ci = null;
	public $config = [];

	public function __construct()
	{
		$this->_ci =& get_instance();

		$this->_ci->load->config('treemenu/custom/tempusorte');
		$this->config = $this->_ci->config->item('root');

		$this->_ci->load->model('ressource/Raumtyp_model', 'RaumtypModel');
		$this->_ci->load->model('ressource/Ort_model', 'OrtModel');
	}

	public function raumtyp()
	{

		$this->_ci->RaumtypModel->addSelect("tbl_raumtyp.raumtyp_kurzbz AS raumtyp_name");
		$this->_ci->RaumtypModel->addSelect('tbl_raumtyp.raumtyp_kurzbz');
		$this->_ci->RaumtypModel->addSelect('tbl_ort.ort_kurzbz as ort_name');
		$this->_ci->RaumtypModel->addSelect('tbl_ort.ort_kurzbz');
		$this->_ci->RaumtypModel->addSelect('tbl_ort.max_person');

		$this->_ci->RaumtypModel->addJoin('public.tbl_ortraumtyp', 'raumtyp_kurzbz');
		$this->_ci->RaumtypModel->addJoin('public.tbl_ort', 'ort_kurzbz');

		$this->_ci->RaumtypModel->addOrder('tbl_raumtyp.raumtyp_kurzbz, tbl_ort.ort_kurzbz');

		$result = $this->_ci->RaumtypModel->loadWhere(array('public.tbl_ort.aktiv' => true));

		$typen = [];

		if (!hasData($result))
			return [];

		foreach (getData($result) as $raum)
		{
			if (!isset($typen[$raum->raumtyp_kurzbz]))
			{
				$typen[$raum->raumtyp_kurzbz] = [
					'path' => $raum->raumtyp_kurzbz,
					'name' => $raum->raumtyp_name,
					'raumtyp_kurzbz' => $raum->raumtyp_kurzbz,
					'children' => [],
				];
			}

			$typen[$raum->raumtyp_kurzbz]['children'][] = [
				'path' => $raum->raumtyp_kurzbz . '/' . $raum->ort_kurzbz,
				'name' => $raum->ort_name,
				'ort_kurzbz' => $raum->ort_kurzbz,
				'max_person' => $raum->max_person,
				'leaf' => true,
			];
		}

		return array_values($typen);
	}
}