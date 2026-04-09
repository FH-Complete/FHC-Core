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
		$result = $this->ci->PrestudentstatusModel-> loadWhere(array(
			'statusgrund_id' => 16,
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


}
