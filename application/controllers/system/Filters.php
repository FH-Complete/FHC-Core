<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

class Filters extends VileSci_Controller
{
	const SESSION_NAME = 'FILTER';

	const SELECTED_FIELDS = 'selectedFields';
	const SELECTED_FILTERS = 'selectedFilters';
	const ACTIVE_FILTERS = 'activeFilters';
	const ACTIVE_FILTERS_OPTION = 'activeFiltersOption';
	const ACTIVE_FILTERS_OPERATION = 'activeFiltersOperation';
	const FILTER_NAME = 'filterName';

	public function __construct()
    {
        parent::__construct();

        // Load session library
        $this->load->library('session');

		$this->load->model('system/Filters_model', 'FiltersModel');
		$this->load->model('person/Benutzer_model', 'BenutzerModel');
    }

	public function tableDataset()
	{
		$json = new stdClass();

		$json->selectedFields = $_SESSION[self::SESSION_NAME]['selectedFields'];
		$json->columnsAliases = $_SESSION[self::SESSION_NAME]['columnsAliases'];
		$json->additionalColumns = $_SESSION[self::SESSION_NAME]['additionalColumns'];
		$json->checkboxes = $_SESSION[self::SESSION_NAME]['checkboxes'];
		$json->dataset = $_SESSION[self::SESSION_NAME]['dataset'];

		$this->output->set_content_type('application/json')->set_output(json_encode($json));
	}

	public function selectFields()
	{
		$json = new stdClass();

		$json->allSelectedFields = $_SESSION[self::SESSION_NAME]['allSelectedFields'];
		$json->allColumnsAliases = $_SESSION[self::SESSION_NAME]['allColumnsAliases'];

		$json->selectedFields = $_SESSION[self::SESSION_NAME]['selectedFields'];
		$json->columnsAliases = $_SESSION[self::SESSION_NAME]['columnsAliases'];

		$this->output->set_content_type('application/json')->set_output(json_encode($json));
	}

	public function selectFilters()
	{
		$json = new stdClass();

		$json->allSelectedFields = $_SESSION[self::SESSION_NAME]['allSelectedFields'];
		$json->allColumnsAliases = $_SESSION[self::SESSION_NAME]['allColumnsAliases'];

		$json->selectedFilters = $_SESSION[self::SESSION_NAME]['selectedFilters'];
		$json->selectedFiltersAliases = array();
		$json->selectedFiltersMetaData = array();

		$json->selectedFiltersActiveFilters = array();
		$json->selectedFiltersActiveFiltersOperation = array();
		$json->selectedFiltersActiveFiltersOption = array();

		$metaData = $_SESSION[self::SESSION_NAME]['metaData'];
		$activeFilters = $_SESSION[self::SESSION_NAME]['activeFilters'];
		$activeFiltersOperation = $_SESSION[self::SESSION_NAME]['activeFiltersOperation'];
		$activeFiltersOption = $_SESSION[self::SESSION_NAME]['activeFiltersOption'];

		for ($i = 0; $i < count($json->selectedFilters); $i++)
		{
			$pos = array_search($json->selectedFilters[$i], $json->allSelectedFields);

			if ($pos !== false)
			{
				$json->selectedFiltersAliases[] = $json->selectedFilters[$i];
				if ($json->allColumnsAliases != null && is_array($json->allColumnsAliases))
				{
					$json->selectedFiltersAliases[] = $json->allColumnsAliases[$pos];
				}

				$json->selectedFiltersMetaData[] = $metaData[$pos];

				if (isset($activeFilters[$json->selectedFilters[$i]]))
				{
					$json->selectedFiltersActiveFilters[] = $activeFilters[$json->selectedFilters[$i]];
				}

				if (isset($activeFiltersOperation[$json->selectedFilters[$i]]))
				{
					$json->selectedFiltersActiveFiltersOperation[] = $activeFiltersOperation[$json->selectedFilters[$i]];
				}

				if (isset($activeFiltersOption[$json->selectedFilters[$i]]))
				{
					$json->selectedFiltersActiveFiltersOption[] = $activeFiltersOption[$json->selectedFilters[$i]];
				}
			}
		}

		$this->output->set_content_type('application/json')->set_output(json_encode($json));
	}

