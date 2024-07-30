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
        $bookmarks = $this->BookmarkModel->getAll($this->uid);

        if(isError($bookmarks)){
            $this->terminateWithError(getError($bookmarks));
        }

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
        if(!isset($bookmark_id)) $this->terminateWithError("missing required parameters");
        
        $bookmark = $this->BookmarkModel->get($bookmark_id);

        if(isError($bookmark)){
            $this->terminateWithError(getError($bookmark));
        }

        $bookmark = current($this->getDataOrTerminateWithError($bookmark));

        // only delete bookmark if the user is the owner of the bookmark
        $this->load->library('PermissionLib');

        if($bookmark->uid == $this->uid || $this->permissionlib->isBerechtigt('admin')){

            $delete_result = $this->BookmarkModel->delete($bookmark_id);

            if(isError($delete_result)){
                $this->terminateWithError(getError($delete_result));
            }

            $delete_result = $this->getDataOrTerminateWithError($delete_result);

            $this->terminateWithSuccess($delete_result);
        }else{
            $this->terminateWithError("You are not authorized to delete this bookmark");
        }
    }
}

