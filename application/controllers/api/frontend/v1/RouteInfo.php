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
if (!defined('BASEPATH'))
	exit('No direct script access allowed');

class RouteInfo extends FHCAPI_Controller
{

	public function __construct()
	{
		parent::__construct([
			'info' => self::PERM_LOGGED,
		]);

		$this->load->model('system/Webservicelog_model', 'WebservicelogModel');
	}

	public function info()
	{
		$payload = json_decode($this->input->raw_input_stream);

		if (isset($payload->app) && isset($payload->path) && $this->isValidApp($payload->app) && $this->isValidPath($payload->path))
		{
			$this->WebservicelogModel->insert(array(
				'webservicetyp_kurzbz' => 'content',
				'beschreibung' => $payload->app,
				'request_data' => $payload->path,
				'execute_user' => getAuthUID(),
				'execute_time' => 'NOW()'
			));
		}
		$this->terminateWithSuccess(true);
	}

	protected function isValidApp($app)
	{
		return preg_match("/^[A-Za-z0-9\-_]+$/", $app);
	}

	protected function isValidPath($path)
	{
		return preg_match("/^[\/A-Za-z0-9_.\-~?%=&;]+$/", $path);
	}
}
