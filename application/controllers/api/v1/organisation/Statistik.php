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

class Statistik extends API_Controller
{
	/**
	 * Statistik API constructor.
	 */
	public function __construct()
	{
		parent::__construct(
			array(
				'Statistik' => 'basis/statistik:rw',
				'All' => 'basis/statistik:r',
				'MenueArray' => 'basis/statistik:r'
			)
		);
		// Load model StatistikModel
		$this->load->model('organisation/statistik_model', 'StatistikModel');


	}

	/**
	 * @return void
	 */
	public function getStatistik()
	{
		$statistik_kurzbz = $this->get('statistik_kurzbz');

		if (isset($statistik_kurzbz))
		{
			$result = $this->StatistikModel->load($statistik_kurzbz);

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
	public function getAll()
	{
		$this->StatistikModel->addOrder($this->get('order'));

		$result = $this->StatistikModel->load();

		$this->response($result, REST_Controller::HTTP_OK);
	}

	/**
	 * @return void
	 */
	public function getMenueArray()
	{
		$this->StatistikModel->addOrder('gruppe');
		$this->StatistikModel->addOrder('bezeichnung');
		$this->StatistikModel->addOrder('statistik_kurzbz');

		$result = $this->StatistikModel->load();

		$this->response($result, REST_Controller::HTTP_OK);
	}

	/**
	 * @return void
	 */
	public function postStatistik()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['statistik_kurzbz']))
			{
				$result = $this->StatistikModel->update($this->post()['statistik_kurzbz'], $this->post());
			}
			else
			{
				$result = $this->StatistikModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($statistik = NULL)
	{
		return true;
	}
}
