<?php

if (! defined('BASEPATH'))
	exit('No direct script access allowed');

/**
 * Database Class
 *
 */
class Prestudentstatus extends CLI_Controller
{
	/**
	 * Initialize Prestudentstatus Class
	 *
	 * @return	void
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Check all Status entries if the selected studienplan is valid for this degree programm / semester
	 * if the Studienplan is not valid it searches for a valid studienplan an corrects the data if there is
	 * an unambiguouse studienplan corresponding to this status
	 */
	public function correctStudienplan()
	{
		$this->load->model('organisation/Studienplan_model', 'StudienplanModel');

		$this->load->model('crm/Prestudentstatus_model', 'PrestudentstatusModel');

		$this->PrestudentstatusModel->addSelect('tbl_prestudentstatus.prestudent_id,
			tbl_prestudentstatus.studiensemester_kurzbz,
			tbl_prestudentstatus.ausbildungssemester,
			tbl_prestudentstatus.status_kurzbz,
			tbl_prestudent.studiengang_kz,
			tbl_prestudentstatus.studienplan_id,
			tbl_studienplan.orgform_kurzbz,
			tbl_prestudent.person_id,
			tbl_studienplan.sprache');
		$this->PrestudentstatusModel->addJoin('public.tbl_prestudent', 'prestudent_id');
		$this->PrestudentstatusModel->addJoin('lehre.tbl_studienplan', 'studienplan_id','LEFT');
		$this->PrestudentstatusModel->addJoin('lehre.tbl_studienordnung', 'studienordnung_id','LEFT');

		$status = $this->PrestudentstatusModel->loadWhere("
			NOT EXISTS (
				SELECT 1 FROM lehre.tbl_studienplan_semester
				WHERE studienplan_id=tbl_prestudentstatus.studienplan_id
				AND studiensemester_kurzbz=tbl_prestudentstatus.studiensemester_kurzbz
				AND semester=tbl_prestudentstatus.ausbildungssemester)
			AND tbl_prestudentstatus.status_kurzbz NOT IN ('Abbrecher','Diplomand','Absolvent')");

		$sum_overall = 0;
		$sum_corrected = 0;
		$sum_notcorrected = 0;

		if(isSuccess($status))
		{
			if(hasData($status))
			{
				foreach($status->retval as $row_status)
				{
					$studienplan = $this->StudienplanModel->getStudienplaeneBySemester(
							$row_status->studiengang_kz,
							$row_status->studiensemester_kurzbz,
							$row_status->ausbildungssemester,
							$row_status->orgform_kurzbz,
							$row_status->sprache);

					if(isSuccess($studienplan) && count($studienplan->retval) == 1)
					{
						$this->PrestudentstatusModel->resetQuery();
						$pk_arr = array('ausbildungssemester' => $row_status->ausbildungssemester,
							'studiensemester_kurzbz' => $row_status->studiensemester_kurzbz,
							'status_kurzbz' => $row_status->status_kurzbz,
							'prestudent_id' => $row_status->prestudent_id);

						$status = $this->PrestudentstatusModel->load($pk_arr);

						if(isSuccess($status))
						{
							$this->PrestudentstatusModel->update($pk_arr,
								array('studienplan_id' => $studienplan->retval[0]->studienplan_id));
							$sum_corrected++;

							if($row_status->status_kurzbz == 'Interessent')
							{
								$this->correctReihungstest(
									$row_status->person_id,
									$row_status->studienplan_id,
									$studienplan->retval[0]->studienplan_id);

								$this->correctReihungstestStudienplan(
									$row_status->studiensemester_kurzbz,
									$row_status->studienplan_id,
									$studienplan->retval[0]->studienplan_id);
							}
						}
					}
					else
					{
						$sum_notcorrected++;
					}

					$sum_overall++;
				}
			}
		}
		else
		{
			show_error(getError($status));
		}
		echo "Corrected:".$sum_corrected."\n";
		echo "Not Corrected:".$sum_notcorrected."\n";
		echo "Overall incorrect:".$sum_overall."\n";
	}

	/**
	 * Corrects the Assignment to a Placement Test
	 * Corrects the Studyplan and adds the Studyplan to the Placement test
	 * @param $person_id ID of the Person
	 * @param $studienplan_id_old ID of the old Studyplan
	 * @param $studienplan_id ID of the new Studyplan
	 */
	private function correctReihungstest($person_id, $studienplan_id_old, $studienplan_id)
	{
		$this->load->model('crm/RtPerson_model', 'RtPersonModel');
		$this->load->model('crm/RtStudienplan_model', 'RtStudienplanModel');

		$this->RtPersonModel->resetQuery();
		// Correct also Assignments to Placement test
		$this->RtPersonModel->addJoin(
			'public.tbl_reihungstest',
			'tbl_reihungstest.reihungstest_id = tbl_rt_person.rt_id'
		);

		$rt = $this->RtPersonModel->loadWhere(array(
			"person_id" => $person_id,
			"studienplan_id" => $studienplan_id_old
			));

		//	"tbl_reihungstest.datum > " => date('Y-m-d H:i:s')

		if(hasData($rt))
		{
			foreach($rt->retval as $row_rt)
			{
				// Update RTPerson Record
				$this->RtPersonModel->update($row_rt->rt_person_id, array(
					'studienplan_id' => $studienplan_id,
					'updateamum' => date('Y-m-d H:i:s'),
					'updatevon' => 'cron'
				));

				// Add new Studyplan to RtStudienplan if missing
				$rt_studienplan = $this->RtStudienplanModel->loadWhere(array(
					"reihungstest_id" => $row_rt->reihungstest_id,
					"studienplan_id" => $studienplan_id
				));

				if(!hasData($rt_studienplan))
				{
					$this->RtStudienplanModel->insert(array(
						"reihungstest_id" => $row_rt->reihungstest_id,
						"studienplan_id" => $studienplan_id
					));
				}
			}
		}
	}

	/**
	 * When a degree Programm gets a new Studyplan the Placementtests are updated and the
	 * new studyplan is added
	 * @param $studiensemester Studiensemester_kurzbz.
	 * @param $studienplan_id_old Id of the old studyplan
	 * @param $studienplan_id id of the new studyplan
	 */
	private function correctReihungstestStudienplan($studiensemester, $studienplan_id_old, $studienplan_id)
	{
		$this->load->model('crm/RtStudienplan_model', 'RtStudienplanModel');

		$this->RtStudienplanModel->resetQuery();
		// Correct also Assignments to Placement test
		$this->RtStudienplanModel->addJoin(
			'public.tbl_reihungstest',
			'tbl_reihungstest.reihungstest_id = tbl_rt_studienplan.reihungstest_id'
		);

		$rt = $this->RtStudienplanModel->loadWhere(array(
			"studienplan_id" => $studienplan_id_old,
			"tbl_reihungstest.studiensemester_kurzbz" => $studiensemester
			));

		if(hasData($rt))
		{
			foreach($rt->retval as $row_rt)
			{
				// Add new Studyplan to RtStudienplan if missing
				$rt_studienplan = $this->RtStudienplanModel->loadWhere(array(
					"reihungstest_id" => $row_rt->reihungstest_id,
					"studienplan_id" => $studienplan_id
				));

				if(!hasData($rt_studienplan))
				{
					echo "Adding StudienplanId: $studienplan_id to ReihungstestId: $row_rt->reihungstest_id";
					$this->RtStudienplanModel->insert(array(
						"reihungstest_id" => $row_rt->reihungstest_id,
						"studienplan_id" => $studienplan_id
					));
				}
			}
		}
	}
}
