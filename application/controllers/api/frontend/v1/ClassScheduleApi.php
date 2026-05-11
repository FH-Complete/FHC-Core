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
			'getAllClassTimeValidityPeriodsPerOrganizationalUnit' => array('lehre/unterrichtszeiten_gk:r'),
			'getClassTimeValidityPeriod' => array('lehre/unterrichtszeiten_gk:r'),
			'createClassTimeSlotValidityPeriod' => array('lehre/unterrichtszeiten_gk:rw'),
			'updateClassTimeSlotValidityPeriod' => array('lehre/unterrichtszeiten_gk:rw'),
			'deleteClassTimeSlotValidityPeriod' => array('lehre/unterrichtszeiten_gk:rw'),
			'getClassTimeSlotsForValidityPeriod' => array('lehre/unterrichtszeiten_gk:r'),
			'createClassTimeSlotsForValidityPeriod' => array('lehre/unterrichtszeiten_gk:r'),
			'updateClassTimeSlotsForValidityPeriod' => array('lehre/unterrichtszeiten_gk:r'),
			'deleteClassTimeSlotsForValidityPeriod' => array('lehre/unterrichtszeiten_gk:r'),
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
			'global',
			'ui',
		]);

	}

	//------------------------------------------------------------------------------------------------------------------
	// Public methods

	public function getAllClassTimeValidityPeriods()
	{
		$entitledOrganizationalUnitsShortCodes = $this->permissionlib->getOE_isEntitledFor('lehre/unterrichtszeiten_gk');

		$organizationalUnitShortCode = $this->input->get('organizationalUnitShortCode');
		$validityPeriodFrom = $this->input->get('validityPeriodFrom');
		$validityPeriodTo = $this->input->get('validityPeriodTo');

		$this->ClassTimeSlotValidityPeriodModel->db->where_in('lehre.tbl_unterrichtszeiten_gueltigkeit.oe_kurzbz', $entitledOrganizationalUnitsShortCodes);
		$this->ClassTimeSlotValidityPeriodModel->addSelect(
			'lehre.tbl_unterrichtszeiten_gueltigkeit.*,' .
			'public.tbl_organisationseinheit.bezeichnung as organisationseinheit_bezeichnung,' .
			'public.tbl_organisationseinheit.organisationseinheittyp_kurzbz as  organisationseinheit_organisationseinheittyp_kurzbz,' .
			'lehre.tbl_studienplan.bezeichnung as studienplan_bezeichnung,' . 
			'lehre.tbl_unterrichtszeiten_typ.bezeichnung_mehrsprachig as unterrichtszeiten_typ_bezeichnung_mehrsprachig, '
		);
		$this->ClassTimeSlotValidityPeriodModel->addJoin('lehre.tbl_studienplan', 'lehre.tbl_studienplan.studienplan_id=lehre.tbl_unterrichtszeiten_gueltigkeit.studienplan_id', 'LEFT');
		$this->ClassTimeSlotValidityPeriodModel->addJoin('public.tbl_organisationseinheit', 'public.tbl_organisationseinheit.oe_kurzbz=lehre.tbl_unterrichtszeiten_gueltigkeit.oe_kurzbz', 'LEFT');
		$this->ClassTimeSlotValidityPeriodModel->addJoin('lehre.tbl_unterrichtszeiten_typ', 'lehre.tbl_unterrichtszeiten_typ.unterrichtszeitentyp_kurzbz=lehre.tbl_unterrichtszeiten_gueltigkeit.unterrichtszeitentyp_kurzbz', 'LEFT');
		$this->ClassTimeSlotValidityPeriodModel->addOrder('gueltig_von', 'DESC');

		if ($organizationalUnitShortCode) {
			$this->ClassTimeSlotValidityPeriodModel->db->where('lehre.tbl_unterrichtszeiten_gueltigkeit.oe_kurzbz', $organizationalUnitShortCode);
		}
		
		if ($validityPeriodFrom) {
			$this->ClassTimeSlotValidityPeriodModel->db
				->where('lehre.tbl_unterrichtszeiten_gueltigkeit.gueltig_von >=', date('Y-m-d', strtotime($validityPeriodFrom)));
		}
		if ($validityPeriodTo) {
			$this->ClassTimeSlotValidityPeriodModel->db
				->where('lehre.tbl_unterrichtszeiten_gueltigkeit.gueltig_bis <=', date('Y-m-d', strtotime($validityPeriodTo)));
		}
		
		$class_time_slot_validity_period_res = $this->ClassTimeSlotValidityPeriodModel->load();
		$class_time_slot_validity_period_res = $this->getDataOrTerminateWithError($class_time_slot_validity_period_res);
		$this->terminateWithSuccess($class_time_slot_validity_period_res);
	}

	public function getAllClassTimeValidityPeriodsPerOrganizationalUnit($organizationUnitId)
	{
		if (!$this->isUserEntitledForOrganizationalUnit($organizationUnitId, 'lehre/unterrichtszeiten_gk')) {
			$this->terminateWithError($this->p->t('ui', 'keineBerechtigung'));
		}

		$this->ClassTimeSlotValidityPeriodModel->addSelect(
			'lehre.tbl_unterrichtszeiten_gueltigkeit.*,' .
			'public.tbl_organisationseinheit.bezeichnung as organisationseinheit_bezeichnung,' .
			'public.tbl_organisationseinheit.organisationseinheittyp_kurzbz as  organisationseinheit_organisationseinheittyp_kurzbz,' .
			'lehre.tbl_studienplan.bezeichnung as studienplan_bezeichnung,' . 
			'lehre.tbl_unterrichtszeiten_typ.bezeichnung_mehrsprachig as unterrichtszeiten_typ_bezeichnung_mehrsprachig, '
		);
		$this->ClassTimeSlotValidityPeriodModel->addJoin('lehre.tbl_studienplan', 'lehre.tbl_studienplan.studienplan_id=lehre.tbl_unterrichtszeiten_gueltigkeit.studienplan_id', 'LEFT');
		$this->ClassTimeSlotValidityPeriodModel->addJoin('public.tbl_organisationseinheit', 'public.tbl_organisationseinheit.oe_kurzbz=lehre.tbl_unterrichtszeiten_gueltigkeit.oe_kurzbz', 'LEFT');
		$this->ClassTimeSlotValidityPeriodModel->addJoin('lehre.tbl_unterrichtszeiten_typ', 'lehre.tbl_unterrichtszeiten_typ.unterrichtszeitentyp_kurzbz=lehre.tbl_unterrichtszeiten_gueltigkeit.unterrichtszeitentyp_kurzbz', 'LEFT');
		$this->ClassTimeSlotValidityPeriodModel->addOrder('gueltig_von', 'DESC');
		$class_time_slot_validity_period_res = $this->ClassTimeSlotValidityPeriodModel->loadWhere(['lehre.tbl_unterrichtszeiten_gueltigkeit.oe_kurzbz' => $organizationUnitId]);
		$class_time_slot_validity_period_res = $this->getDataOrTerminateWithError($class_time_slot_validity_period_res);
		$this->terminateWithSuccess($class_time_slot_validity_period_res);
	}

	public function getClassTimeValidityPeriod($classTimeSlotValidityPeriodId)
	{
		$this->ClassTimeSlotValidityPeriodModel->addSelect('
			lehre.tbl_unterrichtszeiten_gueltigkeit.*,
			public.tbl_organisationseinheit.oe_kurzbz as oe_kurzbz,
			public.tbl_organisationseinheit.bezeichnung as oe_bezeichnung,
			public.tbl_organisationseinheit.organisationseinheittyp_kurzbz as  oe_organisationseinheittyp_kurzbz,
			lehre.tbl_studienplan.studienplan_id,
			lehre.tbl_studienplan.bezeichnung as studienplan_bezeichnung,
		');
		$this->ClassTimeSlotValidityPeriodModel->addJoin('public.tbl_organisationseinheit', 'public.tbl_organisationseinheit.oe_kurzbz=lehre.tbl_unterrichtszeiten_gueltigkeit.oe_kurzbz', 'LEFT');
		$this->ClassTimeSlotValidityPeriodModel->addJoin('lehre.tbl_studienplan', 'lehre.tbl_studienplan.studienplan_id=lehre.tbl_unterrichtszeiten_gueltigkeit.studienplan_id', 'LEFT');
		$class_time_slot_validity_period_res = $this->ClassTimeSlotValidityPeriodModel->load($classTimeSlotValidityPeriodId);

		$class_time_slot_validity_period_res = $this->getDataOrTerminateWithError($class_time_slot_validity_period_res);
		if (!$class_time_slot_validity_period_res || count($class_time_slot_validity_period_res) === 0) {
			$this->terminateWithError($this->p->t('ui', 'classTimeSlotValidityPeriodNotFound'));
		}

		if (!$this->isUserEntitledForOrganizationalUnit($class_time_slot_validity_period_res[0]->oe_kurzbz, 'lehre/unterrichtszeiten_gk')) {
			$this->terminateWithError($this->p->t('ui', 'keineBerechtigung'));
		}

		$this->terminateWithSuccess($class_time_slot_validity_period_res);
	}

	public function createClassTimeSlotValidityPeriod()
	{	
		$this->form_validation->set_rules('validityPeriodFrom', 'Validity Period From', 'required|is_valid_date[Y-m-d]', [
			'required' => $this->p->t('ui', 'error_fieldRequired', ['field' =>  $this->p->t('ui', 'field_validityPeriodFrom')]),
			'is_valid_date' => $this->p->t('ui', 'error_fieldInvalidDate', ['field' =>  $this->p->t('ui', 'field_validityPeriodFrom')])
		]);
		$this->form_validation->set_rules('validityPeriodTo', 'Validity Period To', 'required|is_valid_date[Y-m-d]|callback_date_greater_equal[validityPeriodFrom]');
		$this->form_validation->set_rules('organizationalUnitShortCode', 'Organizational Unit Shortcode', 'required|max_length[32]', [
			'required' => $this->p->t('ui', 'error_fieldRequired', ['field' =>  $this->p->t('ui', 'field_organizationalUnit')]),
			'max_length' => $this->p->t('ui', 'error_fieldMaxLength', ['field' =>  $this->p->t('ui', 'field_organizationalUnit'), 'max' => 32])
		]);
		$this->form_validation->set_rules('semester', 'Semester', 'is_natural_no_zero|less_than_equal_to[8]', [
			'is_natural_no_zero' => $this->p->t('ui', 'error_fieldInvalid', ['field' =>  $this->p->t('ui', 'field_semester')]),
			'less_than_equal_to' => $this->p->t('ui', 'error_fieldMaxValue', ['field' =>  $this->p->t('ui', 'field_semester'), 'max' => 8])
		]);
		$this->form_validation->set_rules('classTimeSlotTypeShortcode', 'Class Time Slot Type Shortcode', 'max_length[32]');
		$this->form_validation->set_rules('studyPlanId', 'Study Plan ID', 'is_natural_no_zero');

		if($this->form_validation->run() == FALSE) $this->terminateWithValidationErrors($this->form_validation->error_array());

		if (!$this->isUserEntitledForOrganizationalUnit($this->input->post('organizationalUnitShortCode'), 'lehre/unterrichtszeiten_gk')) {
			$this->terminateWithError($this->p->t('ui', 'keineBerechtigung'));
		}

		$this->db->trans_start();

		$result = $this->ClassTimeSlotValidityPeriodModel->insert([
			'gueltig_von' => $this->input->post('validityPeriodFrom'),
			'gueltig_bis' => $this->input->post('validityPeriodTo'),
			'oe_kurzbz' => $this->input->post('organizationalUnitShortCode'),
			'ausbildungssemester' => $this->input->post('semester'),
			'anmerkung' => $this->input->post('description'),
			'unterrichtszeitentyp_kurzbz' => $this->input->post('classTimeSlotTypeShortcode') ?? null,
			'studienplan_id' => $this->input->post('studyPlanId'),
			'insertamum' => date('c'),
			'insertvon' => getAuthUid(),
			'updateamum' => date('c'),
			'updatevon' => getAuthUid(),
		]);

		$data = $this->getDataOrTerminateWithError($result);

		$this->db->trans_complete();

		$this->terminateWithSuccess(true);
	}

	public function updateClassTimeSlotValidityPeriod($classTimeSlotValidityPeriodId)
	{
		$this->form_validation->set_rules('validityPeriodFrom', 'Validity Period From', 'required|is_valid_date[Y-m-d]', [
			'required' => $this->p->t('ui', 'error_fieldRequired', ['field' =>  $this->p->t('ui', 'field_validityPeriodFrom')]),
			'is_valid_date' => $this->p->t('ui', 'error_fieldInvalidDate', ['field' =>  $this->p->t('ui', 'field_validityPeriodFrom')])
		]);
		$this->form_validation->set_rules('validityPeriodTo', 'Validity Period To', 'required|is_valid_date[Y-m-d]|callback_date_greater_equal[validityPeriodFrom]');
		$this->form_validation->set_rules('organizationalUnitShortCode', 'Organizational Unit Shortcode', 'required|max_length[32]', [
			'required' => $this->p->t('ui', 'error_fieldRequired', ['field' =>  $this->p->t('ui', 'field_organizationalUnit')]),
			'max_length' => $this->p->t('ui', 'error_fieldMaxLength', ['field' =>  $this->p->t('ui', 'field_organizationalUnit'), 'max' => 32])
		]);
		$this->form_validation->set_rules('semester', 'Semester', 'is_natural_no_zero|less_than_equal_to[8]', [
			'is_natural_no_zero' => $this->p->t('ui', 'error_fieldInvalid', ['field' =>  $this->p->t('ui', 'field_semester')]),
			'less_than_equal_to' => $this->p->t('ui', 'error_fieldMaxValue', ['field' =>  $this->p->t('ui', 'field_semester'), 'max' => 8])
		]);
		$this->form_validation->set_rules('classTimeSlotTypeShortcode', 'Class Time Slot Type Shortcode', 'max_length[32]');
		$this->form_validation->set_rules('studyPlanId', 'Study Plan ID', 'is_natural_no_zero');

		if($this->form_validation->run() == FALSE) $this->terminateWithValidationErrors($this->form_validation->error_array());

		if (!$this->isUserEntitledForOrganizationalUnit($this->input->post('organizationalUnitShortCode'), 'lehre/unterrichtszeiten_gk')) {
			$this->terminateWithError($this->p->t('ui', 'keineBerechtigung'));
		}
		
		$this->db->trans_start();

		$result = $this->ClassTimeSlotValidityPeriodModel->update($classTimeSlotValidityPeriodId, [
			'gueltig_von' => $this->input->post('validityPeriodFrom'),
			'gueltig_bis' => $this->input->post('validityPeriodTo'),
			'oe_kurzbz' => $this->input->post('organizationalUnitShortCode'),
			'ausbildungssemester' => $this->input->post('semester'),
			'anmerkung' => $this->input->post('description'),
			'unterrichtszeitentyp_kurzbz' => $this->input->post('classTimeSlotTypeShortcode') ?? null,
			'studienplan_id' => $this->input->post('studyPlanId'),
			'updateamum' => date('c'),
			'updatevon' => getAuthUid(),
		]);

		$this->db->trans_complete();

		$data = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess(true);
	}
	
	public function deleteClassTimeSlotValidityPeriod($classTimeSlotValidityPeriodId)
	{
		$validityPeriodResult = $this->ClassTimeSlotValidityPeriodModel->loadWhere(['unterrichtszeitengueltigkeit_id' => $classTimeSlotValidityPeriodId]);
		$validityPeriod = $this->getDataOrTerminateWithError($validityPeriodResult)[0];
		if (!$this->isUserEntitledForOrganizationalUnit($validityPeriod->oe_kurzbz, 'lehre/unterrichtszeiten_gk')) {
			$this->terminateWithError($this->p->t('ui', 'keineBerechtigung'));
		}

		$this->db->trans_start();

		$result = $this->ClassTimeSlotModel->delete(['unterrichtszeitengueltigkeit_id'=> $classTimeSlotValidityPeriodId]);
		if (isError($result)) {
			$this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
		}
		
		$result = $this->ClassTimeSlotValidityPeriodModel->delete($classTimeSlotValidityPeriodId);
		if (isError($result)) {
			$this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
		}
			
		$this->db->trans_complete();

		$this->terminateWithSuccess(true);
	}

	public function getClassTimeSlotsForValidityPeriod($classTimeSlotValidityPeriodId)
	{
		$validityPeriodResult = $this->ClassTimeSlotValidityPeriodModel->loadWhere(['unterrichtszeitengueltigkeit_id' => $classTimeSlotValidityPeriodId]);
		$validityPeriodData = $this->getDataOrTerminateWithError($validityPeriodResult);
		if (!$validityPeriodData || count($validityPeriodData) === 0) {
			$this->terminateWithError($this->p->t('ui', 'classTimeSlotValidityPeriodNotFound'));
		}
		
		$validityPeriod = $validityPeriodData[0];

		if (!$this->isUserEntitledForOrganizationalUnit($validityPeriod->oe_kurzbz, 'lehre/unterrichtszeiten_gk')) {
			$this->terminateWithError($this->p->t('ui', 'keineBerechtigung'));
		}

		$this->ClassTimeSlotModel->addOrder('insertamum', 'DESC');
		$class_time_slots_res = $this->ClassTimeSlotModel->loadWhere(['unterrichtszeitengueltigkeit_id' => $classTimeSlotValidityPeriodId]);
		$class_time_slots_res = $this->getDataOrTerminateWithError($class_time_slots_res);
		$this->terminateWithSuccess($class_time_slots_res);
	}

	public function createClassTimeSlotsForValidityPeriod($classTimeSlotValidityPeriodId)
	{
		$this->form_validation->set_rules('unterrichtszeiten', 'Class Time Slots', 'callback_validate_items_in_class_time_slots');
		if($this->form_validation->run() == FALSE) $this->terminateWithValidationErrors($this->form_validation->error_array());

		$validityPeriodResult = $this->ClassTimeSlotValidityPeriodModel->loadWhere(['unterrichtszeitengueltigkeit_id' => $classTimeSlotValidityPeriodId]);
		$validityPeriod = $this->getDataOrTerminateWithError($validityPeriodResult)[0];
		if (!$this->isUserEntitledForOrganizationalUnit($validityPeriod->oe_kurzbz, 'lehre/unterrichtszeiten_gk')) {
			$this->terminateWithError($this->p->t('ui', 'keineBerechtigung'));
		}

		$this->db->trans_start();

		foreach ($this->input->post('unterrichtszeiten') as $timeSlot) {
			$result = $this->ClassTimeSlotModel->insert([
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

	public function updateClassTimeSlotsForValidityPeriod($classTimeSlotValidityPeriodId)
	{
		$this->form_validation->set_rules('unterrichtszeiten', 'Class Time Slots', 'callback_validate_items_in_class_time_slots');
		if($this->form_validation->run() == FALSE) $this->terminateWithValidationErrors($this->form_validation->error_array());

		$validityPeriodResult = $this->ClassTimeSlotValidityPeriodModel->loadWhere(['unterrichtszeitengueltigkeit_id' => $classTimeSlotValidityPeriodId]);
		$validityPeriod = $this->getDataOrTerminateWithError($validityPeriodResult)[0];
		if (!$this->isUserEntitledForOrganizationalUnit($validityPeriod->oe_kurzbz, 'lehre/unterrichtszeiten_gk')) {
			$this->terminateWithError($this->p->t('ui', 'keineBerechtigung'));
		}

		$this->db->trans_start();

		$currentTimeSlotsResult = $this->ClassTimeSlotModel->loadWhere(['unterrichtszeitengueltigkeit_id' => $classTimeSlotValidityPeriodId]);
		$currentTimeSlots = $this->getDataOrTerminateWithError($currentTimeSlotsResult);
		$currentTimeSlotIds = array_column($currentTimeSlots, 'unterrichtszeit_id');
		
		$removedTimeSlotIds = array_values(array_diff($currentTimeSlotIds, array_column($this->input->post('unterrichtszeiten'), 'id')));

		if (count($removedTimeSlotIds) > 0) {
			$query = 'DELETE FROM lehre.tbl_unterrichtszeiten WHERE unterrichtszeit_id IN ?';
			$result = $this->db->query($query, [ $removedTimeSlotIds ]);
		}

		foreach ($this->input->post('unterrichtszeiten') as $timeSlot) {
			$data = [
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
	public function deleteClassTimeSlotsForValidityPeriod($classTimeSlotValidityPeriodId)
	{
		$validityPeriodResult = $this->ClassTimeSlotValidityPeriodModel->loadWhere(['unterrichtszeitengueltigkeit_id' => $classTimeSlotValidityPeriodId]);
		$validityPeriod = $this->getDataOrTerminateWithError($validityPeriodResult)[0];
		if (!$this->isUserEntitledForOrganizationalUnit($validityPeriod->oe_kurzbz, 'lehre/unterrichtszeiten_gk')) {
			$this->terminateWithError($this->p->t('ui', 'keineBerechtigung'));
		}
		
		$this->db->trans_start();

		$result = $this->ClassTimeSlotModel->delete(['unterrichtszeitengueltigkeit_id'=> $classTimeSlotValidityPeriodId]);
		if (isError($result)) {
			$this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
		}

		$this->db->trans_complete();

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
		$this->form_validation->set_rules('shortCode', 'Short Code', 'required|max_length[32]', [
			'required' => $this->p->t('ui', 'error_fieldRequired', ['field' =>  $this->p->t('ui', 'field_classTimeSlotTypeShortCode')]),
			'max_length' => $this->p->t('ui', 'error_fieldMaxLength', ['field' =>  $this->p->t('ui', 'field_classTimeSlotTypeShortCode'), 'max' => 32])
		]);
		$this->form_validation->set_rules('descriptions', 'Descriptions', 'callback_validate_descriptions_array');
		$this->form_validation->set_rules('backgroundColor', 'Background Color', 'required|regex_match[/^#([0-9a-fA-F]{3}){1,2}$/]', [
			'required' => $this->p->t('ui', 'error_fieldRequired', ['field' =>  $this->p->t('ui', 'field_backgroundColor')]),
			'regex_match' => $this->p->t('ui', 'error_fieldInvalid', ['field' =>  $this->p->t('ui', 'field_backgroundColor')]),
		]);

		if($this->form_validation->run() == FALSE) $this->terminateWithValidationErrors($this->form_validation->error_array());

		$this->db->trans_start();

	 	$descriptions = $this->input->post('descriptions');
		$pgArray = $this->arrayToPgArray($descriptions);

		$query = 'INSERT INTO lehre.tbl_unterrichtszeiten_typ (
			unterrichtszeitentyp_kurzbz,
			bezeichnung_mehrsprachig,
			hintergrundfarbe,
			aktiv,
			insertamum,
			insertvon,
			updateamum,
			updatevon) VALUES (?, ?, ?, ?, ?, ?, ?, ?)';
		$result = $this->db->query($query, [
			$this->input->post('shortCode'),
			$pgArray,
			$this->input->post('backgroundColor'),
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

		
		$parsedClassTimeSlotTypeId = urldecode($classTimeSlotTypeId);

	 	$descriptions = $this->input->post('descriptions');
		$pgArray = $this->arrayToPgArray($descriptions);

		$query = 'UPDATE lehre.tbl_unterrichtszeiten_typ SET bezeichnung_mehrsprachig = ?, hintergrundfarbe = ?, aktiv = ?, updateamum = ?, updatevon = ? WHERE unterrichtszeitentyp_kurzbz = ?';
		$result = $this->db->query($query, [
			$pgArray,
			$this->input->post('backgroundColor'),
			$this->input->post('isActive'),
			date('c'),
			getAuthUid(),
			$parsedClassTimeSlotTypeId,
		]);
		$this->db->trans_complete();

		$this->terminateWithSuccess(true);
	}
	
	public function deleteClassTimeSlotType($classTimeSlotTypeId)
	{
		$isClassTimeSlotDeletable = true;
		$parsedClassTimeSlotTypeId = urldecode($classTimeSlotTypeId);
		
		$validityPeriodResult = $this->ClassTimeSlotValidityPeriodModel->loadWhere(['unterrichtszeitentyp_kurzbz' => $parsedClassTimeSlotTypeId]);
		$validityPeriod = $this->getDataOrTerminateWithError($validityPeriodResult);
		if ($validityPeriod && count($validityPeriod) > 0) {
			$isClassTimeSlotDeletable = false;
		}

		$classTimeSlotResult = $this->ClassTimeSlotModel->loadWhere(['unterrichtszeitentyp_kurzbz' => $parsedClassTimeSlotTypeId]);
		$classTimeSlot = $this->getDataOrTerminateWithError($classTimeSlotResult);
		if ($classTimeSlot && count($classTimeSlot) > 0) {
			$isClassTimeSlotDeletable = false;
		}

		$this->db->trans_start();

		if (!$isClassTimeSlotDeletable) {
			$result = $this->ClassTimeSlotTypeModel->update($parsedClassTimeSlotTypeId, ['aktiv' => false, 'updateamum' => date('c'), 'updatevon' => getAuthUid()]);
		} else {
			$result = $this->ClassTimeSlotTypeModel->delete(['unterrichtszeitentyp_kurzbz' => $parsedClassTimeSlotTypeId]);
		}
		if (isError($result)) {
			$this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
		}
		
		$this->db->trans_complete();

		$this->terminateWithSuccess(true);
	}
	//------------------------------------------------------------------------------------------------------------------
	// Private methods
	private function arrayToPgArray(array $assoc) {
		$flat = [null, null];

		foreach ($assoc as $assocItem) {
			if ($assocItem['lang'] === 'de') {
				$flat[0] = $assocItem['value'];
			} else if ($assocItem['lang'] === 'en') {
				$flat[1] = $assocItem['value'];
			} else {
				$flat[] = $assocItem['value'];
			}
		}

		$escaped = array_map(function ($v) {
			return '"' . addslashes($v) . '"';
		}, $flat);
		return '{' . implode(',', $escaped) . '}';
	}

	public function date_greater_equal($toDate, $fromField)
	{
		$fromDate = $this->input->post($fromField);

		if (!$fromDate) {
			$this->form_validation->set_message(
				'date_greater_equal',
				$this->p->t('ui', 'error_fieldRequired', ['field' =>  $this->p->t('ui', 'field_validityPeriodTo')])
			);
			return false;
		}

		if (!strtotime($toDate) || !strtotime($fromDate)) {
			$this->form_validation->set_message(
				'date_greater_equal',
				$this->p->t('ui', 'error_fieldInvalidDate', ['field' =>  $this->p->t('ui', 'field_validityPeriodTo')])
			);
			return false;
		}

		if (strtotime($toDate) < strtotime($fromDate)) {
			$this->form_validation->set_message(
				'date_greater_equal',
				$this->p->t('ui', 'error_fieldDateGreaterEqual', ['field' =>  $this->p->t('ui', 'field_validityPeriodTo'), 'otherField' => $this->p->t('ui', 'field_validityPeriodFrom')])
			);
			return false;
		}

		return true;
	}

	public function validate_items_in_class_time_slots($unterrichtszeiten)
	{
		if (!is_array($this->input->post('unterrichtszeiten')) || count($this->input->post('unterrichtszeiten')) === 0) {
			$this->form_validation->set_message(
				'validate_items_in_class_time_slots',
				$this->p->t('ui', 'error_fieldRequired', ['field' =>  $this->p->t('ui', 'field_classTimeSlot')])
			);
			return false;
		}

		foreach ($this->input->post('unterrichtszeiten') as $index => $timeSlot) {
			if (!isset($timeSlot['wochentag'], $timeSlot['startTime'], $timeSlot['endTime'], $timeSlot['classTimeSlotTypeShortcode'])) {
				$this->form_validation->set_message(
					'validate_items_in_class_time_slots',
					$this->p->t('ui', 'error_fieldClassTimeSlotContentInvalid', ['field' =>  $this->p->t('ui', 'field_classTimeSlot')])
				);
				return false;
			}

			if (!in_array($timeSlot['wochentag'], [1, 2, 3, 4, 5, 6, 0])) {
				$this->form_validation->set_message(
					'validate_items_in_class_time_slots',
					$this->p->t('ui', 'error_fieldWeekdayInvalid', ['field' =>  $this->p->t('ui', 'field_classTimeSlot')])
				);
				return false;
			}


			if (!strtotime($timeSlot['startTime']) || !strtotime($timeSlot['endTime'])) {
				$this->form_validation->set_message(
					'validate_items_in_class_time_slots',
					$this->p->t('ui', 'error_fieldClassTimeSlotTimeInvalid', ['field' =>  $this->p->t('ui', 'field_classTimeSlot')])
				);
				return false;
			}

			if (strtotime($timeSlot['endTime']) <= strtotime($timeSlot['startTime'])) {
				$this->form_validation->set_message(
					'validate_items_in_class_time_slots',
					$this->p->t('ui', 'error_fieldDateGreaterEqual', ['field' =>  $this->p->t('ui', 'field_classTimeSlotEndTime'), 'otherField' => $this->p->t('ui', 'field_classTimeSlotStartTime')])
				);
				return false;
			}
		}

		$slotsByDay = [];
		foreach ($this->input->post('unterrichtszeiten') as $timeSlot) {
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
						$this->p->t('ui', 'error_fieldClassTimeSlotOverlap', ['field' =>  $this->p->t('ui', 'field_classTimeSlot')])
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
				$this->p->t('ui', 'error_fieldRequired', ['field' =>  $this->p->t('ui', 'field_descriptions')])
			);
			return false;
		}

		foreach ($descriptions as $index => $description) {
			if (!isset($description['lang'], $description['value'])) {
				$this->form_validation->set_message(
					'validate_descriptions_array',
					$this->p->t('ui', 'error_fieldDescriptionContentInvalid')
				);
				return false;
			}

			if (empty($description['lang']) || empty($description['value'])) {
				$this->form_validation->set_message(
					'validate_descriptions_array',
					$this->p->t('ui', 'error_fieldDescriptionContentInvalid')
				);
				return false;
			}
		}

		return true;
	}

	private function isUserEntitledForOrganizationalUnit($organizationalUnitShortCode, $requiredPermission)
	{
		$entitledOrganizationalUnitsShortCodes = $this->permissionlib->getOE_isEntitledFor($requiredPermission);
		return in_array($organizationalUnitShortCode, $entitledOrganizationalUnitsShortCodes);
	}
}

