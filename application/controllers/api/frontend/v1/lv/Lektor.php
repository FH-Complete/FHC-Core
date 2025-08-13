<?php
if (!defined('BASEPATH'))
	exit('No direct script access allowed');

class Lektor extends FHCAPI_Controller
{
	private $_uid;
	private $_ci;
	public function __construct()
	{
		parent::__construct([
			'add' => ['admin:rw', 'assistenz:rw'],
			'update' => ['admin:rw', 'assistenz:rw'],
			'cancelVertrag' => ['admin:rw', 'assistenz:rw'],
			'deleteLVPlan' => ['admin:rw', 'assistenz:rw'],
			'deletePerson' => ['admin:rw', 'assistenz:rw'],
			'getLehrfunktionen' => ['admin:r', 'assistenz:r'],
			'getLektoren' => ['admin:r', 'assistenz:r'],
			'getLektorenByLE' => ['admin:r', 'assistenz:r'],
			'getLektorDaten' => ['admin:r', 'assistenz:r'],
			'getLektorVertrag' => ['admin:r', 'assistenz:r'],

		]);

		$this->_ci = &get_instance();
		$this->_setAuthUID();
		$this->_ci->load->library('VariableLib', ['uid' => $this->_uid]);
		$this->_ci->load->library('PermissionLib');
		$this->_ci->load->library('LektorLib');
		$this->_ci->load->library('form_validation');
		$this->loadPhrases([
			'ui'
		]);

		$this->_ci->load->model('accounting/Vertrag_model', 'VertragModel');
		$this->_ci->load->model('ressource/Mitarbeiter_model', 'MitarbeiterModel');
		$this->_ci->load->model('education/lehreinheit_model', 'LehreinheitModel');
		$this->_ci->load->model('education/Lehreinheitmitarbeiter_model', 'LehreinheitmitarbeiterModel');
		$this->_ci->load->model('ressource/stundenplandev_model', 'StundenplandevModel');
		$this->_ci->load->model('ressource/Stundensatz_model', 'StundensatzModel');

	}

	private function checkMitarbeiter($mitarbeiter_uid)
	{
		if (is_null($mitarbeiter_uid))
			$this->terminateWithError($this->p->t('ui', 'ungueltigeParameter'), self::ERROR_TYPE_GENERAL);

		$mitarbeiter_result = $this->_ci->MitarbeiterModel->load($mitarbeiter_uid);

		if (!hasData($mitarbeiter_result) || isError($mitarbeiter_result))
			$this->terminateWithError($this->p->t('ui', 'ungueltigeParameter'), self::ERROR_TYPE_GENERAL);
	}

	public function add()
	{
		$lehreinheit_id = $this->input->post('lehreinheit_id');
		$mitarbeiter_uid = $this->input->post('mitarbeiter_uid');

		$this->checkLehreinheit($lehreinheit_id);
		$this->checkMitarbeiter($mitarbeiter_uid);
		$lehrfach_permission = $this->checkLehrfachPermission($lehreinheit_id, array('assistenz', 'admin'));
		$lehreinheit_permission = $this->checkPermission($lehreinheit_id, array('admin', 'assistenz', 'lv-plan'));

		if (!$lehrfach_permission && !$lehreinheit_permission)
			$this->terminateWithError($this->p->t('ui', 'error_fieldWriteAccess'));

		$result = $this->_ci->lektorlib->addLektorToLehreinheit($lehreinheit_id, $mitarbeiter_uid);

		if (isError($result)) $this->terminateWithError(getError($result));

		$this->terminateWithSuccess("Erfolgreich gespeichert");
	}

