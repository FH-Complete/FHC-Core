<?php

if (!defined("BASEPATH")) exit("No direct script access allowed");

/**
 * This job takes care of sending messages to a pool of users,
 * should not be a scheduled job, or maybe just for a short time,
 * but it is called manually every time it is needed.
 * Each method takes care to send a different message to a different pool of users,
 * so they are very specialize, diffucult to be reused.
 */
class OneTimeMessages extends JOB_Controller
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();

		// Loads CLMessagesModel
		$this->load->model('CL/Messages_model', 'CLMessagesModel');
	}

	/**
	 * Sends the same message to all the applicants whith:
	 * - Status set as "Wartender"
	 * - The given study course type (b = bachelor, m = master)
	 * - The given semester (ex WS2020)
	 * - How long since applicant (days)
	 * - The given template id to be used as message subject and body (vorlage_kurzbz)
	 * The sender of all the messages is specified by the parameter senderId (sender person_id)
	 */
	public function sendMessageToApplicantsStillWaiting($senderId, $studyCourseType, $semester, $days, $messageTemplate)
	{
		$this->logInfo('Send message to applicants still waiting start');

		$queryParams = array(
			$semester,
			$studyCourseType,
			$semester,
			$studyCourseType,
			$semester,
			$studyCourseType
		);

		$dbModel = new DB_Model();

		$dbPrestudents = $dbModel->execReadOnlyQuery(
			'SELECT distinct on(person_id) p.prestudent_id
			   FROM public.tbl_prestudent p
			   JOIN public.tbl_prestudentstatus ps USING (prestudent_id)
 			   JOIN public.tbl_studiengang s USING (studiengang_kz)
			  WHERE get_rolle_prestudent(ps.prestudent_id, NULL) = \'Wartender\'
			    AND ps.studiensemester_kurzbz = ?
			    AND ps.datum <= NOW() - \''.$days.' days\'::interval
			    AND s.typ = ?
			    AND NOT EXISTS (
				SELECT pp.person_id
				  FROM public.tbl_prestudent pp
				  JOIN public.tbl_prestudentstatus pss USING (prestudent_id)
				  JOIN public.tbl_studiengang ss USING (studiengang_kz)
				 WHERE pss.status_kurzbz = \'Aufgenommener\'
				   AND pss.studiensemester_kurzbz = ?
				   AND ss.typ = ?
				   AND pp.person_id = p.person_id
			    )
			    AND NOT EXISTS (
				SELECT pp.person_id
				  FROM public.tbl_prestudent pp
				  JOIN public.tbl_prestudentstatus pss USING (prestudent_id)
				  JOIN public.tbl_studiengang ss USING (studiengang_kz)
				 WHERE pss.status_kurzbz = \'Student\'
				   AND pss.studiensemester_kurzbz = ?
				   AND ss.typ = ?
				   AND pp.person_id = p.person_id
			    )',
			$queryParams
		);

		if (isError($dbPrestudents))
		{
			$this->logError(getError($dbPrestudents), $queryParams);
		}
		elseif (!hasData($dbPrestudents))
		{
			$this->logInfo('There were no users to send message');
		}
		else
		{
			$prestudentIdsArray = array();

			foreach (getData($dbPrestudents) as $dbPrestudent)
			{
				$prestudentIdsArray[] = $dbPrestudent->prestudent_id;
			}

			$sendMessage = $this->CLMessagesModel->sendExplicitTemplateSenderId(
				$senderId,		// sender person id
				$prestudentIdsArray,	// prestudents id
				null,			// organization unit
				$messageTemplate,	// template id
				null			// extra variables
			);

			if (isError($sendMessage))
			{
				$this->logError(
					getError($sendMessage),
					array(
						'prestudents' => $prestudentIdsArray,
						'template' => $messageTemplate
					)
				);
			}

			$this->logInfo('Total amount of prestudents: '.count($prestudentIdsArray));
		}

		$this->logInfo('Send message to applicants still waiting end');
	}
}
