<?php
/**
 * Description of prewh_auto
 *
 * @author bambi
 */
class CorePrewiederholerTagLib
{
	protected $ci;

	public function __construct()
	{
		$this->ci = get_instance();
		$this->ci->load->model('crm/Prestudentstatus_model', 'PrestudentstatusModel');
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

		$this->ci->PrestudentstatusModel->addJoin('public.tbl_studiensemester', 'studiensemester_kurzbz');
		$result = $this->ci->PrestudentstatusModel-> loadWhere(array(
			'statusgrund_id' => 15,
			'studiensemester_kurzbz' => $semester
		));
		$data = $result->retval;

		$prewiederholer_data = array_map(function($item) {
			return [
				'typeId' => 'prestudent_id',
				'id' => $item->prestudent_id,
				'von' => $item->start,
				'bis' => $item->ende
			];
		}, $data);

		return (object) array(
			'data' => $prewiederholer_data
		);
	}

	public function isCriteriaSetFor(array $params)
	{
		if ( !isset($params['id'], $params['studiensemester_kurzbz'], $params['typeId']) ||	$params['typeId'] !== 'prestudent_id')
			return false;

		$semester = $params['studiensemester_kurzbz'];
		$prestudent_id = $params['id'];

		$this->ci->PrestudentstatusModel->addSelect('prestudent_id');
		$this->ci->PrestudentstatusModel->addSelect('start as von');
		$this->ci->PrestudentstatusModel->addSelect('ende as bis');

		$this->ci->PrestudentstatusModel->addJoin('public.tbl_studiensemester', 'studiensemester_kurzbz');

		$result = $this->ci->PrestudentstatusModel->loadWhere(array(
			'statusgrund_id' => 15,
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
