<?php

if (!defined('BASEPATH')) exit('No direct script access allowed');

class MeldezettelJob extends JOB_Controller
{
	const INSERT_VON = 'meldezetteljob';
	const DOKUMENT_KURZBZ = 'Meldezet';

	private $_ci; // Code igniter instance

	public function __construct()
	{
		parent::__construct();

		$this->_ci =& get_instance();

		$this->_ci->load->model('crm/Dokumentprestudent_model', 'DokumentprestudentModel');
	}

	/**
	 * Sets Meldezettel to "accepted" for all students with Meldeadresse.
	 */
	public function acceptMeldezettel()
	{
		$this->logInfo('Start Meldezettel Job');

		$params = array(self::DOKUMENT_KURZBZ);

		$qry = "
			-- get all prestudents with meldeadresse, but no accepted Meldezettel
			SELECT
				DISTINCT prestudent_id
			FROM
				public.tbl_adresse
				JOIN public.tbl_person USING (person_id)
				JOIN public.tbl_prestudent ps USING (person_id)
			WHERE
				typ = 'm'
				AND NOT EXISTS (
					SELECT
						1
					FROM
						public.tbl_dokumentprestudent
					WHERE
						prestudent_id = ps.prestudent_id
						AND dokument_kurzbz = ?
				)";

		// get all prestudents with Meldeadresse and no accpeted Meldezettel
		$result = $this->_ci->DokumentprestudentModel->execReadOnlyQuery($qry, $params);

		if (isError($result))
		{
			$this->logError(getError($result));
		}

		$count = 0;

		if (hasData($result))
		{
			$prestudents = getData($result);

			foreach ($prestudents as $prestudent)
			{
				// set Meldezettel to accepted
				$result = $this->_ci->DokumentprestudentModel->insert(
					array(
						'prestudent_id' => $prestudent->prestudent_id,
						'dokument_kurzbz' => self::DOKUMENT_KURZBZ,
						'datum' => date('Y-m-d'),
						'insertamum' => strftime('%Y-%m-%d %H:%M'),
						'insertvon' => self::INSERT_VON
					)
				);

				if (isError($result))
					$this->logError(getError($result));
				else
					$count++;
			}
		}

		$this->logInfo('End Meldezettel Job', array('Number of changes ' => $count));
	}
}
