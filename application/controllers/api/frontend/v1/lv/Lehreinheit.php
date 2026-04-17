<?php
if (!defined('BASEPATH'))
	exit('No direct script access allowed');

class Lehreinheit extends FHCAPI_Controller
{
	private $_uid;
	private $_ci;
	public function __construct()
	{
		parent::__construct([
			'add' => ['admin:rw',  'assistenz:rw'],
			'copy' => ['admin:rw',  'assistenz:rw'],
			'delete' => ['admin:rw',  'assistenz:rw'],
			'update' => ['admin:rw',  'assistenz:rw'],

			'get' =>  ['admin:r', 'assistenz:r'],
			'getStudiensemester' => ['admin:r', 'assistenz:r'],
			'getLehrfach' => ['admin:r', 'assistenz:r'],
			'getSprache' => ['admin:r', 'assistenz:r'],
			'getRaumtyp' => ['admin:r', 'assistenz:r'],
			'getLehrform' => ['admin:r', 'assistenz:r']
		]);

		$this->_ci = &get_instance();
		$this->_setAuthUID();
		$this->_ci->load->library('VariableLib', ['uid' => $this->_uid]);
		$this->_ci->load->library('PhrasesLib');
		$this->loadPhrases(
			array(
				'global',
				'ui'
			)
		);

		$this->_ci->load->model('education/Lehreinheit_model', 'LehreinheitModel');
		$this->_ci->load->model('education/Lehreinheitgruppe_model', 'LehreinheitgruppeModel');
		$this->_ci->load->model('education/Lehreinheitmitarbeiter_model', 'LehreinheitmitarbeiterModel');
	}

	public function get($lehreinheit_id)
	{
		$lehreinheit = $this->checkLehreinheit($lehreinheit_id);
		$lehreinheit->lehrfaecher = $this->getLehrfaecher($lehreinheit);
		$this->terminateWithSuccess($lehreinheit);
	}

	private function getLehrfaecher($lehreinheit)
	{
		$lehrfacher_array = array($lehreinheit->lehrfach_id);
		$this->_ci->LehreinheitModel->addSelect('lehrveranstaltung_id_kompatibel');
		$this->_ci->LehreinheitModel->addJoin('lehre.tbl_lehrveranstaltung_kompatibel', 'lehrveranstaltung_id');
		$lehrfaecher = $this->_ci->LehreinheitModel->loadWhere(array('lehrveranstaltung_id' => $lehreinheit->lehrveranstaltung_id));


		if (hasData($lehrfaecher))
			$lehrfaecher_array = array_merge($lehrfacher_array, array_column(getData($lehrfaecher), 'lehrveranstaltung_id_kompatibel'));

		$lehrfaecher_array[] = $lehreinheit->lehrveranstaltung_id;

		$this->_ci->LehrveranstaltungModel->addDistinct('lehrfach_id');
		$this->_ci->LehrveranstaltungModel->addSelect("tbl_lehrveranstaltung.lehrveranstaltung_id, CONCAT(tbl_lehrveranstaltung.bezeichnung || '(' || tbl_lehrveranstaltung.oe_kurzbz || ')') as lehrfach");
		$this->_ci->LehrveranstaltungModel->db->where_in('tbl_lehrveranstaltung.lehrveranstaltung_id', $lehrfaecher_array);
		$lehrfaecher_result = $this->_ci->LehrveranstaltungModel->load();

		return hasData($lehrfaecher_result) ? getData($lehrfaecher_result) : array();
	}

