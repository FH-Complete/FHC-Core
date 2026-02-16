<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

class Vertrag extends FHCAPI_Controller
{
	public function __construct()
	{
		parent::__construct([
			'getVertrag' => ['admin:r', 'assistenz:r'],
			'cancelVertrag' => ['admin:r', 'assistenz:r']
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
		$this->load->model('accounting/Vertrag_model', 'VertragModel');
		$this->load->model('education/Lehrveranstaltung_model', 'LehrveranstaltungModel');
		$this->load->model('person/Benutzer_model', 'BenutzerModel');

		// load libraries
		$this->load->library('PermissionLib');
	}

	public function getVertrag()
	{
		$vertrag_id = $this->input->get('vertrag_id');

		if (!isset($vertrag_id) || !is_numeric($vertrag_id))
			$this->terminateWithError($this->p->t('ui', 'error_missingId', ['id'=> 'Vertrag ID']), self::ERROR_TYPE_GENERAL);

		$result = $this->VertragModel->getVertragById($vertrag_id);

		if (isError($result))
		{
			$this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
		}

		if (!hasData($result)) $this->terminateWithSuccess([]);

		$vertrag = getData($result)[0];

		$this->terminateWithSuccess($vertrag);
	}

	public function cancelVertrag()
	{
		$vertrag_id = $this->input->post('vertrag_id');
		$person_id = $this->input->post('person_id');

		if (!isset($vertrag_id) || !is_numeric($vertrag_id))
			$this->terminateWithError($this->p->t('ui', 'error_missingId', ['id'=> 'Vertrag ID']), self::ERROR_TYPE_GENERAL);
		if (!isset($person_id) || !is_numeric($person_id))
			$this->terminateWithError($this->p->t('ui', 'error_missingId', ['id'=> 'Person ID']), self::ERROR_TYPE_GENERAL);

		// * first find lehrveranstaltung_id of the contracts lehrveranstaltung
		$this->VertragModel->addSelect('lehrveranstaltung_id');
		$this->VertragModel->addJoin('lehre.tbl_lehrveranstaltung', 'lehrveranstaltung_id', 'LEFT');
		$result = $this->VertragModel->loadWhere(['vertrag_id' => $vertrag_id]);

		if (isError($result)) $this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);

		if (!hasData($result)) $this->terminateWithSuccess([]);

		$lehrveranstaltung_id = getData($result)[0]->lehrveranstaltung_id;

		$allOe = $this->LehrveranstaltungModel->getAllOe($lehrveranstaltung_id);

		if (isError($allOe)) $this->terminateWithError(getError($allOe), self::ERROR_TYPE_GENERAL);

		$allOe = hasData($allOe) ? getData($allOe) : [];

		$this->addMeta('oe', $allOe);

		// * then check if the user has permissions to cancel the corresponding lv-organisational units
		if (!$this->permissionlib->isBerechtigtMultipleOe('admin', $allOe, 'suid') &&
			!$this->permissionlib->isBerechtigtMultipleOe('lehre/lehrauftrag_bestellen', $allOe, 'suid'))
		{
			return $this->_outputAuthError([$this->router->method => ['admin:rw', 'lehrauftrag_bestellen:rw']]);
		}

		$uidResult = $this->BenutzerModel->getFromPersonId($person_id);

		if (isError($uidResult)) $this->terminateWithError(getError($uidResult), self::ERROR_TYPE_GENERAL);

		if (!hasData($uidResult)) $this->terminateWithError("no user found", self::ERROR_TYPE_GENERAL);

		$mitarbeiter_uid = getData($uidResult)[0]->uid;

		$result = $this->VertragModel->cancelVertrag($vertrag_id, $mitarbeiter_uid);

		$data = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess($data);
	}
}
