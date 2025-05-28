<?php
/**
 * Copyright (C) 2025 fhcomplete.org
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

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

class DirektGruppe extends FHCAPI_Controller
{
	private $_ci;
	public function __construct()
	{
		parent::__construct([
			'add' => ['admin:rw', 'assistenz:rw'],
			'delete' => ['admin:rw', 'assistenz:rw'],
			'getByLehreinheit' => ['admin:r', 'assistenz:r'],
		]);

		$this->_ci = &get_instance();

		$this->loadPhrases([
			'ui'
		]);
		$this->_ci->load->model('education/Lehreinheitgruppe_model', 'LehreinheitgruppeModel');
		$this->_ci->load->model('education/lehreinheit_model', 'LehreinheitModel');
		$this->_ci->load->model('person/Benutzer_model', 'BenutzerModel');
	}

	public function add()
	{
		$uid = $this->input->post('uid');
		$lehreinheit_id = $this->input->post('lehreinheit_id');

		$this->checkPermission($lehreinheit_id, $uid);

		$result = $this->_ci->LehreinheitgruppeModel->direktUserAdd($uid, $lehreinheit_id);

		if (isError($result))
			$this->terminateWithError(getError($result));

		$this->terminateWithSuccess($result);
	}

	public function delete()
	{
		$uid = $this->input->post('uid');
		$lehreinheit_id = $this->input->post('lehreinheit_id');

		$this->checkPermission($lehreinheit_id, $uid);

		$result = $this->_ci->LehreinheitgruppeModel->direktUserDelete($uid, $lehreinheit_id);

		if (isError($result))
			$this->terminateWithError(getError($result));

		$this->terminateWithSuccess($result);
	}

	public function getByLehreinheit($lehreinheit_id = null)
	{
		$this->checkPermission($lehreinheit_id);
		$gruppen = $this->_ci->LehreinheitgruppeModel->getDirectGroup($lehreinheit_id);
		$this->terminateWithSuccess(hasData($gruppen) ? getData($gruppen) : array());
	}

	private function checkPermission($lehreinheit_id, $uid = false)
	{
		if (is_null($lehreinheit_id) || !ctype_digit((string)$lehreinheit_id))
			$this->terminateWithError( $this->p->t('ui', 'ungueltigeParameter'), self::ERROR_TYPE_GENERAL);

		$lehreinheit_result = $this->_ci->LehreinheitModel->load($lehreinheit_id);

		if (!hasData($lehreinheit_result) || isError($lehreinheit_result))
			$this->terminateWithError( $this->p->t('ui', 'ungueltigeParameter'), self::ERROR_TYPE_GENERAL);

		if ($uid)
		{
			$benuzuer_result = $this->_ci->BenutzerModel->load(array($uid));
			if (!hasData($benuzuer_result) || isError($benuzuer_result))
				$this->terminateWithError( $this->p->t('ui', 'ungueltigeParameter'), self::ERROR_TYPE_GENERAL);
		}

		$result = $this->_ci->LehreinheitModel->getOes($lehreinheit_id);

		if (isError($result))
			$this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);

		$oe_array = $result;

		if (!$this->_ci->permissionlib->isBerechtigtMultipleOe('admin', $oe_array, 'suid') &&
			!$this->_ci->permissionlib->isBerechtigtMultipleOe('assistenz', $oe_array, 'suid'))
			$this->terminateWithError($this->p->t('ui', 'error_fieldWriteAccess'));
	}
}