	public function add()
	{
		$lehrveranstaltung_id = $this->input->post('lehrveranstaltung_id');

		if (is_null($lehrveranstaltung_id) || !ctype_digit((string)$lehrveranstaltung_id))
			$this->terminateWithError( $this->p->t('ui', 'ungueltigeParameter'), self::ERROR_TYPE_GENERAL);

		$lehrveranstaltung_result = $this->_ci->LehrveranstaltungModel->loadWhere(array('lehrveranstaltung_id' => $lehrveranstaltung_id));

		if (!hasData($lehrveranstaltung_result) || isError($lehrveranstaltung_result))
			$this->terminateWithError( $this->p->t('ui', 'ungueltigeParameter'), self::ERROR_TYPE_GENERAL);

		$lehrveranstaltung = getData($lehrveranstaltung_result)[0];

		$oe_result = $this->_ci->LehrveranstaltungModel->getAllOe($lehrveranstaltung->lehrveranstaltung_id);
		$oe_array = hasData($oe_result) ? array_column(getData($oe_result), 'oe_kurzbz') : array();

		if (!$this->_ci->permissionlib->isBerechtigtMultipleOe('admin', $oe_array, 'suid') &&
			!$this->_ci->permissionlib->isBerechtigtMultipleOe('assistenz', $oe_array, 'suid') &&
			!$this->_ci->permissionlib->isBerechtigtMultipleOe('lv-plan', $oe_array, 'suid'))
			$this->terminateWithError($this->p->t('ui', 'error_fieldWriteAccess'));

		$this->_ci->load->library('form_validation');

		$updatableFields = array(
			'lehrveranstaltung_id',
			'studiensemester_kurzbz',
			'lehrfach_id',
			'lehrform_kurzbz',
			'stundenblockung',
			'wochenrythmus',
			'gewicht',
			'start_kw',
			'raumtyp',
			'raumtypalternativ',
			'sprache',
			'lehre',
			'anmerkung',
			'lvnr',
			'unr',
		);

		foreach ($updatableFields as $field)
		{
			switch ($field) {
				case 'lehrveranstaltung_id':
					$this->form_validation->set_rules($field, 'Lehrveranstaltung ID', 'required|integer');
					break;
				case 'studiensemester_kurzbz':
					$this->form_validation->set_rules($field, 'Studiensemester', 'required|max_length[16]');
					break;
				case 'lehrfach_id':
					$this->form_validation->set_rules($field, 'Lehrfach ID', 'required|integer');
					break;
				case 'lehrform_kurzbz':
					$this->form_validation->set_rules($field, 'Lehrform', 'required|max_length[8]');
					break;
				case 'stundenblockung':
					$this->form_validation->set_rules($field, 'Stundenblockung', 'required|integer|greater_than_equal_to[0]');
					break;
				case 'wochenrythmus':
					$this->form_validation->set_rules($field, 'Wochenrhytmus', 'required|integer|greater_than_equal_to[0]');
					break;
				case 'start_kw':
					$this->form_validation->set_rules($field, 'Start KW', 'integer|greater_than[0]|less_than_equal_to[53]');
					break;
				case 'gewicht':
					$this->form_validation->set_rules($field, 'Gewicht', 'numeric');
					break;
				case 'raumtyp':
					$this->form_validation->set_rules($field, 'Raumtyp', 'required|max_length[16]');
					break;
				case 'raumtypalternativ':
					$this->form_validation->set_rules($field, 'Raumtyp Alternativ', 'required|max_length[16]');
					break;
				case 'sprache':
					$this->form_validation->set_rules($field, 'Sprache', 'required|max_length[16]');
					break;
				case 'lvnr':
					$this->form_validation->set_rules($field, 'LVNR', 'integer');
					break;
				case 'unr':
					$this->form_validation->set_rules($field, 'UNR', 'integer');
					break;
				case 'lehre':
					$this->form_validation->set_rules($field, 'Lehre', 'trim');
					break;
				case 'anmerkung':
					$this->form_validation->set_rules($field, 'Anmerkung', 'trim');
					break;
			}
		}

		if ($this->form_validation->run() === false)
		{
			$this->terminateWithValidationErrors($this->form_validation->error_array());
		}

		$updateData = array();
		foreach ($updatableFields as $field)
		{
			$value = $this->input->post($field);

			if ($field === 'lehre')
			{
				$value = (bool)$value;
			}
			if ($value !== null)
			{
				$updateData[$field] = $value;
			}
		}

		$updateData['insertvon'] = $this->_uid;
		$updateData['insertamum'] = date('Y-m-d H:i:s');

		$result = $this->_ci->LehreinheitModel->insert(
			$updateData
		);

		if (!isset($updateData['unr']))
		{
			$unr = getData($result);
			$this->_ci->LehreinheitModel->update($unr, array('unr' => $unr));
		}

		$this->terminateWithSuccess($result);
	}