	public function saveFilter()
	{
		$this->_saveFilter($this->input->post("customFilterDescription"));

		$this->output->set_content_type('application/json')->set_output(json_encode('Tutto bene!!!'));
	}

	/**
	 *
	 */
	private function _saveFilter($customFilterDescription)
	{
		$objToBeSaved = new stdClass();

		$filterSessionArray = $this->session->userdata(self::SESSION_NAME);

		$objToBeSaved->name = $customFilterDescription;

		if (isset($filterSessionArray[self::SELECTED_FIELDS]))
		{
			$selectedFields = $filterSessionArray[self::SELECTED_FIELDS];
			$objToBeSaved->columns = array();

			for ($selectedFieldsCounter = 0; $selectedFieldsCounter < count($selectedFields); $selectedFieldsCounter++)
			{
				$objToBeSaved->columns[$selectedFieldsCounter] = new stdClass();
				$objToBeSaved->columns[$selectedFieldsCounter]->name = $selectedFields[$selectedFieldsCounter];
			}
		}

		if (isset($filterSessionArray[self::SELECTED_FILTERS]))
		{
			$selectedFilters = $filterSessionArray[self::SELECTED_FILTERS];
			$objToBeSaved->filters = array();

			for ($selectedFiltersCounter = 0; $selectedFiltersCounter < count($selectedFilters); $selectedFiltersCounter++)
			{
				$objToBeSaved->filters[$selectedFiltersCounter] = new stdClass();
				$objToBeSaved->filters[$selectedFiltersCounter]->name = $selectedFilters[$selectedFiltersCounter];

				if (isset($filterSessionArray[self::ACTIVE_FILTERS])
					&& isset($filterSessionArray[self::ACTIVE_FILTERS][$selectedFilters[$selectedFiltersCounter]]))
				{
					$objToBeSaved->filters[$selectedFiltersCounter]->condition = $filterSessionArray[self::ACTIVE_FILTERS][$selectedFilters[$selectedFiltersCounter]];
				}

				if (isset($filterSessionArray[self::ACTIVE_FILTERS_OPERATION])
					&& isset($filterSessionArray[self::ACTIVE_FILTERS_OPERATION][$selectedFilters[$selectedFiltersCounter]]))
				{
					$objToBeSaved->filters[$selectedFiltersCounter]->operation = $filterSessionArray[self::ACTIVE_FILTERS_OPERATION][$selectedFilters[$selectedFiltersCounter]];
				}

				if (isset($filterSessionArray[self::ACTIVE_FILTERS_OPTION])
					&& isset($filterSessionArray[self::ACTIVE_FILTERS_OPTION][$selectedFilters[$selectedFiltersCounter]]))
				{
					$objToBeSaved->filters[$selectedFiltersCounter]->option = $filterSessionArray[self::ACTIVE_FILTERS_OPTION][$selectedFilters[$selectedFiltersCounter]];
				}
			}
		}

		$desc = $customFilterDescription;
		$descPGArray = '{"'.$desc.'", "'.$desc.'", "'.$desc.'", "'.$desc.'"}';

		$resultBenutzer = $this->BenutzerModel->load(getAuthUID());
		$personId = $resultBenutzer->retval[0]->person_id;

		$result = $this->FiltersModel->loadWhere(array(
			'app' => $_SESSION[self::SESSION_NAME]['app'],
			'dataset_name' => $_SESSION[self::SESSION_NAME]['datasetName'],
			'description' => $descPGArray,
			'person_id' => $personId
		));

		if (hasData($result))
		{
			$this->FiltersModel->update(
				array(
					'app' => $_SESSION[self::SESSION_NAME]['app'],
					'dataset_name' => $_SESSION[self::SESSION_NAME]['datasetName'],
					'description' => $descPGArray,
					'person_id' => $personId
				),
				array(
					'filter' => json_encode($objToBeSaved)
				)
			);
		}
		else
		{
			$this->FiltersModel->insert(array(
				'app' => $_SESSION[self::SESSION_NAME]['app'],
				'dataset_name' => $_SESSION[self::SESSION_NAME]['datasetName'],
				'filter_kurzbz' => uniqid($personId, true),
				'person_id' => $personId,
				'description' => $descPGArray,
				'sort' => null,
				'default_filter' => false,
				'filter' => json_encode($objToBeSaved),
				'oe_kurzbz' => null
			));
		}
	}

