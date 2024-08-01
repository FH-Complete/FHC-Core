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

        $insert_into_result = $this->BookmarkModel->insert(['uid'=>$this->uid, 'url'=>$url, 'title'=>$title,'tag'=>$tag, 'insertvon'=>$this->uid, 'updateamum'=>NULL, 'updatevon'=>NULL]);

        $insert_into_result = $this->getDataOrTerminateWithError($insert_into_result);

        $this->terminateWithSuccess($insert_into_result);

    }
}