	public function update()
	{
		$formData = $this->input->post('formData');
		$lehreinheit_id = $this->input->post('lehreinheit_id');
		$mitarbeiter_uid = $this->input->post('mitarbeiter_uid');

		$this->checkLehreinheit($lehreinheit_id);
		$this->checkMitarbeiter($mitarbeiter_uid);

		$updatableFields = array(
			'lehrfunktion_kurzbz',
			'planstunden',
			'stundensatz',
			'faktor',
			'anmerkung',
			'bismelden',
			'semesterstunden',
			'mitarbeiter_uid'
		);

		$this->form_validation->set_data($formData);

		foreach ($updatableFields as $field)
		{
			if (array_key_exists($field, $formData))
			{
				switch ($field)
				{
					case 'lehrfunktion_kurzbz':
						$this->form_validation->set_rules($field, 'Lehrfunktion', 'required|max_length[16]');
						break;
					case 'planstunden':
						$this->form_validation->set_rules($field, 'Planstunden', 'integer|greater_than_equal_to[0]');
						break;
					case 'stundensatz':
						$this->form_validation->set_rules($field, 'Stundensatz', 'numeric|greater_than_equal_to[0]');
						break;
					case 'faktor':
						$this->form_validation->set_rules($field, 'Faktor', 'numeric|greater_than_equal_to[0]');
						break;
					case 'anmerkung':
						$this->form_validation->set_rules($field, 'Anmerkung', 'max_length[256]');
						break;
					case 'bismelden':
						$this->form_validation->set_rules($field, 'Bis Melden', 'trim');
						break;
					case 'semesterstunden':
						$this->form_validation->set_rules($field, 'Semesterstunden', 'callback__check_semesterstunden');
						break;
					case 'mitarbeiter_uid':
						$this->form_validation->set_rules($field, 'Semesterstunden', 'required|max_length[32]');
						break;
				}
			}
		}
		if (!$this->form_validation->run())
		{
			$this->terminateWithError($this->form_validation->error_array());
		}

		if (isset($formData['semesterstunden']) && (!is_numeric($formData['semesterstunden']) || $formData['semesterstunden'] === ''))
		{
			$formData['semesterstunden'] = null;
		}

		$lehreinheit_permission = $this->checkPermission($lehreinheit_id, array('admin', 'assistenz', 'lv-plan'));

		if (!$lehreinheit_permission)
			$this->terminateWithError($this->p->t('ui', 'error_fieldWriteAccess'));

		$result = $this->_ci->lektorlib->updateLektorFromLehreinheit($lehreinheit_id, $mitarbeiter_uid, $formData);

		if (isError($result)) $this->terminateWithError(getError($result));
		$this->terminateWithSuccess("Erfolgreich geupdated");
	}

	public function _check_semesterstunden($value)
	{
		if ($value === null || $value === '') {
			return true;
		}

		if (!is_numeric($value))
		{
			$this->form_validation->set_message(
				'_check_semesterstunden',
				'Das Feld {field} muss eine Zahl sein.'
			);
			return false;
		}

		if ($value < 0)
		{
			$this->form_validation->set_message(
				'_check_semesterstunden',
				'Das Feld {field} muss eine Zahl größer oder gleich 0 sein.'
			);
			return false;
		}

		return true;
	}
	public function getLehrfunktionen()
	{
		$this->_ci->load->model('education/Lehrfunktion_model', 'LehrfunktionModel');
		$this->_ci->LehrfunktionModel->addOrder('lehrfunktion_kurzbz');
		$this->terminateWithSuccess(getData($this->_ci->LehrfunktionModel->load()));
	}

