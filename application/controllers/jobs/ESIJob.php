<?php

if (!defined('BASEPATH')) exit('No direct script access allowed');

require_once('schedulers/ESIScheduler.php');

/**
 * Controller for initialising generateESI job
 */
class ESIJob extends JQW_Controller
{
	const ESI_PREFIX = 'urn:schac:personalUniqueCode:int:esi:at:';
	const INSERT_VON = 'generateEsiJob';

	/**
	 * Controller initialization
	 */
	public function __construct()
	{
		parent::__construct();

		// load models
		$this->load->model('person/Person_model', 'PersonModel');
		$this->load->model('person/Kennzeichen_model', 'KennzeichenModel');
	}

	//------------------------------------------------------------------------------------------------------------------
	// Public methods

	/**
	 * Initialises generateESI job, handles job queue, logs infos/errors
	 */
	public function generateESI()
	{
		//$jobType = 'DVUHSendPruefungsaktivitaeten';
		$this->logInfo(ESIScheduler::JOB_TYPE_GENERATE_ESI.' job start');

		// Gets the latest jobs
		$lastJobs = $this->getLastJobs(ESIScheduler::JOB_TYPE_GENERATE_ESI);

		if (isError($lastJobs))
		{
			$this->logError(getCode($lastJobs).': '.getError($lastJobs), ESIScheduler::JOB_TYPE_GENERATE_ESI);
		}
		else
		{
			$this->updateJobs(
				getData($lastJobs), // Jobs to be updated
				array(JobsQueueLib::PROPERTY_START_TIME), // Job properties to be updated
				array(date('Y-m-d H:i:s')) // Job properties new values
			);

			$person_arr = $this->_getInputObjArray(getData($lastJobs));

			foreach ($person_arr as $persobj)
			{
				if (!isset($persobj->person_id))
					$this->logError("Error when generating ESI: invalid parameters");
				else
				{
					$person_id = $persobj->person_id;

					// check if there already is an active ESI
					$this->KennzeichenModel->addSelect('1');
					$activeKennzeichenRes = $this->KennzeichenModel->loadWhere(
						array('person_id' => $person_id, 'kennzeichentyp_kurzbz' => ESIScheduler::KENNZEICHENTYP_KURZBZ, 'aktiv' => true)
					);

					if (hasData($activeKennzeichenRes))
					{
						$this->logError("Active ESI for person Id $person_id already exists");
						continue;
					}

					// get Matrikelnr for person for which ESI should be generated
					$this->PersonModel->addSelect('matr_nr');
					$personRes = $this->PersonModel->load($person_id);

					if (!hasData($personRes))
					{
						$this->logError("Person with Id $person_id not found");
						continue;
					}

					$matr_nr = getData($personRes)[0]->matr_nr;

					if (isEmptyString($matr_nr))
					{
						$this->logError("Matrikelnummer for person with Id $person_id is empty");
						continue;
					}

					$esi = self::ESI_PREFIX.$matr_nr;

					// check if ESI was already used
					$this->KennzeichenModel->addSelect('1');
					$existingKennzeichenRes = $this->KennzeichenModel->loadWhere(
						array('person_id' => $person_id, 'kennzeichentyp_kurzbz' => ESIScheduler::KENNZEICHENTYP_KURZBZ, 'inhalt' => $esi)
					);

					if (hasData($existingKennzeichenRes))
					{
						$this->logError("ESI $esi for person Id $person_id already exists");
						continue;
					}

					// if everything ok, save the esi for the person
					$saveEsiResult = $this->KennzeichenModel->insert(
						array(
							'person_id' => $person_id,
							'kennzeichentyp_kurzbz' => ESIScheduler::KENNZEICHENTYP_KURZBZ,
							'inhalt' => $esi,
							'aktiv' => true,
							'insertvon' => self::INSERT_VON
						)
					);

					if (isError($saveEsiResult))
					{
						$this->logError("Error when sending ESI, person Id $person_id ".getError($saveEsiResult));
					}
				}
			}

			// Update jobs properties values
			$this->updateJobs(
				getData($lastJobs), // Jobs to be updated
				array(JobsQueueLib::PROPERTY_STATUS, JobsQueueLib::PROPERTY_END_TIME), // Job properties to be updated
				array(JobsQueueLib::STATUS_DONE, date('Y-m-d H:i:s')) // Job properties new values
			);

			if (hasData($lastJobs)) $this->updateJobsQueue(ESIScheduler::JOB_TYPE_GENERATE_ESI, getData($lastJobs));
		}

		$this->logInfo(ESIScheduler::JOB_TYPE_GENERATE_ESI.' job stop');
	}

	// --------------------------------------------------------------------------------------------
	// Private methods

	/**
	 * Extracts input data from jobs.
	 * @param $jobs
	 * @return array with jobinput
	 */
	private function _getInputObjArray($jobs)
	{
		$mergedUsersArray = array();

		if (count($jobs) == 0) return $mergedUsersArray;

		foreach ($jobs as $job)
		{
			$decodedInput = json_decode($job->input);
			if ($decodedInput != null)
			{
				foreach ($decodedInput as $el)
				{
					$mergedUsersArray[] = $el;
				}
			}
		}
		return $mergedUsersArray;
	}
}
