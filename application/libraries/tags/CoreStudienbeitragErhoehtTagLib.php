<?php
/**
 * Description of dd_auto
 *
 * @author ma0068
 */
class CoreStudienbeitragErhoehtTagLib
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
				'prestudent_id' => []
			);
		}

		$semester = $params['studiensemester_kurzbz'];

		$this->ci->KontoModel->addJoin('public.tbl_prestudent', 'person_id');
		$result = $this->ci->KontoModel-> loadWhere(array(
			'buchungstyp_kurzbz' => 'StudiengebuehrErhoeht',
			'studiensemester_kurzbz' => $semester
		));
		$data = $result->retval;
		$ids = array_map(function($item) {
			return $item->prestudent_id;
		}, $data);

		return (object) array(
			'prestudent_id' => $ids
		);
	}

	public function isCriteriaSetFor(array $params)
	{
		if(!isset($params['prestudent_id']) || !isset($params['studiensemester_kurzbz']))
		{
			return false;
		}

		$semester = $params['studiensemester_kurzbz'];
		$prestudent_id = $params['prestudent_id'];

		$this->ci->KontoModel->addJoin('public.tbl_prestudent', 'person_id');
		$result = $this->ci->KontoModel-> loadWhere(array(
			'buchungstyp_kurzbz' => 'StudiengebuehrErhoeht',
			'studiensemester_kurzbz' => $semester,
			'prestudent_id' => $prestudent_id
		));

		if(hasData($result))
		{
			return true;
		}
		else
			return false;
	}

}
