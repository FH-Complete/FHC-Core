<?php

if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Scheduler for generating ESI (European Student Identifier)
 */
class ESIScheduler extends JQW_Controller
{
	const JOB_TYPE_GENERATE_ESI = 'generateESI';
	const KENNZEICHENTYP_KURZBZ = 'esi';

	private $_active_status_kurzbz = array('Student', 'Diplomand');

	/**
	 * Controller initialization
	 */
	public function __construct()
	{
		parent::__construct();

		$this->load->model('organisation/Studiensemester_model', 'StudiensemesterModel');
	}

	//------------------------------------------------------------------------------------------------------------------
	// Public methods

	/**
	 * Creates jobs queue entries for generateESI job.
	 * @param string $studiensemester_kurzbz semester for which ESIs should be generated
	 */
	public function generateESI($studiensemester_kurzbz = null)
	{
		// if no semester given, get current studiensemester
		if (!isset($studiensemester_kurzbz))
		{
			$semRes = $this->StudiensemesterModel->getAkt();

			if (hasData($semRes))
			{
				$studiensemester_kurzbz = getData($semRes)[0]->studiensemester_kurzbz;
			}
		}

		if (isset($studiensemester_kurzbz))
		{
			$this->logInfo('Start job queue scheduler '.self::JOB_TYPE_GENERATE_ESI);

			$qry = "
				SELECT
					DISTINCT person_id
				FROM
					public.tbl_person pers
					JOIN public.tbl_prestudent ps USING (person_id)
					JOIN public.tbl_prestudentstatus pss USING (prestudent_id)
				WHERE
					pss.studiensemester_kurzbz = ?
					AND pers.matr_nr IS NOT NULL
					AND pss.status_kurzbz IN ?
					AND NOT EXISTS ( -- has no ESI yet
						SELECT 1
						FROM
							public.tbl_kennzeichen
						WHERE
							person_id = pers.person_id
							AND kennzeichentyp_kurzbz = ?
							AND aktiv
					)
					AND NOT EXISTS ( -- making sure it's not an incoming
						SELECT 1
						FROM
							public.tbl_prestudentstatus
						WHERE
							prestudent_id = ps.prestudent_id
							AND status_kurzbz = 'Incoming'
					)";

			$db = new DB_Model();
			$jobInputResult = $db->execReadOnlyQuery($qry, array($studiensemester_kurzbz, $this->_active_status_kurzbz, self::KENNZEICHENTYP_KURZBZ));

			// If an error occured then log it
			if (isError($jobInputResult))
			{
				$this->logError(getError($jobInputResult));
			}
			elseif (hasData($jobInputResult)) // if persons found
			{
				// Add the new job to the jobs queue
				$addNewJobResult = $this->addNewJobsToQueue(
					self::JOB_TYPE_GENERATE_ESI, // job type
					$this->generateJobs( // gnerate the structure of the new job
						JobsQueueLib::STATUS_NEW,
						json_encode(getData($jobInputResult))
					)
				);

				// If error occurred return it
				if (isError($addNewJobResult)) $this->logError(getError($addNewJobResult));
			}
		}
		else
		{
			$this->logError('Error when getting Studiensemester');
		}

		$this->logInfo('End job queue scheduler '.self::JOB_TYPE_GENERATE_ESI);
	}
}