	public function getLektoren()
	{
		$this->_ci->MitarbeiterModel->addSelect('uid, person_id, vorname, nachname');
		$this->_ci->MitarbeiterModel->addJoin('public.tbl_benutzer', 'uid = mitarbeiter_uid');
		$this->_ci->MitarbeiterModel->addJoin('public.tbl_person', 'person_id');
		$this->terminateWithSuccess(getData($this->_ci->MitarbeiterModel->loadWhere(array('public.tbl_benutzer.aktiv' => true))));
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
	public function getLektorenByLE($lehreinheit_id = null)
	{
		$this->checkLehreinheit($lehreinheit_id);
		$le_mitarbeiter_data = $this->_ci->LehreinheitmitarbeiterModel->getLektorenByLe($lehreinheit_id);
		$this->terminateWithSuccess(hasData($le_mitarbeiter_data) ? getData($le_mitarbeiter_data) : array());
	}

	public function getLektorDaten($lehreinheit_id = null, $mitarbeiter_uid = null)
	{
		$lehreinheit = $this->checkLehreinheit($lehreinheit_id);

		if (is_null($mitarbeiter_uid))
			$this->terminateWithError( $this->p->t('ui', 'ungueltigeParameter'), self::ERROR_TYPE_GENERAL);

		$mitarbeiter_result = $this->_ci->MitarbeiterModel->load($mitarbeiter_uid);

		if (!hasData($mitarbeiter_result) || isError($mitarbeiter_result))
			$this->terminateWithError( $this->p->t('ui', 'ungueltigeParameter'), self::ERROR_TYPE_GENERAL);

		$this->load->model('organisation/Studiensemester_model','StudiensemesterModel');
		$studiensemester_result = $this->_ci->StudiensemesterModel->loadWhere(array('studiensemester_kurzbz' => $lehreinheit->studiensemester_kurzbz));
		$studiensemester = getData($studiensemester_result)[0];

		$defaultStundensatz = $this->_ci->StundensatzModel->getDefaultStundensatz($mitarbeiter_uid, $studiensemester->start, $studiensemester->ende, 'lehre');

		$le_mitarbeiter_result = $this->_ci->LehreinheitmitarbeiterModel->getByLeLektor($lehreinheit_id, $mitarbeiter_uid);

		$le_mitarbeiter_data = array();
		if (hasData($le_mitarbeiter_result))
		{
			$le_mitarbeiter_data = getData($le_mitarbeiter_result)[0];
			$le_mitarbeiter_data->default_stundensatz = $defaultStundensatz;
		}
		$vertrag = $this->getLektorVertrag($lehreinheit_id, $mitarbeiter_uid);
		$le_mitarbeiter_data->vertrag = $vertrag;
		$this->terminateWithSuccess($le_mitarbeiter_data);
	}

	private function getLektorVertrag($lehreinheit_id = null, $mitarbeiter_uid = null)
	{
		$this->_ci->load->model('accounting/Vertrag_model', 'VertragModel');
		$vertrag = $this->_ci->VertragModel->getVertrag($mitarbeiter_uid, $lehreinheit_id);
		return hasData($vertrag) ? getData($vertrag)[0] : null;
	}

	private function checkLehrfachPermission($lehreinheit_id, $permissions)
	{
		$lehrfach_oe_kurzbz = $this->_ci->LehreinheitModel->getLehrfachOe($lehreinheit_id);

		if (isError($lehrfach_oe_kurzbz))
			$this->terminateWithError(getError($lehrfach_oe_kurzbz), self::ERROR_TYPE_GENERAL);

		$lehrfach_oe_kurzbz = array('');
		if (hasData($lehrfach_oe_kurzbz))
			$lehrfach_oe_kurzbz = array_column(getData($lehrfach_oe_kurzbz), 'oe_kurzbz');


		return $this->checkPermissionGenerel($permissions, $lehrfach_oe_kurzbz);
	}

	private function checkPermissionGenerel($permissions, $oe_array)
	{
		$hasPermission = false;
		foreach ($permissions as $permission)
		{
			if ($this->_ci->permissionlib->isBerechtigtMultipleOe($permission, $oe_array, 'suid'))
			{
				$hasPermission = true;
				break;
			}
		}

		return $hasPermission;
	}

	private function checkPermission($lehreinheit_id, $permissions)
	{
		$result = $this->_ci->LehreinheitModel->getOes($lehreinheit_id);

		if (isError($result))
			$this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);

		$oe_array = [];
		if (hasData($result))
			$oe_array = getData($result);

		return $this->checkPermissionGenerel($permissions, $oe_array);
	}
	public function cancelVertrag()
	{
		$vertrag_id = $this->input->post('vertrag_id');
		$lehreinheit_id = $this->input->post('lehreinheit_id');
		$mitarbeiter_uid = $this->input->post('mitarbeiter_uid');

		$this->checkLehreinheit($lehreinheit_id);
		$this->checkPermission($lehreinheit_id, array('admin', 'lehre/lehrauftrag_bestellen'));

		if (is_null($vertrag_id) || !ctype_digit((string)$vertrag_id))
			$this->terminateWithError( $this->p->t('ui', 'ungueltigeParameter'), self::ERROR_TYPE_GENERAL);

		$vertrag_result = $this->_ci->VertragModel->load($vertrag_id);

		if (!hasData($vertrag_result) || isError($vertrag_result))
			$this->terminateWithError( $this->p->t('ui', 'ungueltigeParameter'), self::ERROR_TYPE_GENERAL);

		if (is_null($mitarbeiter_uid))
			$this->terminateWithError( $this->p->t('ui', 'ungueltigeParameter'), self::ERROR_TYPE_GENERAL);

		$mitarbeiter_result = $this->_ci->MitarbeiterModel->load($mitarbeiter_uid);

		if (!hasData($mitarbeiter_result) || isError($mitarbeiter_result))
			$this->terminateWithError( $this->p->t('ui', 'ungueltigeParameter'), self::ERROR_TYPE_GENERAL);

		$result = $this->_ci->VertragModel->cancelVertrag($vertrag_id, $mitarbeiter_uid);

		if (isError($result))
			$this->terminateWithError(getError($result));

		$this->terminateWithSuccess($result);
	}

