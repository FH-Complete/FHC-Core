<?php
if (!defined('BASEPATH'))
	exit('No direct script access allowed');

class Gruppe extends FHCAPI_Controller
{
	private $_uid;
	private $_ci;
	public function __construct()
	{
		parent::__construct([
			'add' => ['admin:rw', 'assistenz:rw'],
			'delete' => ['admin:rw', 'assistenz:rw'],
			'deleteFromLVPlan' => ['admin:rw', 'assistenz:rw'],
			'getBenutzerSearch' => ['admin:r', 'assistenz:r'],
			'getAllSearch' => ['admin:r', 'assistenz:r'],
			'getByLehreinheit' => ['admin:r', 'assistenz:r'],
		]);

		$this->_ci = &get_instance();
		$this->_setAuthUID();
		$this->_ci->load->library('PhrasesLib');
		$this->loadPhrases(
			array(
				'ui',
				'lehre'
			)
		);

		$this->_ci->load->model('organisation/Gruppe_model', 'GruppeModel');
		$this->_ci->load->model('organisation/Lehrverband_model', 'LehrverbandModel');
		$this->_ci->load->model('education/Lehreinheitgruppe_model', 'LehreinheitgruppeModel');
		$this->_ci->load->model('person/Person_model', 'PersonModel');
		$this->_ci->load->model('ressource/stundenplandev_model', 'StundenplandevModel');
	}

	public function delete()
	{
		$lehreinheitgruppe_id = $this->input->post('lehreinheitgruppe_id');
		$lehreinheit_id = $this->input->post('lehreinheit_id');

		if (is_null($lehreinheit_id) || !ctype_digit((string)$lehreinheit_id) || is_null($lehreinheitgruppe_id) || !ctype_digit((string)$lehreinheitgruppe_id))
			$this->terminateWithError( $this->p->t('ui', 'ungueltigeParameter'), self::ERROR_TYPE_GENERAL);

		$lehreinheitgruppe_result = $this->_ci->LehreinheitgruppeModel->loadWhere(array('lehreinheitgruppe_id' => $lehreinheitgruppe_id));
		if (!hasData($lehreinheitgruppe_result) || isError($lehreinheitgruppe_result))
			$this->terminateWithError( $this->p->t('ui', 'ungueltigeParameter'), self::ERROR_TYPE_GENERAL);

		$this->checkPermission($lehreinheit_id);

		$result = $this->_ci->LehreinheitgruppeModel->deleteGroup($lehreinheit_id, $lehreinheitgruppe_id);

		if (isError($result))
			$this->terminateWithError(getError($result));

		$this->terminateWithSuccess($result);
	}

	public function add()
	{
		$lehreinheit_id = $this->input->post('lehreinheit_id');
		$gid = $this->input->post('gid');
		$lehrverband = $this->input->post('lehrverband');

		if (is_null($lehreinheit_id) || !ctype_digit((string)$lehreinheit_id) || is_null($gid) || !ctype_digit((string)$gid) || is_null($lehrverband))
			$this->terminateWithError( $this->p->t('ui', 'ungueltigeParameter'), self::ERROR_TYPE_GENERAL);

		$this->checkPermission($lehreinheit_id);

		$result = $this->_ci->LehreinheitgruppeModel->addGroup($lehreinheit_id, $gid, !($lehrverband === 'false'));

		if (isError($result))
			$this->terminateWithError(getError($result));

		$this->terminateWithSuccess($result);
	}

	public function getByLehreinheit($lehreinheit_id = null)
	{
		if (is_null($lehreinheit_id) || !ctype_digit((string)$lehreinheit_id))
			$this->terminateWithError( $this->p->t('ui', 'ungueltigeParameter'), self::ERROR_TYPE_GENERAL);

		$this->checkPermission($lehreinheit_id);

		$gruppen = $this->_ci->LehreinheitgruppeModel->getByLehreinheit($lehreinheit_id);
		$this->terminateWithSuccess(hasData($gruppen) ? getData($gruppen) : array());
	}

	public function deleteFromLVPlan()
	{
		$lehreinheit_id = $this->input->post('lehreinheit_id');
		$lehreinheitgruppe_id = $this->input->post('lehreinheitgruppe_id');

		if (is_null($lehreinheit_id) || !ctype_digit((string)$lehreinheit_id) || is_null($lehreinheitgruppe_id) || !ctype_digit((string)$lehreinheitgruppe_id))
			$this->terminateWithError($this->p->t('ui', 'ungueltigeParameter'), self::ERROR_TYPE_GENERAL);

		$lehreinheitgruppe_result = $this->_ci->LehreinheitgruppeModel->loadWhere(array('lehreinheitgruppe_id' => $lehreinheitgruppe_id));
		if (!hasData($lehreinheitgruppe_result) || isError($lehreinheitgruppe_result))
			$this->terminateWithError($this->p->t('ui', 'ungueltigeParameter'), self::ERROR_TYPE_GENERAL);

		$this->checkPermission($lehreinheit_id);

		$result = $this->_ci->StundenplandevModel->deleteGroupPlanning($lehreinheit_id, $lehreinheitgruppe_id);

		if (isError($result))
			$this->terminateWithError(getError($result));

		$this->terminateWithSuccess($result);
	}


