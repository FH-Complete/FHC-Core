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
	const BERECHTIGUNG_KURZBZ = 'basis/lvverwaltung';

	private $_ci;
	public function __construct()
	{
		parent::__construct([
			'add' => [self::BERECHTIGUNG_KURZBZ . ':rw'],
			'delete' => [self::BERECHTIGUNG_KURZBZ . ':rw'],
			'getByLehreinheit' => [self::BERECHTIGUNG_KURZBZ . ':r'],
		]);

		$this->_ci = &get_instance();

		$this->loadPhrases([
			'ui'
		]);
		$this->_ci->load->model('education/Lehreinheitgruppe_model', 'LehreinheitgruppeModel');
		$this->_ci->load->model('education/lehreinheit_model', 'LehreinheitModel');
		$this->_ci->load->model('person/Benutzer_model', 'BenutzerModel');
		$this->_ci->load->library('LVVerwaltungPermissionLib');
	}

	public function add()
	{
		$uid = $this->input->post('uid');
		$lehreinheit_id = $this->input->post('lehreinheit_id');

		$this->checkPermission($lehreinheit_id, 'suid', $uid);

		$result = $this->_ci->LehreinheitgruppeModel->direktUserAdd($uid, $lehreinheit_id);

		if (isError($result))
			$this->terminateWithError(getError($result));

		$this->terminateWithSuccess($result);
	}

	public function delete()
	{
		$uid = $this->input->post('uid');
		$lehreinheit_id = $this->input->post('lehreinheit_id');

		$this->checkPermission($lehreinheit_id, 'suid', $uid);

		$result = $this->_ci->LehreinheitgruppeModel->direktUserDelete($uid, $lehreinheit_id);

		if (isError($result))
			$this->terminateWithError(getError($result));

		$this->terminateWithSuccess($result);
	}

	public function getByLehreinheit($lehreinheit_id = null)
	{
		$this->checkPermission($lehreinheit_id, 's');
		$gruppen = $this->_ci->LehreinheitgruppeModel->getDirectGroup($lehreinheit_id);
		$this->terminateWithSuccess(hasData($gruppen) ? getData($gruppen) : array());
	}

	private function checkPermission($lehreinheit_id, $art, $uid = false)
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

		if (!$this->_ci->lvverwaltungpermissionlib->isEntitledForLehreinheit($lehreinheit_id, $art))
			$this->terminateWithError($this->p->t('ui', 'notAuthorized'));
	}
}
