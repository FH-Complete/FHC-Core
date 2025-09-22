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

if (!defined("BASEPATH")) exit("No direct script access allowed");

class CLI_Manager extends CLI_Controller
{
	/**
	 *
	 */
	public function __construct()
	{
		parent::__construct();

		// Loads PhrasesLib
		$this->load->library('PhrasesLib');
	}

	/**
	 *
	 */
	public function installFrom($phrasesDirectoryPath)
	{
		$this->phraseslib->installFrom(urldecode($phrasesDirectoryPath));
	}

	/**
	 * Calls the PhrasesLib->syncFiles to create/update the phrases files under the directory application/phrases/
	 */
	public function syncFiles()
	{
		$this->phraseslib->syncFiles();
	}
}

