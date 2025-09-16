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

if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Controller for downloading Akte
 */
class Akte extends Auth_Controller
{
	/**
	 * Calls the parent's constructor and prepares libraries and phrases
	 */
	public function __construct()
	{
		parent::__construct([
			'download' => ['admin:w', 'assistenz:w'],
		]);

		// Load libraries
		$this->load->library('AkteLib');
	}

	//------------------------------------------------------------------------------------------------------------------
	// Public methods

	/**
	 *
	 * Downloads an Akte
	 */
	public function download()
	{
		$akte_id = $this->input->get('akte_id');

		if (!is_numeric($akte_id)) $this->terminateWithError('akte Id missing');

		$akteResult = $this->aktelib->getByAkteId($akte_id);

		if (!hasData($akteResult)) $this->terminateWithError('Akte not found');

		$data = getData($akteResult)[0];

		if (!isEmptyString($data->inhalt))
		{
			$fileObj = new stdClass();
			$fileObj->filename = $data->filename;
			$fileObj->file_content = base64_decode($data->inhalt);
			$fileObj->name = $data->titel;
			$fileObj->mimetype = $data->mimetype;
			$fileObj->disposition = 'attachment';

			$this->outputFile($fileObj);
		}
	}
}

