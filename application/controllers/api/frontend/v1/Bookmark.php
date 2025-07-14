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
/**
 * @SWG\Info(
 *     title="Bookmark API",
 *     version="1.0.0"
 * )
 */

/**
 * @SWG\Swagger(
 *     schemes={"https"},
 *     basePath="/fhcompletecis4/cis.php/api/frontend/v1/Bookmark/"
 * )
 */

/**
 * @SWG\SecurityScheme(
 *     securityDefinition="basicAuth",
 *     type="basic"
 * )
 */


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
			'test_true' => self::PERM_LOGGED
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
	 * @SWG\Get(
	 *      path="/getBookmarks",
	 *      security={{"basicAuth":{}}},
	 *      tags={"bookmarks"},
	 *      summary="Get user's bookmarks",
	 *      description="Returns all bookmarks associated with the authenticated user.",
	 *      @SWG\Response(
	 *          response=200,
	 *          description="List of bookmarks"
	 *      ),
	 *      @SWG\Response(
	 *          response=401,
	 *          description="Unauthorized"
	 *      )
	 *  )
	 */
	public function getBookmarks()
	{
        $this->BookmarkModel->addOrder("bookmark_id");
		$bookmarks = $this->BookmarkModel->loadWhere(["uid"=>$this->uid]);

        $bookmarks = $this->getDataOrTerminateWithError($bookmarks);

        $this->terminateWithSuccess($bookmarks);
    }

    /**
	 * deletes bookmark from associated user 
	 * @access public
	 * @return void
	 * @SWG\Post(
	 *      path="/delete/{bookmark_id}",
	 *      security={{"basicAuth":{}}},
	 *      tags={"bookmarks"},
	 *      summary="Delete a bookmark",
	 *      description="Deletes a bookmark if the user is the owner or an admin.",
	 *      @SWG\Parameter(
	 *          name="bookmark_id",
	 *          in="path",
	 *          required=true,
	 *          type="integer"
	 *      ),
	 *      @SWG\Response(
	 *          response=200,
	 *          description="Bookmark deleted successfully"
	 *      ),
	 *      @SWG\Response(
	 *          response=403,
	 *          description="Forbidden - not the owner"
	 *      ),
	 *      @SWG\Response(
	 *          response=404,
	 *          description="Bookmark not found"
	 *      )
	 *  )
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
	 * @SWG\Post(
	 *      path="/insert",
	 *      security={{"basicAuth":{}}},
	 *      tags={"bookmarks"},
	 *      summary="Insert a new bookmark",
	 *      @SWG\Parameter(
	 *           name="body",
	 *           in="body",
	 *           required=true,
	 *           @SWG\Schema(
	 *               type="object",
	 *               required={"url", "title"},
	 *               @SWG\Property(
	 *                   property="url",
	 *                   type="string",
	 *                   example="https://github.com/swagger-api/swagger-codegen"
	 *               ),
	 *               @SWG\Property(
	 *                   property="title",
	 *                   type="string",
	 *                   example="Swagger Codegen"
	 *               ),
	 *               @SWG\Property(
	 *                   property="tag",
	 *                   type="string",
	 *                   example="API"
	 *               )
	 *           )
	 *       ),
	 *       @SWG\Response(
	 *           response=201,
	 *           description="Bookmark created"
	 *       ),
	 *       @SWG\Response(
	 *           response=400,
	 *           description="Validation error"
	 *       )
	 *  )
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

	/**
	 * @SWG\Post(
	 *      path="/update/{bookmark_id}",
	 *      security={{"basicAuth":{}}},
	 *      tags={"bookmarks"},
	 *      summary="Update a bookmark",
	 *      description="Updates a bookmark's URL and title for the given ID.",
	 *      @SWG\Parameter(
	 *          name="bookmark_id",
	 *          in="path",
	 *          required=true,
	 *          type="integer",
	 *          description="ID of the bookmark to update"
	 *      ),
	 *      @SWG\Parameter(
	 *          name="body",
	 *          in="body",
	 *          required=true,
	 *          @SWG\Schema(
	 *              type="object",
	 *              required={"url", "title"},
	 *              @SWG\Property(
	 *                  property="url",
	 *                  type="string",
	 *                  example="https://updated-url.com"
	 *              ),
	 *              @SWG\Property(
	 *                  property="title",
	 *                  type="string",
	 *                  example="Updated Title"
	 *              )
	 *          )
	 *      ),
	 *      @SWG\Response(
	 *          response=200,
	 *          description="Bookmark updated"
	 *      ),
	 *      @SWG\Response(
	 *          response=400,
	 *          description="Validation error"
	 *      )
	 * )
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

		$now = new DateTime();
		$now = $now->format('Y-m-d H:i:s');

        $update_result = $this->BookmarkModel->update($bookmark_id,['url'=>$url, 'title'=>$title,'updateamum'=>$now]);

        $update_result = $this->getDataOrTerminateWithError($update_result);

        $this->terminateWithSuccess($update_result);

    }
}

