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
				'idArray' => []
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

		$zgvmissing_data = array_map(function($item) {
			return [
				'id' => $item->prestudent_id,
				'von' => null,
				'bis' => null
			];
		}, $data);

		return (object) array(
			'typeId' => 'prestudent_id',
			'data' => $zgvmissing_data
		);

	}

	public function isCriteriaSetFor(array $params)
	{
		if ( !isset($params['id'], $params['studiensemester_kurzbz'], $params['typeId']) ||	$params['typeId'] !== 'prestudent_id')
			return false;

		$semester = $params['studiensemester_kurzbz'];
		$prestudent_id = $params['id'];

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
			return $result;
		}
		else
			return null;
	}

}
