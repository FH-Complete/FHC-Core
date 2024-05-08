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

class Zeitwunsch extends API_Controller
{
	/**
	 * Zeitwunsch API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Zeitwunsch' => 'basis/zeitwunsch:rw'));
		// Load model ZeitwunschModel
		$this->load->model('ressource/zeitwunsch_model', 'ZeitwunschModel');


	}

	/**
	 * @return void
	 */
	public function getZeitwunsch()
	{
		$tag = $this->get('tag');
		$mitarbeiter_uid = $this->get('mitarbeiter_uid');
		$stunde = $this->get('stunde');

		if (isset($tag) && isset($mitarbeiter_uid) && isset($stunde))
		{
			$result = $this->ZeitwunschModel->load(array($tag, $mitarbeiter_uid, $stunde));

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
	public function postZeitwunsch()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['tag']) && isset($this->post()['mitarbeiter_uid']) && isset($this->post()['stunde']))
			{
				$pksArray = array($this->post()['tag'],
									$this->post()['mitarbeiter_uid'],
									$this->post()['stunde']
								);

				$result = $this->ZeitwunschModel->update($pksArray, $this->post());
			}
			else
			{
				$result = $this->ZeitwunschModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($zeitwunsch = NULL)
	{
		return true;
	}
}
