<?php
/**
 * Description of dd_auto
 *
 * @author ma0068
 */
class CoreDoubleDegreeTagLib
{
	protected $ci;

	public function __construct()
	{
		$this->ci = get_instance();
		$this->ci->load->model('codex/Mobilitaet_model', 'MobilitaetModel');
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

		$this->ci->MobilitaetModel->addJoin('bis.tbl_gsprogramm', 'gsprogramm_id');
		$this->ci->MobilitaetModel->addJoin('public.tbl_studiensemester', 'studiensemester_kurzbz');

		$result = $this->ci->MobilitaetModel-> loadWhere(array(
			'gsprogrammtyp_kurzbz' => 'Double',
			'studiensemester_kurzbz' => $semester
		));
		$data = $result->retval;

		$doubledegree_data = array_map(function($item) {
			return [
				'prestudent_id' => $item->prestudent_id,
				'von' => $item->start,
				'bis' => $item->ende
			];
		}, $data);

		return (object) array(
			'data' => $doubledegree_data
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

		$this->ci->MobilitaetModel->addSelect('prestudent_id');
		$this->ci->MobilitaetModel->addSelect('start as von');
		$this->ci->MobilitaetModel->addSelect('ende as bis');

		$this->ci->MobilitaetModel->addJoin('bis.tbl_gsprogramm', 'gsprogramm_id');
		$this->ci->MobilitaetModel->addJoin('public.tbl_studiensemester', 'studiensemester_kurzbz');

		$result = $this->ci->MobilitaetModel->loadWhere(array(
			'gsprogrammtyp_kurzbz' => 'Double',
			'studiensemester_kurzbz' => $semester,
			'prestudent_id' => $prestudent_id
		));

		if(hasData($result))
		{
			//array mit prestudent_id, von und bis
			return $result;
		}
		else
			return null;
	}

}
