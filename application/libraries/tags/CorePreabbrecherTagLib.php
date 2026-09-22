<?php
/**
 * Description of prewh_auto
 *
 * @author ma0068
 */
class CorePreabbrecherTagLib
{
	protected $ci;
	const TYP_ABMELDUNG = ['Abmeldung', 'AbmeldungStgl'];

	public function __construct()
	{
		$this->ci = get_instance();
		$this->ci->load->model('education/Studierendenantrag_model', 'StudierendenantragModel');
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

		$this->ci->StudierendenantragModel->addJoin('public.tbl_studiensemester', 'studiensemester_kurzbz');
		$this->ci->StudierendenantragModel->db->where_in('typ', self::TYP_ABMELDUNG);
		$this->ci->StudierendenantragModel->db->where('studiensemester_kurzbz', $semester);
		$result = $this->ci->StudierendenantragModel->getWithLastStatusWhere([
			'studierendenantrag_statustyp_kurzbz' => Studierendenantragstatus_model::STATUS_CREATED
		]);

		$data = $result->retval;

		$prewiederholer_data = array_map(function($item) {
			return [
				'id' => $item->prestudent_id,
				'von' => $item->start,
				'bis' => $item->ende
			];
		}, $data);

		return (object) array(
			'data' => $prewiederholer_data,
			'typeId' => 'prestudent_id',
		);
	}

	public function isCriteriaSetFor(array $params)
	{
		if ( !isset($params['id'], $params['studiensemester_kurzbz'], $params['typeId']) ||	$params['typeId'] !== 'prestudent_id')
			return false;

		$semester = $params['studiensemester_kurzbz'];
		$prestudent_id = $params['id'];

		$this->ci->StudierendenantragModel->addSelect('prestudent_id');
		$this->ci->StudierendenantragModel->addSelect('start as von');
		$this->ci->StudierendenantragModel->addSelect('ende as bis');

		$this->ci->StudierendenantragModel->addJoin('public.tbl_studiensemester', 'studiensemester_kurzbz');
		$this->ci->StudierendenantragModel->db->where_in('typ', self::TYP_ABMELDUNG);
		$this->ci->StudierendenantragModel->db->where('studiensemester_kurzbz', $semester);
		$this->ci->StudierendenantragModel->db->where('prestudent_id', $prestudent_id);
		$result = $this->ci->StudierendenantragModel->getWithLastStatusWhere([
			'studierendenantrag_statustyp_kurzbz' => Studierendenantragstatus_model::STATUS_CREATED
		]);

		if(hasData($result))
		{
			return $result;
		}
		else
			return null;
	}

}
