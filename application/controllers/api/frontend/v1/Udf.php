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

/**
 * This controller operates between (interface) the JS (GUI) and the UDFLib (back-end)
 * Provides data to the ajax get calls about the Udf component
 * Listens to ajax post calls to change the Udf data
 * This controller works with JSON calls on the HTTP GET or POST and the output is always JSON
 */
class Udf extends FHCAPI_Controller
{
	/**
	 * Calls the parent's constructor and prepares the UDFLib
	 */
	public function __construct()
	{
		// NOTE: UdfLib has its own permissions checks
		parent::__construct([
			'load' => self::PERM_LOGGED,
			'save' => self::PERM_LOGGED
		]);

		// Libraries
		$this->load->library('form_validation');
		$this->load->library('UDFLib');
		
		// Models
		$this->load->model($this->getTargetModelPath(), 'TargetModel');
	}

	//------------------------------------------------------------------------------------------------------------------
	// Public methods

	/**
	 * Load all UDFs for a dataset
	 *
	 * @return void
	 */
	public function load()
	{
		$pks = $this->TargetModel->getPks();
		foreach ($pks as $id)
			$this->form_validation->set_rules($id, $id, 'required');
		

		if (!$this->form_validation->run())
			$this->terminateWithValidationErrors($this->form_validation->error_array());


		$id = [];
		foreach ($pks as $pk)
			$id[$pk] = $this->input->post($pk);
		if (!is_array($this->TargetModel->getPk()))
			$id = current($id);

		$result = $this->udflib->getFieldArray($this->TargetModel, $id);

		$fields = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess($fields);
	}

	/**
	 * Saves UDFs to a dataset
	 *
	 * @return void
	 */
	public function save()
	{
		$pks = $this->TargetModel->getPks();
		foreach ($pks as $id)
			$this->form_validation->set_rules($id, $id, 'required');

		$result = $this->udflib->getCiValidations($this->TargetModel, $this->input->post());
		
		$fieldValidations = $this->getDataOrTerminateWithError($result);

		$this->form_validation->set_rules($fieldvalidations);


		if (!$this->form_validation->run())
			$this->terminateWithValidationErrors($this->form_validation->error_array());


		$id = [];
		$fields = $this->input->post();
		foreach ($pks as $pk) {
			$id[$pk] = $fields[$pk];
			unset($fields[$pk]);
		}
		if (!is_array($this->TargetModel->getPk()))
			$id = current($id);

		$result = $this->TargetModel->update($id, $fields);

		$this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess(array_fill_keys(array_keys($fields), ''));
	}

	//------------------------------------------------------------------------------------------------------------------
	// Private methods

	/**
	 * Get the path to the target model from the url
	 *
	 * @return string
	 */
	private function getTargetModelPath()
	{
		$ci_model_path = array_slice($this->uri->rsegments, 2);
		if ($ci_model_path)
			$ci_model_path[] = ucfirst(array_pop($ci_model_path)) . '_model';
		return implode(DIRECTORY_SEPARATOR, $ci_model_path);
	}
}