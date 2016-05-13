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

if(!defined('BASEPATH')) exit('No direct script access allowed');

class Budget extends APIv1_Controller
{
	/**
	 * Budget API constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		// Load model BudgetModel
		$this->load->model('accounting/budget_model', 'BudgetModel');
		// Load set the uid of the model to let to check the permissions
		$this->BudgetModel->setUID($this->_getUID());
	}

	/**
	 * @return void
	 */
	public function getBudget()
	{
		$budgetID = $this->get('budget_id');
		
		if(isset($budgetID))
		{
			$result = $this->BudgetModel->load($budgetID);
			
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
		if($this->_validate($this->post()))
		{
			if(isset($this->post()['budget_id']))
			{
				$result = $this->BudgetModel->update($this->post()['budget_id'], $this->post());
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