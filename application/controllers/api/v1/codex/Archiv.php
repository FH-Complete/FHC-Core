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

class Archiv extends API_Controller
{
	/**
	 * Archiv API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Archiv' => 'basis/archiv:rw'));
		// Load model ArchivModel
		$this->load->model('codex/archiv_model', 'ArchivModel');
	}

	/**
	 * @return void
	 */
	public function getArchiv()
	{
		$archivID = $this->get('archiv_id');

		if (isset($archivID))
		{
			$result = $this->ArchivModel->load($archivID);

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
	public function postArchiv()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['archiv_id']))
			{
				$result = $this->ArchivModel->update($this->post()['archiv_id'], $this->post());
			}
			else
			{
				$result = $this->ArchivModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($archiv = NULL)
	{
		return true;
	}
}