	public function copy()
	{
		$lehreinheit_id = $this->input->post('lehreinheit_id');
		$art = $this->input->post('art');

		$lehreinheit_old = $this->checkLehreinheit($lehreinheit_id);
		$this->checkPermission($lehreinheit_old->lehreinheit_id);

		$lehreinheit_new = $lehreinheit_old;

		$lehreinheit_new->unr = null;
		unset($lehreinheit_new->lehreinheit_id);
		$lehreinheit_new->updateamum = date('Y-m-d H:i:s');
		$lehreinheit_new->updatevon = $this->_uid;
		$lehreinheit_new->insertamum = date('Y-m-d H:i:s');
		$lehreinheit_new->insertvon = $this->_uid;

		$insert_result = $this->_ci->LehreinheitModel->insert($lehreinheit_new);

		if (isError($insert_result))
			$this->terminateWithError(getError($insert_result), self::ERROR_TYPE_GENERAL);

		$lehreinheit_id_new = getData($insert_result);

		$this->_ci->LehreinheitModel->update(array('lehreinheit_id' => $lehreinheit_id_new), array('unr' => $lehreinheit_id_new));
		if (in_array($art, array('gruppen', 'alle')))
		{
			$gruppen_result = $this->_ci->LehreinheitgruppeModel->loadWhere(array('lehreinheit_id' => $lehreinheit_id));

			if (isError($gruppen_result))
				$this->terminateWithError(getError($gruppen_result), self::ERROR_TYPE_GENERAL);

			if (hasData($gruppen_result))
			{
				$gruppen = getData($gruppen_result);

				foreach ($gruppen as $gruppe)
				{
					$gruppe_new = $gruppe;
					unset($gruppe_new->lehreinheitgruppe_id);
					$gruppe_new->lehreinheit_id = $lehreinheit_id_new;
					$gruppe_new->insertamum = date('Y-m-d H:i:s');
					$gruppe_new->insertvon = $this->_uid;
					$gruppe_new->updateamum = date('Y-m-d H:i:s');
					$gruppe_new->updatevon = $this->_uid;

					$gruppe_new_result = $this->_ci->LehreinheitgruppeModel->insert($gruppe_new);

					if (isError($gruppe_new_result))
						$this->terminateWithError(getError($gruppe_new_result), self::ERROR_TYPE_GENERAL);
				}
			}
		}

		if (in_array($art, array('lektoren', 'alle')))
		{
			$lektoren_result = $this->_ci->LehreinheitmitarbeiterModel->loadWhere(array('lehreinheit_id' => $lehreinheit_id));

			if (isError($lektoren_result))
				$this->terminateWithError(getError($lektoren_result), self::ERROR_TYPE_GENERAL);

			if (hasData($lektoren_result))
			{
				$lektoren = getData($lektoren_result);

				foreach ($lektoren as $lektor)
				{

					$lektor_new = $lektor;
					$lektor_new->lehreinheit_id = $lehreinheit_id_new;
					$lektor_new->insertamum = date('Y-m-d H:i:s');
					$lektor_new->insertvon = $this->_uid;
					$lektor_new->updateamum = date('Y-m-d H:i:s');
					$lektor_new->updatevon = $this->_uid;
					unset($lektor_new->vertrag_id);

					$lektor_new_result = $this->_ci->LehreinheitmitarbeiterModel->insert((array)$lektor_new);

					if (isError($lektor_new_result))
						$this->terminateWithError(getError($lektor_new_result), self::ERROR_TYPE_GENERAL);
				}
			}
		}

		$this->terminateWithSuccess("Erfolgeich gespeichert");
	}

	public function delete()
	{
		$lehreinheit_id = $this->input->post('lehreinheit_id');

		$errors = array();
		if (is_array($lehreinheit_id))
		{
			foreach ($lehreinheit_id as $le_id)
			{
				$lehreinheit = $this->checkLehreinheit($le_id);
				$this->checkPermission($lehreinheit->lehreinheit_id);

				$result = $this->_ci->LehreinheitModel->deleteLehreinheit($lehreinheit->lehreinheit_id);

				if (isError($result))
				{
					$errors[] = getError($result);
				}
			}
		}
		else
		{
			$lehreinheit = $this->checkLehreinheit($lehreinheit_id);
			$this->checkPermission($lehreinheit->lehreinheit_id);

			$result = $this->_ci->LehreinheitModel->deleteLehreinheit($lehreinheit->lehreinheit_id);

			if (isError($result))
				$this->terminateWithError(getError($result));
		}

		if (!isEmptyArray($errors))
		{
			if (count($errors) !== count($lehreinheit_id))
				$this->terminateWithSuccess(array('errors' => $errors));
			else
				$this->terminateWithError($errors);
		}
		else
			$this->terminateWithSuccess('Erfolgreich geloescht');
	}

