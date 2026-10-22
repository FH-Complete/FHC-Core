<?php
/**
 * Description of wiedereinstieg_auto
 *
 * @author ma0068
 */
class CoreUnterbrecherTagLib
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
				'idArray' => []
			);
		}

		$semester = $params['studiensemester_kurzbz'];
		$statusUnterbrecher = Prestudentstatus_model::STATUS_UNTERBRECHER;
		$queryParams = [$statusUnterbrecher, $semester, $statusUnterbrecher];

		$unterbrecher = $this->ci->PrestudentstatusModel->execReadOnlyQuery(
			"SELECT *
			FROM public.tbl_prestudentstatus ps
			JOIN public.tbl_studiensemester USING (studiensemester_kurzbz)
			WHERE status_kurzbz = ?
			AND studiensemester_kurzbz = ?
			  AND (
				  SELECT ps2.status_kurzbz
				  FROM public.tbl_prestudentstatus ps2
				  WHERE ps2.prestudent_id = ps.prestudent_id
					AND ps2.studiensemester_kurzbz = ps.studiensemester_kurzbz
				  ORDER BY ps2.datum DESC, ps2.insertamum DESC, ps2.ext_id DESC
				  LIMIT 1
			  ) = ?;
					", $queryParams);

		if (isError($unterbrecher))
			return $unterbrecher;
		if(hasData($unterbrecher)) {
			$data = $unterbrecher->retval;

			$unterbrecher_data = array_map(function ($item)
			{
				return [
					'id' => $item->prestudent_id,
					'von' => $item->start,
					'bis' => $item->ende
				];
			}, $data);

			return (object)array(
				'data' => $unterbrecher_data,
				'typeId' => 'prestudent_id'
			);
		}
	}

	public function isCriteriaSetFor(array $params)
	{
		if ( !isset($params['id'], $params['studiensemester_kurzbz'], $params['typeId']) ||	$params['typeId'] !== 'prestudent_id')
			return false;

		$semester = $params['studiensemester_kurzbz'];
		$prestudent_id = $params['id'];
		$statusUnterbrecher =  Prestudentstatus_model::STATUS_UNTERBRECHER;
		$queryParams = [$statusUnterbrecher, $semester, $prestudent_id, $statusUnterbrecher];

		$unterbrecher = $this->ci->PrestudentstatusModel->execReadOnlyQuery("
			 SELECT
				ps.prestudent_id,
				ss.start AS von,
				ss.ende AS bis
			FROM public.tbl_prestudentstatus ps
			JOIN public.tbl_studiensemester ss USING (studiensemester_kurzbz)
			WHERE ps.status_kurzbz = ?
			  AND ps.studiensemester_kurzbz = ?
			  AND ps.prestudent_id = ?
			  AND (
				  SELECT ps2.status_kurzbz
				  FROM public.tbl_prestudentstatus ps2
				  WHERE ps2.prestudent_id = ps.prestudent_id
					AND ps2.studiensemester_kurzbz = ps.studiensemester_kurzbz
				  ORDER BY ps2.datum DESC, ps2.insertamum DESC, ps2.ext_id DESC
				  LIMIT 1
			  ) = ?;
		", $queryParams);

		if (isError($unterbrecher))
			return $unterbrecher;
		if(hasData($unterbrecher))
		{
			return $unterbrecher;
		}
		else
			return null;
	}



}