	/**
	 *
	 */
	public function deleteCustomFilter()
	{
		$filter_id = $this->input->post('filter_id');

		if (is_numeric($filter_id))
		{
			$this->FiltersModel->deleteCustomFilter($filter_id);

			$this->output->set_content_type('application/json')->set_output(json_encode('Tutto bene!!!'));
		}
	}

	/**
	 *
	 */
	public function removeSelectedFields()
	{
		$fieldName = $this->input->post('fieldName');

		$allSelectedFields = $_SESSION[self::SESSION_NAME]['allSelectedFields'];
		$allColumnsAliases = $_SESSION[self::SESSION_NAME]['allColumnsAliases'];

		$selectedFields = $_SESSION[self::SESSION_NAME]['selectedFields'];
		$columnsAliases = $_SESSION[self::SESSION_NAME]['columnsAliases'];

		if (($pos = array_search($fieldName, $selectedFields)) !== false)
		{
			array_splice($selectedFields, $pos, 1);

			if ($columnsAliases != null && is_array($columnsAliases))
			{
				array_splice($columnsAliases, $pos, 1);
			}
		}

		$_SESSION[self::SESSION_NAME]['selectedFields'] = $selectedFields;
		$_SESSION[self::SESSION_NAME]['columnsAliases'] = $columnsAliases;

		$json = new stdClass();

		$json->allSelectedFields = $allSelectedFields;
		$json->allColumnsAliases = $allColumnsAliases;
		$json->selectedFields = $selectedFields;
		$json->columnsAliases = $columnsAliases;

		$this->output->set_content_type('application/json')->set_output(json_encode($json));
	}

	/**
	 *
	 */
	public function removeSelectedFilters()
	{
		$fieldName = $this->input->post('fieldName');

		$selectedFilters = $_SESSION[self::SESSION_NAME]['selectedFilters'];
		$selectedFiltersActiveFilters = $_SESSION[self::SESSION_NAME]['activeFilters'];
		$selectedFiltersActiveFiltersOperation = $_SESSION[self::SESSION_NAME]['activeFiltersOperation'];
		$selectedFiltersActiveFiltersOption = $_SESSION[self::SESSION_NAME]['activeFiltersOption'];

		if (($pos = array_search($fieldName, $selectedFilters)) !== false)
		{
			array_splice($selectedFilters, $pos, 1);
			array_splice($selectedFiltersActiveFilters, $pos, 1);
			array_splice($selectedFiltersActiveFiltersOperation, $pos, 1);
			array_splice($selectedFiltersActiveFiltersOption, $pos, 1);
		}

		$_SESSION[self::SESSION_NAME]['selectedFilters'] = $selectedFilters;
		$_SESSION[self::SESSION_NAME]['activeFilters'] = $selectedFiltersActiveFilters;
		$_SESSION[self::SESSION_NAME]['activeFiltersOperation'] = $selectedFiltersActiveFiltersOperation;
		$_SESSION[self::SESSION_NAME]['activeFiltersOption'] = $selectedFiltersActiveFiltersOption;

		$json = new stdClass();

		$json->selectedFilters = $selectedFilters;
		$json->selectedFiltersActiveFilters = $selectedFiltersActiveFilters;
		$json->selectedFiltersActiveFiltersOperation = $selectedFiltersActiveFiltersOperation;
		$json->selectedFiltersActiveFiltersOption = $selectedFiltersActiveFiltersOption;

		$this->output->set_content_type('application/json')->set_output(json_encode($json));
	}

