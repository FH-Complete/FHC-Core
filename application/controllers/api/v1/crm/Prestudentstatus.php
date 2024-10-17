<?php
/**
 * FH-Complete
 *
 * @package		FHC-API
 * @author		FHC-Team
 * @copyright	Copyright (c) 2016, fhcomplete.org
 * @license		GPLv3
 * @link		http://fhcomplete.org
 * @since		Version 1.0
 * @filesource
 */
// ------------------------------------------------------------------------

if (!defined('BASEPATH')) exit('No direct script access allowed');

class Prestudentstatus extends API_Controller
{
	/**
	 * Prestudentstatus API constructor.
	 */
	public function __construct()
	{
		parent::__construct(
			array(
				'Prestudentstatus' => 'basis/prestudentstatus:rw',
				'LastStatus' => 'basis/prestudentstatus:r',
				'StatusByFilter' => 'basis/prestudentstatus:r'
			)
		);
		// Load model PrestudentstatusModel
		$this->load->model('crm/prestudentstatus_model', 'PrestudentstatusModel');
	}

	/**
	 * @return void
	 */
	public function getPrestudentstatus()
	{
		$ausbildungssemester = $this->get('ausbildungssemester');
		$studiensemester_kurzbz = $this->get('studiensemester_kurzbz');
		$status_kurzbz = $this->get('status_kurzbz');
		$prestudent_id = $this->get('prestudent_id');

		if (isset($ausbildungssemester) && isset($studiensemester_kurzbz) && isset($status_kurzbz) && isset($prestudent_id))
		{
			$result = $this->PrestudentstatusModel->load(array($ausbildungssemester, $studiensemester_kurzbz, $status_kurzbz, $prestudent_id));

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	/**
	 * @return void
	 */
	public function getLastStatus()
	{
		$prestudent_id = $this->get("prestudent_id");
		$studiensemester_kurzbz = $this->get("studiensemester_kurzbz");
		$status_kurzbz = $this->get("status_kurzbz");

		if (isset($prestudent_id))
		{
			$result = $this->PrestudentstatusModel->getLastStatus($prestudent_id, $studiensemester_kurzbz, $status_kurzbz);

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	/**
	 * @return void
	 */
	public function postPrestudentstatus()
	{
		$prestudentstatus = $this->post();

		if ($this->_validate($prestudentstatus))
		{
			if(isset($prestudentstatus['new']) && $prestudentstatus['new'] == true)
			{
				// Remove new parameter to avoid DB insert errors
				unset($prestudentstatus['new']);

				$result = $this->PrestudentstatusModel->insert($prestudentstatus);
			}
			else
			{
				$pksArray = array($prestudentstatus['ausbildungssemester'],
									$prestudentstatus['studiensemester_kurzbz'],
									$prestudentstatus['status_kurzbz'],
									$prestudentstatus['prestudent_id']
								);

				$result = $this->PrestudentstatusModel->update($pksArray, $prestudentstatus);
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	/**
	 * @return void
	 */
	public function deletePrestudentstatus()
	{
		$prestudentstatus = $this->delete();

		if ($this->_validate($prestudentstatus))
		{
			$pksArray = array($prestudentstatus['ausbildungssemester'],
								$prestudentstatus['studiensemester_kurzbz'],
								$prestudentstatus['status_kurzbz'],
								$prestudentstatus['prestudent_id']
							);

			$result = $this->PrestudentstatusModel->delete($pksArray);

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($prestudentstatus = null)
	{
		return true;
	}

	/**
	 * Get list of Status entries of a prestudent according to the filter
	 *
	 * @return void
	 */
	public function getStatusByFilter()
	{
		$prestudent_id = $this->get("prestudent_id");
		$status_kurzbz = $this->get("status_kurzbz");
		$ausbildungssemester = $this->get("ausbildungssemester");
		$studiensemester_kurzbz = $this->get("studiensemester_kurzbz");

		if (isset($prestudent_id))
		{
			$result = $this->PrestudentstatusModel->getStatusByFilter($prestudent_id, $status_kurzbz, $ausbildungssemester, $studiensemester_kurzbz);

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

}
