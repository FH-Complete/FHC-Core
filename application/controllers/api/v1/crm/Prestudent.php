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

class Prestudent extends API_Controller
{
	/**
	 * Prestudent API constructor.
	 */
	public function __construct()
	{
		parent::__construct(
			array(
				'Prestudent' => 'basis/prestudent:rw',
				'PrestudentByPersonID' => 'basis/prestudent:r',
				'Specialization' => 'basis/prestudent:rw',
				'LastStatuses' => 'basis/prestudent:r',
				'PrestudentsPerStatus' => 'basis/prestudent:r',
				'RmSpecialization' => 'basis/prestudent:w',
				'AddReihungstest' => 'basis/prestudent:w',
				'DelReihungstest' => 'basis/prestudent:w'
			)
		);
		// Load model PrestudentModel
		$this->load->model('crm/prestudent_model', 'PrestudentModel');
		// Load library ReihungstestLib
		$this->load->library('ReihungstestLib');
	}

	/**
	 * @return void
	 */
	public function getPrestudent()
	{
		$prestudentID = $this->get('prestudent_id');

		if (isset($prestudentID))
		{
			$result = $this->PrestudentModel->load($prestudentID);

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
	public function getPrestudentByPersonID()
	{
		$person_id = $this->get('person_id');

		if (isset($person_id))
		{
			$result = $this->PrestudentModel->load(array('person_id' => $person_id));

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
	public function getSpecialization()
	{
		$prestudent_id = $this->get('prestudent_id');
		$titel = $this->get('titel');

		if (isset($prestudent_id) && isset($titel))
		{
			// Loads model Notiz_model
			$this->load->model('person/Notiz_model', 'NotizModel');

			$result = $this->NotizModel->getSpecialization($prestudent_id, $titel);

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
	public function getLastStatuses()
	{
		$person_id = $this->get('person_id');
		$studiensemester_kurzbz = $this->get('studiensemester_kurzbz');
		$studiengang_kz = $this->get('studiengang_kz');
		$status_kurzbz = $this->get('status_kurzbz');

		if (isset($person_id))
		{
			$result = $this->PrestudentModel->getLastStatuses(
				$person_id,
				$studiensemester_kurzbz,
				$studiengang_kz,
				$status_kurzbz
			);

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	/**
	 * Get all Persons with a Status in the define Timerange
	 * Additionally ALL Prestudents of this person are included.
	 * (Not only the ones with the status)
	 *
	 * @return void
	 */
	public function getPrestudentsPerStatus()
	{
		$this->load->model('person/person_model', 'PersonModel');
		$status_kurzbz = $this->get('status_kurzbz');
		$von = $this->get('von');
		$bis = $this->get('bis');

		if (isset($status_kurzbz) && isset($von) && isset($bis))
		{
			$result = $this->PersonModel->getPersonFromStatus(
				$status_kurzbz,
				$von,
				$bis
			);

			// Remove person images from result array to reduce useless traffic
			foreach($result->retval as $key=>$val)
			{
				unset($result->retval[$key]->foto);
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
	public function postRmSpecialization()
	{
		$notiz_id = $this->post()['notiz_id'];

		if (isset($notiz_id))
		{
			// Loads model Notiz_model
			$this->load->model('person/Notiz_model', 'NotizModel');

			$result = $this->NotizModel->rmSpecialization($notiz_id);

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
	public function postSpecialization()
	{
		$prestudent_id = $this->post()['prestudent_id'];
		$titel = $this->post()['titel'];
		$text = $this->post()['text'];

		if (isset($prestudent_id) && isset($titel) && isset($text))
		{
			// Loads model Notiz_model
			$this->load->model('person/Notiz_model', 'NotizModel');

			$result = $this->NotizModel->addSpecialization($prestudent_id, $titel, $text);

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
	public function postPrestudent()
	{
		$prestudent = $this->post();

		if ($this->_validate($this->post()))
		{
			if (isset($prestudent['prestudent_id']))
			{
				$result = $this->PrestudentModel->update($prestudent['prestudent_id'], $prestudent);
			}
			else
			{
				$result = $this->PrestudentModel->insert($prestudent);
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
	public function deletePrestudent()
	{
		if ($this->_validate($this->delete()))
		{
			$result = $this->PrestudentModel->delete($this->delete()['prestudent_id']);

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
	public function postAddReihungstest()
	{
		$ddReihungstest = $this->post();

		if ($this->_validateReihungstest($ddReihungstest))
		{
			if(isset($ddReihungstest['new']) && $ddReihungstest['new'] == true)
			{
				// Remove new parameter to avoid DB insert errors
				unset($ddReihungstest['new']);

				$result = $this->reihungstestlib->insertPersonReihungstest($ddReihungstest);
			}
			else
			{
				$result = $this->reihungstestlib->updatePersonReihungstest($ddReihungstest);
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
	public function postDelReihungstest()
	{
		$ddReihungstest = $this->post();

		if (isset($ddReihungstest['rt_person_id']))
		{
			$result = $this->reihungstestlib->deletePersonReihungstest($ddReihungstest);

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($prestudent = NULL)
	{
		return true;
	}

	private function _validateReihungstest($ddReihungstest = NULL)
	{
		if (!isset($ddReihungstest['person_id']) || !isset($ddReihungstest['rt_id']) || !isset($ddReihungstest['studienplan_id']))
		{
			return false;
		}

		return true;
	}
}
