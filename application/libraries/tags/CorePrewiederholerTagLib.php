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
				'prestudent_id' => []
			);
		}

		$semester = $params['studiensemester_kurzbz'];
		$result = $this->ci->PrestudentstatusModel-> loadWhere(array(
			'statusgrund_id' => 15,
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
			return (object) array(
				'isSet' => false
			);
		}

		$semester = $params['studiensemester_kurzbz'];
		$prestudent_id = $params['prestudent_id'];

		$result = $this->ci->PrestudentstatusModel->loadWhere(array(
			'statusgrund_id' => 15,
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
