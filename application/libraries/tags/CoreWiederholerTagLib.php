<?php
/**
 * Description of wh_auto
 *
 * @author bambi
 */
class CoreWiederholerTagLib
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
				'prestudent_id' => []
			);
		}

		$semester = $params['studiensemester_kurzbz'];

		$this->ci->PrestudentstatusModel->addJoin('public.tbl_studiensemester', 'studiensemester_kurzbz');
		$result = $this->ci->PrestudentstatusModel-> loadWhere(array(
			'statusgrund_id' => 16,
			'studiensemester_kurzbz' => $semester
		));
		$data = $result->retval;

		$wiederholer_data = array_map(function($item) {
			return [
				'prestudent_id' => $item->prestudent_id,
				'von' => $item->start,
				'bis' => $item->ende
			];
		}, $data);

		return (object) array(
			'data' => $wiederholer_data
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

		$result = $this->ci->PrestudentstatusModel->loadWhere(array(
			'statusgrund_id' => 16,
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
