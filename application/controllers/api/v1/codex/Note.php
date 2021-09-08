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

if (!defined('BASEPATH')) exit('No direct script access allowed');

class Note extends API_Controller
{
	/**
	 * Note API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Note' => 'basis/note:rw'));
		// Load model NoteModel
		$this->load->model('codex/note_model', 'NoteModel');
	}

	/**
	 * @return void
	 */
	public function getNote()
	{
		$note = $this->get('note');

		if (isset($note))
		{
			$result = $this->NoteModel->load($note);

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
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['note']))
			{
				$result = $this->NoteModel->update($this->post()['note'], $this->post());
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
