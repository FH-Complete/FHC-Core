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

class Nation extends APIv1_Controller
{
	/**
	 * Course API constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		// Load model PersonModel
		$this->load->model('codex/nation_model', 'NationModel');
		// Load set the uid of the model to let to check the permissions
		$this->NationModel->setUID($this->_getUID());
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
		
		if ($result->error == EXIT_SUCCESS)
		{
			if ($this->get('ohnesperre'))
			{
				$result = $this->NationModel->loadWhere('sperre IS NULL');
			}
			else
			{
				$result = $this->NationModel->loadWhole();
			}
		}
		
		$this->response($result, REST_Controller::HTTP_OK);
	}
}