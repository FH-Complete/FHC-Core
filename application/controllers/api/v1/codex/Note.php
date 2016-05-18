<?php
/**
 * FH-Complete
 *
 * @package		FHC-API
 * @author		FHC-Team
 * @copyright	Copyright (c) 2016, fhcomplete.org
 * @license		GPLv3
 * @link		http://fhcomplete.org
 * @since		Version 1.0
 * @filesource
 */
// ------------------------------------------------------------------------

if(!defined('BASEPATH')) exit('No direct script access allowed');

class Note extends APIv1_Controller
{
	/**
	 * Note API constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		// Load model NoteModel
		$this->load->model('codex/note_model', 'NoteModel');
		// Load set the uid of the model to let to check the permissions
		$this->NoteModel->setUID($this->_getUID());
	}

	/**
	 * @return void
	 */
	public function getNote()
	{
		$noteID = $this->get('note_id');
		
		if(isset($noteID))
		{
			$result = $this->NoteModel->load($noteID);
			
			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	/**
	 * @return void
	 */
	public function postNote()
	{
		if($this->_validate($this->post()))
		{
			if(isset($this->post()['note_id']))
			{
				$result = $this->NoteModel->update($this->post()['note_id'], $this->post());
			}
			else
			{
				$result = $this->NoteModel->insert($this->post());
			}
			
			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}
	
	private function _validate($note = NULL)
	{
		return true;
	}
}