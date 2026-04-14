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
class ClassScheduleApi extends FHCAPI_Controller
{
	
	/**
	 * Object initialization
	 */
	public function __construct()
	{
		parent::__construct([
			'getAllClassTimeValidityPeriods'=> array('lehre/unterrichtszeiten_gk:r'),
			'getClassTimeValidityPeriod' => array('lehre/unterrichtszeiten_gk:r'),
			'createClassTimeSlotValidityPeriod' => array('lehre/unterrichtszeiten_gk:rw'),
			'updateClassTimeSlotValidityPeriod' => array('lehre/unterrichtszeiten_gk:rw'),
			'deleteClassTimeSlotValidityPeriod' => array('lehre/unterrichtszeiten_gk:rw'),
			'getClassTimeSlotsForValidityPeriod' => array('lehre/unterrichtszeiten_gk:r'),
			'createClassTimeSlotsForValidityPeriod' => array('lehre/unterrichtszeiten_gk:r'),
			'editClassTimeSlotsForValidityPeriod' => array('lehre/unterrichtszeiten_gk:r'),
			'deleteClassTimeSlotsForValidityPeriodPerGroup' => array('lehre/unterrichtszeiten_gk:r'),
			'getAllClassScheduleTypes' => array('lehre/unterrichtszeiten_typ:r'),
			'createClassTimeSlotType' => array('lehre/unterrichtszeiten_typ:rw'),
			'updateClassTimeSlotType' => array('lehre/unterrichtszeiten_typ:rw'),
			'deleteClassTimeSlotType' => array('lehre/unterrichtszeiten_typ:rw'),
		]);

		$this->load->library('form_validation');

		$this->load->model('education/ClassTimeSlotValidityPeriod_model', "ClassTimeSlotValidityPeriodModel");
		$this->load->model('education/ClassTimeSlot_model', "ClassTimeSlotModel");
		$this->load->model('education/ClassTimeSlotType_model', "ClassTimeSlotTypeModel");

		// Loads phrases system
		$this->loadPhrases([
			'global'
		]);

	}

	//------------------------------------------------------------------------------------------------------------------
	// Public methods

	public function getAllClassTimeValidityPeriods()
	{
		$this->ClassTimeSlotValidityPeriodModel->addJoin('lehre.tbl_studienplan', 'lehre.tbl_studienplan.studienplan_id=lehre.tbl_unterrichtszeiten_gueltigkeit.studienplan_id', 'LEFT');
		$this->ClassTimeSlotValidityPeriodModel->addOrder('gueltig_von', 'DESC');
		$class_time_slot_validity_period_res = $this->ClassTimeSlotValidityPeriodModel->load();
		$class_time_slot_validity_period_res = $this->getDataOrTerminateWithError($class_time_slot_validity_period_res);
		$this->terminateWithSuccess($class_time_slot_validity_period_res);
	}

	public function getClassTimeValidityPeriod($classTimeSlotValidityPeriodId)
	{
		$this->ClassTimeSlotValidityPeriodModel->addJoin('lehre.tbl_studienplan', 'lehre.tbl_studienplan.studienplan_id=lehre.tbl_unterrichtszeiten_gueltigkeit.studienplan_id', 'LEFT');
		$class_time_slot_validity_period_res = $this->ClassTimeSlotValidityPeriodModel->load($classTimeSlotValidityPeriodId);
		$class_time_slot_validity_period_res = $this->getDataOrTerminateWithError($class_time_slot_validity_period_res);
		$this->terminateWithSuccess($class_time_slot_validity_period_res);
	}

	public function createClassTimeSlotValidityPeriod()
	{	
		$this->form_validation->set_rules('validityPeriodFrom', 'Validity Period From', 'required|is_valid_date[Y-m-d]');
		$this->form_validation->set_rules('validityPeriodTo', 'Validity Period To', 'required|is_valid_date[Y-m-d]|callback_date_greater_equal[validityPeriodFrom]');
		$this->form_validation->set_rules('degreeProgramShortcode', 'Degree Program Shortcode', 'required|max_length[32]');
		$this->form_validation->set_rules('semester', 'Semester', 'required|is_natural_no_zero|less_than_equal_to[8]');
		$this->form_validation->set_rules('classTimeSlotTypeShortcode', 'Class Time Slot Type Shortcode', 'max_length[32]');
		$this->form_validation->set_rules('studyPlanId', 'Study Plan ID', 'is_natural_no_zero');

		if($this->form_validation->run() == FALSE) $this->terminateWithValidationErrors($this->form_validation->error_array());

		$this->db->trans_start();

		$result = $this->ClassTimeSlotValidityPeriodModel->insert([
			'gueltig_von' => $this->input->post('validityPeriodFrom'),
			'gueltig_bis' => $this->input->post('validityPeriodTo'),
			'oe_kurzbz' => $this->input->post('degreeProgramShortcode'),
			'ausbildungssemester' => $this->input->post('semester'),
			'anmerkung' => $this->input->post('description'),
			'unterrichtszeitentyp_kurzbz' => $this->input->post('classTimeSlotTypeShortcode') ?? '',
			'studienplan_id' => $this->input->post('studyPlanId'),
			'insertamum' => date('c'),
			'insertvon' => getAuthUid(),
			'updateamum' => date('c'),
			'updatevon' => getAuthUid(),
		]);

		$data = $this->getDataOrTerminateWithError($result);

		$this->db->trans_complete();

		$this->terminateWithSuccess(true);

		$this->terminateWithSuccess($this->input->post());
	}

