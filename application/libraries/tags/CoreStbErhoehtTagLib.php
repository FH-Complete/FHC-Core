<?php
/**
 * Description of dd_auto
 *
 * @author ma0068
 */
class CoreStbErhoehtTagLib
{
	protected $ci;

	public function __construct()
	{
		$this->ci = get_instance();
		$this->ci->load->model('crm/Konto_model', 'KontoModel');
	}

	public function getZuordnungIds(array $params)
	{
		if(!isset($params['studiensemester_kurzbz']))
		{
			return (object) array(
				'idArray' => []
			);
		}

		$semester = $params['studiensemester_kurzbz'];

		$this->ci->KontoModel->addJoin('public.tbl_prestudent', 'person_id');
		$this->ci->KontoModel->addJoin('public.tbl_studiensemester', 'studiensemester_kurzbz');

		$result = $this->ci->KontoModel-> loadWhere(array(
			'buchungstyp_kurzbz' => 'StudiengebuehrErhoeht',
			'studiensemester_kurzbz' => $semester
		));
		$data = $result->retval;

		$konto_data = array_map(function($item) {
			return [
				'typeId' => 'prestudent_id',
				'id' => $item->prestudent_id,
				'von' => $item->start,
				'bis' => $item->ende
			];
		}, $data);

		return (object) array(
			'data' => $konto_data
		);
	}

	public function isCriteriaSetFor(array $params)
	{
		if ( !isset($params['id'], $params['studiensemester_kurzbz'], $params['typeId']) ||	$params['typeId'] !== 'prestudent_id')
			return false;

		$semester = $params['studiensemester_kurzbz'];
		$prestudent_id = $params['id'];

		$this->ci->KontoModel->addSelect('prestudent_id');
		$this->ci->KontoModel->addSelect('start as von');
		$this->ci->KontoModel->addSelect('ende as bis');
		$this->ci->KontoModel->addJoin('public.tbl_prestudent', 'person_id');
		$this->ci->KontoModel->addJoin('public.tbl_studiensemester', 'studiensemester_kurzbz');

		$result = $this->ci->KontoModel-> loadWhere(array(
			'buchungstyp_kurzbz' => 'StudiengebuehrErhoeht',
			'studiensemester_kurzbz' => $semester,
			'prestudent_id' => $prestudent_id
		));

		if(hasData($result))
		{
			return $result;
		}
		else
			return null;
	}

}
