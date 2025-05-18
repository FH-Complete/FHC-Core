<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

use \DateTime as DateTime;

class Projektbetreuer extends FHCAPI_Controller
{
	public function __construct()
	{
		parent::__construct([
			'getProjektbetreuer' => ['admin:r', 'assistenz:r'],
			'saveProjektbetreuer' => ['admin:rw', 'assistenz:rw'],
			'deleteProjektbetreuer' => ['admin:rw', 'assistenz:rw'],
			'getBetreuerarten' => ['admin:r', 'assistenz:r'],
			'getNoten' => ['admin:r', 'assistenz:r'],
			'getDefaultStundensaetze' => ['admin:r', 'assistenz:r'],
			'getProjektbetreuerBySearchQuery' => ['admin:r', 'assistenz:r'],
			'validateProjektbetreuer' => ['admin:r', 'assistenz:r']
		]);

		// Load Libraries
		$this->load->library('form_validation');

		// Load language phrases
		$this->loadPhrases([
			'ui',
			'person',
			'projektarbeit'
		]);

		// Load models
		$this->load->model('education/Projektbetreuer_model', 'ProjektbetreuerModel');
		$this->load->model('education/Betreuerart_model', 'BetreuerartModel');
		$this->load->model('ressource/Stundensatz_model', 'StundensatzModel');
		$this->load->model('education/Projektarbeit_model', 'ProjektarbeitModel');
		$this->load->model('education/Note_model', 'NoteModel');
		$this->load->model('person/Person_model', 'PersonModel');

		// load libraries
		$this->load->library('PermissionLib');
	}

	public function getProjektbetreuer()
	{
		$projektarbeit_id = $this->input->get('projektarbeit_id');

		if (!isset($projektarbeit_id)) $this->terminateWithError($this->p->t('ui', 'error_missingId', ['id'=> 'Projektarbeit ID']), self::ERROR_TYPE_GENERAL);

		$this->ProjektbetreuerModel->addSelect(
			'projektarbeit_id, person_id, nachname, vorname, note, punkte, round(stunden, 1) AS stunden,
			stundensatz, betreuerart_kurzbz, vertrag_id, titelpre, titelpost'
		);
		$this->ProjektbetreuerModel-> addSelect("CASE
				WHEN EXISTS
					(SELECT 1 FROM public.tbl_benutzer JOIN public.tbl_mitarbeiter ON(uid=mitarbeiter_uid) WHERE person_id=pers.person_id)
				THEN 'Mitarbeiter'
				WHEN EXISTS
					(SELECT 1 FROM public.tbl_benutzer JOIN public.tbl_student ON(uid=student_uid) WHERE person_id=pers.person_id)
				THEN 'Student'
				ELSE 'Person'
			END AS status");
		$this->ProjektbetreuerModel->addJoin('public.tbl_person pers', 'person_id');
		$result = $this->ProjektbetreuerModel->loadWhere(['projektarbeit_id' => $projektarbeit_id]);

		if (isError($result)) $this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);

		if (!hasData($result)) $this->terminateWithSuccess([]);

		$projektbetreuer = getData($result);

		//~ foreach ($projektbetreuer as $projektarbeit)
		//~ {
			//~ $projektarbeit_id = $projektarbeit->projektarbeit_id;
			//~ $abgabeRes = $this->PaabgabeModel->getEndabgabe($projektarbeit_id);

			//~ if (isError($abgabeRes)) $this->terminateWithError(getError($abgabeRes), self::ERROR_TYPE_GENERAL);

			//~ if (hasData($abgabeRes))
			//~ {
				//~ $paabgabe = getData($abgabeRes)[0];
				//~ $projektarbeit->abgabedatum = $paabgabe->abgabedatum;
			//~ }
		//~ }

		$this->terminateWithSuccess($this->_addFullNameToBetreuer($projektbetreuer));
	}

