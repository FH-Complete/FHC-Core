<?php
/**
 * Copyright (C) 2024 fhcomplete.org
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

if (! defined('BASEPATH')) exit('No direct script access allowed');


/**
 * This controller operates between (interface) the JS (FAS) and the AntragLib (back-end)
 * This controller works with calls on the HTTP GET or POST and the output is always RDF
 */
class Wiederholung extends Auth_Controller
{

	/**
	 * Calls the parent's constructor and loads the FilterCmptLib
	 */
	public function __construct()
	{
		parent::__construct([
			'getLvs' => ['student/studierendenantrag:r', 'student/noten:r'],
			'moveLvsToZeugnis' => ['student/studierendenantrag:w', 'student/noten:w']
		]);

		// Libraries
		$this->load->library('AntragLib');

		// Load language phrases
		$this->loadPhrases([
			'global',
			'studierendenantrag'
		]);
	}

	//------------------------------------------------------------------------------------------------------------------
	// Public methods

	public function getLvs($prestudent_id)
	{
		// header für no cache
		$this->output->set_header("Cache-Control: no-cache");
		$this->output->set_header("Cache-Control: post-check=0, pre-check=0", false);
		$this->output->set_header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
		$this->output->set_header("Pragma: no-cache");
		$this->output->set_header("Content-type: application/xhtml+xml");

		$this->load->library('VariableLib', ['uid' => getAuthUID()]);
		$sem_akt = $this->variablelib->getVar('semester_aktuell');


		$result = $this->antraglib->getLvsForPrestudent($prestudent_id, $sem_akt);
		$lvs = $this->getDataOrTerminateWithError($result) ?: [];

		$rdf_url = 'http://www.technikum-wien.at/antragnote';

		$this->load->view('lehre/Antrag/Wiederholung/getLvs.rdf.php', [
			'url' => $rdf_url,
			'lvs' => $lvs
		]);
	}

	public function moveLvsToZeugnis()
	{
		$anzahl = $this->input->post('anzahl');
		$student_uid = $this->input->post('student_uid');
		$this->load->model('education/Studierendenantraglehrveranstaltung_model', 'StudierendenantraglehrveranstaltungModel');
		$this->load->model('education/Zeugnisnote_model', 'ZeugnisnoteModel');

		$errormsg = array();

		for($i=0; $i<$anzahl; $i++)
		{
			$id = $this->input->post('studierendenantrag_lehrveranstaltung_id_' . $i);
			$result =$this->StudierendenantraglehrveranstaltungModel->load($id);
			if(isError($result))
			{
				$errormsg[] = getError($result);
			}
			elseif(!hasData($result))
			{
				$errormsg[] = $this->p->t('studierendenantrag', 'error_no_lv_in_application');
			}
			else
			{
				$antragLv = getData($result)[0];
				$result= $this->ZeugnisnoteModel->load([
					'lehrveranstaltung_id'=> $antragLv->lehrveranstaltung_id,
					'student_uid'=> $student_uid,
					'studiensemester_kurzbz' => $antragLv->studiensemester_kurzbz
				]);
				if(isError($result))
				{
					$errormsg[] = getError($result);
				}
				else
				{
					if (hasData($result))
					{
						$result = $this->ZeugnisnoteModel->update(
							[
								'lehrveranstaltung_id'=> $antragLv->lehrveranstaltung_id,
								'student_uid'=> $student_uid,
								'studiensemester_kurzbz' => $antragLv->studiensemester_kurzbz
							],
							[
								'note'=> $antragLv->note,
								'uebernahmedatum' => date('c'),
								'benotungsdatum' => $antragLv->insertamum,
								'updateamum' => date('c'),
								'bemerkung'=>$antragLv->anmerkung,
								'updatevon'=>getAuthUID()
							]
						);
					}
					else
					{
						$result = $this->ZeugnisnoteModel->insert([
							'lehrveranstaltung_id'=> $antragLv->lehrveranstaltung_id,
							'student_uid'=> $student_uid,
							'studiensemester_kurzbz' => $antragLv->studiensemester_kurzbz,
							'note'=> $antragLv->note,
							'uebernahmedatum' => date('c'),
							'benotungsdatum' => $antragLv->insertamum,
							'insertamum' => date('c'),
							'bemerkung'=>$antragLv->anmerkung,
							'insertvon'=>getAuthUID()
						]);
					}
					if(isError($result))
					{
						$errormsg[] = getError($result);
					}
				}
			}
		}

		if($errormsg)
			$return = false;
		else
			$return = true;

		$this->load->view('lehre/Antrag/Wiederholung/moveLvs.rdf.php', [
			'return' => $return,
			'errormsg' => $errormsg
		]);
	}
}
