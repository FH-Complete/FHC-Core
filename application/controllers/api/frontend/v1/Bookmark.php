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

class Bookmark extends FHCAPI_Controller
{

	/**
	 * Object initialization
	 */
	public function __construct()
	{
		parent::__construct([
			'getBookmarks' => self::PERM_LOGGED,
            'delete' => self::PERM_LOGGED,
			'insert' => self::PERM_LOGGED,
			'update' => self::PERM_LOGGED,
			'changeOrder' => self::PERM_LOGGED,
			'getAllBookmarkTags' => self::PERM_LOGGED,
			'getTagFilter' => self::PERM_LOGGED,
			'addAndUpdateTagFilter' => self::PERM_LOGGED,
			'isInOverride' => self::PERM_LOGGED,
			'addWidgetToOverride' => self::PERM_LOGGED,
		]);

		$this->load->model('dashboard/Bookmark_model', 'BookmarkModel');

		$this->uid = getAuthUID();
		$this->pid = getAuthPersonID();

	}

	//------------------------------------------------------------------------------------------------------------------
	// Public methods

	
    /**
	 * gets the bookmarks associated to a user 
	 * @access public
	 * @return void
	 */
	public function getBookmarks()
	{
        $this->BookmarkModel->addOrder("sort");
		$bookmarks = $this->BookmarkModel->loadWhere(["uid"=>$this->uid]);

        $bookmarks = $this->getDataOrTerminateWithError($bookmarks);

        $this->terminateWithSuccess($bookmarks);
    }

    /**
	 * deletes bookmark from associated user 
	 * @access public
	 * @return void
	 */
    public function delete($bookmark_id)
	{
        $bookmark = $this->BookmarkModel->load($bookmark_id);

        $bookmark = current($this->getDataOrTerminateWithError($bookmark));

        // only delete bookmark if the user is the owner of the bookmark
        if($bookmark->uid == $this->uid || $this->permissionlib->isBerechtigt('admin')){

            $delete_result = $this->BookmarkModel->delete($bookmark_id);

            $delete_result = $this->getDataOrTerminateWithError($delete_result);

            $this->terminateWithSuccess($delete_result);
        }else{
            $this->_outputAuthError(['delete' => ['admin:rw']]);
        }
    }

    /**
	 * inserts new bookmark into the bookmark table 
	 * @access public
	 * @return void
	 */
    public function insert()
	{
        // form validation
        $this->load->library('form_validation');
        $this->form_validation->set_rules('url', 'URL', 'required|valid_url|max_length[511]');
        $this->form_validation->set_rules('title', 'Title', 'required|max_length[255]');
        if($this->form_validation->run() == FALSE) $this->terminateWithValidationErrors($this->form_validation->error_array());

        $url = $this->input->post('url',true);
        $title = $this->input->post('title',true);
        $tag = $this->input->post('tag', true);
		if (is_array($tag)) {
			$tag = json_encode($tag); // convert PHP array to JSON string
		}
		$sort = $this->input->post('sort', true);

        $insert_into_result = $this->BookmarkModel->insert(['uid'=>$this->uid, 'url'=>$url, 'title'=>$title,'tag'=>$tag, 'insertvon'=>$this->uid, 'updateamum'=>NULL, 'updatevon'=>NULL, 'sort'=>$sort,]);

        $insert_into_result = $this->getDataOrTerminateWithError($insert_into_result);

        $this->terminateWithSuccess($insert_into_result);

    }

	/**
	 * updates bookmark in the bookmark table 
	 * @access public
	 * @return void
	 */
    public function update($bookmark_id)
	{
        // form validation
        $this->load->library('form_validation');
        $this->form_validation->set_rules('url', 'URL', 'required|valid_url|max_length[511]');
        $this->form_validation->set_rules('title', 'Title', 'required|max_length[255]');
        if($this->form_validation->run() == FALSE) $this->terminateWithValidationErrors($this->form_validation->error_array());

        $url = $this->input->post('url',true);
        $title = $this->input->post('title',true);
		$tag = $this->input->post('tag', true);
		if (is_array($tag)) {
			$tag = json_encode($tag);
		}

		$now = new DateTime();
		$now = $now->format('Y-m-d H:i:s');

        $update_result = $this->BookmarkModel->update($bookmark_id,['url'=>$url, 'title'=>$title, 'tag'=>$tag, 'updateamum'=>$now]);

        $update_result = $this->getDataOrTerminateWithError($update_result);

        $this->terminateWithSuccess($update_result);

    }