	public function saveProjektbetreuer()
	{
		$projektarbeit_id = $this->input->post('projektarbeit_id');

		if (!isset($projektarbeit_id) || !is_numeric($projektarbeit_id))
			return $this->terminateWithError($this->p->t('ui', 'error_missingId', ['id'=> 'Projektarbeit ID']), self::ERROR_TYPE_GENERAL);

		if (!$this->ProjektarbeitModel->hasBerechtigungForProjektarbeit($projektarbeit_id))
			return $this->_outputAuthError([$this->router->method => ['admin:rw', 'assistenz:rw']]);

		$projektbetreuer = $this->input->post('projektbetreuerListe');

		//$this->addMeta('form', $projektbetreuer);

		if (!is_array($projektbetreuer))
		{
			return $this->terminateWithError(
				$this->p->t('projektarbeit', 'error_invalidProjektbetreuer'), self::ERROR_TYPE_GENERAL
			);
		}

		foreach ($projektbetreuer as $pb)
		{
			if ($this->_validate($pb) == false)
			{
				$this->addMeta('test', 'foisch');
				$this->terminateWithValidationErrors($this->form_validation->error_array());
			}
		}

		$result = null;

		foreach ($projektbetreuer as $pb)
		{
		//~ $this->addMeta('form', $pb);
		//~ error_log(print_r($pb, true));
		//die();

			//~ $this->ProjektbetreuerModel->addSelect('1');
			//~ $checkResult = $this->ProjektbetreuerModel->loadWhere(
				//~ ['person_id' => $pb['person_id'], 'projektarbeit_id' => $projektarbeit_id, 'betreuerart_kurzbz' => $pb['betreuerart_kurzbz']]
			//~ );

			//~ if (isError($checkResult)) $this->terminateWithError(getError($checkResult), self::ERROR_TYPE_GENERAL);

			$betreuer = [
				'projektarbeit_id' => $projektarbeit_id,
				'person_id' => $pb['person_id'],
				'note' => $pb['note'],
				'stunden' => $pb['stunden'],
				'stundensatz' => $pb['stundensatz'],
				'betreuerart_kurzbz' => $pb['betreuerart_kurzbz']
			];

			if (isset($pb['person_id_old']) && isset($pb['betreuerart_kurzbz_old']))
			{
				$result = $this->ProjektbetreuerModel->update(
					[
						'projektarbeit_id' => $projektarbeit_id,
						'person_id' => $pb['person_id_old'],
						'betreuerart_kurzbz' => $pb['betreuerart_kurzbz_old']
					],
					array_merge($betreuer, ['updateamum' => date('c'), 'updatevon' => getAuthUID()])
				);
			}
			else
			{
				$result = $this->ProjektbetreuerModel->insert(
					array_merge($betreuer, ['insertamum' => date('c'), 'insertvon' => getAuthUID()])
				);
			}

			if (isError($result)) $this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
		}

		$this->terminateWithSuccess(hasData($result) ? getData($result) : []);
	}

	public function deleteProjektbetreuer()
	{
		$projektarbeit_id = $this->input->post('projektarbeit_id');
		$person_id = $this->input->post('person_id');
		$betreuerart_kurzbz = $this->input->post('betreuerart_kurzbz');

		if (!isset($projektarbeit_id) || !is_numeric($projektarbeit_id))
			return $this->terminateWithError($this->p->t('ui', 'error_missingId', ['id'=> 'Projektbetreuer ID'], self::ERROR_TYPE_GENERAL));

		if (!isset($person_id) || !is_numeric($person_id))
			return $this->terminateWithError($this->p->t('ui', 'error_missingId', ['id'=> 'Person ID'], self::ERROR_TYPE_GENERAL));

		if (!isset($betreuerart_kurzbz))
			return $this->terminateWithError($this->p->t('ui', 'error_missingId', ['id'=> 'Betreuerart'], self::ERROR_TYPE_GENERAL));

		if (!$this->ProjektarbeitModel->hasBerechtigungForProjektarbeit($projektarbeit_id))
			return $this->_outputAuthError([$this->router->method => ['admin:rw', 'assistenz:rw']]);

		$validate = $this->_validateDelete($projektarbeit_id, $person_id);

		if (isError($validate)) return $this->terminateWithError(getError($validate), self::ERROR_TYPE_GENERAL);

		$result = $this->ProjektbetreuerModel->delete(
			['projektarbeit_id' => $projektarbeit_id, 'person_id' => $person_id, 'betreuerart_kurzbz' => $betreuerart_kurzbz]
		);

		if (isError($result)) return $this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);

