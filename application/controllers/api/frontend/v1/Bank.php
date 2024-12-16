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
 * along with this program.  If not, see <https://www.gnu.org/licenses/> .
 */

if (! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Controller between the bank related VueJS components and the backend
 */
class Bank extends FHCAPI_Controller
{
	const BANK_NAME_PARAM = 'name';
	const BIC_PARAM = 'bic';
	const IBAN_PARAM = 'iban';

	/**
	 * Object initialization
	 */
	public function __construct()
	{
		// Sets permissions
		parent::__construct(array(
			'getBankData' => self::PERM_LOGGED,
			'postBankData' => self::PERM_LOGGED
		));

		// Load language phrases
		$this->loadPhrases(array('person'));

		// Loads model Bankverbindung_model
		$this->load->model('person/Bankverbindung_model', 'BankverbindungModel');
	}

	//------------------------------------------------------------------------------------------------------------------
	// Public methods

	/**
	 * Reads the bank data using the person id of the logged person and returns them in JSON format
	 */
	public function getBankData()
	{
		// Person id of the logged user
		$loggedPersonId = getAuthPersonId();

		// If null then not authenticated then terminate
		if ($loggedPersonId == null) $this->terminateWithError('Not logged user/User without an associated person', self::ERROR_TYPE_AUTH);

		// Gets the latest added to the database bank data for this logged user
		$bankDataResult = $this->BankverbindungModel->execReadOnlyQuery(
			'SELECT
				bv.name,
				bv.bic,
				bv.iban,
				COALESCE(bv.updateamum, bv.insertamum) AS update_date,
				bv.insertamum
			  FROM
				public.tbl_bankverbindung bv
			 WHERE bv.person_id = ?
		      ORDER BY bv.insertamum DESC, update_date DESC
			 LIMIT 1',
			array($loggedPersonId)
		);

		// Get the retrieved data or terminate
		$data = $this->getDataOrTerminateWithError($bankDataResult);

		// Anyway terminate it!
		$this->terminateWithSuccess($data);
	}

	/**
	 * Writes the bank data using the person id of the logged person and the posted bank data
	 */
	public function postBankData()
	{
		// UID and Person id of the logged user
		$loggedUID = getAuthUID();
		$loggedPersonId = getAuthPersonId();

		// If null then not authenticated then terminate
		if ($loggedUID == null) $this->terminateWithError('Not logged user/User without UID', self::ERROR_TYPE_AUTH);
		if ($loggedPersonId == null) $this->terminateWithError('Not logged user/User without Person Id', self::ERROR_TYPE_AUTH);

		// Loads model Mitarbeiter_model
		$this->load->model('ressource/Mitarbeiter_model', 'MitarbeiterModel');

		// Checks if the logged user is an amployee, in case stop the execution
		if ($this->MitarbeiterModel->isMitarbeiter($loggedUID)) $this->terminateWithValidationErrors(array('' => $this->p->t('person', 'notForEmployees')));

		// Loads the CI validation library
		$this->load->library('form_validation');

		// Checks if the posted parameters are fine
		$this->form_validation->set_rules(self::BANK_NAME_PARAM, null, array('required', 'alpha_numeric_spaces'));
		$this->form_validation->set_rules(self::BIC_PARAM, null, array('required', 'alpha_numeric'));
		$this->form_validation->set_rules(self::IBAN_PARAM, null, array('required', 'alpha_numeric_spaces'));

		// Run the validation and checks the result
		if (!$this->form_validation->run()) $this->terminateWithValidationErrors($this->form_validation->error_array());

		// Checks if the provided BIC is fine
		$bic = preg_replace("/[^A-Za-z0-9 ]/", '', $this->input->post(self::BIC_PARAM));
		if (!$this->_checkBic($bic)) $this->terminateWithValidationErrors(array('bic' => $this->p->t('person', 'notValidaBIC')));

		// Checks if the provided IBAN is fine using the php-iban library
		$iban = preg_replace("/[^A-Za-z0-9 ]/", '', $this->input->post(self::IBAN_PARAM));
		if (!verify_iban($iban)) $this->terminateWithValidationErrors(array('iban' => $this->p->t('person', 'notValidaIBAN')));

		// Check if there is at least a record in the bank data table
		$bankDataResult = $this->BankverbindungModel->execReadOnlyQuery(
			'SELECT
				bv.bankverbindung_id,
				COALESCE(bv.updateamum, bv.insertamum) AS update_date,
				bv.insertamum
			  FROM
				public.tbl_bankverbindung bv
			 WHERE bv.person_id = ?
		      ORDER BY bv.insertamum DESC, update_date DESC
			 LIMIT 1',
			array($loggedPersonId)
		);

		// If a db error occurred then terminate
		if (isError($bankDataResult)) $this->terminateWithError('Database error while retrieving bank data', self::ERROR_TYPE_DB);

		$writeDataResult = null; // it is considered as an error

		// If at least a record exists then update
		if (hasData($bankDataResult))
		{
			// Then update
			$writeDataResult = $this->BankverbindungModel->update(
				getData($bankDataResult)[0]->bankverbindung_id,
				array(
					'name' => $this->input->post(self::BANK_NAME_PARAM),
					'bic' => $bic,
					'iban' => $iban,
					'updateamum' => 'NOW()',
					'verrechnung' => true,
					'updatevon' => $loggedUID,
					'typ' => 'p'
				)
			);
		}
		else // otherwise insert
		{
			// Otherwise insert
			$writeDataResult = $this->BankverbindungModel->insert(
				array(
					'person_id' => $loggedPersonId,
					'name' => $this->input->post(self::BANK_NAME_PARAM),
					'bic' => $this->input->post(self::BIC_PARAM),
					'iban' => $this->input->post(self::IBAN_PARAM),
					'insertamum' => 'NOW()',
					'verrechnung' => true,
					'insertvon' => $loggedUID,
					'typ' => 'p'
				)
			);
		}

		// If a db error occurred then terminate
		if (isError($writeDataResult)) $this->terminateWithError('Database error while writing bank data', self::ERROR_TYPE_DB);

		// If everything was fine then return a success
		$this->terminateWithSuccess('Database updated');
	}

	/**
	 * Generic BIC check
	 */
	private function _checkBic($bic)
	{
		// Check if the provided BIC is fine
		return preg_match("^([a-zA-Z]){4}([a-zA-Z]){2}([0-9a-zA-Z]){2}([0-9a-zA-Z]{3})?$^", $bic) == 1;
	}
}

