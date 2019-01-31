<?php
/* Copyright (C) 2019 fhcomplete.org
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307, USA.
 *
 * Authors: Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>
 */
if (! defined('BASEPATH'))
	exit('No direct script access allowed');

class LVPlanJob extends FHC_Controller
{
	/**
	 * Initialize LVPlanJob Class
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
		$result .= "php ".$this->config->item('index_page')." jobs/LVPlanJob AddDirectGroups";

		echo $result.PHP_EOL;
	}

	/**
	 * Check all Courses with direkt Groups attached and adds the Groups to the Schedule if missing
	 */
	public function addDirectGroups()
	{
		$studiensemester_arr = array();

		$this->load->model('organisation/Studiensemester_model', 'StudiensemesterModel');
		$this->load->model('ressource/Stundenplandev_model', 'StundenplandevModel');
		$this->load->model('education/Lehreinheit_model', 'LehreinheitModel');
		$this->load->model('education/Lehreinheitgruppe_model', 'LehreinheitgruppeModel');

		// Get actual Studiensemester
		$resultsem = $this->StudiensemesterModel->getAktOrNextSemester();

		if(hasData($resultsem))
		{
			$studiensemester_arr[] = $resultsem->retval[0]->studiensemester_kurzbz;
		}
		else
		{
			echo 'kein Studiensemester gefunden';
			return false;
		}

		// Get nearest Studiensemester to actual
		$resultsem = $this->StudiensemesterModel->getNearestFrom($studiensemester_arr[0]);
		if(hasData($resultsem))
		{
			$studiensemester_arr[] = $resultsem->retval[0]->studiensemester_kurzbz;
		}

		foreach($studiensemester_arr as $studiensemester)
		{
			echo "LVPlanJob/addDirectGroups Studiensemester: ".$studiensemester."\n";
			$succ = 0;
			$fail = 0;

			// get all schedule entries where group is missing
			$result = $this->StundenplandevModel->getMissingDirectGroups($studiensemester);
			if(hasData($result))
			{
				foreach($result->retval as $row)
				{
					$this->LehreinheitModel->addJoin('lehre.tbl_lehrveranstaltung','lehrveranstaltung_id');
					$result_le = $this->LehreinheitModel->loadWhere(array('lehreinheit_id' => $row->lehreinheit_id));

					// load additional data of course
					$unr = null;
					$stg_kz = null;
					$semester = null;
					$gruppe_kurzbz = null;

					if (hasData($result_le))
					{
						$le = $result_le->retval[0];
						$unr = $le->unr;
						$stg_kz = $le->studiengang_kz;
						$semester = $le->semester;
					}
					else
					{
						echo 'Failed to load Lehreinheit '.$row->lehreinheit_id;
						$fail++;
						continue;
					}

					// get direct group if course
					$result_leg = $this->LehreinheitgruppeModel->getDirectGroup($row->lehreinheit_id);
					if (hasData($result_leg))
					{
						$gruppe_kurzbz = $result_leg->retval[0]->gruppe_kurzbz;
					}
					else
					{
						echo 'Failed to load direct group for le '.$row->lehreinheit_id;
						$fail++;
						continue;
					}

					// add group to schedule
					$result = $this->StundenplandevModel->insert(
						array(
							'lehreinheit_id' => $row->lehreinheit_id,
							'unr' => $unr,
							'studiengang_kz' => $stg_kz,
							'semester' => $semester,
							'verband' => '',
							'gruppe' => '',
							'gruppe_kurzbz' => $gruppe_kurzbz,
							'mitarbeiter_uid' => $row->mitarbeiter_uid,
							'ort_kurzbz' => $row->ort_kurzbz,
							'datum' => $row->datum,
							'stunde' => $row->stunde,
							'titel' => null,
							'anmerkung' => null,
							'fix' => false,
							'updateamum' => date('Y-m-d H:i:s'),
							'updatevon' => 'lvplanjob',
							'insertvon' => 'lvplanjob',
							'insertamum' => date('Y-m-d H:i:s')
						)
					);

					if (isSuccess($result))
					{
						$succ++;
					}
					else
					{
						$fail++;
					}
				}
			}
			echo "New Entries ".$succ."\n";
			echo "Failed ".$fail."\n";
		}
	}
}
