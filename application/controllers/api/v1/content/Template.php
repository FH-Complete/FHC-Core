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

class Template extends API_Controller
{
	/**
	 * Template API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Template' => 'basis/template:rw'));
		// Load model TemplateModel
		$this->load->model('content/template_model', 'TemplateModel');
	}

	/**
	 * @return void
	 */
	public function getTemplate()
	{
		$template_kurzbz = $this->get('template_kurzbz');

		if (isset($template_kurzbz))
		{
			$result = $this->TemplateModel->load($template_kurzbz);

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
	public function postTemplate()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['template_kurzbz']))
			{
				$result = $this->TemplateModel->update($this->post()['template_kurzbz'], $this->post());
			}
			else
			{
				$result = $this->TemplateModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($template = NULL)
	{
		return true;
	}
}
