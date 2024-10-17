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

class Studienjahr extends API_Controller
{
	/**
	 * Studienjahr API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Studienjahr' => 'basis/studienjahr:rw'));
		// Load model StudienjahrModel
		$this->load->model('organisation/studienjahr_model', 'StudienjahrModel');


	}

	/**
	 * @return void
	 */
	public function getStudienjahr()
	{
		$studienjahr_kurzbz = $this->get('studienjahr_kurzbz');

		if (isset($studienjahr_kurzbz))
		{
			$result = $this->StudienjahrModel->load($studienjahr_kurzbz);

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
	public function postStudienjahr()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['studienjahr_kurzbz']))
			{
				$result = $this->StudienjahrModel->update($this->post()['studienjahr_kurzbz'], $this->post());
			}
			else
			{
				$result = $this->StudienjahrModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($studienjahr = NULL)
	{
		return true;
	}
}
