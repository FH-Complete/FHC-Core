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

class Nation extends API_Controller
{
	/**
	 * Course API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Nation' => 'basis/nation:r', 'All' => 'basis/nation:r'));
		// Load model NationModel
		$this->load->model('codex/Nation_model', 'NationModel');
	}

	public function getNation()
    {
		$nation_code = $this->get("nation_code");

		if (isset($nation_code))
		{
			$result = $this->NationModel->loadWhere(array('nation_code' => $nation_code));

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
    }

	public function getAll()
	{
		if (!$this->get('orderEnglish'))
		{
			$result = $this->NationModel->addOrder('kurztext');
		}
		else
		{
			$result = $this->NationModel->addOrder('engltext');
		}

		if (isSuccess($result))
		{
			if ($this->get('ohnesperre'))
			{
				$result = $this->NationModel->loadWhere('sperre IS NULL');
			}
			else
			{
				$result = $this->NationModel->load();
			}
		}

		$this->response($result, REST_Controller::HTTP_OK);
	}
}