	public function updateClassTimeSlotValidityPeriod($classTimeSlotValidityPeriodId)
	{
		$this->form_validation->set_rules('validityPeriodFrom', 'Validity Period From', 'required|is_valid_date[Y-m-d]');
		$this->form_validation->set_rules('validityPeriodTo', 'Validity Period To', 'required|is_valid_date[Y-m-d]|callback_date_greater_equal[validityPeriodFrom]');
		$this->form_validation->set_rules('degreeProgramShortcode', 'Degree Program Shortcode', 'required|max_length[32]');
		$this->form_validation->set_rules('semester', 'Semester', 'required|is_natural_no_zero|less_than_equal_to[8]');
		$this->form_validation->set_rules('classTimeSlotTypeShortcode', 'Class Time Slot Type Shortcode', 'max_length[32]');
		$this->form_validation->set_rules('studyPlanId', 'Study Plan ID', 'is_natural_no_zero');

		if($this->form_validation->run() == FALSE) $this->terminateWithValidationErrors($this->form_validation->error_array());

		$result = $this->ClassTimeSlotValidityPeriodModel->update($classTimeSlotValidityPeriodId, [
			'gueltig_von' => $this->input->post('validityPeriodFrom'),
			'gueltig_bis' => $this->input->post('validityPeriodTo'),
			'oe_kurzbz' => $this->input->post('degreeProgramShortcode'),
			'ausbildungssemester' => $this->input->post('semester'),
			'anmerkung' => $this->input->post('description'),
			'unterrichtszeitentyp_kurzbz' => $this->input->post('classTimeSlotTypeShortcode'),
			'studienplan_id' => $this->input->post('studyPlanId'),
			'updateamum' => date('c'),
			'updatevon' => getAuthUid(),
		]);

		$data = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess(true);
	}
	
	public function deleteClassTimeSlotValidityPeriod($classTimeSlotValidityPeriodId)
	{
		$result = $this->ClassTimeSlotValidityPeriodModel->delete($classTimeSlotValidityPeriodId);
		if (isError($result)) {
			$this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
		}
			
		$result = $this->ClassTimeSlotModel->delete(['unterrichtszeitengueltigkeit_id'=> $classTimeSlotValidityPeriodId]);
		if (isError($result)) {
			$this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
		}

		$this->terminateWithSuccess(true);
	}

	public function getClassTimeSlotsForValidityPeriod($classTimeSlotValidityPeriodId)
	{
		$this->ClassTimeSlotModel->addOrder('insertamum', 'DESC');
		$class_time_slots_res = $this->ClassTimeSlotModel->loadWhere(['unterrichtszeitengueltigkeit_id' => $classTimeSlotValidityPeriodId]);
		$class_time_slots_res = $this->getDataOrTerminateWithError($class_time_slots_res);
		$this->terminateWithSuccess($class_time_slots_res);
	}

	public function createClassTimeSlotsForValidityPeriod($classTimeSlotValidityPeriodId)
	{
		$this->form_validation->set_rules('classTimeSlots', 'Validity Period From', 'callback_validate_items_in_class_time_slots');
		if($this->form_validation->run() == FALSE) $this->terminateWithValidationErrors($this->form_validation->error_array());

		$this->db->trans_start();

		$timeSlotGroupIdentifier = uniqid();

		foreach ($this->input->post('classTimeSlots') as $timeSlot) {
			$result = $this->ClassTimeSlotModel->insert([
				'unterrichtszeit_gruppe_identifikator' => $timeSlotGroupIdentifier,
				'wochentag' => $timeSlot['wochentag'],
				'uhrzeit_von' => $timeSlot['startTime'],
				'uhrzeit_bis' => $timeSlot['endTime'],
				'unterrichtszeitentyp_kurzbz' => $timeSlot['classTimeSlotTypeShortcode'],
				'unterrichtszeitengueltigkeit_id' => $classTimeSlotValidityPeriodId,
				'insertamum' => date('c'),
				'insertvon' => getAuthUid(),
				'updateamum' => date('c'),
				'updatevon' => getAuthUid(),
			]);

			$this->getDataOrTerminateWithError($result);
		}
		$this->db->trans_complete();

		$this->terminateWithSuccess(true);
	}

