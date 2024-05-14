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

class Contentgruppe extends API_Controller
{
	/**
	 * Contentgruppe API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Contentgruppe' => 'basis/contentgruppe:rw'));
		// Load model ContentgruppeModel
		$this->load->model('content/contentgruppe_model', 'ContentgruppeModel');
	}

	/**
	 * @return void
	 */
	public function getContentgruppe()
	{
		$gruppe_kurzbz = $this->get('gruppe_kurzbz');
		$content_id = $this->get('content_id');

		if (isset($gruppe_kurzbz) && isset($content_id))
		{
			$result = $this->ContentgruppeModel->load(array($gruppe_kurzbz, $content_id));

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
	public function postContentgruppe()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['gruppe_kurzbz']) && isset($this->post()['content_id']))
			{
				$result = $this->ContentgruppeModel->update(array($this->post()['gruppe_kurzbz'], $this->post()['content_id']), $this->post());
			}
			else
			{
				$result = $this->ContentgruppeModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($contentgruppe = NULL)
	{
		return true;
	}
}