	public function getAllSearch()
	{
		$query = $this->input->get('query');

		if (is_null($query))
			$this->terminateWithError($this->p->t('ui', 'ungueltigeParameter'), self::ERROR_TYPE_GENERAL);

		$query_words = explode(' ', $query);

		$this->_ci->GruppeModel->addSelect('gruppe_kurzbz,
											studiengang_kz,
											semester,
											bezeichnung,
											gid,
											\'false\' as lehrverband');
		$this->_ci->GruppeModel->db->where(array('sichtbar' => true, 'aktiv' => true, 'lehre' => true, 'direktinskription' => false, 'semester IS NOT NULL' => null));
		$this->_ci->GruppeModel->db->group_start();
		foreach ($query_words as $word)
		{
			$this->_ci->GruppeModel->db->group_start();
			$this->_ci->GruppeModel->db->where('gruppe_kurzbz ILIKE', "%" . $word . "%");
			$this->_ci->GruppeModel->db->or_where('bezeichnung ILIKE', "%" . $word . "%");
			$this->_ci->GruppeModel->db->group_end();
		}
		$this->_ci->GruppeModel->db->group_end();

		$gruppen_result = $this->_ci->GruppeModel->load();

		$gruppen_array = array();

		if (isError($gruppen_result))
			$this->terminateWithError(getError($gruppen_result), self::ERROR_TYPE_GENERAL);

		if (hasData($gruppen_result))
			$gruppen_array = getData($gruppen_result);

		$this->_ci->LehrverbandModel->addSelect('CONCAT(UPPER(CONCAT(typ, kurzbz)), \'\', semester, verband, COALESCE(gruppe,\'\')) as gruppe_kurzbz,
												studiengang_kz,
												semester,
												tbl_lehrverband.bezeichnung,
												gid,
												\'true\' as lehrverband');
		$this->_ci->LehrverbandModel->addJoin('public.tbl_studiengang', 'studiengang_kz');
		$this->_ci->LehrverbandModel->addOrder('verband');
		$this->_ci->LehrverbandModel->addOrder('gruppe');
		$this->_ci->LehrverbandModel->db->where(array('tbl_lehrverband.aktiv' => true));

		$this->_ci->LehrverbandModel->db->group_start();
		foreach ($query_words as $word)
		{
			$this->_ci->LehrverbandModel->db->group_start();
			$this->_ci->LehrverbandModel->db->where('CONCAT(CONCAT(typ, kurzbz), \'\', semester, verband, COALESCE(gruppe,\'\')) ILIKE', "%" . $word . "%");
			$this->_ci->LehrverbandModel->db->or_where('tbl_lehrverband.bezeichnung ILIKE', "%" . $word . "%");
			$this->_ci->LehrverbandModel->db->group_end();
		}
		$this->_ci->LehrverbandModel->db->group_end();
		$lehrverband_result = $this->_ci->LehrverbandModel->load();

		$lehrverband_array = array();

		if (isError($lehrverband_result))
			$this->terminateWithError(getError($lehrverband_result), self::ERROR_TYPE_GENERAL);

		if (hasData($lehrverband_result))
			$lehrverband_array = getData($lehrverband_result);

		$all_gruppen = array_merge($gruppen_array, $lehrverband_array);

		$this->terminateWithSuccess($all_gruppen);
	}

	public function getBenutzerSearch()
	{
		$query = $this->input->get('query');

		if (is_null($query))
			$this->terminateWithError($this->p->t('ui', 'ungueltigeParameter'), self::ERROR_TYPE_GENERAL);

		$query_words = explode(' ', $query);

		$this->_ci->PersonModel->addSelect('vorname, nachname, uid, semester, UPPER(CONCAT(tbl_studiengang.typ, tbl_studiengang.kurzbz)) as studiengang');
		$this->_ci->PersonModel->addJoin('public.tbl_benutzer', 'person_id');
		$this->_ci->PersonModel->addJoin('public.tbl_mitarbeiter', 'uid = mitarbeiter_uid', 'LEFT');
		$this->_ci->PersonModel->addJoin('public.tbl_student', 'uid = student_uid', 'LEFT');
		$this->_ci->PersonModel->addJoin('public.tbl_studiengang', 'studiengang_kz', 'LEFT');

		$this->_ci->PersonModel->db->where(array('tbl_benutzer.aktiv' => true));

		$this->_ci->PersonModel->db->group_start();
		foreach ($query_words as $word)
		{
			$this->_ci->PersonModel->db->group_start();
			$this->_ci->PersonModel->db->where('tbl_person.vorname ILIKE', "%" . $word . "%");
			$this->_ci->PersonModel->db->or_where('tbl_person.nachname ILIKE', "%" . $word . "%");
			$this->_ci->PersonModel->db->or_where('uid ILIKE', "%" . $word . "%");
			$this->_ci->PersonModel->db->or_where('CONCAT(tbl_studiengang.typ, tbl_studiengang.kurzbz) ILIKE', "%" . $word . "%");

			if (is_numeric($word))
			{
				$this->_ci->PersonModel->db->or_where('semester', $word);
			}
			$this->_ci->PersonModel->db->group_end();
		}
		$this->_ci->PersonModel->db->group_end();
		$personen = $this->_ci->PersonModel->load();
		$this->terminateWithSuccess(hasData($personen) ? getData($personen) : array());
	}

	private function _setAuthUID()
	{
		$this->_uid = getAuthUID();

		if (!$this->_uid)
			show_error('User authentification failed');
	}

	private function checkPermission($lehreinheit_id)
	{
		$lehreinheit_result = $this->_ci->LehreinheitModel->load($lehreinheit_id);

		if (!hasData($lehreinheit_result) || isError($lehreinheit_result))
			$this->terminateWithError( $this->p->t('ui', 'ungueltigeParameter'), self::ERROR_TYPE_GENERAL);

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

}
