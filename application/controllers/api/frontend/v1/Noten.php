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

use CI3_Events as Events;

class Noten extends FHCAPI_Controller
{

	/**
	 * Object initialization
	 */
	public function __construct()
	{
		parent::__construct([
			'getStudentenNoten' => self::PERM_LOGGED, // todo: berechtigung
			'getNoten' => self::PERM_LOGGED,
			'saveStudentenNoten' => self::PERM_LOGGED // todo: berechtigungen!
		]);

		$this->load->library('AuthLib', null, 'AuthLib');
		
		// Loads phrases system
		$this->loadPhrases([
			'global'
		]);

	}

	public function getStudentenNoten() {
		$lv_id = $this->input->get("lv_id",TRUE);
		$sem_kurzbz = $this->input->get("sem_kurzbz",TRUE);

		if (!isset($lv_id) || isEmptyString($lv_id)
			|| !isset($sem_kurzbz) || isEmptyString($sem_kurzbz))
			$this->terminateWithError($this->p->t('global', 'wrongParameters'), 'general');

		// todo: check various other berechtigungen if its mitarbeiter/lektor/zugeteilterLektor?

		$this->load->model('education/Pruefung_model', 'PruefungModel');
		$this->load->model('education/Lehrveranstaltung_model', 'LehrveranstaltungModel');

		// get studenten for lva & sem with zeugnisnote if available
		$studenten = $this->LehrveranstaltungModel->getStudentsByLv($sem_kurzbz, $lv_id);
		$studentenData = $this->getDataOrTerminateWithError($studenten);
		
		$func = function ($value) {
			return $value->uid;
		};

		$grades = array();
		$student_uids = array_map($func, $studentenData);
		foreach($student_uids as $uid) {
			$grades[$uid]['grades'] = [];
		}
		
		// send $grades reference to moodle addon
		Events::trigger(
			'moodleGrades',
			function & () use (&$grades)
			{
				return $grades;
			},
			[
				'lvid' => $lv_id,
				'stsem' => $sem_kurzbz
			]
		);
		
		// get all prüfungen with noten held in that semester in that lva
		$pruefungen = $this->PruefungModel->getPruefungenByLvStudiensemester($lv_id, $sem_kurzbz);
		$pruefungenData = $this->getDataOrTerminateWithError($pruefungen);

		$this->terminateWithSuccess(array($studentenData, $pruefungenData, DOMAIN, $grades));
	}

	public function getNoten() {
		$this->load->model('education/Note_model', 'NoteModel');

		$result = $this->NoteModel->getAll();
		$noten = $this->getDataOrTerminateWithError($result);
		$this->terminateWithSuccess($noten);
	}
	
	public function saveStudentenNoten() {
		$result = $this->getPostJSON();

		if(!property_exists($result, 'password')) {
			$this->terminateWithError($this->p->t('global', 'missingParameters'), 'general');
		}

		// TODO: send & save noten
		
		$this.$this->terminateWithSuccess($this->AuthLib->checkUserAuthByUsernamePassword(getAuthUID(), $result->password));
	}

}

