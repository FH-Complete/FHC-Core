<?php


class FHC_navigation extends Widget
{
	public function display($data)
	{
		if (!isset($data['items']))
		{
			//default menu with filters abgeschickt/not abgeschickt
			$listFiltersSent = array();
			$listFiltersNotSent = array();

			$listFiltersSent = $this->_getFilterList('%InfoCenterSentApplication%');

			$listFiltersNotSent = $this->_getFilterList('%InfoCenterNotSentApplication%');

			$filtersarray = array('abgeschickt' => array('link' => '#', 'description' => 'Abgeschickt', 'expand' => true, 'children' => array()),
				'nichtabgeschickt' => array('link' => '#', 'description' => 'Nicht abgeschickt','expand' => true,'children' => array()));

			$this->_fillFilters($listFiltersSent, $filtersarray['abgeschickt']);
			$this->_fillFilters($listFiltersNotSent, $filtersarray['nichtabgeschickt']);

			$data = array();
			$data['items'] = array('dashboard' => array('link' => '#', 'description' => 'Dashboard', 'icon' => 'dashboard'),
				'filters' => array('link' => '#', 'description' => 'Filter', 'icon' => 'filter','expand' => true, 'children' =>
					$filtersarray
				));
		}

		$this->view('widgets/fhcnavigation', $data);
	}

	private function _getFilterList($filter_kurzbz)
	{
		$this->load->model('system/Filters_model', 'FiltersModel');

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

	private function _fillFilters($filters, &$tofill)
	{
		foreach ($filters as $filterId => $description)
		{
			$toPrint = "%s=%s";
			$tofill['children'][] = array('link' => sprintf($toPrint, base_url('index.ci.php/system/infocenter/InfoCenter?filterId'), $filterId), 'description' => $description);
		}
	}
}