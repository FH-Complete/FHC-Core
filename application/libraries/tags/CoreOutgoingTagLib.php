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
				'idArray' => []
			);
		}

		$semester = $params['studiensemester_kurzbz'];

		$result = $this->ci->BisioModel->getOutgoingsOfSemester($semester);

		$data = $result->retval;

		$outgoing_data = array_map(function($item) {
			return [
				'typeId' => 'prestudent_id',
				'id' => $item->prestudent_id,
				'von' => $item->von,
				'bis' => $item->bis
			];
		}, $data);

		return (object) array(
			'data' => $outgoing_data
		);
	}

	public function isCriteriaSetFor(array $params)
	{
		if ( !isset($params['id'], $params['studiensemester_kurzbz'], $params['typeId']) ||	$params['typeId'] !== 'prestudent_id')
			return false;

		$semester = $params['studiensemester_kurzbz'];
		$prestudent_id = $params['id'];

		$result = $this->ci->BisioModel->isPrestudentOutgoing($semester, $prestudent_id);

		if (hasData($result)) {
			return $result;
		}
		else
			return null;
	}

}