	public function editClassTimeSlotsForValidityPeriod($classTimeSlotValidityPeriodId)
	{
		$this->form_validation->set_rules('classTimeSlots', 'Validity Period From', 'callback_validate_items_in_class_time_slots');
		if($this->form_validation->run() == FALSE) $this->terminateWithValidationErrors($this->form_validation->error_array());

		$this->db->trans_start();

		$timeSlotGroupIdentifier = uniqid();

		foreach ($this->input->post('classTimeSlots') as $timeSlot) {
			$data = [
				'unterrichtszeit_gruppe_identifikator' => $timeSlotGroupIdentifier,
				'wochentag' => $timeSlot['wochentag'],
				'uhrzeit_von' => $timeSlot['startTime'],
				'uhrzeit_bis' => $timeSlot['endTime'],
				'unterrichtszeitentyp_kurzbz' => $timeSlot['classTimeSlotTypeShortcode'],
				'unterrichtszeitengueltigkeit_id' => $classTimeSlotValidityPeriodId,
				'updateamum' => date('c'),
				'updatevon' => getAuthUid(),
			];
			if (isset($timeSlot['id'])) {
				$result = $this->ClassTimeSlotModel->update($timeSlot['id'], $data);
			} else {
				$result = $this->ClassTimeSlotModel->insert(array_merge($data, [
					'insertvon' => getAuthUid(),
					'updateamum' => date('c'),
				]));
			}

			$this->getDataOrTerminateWithError($result);
		}
		$this->db->trans_complete();

		$this->terminateWithSuccess(true);

	}
	public function deleteClassTimeSlotsForValidityPeriodPerGroup($classTimeSlotValidityPeriodId, $groupIdentifikator)
	{
		$result = $this->ClassTimeSlotModel->delete(['unterrichtszeitengueltigkeit_id'=> $classTimeSlotValidityPeriodId, 'unterrichtszeit_gruppe_identifikator' => $groupIdentifikator]);
		if (isError($result)) {
			$this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
		}

		$this->terminateWithSuccess(true);
	}

	public function getAllClassScheduleTypes()
	{
		$filter = $this->input->get('filter');

		if ($filter) {
			if (isset($filter['aktiv'])) {
				$this->ClassTimeSlotTypeModel->db->where('aktiv', $filter['aktiv']);
			}
		}

		$class_schedule_types_res = $this->ClassTimeSlotTypeModel->load();
		$class_schedule_types_res = $this->getDataOrTerminateWithError($class_schedule_types_res);

		$this->terminateWithSuccess($class_schedule_types_res);
	}

	public function createClassTimeSlotType()
	{
		$this->form_validation->set_rules('shortCode', 'Short Code', 'required|max_length[32]');
		$this->form_validation->set_rules('descriptions', 'Descriptions', 'callback_validate_descriptions_array');

		if($this->form_validation->run() == FALSE) $this->terminateWithValidationErrors($this->form_validation->error_array());

		$this->db->trans_start();

	 	$descriptions = $this->input->post('descriptions');
		$pgArray = $this->arrayToPgArray($descriptions);

		$query = 'INSERT INTO lehre.tbl_unterrichtszeiten_typ (
			unterrichtszeitentyp_kurzbz,
			bezeichnung_mehrsprachig,
			aktiv,
			insertamum,
			insertvon,
			updateamum,
			updatevon) VALUES (?, ?, ?, ?, ?, ?, ?)';
		$result = $this->db->query($query, [
			$this->input->post('shortCode'),
			$pgArray,
			$this->input->post('isActive'),
			date('c'),
			getAuthUid(),
			date('c'),
			getAuthUid(),
		]);
		$this->db->trans_complete();

