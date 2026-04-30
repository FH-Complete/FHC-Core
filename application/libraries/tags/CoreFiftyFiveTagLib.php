<?php
/**
 * Description unruly
 * Test for different typeId
 *
 * @author ma0068
 */
class CoreFiftyFiveTagLib
{
	protected $ci;

	public function __construct()
	{
		$this->ci = get_instance();
		$this->ci->load->model('person/Person_model', 'PersonModel');
		$this->ci-> load->model('organisation/Studiensemester_model', 'StudiensemesterModel');
	}

	public function getZuordnungIds(array $params)
	{
		if(!isset($params['studiensemester_kurzbz']))
		{
			return (object) array(
				'person_id' => []
			);
		}

		$semester = $params['studiensemester_kurzbz'];

		$result = $this->ci->StudiensemesterModel->loadWhere(array(
			'studiensemester_kurzbz' => $semester
		));
		$data = $result->retval[0];

		$semVon = $data->start;
		$semBis = $data->ende;
		$result = $this->ci->PersonModel->getFiftyFivers($semVon, $semBis);

		$data = $result->retval;
		$fiftyFiveData = array_map(function($item) {
			return [
				'id' => $item->person_id,
				'typeId' => 'person_id',
			];
		}, $data);

		return (object) array(
			'data' => $fiftyFiveData
		);
	}

	public function isCriteriaSetFor(array $params)
	{
		if(!isset($params['person_id']) || !isset($params['studiensemester_kurzbz']))
		{
			return false;
		}
		$semester = $params['studiensemester_kurzbz'];
		$person_id = $params['id'];
		$typeId = $params['typeId'];

		if($typeId != 'person_id')
			return null;

		$result = $this->ci->StudiensemesterModel->loadWhere(array(
			'studiensemester_kurzbz' => $semester
		));
		$data = $result->retval[0];

		$semVon = $data->start;
		$semBis = $data->ende;
		$result = $this->ci->PersonModel->isFiftyFive($semVon, $semBis, $person_id);

		$data = $result->retval;
		$fiftyFiveData = array_map(function($item) {
			return [
				'id' => $item->person_id,
				'typeId' => 'person_id',
			];
		}, $data);

		if(hasData($result))
		{
			//array mit prestudent_id, von und bis
			return $result;
		}
		else
			return null;
	}

}