	/**
	 *
	 */
	public function addSelectedFields()
	{
		$fieldName = $this->input->post('fieldName');

		$allSelectedFields = $_SESSION[self::SESSION_NAME]['allSelectedFields'];
		$allColumnsAliases = $_SESSION[self::SESSION_NAME]['allColumnsAliases'];

		$selectedFields = $_SESSION[self::SESSION_NAME]['selectedFields'];
		$columnsAliases = $_SESSION[self::SESSION_NAME]['columnsAliases'];

		if (($pos = array_search($fieldName, $allSelectedFields)) !== false
			&& array_search($fieldName, $selectedFields) === false)
		{
			array_push($selectedFields, $fieldName);

			if ($columnsAliases != null && is_array($columnsAliases))
			{
				array_push($columnsAliases, $allColumnsAliases[$pos]);
			}
		}

		$_SESSION[self::SESSION_NAME]['selectedFields'] = $selectedFields;
		$_SESSION[self::SESSION_NAME]['columnsAliases'] = $columnsAliases;

		$json = new stdClass();

		$json->allSelectedFields = $allSelectedFields;
		$json->allColumnsAliases = $allColumnsAliases;
		$json->selectedFields = $selectedFields;
		$json->columnsAliases = $columnsAliases;

		$this->output->set_content_type('application/json')->set_output(json_encode($json));
	}

	/**
	 *
	 */
	public function addSelectedFilters()
	{
		$fieldName = $this->input->post('fieldName');

		$selectedFilters = $_SESSION[self::SESSION_NAME]['selectedFilters'];
		$selectedFiltersActiveFilters = $_SESSION[self::SESSION_NAME]['activeFilters'];
		$selectedFiltersActiveFiltersOperation = $_SESSION[self::SESSION_NAME]['activeFiltersOperation'];
		$selectedFiltersActiveFiltersOption = $_SESSION[self::SESSION_NAME]['activeFiltersOption'];

		if (!in_array($fieldName, $selectedFilters))
		{
			array_push($selectedFilters, $fieldName);
			$selectedFiltersActiveFilters[$fieldName] = "";
			$selectedFiltersActiveFiltersOperation[$fieldName] = "";
			$selectedFiltersActiveFiltersOption[$fieldName] = "";
		}

		$_SESSION[self::SESSION_NAME]['selectedFilters'] = $selectedFilters;
		$_SESSION[self::SESSION_NAME]['activeFilters'] = $selectedFiltersActiveFilters;
		$_SESSION[self::SESSION_NAME]['activeFiltersOperation'] = $selectedFiltersActiveFiltersOperation;
		$_SESSION[self::SESSION_NAME]['activeFiltersOption'] = $selectedFiltersActiveFiltersOption;

		$json = new stdClass();

		$json->selectedFilters = $selectedFilters;
		$json->selectedFiltersActiveFilters = $selectedFiltersActiveFilters;
		$json->selectedFiltersActiveFiltersOperation = $selectedFiltersActiveFiltersOperation;
		$json->selectedFiltersActiveFiltersOption = $selectedFiltersActiveFiltersOption;

		$this->output->set_content_type('application/json')->set_output(json_encode($json));
	}

	/**
	 *
	 */
	public function applyFilter()
	{
		$fieldNames = $this->input->post('filterNames');
		$filterOperations = $this->input->post('filterOperations');
		$filterOperationValues = $this->input->post('filterOperationValues');
		$filterOptions = $this->input->post('filterOptions');

		$activeFilters = array_combine($fieldNames, $filterOperationValues);
		$activeFiltersOperation = array_combine($fieldNames, $filterOperations);
		$activeFiltersOption = array_combine($fieldNames, $filterOptions);

		$_SESSION[self::SESSION_NAME]['activeFilters'] = $activeFilters;
		$_SESSION[self::SESSION_NAME]['activeFiltersOperation'] = $activeFiltersOperation;
		$_SESSION[self::SESSION_NAME]['activeFiltersOption'] = $activeFiltersOption;

		$json = new stdClass();

		$json->fieldNames = $fieldNames;
		$json->activeFilters = $activeFilters;
		$json->activeFiltersOperation = $activeFiltersOperation;
		$json->activeFiltersOption = $activeFiltersOption;

		$this->output->set_content_type('application/json')->set_output(json_encode($json));
	}
}
