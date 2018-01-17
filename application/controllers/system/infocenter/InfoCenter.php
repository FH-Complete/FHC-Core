<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

class InfoCenter extends VileSci_Controller
{
	public function __construct()
    {
        parent::__construct();

		//
		$this->load->model('system/Filters_model', 'FiltersModel');

		//
        $this->load->library('WidgetLib');
    }

	/**
	 *
	 */
	public function index()
	{
		$listFiltersSent = array();
		$listFiltersNotSent = array();

		$listFiltersSent = $this->_getFilterList('%InfoCenterSentApplication%');

		$listFiltersNotSent = $this->_getFilterList('%InfoCenterNotSentApplication%');

		$this->load->view(
			'system/infocenter/infocenter.php',
			array(
				'listFiltersSent' => $listFiltersSent,
				'listFiltersNotSent' => $listFiltersNotSent
			)
		);
	}

	/**
	 *
	 */
	private function _getFilterList($filter_kurzbz)
	{
		$listFilters = array();

		$personActionsArray = array(
			'app' => 'aufnahme',
			'dataset_name' => 'PersonActions',
			'person_id' => null,
			'default_filter' => false,
			'array_length(description, 1) >' => 0
		);

		$this->FiltersModel->resetQuery();
		$this->FiltersModel->addSelect('filter_id, description');
		$this->FiltersModel->addOrder('sort', 'ASC');

		$personActionsArray['filter_kurzbz ILIKE'] = $filter_kurzbz;
		$filters = $this->FiltersModel->loadWhere($personActionsArray);
		if (hasData($filters))
		{
			for ($filtersCounter = 0; $filtersCounter < count($filters->retval); $filtersCounter++)
			{
				$filter = $filters->retval[$filtersCounter];

				$listFilters[$filter->filter_id] = $filter->description[0];
			}
		}

		return $listFilters;
	}
}
