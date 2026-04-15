<?php
/**
 * Description of zgv_auto
 *
 * @author ma0068
 */
class CoreMissingZgvTagLib
{
	protected $ci;

	public function __construct()
	{
		$this->ci = get_instance();
		$this->ci->load->model('crm/Prestudent_model', 'PrestudentModel');
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

		$this->ci->PrestudentModel->addJoin('public.tbl_prestudentstatus', 'prestudent_id');
		$this->ci->PrestudentModel->addJoin('public.tbl_benutzer bn', 'person_id');
		$this->ci->PrestudentModel->addJoin('public.tbl_studiengang', 'studiengang_kz');
		$result = $this->ci->PrestudentModel-> loadWhere(array(
			'bn.aktiv' => true, //check if necessary
			'zgvdatum' => null,
			'typ' => 'b',
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
			return false;
		}

		$semester = $params['studiensemester_kurzbz'];
		$prestudent_id = $params['prestudent_id'];

		$this->ci->PrestudentModel->addJoin('public.tbl_prestudentstatus', 'prestudent_id');
		$this->ci->PrestudentModel->addJoin('public.tbl_benutzer bn', 'person_id');
		$this->ci->PrestudentModel->addJoin('public.tbl_studiengang', 'studiengang_kz');
		$result = $this->ci->PrestudentModel->loadWhere(array(
			'bn.aktiv' => true, //check if necessary
			'zgvdatum' => null,
			'typ' => 'b',
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
