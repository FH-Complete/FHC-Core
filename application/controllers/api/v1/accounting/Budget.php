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

class Budget extends API_Controller
{
	/**
	 * Budget API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Budget' => 'basis/budget:rw'));
		// Load model BudgetModel
		$this->load->model('accounting/budget_model', 'BudgetModel');
	}

	/**
	 * @return void
	 */
	public function getBudget()
	{
		$kostenstelle_id = $this->get('kostenstelle_id');
		$geschaeftsjahr_kurzbz = $this->get('geschaeftsjahr_kurzbz');

		if (isset($kostenstelle_id) && isset($geschaeftsjahr_kurzbz))
		{
			$result = $this->BudgetModel->load(array($kostenstelle_id, $geschaeftsjahr_kurzbz));

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
	public function postBudget()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['budget_id']) && isset($this->post()['geschaeftsjahr_kurzbz']))
			{
				$result = $this->BudgetModel->update(array($this->post()['budget_id'], $this->post()['geschaeftsjahr_kurzbz']), $this->post());
			}
			else
			{
				$result = $this->BudgetModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($budget = NULL)
	{
		return true;
	}
}
