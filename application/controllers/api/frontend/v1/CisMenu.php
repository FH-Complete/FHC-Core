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


class CisMenu extends FHCAPI_Controller
{

	/**
	 * Object initialization
	 */
	public function __construct()
	{
		parent::__construct([
			'getMenu' => self::PERM_LOGGED,
		]);



	}

	//------------------------------------------------------------------------------------------------------------------
	// Public methods

	/**
	 * fetches the menu for CIS from the database based on the userLanguage
	 */
	public function getMenu()
	{
		$this->load->model('content/Content_model', 'ContentModel');
		$this->load->config('cis');
		$cis4_content_id = $this->config->item('cis_menu_root_content_id');
		$result = $this->ContentModel->getMenu($cis4_content_id, getAuthUID(), getUserLanguage());
		$result = $this->getDataOrTerminateWithError($result);
		$menu = $result->childs ?? [];

		$menu = $this->generateUrlsForMenuItems($menu);
		$this->terminateWithSuccess($menu);
	}

	private function generateUrlsForMenuItems($menuItems)
	{
		return array_map(
			function ($menuItem) {
				return $this->generateUrlForMenuItem($menuItem);
			},
			$menuItems
		);
	}

	private function generateUrlForMenuItem($menuItem)
	{
		$menuItem->url = $this->menuItemUrlHelper($menuItem);
		unset($menuItem->content);

		if ($menuItem->childs && count($menuItem->childs)) {
			$menuItem->childs = $this->generateUrlsForMenuItems($menuItem->childs);
		}

		return $menuItem;
	}

	private function menuItemUrlHelper($menuItem)
	{
		if ($menuItem->template_kurzbz !== 'redirect') {
			return site_url("/CisVue/Cms/content/" . $menuItem->content_id);
		}

		if (!$menuItem->content || !mb_strlen($menuItem->content)) {
			return '';
		}

		$doc = new DOMDocument();
		$doc->loadXML($menuItem->content);
		$urlElem = $doc->getElementsByTagName('url')->item(0);

		if (!$urlElem) {
			return '';
		}

		$url = $urlElem->textContent;

		if (strpos($url, '../cms/news.php') !== false) {
			$newsRegex = '/^\.\.\/cms\/news\.php/';
			$url = preg_replace($newsRegex, site_url("/CisVue/Cms/news"), $url);
		}

		if (strpos($url, '../cms/content.php?') !== false) {
			$contentRegex = '/^\.\.\/cms\/content\.php\?content_id=([0-9]+)/';
			$matches = [];
			preg_match($contentRegex, $url, $matches);
			$url = site_url('/CisVue/Cms/content/' . $matches[1]);
		}

		if (strpos($url, '../index.ci.php') !== false) {
			$indexRegex = '/^\.\.\/index\.ci\.php/';
			$url = preg_replace($indexRegex, site_url(), $url);
		}

		if (strpos($url, '../') !== false) {
			$relativeRegex = '/^\.\.\//';
			$url = preg_replace($relativeRegex, base_url(), $url);
		}

		return $url;
	}

}

