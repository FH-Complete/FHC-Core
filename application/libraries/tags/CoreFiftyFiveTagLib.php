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
				'id' => $item->person_id
			];
		}, $data);

		return (object) array(
			'data' => $fiftyFiveData,
			'typeId' => 'person_id'
		);
	}

	public function isCriteriaSetFor(array $params)
	{
		if ( !isset($params['id'], $params['studiensemester_kurzbz'], $params['typeId']) ||	$params['typeId'] !== 'person_id')
			return false;

		$semester = $params['studiensemester_kurzbz'];
		$person_id = $params['id'];
		$typeId = $params['typeId'];


		$result = $this->ci->StudiensemesterModel->loadWhere(array(
			'studiensemester_kurzbz' => $semester
		));
		$data = $result->retval[0];

		$semVon = $data->start;
		$semBis = $data->ende;
		$result = $this->ci->PersonModel->isFiftyFive($semVon, $semBis, $person_id);


		if(hasData($result))
		{
			return $result;
		}
		else
			return null;
	}

}