	public function deletePerson()
	{
		$lehreinheit_id = $this->input->post('lehreinheit_id');
		$mitarbeiter_uid = $this->input->post('mitarbeiter_uid');

		$this->checkLehreinheit($lehreinheit_id);
		$this->checkPermission($lehreinheit_id, array('admin', 'assistenz', 'lv-plan'));

		if (is_null($mitarbeiter_uid))
			$this->terminateWithError($this->p->t('ui', 'ungueltigeParameter'), self::ERROR_TYPE_GENERAL);

		$mitarbeiter_result = $this->_ci->MitarbeiterModel->load($mitarbeiter_uid);

		if (!hasData($mitarbeiter_result) || isError($mitarbeiter_result))
			$this->terminateWithError($this->p->t('ui', 'ungueltigeParameter'), self::ERROR_TYPE_GENERAL);

		$delete_result =$this->_ci->LehreinheitmitarbeiterModel->deleteLektorFromLe($lehreinheit_id, $mitarbeiter_uid);

		if (isError($delete_result))
			$this->terminateWithError(getError($delete_result));

		$this->terminateWithSuccess($delete_result);
	}

	public function deleteLVPlan()
	{
		$lehreinheit_id = $this->input->post('lehreinheit_id');
		$mitarbeiter_uid = $this->input->post('mitarbeiter_uid');

		$this->checkLehreinheit($lehreinheit_id);
		$this->checkPermission($lehreinheit_id, array('lv-plan/lektorentfernen'));

		if (is_null($mitarbeiter_uid))
			$this->terminateWithError($this->p->t('ui', 'ungueltigeParameter'), self::ERROR_TYPE_GENERAL);

		$mitarbeiter_result = $this->_ci->MitarbeiterModel->load($mitarbeiter_uid);

		if (!hasData($mitarbeiter_result) || isError($mitarbeiter_result))
			$this->terminateWithError($this->p->t('ui', 'ungueltigeParameter'), self::ERROR_TYPE_GENERAL);


		$delete_result = $this->_ci->StundenplandevModel->deleteLektorPlanning($lehreinheit_id, $mitarbeiter_uid);

		if (isError($delete_result))
			$this->terminateWithError(getError($delete_result));

		$this->terminateWithSuccess($delete_result);
	}

	private function _setAuthUID()
	{
		$this->_uid = getAuthUID();

		if (!$this->_uid)
			show_error('User authentification failed');
	}
}
