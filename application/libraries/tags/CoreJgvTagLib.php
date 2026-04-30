<?php
/**
 * Description of jgv_auto (Jahrgangsvertretung)
 *
 * @author ma0068
 */
class CoreJgvTagLib
{
	protected $ci;

	public function __construct()
	{
		$this->ci = get_instance();
		$this->ci->load->model('person/Benutzerfunktion_model', 'BenutzerfunktionModel');
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

		$result = $this->ci->BenutzerfunktionModel->getPrestudentsOfJgv($semester);

		$data = $result->retval;

		$jgv_data = array_map(function($item) {
			return [
				'typeId' => 'prestudent_id',
				'id' => $item->prestudent_id,
				'von' => $item->datum_von,
				'bis' => $item->datum_bis
			];
		}, $data);

		return (object) array(
			'data' => $jgv_data
		);
	}

	public function isCriteriaSetFor(array $params)
	{
		if ( !isset($params['id'], $params['studiensemester_kurzbz'], $params['typeId']) ||	$params['typeId'] !== 'prestudent_id')
			return false;

		$semester = $params['studiensemester_kurzbz'];
		$prestudent_id = $params['id'];

		$result = $this->ci->BenutzerfunktionModel->isJgv($semester, $prestudent_id);

		if(hasData($result))
		{
			return $result;
		}
		else
			return null;
	}

}