		$this->terminateWithSuccess(true);
	}

	public function updateClassTimeSlotType($classTimeSlotTypeId)
	{
		$this->form_validation->set_rules('descriptions', 'Descriptions', 'callback_validate_descriptions_array');

		if($this->form_validation->run() == FALSE) $this->terminateWithValidationErrors($this->form_validation->error_array());

		$this->db->trans_start();

	 	$descriptions = $this->input->post('descriptions');
		$pgArray = $this->arrayToPgArray($descriptions);

		$query = 'UPDATE lehre.tbl_unterrichtszeiten_typ SET bezeichnung_mehrsprachig = ?, aktiv = ?, updateamum = ?, updatevon = ? WHERE unterrichtszeitentyp_kurzbz = ?';
		$result = $this->db->query($query, [
			$pgArray,
			$this->input->post('isActive'),
			date('c'),
			getAuthUid(),
			$classTimeSlotTypeId,
		]);
		$this->db->trans_complete();

		$this->terminateWithSuccess(true);
	}
	
	public function deleteClassTimeSlotType($classTimeSlotTypeId)
	{
		$result = $this->ClassTimeSlotTypeModel->delete(['unterrichtszeitentyp_kurzbz' => $classTimeSlotTypeId]);
		if (isError($result)) {
			$this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
		}
			
		$this->terminateWithSuccess(true);
	}
	//------------------------------------------------------------------------------------------------------------------
	// Private methods
	private function arrayToPgArray(array $assoc) {
		$flat = [];

		foreach ($assoc as $assocItem) {
			$flat[] = $assocItem['lang'] . ':' . $assocItem['value'];
		}

		$escaped = array_map(function ($v) {
			return '"' . addslashes($v) . '"';
		}, $flat);
		return '{' . implode(',', $escaped) . '}';
	}

	public function date_greater_equal($toDate, $fromField)
	{
		$fromDate = $this->input->post($fromField);

		// Check if "from" exists
		if (!$fromDate) {
			$this->form_validation->set_message(
				'date_greater_equal',
				'Validity Period From is required.'
			);
			return false;
		}

		// Validate both dates
		if (!strtotime($toDate) || !strtotime($fromDate)) {
			$this->form_validation->set_message(
				'date_greater_equal',
				'Both dates must be valid.'
			);
			return false;
		}

		// Compare dates
		if (strtotime($toDate) < strtotime($fromDate)) {
			$this->form_validation->set_message(
				'date_greater_equal',
				'The {field} must be greater than or equal to Validity Period From.'
			);
			return false;
		}

		return true;
	}

	public function validate_items_in_class_time_slots($classTimeSlots)
	{
		// see if $classTimeSlots is an array and has at least one item

		if (!is_array($this->input->post('classTimeSlots')) || count($this->input->post('classTimeSlots')) === 0) {
			$this->form_validation->set_message(
				'validate_items_in_class_time_slots',
				'At least one class time slot is required.'
			);
			return false;
		}

		foreach ($this->input->post('classTimeSlots') as $index => $timeSlot) {
			if (!isset($timeSlot['wochentag'], $timeSlot['startTime'], $timeSlot['endTime'], $timeSlot['classTimeSlotTypeShortcode'])) {
				$this->form_validation->set_message(
					'validate_items_in_class_time_slots',
					'Each class time slot must have a weekday, start time, end time and class time slot type shortcode.'
				);
				return false;
			}

			if (!in_array($timeSlot['wochentag'], [1, 2, 3, 4, 5])) {
				$this->form_validation->set_message(
					'validate_items_in_class_time_slots',
					'Weekday must be an integer between 1 (Monday) and 5 (Friday).'
				);
				return false;
			}


			log_message('error', 'Validating class time slots: ' . print_r($timeSlot['startTime'], true));
			if (!strtotime($timeSlot['startTime']) || !strtotime($timeSlot['endTime'])) {
				$this->form_validation->set_message(
					'validate_items_in_class_time_slots',
					'Start time and end time must be valid time strings.'
				);
				return false;
			}

			if (strtotime($timeSlot['endTime']) <= strtotime($timeSlot['startTime'])) {
				$this->form_validation->set_message(
					'validate_items_in_class_time_slots',
					'End time must be greater than start time.'
				);
				return false;
			}
		}

		$slotsByDay = [];
		foreach ($this->input->post('classTimeSlots') as $timeSlot) {
			$slotsByDay[$timeSlot['wochentag']][] = $timeSlot;
		}

		foreach ($slotsByDay as $day => $slots) {
			usort($slots, function ($a, $b) {
				return strtotime($a['startTime']) - strtotime($b['startTime']);
			});

			for ($i = 1; $i < count($slots); $i++) {
				if (strtotime($slots[$i]['startTime']) < strtotime($slots[$i - 1]['endTime'])) {
					$this->form_validation->set_message(
						'validate_items_in_class_time_slots',
						'Class time slots for each day must not overlap.'
					);
					return false;
				}
			}
		}


		return true;
	}

	public function validate_descriptions_array($descriptions)
	{
		$descriptions = $this->input->post('descriptions');
		if (!is_array($descriptions) || count($descriptions) === 0) {
			$this->form_validation->set_message(
				'validate_descriptions_array',
				'Descriptions must be a non-empty array.'
			);
			return false;
		}

		foreach ($descriptions as $index => $description) {
			if (!isset($description['lang'], $description['value'])) {
				$this->form_validation->set_message(
					'validate_descriptions_array',
					'Each description must have a language and a value.'
				);
				return false;
			}

			if (empty($description['lang']) || empty($description['value'])) {
				$this->form_validation->set_message(
					'validate_descriptions_array',
					'Language and value in each description must not be empty.'
				);
				return false;
			}
		}

		return true;
	}
}

