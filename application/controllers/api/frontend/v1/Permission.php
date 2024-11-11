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

class Permission extends FHCAPI_Controller
{
	public function __construct()
	{
		parent::__construct([
			'isBerechtigt' => self::PERM_LOGGED
		]);

		// Load the library SearchBarLib
		$this->load->library('PermissionLib');
	}
	
	public function isBerechtigt()
	{
		$payload = json_decode($this->input->raw_input_stream, TRUE);
		if( !isset($payload['berechtigung_kurzbz']) || empty($payload['berechtigung_kurzbz']) )
		{
			$this->terminateWithError('Missing Parameter "berechtigung_kurzbz"');
		}
		$berechtigung_kurzbz = $payload['berechtigung_kurzbz'];
		$art = isset($payload['art']) ? $payload['art'] : null;
		$oe_kurzbz = isset($payload['oe_kurzbz']) ? $payload['oe_kurzbz'] : null;
		$kostenstelle_id = isset($payload['kostenstelle_id']) ? $payload['kostenstelle_id'] : null;
		$payload['isBerechtigt'] = $this->permissionlib->isBerechtigt(
			$berechtigung_kurzbz, $art, $oe_kurzbz, $kostenstelle_id
		);
		
		$this->terminateWithSuccess($payload);
	}
}