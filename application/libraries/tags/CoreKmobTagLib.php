<?php
/**
 * Description of kMob_auto
 *
 * @author ma0068
 */
class CoreKmobTagLib
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

		$result = $this->ci->BisioModel->getShortMobilitiesOfSemester($semester);

		$data = $result->retval;

		$outShortData = array_map(function($item) {
			return [
				'id' => $item->prestudent_id,
				'von' => $item->von,
				'bis' => $item->bis
			];
		}, $data);

		return (object) array(
			'data' => $outShortData,
			'typeId' => 'prestudent_id',
		);
	}

	public function isCriteriaSetFor(array $params)
	{
		if ( !isset($params['id'], $params['studiensemester_kurzbz'], $params['typeId']) ||	$params['typeId'] !== 'prestudent_id')
			return false;

		$semester = $params['studiensemester_kurzbz'];
		$prestudent_id = $params['id'];

		$result = $this->ci->BisioModel->isPrestudentShortMob($semester, $prestudent_id);

		if (hasData($result)) {
			return $result;
		}
		else
			return null;
	}

}
