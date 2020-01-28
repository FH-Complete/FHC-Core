<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

class PrestudentMultiAssign extends Auth_Controller
{
	public function __construct()
    {
        parent::__construct(
			array(
				'index' => 'basis/student:r',
				'linkToStufe' => 'basis/student:rw',
				'linkToAufnahmegruppe' => 'basis/student:rw'
			)
		);

		// Loads the widget library
		$this->load->library('WidgetLib');
    }

    public function index()
	{
		$studiengang = $this->input->post('studiengang');
		$studiensemester = $this->input->post('studiensemester');
		$aufnahmegruppe = $this->input->post('aufnahmegruppe');
		$reihungstest = $this->input->post('reihungstest');
		$stufe = $this->input->post('stufe');

		// Converts string 'null' to a null value
		$stufe = ($stufe == 'null' ? null : $stufe);
		$studiengang = ($studiengang == 'null' ? null : $studiengang);
		$reihungstest = ($reihungstest == 'null' ? null : $reihungstest);
		$aufnahmegruppe = ($aufnahmegruppe == 'null' ? null : $aufnahmegruppe);
		$studiensemester = ($studiensemester == 'null' ? null : $studiensemester);

		$returnUsers = null;
		if ($studiengang != null || $studiensemester != null || $aufnahmegruppe!= null
			|| $reihungstest != null || $stufe != null)
		{
			$returnUsers = $this->_getPrestudents($studiengang, $studiensemester, $aufnahmegruppe, $reihungstest, $stufe);
		}

		$users = null;
		if (hasData($returnUsers))
		{
			$users = $returnUsers->retval;
		}

		if ($returnUsers == null || isSuccess($returnUsers))
		{
			$viewData = array(
				'studiengang' => $studiengang,
				'studiensemester' => $studiensemester,
				'aufnahmegruppe' => $aufnahmegruppe,
				'reihungstest' => $reihungstest,
				'stufe' => $stufe,
				'users' => $users
			);

			$this->load->view('system/aufnahme/prestudentMultiAssign', $viewData);
		}
		else if (isError($returnUsers))
		{
			show_error(getError($returnUsers));
		}
	}

	/**
	 * To assign a stufe to one or more prestudents
	 */
	public function linkToStufe()
	{
		$prestudentIdArray = $this->input->post('prestudent_id');
		$stufe = $this->input->post('stufe');

		// Converts string 'null' to a null value
		$stufe = ($stufe == 'null' ? null : $stufe);

		// Load model PrestudentstatusModel
        $this->load->model('crm/Prestudentstatus_model', 'PrestudentstatusModel');

        // Set the HTTP header
        $this->output->set_header('Content-Type: application/json; charset=utf-8');

        $result = error("No valid parameters");
        if (isset($stufe)
			&& isset($prestudentIdArray)
			&& is_array($prestudentIdArray)
			&& count($prestudentIdArray) > 0)
        {
			$result = $this->PrestudentstatusModel->updateStufe($prestudentIdArray, $stufe);

			if (isSuccess($result))
			{
				echo '{"msg": "Data correctly saved"}';
			}
			else
			{
				echo '{"msg": "Error occurred while saving data, please contact the administrator"}';
			}
		}
		else
		{
			echo '{"msg": "'.$result->retval.'"}';
		}
	}

	/**
	 * To assign one or more prestudents to a gruppe
	 */
	public function linkToAufnahmegruppe()
	{
		$prestudentIdArray = $this->input->post('prestudent_id');
		$aufnahmegruppe = $this->input->post('aufnahmegruppe');

		// Converts string 'null' to a null value
		$aufnahmegruppe = ($aufnahmegruppe == 'null' ? null : $aufnahmegruppe);

		// Load model PrestudentstatusModel
        $this->load->model('crm/Prestudent_model', 'PrestudentModel');

        // Set the HTTP header
        $this->output->set_header('Content-Type: application/json; charset=utf-8');

        $result = error("No valid parameters");
        if (isset($aufnahmegruppe)
			&& isset($prestudentIdArray)
			&& is_array($prestudentIdArray)
			&& count($prestudentIdArray) > 0)
        {
			$result = $this->PrestudentModel->updateAufnahmegruppe($prestudentIdArray, $aufnahmegruppe);

			if (isSuccess($result))
			{
				echo '{"msg": "Data correctly saved"}';
			}
			else
			{
				echo '{"msg": "Error occurred while saving data, please contact the administrator"}';
			}
		}
		else
		{
			echo '{"msg": "'.$result->retval.'"}';
		}
	}

	/**
	 * Get the prestudents using search parameters
	 */
	private function _getPrestudents($studiengang, $studiensemester, $aufnahmegruppe, $reihungstest, $stufe)
	{
		// Load model prestudentm_model
        $this->load->model('crm/Prestudent_model', 'PrestudentModel');

        if ($studiengang == '' || isEmptyString($studiengang))
		{
			$studiengang = null;
		}

		if ($studiensemester == '' || isEmptyString($studiensemester))
		{
			$studiensemester = null;
		}

		if ($aufnahmegruppe == '' || isEmptyString($aufnahmegruppe))
		{
			$aufnahmegruppe = null;
		}

		if ($reihungstest == '' || isEmptyString($reihungstest))
		{
			$reihungstest = null;
		}

		if ($stufe == '' || isEmptyString($stufe))
		{
			$stufe = null;
		}

        return $this->PrestudentModel->getPrestudentMultiAssign(
			$studiengang,
			$studiensemester,
			$aufnahmegruppe,
			$reihungstest,
			$stufe
		);
	}
}
