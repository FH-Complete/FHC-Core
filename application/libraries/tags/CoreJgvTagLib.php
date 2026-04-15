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
				'prestudent_id' => []
			);
		}

		$semester = $params['studiensemester_kurzbz'];

		$result = $this->ci->BenutzerfunktionModel->getPrestudentsOfJgv($semester);

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

		$result = $this->ci->BenutzerfunktionModel->isJgv($semester, $prestudent_id);

		if(hasData($result))
		{
			return true;
		}
		else
			return false;
	}

}
