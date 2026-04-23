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

		$outgoing_data = array_map(function($item) {
			return [
				'prestudent_id' => $item->prestudent_id,
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
		if (!isset($params['prestudent_id']) || !isset($params['studiensemester_kurzbz'])) {
			return false;
		}

		$semester = $params['studiensemester_kurzbz'];
		$prestudent_id = $params['prestudent_id'];

		$result = $this->ci->BisioModel->isPrestudentOutgoing($semester, $prestudent_id);

		if (hasData($result)) {
			return true;
		} else
			return false;
		}

/*		if(hasData($result))
		{
			return array(
				$result
			);

			var_dump($result);
			die();

			$row = $result->data->retval[0] ?? null;
			return [
				'prestudent_id' => $row->prestudent_id, //trying to get property of non-object
				'von' => $row->von, //trying to get property of non-object
				'bis' => $row->bis //trying to get property of non-object
			];


			return current($result);//-> warum retvall = []
			return getData($result); //-> warum retvall = []

			$row = $result->data->retval[0] ?? null;
			$row = $result->data->retval[0] ?? null;
			return [
				'prestudent_id' => $row->prestudent_id,
				'von' => $row->von,
				'bis' => $row->bis
			];
		}
		else
			return false;*/

		//return $result;
/*		if(hasData($result))
		{
			$data = $result->retval;
			return $result;
			return [
				'prestudent_id' => $data->prestudent_id,
				'von' => $data->von,
				'bis' => $data->bis
			];
		}
		else
			return [
				'prestudent_id' => $prestudent_id,
				'von' => null,
				'bis' => null
			];*/

/*		return (object) array(
			'data' => $data
		);*/

	//	return $data;

	//}

}