	public function update()
	{
		$lehreinheit = $this->checkLehreinheit($this->input->post('lehreinheit_id'));

		$this->checkPermission($lehreinheit->lehreinheit_id);

		$this->_ci->load->library('form_validation');

		$formData = $this->input->post('formData');

		$updatableFields = array(
			'lehrveranstaltung_id',
			'studiensemester_kurzbz',
			'lehrfach_id',
			'lehrform_kurzbz',
			'stundenblockung',
			'wochenrythmus',
			'gewicht',
			'start_kw',
			'raumtyp',
			'raumtypalternativ',
			'sprache',
			'lehre',
			'anmerkung',
			'lvnr',
			'unr',
		);

		$this->form_validation->set_data($formData);

		foreach ($updatableFields as $field)
		{
			if (array_key_exists($field, $formData))
			{
				switch ($field)
				{
					case 'lehrveranstaltung_id':
						$this->form_validation->set_rules($field, 'Lehrveranstaltung ID', 'required|integer');
						break;
					case 'studiensemester_kurzbz':
						$this->form_validation->set_rules($field, 'Studiensemester', 'required|max_length[16]');
						break;
					case 'lehrfach_id':
						$this->form_validation->set_rules($field, 'Lehrfach ID', 'required|integer');
						break;
					case 'lehrform_kurzbz':
						$this->form_validation->set_rules($field, 'Lehrform', 'required|max_length[8]');
						break;
					case 'stundenblockung':
						$this->form_validation->set_rules($field, 'Stundenblockung', 'required|integer|greater_than_equal_to[0]');
						break;
					case 'wochenrythmus':
						$this->form_validation->set_rules($field, 'Wochenrhytmus', 'required|integer|greater_than_equal_to[0]');
						break;
					case 'start_kw':
						$this->form_validation->set_rules($field, 'Start KW', 'integer|greater_than[0]|less_than_equal_to[53]');
						break;
					case 'gewicht':
						$this->form_validation->set_rules($field, 'Gewicht', 'numeric|greater_than_equal_to[0]');
						break;
					case 'raumtyp':
						$this->form_validation->set_rules($field, 'Raumtyp', 'required|max_length[16]');
						break;
					case 'raumtypalternativ':
						$this->form_validation->set_rules($field, 'Raumtyp Alternativ', 'required|max_length[16]');
						break;
					case 'sprache':
						$this->form_validation->set_rules($field, 'Sprache', 'required|max_length[16]');
						break;
					case 'lvnr':
						$this->form_validation->set_rules($field, 'LVNR', 'integer');
						break;
					case 'unr':
						$this->form_validation->set_rules($field, 'UNR', 'integer|greater_than_equal_to[0]');
						break;
					case 'lehre':
						$this->form_validation->set_rules($field, 'Lehre', 'trim');
						break;
					case 'anmerkung':
						$this->form_validation->set_rules($field, 'Anmerkung', 'trim');
						break;
				}
			}
		}

		if ($this->form_validation->run() === false)
		{
			$this->terminateWithValidationErrors($this->form_validation->error_array());
		}

		$updateData = [];
		foreach ($updatableFields as $field)
		{
			if (array_key_exists($field, $formData))
			{
				$updateData[$field] = $formData[$field];
			}
		}


		$updateData['updatevon'] = $this->_uid;
		$updateData['updateamum'] = date('Y-m-d H:i:s');
		$result = $this->_ci->LehreinheitModel->update(
			[
				'lehreinheit_id' => $this->input->post('lehreinheit_id'),
			],
			$updateData
		);

		if (isError($result))
			$this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
		$this->terminateWithSuccess($this->p->t('global', 'gespeichert'));
	}


	private function checkPermission($lehreinheit_id)
	{
		$result = $this->_ci->LehreinheitModel->getOes($lehreinheit_id);

		if (isError($result))
			$this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);

		$oe_array = [];
		if (hasData($result))
			$oe_array = getData($result);

		if (!$this->_ci->permissionlib->isBerechtigtMultipleOe('admin', $oe_array, 'suid') &&
			!$this->_ci->permissionlib->isBerechtigtMultipleOe('assistenz', $oe_array, 'suid') &&
			!$this->_ci->permissionlib->isBerechtigtMultipleOe('lv-plan', $oe_array, 'suid'))
			$this->terminateWithError($this->p->t('ui', 'error_fieldWriteAccess'));
	}
	private function checkLehreinheit($lehreinheit_id)
	{
		if (is_null($lehreinheit_id) || !ctype_digit((string)$lehreinheit_id))
			$this->terminateWithError( $this->p->t('ui', 'ungueltigeParameter'), self::ERROR_TYPE_GENERAL);

		$lehreinheit_result = $this->_ci->LehreinheitModel->load($lehreinheit_id);

		if (!hasData($lehreinheit_result) || isError($lehreinheit_result))
			$this->terminateWithError( $this->p->t('ui', 'ungueltigeParameter'), self::ERROR_TYPE_GENERAL);

		return getData($lehreinheit_result)[0];
	}

	private function _setAuthUID()
	{
		$this->_uid = getAuthUID();

		if (!$this->_uid)
			show_error('User authentification failed');
	}
}
