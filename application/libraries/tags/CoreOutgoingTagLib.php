<?php
/**
 * Description of out_auto
 *
 * @author ma0068
 */
class CoreOutgoingTagLib
{
	protected $ci;

	public function __construct()
	{
		$this->ci = get_instance();
		$this->ci->load->model('codex/Bisio_model', 'BisioModel');
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

		$result = $this->ci->BisioModel->getOutgoingsOfSemester($semester);

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

		$result = $this->ci->BisioModel->isPrestudentOutgoing($semester, $prestudent_id);
		if(hasData($result))
		{
			return true;
		}
		else
			return false;
	}

}