	/**
	 * changes sort of two bookmarks in the bookmark table
	 * @access public
	 * @return void
	 */
    public function changeOrder($bookmark_id1, $bookmark_id2)
	{

		$result1 = $this->BookmarkModel->load($bookmark_id1);
		$data1 = $this->getDataOrTerminateWithError($result1);
		$sort1 = current($data1)->sort;

		$result2 = $this->BookmarkModel->load(["bookmark_id"=>$bookmark_id2]);
		$data2 = $this->getDataOrTerminateWithError($result2);
		$sort2 = current($data2)->sort;

        $update_result1 = $this->BookmarkModel->update($bookmark_id1,['sort'=>$sort2,]);
        $update_result[] = $this->getDataOrTerminateWithError($update_result1);

		$update_result2 = $this->BookmarkModel->update($bookmark_id2,['sort'=>$sort1,]);
        $update_result[] = $this->getDataOrTerminateWithError($update_result2);

        $this->terminateWithSuccess($update_result);
    }

	/**
	 * get all the bookmark tags associated to a user
	 * @access public
	 * @return void
	 */
	public function getAllBookmarkTags()
	{
		$this->BookmarkModel->addOrder("sort");
		$result = $this->BookmarkModel->getAllBookmarkTags($this->uid);

		$bookmarks = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess(current($bookmarks));
	}

	/**
	 * get all tagFilter of a certain bookmark widget
	 * @access public
	 * @return void
	 */
	public function getTagFilter($widgetId, $sectionName)
	{
		$result = $this->BookmarkModel->getTagFilter($widgetId, $this->uid, $sectionName);
		$data = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess(current($data));
	}
	/**
	 * get all tagFilter of a certain bookmark widget
	 * @access public
	 * @return void
	 */
	public function addAndUpdateTagFilter($widgetId, $sectionName)
	{
		$tags = $this->input->post('tags',true);
		if (is_array($tags))
		{
			$tags = json_encode($tags);
		}
		$result = $this->BookmarkModel->addAndUpdateTagFilter($widgetId, $this->uid, $sectionName, $tags);

		$data = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess($data);
	}

	/**
	 * checks if a widget has already an entry in the benutzeroverride
	 * @access public
	 * @return void
	 */
	public function isInOverride($widgetId, $sectionName)
	{
		$result = $this->BookmarkModel->checkOrAddToOverride($widgetId, $this->uid, $sectionName);

		$data = getData($result);
		if(!$data)
			$this->terminateWithSuccess([false, 0]);

		$id = current($data)->widgetid;
		$id = trim($id, '"');

		if ($id != $widgetId)
			$this->terminateWithSuccess([false, 1]);
		else
			$this->terminateWithSuccess([true, null]);
	}
	/**
	 * adds widget benutzeroverride
	 * @access public
	 * @return void
	 */
	public function addWidgetToOverride($widgetId, $sectionName, $mode, $x, $y, $h, $w)
	{
		$this->load->library('dashboard/DashboardLib', null, 'DashboardLib');
		$dashboard_kurzbz = "CIS";
		$structure = [
			"custom" => [
				"widgets" => []
			],
			"general" => [
				"widgets" => [
					$widgetId => [
						"widget" => 3,
						"config" => new stdClass(),
						"place" => [
							"3" => [
								"x" => $x,
								"y" => $y,
								"w" => $w,
								"h" => $h
							]
						],
						"widgetid" => $widgetId,
						"custom" => 1,
						"id" => "insertByBookmarkwidget"
					]
				]
			]
		];
		$jsonOverrideNew = json_encode($structure, JSON_UNESCAPED_UNICODE);

		//no existing benutzeroverride
		if($mode == 0)
		{
			$override = $this->DashboardLib->getOverrideOrCreateEmptyOverride($dashboard_kurzbz, $this->uid);

			$override->override = $jsonOverrideNew;

			$result = $this->DashboardLib->insertOrUpdateOverride($override);

			$this->terminateWithSuccess($result);

		}
		//benutzeroverride existing, but widget not included
		elseif($mode == 1)
		{
			$overrideExisting = $this->DashboardLib->getOverrideOrCreateEmptyOverride($dashboard_kurzbz, $this->uid);
			$override = json_decode($overrideExisting->override, true); // decode as Array

			$newWidget = [
				"widget" => 3,
				"config" => new stdClass(),
				"place" => [
					"3" => [
						"x" => $x,
						"y" => $y,
						"w" => $w,
						"h" => $h
					]
				],
				"widgetid" => $widgetId,
				"custom" => 1,
				"id" => "insertByBookmarkwidget"
			];

			$override['general']['widgets'][$widgetId] = $newWidget;

			$jsonOverrideUpdated = json_encode($override, JSON_UNESCAPED_UNICODE);
			$overrideExisting->override = $jsonOverrideUpdated;

			$result = $this->DashboardLib->insertOrUpdateOverride($overrideExisting);

			$this->terminateWithSuccess($result);
		}
		else
			$this->terminateWithError("Error: no known mode");
	}

}

