<?php

if (! defined('BASEPATH'))
	exit('No direct script access allowed');

/**
 * Database Class
 *
 */

class Prestudentstatus extends FHC_Controller
{
	/**
	 * Initialize Prestudentstatus Class
	 *
	 * @return	void
	 */
	public function __construct()
	{
		// An empty array as parameter will ensure that this controller is ONLY callable from command line
		parent::__construct(array());

		if ($this->input->is_cli_request())
		{
			$cli = true;
		}
		else
		{
			$this->output->set_status_header(403, 'Jobs must be run from the CLI');
			echo "Jobs must be run from the CLI";
			exit;
		}
	}

	/**
	 * Main function index as help
	 *
	 * @return	void
	 */
	public function index()
	{
		$result = "The following are the available command line interface commands\n\n";
		$result .= "php index.ci.php jobs/Prestudentstatus CorrectStudienplan";

		echo $result.PHP_EOL;
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
			tbl_studienplan.orgform_kurzbz');
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
							$row_status->orgform_kurzbz);

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
			show_error($status->retval);
		}
		echo "Corrected:".$sum_corrected."\n";
		echo "Not Corrected:".$sum_notcorrected."\n";
		echo "Overall incorrect:".$sum_overall."\n";
	}
}