		if (!hasData($result))
		{
			$this->outputJson($result);
		}

		return $this->terminateWithSuccess(current(getData($result)) ? : null);
	}

	public function getBetreuerarten()
	{
		$result = $this->BetreuerartModel->loadWhere(['aktiv' => true]);

		if (isError($result)) return $this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);

		return $this->terminateWithSuccess(hasData($result) ? getData($result) : []);
	}

	public function getNoten()
	{
		$result = $this->NoteModel->load();

		if (isError($result)) return $this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);

		return $this->terminateWithSuccess(hasData($result) ? getData($result) : []);
	}

	public function getDefaultStundensaetze()
	{
		$person_id = $this->input->get('person_id');
		$studiensemester_kurzbz = $this->input->get('studiensemester_kurzbz');

		$result = $this->StundensatzModel->getStundensatzForMitarbeiter($person_id, $studiensemester_kurzbz);

		$this->addMeta('res', $result);

		//if (isError($result)) return $this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);

		return $this->terminateWithSuccess($result);
	}

	public function getProjektbetreuerBySearchQuery()
	{
		$searchString = $this->input->get('searchString');

		if (!isset($searchString)) $this->terminateWithError($this->p->t('projektarbeit', 'error_searchStringMissing', self::ERROR_TYPE_GENERAL));

		$result = $this->PersonModel->searchPerson($searchString);

		$this->addMeta('met', $result);

		if (isError($result)) return $this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);

		return $this->terminateWithSuccess(hasData($result) ? $this->_addFullNameToBetreuer(getData($result)) : []);
	}

	/**
	 *
	 * @param
	 * @return object success or error
	 */
	public function validateProjektbetreuer()
	{
		$projektbetreuerArr = $this->input->post('projektbetreuer');

		if (!is_array($projektbetreuerArr)) $projektbetreuerArr = [$projektbetreuerArr];

		foreach ($projektbetreuerArr as $pb)
		{
			if ($this->_validate($pb) == false)
			{
				$this->terminateWithValidationErrors($this->form_validation->error_array());
			}
		}

		$this->terminateWithSuccess([]);
	}

	/**
	 *
	 * @param
	 * @return object success or error
	 */
	private function _validate($formData)
	{
		$this->form_validation->set_data($formData);

		$this->form_validation->set_rules('betreuerart_kurzbz', 'Betreuerart', 'required', [
			'required' => $this->p->t('ui', 'error_fieldRequired', ['field' => 'Betreuerart'])
		]);

		$this->form_validation->set_rules('person_id', 'Person', 'required', [
			'required' => $this->p->t('ui', 'error_fieldRequired', ['field' => 'Person ID'])
		]);

		$this->form_validation->set_rules('stunden', 'Stunden', 'numeric', [
			'numeric' => $this->p->t('ui', 'error_fieldNotNumeric', ['field' => 'Stunden'])
		]);

		$this->form_validation->set_rules('stundensatz', 'Stundensatz', 'numeric', [
			'numeric' => $this->p->t('ui', 'error_fieldNotNumeric', ['field' => 'Stundensatz'])
		]);


		return $this->form_validation->run();
	}

	/**
	 *
	 * @param
	 * @return object success or error
	 */
	private function _validateDelete($projektarbeit_id, $person_id)
	{
		$this->ProjektbetreuerModel->addSelect('vertrag_id');
		$result = $this->ProjektbetreuerModel->loadWhere(['projektarbeit_id' => $projektarbeit_id, 'person_id' => $person_id]);

		if (isError($result)) return $result;

		if (hasData($result) && getData($result)[0]->vertrag_id != null) return error($this->p->t('projektarbeit', 'error_betreuerHatVertrag'));

		return success();
	}

	/**
	 *
	 * @param
	 * @return object success or error
	 */
	private function _addFullNameToBetreuer($betreuerArr)
	{
		foreach ($betreuerArr as $betreuer)
		{
			$betreuer->name = ($betreuer->titelpre ? $betreuer->titelpre . ' ' : '') .
				$betreuer->nachname . ' ' . $betreuer->vorname . ($betreuer->titelpost ? ' ' . $betreuer->titelpre : '').
				' (' . $betreuer->status . ')';
		}

		return $betreuerArr;
	}
}
