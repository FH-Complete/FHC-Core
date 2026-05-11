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

require_once(APPPATH . 'libraries/menu/StgBasedMenuLib.php');

/**
 * StudVw Menu library
 */
class StvMenuLib extends StgBasedMenuLib
{
	public function build($url_segments = [])
	{
		$result = $this->buildSubmenu($url_segments);

		if ($result === null)
			show_404();

		return $result;
	}
	public function buildAll()
	{
		return $this->buildMenu();
	}

	protected function getLinkTemplate($vars)
	{
		$result = $this->convertFullUrlToLinkUrl($vars['_url'], $this->config);

		$firstLevelPrestudent = (count($vars['_path']) > 1 && $vars['_path'][1] == 'prestudent');
		$secondLevelPrestudent = (count($vars['_path']) > 2 && $vars['_path'][2] == 'prestudent');
		if (!$firstLevelPrestudent && !$secondLevelPrestudent)
			$result = 'CURRENT_SEMESTER/' . $result;

		if ($result)
			return $result . '/%s';

		return '%s';
	}

	private function convertFullUrlToLinkUrl($url_segments, $config)
	{
		if (!count($url_segments))
			return '';
		$result = $current_segment = array_shift($url_segments);
		
		if (isset($config[$current_segment][self::GENERATOR_FUNC_KEY])) {
			$result = array_shift($url_segments);
		}

		$children = '';

		if (isset($config[$current_segment]['children']))
			$children = $this->convertFullUrlToLinkUrl($url_segments, $config[$current_segment]['children']);

		if ($children)
			return $result . '/' . $children;

		return $result;
	}
}
