<?php
/**
 * Copyright (C) 2026 fhcomplete.org
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
 * This controller generates javascript from snippets for tabulator columns
 */
class Tabulatorcolumns extends Auth_Controller
{
	public function __construct()
	{
		parent::__construct([
			'stv' => ['admin:r', 'assistenz:r'],
			'lvverwaltung' => ['admin:r', 'assistenz:r']
		]);
	}

	/**
	 * Render tabulator columns for config
	 *
	 * @param string				$config
	 *
	 * @return void
	 */
	public function _remap($config)
	{
		$this->load->config($config);
		$list_columns = $this->config->item('list_columns') ?: [];

		$snippets = [
			'jssnippets/tabulatorcolumns/' . $config . '.js'
		];

		foreach ($list_columns as $col) {
			$snippets[] = $col['js'];
		}

		$this->output->set_content_type('application/javascript');

		$this->load->view('jssnippets/tabulatorcolumns.js', [
			'snippets' => $snippets
		]);
	}
}
